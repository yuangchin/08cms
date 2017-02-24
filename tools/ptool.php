<?php
define('M_NOUSER',1);
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
header("Content-type:text/javascript;charset=$mcharset");
cls_env::CheckSiteClosed(1);
foreach(array('aid','mid','chid','addno',) as $k) $$k = max(0,intval(@$$k));
$_did = false;
if($enablestatic && !empty($static)){
	$mode = empty($mode) ? 'arc' : trim($mode);
	switch($mode){
		case 'arc':
			if(cls_Static::InParsePeriod(@$arc_nostatic)) break;
			if(!$aid) break;//将chid传过来
			if(!$chid) $chid = $db->result_one("SELECT chid FROM {$tblprefix}archives_sub WHERE aid='$aid'"); #兼容之前版本生成的静态页面的更新
			if(!$chid || !($channel = cls_channel::Config($chid)) || $channel['noautostatic']) break;
			$arc = new cls_arcedit;
			if(!$arc->set_aid($aid,array('au'=>0,'chid'=>$chid))) break;
			if($arc->need_static_refresh($addno)){
				$arc->tostatic($addno);
				$_did = true;
			}
		break;
		case 'cnindex':	
			$temparr = cls_env::_GET();
			$cnstr = cls_cnode::cnstr($temparr);
			if(cls_Static::InParsePeriod($cnstr ? @$cn_nostatic : @$indexnostatic)) break;
			if(need_static_refresh($cnstr,$addno,'cnode')){
				cls_CnodePage::Create(array('cnstr' => $cnstr,'addno' => $addno,'inStatic' => true));
				$_did = true;
			}
		break;
		case 'mcnode':
			$temparr = cls_env::_GET();
			$cnstr = cls_node::mcnstr($temparr);
			if(cls_Static::InParsePeriod(@$mcn_nostatic)) break;
			if(need_static_refresh($cnstr,$addno,'mcnode')){
				cls_McnodePage::Create(array('cnstr' => $cnstr,'addno' => $addno,'inStatic' => true));
				$_did = true;
			}
		break;
	}
}
if(!$_did && !empty($msp_static) && !empty($mspacecircle) && $mid){
	$n = true;
	if(cls_Static::InParsePeriod(@$ms_nostatic)) $n = false;
	if($n && !($arr = $db->fetch_one("SELECT mspacepath,msrefreshdate FROM {$tblprefix}members WHERE mid='$mid'"))) $n = false;
	if($n && (!$arr['mspacepath'] || ($timestamp - $arr['msrefreshdate'] < $mspacecircle * 60))) $n = false;
	if($n){
		cls_Mspace::ToStatic($mid);
		$_did = true;
		// 设置:空间静态更新时间
		$auser = new cls_userinfo;
		$auser->activeuser($mid);
		$auser->updatefield('msrefreshdate',$timestamp);
		$auser->updatedb();
	}
}
if(!empty($upsen)){
	define('M_UPSEN',1);
	$curuser->currentuser();
	$_infoed = 1;
}

if(!empty($enabelstat)){
	if($aid){
		if($clickscachetime){
			$cf = M_ROOT.'dynamic/stats/aclicks.cac';
			$ct = M_ROOT.'dynamic/stats/aclicks_time.cac';
			mmkdir(M_ROOT.'dynamic/stats/',0);
			if(!$_did && ($timestamp - @filemtime($ct) > $clickscachetime)){
				if(@$clicksarr = file($cf)){
					if(@$fp = fopen($cf,'w')) fclose($fp);
					if(@$fp = fopen($ct,'w')) fclose($fp);
					$clicksarr = array_count_values($clicksarr);
					foreach($clicksarr as $k => $v){
						if($k && $v){
							if($ntbl = atbl($k,2)){
								$db->query("UPDATE {$tblprefix}$ntbl SET clicks=clicks+$v".($statweekmonth ? ",wclicks=wclicks+$v,mclicks=mclicks+$v" : '')." WHERE aid=$k");
							}
						}
					}
					$_did = true;
				}
			}
			if(@$fp = fopen($cf,'a')){
				fwrite($fp,"$aid\n");
				fclose($fp);
			}
		}elseif($ntbl = atbl($aid,2)){
			$db->query("UPDATE {$tblprefix}$ntbl SET clicks=clicks+1".($statweekmonth ? ",wclicks=wclicks+1,mclicks=mclicks+1" : '')." WHERE aid='$aid'");
		}
	}
	if(!empty($mid)){
		if($clickscachetime){
			$cf = M_ROOT.'dynamic/stats/msclicks.cac';
			$ct = M_ROOT.'dynamic/stats/msclicks_time.cac';
			mmkdir(M_ROOT.'dynamic/stats/',0);
			if(!$_did && ($timestamp - @filemtime($ct) > $clickscachetime)){
				if($clicksarr = @file($cf)){
					if(@$fp = fopen($cf,'w')) fclose($fp);
					if(@$fp = fopen($ct,'w')) fclose($fp);
					$clicksarr = array_count_values($clicksarr);
					foreach($clicksarr as $k => $v) $k && $v && $db->query("UPDATE {$tblprefix}members_sub SET msclicks=msclicks+$v WHERE mid='$k'");
					$_did = true;
				}
			}
			if(@$fp = fopen($cf,'a')){
				fwrite($fp,"$mid\n");
				fclose($fp);
			}
		}else $db->query("UPDATE {$tblprefix}members_sub SET msclicks=msclicks+1 WHERE mid='$mid'");
	}
}
if(!$_did){	
	$objcron=new cls_cron();
	$objcron->_init_cron();
	#$objcron->run(51);//计划任务前台测试点,测试前先屏蔽前一行，参数为计划任务id
}
exit();

# 根据静态更新周期及上次静态生成时间(needstatics)，判断是否需要自动更新
function need_static_refresh($cnstr = '',$addno = 0,$type = 'cnode'){
	global $indexcircle,$cn_periods,$mcnindexcircle,$timestamp,$db,$tblprefix;
	if($cnstr){
		if($type == 'cnode'){
			if(!($node = cls_node::read_cnode($cnstr))) return false;
			$period = empty($node['cfgs'][$addno]['period']) ? 0 : $node['cfgs'][$addno]['period'];
			$period || $period = empty($cn_periods[$addno]) ? 0 : $cn_periods[$addno];
			$ns = $db->result_one("SELECT needstatics FROM {$tblprefix}cnodes WHERE ename='$cnstr'");
		}else{
			if(!($node = cls_node::read_mcnode($cnstr))) return false;
			$period = empty($node['cfgs'][$addno]['period']) ? 0 : $node['cfgs'][$addno]['period'];
			$period || $period = empty($mcnindexcircle) ? 0 : $mcnindexcircle;
			$ns = $db->result_one("SELECT needstatics FROM {$tblprefix}mcnodes WHERE ename='$cnstr'");
		}
		$ns = explode(',',$ns);
		$ns = empty($ns[$addno]) ? 0 : $ns[$addno];
	}else{
		$period = $indexcircle;
		if(!($ns = $db->result_one("SELECT value FROM {$tblprefix}mconfigs WHERE varname='".($type == 'cnode' ? 'ineedstatic' : 'mcnneedstatic')."'"))) $ns = 0;
	}
	$period = empty($period) ? 12*3600 : $period * 60;
	return $timestamp - $period > $ns ? true : false;
}
