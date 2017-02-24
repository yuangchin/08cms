<?php

// 参考admina.php, 加载file,cache;
define('M_ADMIN', TRUE);
define('NOROBOT', TRUE);
define('M_UPSEN', TRUE);
include_once dirname(dirname(__FILE__)).'/general.inc.php';
include_once M_ROOT.'include/admina.fun.php';

// 有相关权限者才可执行此操作
if($re = $curuser->NoBackFunc('smsapi')) cls_message::show($re);
//print_r($a_funcs);

// sms-api-load
// $class = "sms_$sms_cfg_api";
$smsdo = new cls_sms();
$baseurl = $smsdo->smsdo->baseurl;

// 登陆注册操作-仅此emay+http接口有此操作
if($sms_cfg_api=='emhttp' && $act=='login'){
		aheader(); 
		$userid = $smsdo->smsdo->userid;
		$userpw = $smsdo->smsdo->userpw;
		echo "<div class='mainBox'><div class='itemtitle'><h3>亿美(http)接口 : 登录注册页(仅第一次使用需要此操作)</h3></div></div>";
		tabheader(' 第一步：序列号注册','fm_smsreg',"{$baseurl}registdetailinfo.action",2,0,1);
		trbasic('点击右边 序列号注册','',"<a href='{$baseurl}regist.action?cdkey=$userid&password=$userpw' target='_blank'>点此完成[序列号注册]</a>",'');
		tabfooter();
		
		tabheader(' 第二步：企业信息注册'); //  rule="text" must="1" mode="" regx="" min="3" max="50" rev="联系人">
		trbasic('短信序列号','cdkey'    ,$userid ,'text', array('w'=>30,'guide'=>'请从短信供应商获取!'      ,'validate'=>' rule="text" must="1" regx="" min="6" max="24" '));
		trbasic('序列号密码','password' ,$userpw ,'text', array('w'=>30,'guide'=>'请从短信供应商获取!'      ,'validate'=>' rule="text" must="1" regx="" min="4" max="12" '));
		trbasic('企业名称'  ,'ename'    ,''      ,'text', array('w'=>50,'guide'=>'(最多60字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="2" max="60" '));
		trbasic('联系人姓名','linkman'  ,''      ,'text', array(        'guide'=>'(最多20字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="2" max="20" '));
		trbasic('联系电话'  ,'phonenum' ,''      ,'text', array(        'guide'=>'(最多20字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="2" max="20" '));
		trbasic('联系手机'  ,'mobile'   ,''      ,'text', array(        'guide'=>'(最多15字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="2" max="15" '));
		trbasic('联系传真'  ,'fax'      ,''      ,'text', array(        'guide'=>'(最多20字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="2" max="20" '));
		trbasic('电子邮件'  ,'email'    ,''      ,'text', array('w'=>50,'guide'=>'(最多60字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="2" max="60" '));
		trbasic('公司地址'  ,'address'  ,''      ,'text', array('w'=>50,'guide'=>'(最多60字节)，必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="6" max="60" '));
		trbasic('邮政编码'  ,'postcode' ,''      ,'text', array(        'guide'=>'(最多6字节)， 必须输入！' ,'validate'=>' rule="text" must="1" regx="" min="6" max="6"  '));
		tabfooter('btn_smsreg');
		
}


// 登陆操作-仅此emay接口有此操作
if($sms_cfg_api=='emay' && $act=='login'){
	$msg = $smsdo->smsdo->login();
	if($msg[0]=='1'){
		echo "登陆成功！<br>详情如下：";
	}else{
		echo "操作错误！<br>详情如下：";
	}
	print_r($msg);	
}
/*
 因 亿美软通 接口提供方，比较频繁的login,logout操作，会造成sessionKey不能使用，或只是第一次生成的sessionKey有效；
 所以这里只做一次login，后续其它login,logout操作，请联系亿美相关人员。
*/
// 注销操作-仅此emay接口有此操作
if($sms_cfg_api=='emay' && $act=='logout'){
	die('更多登陆/注销操作，请联系亿美相关人员。'); //[<a href="include/sms/extra_act.php?act=logout" target="_blank">注销(logout)</a>]操作后,不能再发短信了,除非再进行login(登录)
	$msg = $smsdo->smsdo->logout();
	if($msg[0]=='1'){
		echo "注销成功！<br>详情如下：";
	}else{
		echo "操作错误！<br>详情如下：";
	}
	print_r($msg);	
}
// 充值-主要是测试api使用
if($sms_cfg_api=='0test' && $act=='chargeUp'){
	if(!empty($charge)){
		$msg = $smsdo->chargeUp($charge);
		if($msg[0]=='1'){
			echo "充值成功！<br>详情如下：";
		}else{
			echo "操作错误！<br>详情如下：";
		}
		print_r($msg);	
	}
}

?>
