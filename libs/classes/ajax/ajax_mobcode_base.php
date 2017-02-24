<?php
/**
 * js检查短信验证码
 *
 * 用于提交表单前的js认证: 认证由{$cms_abs}include/sms/cer_code.js 中提交的手机认证码.
 * @example   请求范例URL：_08cms_validator.init("ajax","msgcode",{ url: '{$cms_abs}<?php echo _08_Http_Request::uri2MVC("ajax=Mobcode&val=%1"); ?>' });
 * @author    Peace <xpigeon@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Mobcode_Base extends _08_Models_Base
{
    public function __toString()
    {       
    	
		$timestamp = TIMESTAMP;
		$val = empty($this->_get['val']) ? "" : $this->_get['val']; //msgcode
		$m_cookie = cls_env::_COOKIE();
		
		@list($inittime, $initcode) = maddslashes(explode("\t", authcode($m_cookie['08cms_msgcode'],'DECODE')),1);
		if($timestamp - $inittime > 1800 || $initcode != $val){
			 $msg = "手机确认码有误"; //:{$val},{$initcode}
		}else{
			$msg = '';	
		}
        return $msg;
    }
}