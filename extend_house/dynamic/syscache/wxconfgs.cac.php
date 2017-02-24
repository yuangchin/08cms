<?php

// 总站配置
$wxconfgs['sys_confgs'] = array (
	'tabs' => array( //有哪些类型的菜单配置
		'0' => '总站',
		'property' => '楼盘',
		'brokers' => '经纪人',
	),
	'fids' => array( //有哪些类型的菜单配置
		'mhome' => '/mobile/index.php?caid=11', //手机版本:会员首页fid
		//'local' => '201', //微信版本:我的附近fid
		//'jifen' => '202', //微信版本:会员积分fid
	),
	'fields' => array( //一些列表(主要是会员)中显示哪些字段
		'subject',
		'company',
		'companynm',
		'nicename',
		'xingming'
	),
	'push_more' => array( //push_more
		'push_130' => '{mobileurl}index.php?caid=2',
		'push_131' => '{mobileurl}index.php?caid=3',
		'push_132' => '{mobileurl}index.php?caid=4',
	),
	'picks' => array(
		//基础菜单选项
		41 => array (
			'name' => '我的账户',
			'val' => '{mobileurl}wxlogin.php?oauth=snsapi_base&state=mlogin',
		),
		42 => array (
			'name' => '我的附近',
			'val' => 'MY_LOCAL',
		),
		/*43 => array (
			'name' => '签到积分',
			'val' => '{mobileurl}wxlogin.php?oauth=snsapi_base&state=mjifen',
		),*/
		//推送位菜单选项
		51 => array (
			'name' => '推荐楼盘',
			'val' => 'MENU_PUSH_push_130', //对应push_130
		),
		52 => array (
			'name' => '推荐二手房',
			'val' => 'MENU_PUSH_push_131', //对应push_131
		),
		53 => array (
			'name' => '推荐出租',
			'val' => 'MENU_PUSH_push_132', //对应push_132
		),
		61 => 
		array (
			'name' => '计算器',
			'val' => '{mobileurl}index.php?caid=1&addno=2',
		),
	),
);

// 总站菜单
$wxconfgs['0'] = array (

	'config' => array(
		'name' => '总站菜单',
		'type' => 'a',
		'chids' => array(4),
	),
	'default' => array(
	
		10 => 
		array (
			'name' => '找楼盘',
			'val' => '',
		),
		11 => 
		array (
			'name' => '找楼盘',
			'val' => '{mobileurl}index.php?caid=2',
		),
		12 => 
		array (
			'name' => '查房价',
			'val' => '{mobileurl}index.php?caid=2&addno=3',
		),
		13 => 
		array (
			'name' => '看房团',
			'val' => '{mobileurl}index.php?caid=560',
		),
		14 => 
		array (
			'name' => '团购',
			'val' => '{mobileurl}index.php?caid=5',
		),
		15 => 
		array (
			'name' => '商业地产',
			'val' => '{mobileurl}index.php?caid=612',
		),
		
		20 => 
		array (
			'name' => '二手出租',
			'val' => '',
		),
		21 => 
		array (
			'name' => '二手房',
			'val' => '{mobileurl}index.php?caid=3',
		),
		22 => 
		array (
			'name' => '出租房',
			'val' => '{mobileurl}index.php?caid=4',
		),
		23 => 
		array (
			'name' => '小区',
			'val' => '{mobileurl}index.php?caid=2&addno=2',
		),
		24 => 
		array (
			'name' => '经纪人',
			'val' => '{mobileurl}index.php?caid=3&addno=3',
		),
		25 => 
		array (
			'name' => '经纪公司',
			'val' => '{mobileurl}index.php?caid=3&addno=4',
		),
		
		30 => 
		array (
			'name' => '我…',
			'val' => '',
		),
		31 => array (
			'name' => '我的账户',
			'val' => '{mobileurl}wxlogin.php?oauth=snsapi_base&state=mlogin',
		),
		32 => array (
			'name' => '我的附近',
			'val' => 'MY_LOCAL',
		),
		/*33 => array (
			'name' => '每日积分',
			'val' => '{mobileurl}wxlogin.php?oauth=snsapi_base&state=mjifen',
		),*/
		
	),	
	'picks' => array(),
);

// 楼盘
$wxconfgs['property'] = array (
	'config' => array(
		'name' => '楼盘微信菜单',
		'type' => 'a',
		'chids' => array(4),
	),
	'default' => array(
	
		10 => 
		array (
			'name' => '楼盘概述',
			'val' => '',
		),
		11 => 
		array (
			'name' => '最新动态',
			'val' => '{mobileurl}archive.php?aid={aid}&addno=3&img_hx=1#index_section',
		),
		12 => 
		array (
			'name' => '价格',
			'val' => '{mobileurl}archive.php?aid={aid}&addno=5#index_section',
		),
		13 => 
		array (
			'name' => '点评',
			'val' => '{mobileurl}archive.php?aid={aid}#comment_section',
		),
		14 => 
		array (
			'name' => '计算器',
			'val' => '{mobileurl}index.php?caid=1&addno=2',
		),
		
		
		20 => 
		array (
		'name' => '楼盘相册',
		'val' => '',
		),
		21 => 
		array (
		'name' => '户型图',
		'val' => '{mobileurl}archive.php?aid={aid}&addno=3&img_hx=1#index_section',
		),
		22 => 
		array (
		'name' => '楼盘实景',
		'val' => '{mobileurl}archive.php?aid={aid}&addno=3&img_tk=1#index_section',
		),

		30 => 
		array (
		'name' => '地图',
		'val' => '{mobileurl}archive.php?aid={aid}&addno=1#index_section',
		),
		
	),
	'picks' => array(),
);

// 经纪人
$wxconfgs['brokers'] = array (
	'config' => array(
		'name' => '经纪人微信菜单',
		'type' => 'm',
		'chids' => array(2),
	),
	'default' => array(
	
        10 => 
        array (
        'name' => '二手房',
        'val' => '{mobileurl}index.php?caid=13&mid={mid}&addno=1',
        ),
		
        20 => 
        array (
        'name' => '出租房',
        'val' => '{mobileurl}index.php?caid=13&mid={mid}&addno=2',
        ),

        30 => 
        array (
        'name' => '我的…',
        'val' => '',
        ),
        31 => 
        array (
        'name' => '我的店铺',
        'val' => '{mobileurl}index.php?caid=13&mid={mid}',
        ),
        32 => 
        array (
        'name' => '我的工具',
        'val' => '{mobileurl}index.php?caid=1&addno=2',
        ),
		
		
	),
	'picks' => array(),
);

?>