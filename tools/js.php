<?php
if(empty($_GET['is_p'])) define('M_NOUSER',1);
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
header("Content-type:text/javascript;charset=$mcharset");
_08_Loader::import( _08_INCLUDE_PATH . 'http.cls' );
http::clearCache();

cls_JsTag::Create(array('DataFormat' => 'jswrite'));