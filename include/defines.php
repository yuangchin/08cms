<?php
/**
 * 常量定义文件
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

/* M_UPSEN  前台、M_ADMIN  代表后台、M_MCENTER 会员中心、IN_MOBILE  手机版、_08_MSPACE 会员空间 */
define('M_COM', TRUE);

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

defined('TIMESTAMP') || define('TIMESTAMP', time());

define('_08_CACHE_DIR', 'dynamic'); # 系统动态目录
define('_08_USERCACHE_DIR', 'cache'); # 用户缓存目录
define('_08_SYSCACHE_DIR', 'syscache'); # 系统开发配置缓存目录

define('_08_CACHE_PATH', M_ROOT . _08_CACHE_DIR . DS); # 动态目录完全路径
define('_08_USERCACHE_PATH',_08_CACHE_PATH ._08_USERCACHE_DIR . DS); # 系统缓存完全目录
define('_08_SYSCACHE_PATH',_08_CACHE_PATH ._08_SYSCACHE_DIR . DS); # 系统开发配置缓存完全路径

define('_08_LIBS_DIR', 'libs'); # 系统库目录
define('_08_LIBS_PATH', M_ROOT . _08_LIBS_DIR . DS); # 系统库完全路径

define('_08_EXTEND_DIR', empty($_08_extend_dir) ? 'extend_sample' : $_08_extend_dir); # 扩展系统目录
define('_08_EXTEND_PATH', M_ROOT . _08_EXTEND_DIR . DS); # 完全扩展路径
define('_08_EXTEND_LIBS_PATH', _08_EXTEND_PATH . _08_LIBS_DIR . DS); # 完全扩展库路径
define('_08_EXTEND_CACHE_PATH', _08_EXTEND_PATH . _08_CACHE_DIR . DS);
define('_08_EXTEND_SYSCACHE_PATH', _08_EXTEND_CACHE_PATH . _08_SYSCACHE_DIR . DS);

// 定义模板文件的PHP缓存目录
define('_08_TPL_CACHE', _08_CACHE_PATH . 'tplcache' . DS);

// 定义模板标识编辑临时缓存目录，用于临时存储编辑模板时鼠标选中的文本
define('_08_TEMP_TAG_CACHE', _08_CACHE_PATH . 'temp_tag_cache' . DS);

// 定义后台文件夹，方便以后随时修改
define('_08_ADMIN', 'admina');
define('_08_ADMIN_PATH', M_ROOT . _08_ADMIN . DS);

// 模板路径
define('_08_TEMPLATE_DIR', 'template');
define('_08_TEMPLATE_PATH', M_ROOT . _08_TEMPLATE_DIR . DS);

// include路径
define('_08_INCLUDE_DIR', 'include');
define('_08_INCLUDE_PATH', M_ROOT . _08_INCLUDE_DIR . DS);

// 核心API路径
define('_08_CORE_API_PATH', _08_INCLUDE_PATH . 'core_api' . DS);

// include/extends路径
define('_08_INCLUDE_EX_PATH', _08_INCLUDE_PATH .'extends'. DS);

// 定义APP管理路径
define('_08_APPLICATION_PATH', _08_INCLUDE_PATH . 'application' . DS);

// 定义扩展APP管理路径
define('_08_EXTEND_APPLICATION_PATH', _08_EXTEND_PATH . _08_INCLUDE_DIR . DS . 'application' . DS);

// 定义插件管理路径
define('_08_PLUGINS_PATH', _08_APPLICATION_PATH . 'plugins' . DS);

// 定义插件扩展管理路径
define('_08_EXTEND_PLUGINS_PATH', _08_EXTEND_APPLICATION_PATH . 'plugins' . DS);

// 执行APP应用
define('_08CMS_APP_EXEC', true);

// 定义MVC中的V路径
define('_08_V_PATH', _08_APPLICATION_PATH . 'views' . DS);

// 定义tools路径
define('M_TOOLS_PATH', M_ROOT . 'tools' . DS);

// 定义外部文件路径
define('_08_OUTSIDE_PATH', M_ROOT . 'outside' . DS);

define('M_REFERER',isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

define('M_URI',isset($_SERVER['REQUEST_URI']) ? rawurldecode($_SERVER['REQUEST_URI']) : '');

if (isset($_SERVER['HTTP_HOST']))
{
    define('M_SERVER',strtolower($_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']));
}
else
{
	define('M_SERVER', 'localhost');
}

define('QUOTES_GPC', function_exists('get_magic_quotes_gpc') ? get_magic_quotes_gpc() : false);

$os = strtoupper(substr(PHP_OS, 0, 3));
if (!defined('IS_WIN'))
{
	define('IS_WIN', ($os === 'WIN') ? true : false);
}
if (!defined('IS_MAC'))
{
	define('IS_MAC', ($os === 'MAC') ? true : false);
}
if (!defined('IS_UNIX'))
{
	define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')) ? true : false);
}