<?php
/**
 * @Plugin Name: 房产系统微信插件
 * @Author:      Wilson
 * @Description: Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 * @Version:     1.0
 * @Enable:      Yes
 */
 
defined('_08CMS_APP_EXEC') || exit('No Permission');

if ( defined('M_ADMIN') )
{                
    _08_Loader::import(dirname(__FILE__) . '::admin::admin_weixin_property');
    $admin_property_plugin_class_name = '_08_Admin_Weixin_Property';
    _08_Plugins_Base::register('admin.weixin_property.init', array($admin_property_plugin_class_name, 'init'));
}
else if ( defined('M_MCENTER') ) # 注册会员中心插件
{
	_08_Loader::import(dirname(__FILE__) . '::member::member_weixin_property');
    $member_property_plugin_class_name = '_08_Member_Weixin_Property';
    _08_Plugins_Base::register('member.weixin_property', array($member_property_plugin_class_name, 'init'));
}