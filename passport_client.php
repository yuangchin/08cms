<?php
/**
 * PHPWIND应用主入口
 * 
 * 本文件名称应该重新命名，但因为兼容性保留原名称，假如日后要扩展PHPWIND的其它应用都可从此入口进入即可
 */
define('PW_EXEC', TRUE);
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

include dirname(__FILE__). DS . 'include' . DS . 'general.inc.php';

// 定义一个PHPWIND应用路径
define('_08_PHPWIND_PATH', _08_INCLUDE_PATH . 'phpwind' . DS);

# 注册pw_前缀到该路径，让该路径脚本支持自动加载
_08_Loader::registerPrefix('pw_', _08_INCLUDE_PATH . 'phpwind');

# 定义应用ID或标识符
$configs = array();
if ( empty($action) || (isset($action) && ($action == 'windid_client')) )
{
    empty($action) && $action = 'windid_client';
    $configs = array('config' => cls_env::_GET_POST(), 'mconfig' => $mconfigs);
}
else
{
    _08_FilesystemFile::filterFileParam($action);
    if ( is_file(_08_PHPWIND_PATH . $action . '.config.php') )
    {
        $configs = (include _08_PHPWIND_PATH . $action . '.config.php');
    }
}

$app = _08_factory::getApplication($action, $configs, 'pw_');
if ( method_exists($app, 'run') )
{
    $app->run();
}