<?php
define('M_NOUSER',1);
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
header("Content-type:text/javascript;charset=$mcharset");
cls_env::CheckSiteClosed(1);
foreach(array('aid','mid','chid','addno',) as $k) $$k = max(0,intval(@$$k));
$_did = false;

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
}
exit();

