<?php
/* 
** 针对积分处理的方法汇总
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_CurrencyBase{
	
	//积分清除计划
	static function clearCurrency(){
		global $db,$tblprefix;
		$interval = cls_env::mconfig('point_interval');
		if(empty($interval))  return;
		$fliename =_08_CACHE_PATH.'stats/currency.cf';
		if(!file_exists($fliename) || filemtime($fliename)<= mktime(0,0,0)){
			$cridsarr = cridsarr(1);
			if(isset($cridsarr[0])) unset($cridsarr[0]);
			$t = strtotime("-$interval months");
			foreach($cridsarr as $k=>$v){
				$db->query("DELETE FROM {$tblprefix}currency$k WHERE createdate < $t");
			}
			mmkdir($fliename,1,1);
			touch($fliename);
			chmod($fliename,'0666');
		}
	}
}