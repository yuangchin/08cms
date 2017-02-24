<?php
/**
 * 微信事件响应管理扩展模型(接收事件推送)
 * {@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%8E%A5%E6%94%B6%E4%BA%8B%E4%BB%B6%E6%8E%A8%E9%80%81}
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Event extends _08_M_Weixin_Message
{
    /**
     * 取消关注事件
     * 用户取消关注公众号事，微信会把这个事件推送到开发者填写的URL。方便开发者给用户下发欢迎消息或者做帐号的解绑。 
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  nv50
     */
    public function responseUnsubscribe()
    {     
    }
    
    /**
     * 响应关注/扫描带参数二维码事件
     * 用户在关注公众号事，微信会把这个事件推送到开发者填写的URL。方便开发者给用户下发欢迎消息或者做帐号的绑定。 
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  nv50
     */
    public function responseSubscribe()
    {
        # 扫描带参数二维码事件
//        if ( isset($this->_post->Ticket) && strtoupper($this->_post->Ticket) == 'TICKET' )
//        {
//            return $this->_ReplyText( "扫描带参数二维码事件" );
//        }

        return $this->_ReplyText( "您好，欢迎您关注 {$this->_mconfigs['hostname']}。" );
    }
    
    /**
     * 用户已关注时的事件推送
     * 目前支持登录与注册事件，$eventKey为1时则登录，为2时则注册。
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  nv50
     */
    public function responseScan()
    {
		global $onlineip;
        $cms_abs = _08_CMS_ABS;
        $time = TIMESTAMP;
        $weixin_token = _08_Encryption::password($this->_mconfigs['weixin_token']);
        $hash = cls_env::getHashValue();
		$eventKey = intval($this->_post->EventKey); //二维码场景ID
		$fromName = $this->_post->FromUserName; //微信open_id(用户ID)
		$db = _08_factory::getDBO();
		if (empty($this->_mconfigs['weixin_login_register'])){                           
            return $this->_ReplyText( '抱歉，该功能暂时未开启。' );
        }
		$token = _08_Encryption::getInstance("$fromName,{$weixin_token},{$time},{$hash}")->enCryption();
		$urlLoin = "{$cms_abs}login.php?action=weixin_login&is_weixin=1&token={$token}&scene_id=$eventKey";
		$message = "<a href=\"$urlLoin\">点击立即登录{$this->_mconfigs['hostname']}</a>";
		$row = $db->select('weixin_from_user_name')->from('#__members')->where(array('weixin_from_user_name'=>$fromName))->exec()->fetch();
		if($eventKey==parent::SCENE_ID_REGISTER && empty($row)){ //未注册,扫描注册
			$username = $this->setUserName();
			$password = cls_string::Random(6);
			$email = ($username . '@' . @$this->_mconfigs['cms_top']);  
			$newuser = new cls_userinfo;
			$mchid = $autocheck = 1;
			if($mid = $newuser->useradd($username,_08_Encryption::password($password),$email, $mchid)){
				//UC会员同步注册
				$_ucid = cls_ucenter::register($username,$password,$email,TRUE);
				# 同步注册通行证
				$pw_uid = cls_WindID_Send::getInstance()->synRegister($username, $password, $email, $onlineip);				
				$newuser->updatefield('weixin_from_user_name', $fromName);
				$newuser->check($autocheck);
				$newuser->updatedb();
				$_message = "{$this->_mconfigs['hostname']}\n欢迎您成功注册账户：\n用户名: {$username} \n密码: {$password} \n";
				$message = "$_message 此账户已经与微信账户成功绑定，并支持微信扫描登录。\n $message ";
			}else{
				$message = "[{$username}] 注册失败，请稍候重试。";
			}
		}elseif($eventKey==parent::SCENE_ID_REGISTER){ //已经注册,扫描注册
			$message = "您已经扫描注册过，请直接扫描登录！";
		}elseif($eventKey && $row){ //已经注册,扫描登录
			//;
		}elseif($eventKey){ //未注册,扫描登录(执行绑定)
			$mobiledir = cls_env::mconfig('mobiledir');
			$urlLoin = "{$cms_abs}$mobiledir/login.php?action=login&weixinid=$fromName&scene_id=$eventKey"; //&token={$token}&
			$message = "您还未用微信扫描注册；<a href=\"$urlLoin\">点击转到手机版登录{$this->_mconfigs['hostname']}</a>"; 
			// ??? 后续看怎样登录同时绑定微信号
		}
        return $this->_ReplyText( $message );
    }
    
    /**
     * 响应上报地理位置事件
     * 用户同意上报地理位置后，每次进入公众号会话时，都会在进入时上报地理位置，或在进入会话后每5秒上报一次地理位置，
     * 公众号可以在公众平台网站中修改以上设置。上报地理位置时，微信会将上报地理位置事件推送到开发者填写的URL。 
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  nv50
     */
    public function responseLocation()
    {
        if ( isset($this->_post->FromUserName) && (isset($this->_post->Event) && (strtoupper($this->_post->Event) == 'LOCATION')) )
        {            
            $datas = _08_M_Weixin_Base::getConfigs( $this->_post );
            $datas['FromUserName'] = (string) $this->_post->FromUserName;
            # 地理位置纬度
            isset($this->_post->Latitude) && $datas['Latitude'] = floatval($this->_post->Latitude);
            # 地理位置经度
            isset($this->_post->Longitude) && $datas['Longitude'] = floatval($this->_post->Longitude);
            # 地理位置精度
            isset($this->_post->Precision) && $datas['Precision'] = floatval($this->_post->Precision);          
            _08_M_Weixin_Base::setConfigs( $datas, $this->_post );  
        }
    }
    
    /**
     * 响应点击事件（即根据用户点击的按钮响应相应的回复）
     * 用户点击自定义菜单后，如果菜单按钮设置为click类型，则微信会把此次点击事件推送给开发者，注意view类型（跳转到URL）的菜单点击不会上报。 
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  nv50
     */
    public function responseClick()
    {        
        if ( isset($this->_post->EventKey) )
        {
            $method = strtolower($this->_post->EventKey);
            $Weixin_Extends_Event_Click = parent::getModels('Weixin_Extends_Event_Click', $this->_post);
            if ( method_exists($Weixin_Extends_Event_Click, $method) )
            {
                return call_user_func(array($Weixin_Extends_Event_Click, $method));
            }
        }
    }
	
    /**
     * 自动设置登录用户名
     * 
     * @return string $username 返回一个可用的用户名
     * @since  nv50
     */
    function setUserName(){  
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$fromName = $this->_post->FromUserName; //微信open_id(用户ID)
		$_08_M_Weixin_Users = _08_factory::getInstance('_08_M_Weixin_Users', $this->_post);
		$weixin_userinfo = $_08_M_Weixin_Users->getUserInfo($fromName);
		$weixin_userinfo = cls_string::iconv('utf-8',$mcharset,$weixin_userinfo);
		$weixin_userinfo = _08_Documents_JSON::decode($weixin_userinfo);
		if(!empty($weixin_userinfo['nickname'])){
			$username = $weixin_userinfo['nickname'];
		}else{
			$username = cls_string::Random(8);
		}
		$username || $username = cls_string::Random(8);
		$username = cls_string::ParamFormat(cls_string::Pinyin($username));
		if(strlen($username)>15) $username = substr($username,0,15);
		while(cls_userinfo::checkUserName($username)){
			$username = substr($username,0,9).'_'.cls_string::Random(3);
		}
		return $username;
	}
	
}

//$ustr = "\n$username,$password,$email,$mchid,".$fromName.",$autocheck\n";
//$umsg = cls_outbug::fmtArr($this->_post);
//cls_outbug::main("_08_M_Weixin_Event::Scan-27a".$ustr.$umsg,'','wetest/log_'.date('Y_md').'.log',1);
