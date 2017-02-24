<?php
/**
 * 获取服务器时间。
 *
 * @example   请求范例URL：/index.php?/ajax/jsNowTime/ OR _08_Http_Request::uri2MVC('ajax=jsNowTime');
 * @author    Peace <xpigeon@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_ftend extends _08_Models_Base
{
    public function __toString()
    {
		return TIMESTAMP; 
    }
}