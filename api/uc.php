<?php

define('UC_CLIENT_VERSION','1.6.0');
define('UC_CLIENT_RELEASE','20110501');
define('API_DELETEUSER', 1);		    //note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);		    //note 用户改名 API 接口开关
define('API_GETTAG', 1);		        //note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);		        //note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		        //note 同步登出 API 接口开关
define('API_UPDATEPW', 1);		        //note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);    	//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);	  	    //note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);		    //note 更新应用列表 开关
define('API_UPDATECLIENT', 1);		    //note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		    //note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);	    //note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1);		        //note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1);	//note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');



defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', @get_magic_quotes_gpc());
//uc_log('flag_before_general.inc.php');
require(dirname(dirname(__FILE__)).'/include/general.inc.php');
define('UC_CLIENT_ROOT', _08_INCLUDE_PATH . 'uc_client' . DS);
define('UC_DATADIR', UC_CLIENT_ROOT . 'data' . DS);
define('UC_DATA_CACHE_DIR', UC_DATADIR . 'cache' . DS);
//uc_log('flag_after_general.inc.php'); 
if(!cls_ucenter::init()) exit(API_RETURN_FAILED);
if(!$enable_uc) exit(API_RETURN_FAILED);
$_DCACHE = $get = $post = array();
$code = @$_GET['code'];
if($code == 'uclog_view'){ // 用于管理员调试(webparam)权限
	include_once M_ROOT.'include/admina.fun.php';
	if($re = $curuser->NoBackFunc('webparam')) cls_message::show($re);
	
	$date = empty($date) ? date('Y_md') : $date;
	$logfile = $cms_abs."dynamic/debug/uc_{$date}.log";
	echo "\n<pre>path: $logfile:\n查看其它日期的记录,加参数如：&date=2008_0101\n<hr>"; 
	echo @file_get_contents($logfile); 
	echo "\n</pre>";
	exit();
}
parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
if($uc_debug) uc_log('@top'); // * 调试代码
if(MAGIC_QUOTES_GPC) $get = _stripslashes($get);
$timestamp = time();
$authorization = md5($authkey);
if(empty($get)) exit('Invalid Request');
if($timestamp - $get['time'] > 3600) exit('Authracation has expiried');
$action = $get['action'];
require_once M_ROOT.'include/uc_client/lib/xml.class.php';
$post = xml_unserialize(file_get_contents('php://input'));

if(in_array($action, array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcredit', 'getcreditsettings', 'updatecreditsettings'))) {
	$GLOBALS['db'] = $db;
	$GLOBALS['tablepre'] = $tblprefix;
	$uc_note = new uc_note();
	exit($uc_note->$action($get, $post));
}else exit(API_RETURN_FAILED);

class uc_note {

	var $db = '';
	var $tablepre = '';
	var $appdir = '';
    private static $__ucdb = null;

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once M_ROOT.'include/uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
		$this->appdir = M_ROOT;
		$this->db = $GLOBALS['db'];
		$this->tablepre = $GLOBALS['tablepre'];
	}

	function test($get, $post) {
		global $uc_connect;
		if($uc_connect == 'mysql')
        {
		    self::getUcDBO();
			$query = self::$__ucdb->link ? self::$__ucdb->query('SHOW COLUMNS FROM ' . UC_DBTABLEPRE . 'members', 'SILENT') : 0;
            
			if(!$query){
				uc_log("@mysql connect error!($query)");
				return API_RETURN_FAILED;
			}
		}
		return API_RETURN_SUCCEED;
	}

    /**
     * 同步删除本系统用户
     * 
     * @todo 该功能目前检测到Ucenter服务端发送过来的$get['ids']并不是用户ID，所以暂时关闭该功能，待Ucneter完善后再开
     */
	function deleteuser($get, $post) {
		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);
        $uids = explode(',', str_replace("'", '', stripslashes($get['ids'])));
            
        if ( !empty($uids) && is_array($uids) )
        {
            $uids = array_filter(array_map('intval', $uids));
            
            $this->db->select('mid')->from('#__members')->where(cls_ucenter::UC_UID . ' IN(' . implode(', ', $uids) . ')')->exec();
            $mids_array = array();
            while ( $row = $this->db->fetch() )
            {
                if ( $row['mid'] && ($row['mid'] != 1) )
                {
                    $mids_array[] = $row['mid'];
                }
            }
            
            $mids_str = implode(', ', $mids_array);
            
            # 检测过滤后的值
            if($mids_str)
            {
    			$this->db->query("DELETE FROM ".$this->tablepre."members WHERE mid IN ($mids_str)",'UNBUFFERED');
    			$this->db->query("DELETE FROM ".$this->tablepre."members_sub WHERE mid IN ($mids_str)",'UNBUFFERED');
    			$mchannels = cls_cache::Read('mchannels');
    			foreach($mchannels as $k => $v) $this->db->query("DELETE FROM ".$this->tablepre."members_$k WHERE mid IN ($mids_str)",'UNBUFFERED');
    		}
        }
		
		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post) {
		if(!API_RENAMEUSER) return API_RETURN_FORBIDDEN;
		$oldusername = $get['oldusername'];$usernamenew = $get['newusername'];
		$this->db->query("UPDATE {$this->tablepre}members SET mname='$usernamenew' WHERE mname='$oldusername'");
		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
		if(!API_GETTAG) return API_RETURN_FORBIDDEN;
	}

	function synlogin($get, $post){
		if(!API_SYNLOGIN) return API_RETURN_FORBIDDEN;
		cls_HttpStatus::trace('P3P');
		$mname = $get['username'];
		if($cmember = $this->db->fetch_one("SELECT mid,mname,password,email FROM ".$this->tablepre."members WHERE mname='$mname' AND checked=1")){
		    $acuser = new cls_userinfo();            # 同步对应服务端与客户端用户ID
            $acuser->activeuser($cmember['mid']);
            $acuser->updatefield(cls_ucenter::UC_UID, $get['uid']);
            $acuser->updatedb();
			$acuser->autopush(); //自动推送
            
			$acuser->LoginFlag($cmember['mid'],$cmember['password']);
		}
	}

	function synlogout($get, $post) {
		if(!API_SYNLOGOUT) return API_RETURN_FORBIDDEN;
		cls_HttpStatus::trace('P3P');
		cls_userinfo::LogoutFlag();
	}

	function updatepw($get, $post) {
		if(!API_UPDATEPW) return API_RETURN_FORBIDDEN;
		$username = $get['username'];
		$password = $get['password'];
		if(empty($password)) return API_RETURN_SUCCEED; //在某些情况下,uc传来的密码为空,这样执行肯定出问题; 而正确的操作已经在本系统执行,这里直接退出
		$password = _08_Encryption::password($password);
		$this->db->query("UPDATE {$this->tablepre}members SET password='$password' WHERE mname='$username'");
		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) {
		if(!API_UPDATEBADWORDS) return API_RETURN_FORBIDDEN;
        self::updateCache('badwords', $post);
		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) {
		if(!API_UPDATEHOSTS) return API_RETURN_FORBIDDEN;
        self::updateCache('hosts', $post);
		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		if(!API_UPDATEAPPS) return API_RETURN_FORBIDDEN;
        self::updateCache('apps', $post);
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		if(!API_UPDATECLIENT) return API_RETURN_FORBIDDEN;
        self::updateCache('settings', $post);
		return API_RETURN_SUCCEED;
	}
    
    /**
     * 更新服务端配置到本系统缓存文件
     * 
     * @param string $key  要更新的标识名，有hosts, apps, settings, badwords
     * @param array  $post 要更新的数据
     */
    public static function updateCache($key, array $post)
    {
        $cachefile = UC_DATA_CACHE_DIR . "$key.php";
        $fp = fopen($cachefile, 'w');
        $s = "<?php\r\n";
        $s .= "\$_CACHE['$key'] = ".var_export($post, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);
    }

	function updatecredit($get, $post) {
		if(!API_UPDATECREDIT) return API_RETURN_FORBIDDEN;
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];
		$time = $get['time'];
		$ucresult = uc_get_user($uid,1);
		if(!is_array($ucresult)) return API_RETURN_FAILED;
		$mname = $ucresult[1];
		$row = $this->db->fetch_one("SELECT mid,mname FROM ".$this->tablepre."members WHERE mname='$mname'");
		$this->db->query("UPDATE ".$this->tablepre."members SET currency$credit=currency$credit+'$amount' WHERE mid='$row[mid]'");
		include_once M_ROOT.'dynamic/cache/currencys.cac.php';
		$record = mhtmlspecialchars($time."\t".$row['mid']."\t".$row['mname']."\t".$currencys[$credit]['cname']."\t".'+'."\t".$amount."\t".'ucenter currency exchange');
		record2file('currencylog',$record);
		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) {
		if(!API_GETCREDIT) return API_RETURN_FORBIDDEN;
		$uid = intval($get['uid']);
		$credit = intval($get['credit']);
		$currencys = cls_cache::Read('currencys');
		if(empty($currencys[$credit])) return 0;
		$ucresult = uc_get_user($uid,1);
		if(!is_array($ucresult)) return 0;
		$mname = $ucresult[1];
		return $this->db->result_first("SELECT currency$credit FROM ".$this->tablepre."members WHERE mname='$mname'");
	}

	function getcreditsettings($get, $post){
		if(!API_GETCREDITSETTINGS) return API_RETURN_FORBIDDEN;
		include_once M_ROOT.'dynamic/cache/currencys.cac.php';
		$credits = array();
		foreach($currencys as $k => $v)  $credits[$k] = array(strip_tags($v['cname']),$v['unit']);
		return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post) {
		if(!API_UPDATECREDITSETTINGS) return API_RETURN_FORBIDDEN;
		$credit = $get['credit'];
		$outextcredits = array();
		if($credit) {
			foreach($credit as $appid => $credititems) {
				if($appid == UC_APPID) {
					foreach($credititems as $value) {
						$outextcredits[] = array(
							'appiddesc' => $value['appiddesc'],
							'creditdesc' => $value['creditdesc'],
							'creditsrc' => $value['creditsrc'],
							'title' => $value['title'],
							'unit' => $value['unit'],
							'ratiosrc' => $value['ratiosrc'],
							'ratiodesc' => $value['ratiodesc'],
							'ratio' => $value['ratio']
						);
					}
				}
			}
		}
		$this->db->query("REPLACE INTO ".$this->tablepre."mconfigs (varname,value,cftype) VALUES ('outextcredits','".addslashes(serialize($outextcredits))."','uc');", 'UNBUFFERED');
		@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'cache.fun.php';
		cls_CacheFile::Update('mconfigs');
		return API_RETURN_SUCCEED;
	}
    
    /**
     * 获取UC数据句操作句柄
     */
    private static function getUcDBO()
    {
        self::$__ucdb =& _08_factory::getDBO( 
            array('dbhost' => UC_DBHOST, 'dbuser' => UC_DBUSER, 'dbpw' => UC_DBPW, 'dbname' => UC_DBNAME, 
                  'tblprefix' => UC_DBTABLEPRE, 'pconnect' => UC_DBCONNECT, 'dbcharset' => UC_DBCHARSET)
        );        
        return self::$__ucdb;
    }
}

function _setcookie($var, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,
		$life ? $timestamp + $life : 0, $cookiepath,
		$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function _stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function _08cms_debug($arr){
	@include 'debugs.php';
	$debugs = empty($debugs) ? array() : $debugs;
	$debugs[] = $arr;
	if(@$fp = fopen('debugs.php','wb')){
		fwrite($fp,"<?php\n\$debugs = ".var_export($debugs,TRUE)." ;\n?>");
		fclose($fp);
	}
}
function uc_log($msg=''){
	global $get,$code,$uc_connect;
	if($code=='uclog_view') return;
	if(!defined('M_ROOT')) define('M_ROOT',dirname(dirname(__FILE__)));
	if(!defined('UC_KEY')) define('UC_KEY','(null uckey)');
	if(!is_dir(M_ROOT.'dynamic/debug/')) @mmkdir(M_ROOT.'dynamic/debug/',0);
	$logfile = M_ROOT.'dynamic/debug/uc_'.date('Y_md').'.log';
	!is_file($logfile) && @touch($logfile);
	$dold = "\n\r\n".@file_get_contents($logfile);
	$data = var_export($get,TRUE);
	$str = date('Y-m-d H:i:s')." --- $msg\n";
	$str .= "method=$uc_connect\n";
	$str .= "key=".UC_KEY."\n";
	$str .= "code=$code\n$data";
	$str .= $dold;
	@file_put_contents($logfile,$str);
}


?>