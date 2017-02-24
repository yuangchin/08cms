<?php
/**
 * @package     08CMS.Installation
 * @copyright   Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 */
if (version_compare(PHP_VERSION, '5.2.3', '<'))
{
	die('您的主机需要使用PHP 5.2.3或更高版本才能运行此版本的08CMS !');
}

define( '_08_INSTALL_EXEC', TRUE );
define('DS', DIRECTORY_SEPARATOR);
define( 'M_ROOT', dirname(dirname(__FILE__)) . DS );
define( '_08_INSTALL_PATH', dirname(__FILE__) . DS );

# 导入安装引导脚本
include dirname(__FILE__) . DS . 'application' . DS . 'install.inc.php';

# 开始运行安装向导
_08_factory::getInstance('_08_Install_Application')->run();