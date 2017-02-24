<?php
/**
* 跟服务器环境配置、超全局变量、系统全局设置等相关的处理方法
* 来访过滤，安全预处理等
*/
defined('M_COM') || exit('No Permission');
abstract class cls_envBase{
	
    const _08_HASH = '_08_hash';
    private static $hash = '';
    public static $__baseIncConfigs = array();
    private static $globals = NULL;
	protected static $_08_GET = NULL;
	protected static $_08_POST = NULL;
	protected static $_08_COOKIE = NULL;

    /**
     * 加载加密核心
     * 
     */
    public static function LoadZcore(){
		include _08_EXTEND_LIBS_PATH .'classes'.DS.'zcore'.DS.'cecore'.(PHP_VERSION < '5.3.0' ? '' : '_1').'.cls.php';
    }

    /**
     * XSS转码
     * 
     * @param mixed $values     要转码的变量值
     * @param bool  $delete_rep 是否删除特殊字符
     * @param bool  $derep      是否反编码之前编码过的数据
     * @param int   $quotes     htmlspecialchars函数的第二个参数{@link http://docs.php.net/manual/zh/function.htmlspecialchars.php}
     */
    public static function repGlobalValue( &$values, $delete_rep = false, $derep = false, $quotes = ENT_QUOTES ){
		if(is_null(self::$globals)){
			self::$globals = array_merge( (array) self::_GET_POST(), (array) self::_COOKIE());
			self::$globals = cls_array::_array_multi_to_one(self::$globals, true);
		}
        
        if ( is_array($values) )
        {
            cls_array::_array_uasort($values);
            cls_array::_array_uasort(self::$globals);

            $newValues = array();
            foreach($values as $key => $value)
            {
                if ( is_array($value) || cls_Array::_in_array(self::$globals, array($value), 2) || 
                     cls_Array::_in_array(array_keys(self::$globals), array($value), 2) )
                {
                    self::repGlobalValue($value, $delete_rep, $derep, $quotes);
                }elseif(isset(self::$globals[$key])){
                    $_values = array($key => $value);
                    $value = self::repAction($_values, $delete_rep, $derep, $quotes);
				}
                $key = mhtmlspecialchars($key);
                $newValues[$key] = $value;
            }
            
            $values = $newValues;
        }
        else
        {
            # 开始转码
            $values = self::repAction($values, $delete_rep, $derep, $quotes);
        }
    }
    
    /**
     * 开始编码XSS，注：不支持$delete_rep后的字符
     */
    private static function repAction( $value, $delete_rep = false, $derep = false, $quotes = ENT_QUOTES )
    {
        if ( is_array($value) )
        {
            $key = key($value);
            $value = current($value);
        }
        if ( $delete_rep )
        {
            $value = preg_replace('@<script.*>.*</script>@isU', '', $value);
        }
        
        # 如果编码已经编码过的数据
        if ( $derep )
        {
            return mhtmlspecialchars($value, $quotes, $delete_rep);
        }
            
        foreach ( self::$globals as $_key => $global)
        {
            if ( !is_string($global) || (false === @strpos($global, $value)) && (false === @strpos($value, $global)) &&
                 (false === @strpos($value, $_key)) )
            {
                continue;
            }
            
            if ( isset($key) && ($key == $_key) || (strlen($global) > strlen($value)) )
            {
                $value = self::filteringCodingTable($value, $delete_rep, $quotes);
            }
            else
            {
                if ( false !== @strpos($value, $_key) )
                {
                    $value = str_replace($_key, self::filteringCodingTable($_key, $delete_rep, $quotes), $value);
                }
                else
                {
                	$value = str_replace($global, self::filteringCodingTable($global, $delete_rep, $quotes), $value);
                }
            }
        }
        
        return $value;
    }
    
    /**
     * 反编码经过 htmlspecialchars 函数编码过的字符串
     * 
     * @param  mixed  $values   已经编码过的字符串或数组
     * @param  array  $varnames 要反编码的变量名称与值，如果未指定则反编码所有
     * @since  nv50
     */
    public static function deRepGlobalValue($values, array $varnames = array(), $quotes = ENT_QUOTES )
    {
        if ( is_array($values) )
        {
            foreach($values as $key => &$value )
            {
                $value = self::deRepGlobalValue($value, $varnames, $quotes);               
            }
        }
        else
        {
        	$values = (string) $values;
            if ( !empty($varnames) )
            {
                foreach ( $varnames as $value ) 
                {
                    if(empty($value)) continue;
					if ( false !== strpos($values, $value) )
                    {
                        $values = str_replace($value, htmlspecialchars_decode($value, $quotes), $values);
                    }
                }
            }
            else
            {
                $values = htmlspecialchars_decode($values, $quotes);            	
            }
        }
		return $values;
    }
    
    /**
     * 过滤已经编码过的字符
     * 
     * @param  string $string 要编码的字符
     * @return string         编码后的字符
     * 
     * @since  1.0
     */
    private static function filteringCodingTable( $string, $delete_rep = false, $quotes = ENT_QUOTES )
    {
        $translation_table = get_html_translation_table();
        $array = array();
        for ($i = 0; $i < count($translation_table); ++$i) 
        {
            $array[] = "[__08cms__$i]";
        }
        # 把已经编码过的数据用自定义字符替换
        $string = str_replace($translation_table, $array, $string);
        # 开始编码数据
        if(0 === stripos($string, 'http'))
        {
			$string = self::repGlobalURL($string);
		}
        else
        {
			$string = mhtmlspecialchars($string, $quotes, $delete_rep);
		}
        # 把自定义字符还原回已经编码过的数据
        $string = str_replace($array, $translation_table, $string);
        
        return $string;
    }
    
    /**
     * 编码URL
     * 
     * @param  string $url    要编码的URL
     * @return string $my_url 编码后的URL
     * 
     * @since  1.0
     */
    public static function repGlobalURL( $url )
    {
        $my_url = '';
        $url = str_replace('&', '[--08cms--]', $url);
        $url_info = parse_url($url);
        if ( isset($url_info['scheme']) )
        {
            $my_url .= $url_info['scheme'] . '://';
        }
        
        if ( isset($url_info['user']) )
        {
            $my_url .= $url_info['user'] . ':';
        }
        
        if ( isset($url_info['pass']) )
        {
            $my_url .= $url_info['pass'] . '@';
        }
        
        if ( isset($url_info['host']) )
        {
            $my_url .= $url_info['host'];
        }

        if ( isset($url_info['port']) )
        {
            $my_url .= ':'.$url_info['port'];
        }
        
        if ( isset($url_info['path']) )
        {
            $my_url .= mhtmlspecialchars($url_info['path']);
        }
        
        if ( isset($url_info['query']) )
        {
            $my_url .= '?' . mhtmlspecialchars(urldecode($url_info['query']));
        }
        
        if ( isset($url_info['fragment']) )
        {
            $my_url .= '#' . mhtmlspecialchars($url_info['fragment']);
        }
        
        $my_url = str_replace('[--08cms--]', '&', $my_url);
        return $my_url;
    }
	
	public static function OnlineIP(){
		if(isset($_SERVER['HTTP_X_REAL_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_REAL_FORWARDED_FOR'])){
			$onlineip = $_SERVER['HTTP_X_REAL_FORWARDED_FOR'];
		}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])){
			$onlineip = $_SERVER['HTTP_CLIENT_IP'];
		}else{
            if (isset($_SERVER['REMOTE_ADDR']))
            {
			     $onlineip = $_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
            }
            else
            {
            	$onlineip = '127.0.0.1';
            }
		}
		preg_match("/[\d\.]{7,15}/",$onlineip,$onlineipmatches);
		$onlineip = isset($onlineipmatches[0]) ? $onlineipmatches[0] : '';
		return $onlineip;
	}
	
	public static function IpBanned($onlineip){
		if($bannedipstr = implode('|',cls_cache::Read('bannedips'))){
			if(preg_match("/^($bannedipstr)$/",$onlineip)) return true;
		}
		return false;
	}
	
	//是否搜索引擎来访
	public static function IsRobot($user_agent = ''){
		$kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
		$kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
		$user_agent || $user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
		if(!in_str('http://',$user_agent) && preg_match("/($kw_browsers)/i",$user_agent)){
			return false;
		}elseif(preg_match("/($kw_spiders)/i",$user_agent) || (class_exists('_08_Browser') && _08_Browser::getInstance()->isRobot())) return true;
		return false;
	}
	
	public static function RobotFilter(){
		if(defined('NOROBOT') && ISROBOT) exit(header("HTTP/1.1 403 Forbidden"));
	}
	
	//只允许不带附加参数的页面允许搜索引擎
	//$Params页面外部参数，$AllowKeys允许传的参数名(空为不允许任何参数)
	public static function AllowRobot($Params = array(),$AllowKeys = array()){
		if((defined('ISROBOT') && !ISROBOT) || !$Params) return;
		foreach($Params as $k => $v){
			if(!in_array($k,$AllowKeys)){
				header("HTTP/1.1 403 Forbidden");
				exit("[cls_envBase::AllowRobot()]NetworkError: 403 Forbidden"); //可能手动修改UA导致停止显示，这里显示信息便于调试
			}
		}
	}    
	
	public static function GLOBALS()
    {
	    if ( isset($_SERVER['REQUEST_URI']) )
        {
            $_SERVER['REQUEST_URI'] = self::maddslashes(rawurldecode($_SERVER['REQUEST_URI']));
        }
		if(isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) exit('08cms Error');
	}
	
    /**
     * 获取$_GET参数
     * 
     * @param  mixed $varnames 想要获取的参数名称
     * @return array           获取到的$_GET参数数组
     * 
     * @since  nv50
     */
	public static function &_GET( $varnames = '' ){
	    # 让MVC里使用不在MVC架构上的函数时也能通过该方法获取MVC的GET参数
	    if ( class_exists('cls_frontController') && cls_frontController::checkActionMVC() )
        {
            $frontController = cls_frontController::getInstance(array_merge($_POST, $_GET));
            $_GET = $frontController->getParams();
        }
		if(is_null(self::$_08_GET)){
			//将伪静态URL中的变量转到$_GET
			if(!empty($_SERVER['QUERY_STRING']) && (strpos($_SERVER['QUERY_STRING'],'/ajax/')===0
			  || strpos($_SERVER['QUERY_STRING'],'/editor/')===0
			  || strpos($_SERVER['QUERY_STRING'],'/paygate/')===0
			  || strpos($_SERVER['QUERY_STRING'],'/upload/')===0
			  || strpos($_SERVER['QUERY_STRING'],'/weixin/')===0)){ // Ajax等访问，不处理伪静态URL
			//if(defined('_08CMS_AJAX_EXEC')){ // 不能用类似这个判断,执行到这里时,ajax_controller还没有加载
				$dealVURL = 0;
			}elseif(defined('UN_VIRTURE_URL')){ // /index.php过来，处理伪静态URL
				$dealVURL = 1;	
			}else{ // 其它不处理
				$dealVURL = 0;	
			} 
			if($QueryStringParams = self::QueryStringToArray($dealVURL)){
				$_GET = $QueryStringParams;
			}
			self::$_08_GET = self::_PreDealGP($_GET);
		}
        
        // 对$_GET全局数组重写，防止直接用$_GET['xxx']来获取变量值来跳过安全机制
        $_GET = self::$_08_GET;
        self::initVarnames($varnames, self::$_08_GET);
		return self::$_08_GET;
	}
    
    /**
     * 初始化数组对象元素（该方法可减少未初始化变量使用的报错）
     * 如果该变量不存在于$params数组里则自动初始化为null
     * 
     * @param mixed $varnames 想要初始化的参数名称
     * @param array $params   从该对象里初始化元素
     * 
     * @since nv50
     */
    public static function initVarnames($varnames, array &$params)
    {
        if ( !is_array($varnames) )
        {
            $varnames = array_map('trim', array_filter(explode(',', (string) $varnames)));
        }
        
        foreach ( $varnames as $varname ) 
        {
            if ( !isset($params[$varname]) )
            {
                $params[$varname] = null;
            }
        }
    }	
	
    /**
     * 获取$_POST参数
     * 
     * @param  mixed $varnames 想要获取的参数名称
     * @return array           获取到的$_POST参数数组
     * 
     * @since  nv50
     */
	public static function &_POST( $varnames = '' ){
		if(is_null(self::$_08_POST)){
			self::$_08_POST = self::_PreDealGP($_POST);
		}
        
        self::initVarnames($varnames, self::$_08_POST);
        // 对$_POST全局数组重写，防止直接用$_POST['xxx']来获取变量值来跳过安全机制
        $_POST = self::$_08_POST;
		return self::$_08_POST;
	}
	
    /**
     * 获取$_GET/$_POST参数
     * 
     * @param  mixed $varnames 想要获取的参数名称
     * @return array           获取到的$_GET/$_POST参数数组
     * 
     * @since  nv50
     */
	public static function _GET_POST( $varnames = '' ){
		return self::_GET( $varnames ) + self::_POST( $varnames );
	}
	
	public static function _FILES(){
		if($_FILES) $_FILES = self::maddslashes($_FILES);
	}
	
	public static function &_COOKIE(){
		if(is_null(self::$_08_COOKIE)){
			global $ckpre;
			$cklen = strlen($ckpre);
			if(!empty($_COOKIE)){ 
				foreach((array)$_COOKIE as $k => $v){
					$_COOKIE[$k] = self::maddslashes($v); //所有的都转码一下,以免直接使用$_COOKIE
					if(substr($k,0,$cklen) == $ckpre){ 
						self::$_08_COOKIE[(substr($k,$cklen))] = $_COOKIE[$k];		
					}
				}
			}
		}
		return self::$_08_COOKIE;
	}
	
    /**
     * 取得顶级域名
     * 
     * @param string $url		指定的url
     * 
     * @return string			得到指定url的顶级域名
     * @static
     * @since 1.0
     */ 
	public static function TopDomain($host){
		if(strpos($host,':/')){
			$parts = parse_url($host);
			$host = $parts['host']; 
		}
		$arr = explode('.',$host);
		if(!strpos($host,'.')){ //localhost/pcname
			return '';
		}elseif(is_numeric($arr[count($arr)-1])){ //IP(ipv6未考虑)
			return '';	
		}else{ //域名
			$arr = explode('.',$host); $cnt = count($arr); 
			$part1 = $arr[$cnt-1]; $part2 = $arr[$cnt-2];
			$re = "$part2.$part1"; //默认
			if($cnt>=3){
				if(strlen($part2)==1){ //www.g.cn, www.g.com(我们没有这种客户,除非测试域名)
					//默认
				}elseif(strlen($part2)==2){ //www.dg.gd.cn, www.fyh.cn.com, www.88.com, www.88.cn
					$re = preg_match('/[a-z]{2}/',$part2) ? $arr[$cnt-3].".$re" : $re;
				}elseif(strlen($part1)==2){ //3(+).2 : www.08cms.cn, www.net.cn
					$t3p = '.com.net.org.edu.gov.idx.int.mil.top.cat.biz.pro.tel.xxx.aero.arpa.asia.coop.info.mobi.name.jobs.museum.travel.';
					$re = strpos($t3p,$part2) ? $arr[$cnt-3].".$re" : $re;
				}
			}
			return $re;
		} 
	}
	
	/**
	 * 检测站点是否关闭，及手机版是否关闭
	 *
	 * js.php,ptool.php等js中调用，用$noout = 1不显示原因
	 *
	 * @param  bool    $noout 不要显示关闭原因，默认0:显示原因,1:不显示
	 * @return NULL   (直接输出或停止,无返回)
	 */
	public static function CheckSiteClosed($noout = 0){
		global $cmsclosed,$cmsclosedreason,$enable_mobile;
		if($cmsclosed){
			if(!$noout){
				cls_message::show(empty($cmsclosedreason) ? '网站正在维护，请稍后再连接。': mnl2br($cmsclosedreason));
			}else exit();
		}elseif(defined('IN_MOBILE') && empty($enable_mobile)){
			if(!$noout){
				cls_message::show('手机版尚未开放');
			}else exit();
		}
	}
	
	/**
     * 获取CSRF HASH值
     * 
     * @param  bool   $reset 是否重新设置，true为重新设置，false为获取COOKIE，如果不存在才重新设置
     * 
     * @return string $hash 返回随机生成的hash值
     * @since  1.0
     */
	public static function getHashValue( $reset = false )
    {
        $cookies = self::_COOKIE();
        if ( $reset || empty($cookies[self::_08_HASH]) )
        {
            if ( $reset || empty(self::$hash) )
            {
                $hash = _08_Encryption::password(cls_string::Random(8)); # 构造Hash值
                if ( !headers_sent() )
                {
                    msetcookie(self::_08_HASH, $hash, 3600, true);
                    self::$hash = $hash;
                }
            }
            else
            {
            	$hash = self::$hash;
            }
        }
        else
        {
        	$hash = $cookies[self::_08_HASH];
        }
        
        return $hash;
    }
	
	/**
	 * 获取系统授权中的相关项(如授权域名，授权系统类型，授权码)
	 *
	 * @return string
	 */
	public static function GetLicense($key = 'lic_str'){
		if(!in_array($key,array('lic_domain','lic_type','lic_str',))) return '';
		$certvars = cls_cache::cacRead('certvars');
		return empty($certvars[$key]) ? '' : $certvars[$key];
	}
	
	/**
	 * 从伪静态后的$_SERVER['QUERY_STRING']中得到参数数组
	 *
     * @param  int $un_virtual 伪静态规则处理方式，0-不处理，1-处理
	 *
	 * @return array 无伪静态情况或不执行伪静态则返回空数组
	 */
	public static function QueryStringToArray($un_virtual = 0){
		$ReturnArray = array();
         if (!isset($_SERVER['QUERY_STRING'])) return $ReturnArray;
         elseif($un_virtual && $QueryString = rawurldecode($_SERVER['QUERY_STRING'])){
			 
			 $QueryString = preg_replace("/(\/domain\/[^\/]+)/i",'',$QueryString);#过滤掉域名带有'-'的情况，以免下面的正则匹配到
			 
			if(preg_match("/(\w+)-(.+?)(?:$|\/|\.html)/i",$QueryString)){# 基本兼容伪静态字串之后再附加 &xxx=3 参数的方式
				$QueryString = preg_replace("/(\w+)-(.+?)(?:$|\/|\.html)/is","\\1=\\2&",$QueryString);
				parse_str($QueryString,$ReturnArray);
			}
		}
		return $ReturnArray;
	}
	
	/**
     * 读取$mconfigs的值
     * @param  string  $key 键名，支持'xx.kk.dd'得到$mconfigs['xx']['kk']['dd']
     */
	public static function mconfig($Key = ''){
		$mconfigs = cls_cache::Read('mconfigs');
		if(!($KeyArray = cls_Array::ParseKey($Key))) return $mconfigs;
		return cls_Array::Get($mconfigs,$Key);
    }
	/**
     * 获取$GLOBAL的值
     * @param  string  $key 键名，支持'xx.kk.dd'得到$GLOBALS['xx']['kk']['dd']
     */
	public static function GetG($Key = ''){
		return cls_Array::Get($GLOBALS,$Key);
    }
	
	/**
     * 设置$GLOBAL的值
     * @param  string  $key 键名，支持'xx.kk.dd'设置$GLOBALS['xx']['kk']['dd']
     * @param  string  $Value 值
    */
	public static function SetG($Key = '',$Value = 0){
		cls_Array::Set($GLOBALS,$Key,$Value);
    }
   
    /**
     * 获取base.inc.php文件里的变量值，让其它地方调用尽量少用global
     * 
     * @param  string $var 要获取的变量名称，如果设置了该名只返回该名称变量的值,多个以逗号分开
     * @return array       多个变量或不指定变量返回配置数组，单个变量返回变量值。
     * 
     * @since  nv50
     */
    public static function getBaseIncConfigs( $var = '' )
    {
        if ( empty(self::$__baseIncConfigs) )
        {
			$_KeyArray = array(
			'_08_extend_dir','dbuser','dbpw','dbhost','dbname','pconnect','tblprefix','dbcharset','drivers', 'dbport',
			'mcharset','cms_version','lan_version','ckpre','ckdomain','ckpath','adminemail','phpviewerror','is_menghu','is_bz',
			'excache_prefix','ex_memcache_server','ex_memcache_port','ex_memcache_pconnect','ex_memcache_timeout','ex_eaccelerator','ex_xcache','ex_secache','ex_secache_size',
			);
            include M_ROOT . 'base.inc.php';
			foreach($_KeyArray as $k){
				if(isset($$k)){
					self::$__baseIncConfigs[$k] = $$k;
				}
                else
                {
                	self::$__baseIncConfigs[$k] = null;
                }
			}
        }
        
        # 如果指定了要获取的变量名称则只返回参数传递的变量值
        # 如果var为单变量，则返回该变量值，如果var为多变量(','分隔)，返回数组
       if ( !empty($var) ){
            $vars = array_filter(explode(',', (string) $var));
			if(count($vars) > 1){
				$varValues = array();
				foreach ( $vars as $varName ) 
				{
					$varName = trim($varName);
					$varValues[$varName] = isset(self::$__baseIncConfigs[$varName]) ? self::$__baseIncConfigs[$varName] : NULL;
				}
				return $varValues;
			}else{
				$var = trim($var);
				return isset(self::$__baseIncConfigs[$var]) ? self::$__baseIncConfigs[$var] : NULL;
			}
        }else{
			return self::$__baseIncConfigs;
		}
    }
	
	# 对GP数组进行预处理
	private static function _PreDealGP($SourceArray = array()){
		$ReturnArray = array();
		foreach($SourceArray as $k => $v){
			if($k == 'GLOBALS') exit('08cms Error');
			if($k{0} ==  '_') continue; 
			if(in_array($k,array('infloat','handlekey','aid','caid','mid','chid','mchid','addno','win_id','mincount','maxcount','wmid','field_id'))){
				$v = (int)$v;
			}else{
				$v = self::maddslashes($v);
			}
			$ReturnArray[$k] = $v;
		}
		return $ReturnArray;
	}
	
    /**
     * 在指定的预定义字符(',",\,NULL)前添加反斜杠，支持数组，注意：如不是用于GPC，要让force=1
     *
     * @param  string   $s     原始字符串，可以是数组
     * @param  bool     $force 强制选项
     * @return string   $s     处理后的字符串
     */
    public static function maddslashes($s, $force = 0)
    {
    	defined('QUOTES_GPC') || define('QUOTES_GPC', @get_magic_quotes_gpc());
    	if(!QUOTES_GPC || $force){
    		if(is_array($s)){
    			foreach($s as $k => $v) $s[$k] = self::maddslashes($v, $force);
    		}else $s = addslashes($s);
    	}
    	return $s;
    }
   
    /**
     * 通过gzipenable配置判断Output开启方式,
     * @param  bool     $checkGzip 为true考虑php环境和gzipenable配置
     */
    public static function mob_start($checkGzip=false)
    {   
		if($checkGzip){		  
			if( self::mconfig('gzipenable') && self::gzipenable() )
				{
					ob_start('ob_gzhandler');
				}
				else
				{
					ob_start();
				}   
		}else{
		  ob_start();
		}
    }
    
    /**
     * 判断当前环境能否开启GZIP环境
     * 
     * @return bool 如果环境能开启gzip返回TRUE，否则FALSE
     * 
     * @since  nv50
     */
	public static function gzipenable()
    {
        return (bool) (extension_loaded('zlib') && !ini_get('zlib.output_compression'));
    }
   
    /**
     * 检查环境
     * 
     * @since nv50
     */
    public static function __checkEnvironment()
    {
        if( @ini_get('register_globals') )
        {
            die('为了您的安全，请将PHP.INI中的register_globals设置为Off，否则程序无法继续。');
        }
        
        $basefile = M_ROOT . 'base.inc.php';
        if ( !is_file($basefile) )
        {
            die('base.inc.php不存在，请先上传!');
        }
        
        # 为兼容之前程序运行，暂时先保留该配置判断
        if( !@ini_get('short_open_tag') )
        {
            die('请将PHP.INI中的short_open_tag设置为On，否则程序无法继续。');
        }
		
		# 为了确保默认开启的缓冲区不是gzip方式处理,先保留该配置判断
        if( @ini_get('output_handler') != '' )
        {
            die('请将PHP.INI中的output_handler设置为空，否则程序无法继续。');
        }
		
/*		
		
		if ( !is_file(_08_CACHE_PATH . 'install.lock') )
		{
			header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'index.php')) . 'install/index.php');
			exit;
		}
		
*/		
    }
           
    /**
     * 过滤ClickJacking攻击，注意：该报头有三个值，分别为：
     * DENY               拒绝当前页面加载任何frame页面
     * SAMEORIGIN         则frame页面的地址只能为同源域名下的页面
     * ALLOW-FROM origin  定义允许frame加载的页面地址
     */ 
    public static function filterClickJacking()
    {
        $gets = self::_GET('domain');
		$mconfigs = cls_cache::Read('mconfigs');		
        if (empty($gets['domain']) || !self::setDoMain($gets['domain']))
        {
           if (!preg_match("/{$mconfigs['cms_top']}$/i", @$_SERVER['HTTP_HOST'])){
			  cls_HttpStatus::trace(array('X-Frame-Options' => 'SAMEORIGIN'));
		   }
        }
    }
    
    /**
     * 允许Ajax跨域（只适用于IE8以上），只要在调用JS时多传递一个参数： domain=news.08cms.com   这个域可自定义
     * 
     * @example $.get($cms_abs + "tools/ajax.php?action=get_regcode&domain=" + document.domain, function(data) { .... });
     */
    public static function setDoMain( $domain )
    {
        $mconfigs = cls_cache::Read('mconfigs');
        $domain = (string) $domain;
        if ( isset($_SERVER['SERVER_PORT']) && !empty($domain) && !filter_var($domain, FILTER_VALIDATE_IP) )
        {
            # 只允许同顶级域名的其它域名跨域
            if ( preg_match("/{$mconfigs['cms_top']}$/i", $domain) )
            {
                $domainValue = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $domain;              
                
                cls_HttpStatus::trace(array('Access-Control-Allow-Origin' => $domainValue));
				#cls_HttpStatus::trace(array('X-Frame-Options' => 'ALLOW-FROM ' . $domainValue));
				cls_HttpStatus::trace(array('X-Frame-Options' => 'ALLOWALL'));	//ALLOWALL				
                cls_HttpStatus::trace(array('Access-Control-Allow-Headers' => 'X-Requested-With,X_Requested_With'));
                return true;
            }
        }
        
        return false;
    }
}
