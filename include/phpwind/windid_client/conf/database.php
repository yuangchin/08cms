<?php
/**
 * 1.作为WindID客户端请配置连接的服务端WindID数据库
 * 2.作为服务端或独立系统，也可配置WindID数据库与phpwind分库操作
 */

/*如果WindID数据库与phpwind数据库采用相同设置，请注释此项*/
if(empty($mconfigs)) $mconfigs = cls_cache::Read('mconfigs');
return array(
    //数据库地址|库名|端口
	'dsn' => "mysql:host={$mconfigs['pptin_dbhost']};dbname={$mconfigs['pptin_dbname']};port={$mconfigs['pptin_port']}",  
    //数据库用户名
	'user' => $mconfigs['pptin_dbuser'],	
    //数据库密码									 
	'pwd' => $mconfigs['pptin_dbpwd'],	
    //数据库编码方式										 
	'charset' => ($mconfigs['pptout_charset'] == 'utf-8' ? 'utf8' : $mconfigs['pptout_charset']),
    //表前缀
	'tableprefix' => $mconfigs['pptin_dbpre']									 
);

?>