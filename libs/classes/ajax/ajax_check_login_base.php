<?php
/**
 * 判断用户登录请求
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_login/username/admin/password/admin/verify/ttt/regcode/9830/datatype/js
 *                         http://nv50.08cms.com/index.php?/ajax/check_login/username/admin/password/admin/verify/ttt/regcode/9830/callback/callbackFun
 * @ 参数: subdata=1       返回sub表取通用字段资料
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Login_Base extends _08_Models_Base
{
    protected $status;
    
    protected $_userInfo;
    
    public function __construct()
    {
        parent::__construct();
        $this->_userInfo = array();
        $this->status = array('error' => '', 'message' => '', 'user_info' => array('mid' => 0, 'mname' => '游客'));
    }
    
    public function __toString()
    {
        $verify = empty($this->_get['verify'])?'':$this->_get['verify'];
		cls_env::SetG('verify',$verify); //regcode_pass($rname,$code='')要global $verify; 得到此值
		if (!regcode_pass('login', empty($this->_get['regcode']) ? '' : trim($this->_get['regcode'])))
        {
            $this->status['error'] = '验证码错误';
        }
        elseif ( empty($this->_get['username']) || empty($this->_get['password']) )
        {
            $this->status['error'] = '用户名或密码不能为空。';
        }
        else
        {
            ob_start();
            $this->_get['username'] = cls_String::iconv('UTF-8', cls_env::getBaseIncConfigs('mcharset'), $this->_get['username']);
    		//结合当前登录帐号及密码，将UC会员与本站会员进行整合，并处理同步登录
            $this->_curuser->UCLogin($this->_get['username'],$this->_get['password']);
           	# 同步登录通行证
            $windid = cls_WindID_Send::getInstance();
            $windid->setter('is_show_error_message', false);
    		$windid->synLogin( $this->_get['username'], $this->_get['password'] );
            $contents = ob_get_contents();
            ob_end_clean();
            cls_phpToJavascript::toAjaxSynchronousRequest($contents);
        	$md5_password = _08_Encryption::password($this->_get['password']);
            $user = new cls_userinfo;
            $user->activeuserbyname($this->_get['username']);
			$user->sub_data(); 
            $this->_userInfo = $user->getter('info');
            if ($md5_password == $this->_userInfo['password'])
            {
				if(empty($this->_userInfo['checked'])){
					$this->status['error'] = '会员未审核。';
				}else{
					$user->LoginFlag($this->_userInfo['mid'], $md5_password);
					$this->status['user_info'] = $this->filterUserInfo();
					$this->status['message'] = '登录成功。';	
				}
            }
            else
            {
            	$this->status['error'] = '用户名或密码错误。';
            }
        }       
        
        return $this->status;
    }
    
    public function filterUserInfo()
    {
        $new_user_info = array();
        if ($this->_userInfo)
        {
			$arr = array('mid', 'mname', 'mchid', 'checked', 'qq_nickname', 'regdate', 'currency0');
            
			$grouptypes = cls_cache::Read('grouptypes'); 
			foreach($grouptypes as $k => $v)
            {
            	if(!empty($this->_userInfo['grouptype'.$k]))
                {
            		$usergroups = cls_cache::Read('usergroups',$k);
            		$usergroupName = $usergroups[$this->_userInfo['grouptype'.$k]]['cname'];
				    $new_user_info["grouptype{$k}name"] = $usergroups[$this->_userInfo['grouptype'.$k]]['cname'];
            	}
			}
            
			$currencys = cls_cache::Read('currencys');
			foreach($currencys as $k=>$v){
				$arr[] = "currency$k";
			}            
			foreach ($arr as $key)
            {
                if (isset($this->_userInfo[$key]))
                {
                    $new_user_info[$key] = $this->_userInfo[$key];
                }
            }
        }
        if(!empty($this->_get['subdata'])){ //从sub表取通用字段资料
			#$this->_curuser->sub_data(); 
			$mfields0 = cls_cache::Read('mfields',0); 
			foreach($mfields0 as $key=>$cfg){ 
				if(!empty($this->_userInfo[$key])){ 
					$val = $this->_userInfo[$key]; 
					if($cfg['datatype']=='image'){ //处理单图头像等
						$val = cls_url::tag2atm($val);
					}
					$new_user_info[$key] = $val;
				}
			}
		}
        return $new_user_info;
    }
}