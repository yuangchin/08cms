<?php

//----------------------------------------------------------------------

// 配置-用于后台设置

//global $sms_cfg_aset,$sms_cfg_api,$sms_cfg_tmieout;
$sms_cfg_tmieout = 3; //http连接超时时间(秒), 测试接口(0test)不用这个
//global $sms_cfg_upw,$sms_cfg_pr3,$sms_cfg_pr4,$sms_cfg_pr5;
//global $sms_cfg_mchar,$sms_cfg_mtels;

$sms_cfg_aset = array(
	'winic' => array(
		'name' => '移动商务',
		'home' => 'http://www.winic.org/',
		'unit' => '元', // 余额单位(元 或 条)
		'admin' => 'http://www.900112.com/', //如无此项可不填
		'note' => 'HTTP发送,内容不支持空格换行',
		'nmem' => 'HTTP发送,内容不支持空格换行', //会员提示
	),
	'cr6868' => array(
		'name' => '创瑞传媒',
		'home' => 'http://www.cr6868.com/',
		'unit' => '条', // 余额单位(元 或 条)
		'admin' => 'http://web.cr6868.com/login.aspx', //如无此项可不填
		'note' => '信息中不能含&#特殊字符，具体咨询短信供应商。',
		'nmem' => '', //会员提示
	),
	'emhttp' => array(
		'name' => '亿美(http)',
		'unit' => '元', // 余额单位(元 或 条)
		'home' => 'http://www.emay.cn/', 
		'admin' => '', //
		'note' => '亿美软通接口(http调用), 新注册亿美用户建议首选本调用方式, 第一次使用时,需使用[<a href="include/sms/extra_act.php?act=login" target="_blank">登录(login)</a>]操作; 如有问题请联系亿美相关人员指定Key值。',
		'nmem' => '', //会员提示
		'gray' => 1,
	),
	/*
	'emay' => array(
		'name' => '亿美(ws)',
		'unit' => '元', // 余额单位(元 或 条)
		'home' => 'http://www.emay.cn/', 
		'admin' => '', //http://sdkhttp.eucp.b2m.cn/sdk/SDKService
		'note' => '亿美软通接口(Services调用), 第一次使用时,需使用[<a href="include/sms/extra_act.php?act=login" target="_blank">登录(login)</a>]操作; 如有问题请联系亿美相关人员指定Key值。<br />',
		'nmem' => '', //会员提示
	),*/
	'dxqun' => array(
		'name' => '短信群',
		'unit' => '元', // 余额单位(元 或 条)
		'home' => 'http://www.dxqun.com/',
		'admin' => 'http://www.dxton.com/', //如无此项可不填
		'note' => 'HTTP发送，<span style="color:#F0F">严格按照短信提供商的[短信模版]内容发送，否则发不出去</span>；有疑问请先联络[短信提供商] 或 选用别的接口。',
		'nmem' => '', //会员提示
		'gray' => 1,
	),
	/* 没有给测试短信,先隐藏
	'eshang8' => array(
		'name' => 'E商网络',
		'unit' => '条', // 余额单位(元 或 条)
		'home' => 'http://www.eshang8.cn',
		'admin' => 'http://sms.eshang8.com/', //如无此项可不填
		'note' => '短信长度小于等于70个字符。',
		'nmem' => '短信长度小于等于70个字符。', //会员提示
	),
	//*/
	'0test' => array(
		'name' => '流程测试',
		'unit' => '条', // 余额单位(元 或 条)
		'home' => '', //如无此项可不填
		'admin' => '', //如无此项可不填
		'note' => '测试接口,用于测试系统其它流程,提供[充值<a href="include/sms/extra_act.php?act=chargeUp&charge=20" target="_blank">[+20</a>|<a href="include/sms/extra_act.php?act=chargeUp&charge=-20" target="_blank">-20]</a>操作]<br />具体操作不会发短信,仅写一个文件记录表示发短信; <br />',
		'nmem' => '测试接口,用于测试系统其它流程。', //会员提示
	),
);

// 固定configs

//如果不是以下数据，请在这里配置(未用)
//$sms_cfg_mchar = 70; // 一条信息,文字个数(小灵通65个字)
//$sms_cfg_mtels = 200; // 一次发送,最多200个手机号码个数

//----------------------------------------------------------------------
