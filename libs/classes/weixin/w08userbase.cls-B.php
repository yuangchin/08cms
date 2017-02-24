<?php
// 用户相关操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08UserBase extends cls_wmpUser{

	public $_db = NULL;

	function __construct($cfg=array()){
		parent::__construct($cfg);
		$this->_db = _08_factory::getDBO();
	}
	
	//得到一个可用的08cms用户名
    static function fmtUserName($user=''){  
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		if(is_array($user) && !empty($user['nickname'])){
			$username = $user['nickname'];
		}elseif(is_object($user) && !empty($user->nickname)){
			$username = cls_string::iconv('utf-8',$mcharset,$user->nickname);
		}else{
			$username = cls_string::Random(8);
		}
		$username = cls_string::ParamFormat(cls_string::Pinyin($username));
		if(strlen($username)>15) $username = substr($username,0,15);
		//$re = array('value' => $value,'error' => '');
		while(self::fmtUserCheck($username,'mname','add')){
			$username = substr($username,0,9).'_'.cls_string::Random(3);
		}
		return $username;
	}
    static function fmtUserCheck($username=''){ 
		$re = cls_userinfo::CheckSysField($username,'mname','add');
		return $re['error'];
	}

	// 授权链接,登录认证
    static function chkLoginBase($openid,$state='mlogin'){ 
		$wmDefCfg = cls_cache::exRead('wxconfgs');
		$mhome_fid = $wmDefCfg['sys_confgs']['fids']['mhome'];
		$mhurl = is_numeric($mhome_fid) ? "info.php?fid=$mhome_fid" : "$mhome_fid";
		$openid = empty($openid) ? '(is_null_openid)' : $openid; //某种情况下,显示未关注出现openid为空的情况
		$uinfo = self::setLoginLogger($openid);
		$cms_abs = cls_env::mconfig('cms_abs');
		//绑定了直接登录
		if(!empty($uinfo) && $state=='mlogin'){ 
			header("Location:{$cms_abs}".$mhurl);
		}elseif(!empty($uinfo) && isset($wmDefCfg['sys_confgs']['fids'][$state])){
			$mhome_fid = $wmDefCfg['sys_confgs']['fids'][$state];
			$mhurl = is_numeric($mhome_fid) ? "info.php?fid=$mhome_fid" : "$mhome_fid";
			header("Location:{$cms_abs}".$mhurl);
		//}else{
			//return $state; //返回用于扩展
		}
	}
	// 设置录登录状态
    static function setLoginLogger($openid=''){ 
		$db = _08_factory::getDBO();
		$wuser = new cls_userinfo(); 
		$row = $db->select()->from('#__members')->where(array('weixin_from_user_name'=>$openid))->exec()->fetch();
		if($row){ //cls_message::show(" .... 完善跳转 ... 绑定了直接登录");	
			$wuser = new cls_userinfo(); //用不用$curuser???
			$wuser->activeuser($row['mid']);
			$wuser->OneLoginRecord();
		}
		return $wuser->info;
		//未绑定,进入模版
	}
	// 设置扫描登录完成
    static function setScanLogin($scene,$openid='',$mid=0){ 
		$db = _08_factory::getDBO();
		self::setLoginLogger($openid); //'extp'=>$mid, $openid.$mid
		$db->update("#__weixin_qrlimit", array('stat'=>'LoginOK','openid'=>"$mid"))->where(array('sid'=>$scene))->exec();
	}
	
	// 添加用户
    static function addUser($openid,$mname,$password,$mchid=1){ 
		//预处理会员帐号、密码
		foreach(array('mname','password') as $key){
			$re = cls_userinfo::CheckSysField($$key,$key,'add');
			if($re['error']){
				return array('mid'=>0, 'autocheck'=>0, 'msg'=>$re['error']);
			}else $$key = $re['value'];
		}
		//绑定...
		$newuser = new cls_userinfo;
		$email = ($mname . '@' . cls_env::mconfig('cms_top'));
		if($mid = $newuser->useradd($mname,_08_Encryption::password($password),$email,$mchid)){
			//会员可以自行手动选择的会员组设置
			foreach($grouptypes as $k => $v){
				if(!$v['mode'] && isset(${"grouptype$k"})){
					$newuser->updatefield("grouptype$k",${"grouptype$k"});
				}
			}
			//reg_ex1(); //扩展;
			$mchannel = cls_cache::Read('mchannel',$mchid);
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
			$newuser->updatefield('weixin_from_user_name',$openid); //关联微信用户
			$newuser->updatedb();
			$msg = !$autocheck ? '用户等待审核' : ($autocheck == 2 ? '会员激活邮件已发送到您的邮箱，请进入邮箱激活' : '会员注册成功。');
		}else{
			$mid = 0;
			$autocheck = 0;
			$msg = "会员注册失败，请重新注册。";
		}
		return array('mid'=>$mid, 'autocheck'=>$autocheck, 'msg'=>$msg);
	}
	
	//绑定用户
    static function bindUser($openid,$mname,$password){  
		if(empty($mname)) die('错误:'.__FUNCTION__); //原则上没有这个情况
		$db = _08_factory::getDBO();
		$md5_password = _08_Encryption::password($password);
		$row = $db->select()->from('#__members')->where(array('mname'=>$mname))->_and(array('password'=>$md5_password))->exec()->fetch();
		if(!$row){  
		   $mid = 0;
		   $msg = '密码错误'; //cls_message::show('密码错误',axaction(1,M_REFERER));
		   $res = 0;
		}else{
			$db->update('#__members', array('weixin_from_user_name'=>$openid))->where(array('mname'=>$mname))->exec();
			$mid = $row['mid'];
			$msg = '绑定成功';
			$res = 1;
			$newuser = new cls_userinfo;
			$newuser->activeuser($mid);
			$newuser->OneLoginRecord();
		}
		return array('mid'=>$mid, 'res'=>$res, 'msg'=>$msg);
	}
	
	// 扫描过来 : setPwd
    static function resetPwd($openid,$scene,$mname){ 
		if(empty($openid)) die('错误:'.__FUNCTION__); //原则上没有这个情况
		$db = _08_factory::getDBO();
		$mname = cls_w08Basic::iconv('utf-8',cls_env::getBaseIncConfigs('mcharset'),$mname);
		$org_password = cls_string::Random(6,1);
		$md5_password = _08_Encryption::password($org_password);
		$db->update('#__members', array('password'=>$md5_password))->where(array('weixin_from_user_name'=>$openid))->exec();
		$db->update("#__weixin_qrlimit", array('ctime'=>0))->where(array('sid'=>$scene))->exec();
		$msg = "您的登录帐号为：{$mname}。<br>";
		$msg .= "您的密码重置为：{$org_password}。";
		return $msg;
	}

}
