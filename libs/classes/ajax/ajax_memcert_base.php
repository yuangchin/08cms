<?php
/**
 * 检查短信验证功能
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/memcert/datatype/xml/option/msgcode/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Memcert_Base extends _08_Models_Base
{
    public function __toString()
    {       
    	$timestamp = TIMESTAMP;
		$mctid = empty($this->_get['mctid']) ? '0' : $this->_get['mctid'];
		$sms = new cls_sms();
		$msg = $sms->smsTpl($mctid,1);
		
    	$info = array();
    	$mobile = empty($this->_get['mobile']) ? "" : $this->_get['mobile'];
		
		if($sms->isClosed()){ 
			$info = array(
				'time' => -1,
				'text' => '系统没有设置短信接口平台!'
			);
			return $info;
		}
		if($this->_get['option'] == 'msgcode'){
    		if(strlen($mobile)<10){
    			$info = array(
    				'time' => 0,
    				'text' => '手机号码格式错误'
    			);
    		}elseif(preg_match("/^\d{3,4}[-]?\d{7,8}$/", $mobile)){
    			$msgcode = cls_string::Random(6, 1);
    			/*if(empty($sms_cfg_api) || ($sms_cfg_api == '(close)')){
    				$info = array(
    					'time' => -1,
    					'text' => '系统没有设置短信接口平台!'
    				);
    			}else{*/
    				@list($inittime, $initcode) = maddslashes(explode("\t", @authcode($m_cookie['08cms_msgcode'],'DECODE')),1);
    				if(($timestamp - $inittime) > 60){
    
    					$msg = str_replace(array('%s','{$smscode}'), $msgcode, $msg);
    
    					//$sms = new cls_sms();
    					$msg = $sms->sendSMS($mobile,$msg,'ctel');
    
    					if($msg[0]==1){
    						msetcookie('08cms_msgcode', authcode("$timestamp\t$msgcode", 'ENCODE'));
    					}else{
    						$info = array(
    							'time' => -1,
    							'text' => '发送信息失败，请联系管理员！'
    						);
    					}
    				}else{
    					$info = array(
    						'time' => 1,
    						'text' => '请不要重复提交，等待系统响应'
    					);
    				}
    			//}
    		}else{
    			$info = array(
    				'time' => 0,
    				'text' => '手机号码格式错误'
    			);
    		}
    	}
    	//usleep(1000); //8.2*1000*
        return $info;
    }
}