<?php
/**
 * 新浪微博认证类
 *
 * 如果想扩展微博功能直接在本类增加方法与SDK
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('OTHER_SITE_BIND_PATH') || die('Access forbidden!');
class sinaAuth extends Auther {
    protected static $_sae_instance = null;
    /**
     * 保存授权信息
     *
     * @var    array
     * @static
     * @since  1.0
     */
    private static $_token = array();

    /**
     * 获取用户名称
     *
     * @return string 要获取的用户名称
     * @since  1.0
     */
    public function getUserName()
    {
        $info = & $this->getUserInfo();
        return isset($info['name']) ? $info['name'] : '';
    }

    /**
     * 获取用户头像
     *
     * @return string 返回用户头像URL
     * @since  1.0
     */
    public function getUserAvatar() 
    {
        $info = & $this->getUserInfo();
        return isset($info['avatar_large']) ? $info['avatar_large'] : '';
    }

    /**
     * 获取用户信息
     *
     * @return array 要获取的用户信息
     * @since  1.0
     */
    public function getUserInfo() 
    {
        if(!isset($_SESSION['token']['uid'])) return array();
        return self::getClientInstance()->show_user_by_id($_SESSION['token']['uid']);
    }
    
    /**
     * 发送授权回收
     */ 
    public function sendRevokeOAuth()
    {
        if ( empty($_SESSION['token']) )
        {
            return false;
        }
        
        return self::getClientInstance()->revokeoauth2($_SESSION['token']);
    }

    /**
     * 安装微博验证
     *
     * @since 1.0
     */
    public function Setup() 
    {
        global $code, $type, $sina_closed;
        empty($sina_closed) || cls_message::show('新浪微博登录功能已经关闭！');
        $o = & parent::getSinaWeiBoAuthInstance();
        if (!empty($code)) 
        {
        	$keys = array();
        	$keys['code'] = $code;
        	$keys['redirect_uri'] = WB_CALLBACK_URL;
        	try {
        	    if(empty($_SESSION['token'])) 
                {
                    self::$_token = $o->getAccessToken( 'code', $keys ) ;
            	    $data = $o->parseSignedRequest(self::$_token['access_token']);
                    ($data == -2) && cls_message::show('签名错误！');
                    // 授权完成
                    if (!empty(self::$_token)) {
                    	$_SESSION[otherSiteBind::$authfields[$type]] = self::$_token['uid'];
                    	$_SESSION['token'] = self::$_token;
                    }
                }
        	} catch (OAuthException $e) {
        	    die('error: ' . $e->getMessage());
        	}
        }
    }
    
    /**
     * 返回回调地址
     * 
     * @param string 要返回的回调地址
     */  
    public function getCallBack()
    {
        parent::getSinaWeiBoAuthInstance();
        return parent::$_urls['sina'];
    }

    /**
     * 获取操作微博信息的对象
     *
     * @return object self::$_sae_instance 返回操作微博信息的对象
     * @since  1.0
     */
    public static function getClientInstance() 
    {
        global $cms_abs, $sina_appid, $sina_appkey, $mcharset;
        include_once( OTHER_SITE_BIND_PATH . 'weibocom' . DS . 'config.php' );
        require_once( OTHER_SITE_BIND_PATH . 'weibocom' . DS . 'saetv2.ex.class.php' );
        if(!(self::$_sae_instance instanceof SaeTClientV2)) 
        {
            self::$_sae_instance = new SaeTClientV2(WB_AKEY, WB_SKEY, $_SESSION['token']['access_token']);
        }
        return self::$_sae_instance;
    }
}