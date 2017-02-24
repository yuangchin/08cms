<?php
/**
 * 副件分类fcaid定义是否合法
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_fcaid/fcaid/66/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Fcaid_Base extends _08_Models_Base
{
    public function __toString()
    {
    	$msg = cls_fcatalog::CheckNewID(@$this->_get['fcaid']);
    	return $msg;	
    }
}