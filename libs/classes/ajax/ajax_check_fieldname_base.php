<?php
/**
 * 字段标识是否合法
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_fieldname/sourcetype/test/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Fieldname_Base extends _08_Models_Base
{
    public function __toString()
    {
    	$msg = cls_fieldconfig::CheckNewID(@$this->_get['sourcetype'],@$this->_get['sourceid'],@$this->_get['fieldname']);
    	return $msg;		
    }
}