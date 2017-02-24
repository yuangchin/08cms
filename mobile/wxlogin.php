<?php
// === 08初始化
define('IN_WEIXIN',TRUE);
define('IN_MOBILE', TRUE);
include dirname(dirname(__FILE__)).'/include/general.inc.php';
//08初始化
$db = _08_factory::getDBO(); 
//注意模版缓存，微信客户端过来，模版调试是关闭的...
if(cls_env::mconfig('weixin_debug')){
	@unlink(_08_TPL_CACHE."/common/3g_wxlogin.html.php");
}
//$v_device = '<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>';


// === 获取配置,wecfg为总站配置,wenow为商家或文档的配置
$mpaid = empty($mpaid) ? 0 : intval($mpaid);
$mpmid = empty($mpmid) ? 0 : intval($mpmid);
$scene = empty($scene) ? '' : $scene; 
$wecfg = cls_w08Basic::getConfig(); 
if(!empty($mpmid) || !empty($mpaid)){
	$key = $mpmid || $mpaid;
	$type = $mpmid ? 'mid' : 'aid';
	$wenow = cls_w08Basic::getConfig($key, $type); 
}else{
	$wenow = array();	
}


// === 验证code，codecheck
$code = empty($code) ? '' : $code; //echo ",$code,";
$state = empty($state) ? '' : $state;
$openid = empty($openid) ? '' : $openid; //'oyDK8vjjcn2cFbxMLaMBhKEsYbCk'; //
$codecheck = empty($codecheck) ? '' : $codecheck; 
$authkey = cls_env::mconfig('authkey'); 
$sflag = empty($sflag) ? '' : $sflag; //echo "$sflag";
// code作为换取access_token的票据，每次用户授权带上的code将不一样，code只能使用一次，5分钟未被使用自动过期。
if($code && empty($codecheck)){
	$oauth = new cls_wmpOauth($wecfg);
	$actoken = $oauth->getACToken($code); 
	if(!empty($actoken['errmsg'])){
		cls_message::show("[{$actoken['errcode']}]{$actoken['errmsg']}");
	} 
	$openid = $actoken['result']['openid'];
	$codecheck = md5($code.$openid.$authkey);
// 提交后用$codecheck认证信息
}elseif($code && !empty($codecheck) && !empty($openid)){
	// 防止恶意操作
	$codecfrom = md5($code.$openid.$authkey);
	if($codecfrom!==$codecheck){
		cls_message::show("验证失败，可能是重复提交！");	
	}
	// 防止重复操作(恶意)
	$row = $db->select()->from('#__members')->where(array('weixin_from_user_name'=>$openid))->exec()->fetch();
	if($row){ 
		cls_message::show("此微信号已经绑定会员！请直接登录！");	
	}
}else{ //避免后患，停止掉！(调试可屏蔽)
	cls_message::show("Error1: code=$code, state=$state, openid=$openid, codecheck=$codecheck");
}
if($scene && in_array($state,array('binding','dologin','dogetpw'))){
	if(!$db->select()->from("#__weixin_qrlimit")->where(array('sid'=>$scene,'sflag'=>$sflag))->_and(array('ctime'=>(TIMESTAMP-5*60)),'>')->exec()->fetch()){
		cls_message::show("超时了，请重新扫描！");	
	}
}
//$this->sflag = cls_string::Random(8);
//$db->update("#__$table", array('sflag'=>$this->sflag))->where(array('sid'=>$sid))->exec();
// print_r($actoken); 


// === 执行逻辑事务
/* 
 - 扫描过来 : 带 scene,openid, 注意处理scene
*/
// 扫描过来 : dogetpw
if($state=='dogetpw'){
	$mname = empty($mname) ? '' : $mname; //echo $mname;
	$msg = cls_w08User::resetPwd($openid,$scene,$mname);
	cls_message::show($msg);
// 扫描过来 : dologin
}elseif($state=='dologin'){
	$mid = empty($mid) ? '' : intval($mid);
	cls_w08User::setScanLogin($scene,$openid,$mid);
	$msg = "已经自动登录……，请留意屏幕跳转。";
	cls_message::show($msg); 
// 扫描过来 : binding 
}elseif($state=='binding'){ 
	;//这里为空,直接进入模版
// (点菜单,扫码,点链接过来) : 执行绑定
}elseif($state=='dobind'){ 
	$wmDefCfg = cls_cache::exRead('wxconfgs');
	$mhome_fid = $wmDefCfg['sys_confgs']['fids']['mhome'];
	$act08 = empty($act08) ? '' : $act08;
	$state_old = empty($state_old) ? '' : $state_old;
	$username = empty($username) ? '' : $username;
	$password = empty($password) ? '' : $password;
	$mchid = empty($mchid) ? 1 : $mchid; //echo ":$username,$password:";
	//1. 快速新增帐号
	if($act08=='add'){  
		$re = cls_w08User::addUser($openid,$username,$password,$mchid); 
		$msg = $re['msg']; //('mid'=>$mid, 'autocheck'=>$autocheck, 'msg'=>$msg);
		if($re['mid']){
			$msg .= "<br>您的登录帐号为：{$username}。";
			$msg .= "<br>您的登录密码为：{$password}。";
		} 
		$isok = $re['autocheck']==1 ? 1 : 0;
	//2. 绑定已有帐号
	}else{
		$re = cls_w08User::bindUser($openid,$username,$password);
		$msg = $re['msg']; //return array('res'=>$res, 'msg'=>$msg);
		$isok = $re['res'];
	}
	//3. 失败检查
	if(!$isok){
		cls_message::show("$msg, 请重新操作！");
	}
	$wmDefCfg = cls_cache::exRead('wxconfgs');
	$mhome_fid = $wmDefCfg['sys_confgs']['fids']['mhome'];
	$mhurl = is_numeric($mhome_fid) ? "info.php?fid=$mhome_fid" : "$mhome_fid";
	//4. 菜单过来,进入会员中心页
	if($state_old=='mlogin'){ 
		header("Location:{$cms_abs}".$mhurl);
	//5. 扫码过来,更新数据库+提示信息+进入会员中心链接
	}else{ 
		cls_w08User::setScanLogin($scene,$openid,$re['mid']); // 重置扫码
		$msg .= "<br>电脑版本已经自动登录……，请留意屏幕跳转。";
		$msg .= "<br>点击进入:手机版<a href='{$cms_abs}$mhurl'>用户中心</a> <br> ";
		cls_message::show($msg);
	}
// 菜单过来 : 登录
}elseif($state=='mlogin'){
	cls_w08User::chkLogin($openid,$state);
	//绑定了直接登录
	//未绑定,进入模版
// 未知授权过来 : 登录认证
}elseif(!empty($state)){
	cls_w08User::chkLogin($openid,$state);
}else{ //避免后患，停止掉！(调试可另处理)
	cls_message::show("Error2: code=$code, state=$state, openid=$openid, codecheck=$codecheck");
}


// === 获取用户信息
/*
 - 因为是管理多个公众号，用主站的公众号(snsapi_base)授权，获取openid
 - 但是(snsapi_base)授权不能获取用户详细信息，所以用各自的公众号获取用户信息
*/
$wucfg = empty($wenow) ? $wecfg : $wenow; 
$weuser = new cls_w08User($wucfg); 
$uinfo = $weuser->getUserInfo($openid); 
$mname08 = cls_w08UserBase::fmtUserName($uinfo);
$mpass08 = substr($mname08,0,3).'_'.cls_string::Random(5); 


// 08模版:
$forward = ''; 
$_da = array('forward'=>rawurlencode($forward));
$_kda = array('mpaid','mpmid','scene','code','openid','codecheck','uinfo','mname08','mpass08');
foreach($_kda as $k){
	$_da[$k] = isset($$k) ? $$k : '';
}
$_da['state_old'] = $state;
$_da['state'] = 'dobind'; //print_r($_da);
$tplname = cls_tpl::SpecialTplname('3g_wxlogin',1);
$html = cls_SpecialPage::Create(
	array(
		'tplname' => $tplname, '_da' => $_da,
		'LoadAdv' => true,
		'NodeMode' => defined('IN_MOBILE'),
	)
);
exit($html);
	
