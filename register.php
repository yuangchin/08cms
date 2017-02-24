<?php
defined('M_UPSEN') || define('M_UPSEN', TRUE);
defined('NOROBOT') || define('NOROBOT', TRUE);
include_once dirname(__FILE__).'/include/general.inc.php';
if($ex = exentry('register')){include($ex);mexit();}
//以上四行在一起，扩展时将这四行一起去掉或替换掉。
//$mobiledir = cls_env::mconfig('mobiledir'); //整到类中去了就要这一句
_08_FilesystemFile::filterFileParam($mobiledir); 
$ismobiledir = defined('IN_MOBILE') ? "$mobiledir/" : ''; //echo $ismobiledir; //处理手机版地址
foreach(array('mchannels','catalogs','cotypes','mtconfigs',) as $k) $$k = cls_cache::Read($k);

$forward = empty($forward) ? M_REFERER : $forward;

cls_env::CheckSiteClosed();//关闭站点时不能关闭ajax校验
if(empty($forward))$forward = $cms_abs;
$forwardstr = "forward=".urlencode($forward);
$curuser->info['mid'] && cls_message::show('请不要重复注册 [<a href="login.php?action=logout">退出</a>]', '');
$registerclosed && cls_message::show(empty($regclosedreason) ? '本站暂时关闭注册新会员。' : mnl2br($regclosedreason));
$mchid = empty($mchid) ? 1 : max(1,intval($mchid));
if(!($mchannel = cls_cache::Read('mchannel',$mchid))) cls_message::show('请指定正确的会员类型');
$mfields = cls_cache::Read('mfields',$mchid);
$grouptypes = cls_cache::Read('grouptypes');
$sms = new cls_sms();

if(!submitcheck('register')){
	if(defined('IN_MOBILE')){ //手机版模板
		$tplname = cls_tpl::SpecialTplname('register',defined('IN_MOBILE'));
	}else{ //网页版模板
		$tplname = cls_tpl::CommonTplname('member',$mchid,'addtpl');
	} //$tplname = '';
	if(!$tplname){
		include_once M_ROOT.'include/adminm.fun.php';
		_header('注册');
		$mchannel = cls_cache::Read('mchannel',$mchid);
	
		tabheader('注册新会员','cmsregister',"?mchid=$mchid&forward=".rawurlencode($forward),2,1,1);
		$muststr = '<span style="color:red">*</span>';
		trbasic($muststr.'会员名称','mname', '', 'text', array('validate' => ' rule="text" must="1" min="3" max="15"'));
		trbasic($muststr.'密码','password','','password', array('validate' => ' rule="text" must="1"'));
		trbasic($muststr.'重复密码','password2','','password', array('validate' => ' rule="comp" must="1" vid="password"'));
		trbasic($muststr.'E-mail','email', '', 'text', array('validate' => ' rule="email" must="1" rev="E-mail"'));
		tr_regcode('register');
		echo '<script type="text/javascript">window._08cms_validator && _08cms_validator.attribute("mname","warn","用户名应为3-15个字节！").init("ajax","mname",{cache:1,url:"'.$cms_abs . _08_Http_Request::uri2MVC('ajax=Check_Member_Info_Base&filed=mname&val=%1').'"}).attribute("password","warn","密码空或包含非法字符（不能用全0作密码）！").attribute("password2","init","请先输入密码！").attribute("password2","comp","请输入确认密码！").attribute("password2","warn","两次输入的密码不一致！").init("ajax","email",{cache:1,url:"'.$cms_abs . _08_Http_Request::uri2MVC('ajax=Check_Member_Info_Base&filed=email&val=%1').'"});</script>';
		foreach($grouptypes as $k => $v){
			if(!$v['mode'] && !in_array($mchid,explode(',',$v['mchids']))){
				trbasic($v['cname'],'grouptype'.$k,makeoption(ugidsarr($k,$mchid)),'select');
			}
		}
		$a_field = new cls_field;
		foreach($mfields as $k => $v){
			if(in_array($v['datatype'],array('image','images','flash','flashs','media','medias','file','files')))continue;
			if($v['available'] && !$v['issystem']){
				$a_field->init($v);
				$a_field->isadd = 1;
				$a_field->trfield();
			}
		}
		tabfooter('register','注册');
		_footer();
	}else{//定制的模板

		$html = cls_SpecialPage::Create(
			array(
				'tplname' => $tplname,
				'_da' => array('mchid' => $mchid,'forward' => rawurlencode($forward),),
				'LoadAdv' => true,
				'NodeMode' => defined('IN_MOBILE'),
			)
		);
		exit($html);
	}
}else{
    if($sms->smsEnable('register')){ //??手机版排除
        $msgcode = cls_env::GetG('msgcode');
		$smstelfield = cls_env::GetG('smstelfield'); 
		$smstelval = cls_env::GetG($smstelfield); 
		if(!$pass=smscode_pass('register',$msgcode,$smstelval)) cls_message::show('手机确认码有误', M_REFERER);
		//会员认证-会员手机认证:强制认证(v)
		//审核-自动审核(x)
    }else{
        //验证码
        if(!regcode_pass('register',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码输入错误！',M_REFERER);
		$smstelfield = '';
		$smstelval = '';
    }
	//预处理会员帐号、密码、Email
	foreach(array('mname','password','email') as $key){
		$re = cls_userinfo::CheckSysField(@$$key,$key,'add');
		if($re['error']){
			cls_message::show($re['error'], M_REFERER);
		}else $$key = $re['value'];
	}
	$password2 = trim($password2);
	if($password != $password2) cls_message::show('两次输入密码不一致',M_REFERER);
	
	//UC会员同步注册
	$uc_uid = cls_ucenter::register($mname,$password,$email,TRUE);
      
      # 同步注册通行证
	$pw_uid = cls_WindID_Send::getInstance()->synRegister( $mname, $password, $email, $onlineip );
      
	$a_field = new cls_field;
	foreach($mfields as $k => $v){
		if(in_array($v['datatype'],array('image','images','flash','flashs','media','medias','file','files')))continue;
		if(!$v['issystem'] && isset($$k)){
			$a_field->init($v);
			$$k = $a_field->deal('','message',M_REFERER);
		}
	}
	unset($a_field);
	
	$newuser = new cls_userinfo;
	if($mid = $newuser->useradd($mname,_08_Encryption::password($password),$email,$mchid)){
		//会员可以自行手动选择的会员组设置
		foreach($grouptypes as $k => $v){
			if(!$v['mode'] && isset(${"grouptype$k"})){
				$newuser->updatefield("grouptype$k",${"grouptype$k"});
			}
		}
		foreach($mfields as $k => $v){
			if(in_array($v['datatype'],array('image','images','flash','flashs','media','medias','file','files')))continue;
			if(!$v['issystem'] && isset($$k)){
				$newuser->updatefield($k,$$k,$v['tbl']);
				if($arr = multi_val_arr($$k,$v)) foreach($arr as $x => $y) $newuser->updatefield($k.'_'.$x,$y,$v['tbl']);
			}
		}
		//reg_ex1(); //扩展;
		$autocheck = $mchannel['autocheck'];
          
          # 当本系统注册用户成功后保存PW用户ID到本系统
          empty($pw_uid) || $newuser->updatefield(cls_Windid_Message::PW_UID, $pw_uid);
          # 当本系统注册用户成功后保存UC用户ID到本系统
          empty($uc_uid) || $newuser->updatefield(cls_ucenter::UC_UID, $uc_uid);
		
		$newuser->check($autocheck);
		if($autocheck == 1){
			$newuser->OneLoginRecord();
		}elseif($autocheck == 2){
			cls_userinfo::SendActiveEmail($newuser->info);
		}
		$newuser->updatedb();
		if($autocheck == 1) $newuser->autopush(); //自动推送
		if($smstelfield && $smstelval){ //开启注册手机短信认证码(则自动认证:会员认证-手机认证)
			$newuser->automcert($smstelfield,$smstelval);
		}
		if(!$forward || preg_match('/\bregister\.php(\?|#|$)/i', $forward)) $forward = $cms_abs.$ismobiledir;
		if($autocheck == 1 && !defined('IN_MOBILE')) $forward = $cms_abs.$ismobiledir.'adminm.php'; //网页版才有会员中心		
		cls_message::show(!$autocheck ? '用户等待审核' : ($autocheck == 2 ? '会员激活邮件已发送到您的邮箱，请进入邮箱激活' : '会员注册成功。'),$forward);
	}else cls_message::show('会员注册失败，请重新注册。',"?mchid=$mchid&forward=".rawurlencode($forward));
}
