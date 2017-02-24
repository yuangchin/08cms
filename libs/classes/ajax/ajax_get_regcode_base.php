<?php
/**
 * 用AJAX获取验证码
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/get_regcode/regtype/register/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Get_Regcode_Base extends _08_Models_Base
{
    public function __toString()
    {
    	$verify = empty($this->_get['verify']) ? '08cms_regcode' : trim($this->_get['verify']);
        $regtype = empty($this->_get['regtype']) ? '' : trim($this->_get['regtype']);
    	$inputName = empty($this->_get['input_name']) ? '' : trim($this->_get['input_name']);
    	$formName = empty($this->_get['form_name']) ? '' : trim($this->_get['form_name']);
    	$class = empty($this->_get['class']) ? '' : trim($this->_get['class']);
    	$inputString = empty($this->_get['input_string']) ? '' : trim($this->_get['input_string']);
        if ( $regtype )
        {
            $cms_regcode = $this->_mconfigs['cms_regcode'];
            if ( @in_array($regtype, array_filter(explode(',', $cms_regcode))) )
            {
                return (_08_HTML::getCode($verify, $formName, $class, $inputName, $inputString));
            }
        }
    }
}