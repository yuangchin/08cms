<?php
if(empty($mconfigs)) $mconfigs = cls_cache::Read('mconfigs');
return array( 
	'connect' => $mconfigs['pptout_connect'],  //db为本地连接  http远程连接  如为db，请同时配置database.php里的数据库设置
	'serverUrl' => $mconfigs['pptin_url'],  //服务端访问地址. 如:http://www.phpwind.net
	'clientId' => $mconfigs['pptin_appid'],   //该客户端在WindID里的id
	'clientKey' => $mconfigs['pptin_key'],  //通信密钥，请保持与WindID里的一致
	'charset' => $mconfigs['pptout_charset'],	   //客户端使用的字符编码
);
?>