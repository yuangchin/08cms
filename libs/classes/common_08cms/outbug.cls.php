<?php
class cls_outbug{

	//说明:本类收集post,get,及操作者的ip,session等信息记录成文件；用于整合,支付,微信等调试
	//demo:cls_outbug::main("Weixin_Test::Scan-28b",'','wetest/log_'.date('Y_md').'.log',1);
	
	function xx__construct(){
        //$this->profiler = _08_Profiler::getInstance($prefix);
	}
	
	// *** 调试信息
	static function main($msg='',$paras='',$logfile='',$keepold=1){ 
		$info = empty($msg) ? '' : ("\r\n<b>[User Message]=</b>($msg)\r\n");
		$info .= "\r\n<b>[Base Info]</b>".self::fmtArr(self::sysInfo($paras))."\r\n"; 
		$info .= self::sysPara($paras);
		if($logfile=='show'){ // show
			$dcss = "border:1px solid #F00; background-color:#FFFFCC; padding:8px; margin:5px; clear:both; display:block;";	
			print_r("\n\n<div style=\"$dcss\">$info</div>\n\n");
		}else{ // file
			self::save($info,$logfile,$keepold);
		}
	}

	// *** 系统参数
	static function sysPara($keys=''){
		if(empty($keys)) $keys = 'GET,POST,SESSION'; // 08系统GET中包含了POST信息  ,'COOKIE' POST,
		$re = ''; 
		$arr = explode(',',$keys); 
		foreach($arr as $key){ 
			$kval = "\$_{$key}"; eval("\$kv = $kval;");
			$re .= "\r\n<b>[$key]</b>\r\n";
			$re .= self::fmtArr($kv);
		} 
		//echo "<pre>GET:"; print_r($_GET); echo "\n\nPOST:"; print_r($_POST); die();
		return $re;
	}

	// *** 系统状态信息
	static function sysInfo(){
		$info = array(); 
		$info['ram'] = memory_get_usage(); 
		$info['run'] = microtime(1); 
		$info['vp'] = $_SERVER['REQUEST_URI'];
		$info['rp'] = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'(null referer)';
		foreach(array('vp','rp') as $k){
			$info[$k] = str_replace(array("'","\\"),array("`","#"),$info[$k]);
		}
		$info['ip'] = self::userIP(1);
		$info['ua'] = self::userAG();
		return $info;
	}
	
	// *** save(file)
	static function save($data,$logfile='',$keepold=1){
		$logfile = $logfile ? (strpos($logfile,'/') ? $logfile : "dynamic/debug/$logfile") : 'dynamic/debug/bug_'.date('Y_md').'.log';
		mmkdir(M_ROOT.dirname($logfile),1);
		if($keepold){
			!is_file($logfile) && @touch($logfile);
			$dold = "\r\n\r\n ".str_repeat('#',76)." \r\n\r\n".@file_get_contents(M_ROOT.$logfile);
		}else{
			$dold = '';	
		}
		$data = "$data$dold"; //echo 'xxx';
		file_put_contents(M_ROOT.$logfile,$data);		
	}
	
	// *** 格式化信息(array-=>string)
	static function fmtArr($arr=array()){
		$re = '';
		if(is_array($arr)){
			foreach($arr as $k=>$v){ 
				$iv = is_array($arr) ? var_export($v,1) : $v;
				$iv = str_replace(array("\r","\n"),array("\\r","\\n"),"[$k]=($iv)"); // "<",">","'", "《","》","`",
				$re .= "$iv\r\n";
			} 
		}elseif(is_object($arr)){
			foreach($arr as $k=>$v){
				$iv = is_array($arr) ? var_export($v,1) : $v;
				$iv = str_replace(array("\r","\n"),array("\\r","\\n"),"[$k]=($iv)"); // "<",">","'", "《","》","`",
				$re .= "$iv\r\n";	
			}
		}
		return $re;
	}

	// 获取客户端软件信息
	static function userAG(){
		$ua = empty($_SERVER['HTTP_USER_AGENT']) ? '(null)' : $_SERVER['HTTP_USER_AGENT'];
		$ua .= '(x_wap|:via:'.@$_SERVER['HTTP_X_WAP_PROFILE'].'|'.@$_SERVER['HTTP_VIA'].')';
		//$ua = str_replace(array("'","\\"),array("",""),$ua);
		return $ua;
	}
	
	// 获取客户端IP地址
	static function userIP($flag=0){
		$a = array('f'=>'HTTP_X_FORWARDED_FOR','a'=>'REMOTE_ADDR','c'=>'HTTP_CLIENT_IP'); //'r'=>'HTTP_X_REAL_FORWARDED_FOR',
		$ip = '';
		foreach($a as $k=>$v){
			$v = str_replace(' ','',$v);
			if(isset($_SERVER[$v]) && !strstr($ip,$_SERVER[$v])){
				$ip .= ';'.($flag ? "$k," : '').$_SERVER[$v];
			}
		}
		//$ip = str_replace(array("'","\\"),array("",""),$ip);
		return $ip;
	}
	
	// 运行统计信息
	static function runInfo(){ 
		global $_cbase;
		$qtime = $_cbase['run']['qtime'];
		$rtime = microtime(1) - $_cbase['run']['timer'];
		if($rtime>1){
			$unit = 's'; 
			$qtime = number_format($qtime,4);
			$rtime = number_format($rtime,4);
		}else{
			$unit = 'ms';
			$qtime = number_format($qtime*1000,4);
			$rtime = number_format($rtime*1000,4);
		} //  Done in 0.253444 sec(s), 12 queries .
		$info = "Done:$qtime/$rtime($unit); ";
		$info .= "".$_cbase['run']['query']."(queries)/".round(memory_get_usage()/1024/1024, 3)."(MB); ";
		$info .= "tpl:".(empty($_cbase['run']['tplname']) ? '(null)' : $_cbase['run']['tplname'])."; "; //tpl 
		$info .= "Time:".str_replace('T',' ',date(DATE_ATOM))."; ";
		return $info;
	}	

}
