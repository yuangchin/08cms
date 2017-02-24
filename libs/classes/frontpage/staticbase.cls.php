<?php
/**
 * 与页面生成静态操作有关的一些方法汇总
 */
defined('M_COM') || exit('No Permission');
abstract class cls_StaticBase{
	
	# 是否为自动更新静态的暂停时期	
	# PausePeriod：管理后台设置的不同类型页面的静态暂停时段，格式："10-12,15,20-22"表示每日的小时段
	public static function InParsePeriod($PausePeriod = ''){
		if($PausePeriod && $na = explode(',',$PausePeriod)){
			$nh = date('G',TIMESTAMP);
			foreach($na as $k){
				if(strpos($k,'-') !== FALSE){
					$xy = explode('-',$k);
					if(($nh >= $xy[0]) && ($nh <= $xy[1])) return true;
				}elseif($k == $nh) return true;
			}
		}
		return false;
	}
	
}
