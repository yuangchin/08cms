<?php
/**
 * WindID提示信息类
 *
 * @package    PHPWIND
 * @subpackage WindID
 * @author     Wilson <Wilsonnet@163.com>
 * @copyright  Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
 
defined('PW_EXEC') || exit('No Permission');
class cls_Windid_Message
{
    /**
     * 要生成PW用户COOKIE的名称
     * 该COOKIE只保证同用户名不同ID时间的登录或退出操作，如果使用该平台入口前保证了用户名与ID一至，则COOKIE不是必须
     * 
     * @var string
     */
	const PW_UID_COOKIE = 'pw_uid';
	
    /**
     * 要生成PW用户COOKIE的时间
     * 
     * @var int
     */
	const PW_UID_COOKIE_TIME = 31536000;
    
    /**
     * WINDID服务端在本系统的数据表里的用户字段标识名
     * 
     * @var string
     */
    const PW_UID = 'pw_uid';
    
    /**
     * WINDID服务端在本系统的数据表里的私信ID字段标识名
     */
    const PW_MESSAGE_ID = 'pw_message_id';
    
    /**
     * 根据状态码返回状态信息
     * 
     * @param  int    $code 状态码
     * @return string       状态信息
     * @since  1.0 
     */
    public static function get($code)
    {
        $msg = array(
            '1'=>'操作成功',
            '0'=>'操作失败',
            
            '-1'=>'用户名为空',
            '-2'=>'用户名长度错误',
            '-3'=>'用户名含有非法字符',
            '-4'=>'用户名含有禁用字符',
            '-5'=>'用户名已经存在',
            '-6'=>'邮箱为空',
            '-7'=>'非法邮箱地址',
            '-8'=>'邮箱不在白名单中',
            '-9'=>'邮箱在黑名单中',
            '-10'=>'邮箱地址已被注册',
            '-11'=>'密码长度错误',
            '-12'=>'密码含有非法字符',
            '-13'=>'原密码错误',
            '-14'=>'帐号不存在',
            '-20'=>'两次输入的密码不一致',
                    
            '-30'=>'私信长度错误',
                
            '-40'=>'学校为空',
            '-42'=>'学校地区为空',
            '-42'=>'学校类型为空',
          
            '-80'=>'上传失败',
            '-81'=>'上传类型错误',
            '-82'=>'上传文件太小',
            '-83'=>'上传文件太大',
            '-84'=>'上传文件错误',
        
            '-90'=>'连接超时',
            '-91'=>'类名错误',
            '-92'=>'方法错误',
            '-93'=>'服务器错误',      
        );
        
        return (isset($msg[$code]) ? ('[WindID] ' . $msg[$code]) : $code);
    }
}