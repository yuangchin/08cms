<?php
/**
 * 用户基类装饰器（装饰器模式）
 * 防止用户基类越来越庞大，并减少多重继承之类的复杂逻辑
 * 
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
defined('M_COM') || exit('No Permisson');
class cls_UserbaseDecorator
{
	private $user;
	
	public function __construct( cls_userbase $user )
    {
        $this->user = $user;
    }
    
    /**
     * 对通过了UC或通行证之类的登录进行同步更新本地数据
     * 
     * @param string $password 当前登录的用户密码
     * @param string $email    当前登录的用户邮箱
     * 
     * @since 1.0
     */
    public function synUpdateLocalData( $password, $email )
    {
        $md5_password = _08_Encryption::password($password);
		$needupdate = false;
        # 如果本系统数据与UC或通行证的不同步则进行同步
		if($this->user->info['email'] != $email)
        {
			$this->user->updatefield('email', $email);
			$needupdate = true;
		}
        
		if($this->user->info['password'] != $md5_password)
        {
			$this->user->updatefield('password', $md5_password);
			$needupdate = true;
		}
		$needupdate && $this->user->updatedb();
    }
    
    /**
     * 对通过了UC或通行证之类的登录进行同步添加本地用户
     * 
     * @param  string $username      当前登录的用户名
     * @param  string $password      当前登录的用户密码
     * @param  string $email         当前登录的用户邮箱
     * @param  array  $update_fields 要更新的其它字段信息，KEY为字段名，VALUE为值，如果不需要标志可不传递该参数
     * @return bool                  如果注册成功返回true，否则返回false
     * 
     * @since  1.0
     */
    public function synAddLocalUser( $username, $password, $email, $update_fields = array() )
    {
		$newuser = new cls_userinfo;
        $userLen = strlen($username);
        $add_status = false;
        $md5_password = _08_Encryption::password($password);
        
        # 判断用户名是否存在，如果$uid小于或等于0则用户名不存在
        $uid = $newuser->getIdForName($username);
		if(
            $uid <= 0 &&
            ($mid = $newuser->useradd($username, $md5_password, $email, $mchid = 1)) && 
            ($userLen >= 3 && $userLen <= 15) )
        {
            # 同时更新其它字段
            if ( !empty($update_fields) && is_array($update_fields) )
            {
                foreach($update_fields as $field => $value)
                {
                    $newuser->updatefield($field, $value);
                }
            }
			$newuser->check(1, true);
			//将新增会员的资料转到$curuser后，统一处理登录事务
			$this->user->info = array_merge($this->user->info, $newuser->info);
            $add_status = true;
		}
        unset($newuser);
		return ($add_status ? true : false);
    }
}