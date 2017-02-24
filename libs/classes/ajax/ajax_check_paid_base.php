<?php
/**
 * 推送位paid定义是否合法
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_paid/paid/test/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Paid_Base extends _08_Models_Base
{
    public function __toString()
    {
    	$msg = cls_PushArea::CheckNewID(@$this->_get['paid']);
    	return $msg;
    }
}