<?php
define('M_NOUSER',1);
include_once dirname(dirname(__FILE__)).'/include/general.inc.php'; 

header("content-type: text/javascript;"); // charset=$mcharset
$act = cls_Safefillter::_req('act'); 

// 表单初始化
if($act=='init'){
	cls_Safefillter::formInit();	
}

// 表单ajax设置签名
if($act=='ajax'){
	cls_Safefillter::formAjax();	
}

// url签名
if($act=='surl'){
	cls_Safefillter::urlSign();
}

/*
define('M_ROOT', substr(dirname(__FILE__), 0, -5)); 
#include(M_ROOT.'include/defines.php');
include(M_ROOT.'libs/classes/common_08cms/safefillter.cls.php');
//include_once M_ROOT.'/include/general.inc.php';
*/
