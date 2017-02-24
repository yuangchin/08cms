<?php
define('M_ROOT', substr(dirname(__FILE__), 0, -7));
@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', '0');
@include M_ROOT . 'base.inc.php';

//开发调试模式，显示所有错误
empty($phpviewerror) || @ini_set('display_errors', 'On');
if ( $phpviewerror == 3 )
{
    error_reporting(E_ALL);
}
else
{
	error_reporting(0);
}

//定义系统常量
include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'defines.php';

//定义程序默认编码，用于设置文件公共编码，如AJAX请求时就可不用在ajax.php里一个个设置编码，
@header("Content-type:text/html;charset=$mcharset");

//第三方扩展缓存，只跟base.inc.php的设置有关
include_once _08_INCLUDE_PATH . 'excache.cls.php';
$m_excache = cls_excache::OneInstance();

// 引入自动加载类
require _08_INCLUDE_PATH . 'loader.cls.php';
_08_Loader::setup();
cls_env::__checkEnvironment();

//开启php缓冲区
cls_env::mob_start();

//加载通用函数
require _08_INCLUDE_PATH .'general.fun.php';

//搜索引擎
define('ISROBOT',cls_env::IsRobot());
cls_env::RobotFilter();

//全局变量
cls_env::GLOBALS();
cls_env::_FILES();
extract((array)cls_env::_GET_POST(),EXTR_SKIP);
$m_cookie = cls_env::_COOKIE();
$onlineip = cls_env::OnlineIP();

//初始化系统配置
$mconfigs = cls_cache::Read('mconfigs');
extract($mconfigs,EXTR_OVERWRITE );

#ini_set('date.timezone','ETC/GMT'.(empty($timezone) ? 0 : $timezone));
@date_default_timezone_set('ETC/GMT'.(empty($timezone) ? 0 : $timezone));

/**
 * 设置全局SESSION配置，如果memcache开启则自动把SESSION存到memcache里
 * （注意：请尽量用memcache存放SESSION，特别是程序运行在多服务器上）
 */
if ( strtolower(@$m_excache->__cache_type) == 'memcached' && !empty($m_excache->obj->enable) )
{
    @ini_set('session.save_handler', "memcache");
    @ini_set('session.save_path', "tcp://$ex_memcache_server:$ex_memcache_port");
}
else # 否则存到文件里
{
    @ini_set('session.save_handler', "files");
    $tmpPath = sys_get_temp_dir();
	if ( is_writable($tmpPath) )
    {
        @ini_set('session.save_path', $tmpPath);
    }
}

if ( !headers_sent() )
{
    session_start();
}

$timestamp = TIMESTAMP;
if(!empty($disable_htmldir)){
	$mconfigs['cnhtmldir'] = $cnhtmldir = '';
}
$authorization = md5($authkey);

//IP禁止及安全过滤
if(cls_env::IpBanned($onlineip)) exit('IP_Fobidden'); 

//是否模板调试模式
$debugtag = $onlineip && ($v = explode(',',@$debugtag_ips)) && in_array($onlineip,$v) ? 1 : 0;
define('_08_DEBUGTAG', $debugtag);

//加载加密核心
cls_env::LoadZcore();

// 引入其它网站登录接口
_08_Loader::register('otherSiteBind', M_TOOLS_PATH . 'other_site_sdk' . DS . 'other_site_bind.php');

//页面调试信息
$_mdebug = new cls_debug;

//连接数据库
$dbcharset = !$dbcharset && in_array(strtolower($mcharset),array('gbk','big5','utf-8')) ? str_replace('-', '', $mcharset) : $dbcharset;
$db = _08_factory::getDBO();

//建立当前浏览者对象
$curuser = cls_UserMain::CurUser();
$curuser->vsrecord();
$memberid = $curuser->info['mid'];

# 改变 cms_abs 的作用域
defined( '_08_CMS_ABS' ) || define( '_08_CMS_ABS', $cms_abs );
# 定义MVC路由入口
defined( '_08_ROUTE_ENTRANCE' ) || define( '_08_ROUTE_ENTRANCE', 'index.php?/' );

//开发调试模式
if(($phpviewerror == 1) && $curuser->isadmin()){
	error_reporting(E_ALL);
}elseif(($phpviewerror == 2) && $curuser->info['mid']){
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

cls_env::filterClickJacking();
