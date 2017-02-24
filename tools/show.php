<?php
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
if(empty($by) || empty($sn)){
	$message =  "支付失败！";
}else{
	if(empty($jumpurl)){
		$pages = array(
			'orders'	=> 'orders',
			'pays'		=> 'pays',
		);
		$names = array(
			'orders'	=> 'oid',
			'pays'		=> 'pid',
		);
		$action		= empty($pages) || !is_array($pages) || !isset($pages[$by]) ? $by : $pages[$by];
		$name		= empty($names) || !is_array($names) || !isset($names[$by]) ? 'id' : $names[$by];
		$adminm		= 'adminm.php';
		$jumpurl	= "$cms_abs$adminm?action=$action&$name=$id";
	}
	$message = "支付完成！";
}
cls_message::show($message, $jumpurl);
?>