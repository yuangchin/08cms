<?php
/**
 * 微信配置类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Config extends _08_M_Weixin_Base
{
    /**
     * 获取微信通信链接
     * 
     * @param  string $param 区分通信入口的参数，如：mid=1  或 aid=1
     * @return string        返回通信链接
     * @since  nv50
     */
    public static function getWeixinURL( $param = '' )
    {        
        return _08_CMS_ABS . _08_Http_Request::uri2MVC('weixin=' . $param);
    }
}