<?php
/**
 * 其它网站绑定回调类，利用该接口与本程序结合
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
isset($_GET['type']) || die('No Permission');
include dirname(dirname(dirname(__FILE__))) . DS . 'include' . DS . 'general.inc.php';
defined( 'OTHER_SITE_BIND_PATH' ) || define( 'OTHER_SITE_BIND_PATH', dirname(__FILE__) . DS );
require_once OTHER_SITE_BIND_PATH . '08cms_bind_interface.php';
# 验证请求
otherSiteBind::checkAction();
/**
 * 强制其它网站接口必须继承该抽象类和定义抽象方法
 */
abstract class Auther extends otherSiteBind
{
    /**
     * 获取外站用户名称
     *
     * 如果不想在注册绑定或登录绑定时自动显示用户名时，只在子类里定义函数即可
     *
     * @return string 要获取的用户名称
     * @since  1.0
     */
    abstract public function getUserName();

    /**
     * 安装脚本
     *
     * @since 1.0
     */
    abstract public function Setup();

    /**
     * 获取用户头像
     *
     * 如果不想获取该信息时，只在子类里定义函数即可
     *
     * @return string 返回用户头像URL
     * @since  1.0
     */
    abstract public function getUserAvatar();
}

/**
 * 其它授权验证工厂类
 */
class otherAuthFactory
{
    private static $_instance = null;

    /**
     * 创建工厂对象
     *
     * @param  string $type   登录类型，即请求授权后返回该页面地址所带的URL参数
     * @return object         返回构造的工厂对象
     * @since  1.0
     */
    public static function Create($type)
    {
        _08_FilesystemFile::filterFileParam($type);
        // 构造工厂，接口文件命名规则为：登录类型 + '_auth.php'
        $class = $type . 'Auth';
        if(is_file(OTHER_SITE_BIND_PATH . $type . '_auth.php')) {
            require_once OTHER_SITE_BIND_PATH . $type . '_auth.php';
        } else {
            cls_message::show("$class 接口文件不存在！");
        }
                
        if( class_exists($class) ) {
            if ( !(self::$_instance instanceof $class) )
            {
                self::$_instance = new $class();
            }            
        } else {
            cls_message::show("$class 接口不存在！");
        }
        (self::$_instance instanceof Auther) || cls_message::show("$class 接口必须继承于 auther抽象类！");

        return self::$_instance;
    }

    /**
     * 操作后执行刷新父窗口并关闭本窗口
     *
     * @param string $msg 最后提示的信息
     * @since 1.0
     */
    public static function UcActive($msg = '')
    {
		$cms_top = cls_env::mconfig('cms_top');
		$str = "document.domain = '$cms_top' || document.domain;\n";
		$browser = _08_Browser::getInstance();
		global $m_cookie;
		if($browser->isMobile() && !empty($m_cookie['mobile_reurl'])){
			$str .= (empty($msg) ? '' : "alert('$msg');\n");
			$str .= "var url='{$m_cookie['mobile_reurl']}';\n";
			$str .= "var tl=top.location; tl.assign ? tl.assign(url) : tl.replace(url);\n";
		}else{
			$str .= "try{window.opener.location.reload();}catch(ex){}\n";
			$str .= (empty($msg) ? '' : "alert('$msg');\n");
			$str .= "window.close();\n";
		}
		exit("<script type='text/javascript'>$str</script>");
    }
    
    public static function checkPass()
    {
        $curuser = cls_UserMain::CurUser();
        $post = cls_env::_POST('password, check_pass');
        if (!empty($post['check_pass']))
        {
            if (empty($post['password']) || (_08_Encryption::password($post['password']) != $curuser->info['password']))
            {
                cls_message::show('密码不正确。', M_REFERER);
            }
            else
            {
                # 重新跳转授权页面以重新登录授权
            	header('Location:' . self::$_instance->getCallBack());
                exit;
            }
        }
        echo <<<HTML
        <form method="post">
            请输入用户密码：<input type="password" name="password" /> <input type="submit" name="check_pass" value="提交" />
        </form>
HTML;
    }
}


// 创建工厂对象，并在对象中自动执行授权验证
$auth = & otherAuthFactory::Create($type);
$auth->Setup();

# 重新绑定
if( isset($act) )
{
    if ($act === ($type . '_reauth'))
    {
        if (empty($curuser->info['mid']))
        {
            cls_message::show('请先登录。');
        }
        
        otherAuthFactory::checkPass();
        exit;
    }
    else
    {
        switch(strtolower($act)) {
            case 'uc_action' : otherAuthFactory::UcActive(); break;
            # 重新授权
            default : bind08CMSInterface::actionBind($act); break;
        }
    }
}

# 如果前台登录不成功会出现写入不了SESSION情况
(empty($_SESSION[otherSiteBind::$authfields[$type]]) && (@$act != ($type . '_reauth'))) && cls_message::show('错误请求，当前登陆信息已经过期！');

$minfo = $db->fetch_one("
    SELECT `mid`, `mname`, `email`, `password`, `checked`, `mchid`, `isfounder` FROM `{$tblprefix}members`
    WHERE `" . otherSiteBind::$authfields[$type] . "` = '" . $_SESSION[otherSiteBind::$authfields[$type]] . "' LIMIT 1");
if(empty($minfo)) {
    bind08CMSInterface::BindTemplate($auth, $minfo);
} else {
    bind08CMSInterface::Login08CMS($minfo);
}