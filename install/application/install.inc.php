<?php
/**
 * 安装引导配置
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08_INSTALL_EXEC') || exit('No Permission');
error_reporting(E_ALL);
@ini_set("display_errors", TRUE);
@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', 0);

session_start();

@include M_ROOT . 'base.inc.php';
include M_ROOT . 'include' . DS . 'defines.php';
@header("Content-type:text/html;charset=$mcharset");
require _08_INCLUDE_PATH . 'excache.cls.php';
# 引入自动加载类
require _08_INCLUDE_PATH . 'loader.cls.php';
_08_Loader::autoLoadPathConfigs(
    array(
        '_08_C_' => array(_08_EXTEND_PATH . 'install.controller', _08_INSTALL_PATH . 'controller'),
        '_08_M_' => _08_INSTALL_PATH . 'model',
        '_08' => array(dirname(__FILE__), _08_EXTEND_LIBS_PATH . 'classes|1', _08_LIBS_PATH . 'classes|1', substr(_08_APPLICATION_PATH, 0, -1)),
        'cls_' => array(_08_EXTEND_LIBS_PATH . 'classes|1', _08_LIBS_PATH . 'classes|1', substr(_08_APPLICATION_PATH, 0, -1))
    )
);
_08_Loader::autoLoadRegister();

# 引入工厂类
require _08_INCLUDE_PATH . 'factory.php';
include _08_INCLUDE_PATH . 'general.fun.php';
include _08_INCLUDE_PATH . 'message.cls.php';