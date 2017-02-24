<?php
/**
 * 其它网站绑定接口类，使用该接口时请尽量把SESSION保存到memcached里，效率会比较直接保存SESSION到文件好。
 *
 * 新增加接口只需在本类里添加一个方法即可，不想调用的方法请在第39行处添加列表中（注：类的内建方法必须排除掉，如：魔术方法）
 * 具体请看：http://php.net/manual/zh/language.oop5.magic.php
 * 注：新增的方法必须在结束前设置自身的授权URL赋给属性$_urls，下标名必须与JS函数OtherWebSiteLogin参数type一致
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
defined( 'OTHER_SITE_BIND_PATH' ) || define( 'OTHER_SITE_BIND_PATH', dirname(__FILE__) . DS );

class otherSiteBind {
    /**
     * 设置授权保存的SESSION键(注：该值必须与数据表members标志为该登录信息的字段名称一致)，
     * 例： array($type => $key); $type是GET传递过来的类型
     */
    public static $authfields = array('sina' => 'sina_uid', 'qq' => 'openid');
    
    /**
     * 存储外部网站授权URL
     *
     * @var    array
     * @static
     * @since  1.0
     */
    protected static $_urls = array();

    public function __construct() 
    {
        # 用手动调用是因为保证执行顺序问题
        self::checkAction();
        $reflection = new ReflectionClass('otherSiteBind');
        // 用反射API自动调用扩展方法（除了__construct和__toString方法）
        foreach($reflection->getMethods() as $k => $v)
        {
            // 排除不自动反射调用的方法，注：类的内建方法必须排除掉，如：魔术方法
            if(!in_array($v->name, array(__FUNCTION__, '__toString', 'checkAction')) && $reflection->hasMethod($v->name)) {
                $function = $reflection->getMethod($v->name);
                $function->invoke(null);
            }
        }
    }
    
    /**
     * 验证参数
     */ 
    public static function checkAction()
    {
        global $type;
        // 限制$type的值必须在otherSiteBind::$authfields的键中
        if(!empty($type) && !key_exists($type, self::$authfields) && isset($_SERVER['PHP_SELF']) )
        {
            $basename = basename(dirname(__FILE__));
            // 只过滤当前目录下的文件
            if(false !== stripos($_SERVER['PHP_SELF'], $basename))
            {
                die('非法参数!');
            }
        }
    }

    /**
     * 获取QQ登录授权URL
     *
     * @static
     * @since  1.0
     */
    public static function getQQAuthInstance()
    {
        global $cms_abs, $qq_appid, $qq_appkey, $qq_closed, $mcharset, $cms_top, $dbhost, $dbname, $dbpw, $dbuser;
        include_once( OTHER_SITE_BIND_PATH . 'qqcom' . DS . 'Config.php' );
        // 当该登录功能关闭时设置URL为close字样
        if( $qq_closed || empty($qq_appid) || empty($qq_appkey) ) return self::$_urls['qq'] = 'close';
        include_once( OTHER_SITE_BIND_PATH . 'qqcom' . DS . 'comm' . DS . 'config.php' );
        empty(self::$_urls['qq']) && self::$_urls['qq'] = $cms_abs . "tools/other_site_sdk/qqcom/oauth/qq_login.php";
        self::$_urls['qq_reauth'] = "{$cms_abs}tools/other_site_sdk/other_site_public_callback.php?type=qq&act=qq_reauth&target=" . urlencode(self::$_urls['qq']);
    }

    /**
     * 获取新浪SDK授权入口对象句柄与授权URL
     *
     * @static
     * @since  1.0
     */
    public static function getSinaWeiBoAuthInstance() 
    {
        global $cms_abs, $sina_appid, $sina_appkey, $mcharset, $sina_closed;
        // 当该登录功能关闭时设置URL为close字样
        if( $sina_closed || empty($sina_appid) || empty($sina_appkey) ) return self::$_urls['sina'] = 'close';
        include_once( OTHER_SITE_BIND_PATH . 'weibocom' . DS . 'config.php' );
        require_once( OTHER_SITE_BIND_PATH . 'weibocom' . DS . 'extends_saetv2.ex.class.php' );
        $o = new extendsSaeTOAuthV2( WB_AKEY , WB_SKEY );
        empty(self::$_urls['sina']) && self::$_urls['sina'] = $o->getAuthorizeURL( WB_CALLBACK_URL );
        self::$_urls['sina_reauth'] = "{$cms_abs}tools/other_site_sdk/other_site_public_callback.php?type=sina&act=sina_reauth&target=" . urlencode(WB_CALLBACK_URL);
        return $o;
    }

    /**
     * 获取QQ微博登录授权URL
     *
     * @static
     * @since  1.0
    public static function getQQWeiboAuthInstance() {
        global $cms_abs, $qq_appid, $qq_appkey, $qq_closed, $mcharset;
        // 当该登录功能关闭时设置URL为close字样
        if( $qq_closed || empty($qq_appid) || empty($qq_appkey) ) return self::$_urls['qq'] = 'close';
        include_once( OTHER_SITE_BIND_PATH . 'qqcom' . DS . 'Config.php' );
        require_once( OTHER_SITE_BIND_PATH . 'qqcom' . DS . 'Tencent.php' );
        OAuth::init($client_id, $client_secret);
        Tencent::$debug = $debug;
        empty(self::$_urls['qq_weibo']) && self::$_urls['qq_weibo'] = OAuth::getAuthorizeURL( CALLBACK );
    }
     */

    public function __toString() 
    {
        return json_encode(self::$_urls);
    }
}