<?php
/**
 * QQ登录认证类
 *
 * 如果想扩展功能直接在本类增加方法与SDK
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('OTHER_SITE_BIND_PATH') || die('Access forbidden!');
class qqAuth extends Auther {
    /**
     * 安装验证接口
     *
     * @since 1.0
     */
    public function Setup()
    {
        global $cms_abs, $type, $m_cookie;
        $url = $cms_abs . 'tools/other_site_sdk/qqcom/oauth/qq_callback.php?' . $_SERVER['QUERY_STRING'];
        if(!isset($_SESSION[otherSiteBind::$authfields[$type]])) {
            if( !isset($m_cookie['read_openid']) || $m_cookie['read_openid'] < 3 ) {
                if ( isset($m_cookie['read_openid']) )
                {
                    msetcookie('read_openid', ++$m_cookie['read_openid']);
                }
                else 
                {
                    msetcookie('read_openid', 1);
                }
            } else {
                msetcookie('read_openid', 0);
                $message = <<<HTML
                登录失败，请稍候再试。<br /><br />
                <!-- 
                    获取授权信息失败，请检查您的PHP环境:
                    1. SESSION是否可读写
                    2. php_openssl.dll和php_curl.dll扩展库是否已经开启！
                -->
HTML;
                cls_message::show($message);
            }
            // 不想在此关闭窗口，所以在这用这方法让qq_callback.php的<script>window.close();</script>失效
            echo '<script type="text/javascript" src="' . $url . '"></script>';
            exit ('<script type="text/javascript"> window.location.reload(); </script>');
        } else {
            $_SESSION[otherSiteBind::$authfields[$type]] = $_SESSION["openid"];
        }
    }

    /**
     * 获取用户名称
     *
     * @return string 要获取的用户名称
     * @since  1.0
     */
    public function getUserName()
    {
        global $mcharset;
        $info = & $this->getUserInfo();
        if(false === stripos($mcharset, 'UTF'))
        {
            return cls_string::iconv('UTF-8', $mcharset, $info['nickname']);
        }
        else
        {
            return $info['nickname'];
        }
    }

    /**
     * 获取用户头像
     *
     * figureurl_2为100*100，figureurl_1为50*50，figureurl_0为：20*20
     *
     * @return string 要获取的用户名称
     * @since  1.0
     */
    public function getUserAvatar( $figureurl = 'figureurl_2' )
    {
        $info = & $this->getUserInfo();
        return $info[$figureurl];
    }

    /**
     * 获取用户信息
     *
     * @return array 要获取的用户信息
     * @since  1.0
     */
    public function getUserInfo() {
        include_once OTHER_SITE_BIND_PATH . 'qqcom' . DS . 'comm' . DS . 'utils.php';
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
            . "access_token=" . $_SESSION['access_token']
            . "&oauth_consumer_key=" . $_SESSION["appid"]
            . "&openid=" . $_SESSION["openid"]
            . "&format=json";

        $info = get_url_contents($get_user_info);
        $arr = json_decode($info, true);

        return $arr;
    }
    
    /**
     * 发送授权回收
     * 
     * @todo 因QQ不需要发送，直接跳过就好
     */ 
    public function sendRevokeOAuth() {}
    
    /**
     * 返回回调地址
     * 
     * @param string 要返回的回调地址
     */    
    public function getCallBack()
    {
        parent::getQQAuthInstance();
        return parent::$_urls['qq'];
    }
}