<?php
/**
* 该文件用于向PW服务端发送通知请求
* 相关API请查看{@link http://wiki.open.phpwind.com/index.php?title=WindID_API}
* 
* @package    PHPWIND
* @subpackage WindID
* @author     Wilson <Wilsonnet@163.com>
* @copyright  Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
*/
define('PW_EXEC', TRUE);

class cls_WindID_Send
{
    /**
     * 是否显示错误信息
     **/        
    private $is_show_error_message = true;
    
    private $error_message = '';     
    
    private static $instance = null;  
    
    private $enable = true;     
                
    /**
     * 初始化验证，用于判断后台是否启用PW功能
     * 
     * @static
     */
	protected function __construct()
	{
    	$this->enable = (bool) cls_env::mconfig('enable_pptin');
        if (!$this->isEnable())
        {
            $this->error_message = 'WINDID 未开启。';
        }
        
        try
        {
            _08_Loader::import('include:phpwind:windid_message.pw');
            _08_Loader::import('include:phpwind:windid_client:src:windid:WindidApi');
        }
        catch(WindDbException $e)
        {
            #echo $e->getMessage();   强制终止后台用户开了通行证后设置错误，让重设或关闭通行证。
            if ( defined('M_ADMIN') && $this->isEnable() && $this->is_show_error_message )
            {
                cls_message::show('WINDID通行证通信失败，请先在系统配置 - 通行证里设置好WINDID的配置信息');
            }
            else  # 因为不想这原因影响到前台用户正常使用，则前台不强制
            {
            	return false;
            }
        }
		
        return true;
	}
	
	/**
	 * 向WINDID服务端发送同步登录请求
     * {@link http://wiki.open.phpwind.com/index.php?title=User/login}
	 * 
	 * @param  string $username 请求登录的用户名
	 * @param  string $password 请求登录的密码
     * @return int              如果登录成功返回用户UID，否则直接打印错误信息
	 * 
     * @static
	 * @since  1.0
	 */
	public function synLogin( $username, $password )
	{
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
		$curuser = cls_UserMain::CurUser();
		$api = self::__getWindidAPI();
		# 尝试向WINDID服务端发送登录请求
		list($status, $userinfo) = $api->login( $username, $password );
        /**
         * 如果登录成功时向其它应用客户端发送同步登录请求，具体返回状态码表示请看
         * @see cls_Windid_Message::get()
         */
		if($status == 1)
		{
            $user = new cls_UserbaseDecorator($curuser);
            # 同步更新本地数据
            if ( isset($curuser->info['mid']) && ($curuser->info['mid'] > 0) )
            {
                $user->synUpdateLocalData($password, $userinfo['email']);
            }
            else // 如果服务端有该用户而本程序没有则自动注册
            {
                $user->synAddLocalUser($username, $password, $userinfo['email']);
            }
            unset($user);
			
			# 给PW登录成功的用户写一个COOKIE标识PW用户的ID，预防两边用户ID不同步情况的退出问题。
			msetcookie(cls_Windid_Message::PW_UID_COOKIE, $userinfo['uid'], cls_Windid_Message::PW_UID_COOKIE_TIME);
			echo $api->synLogin($userinfo['uid']);
            return $userinfo['uid'];
		}
		else # 如果同步登录不成功时提示相应错误
		{
		    if ($this->is_show_error_message)
            {
                cls_message::show( cls_Windid_Message::get($status), M_REFERER );                                
            }
            else
            {
            	$this->error_message = cls_Windid_Message::get($status);
                return false;                
            }
		}
	}
    
    /**
     * 向WINDID服务端发送同步注册请求
     * {@link http://wiki.open.phpwind.com/index.php?title=User/register}
     * 
	 * @param  string $username 请求的用户名
	 * @param  string $password 请求的密码
	 * @param  string $email    请求的邮箱
     * @param  string $regip    注册IP
     * @param  bool   $synlogin 是否进行注册后同步登录，TRUE为同步，FALSE为不同步（后台添加不需要同步）
     * @param  string $question 密码提示问题
     * @param  string $answer   密码提示答案
     * @return int              返回WINDID注册后的用户ID，否则直接打印错误
     * 
     * @static
     * @since  1.0
     */
    public function synRegister( $username, $password, $email, $regip = '', $synlogin = true, $question = '', $answer = '' )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        $api = self::__getWindidAPI();
        $uid = $api->register( $username, $email, $password, $question, $answer, $regip );
        if ($uid > 0)
        {
            # 注册成功后同步登录
            if ( $synlogin )
            {
		        list($status,) = $api->login( $username, $password );
                if ( $status == 1 )
                {
                    // BUG: WINDID的SDK貌似有BUG，注册后登录要操作多次登录才登录成功？？？但登录又没有这问题
                    for($i = 0; $i < 3; ++$i) echo $api->synLogin($uid);
                    
        			# 给PW登录成功的用户写一个COOKIE标识PW用户的ID，预防两边用户ID不同步情况的退出问题。
        			msetcookie(cls_Windid_Message::PW_UID_COOKIE, $uid, cls_Windid_Message::PW_UID_COOKIE_TIME);
                }
            }
            return $uid;
        }
        else
        {
		    if ($this->is_show_error_message)
            {
                cls_message::show( cls_Windid_Message::get($uid), M_REFERER );                
            }
            else
            {
            	$this->error_message = cls_Windid_Message::get($uid);
                return false;
            }
        }
    }
    
    /**
     * 同步编辑用户基本信息
     * {@link http://wiki.open.phpwind.com/index.php?title=User/edit}
     * 
     * @param  int    $uid      用户ID
     * @param  string $password 用户原密码
     * @param  array  $editinfo 修改信息array('username', 'password', 'email', 'question', 'answer')
     * 
     * @static
     * @since  1.0
     */
    public function editUser( $uid, $password, array $editinfo )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        
        $api = self::__getWindidAPI();
        return $api->editUser( self::_getPwUidByMid($uid), $password, $editinfo );
    }
    
    /**
     * 通过本系统的用户ID获取WINDID服务端的用户ID
     * 
     * @param  int $mid 本系统的用户ID
     * @return int      返回WINDID服务端的用户ID
     */
    protected static function _getPwUidByMid( $mid )
    {
        $info = cls_userbase::getUserInfo( cls_Windid_Message::PW_UID, 'mid = ' . (int)$mid  );
        return $info[cls_Windid_Message::PW_UID];
    }
    
    /**
     * 同步编辑用户详细信息
     * {@link http://wiki.open.phpwind.com/index.php?title=User/editInfo}
     * 
     * @param  int    $uid      用户ID
     * @param  array  $editinfo 修改信息
     * array('realname', 'gender', 'byear', 'bmonth','bday', 'hometown', 'location', 
     *       'homepage', 'qq', 'aliww', 'mobile', 'alipay', 'msn','profile')
     * 
     * @static
     * @since  1.0
     */
    public function editUserInfo( $uid, array $editinfo )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        
        $api = self::__getWindidAPI();
        return $api->editUserInfo( self::_getPwUidByMid($uid), $editinfo );
    }
    
    /**
     * 同步删除单个用户
     * {@link http://wiki.open.phpwind.com/index.php?title=User/delete}
     * 
     * @param  int $uid 用户ID
     * @return int      删除成功返回1，否则返回0
     * 
     * @static
     * @since  1.0
     */
    public function deleteUser( $uid )
    {
        return self::batchDeleteUser( (array)$uid );
    }
    
    /**
     * 同步删除多个用户
     * {@link http://wiki.open.phpwind.com/index.php?title=User/batchDelete}
     * 
     * @param  array $uids 用户ID数组
     * @return int         删除成功返回1，否则返回0
     * 
     * @static
     * @since  1.0
     */
    public function batchDeleteUser( array $mids )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        
        $api = self::__getWindidAPI();
        return $api->batchDeleteUser( self::_getPwUidsByMids($mids) );
    }
    
    /**
     * 发送站内短信
     * {@link http://wiki.open.phpwind.com/index.php?title=Message/send}
     * 
     * @param  array  $mids    接收用户ID，支持多个
     * @param  string $content 短信内容
     * @param  int    $fromUid 发送者ID
     * @return int             成功返回1，失败返回错误代码
     */
    public function send( $mids, $content, $fromUid )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        $api = self::__getWindidAPI('message');
        return $api->send( self::_getPwUidsByMids($mids), $content, $fromUid );
    }
    
    /**
     * 根据传入的本系统UID返回WINDID服务端的用户ID
     * 
     * @param  array $mids 本系统用户ID
     * @return array $uids WINDID服务端用户ID
     * @since  1.0
     */
    protected static function _getPwUidsByMids( array $mids )
    {        
        $uids = array();
        if ( !empty($mids) )
        {
            $mids = array_map('intval', $mids);
            #获取对应WINDID用户的ID
            $db = _08_factory::getDBO();
            $db->select( cls_Windid_Message::PW_UID )
               ->from('#__members')
               ->where('mid ')->_in($mids)
               ->exec();
            while( $row = $db->fetch() )
            {
                $uids[] = $row[cls_Windid_Message::PW_UID];
            }
        }
        return $uids;
    }
    
    /**
     * 删除多条短信
     * {@link http://wiki.open.phpwind.com/index.php?title=Message/deleteMessages}
     * 
     * @param  int   $uid        用户ID
     * @param  array $messageIds 私信ID
     * @return int               成功返回1 失败返回0
     */
    public function deleteMessages( $uid, array $messageIds )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        
        $api = self::__getWindidAPI('message');
        return $api->deleteMessages( self::_getPwUidByMid($uid), self::_getMessageIds($messageIds) );
    }
    
    /**
     * 通过本系统的私信ID获取WINDID服务端私信ID
     * 
     * @param  array $messageIds  本系统的私信ID
     * @return array $message_ids WINDID服务端私信ID
     * @since  1.0
     */
    protected static function _getMessageIds( array $messageIds )
    {
        # 获取本系统私信ID
        $db = _08_factory::getDBO();
        $db->select(cls_Windid_Message::PW_MESSAGE_ID)
           ->from('#__pms')
           ->where('pmid')->_in($messageIds)
           ->exec();
        $message_ids = array();
        while($row = $db->fetch())
        {
            $message_ids[] = $row[cls_Windid_Message::PW_MESSAGE_ID];
        }
        return $message_ids;
    }
    
 	/**
	 * 更新用户积分
     * {@link http://wiki.open.phpwind.com/index.php?title=User/editCredit}
	 *
	 * @param  int  $uid   用户ID
	 * @param  int  $cType 积分所代表的ID，即 前缀_windid_user_data数据表的字段 credit1 (1-8)
	 * @param  int  $value 要更新积分值
     * @param  bool $isset 该操作是修改积分还是增加积分，TRUE为修改，FASLE为增加
     * 
     * @static
     * @since  1.0
	 */   
    public function editCredit( $uid, $cType, $value, $isset = false )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        $api = self::__getWindidAPI();
        return $api->editCredit( self::_getPwUidByMid($uid), $cType, $value, $isset );
    }
    
    /**
     * 验证用户信息
     * {@link http://wiki.open.phpwind.com/index.php?title=User/checkInput}
     * 
     * @param  string $input    需验证的字符
     * @param  int    $type     验证类型 1-用户名, 2-密码, 3-邮箱
     * @param  string $username 用户名,用于对某username的上述类型进行验证
     * @param  int    $uid      用户UID,用于对某uid的上述类型进行验证
     * @return int              int型，验证成功，返回1, 登录失败，返回小于1的错误代码
     * 
     * @static
     * @since  1.0
     */
    public function checkUserInput($input, $type, $username = '', $uid = 0)
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() )
        {
            return false;
        }
        $api = self::__getWindidAPI();
        return $api->checkUserInput($input, $type, $username, $uid );
    }
    
    /**
     * 获取PW接口API对象
     * 
     * @return object API对象句柄
     */
    private static function __getWindidAPI( $type = 'user' )
    {
        return WindidApi::api( $type );
    }
    
    /**
     * 获取PW用户未读私信
     * {@link http://wiki.open.phpwind.com/index.php?title=Message/getUnreadDialogsByUid}
     * 
     * @param  int   $uid 用户ID
     * @return array      返回用户未读私信数组
     */
    public function getPwUserMessage( $uid )
    {
        # 如果后台未开启WINDID则不作操作
		if ( !$this->isEnable() || ($uid < 0) )
        {
            return false;
        }
        $api = self::__getWindidAPI('message');
        return $api->getUnreadDialogsByUid( self::_getPwUidByMid($uid) );
    }
	
	/**
	 * 向WINDID服务端发送同步退出请求
     * 
     * @return bool 退出成功返回TRUE，否则返回FALSE
	 * @since  1.0
	 */
	public function synLogout()
	{
	    # 如果后台未开启WINDID则不作操作
		$cookies = cls_env::_COOKIE();
		if ( $this->isEnable() && !empty($cookies[cls_Windid_Message::PW_UID_COOKIE]) )
        {
    		mclearcookie( cls_Windid_Message::PW_UID_COOKIE );
            $api = WindidApi::api('user');
    		echo $api->synLogout($cookies[cls_Windid_Message::PW_UID_COOKIE]);
            return true;
        }
        
        return false;
	}
    
    /**
     * 是否已经开启WINNDID
     * 
     * @return bool 如果已经开启返回TRUE，否则返回FALSE
     * @since  nv50
     **/
    public function isEnable()
    {
        return $this->enable;
    }
    
    public static function getInstance()
    {
        if (!(self::$instance instanceof self))
        {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * 设置属性
     * 注：如果子类调用该方法时、子类的要判断的属性必需不能为 private
     * 
     * @param string $name  要设置的属性名称
     * @param mixed  $value 属性值
     * 
     * @since nv50
     */
    public function setter($name, $value)
    {        
        if ( property_exists($this, $name) )
        {
            $this->$name = $value;
        }
    }    
    
    /**
     * 获取属性
     * 注：如果子类调用该方法时、子类的要判断的属性必需不能为 private
     * 
     * @param  string $name  要获取的属性名称
     * @return mixed         返回获取到的属性值，如果不存在该属性或是该属性为private时返回null
     * 
     * @since  nv50
     */
    public function getter($name)
    {    
        if ( property_exists($this, $name) )
        {
            return $this->$name;
        }
        
        return null;
    }
}