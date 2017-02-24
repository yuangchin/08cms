<?php
/**
* ucenter会员整合类
* 
*/
class cls_ucenter{
    /**
     * UCenter服务端在本系统的数据表里的用户字段标识名
     * 
     * @var string
     */
    const UC_UID = 'uc_uid';
    
	function __construct(){//备用，有可能之后需要new
		self::init();
	}
	
	//初始化
	public static function init(){
		if(!cls_env::mconfig('enable_uc')) return false;
		if(!defined('UC_CONNECT')){
			self::_define_cont();
			require_once _08_INCLUDE_PATH . 'uc_client/client.php';
		}
		return true;
	}
	
	//会员同步退出
	public static function logout(){
		if(!self::init()) return;
		self::_hidden(uc_user_synlogout());
	}
	
	//会员同步登录
	public static function login($uid){
		if(!self::init() || $uid <= 0) return false;
		self::_hidden(uc_user_synlogin($uid));
		return true;
	}
	
	//删除会员
	public static function delete($mnames = array()){
		if(!self::init()) return false;
		if(!$mnames) return false;
		$uids = array();
		foreach($mnames as $k){
			$re = uc_get_user($k);
			is_array($re) && $uids[] = $re[0];
		}
		$uids && uc_user_delete($uids);
		return true;
	}
	
	//用于新用户的注册，$synlogin注册成功则同步登录
	//返回错误信息或uid
	public static function register($username,$password,$email = '',$synlogin = FALSE)
    {
		$re = array('error' => '');
		if(!self::init()) return $re['error'];
		$uid = uc_user_register($username,$password,$email);
		if($uid <= 0) {
			if($uid == -1) {
				$re['error'] = '[Ucenter] 用户名不合法';
			}elseif($uid == -2) {
				$re['error'] = '[Ucenter] 包含不允许注册的词语';
			}elseif($uid == -3) {
				$re['error'] = '[Ucenter] 用户名已经存在';
			}elseif($uid == -4) {
				$re['error'] =  '[Ucenter] Email格式有误';
			}elseif($uid == -5) {
				$re['error'] = '[Ucenter] Email不允许注册';
			}elseif($uid == -6) {
				$re['error'] = '[Ucenter] 该Email已经被注册';
			}else {
				$re['error'] = '[Ucenter] 错误操作';
			}
            cls_message::show( $re['error'], M_REFERER );
		}elseif($synlogin){//登录成功需要同步登录
			self::login($uid);
		}
		return $uid;
	}
	
	//修改密码及email，不修改留空$newpw或$email
	//返回错误信息
	public static function edit($username,$newpw = '',$email = ''){
		$re = '';	
		if(!self::init()) return $re;
		if(!$newpw && !$email) return $re;
		$ucre = uc_user_edit($username, '', $newpw, $email, 1);
		switch($ucre){
	#		case 0:
			case -1:
				$re = '[Ucenter] 修改失败';
				#$re = '[Ucenter] 密码或Email没有做任何修改';
			break;
			case -4:
				$re = '[Ucenter] Email格式有误';
			break;
			case -5:
				$re = '[Ucenter] Email不允许注册';
			break;
			case -6:
				$re = '[Ucenter] Email已经被注册';
			break;
			case -7:
				$re = '[Ucenter] 该用户受保护无权限更改';
			break;
		}
		return $re;		
	}
	
	//用户的登录验证，如UC不存在该会员(-1)，后续将注册新会员到UC，所以uid>0或udi=-1时都不要返回错误
	//需要返回UC中资料，用于本站加会员或更新本站会员资料
	public static function checklogin($username,$password){
		$re = array('error' => '');
		if(!self::init()) return $re;
		list($re['uid'], $re['username'], $re['password'], $re['email']) = uc_user_login($username,$password);
		if($re['uid'] > 0) {
			# '登录成功';
		} elseif($re['uid'] == -1) {
			# '用户不存在,或者被删除';
		} elseif($re['uid'] == -2) {
			$re['error'] = '[Ucenter] 密码错误';
		} else {
			$re['error'] = '[Ucenter] 未定义';
		}
        
		return $re;
	}
	
	//用于检查用户输入的用户名的合法性，返回错误信息
	public static function checkname($mname){
		$re = '';	
		if(!self::init()) return $re;
		$uid = uc_user_checkname($mname);
		switch($uid){
			case -1:
				$re = '[Ucenter] 用户名不合法';
			break;
			case -2:
				$re = '[Ucenter] 包含不允许注册的词语';
			break;
			case -3:
				$re = '[Ucenter] 用户名已经存在';
			break;
		}
		return $re;		
	}
	
	//用于检查用户输入的 Email 的合法性，返回错误信息
	public static function checkemail($email){
		$re = '';	
		if(!self::init()) return $re;
		$ucresult  = uc_user_checkemail($email);
		switch($ucresult ){
			case -4:
				$re = '[Ucenter] Email 格式有误';
			break;
			case -5:
				$re = '[Ucenter] Email 不允许注册';
			break;
			case -6:
				$re = '[Ucenter] 该 Email 已经被注册';
			break;
		}
		return $re;		
	}
	
	private static function _hidden($html){
		echo "<div style=\"display:none\">$html</div>";
	}
	
	
	private static function _define_cont(){
		define('UC_CONNECT', cls_env::mconfig('uc_connect')); //mysql/post
		define("UC_DBHOST", cls_env::mconfig('uc_dbhost')) ;
		define("UC_DBUSER", cls_env::mconfig('uc_dbuser')) ;
		define("UC_DBPW", cls_env::mconfig('uc_dbpwd')) ;
		define("UC_DBNAME", cls_env::mconfig('uc_dbname')) ;
		define('UC_DBCHARSET', cls_env::GetG('dbcharset'));
		define("UC_DBTABLEPRE", '`'.cls_env::mconfig('uc_dbname').'`.'.cls_env::mconfig('uc_dbpre')) ;
		define('UC_DBCONNECT', '0');
		define("UC_KEY", cls_env::mconfig('uc_key')) ;
		define("UC_API", cls_env::mconfig('uc_api')) ;
		define('UC_CHARSET', cls_env::getBaseIncConfigs('mcharset'));
		define("UC_IP", cls_env::mconfig('uc_ip')) ;
		define('UC_APPID', cls_env::mconfig('uc_appid')) ;
		define('UC_PPP', '20');
	}
}
