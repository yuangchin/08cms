<?php
/***官方扩展项目中的脚本分布配置****
* 手动修改此文件后，需要刷新扩展缓存
* 请按优先顺序，放置各个具体的入口，因为有可能出现嵌套的现象
* 脚本位置为当前系统根目录中的相对位置 
* 单个值可为空(表示只允许不传此键或值为空)，多个值使用数组传值，其中不能有空值
* 如果新添加入口脚本，请打开源入口脚本，查看定制的起始点。
* 官方扩展入口优先级低于用户定制入口，示例：
$extendscripts = array(
	'admina' => array(
		'admina/xxx.php' => array(//脚本入口,脚本位置相对于当前系统根目录
			'entry' => 'abrels',
			'action' => 'abreldetail',
			'arid' => array(3,4,5),
		),
		'admina/ttt.php' => array(//脚本入口,脚本位置相对于当前系统根目录
			'entry' => 'abrels',
			'action' => 'abreldetail',
			'arid' => 8,
		),
	),
);
*/
$extendscripts = array(
	'admina.php' => array(
	),
	'adminm.php' => array(
	),
	'login.php' => array(
	),
	'register.php' => array(
	),
	'index.php' => array(
	),
	'archive.php' => array(//非url参数chid可以指定为键值
	),
	'info.php' => array(
	),
	'search.php' => array(
	),
	'tools/ajax.php' => array(
	),
	'etools/ajax.php' => array(
	),
	'member/index.php' => array(
	),
	'member/search.php' => array(
	),
	'mspace/index.php' => array(
	),
	'mspace/archive.php' => array(//非url参数chid可以指定为键值
	),
);
?>
