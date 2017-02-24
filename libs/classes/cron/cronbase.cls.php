<?php
class cls_cronbase{
    protected $dir = array();//脚本位置,相对根目录。注意顺序，核心中放在前面，扩展放在后面   
    
    function __construct(){        
        $this->dir = array(
        _08_LIBS_DIR.DS.'classes'.DS.'cron'.DS,
        _08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'classes'.DS.'cron'.DS
        );
}
/*
*目前只能先加脚本,再加计划任务，脚本存在就完成添加成功。
*打算修正为先加计划任务再加脚本到指定目录。这样方便判断是否有重名脚本。
*
*/
    public function isFile($file){
        $arr = array_reverse($this->dir);
        foreach($arr as $d){
            if(is_file(M_ROOT.$d.$file)) return M_ROOT.$d.$file;
            continue;    
        }
        return false;
    }
    
    public function getPath($id=0){
       return empty($this->dir[$id]) ? $this->dir[0] : $this->dir[$id];
    }
    
    
	function _init_cron(){
		global $mconfigs,$timestamp;
		if(@$mconfigs['nextrun'] <= $timestamp) {
			self::run();
		}
	}
    
	function run($cronid = 0) {
		global $db,$tblprefix,$timestamp;
		$cron = $db->fetch_one("SELECT * FROM {$tblprefix}cron WHERE ".($cronid ? "cronid='$cronid'" : "available=1 AND nextrun<='$timestamp'")." ORDER BY nextrun LIMIT 1");		
        $processname ='_08CMS_CRON_'.(empty($cron) ? 'CHECKER' : $cron['cronid']);        
        if(process::islocked($processname, 600)) {
			   return false;
		}          	  
		if($cron) {
            $fileClass = trim($cron['filename']);        
            $fileClass = 'cron_' . str_replace(strrchr($fileClass,'.'),'',$fileClass);
			$cron['filename'] = str_replace(array('..', '/', '\\'), '', $cron['filename']);            
			if(!($cronfile = $this->isFile($cron['filename']))) return false;                  
			$cron['minute'] = explode("\t", $cron['minute']);
			$cron['hour'] = explode("\t",$cron['hour']);
			if(!function_exists('validhour')){
				function validhour($hour){
					$hour = min(24,max(1,$hour));
					return $hour;
				}
			}            
			$hourarr = array_filter($cron['hour'],"validhour");            
			sort($hourarr);
			$hournow = gmdate('H', $timestamp + 8 * 3600);
			$minutenow = gmdate('i', $timestamp + 8 * 3600);
			if(max($hourarr) >= $hournow){
				foreach($hourarr as $hour){
					if($hour > $hournow){
						$cron['hour'] = $hour;
						break;
					}elseif($hour == $hournow){
						if(max($cron['minute']) > $minutenow){
							$cron['hour'] = $hour;
							break;
						}else{
							if($hour == max($hourarr)){
								$cron['hour'] = min($hourarr);
							}
							continue;	
						}
					}
				}
			}else{
				$cron['hour'] = min($hourarr);
			}
			#var_export($cron);
			
			self::setnextime($cron);
			@set_time_limit(1000);
			@ignore_user_abort(TRUE);                                             
			if(is_file($cronfile)) {
				 include_once _08_INCLUDE_PATH .'admina.fun.php';			     
			     include_once(M_ROOT.$this->getPath(0).'exec.cron.php');				  
			     include($cronfile);	
			}else{
			     return false;
			}      
            if(class_exists($fileClass)){
                new $fileClass();
            }else{
                cls_message::show('<span style="color:red;">'.$cron['filename'].'计划任务执行失败,请检查计划任务脚本书写规划</span>',axaction(6,'?entry=misc&action=cronedit'),5000);
            }
		}
		self::nextcron();
		process::unlock($processname);
		return true;
	}
	
	function nextcron() {
		global $timestamp,$db,$tblprefix;
		$mconfigs = cls_cache::Read('mconfigs');
		$nextrun = $db->result_one("SELECT nextrun FROM {$tblprefix}cron WHERE available>'0' ORDER BY nextrun LIMIT 1");
		if($nextrun !== FALSE) {
			$mconfigs['nextrun']=$nextrun;
			cls_CacheFile::Save($mconfigs, 'mconfigs');
		} else {
			$mconfigs['nextrun']=$timestamp + 86400 * 365;
			cls_CacheFile::Save($mconfigs, 'mconfigs');
		}
		return true;
	}

	function setnextime($cron) {
		global $timestamp,$db,$tblprefix;
		if(empty($cron)) return FALSE;
		list($yearnow, $monthnow, $daynow, $weekdaynow, $hournow, $minutenow) = explode('-', gmdate('Y-m-d-w-H-i', $timestamp + 8 * 3600));
		if($cron['weekday'] == -1) {
			if($cron['day'] == -1) {
				$firstday = $daynow;
				$secondday = $daynow + 1;
			} else {
				$firstday = $cron['day'];
				$secondday = $cron['day'] + gmdate('t', $timestamp + 8 * 3600);
			}
		} else {
			$firstday = $daynow + ($cron['weekday'] - $weekdaynow);
			$secondday = $firstday + 7;
		}
		if($firstday < $daynow) {
			$firstday = $secondday;
		}
		if($firstday == $daynow) {
			$todaytime = self::todaynextrun($cron);
			if($todaytime['hour'] == -1 && $todaytime['minute'] == -1) {
				$cron['day'] = $secondday;
				$nexttime = self::todaynextrun($cron, 0, -1);
				$cron['hour'] = $nexttime['hour'];
				$cron['minute'] = $nexttime['minute'];
			} else {
				$cron['day'] = $firstday;
				$cron['hour'] = $todaytime['hour'];
				$cron['minute'] = $todaytime['minute'];
			}
			
		} else {
			$cron['day'] = $firstday;
			$nexttime = self::todaynextrun($cron, 0, -1);
			$cron['hour'] = $nexttime['hour'];
			$cron['minute'] = $nexttime['minute'];
		}
		$nextrun = @gmmktime($cron['hour'], $cron['minute'] > 0 ? $cron['minute'] : 0, 0, $monthnow, $cron['day'], $yearnow) - 8 * 3600;

		$availableadd = $nextrun > $timestamp ? '' : ', available=\'0\'';
		$db->query("UPDATE {$tblprefix}cron SET lastrun='$timestamp', nextrun='$nextrun' $availableadd WHERE cronid='$cron[cronid]'");

		return true;
	}
	
	
	function todaynextrun($cron, $hour = -2, $minute = -2) {
		global $timestamp;

		$hour = $hour == -2 ? gmdate('H', $timestamp + 8 * 3600) : $hour;
		$minute = $minute == -2 ? gmdate('i', $timestamp + 8 * 3600) : $minute;
		$nexttime = array();
		if($cron['hour'] == -1 && !$cron['minute']) {
			$nexttime['hour'] = $hour;
			$nexttime['minute'] = $minute + 1;
		} elseif($cron['hour'] == -1 && $cron['minute'] != '') {
			$nexttime['hour'] = $hour;
			if(($nextminute = self::nextminute($cron['minute'], $minute)) === false) {
				++$nexttime['hour'];
				$nextminute = $cron['minute'][0];
			}
			$nexttime['minute'] = $nextminute;
		} elseif($cron['hour'] != -1 && $cron['minute'] == '') {
			if($cron['hour'] < $hour) {
				$nexttime['hour'] = $nexttime['minute'] = -1;
			} elseif($cron['hour'] == $hour) {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $minute + 1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = 0;
			}
		} elseif($cron['hour'] != -1 && $cron['minute'] != '') {
			$nextminute = self::nextminute($cron['minute'], $minute);
			if($cron['hour'] < $hour || ($cron['hour'] == $hour && $nextminute === false)) {
				$nexttime['hour'] = -1;
				$nexttime['minute'] = -1;
			} elseif($cron['hour'] > $hour) {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = min($cron['minute']);
			}else{
				#var_export($nextminute);
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $nextminute;
			}
		}

		return $nexttime;
	}

	function nextminute($nextminutes, $minutenow) {
		foreach($nextminutes as $nextminute) {
			if($nextminute > $minutenow) {
				return $nextminute;
			}
		}
		return false;
	}
}

class process
{
	function islocked($process, $ttl = 0) {
		$ttl = $ttl < 1 ? 600 : intval($ttl);
		if(self::_status('get', $process)){
			return true;
		} else {
			return self::_find($process, $ttl);
		}
	}

	function unlock($process) {
		self::_status('rm', $process);
		self::_cmd('rm', $process);
	}

	function _status($action, $process) {
		static $plist = array();    
		switch ($action) {
			case 'set' : $plist[$process] = true; break;
			case 'get' : return !empty($plist[$process]); break;
			case 'rm' : $plist[$process] = null; break;
			case 'clear' : $plist = array(); break;
		}
		return true;
	}

	function _find($name, $ttl) {

		if(!self::_cmd('get', $name)) {
			self::_cmd('set', $name, $ttl);
			$ret = false;
		} else {
			$ret = true;
		}
		self::_status('set', $name);
		return $ret;
	}

	function _cmd($cmd, $name, $ttl = 0) {
		return self::_process_cmd_db($cmd, $name, $ttl);
	}

	function _process_cmd_db($cmd, $name, $ttl = 0) {
		global $db,$tblprefix,$timestamp;
#		echo $cmd."->".$name."<br/>";
		$ret = '';
		switch ($cmd) {
			case 'set':
				$ret = $db->query("insert into {$tblprefix}process set processid='".$name."',expiry='".($timestamp+$ttl)."'");
				break;
			case 'get':
				$ret = $db->fetch_one("SELECT * FROM {$tblprefix}process WHERE processid='$name'");
				if(empty($ret)) {
					$ret = false;
				} else {
					if($ret['expiry'] < $timestamp){
						cls_cron::nextcron();
						self::unlock($name);
						return $ret = true;
					}else{
						$ret = true;
					}
				}
				break;
			case 'rm':
				$ret = $db->query("delete from {$tblprefix}process where processid='$name' OR expiry<'".$timestamp. "'");
				break;
		}
		return $ret;
	}
	
}


