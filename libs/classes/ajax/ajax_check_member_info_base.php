<?php
/**
 * 检查用户信息是否重复（为安全考虑，目前只对用户名和邮箱进行判断）
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_member_info/val/admin/filed/mname/
 *                         http://nv50.08cms.com/index.php?/ajax/check_member_info/val/admin@08cms.com/filed/email/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Member_Info_Base extends _08_Models_Base
{
    public function __toString()
    {
        $output = '';
        if ( !empty($this->_get['filed']) && isset($this->_get['val']) && in_array($this->_get['filed'], array('mname', 'email')) )
        {
            $mcharset = cls_env::getBaseIncConfigs('mcharset');
        	$val = empty($this->_get['val']) ? '' : cls_string::iconv("utf-8", $mcharset, $this->_get['val']); 
        	$re = cls_userinfo::CheckSysField($val, trim($this->_get['filed']));
        	if($re['error']) $output = $re['error'];
        }
        
        return $output;
    }
}