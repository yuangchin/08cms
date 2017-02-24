<?php
header('Content-Type: text/html; charset=' . $mcharset);
//填写自己的appid
$client_id = $qq_appid;
//填写自己的appkey
$client_secret = $qq_appkey;
//调试模式
$debug = false;
//回调url
define('CALLBACK', $cms_abs . 'tools/other_site_sdk/other_site_public_callback.php?type=qq');