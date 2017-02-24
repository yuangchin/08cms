<?php
/**
 * @Plugin Name:    微信插件
 * @Author:     Wilson
 * @Description:  Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 * @Version:     1.0
 * @Enable: Yes
 */
 
defined('_08CMS_APP_EXEC') || exit('No Permission');

# 注册后台插件
if ( defined('M_ADMIN') )
{
    _08_Loader::import(dirname(__FILE__) . '::admin::admin_weixin_plugin_header');
    _08_Loader::import(dirname(__FILE__) . '::admin::admin');
    $admin_plugin_class_name = '_08_Admin_Weixin_Plugin';
    _08_Plugins_Base::register('admin.weixin.init', array($admin_plugin_class_name, 'config'));
    _08_Plugins_Base::register('admin.weixin.config', array($admin_plugin_class_name, 'config'));
    _08_Plugins_Base::register('admin.weixin.menu', array($admin_plugin_class_name, 'menu'));
    _08_Plugins_Base::register('admin.weixin.architecture', array($admin_plugin_class_name, 'architecture'));
}
