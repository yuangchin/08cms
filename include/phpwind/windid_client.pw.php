<?php
/**
 * 该文件为PHPWind的WindID服务端向本客户端发送的通知接收脚本，相关API请查看
 * 服务端：{@link http://wiki.open.phpwind.com/index.php?title=WindID_API}
 * 客户端：{@link http://wiki.open.phpwind.com/index.php?title=WindID%E5%AE%A2%E6%88%B7%E7%AB%AF%E6%8E%A5%E5%8F%A3%E5%BC%80%E5%8F%91%E8%AF%B4%E6%98%8E}
 *
 * @package    PHPWIND
 * @subpackage WindID
 * @author     Wilson <Wilsonnet@163.com>
 * @copyright  Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
defined('PW_EXEC') || exit('No Permission');
// 定义一个PHPWIND应用路径
define('_08_PHPWIND_CLIENT_PATH', _08_PHPWIND_PATH . 'windid_client' . DS);
define('_08_PHPWIND_CLIENT_SERVICE_BASE_PATH', _08_PHPWIND_CLIENT_PATH .'src' . DS . 'windid' . DS . 'service' . DS . 'base' . DS);

class pw_Windid_Client
{    
    /**
     * 失败状态标志
     * 
     * @var string
     */ 
    const FAIL = 'fail';
    
    /**
     * 成功状态标志
     * 
     * @var string
     */
    const SUCCESS = 'success';
    
    /**
     * 用户类句柄
     * 
     * @static
     */ 
    private static $userInstance = null;
    
    /**
     * 当前请求的通知参数
     * 
     * @var array
     */ 
    protected $_config = array();
    
    /**
     * 系统配置参数
     * 
     * @var array
     */
    protected $_mconfigs = array();
    
    # 初始化配置参数
    public function __construct( $configs )
    {
        $this->_mconfigs = (array) $configs['mconfig'];        
        $this->_config = (array) $configs['config'];
        
        # 如果以下参数不存在时则自动初始化
        foreach(array('windidkey', 'time', 'clientid', 'operation', 'uid') as $key)
        {
            isset($this->_config[$key]) || $this->_config[$key] = 0;
        }
        
        # 如果后台未开启通行证功能时
        if ( empty($this->_mconfigs['enable_pptin']) )
        {
            $this->_showError( self::FAIL );
        }
        # 引入windid接口类
        require_once (_08_PHPWIND_CLIENT_PATH . 'src' . DS . 'windid' . DS . 'WindidApi.php'); 
        # 该文件引不引入貌似没影响，但官方提供的示例代码加了，所以暂时保留
        require_once (_08_PHPWIND_CLIENT_SERVICE_BASE_PATH . 'WindidUtility.php'); 
        isset($database) && $this->_config['db'] = $database;
        
        # 验证授权，如果未开启通行证功能或是密钥不对时视为通信或操作失败
        $appkey = pw_Windid_Utility::appKey(@$this->_mconfigs['pptin_appid'], $this->_config['time'], @$this->_mconfigs['pptin_key']);
		# 验证不过时提示通信失败
        if ( $appkey !== $this->_config['windidkey'] )
        {
            $this->_showError( self::FAIL );
        }
        
        $time = Pw::getTime();
        if (($time - $this->_config['time']) > @$this->_mconfigs['pptin_expire']) $this->_showError( 'timeout' );
    }
    
    # 开始运行应用
    public function run()
    {
	    try
	    {
            $notify = (include (_08_PHPWIND_CLIENT_SERVICE_BASE_PATH . 'WindidNotifyConf.php'));
            
            # 如果windid接口类有该通知代码时调用通知方法
            if ( !empty($notify[$this->_config['operation']]['method']) )
            {
                $function = $notify[$this->_config['operation']]['method'];
                # 通知方法返回true时，显示通信成功
                if ( (bool) call_user_func(array($this, '_' . $function)) )
                {
                    $this->_showMessage( self::SUCCESS );
                }
            }
	    }
	    catch (_08_ApplicationException $e)
	    {
	   		$this->_showError( self::FAIL );
	    }
        
        $this->_showError( self::FAIL );
    }
    
    /**
     * 通信测试，如果能执行该函数则证明通信成功
     * 
     * @return bool 通信成功返回true，否则发生异常时返回false
     */ 
    protected function _test()
    {
        if(@WINDID_CONNECT == 'db')
        {
            try
            {
                $db = new PDO($this->_config['db']['dsn'], $this->_config['db']['user'], $this->_config['db']['pwd']);
                $row = $db->query("SHOW COLUMNS FROM {$this->_config['db']['tableprefix']}user");
            }
            catch(PDOException $e)
            {
                throw new _08_ApplicationException();
            }
        }
        # 如果WINDID_CONNECT未定义也视为通信不成功
		if( (false == defined('WINDID_CONNECT')) || empty($row) )
        {
			return false;
		}
        
        return true;
    }
    
    /**
     * 添加用户
     * 
     * @return bool 添加成功返回true，否则返回false
     */
    protected function _addUser()
    {
        $userinfo = $this->_getPwUser();
        $user = new cls_UserbaseDecorator( self::_getUserInstance() );
        # 先随机生成一个密码，到时让用户登录时自动同步修改
        $flag = (bool) $user->synAddLocalUser(
            $userinfo['username'], 
            cls_string::Random(6), 
            $userinfo['email'],
            array(cls_Windid_Message::PW_UID => $userinfo['uid'])
        );
        unset($user);
        # 注册成功时进行同步登录
        if ($flag)
        {
            $this->_synLogin();
            return true;
        }
        
        return false;
    }
    
    /**
     * 同步登录
     * 
     * @return bool 执行成功返回true，否则返回false
     */
    protected function _synLogin()
    {
        cls_HttpStatus::trace('P3P');
        $acuser = self::_getUserInstance();
        $userInfo = $this->_getPwUser();
		$db->select('mid, password')->from('#__members')->where(array('mname' => $userInfo['username']))->_and('checked = 1')->exec();
		if ( $cmember = $db->fetch() )
        {
            # 同步对应服务端与客户端用户ID
            $acuser->activeuser($cmember['mid']);
            $acuser->updatefield(cls_Windid_Message::PW_UID, $userInfo['uid']);
            $acuser->updatedb();
			$acuser->autopush(); //自动推送
            
            # 执行登录
			msetcookie(cls_Windid_Message::PW_UID_COOKIE, $userInfo['uid'], cls_Windid_Message::PW_UID_COOKIE_TIME); 
			$acuser->LoginFlag($cmember['mid'], $cmember['password']);
            return true;
		}
        
        return false;
    }
    
    /**
     * 同步登出
     * 
     * @return bool 执行成功返回true;
     */ 
    protected function _synLogout()
    {
        cls_HttpStatus::trace('P3P');
        cls_userinfo::LogoutFlag();
        return true;
    }
    
    /**
     * 编辑用户基本信息(密码，邮箱，安全问题)
     * 注：密码要在用户下次登录时修改，安全问题本系统暂时未定义
     * 
     * @todo 修改安全问题以后需求要加时要作处理
     */ 
    protected function _editUser()
    {
        $userInfo = $this->_getPwUser();
        if (empty($userInfo)) return false;
        $actuser = self::_getUserInstance();
        $actuser->activeuserbyname($userInfo['username']);
        # 本系统只有邮箱可修改，密码会在下一次登录时修改
        $actuser->updatefield('email', $userInfo['email']);
        $actuser->updatedb();
        return true;
    }
    
    /**
     * 更新头像
     * 
     * @todo 因本系统头像字段不固定，暂时不做更新头像通信
     */ 
    protected function _uploadAvatar()
    {
        #$avatar = $this->_getPwUserAvatar();
    }
    
    /**
     * 编辑用户积分(想测试在后台积分修改时就会发送请求过来~~)
     * 
     * @return bool 修改成功返回true，获取不到用户信息时返回false
     * @todo        暂时不对该积分同步
     */ 
    protected function _editCredit()
    {
    }
    
    /**
     * 同步用户未读私信
     */ 
    protected function _editMessageNum()
    {
        global $mcharset;
        $db = _08_factory::getDBO();
        # 获取多条未读对话
        $messages = $this->_getPwUserMessage();
        foreach($messages as &$message)
        {
            if (empty($message))
            {
                continue;
            }
            $message['last_message'] = unserialize($message['last_message']);
        
            $title = mb_substr($message['last_message']['content'], 0, 20, $mcharset);
            # 因这没传递私信ID过来，所以通过搜索私信的办法获取私信ID，然后保存
            $message_info = WindidApi::api('message')->searchMessage(
                array('fromuid'=>$message['last_message']['from_uid'], 'keyword'=>$message['last_message']['content']),
                0, 1
            );
            $message_id = @$message_info[1][0]['message_id'];
            # 把外部发送的私信保存到本系统
            $db->insert(
                '#__pms',
                array(
                    'fromuser' => $message['last_message']['from_username'], 
                    'fromid' => $message['last_message']['from_uid'], 
                    'toid' => $message['last_message']['to_uid'], 
                    'title' => $title, 
                    'content' => $message['last_message']['content'], 
                    'pmdate' => $message['modified_time'],
                    cls_Windid_Message::PW_MESSAGE_ID => (int)$message_id
                )
            )->exec();
        }
        
        unset($db);
    }
    
    /**
     * 删除用户，服务端没实现批量删除功能，只能一个个删除
     * 
     * @return bool 删除成功返回true，否则返回false
     */ 
    protected function _deleteUser()
    {
        $flag = false;
        if( $this->_config['uid'] > 1 )
        {
            $actuser = self::_getUserInstance();
            $actuser->activeuser( self::_getMidByPwUid( $this->_config['uid'] ) );
            $flag = $actuser->delete();
            unset($actuser);
        }        
        
        return ($flag ? true : false);
    }
    
    /**
     * 通过WINDID服务端的用户ID获取本系统对应的用户ID
     * 
     * @param  int $uid WINDID服务端用户ID
     * @return int      返回本系统用户ID
     * @since  1.0
     */
    protected static function _getMidByPwUid( $uid )
    {
        $user = self::_getUserInstance();
        $info = $user->getUserInfo('mid', cls_Windid_Message::PW_UID . ' = ' . (int)$uid);
        return $info['mid'];
    }
	
	/**
	 * 根据请求会员ID向服务端获取PW的会员信息
     * 
     * @param  string $type 传递WindidUserApi类里，以 getUser 开头的家族API后缀
	 * @return array        返回获取到的用户信息
     * 
     * @since  1.0
	 */
	protected function _getPwUser( $type = '' )
	{
        if ( !$this->_checkUID() )
        {
            $this->_showError( self::FAIL );
        }
        $api = WindidApi::api('user');
        return call_user_func( array($api, 'getUser' . @ucfirst($type)), $this->_config['uid'] );
	}
    
    /**
	 * 根据请求会员ID向服务端获取PW的会员头像
     * 
     * @param  string $size 头像大小，big-大，middle-中，small-小
	 * @return array        返回获取到的用户头像信息
     * 
     * @since  1.0
	 */
	protected function _getPwUserAvatar($size = 'middle')
	{
        if ( !$this->_checkUID() )
        {
            $this->_showError( self::FAIL );
        }
        $api = WindidApi::api('avatar');
        return $api->getAvatar($this->_config['uid'], $size);
	}
    
    /**
	 * 根据请求会员ID向服务端获取PW的多条未读对话
     * 
	 * @return array 返回获取到的用户私信
     * 
     * @since  1.0
	 */
    protected function _getPwUserMessage()
    {
        if ( !$this->_checkUID() )
        {
            $this->_showError( self::FAIL );
        }
        $api = WindidApi::api('message');
        return $api->getUnreadDialogsByUid($this->_config['uid']);
    }
    
    /**
     * 检查UID是否合法
     * 
     * @return bool 如果合法返回TRUE，否则返回FALSE
     */
    protected function _checkUID()
    {
        if ( empty($this->_config['uid']) || (isset($this->_config['uid']) && ($this->_config['uid'] <= 0)) )
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取用户类句柄
     * 
     * @return object 返回用户类的实例句柄
     */
    protected static function _getUserInstance()
    {
        if(! (self::$userInstance instanceof cls_userinfo) )
        {
            self::$userInstance = new cls_userinfo;
        }
        
        return self::$userInstance;
    }
	
    /**
     * 打印错误信息
     * 
     * @param string $message 要打印的信息
     * @since 1.0
     */ 
	protected function _showError($message = '', $referer = '', $refresh = false)
    {
		exit($message);
	}

    /**
     * 打印信息
     * 
     * @param string $message 要打印的信息
     * @since 1.0
     */ 
	protected function _showMessage($message = '', $referer = '', $refresh = false)
    {
		exit($message);
	}
    
    public function __call($name, $arguments)
    {
        $this->_showError( self::FAIL );
    }
}