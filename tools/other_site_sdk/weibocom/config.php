<?php
header('Content-Type: text/html; charset=' . $mcharset);
define( "WB_AKEY" , $sina_appid );
define( "WB_SKEY" , $sina_appkey );
define( "WB_CALLBACK_URL" , $cms_abs . 'tools/other_site_sdk/other_site_public_callback.php?type=sina' );
