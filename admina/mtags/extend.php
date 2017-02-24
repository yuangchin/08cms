<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
$extype = empty($mtagnew['extype']) ? (empty($mtag['extype']) ? '' : $mtag['extype']) : $mtagnew['extype'];
if(!$extype || empty($extypes[$extype])) mtag_error('请选择扩展标识类型');
$_exfile = dirname(__FILE__).DS."..".DS."extags/$extype.php";
if(!file_exists($_exfile)) mtag_error('未找到扩展接口文件');
include $_exfile;
?>
