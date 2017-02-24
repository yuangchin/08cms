<?php
/**
   Fafil(Safe fillter), 短信中使用，限制外部地址任意提交。
*/
// TIMESTAMP, M_ROOT
// cls_env::mconfig('cmsurl'), authcode(), cls_env::TopDomain(), mmkdir()
// 

class cls_Safefillter{ 
	
	static $rnd_seed    = 'bbmU-dcxy-Xwrm-ECZ2'; // 安全随机码; !!!为了安全,可经常改改; 任意字符,18~21位,
	static $rnd_varname = 'rnd__08';             // 变量前缀; !!!为了安全,可经常改改; 08系统下,最好以字母开头; 其它系统下可用_开头;
	static $rnd_timeout = 60;                    // 超时时间(min)
	static $rnd_split   = '~';                   // 分割符号,不要用url特殊字符; 可用如：~_-/;|@等; 但不要与系统中用作其它用途的分割号冲突
	static $rnd_debug   = 1;                     // 是否调试...
	
	/* 初始化url
	  Demo : <a href="{$cms_abs}etools/wdadd.php<?php echo '&'.cls_Safefillter::urlInit();?>">我要提问</a> 
	*/
	static function urlInit(){
		return self::$rnd_varname."=".self::_getStamp();
	}
	
	/* 签名url(用于ajax请求)
	  Demo : /tools/safefillter.php?act=surl 
	*/
	static function urlSign(){
		self::refCheck();
		$sign = cls_Safefillter::urlInit();
		echo "var _url_sign = '$sign';";
	}
	
	/* 认证url
	  Demo : cls_Safefillter::urlCheck();
	*/
	static function urlCheck($die=1){
		self::refCheck(); //exit('IP_Fobidden'); 
		$re = self::_chkStamp();
		if($re && $die){ 
			self::_stop(__CLASS__, __FUNCTION__, $re);
		}
		return $re; 
	}
	
	/* 初始化form (??? 不用/index.php?/ajax/入口,可部署在之前的系统)
	  fmid : 表单id
	  Demo : <script type='text/javascript' src='{cms_abs}tools/safefillter.php?act=init&fmid=addcu12'></script>;
	*/
	static function formInit(){
		$fmid = self::_req('fmid');
		$elmid = $fmid.self::$rnd_varname;
		$elmfm = self::$rnd_varname.'_fmid';
		$elms = "<input name='$elmfm' id='$elmfm' type='hidden' value='$fmid'><input name='$elmid' id='$elmid' type='hidden' value=''>";
		echo "document.write(\"$elms\");\n";
		echo "function _{$fmid}_setAjaxVals(v){document.getElementById('$elmid').value = v;}\n"; 
	}
	
	/* ajax设置form (??? 不用/index.php?/ajax/入口,可部署在之前的系统)
	  fmid : 表单id
	  Demo : $.getScript({cms_abs}tools/safefillter.php?act=ajax&fmid=addcu12), function(){
				try{_addcu12_setAjaxVals(_addcu12_stamp);}catch(e){}
			 });
	  原理 : js绑定事件并作相关判断; 
	  		 获取类似{cms_abs}tools/safefillter.php?act=ajax&fmid=addcu12得到js代码并运行; 
			 执行js类似语句_addcu12_setAjaxVals(_addcu12_stamp);
	*/
	static function formAjax(){
		self::refCheck();
		$fmid = self::_req('fmid');
		$stamp = self::_getStamp(0);
		echo "var _{$fmid}_stamp = '$stamp';";
	}
	
	/* 认证form
	  * ref : 来源地址
	  * fmid : 表单id,可省略
	  Demo : cls_Safefillter::urlCheck('register.php','cmsregister');
	*/
	static function formCheck($ref='',$fmid=''){
		self::refCheck($ref);
		$fmid || $fmid = self::_req(self::$rnd_varname.'_fmid');
		if($re = self::_chkStamp($fmid)) self::_stop(__CLASS__, __FUNCTION__, $re);
	}

	//检测是否外部提交过来的Url
	//expath : 路径匹配部分,可为空
	//die : 默认直接die, 如为空则返回用于判断
	//return : 默认直接die; false:不是外部提交来的地址; true(string):相关信息,表示是外部提交或直接输入网址过来
	//demo: if(cls_Safefillter::refCheck('',0)) die("不是来自{$cms_abs}的请求！");
	//demo: if(cls_Safefillter::refCheck('/dgpeace/_php_test.php'));
	static function refCheck($expath='',$die=1){
		$re = '';
		$from = empty($_SERVER["HTTP_REFERER"]) ? '' : $_SERVER["HTTP_REFERER"];
		$froma = parse_url($from);
		//为空:(输入地址等)
		if(empty($from)) $re = 'Null'; 
		// 匹配:主机/域名+端口
		$from = self::_urlParse($from);
		$hnow = self::_urlParse($_SERVER['HTTP_HOST']); //HTTP_HOST = SERVER_NAME:SERVER_PORT
		if(@$from['host']!==@$hnow['host']){ 
			$re = $from['host']; 
		}
		// 匹配:路径
		$npath = cls_env::mconfig('cmsurl'); // 如:/house/
		if($expath) $npath = str_replace(array('///','//'),'/',"$npath/$expath"); 
		if(strlen($npath)>0 && !preg_match('/^'.preg_quote($npath,"/").'/i',$froma['path'])){ 
			$re = $npath;	
		} 
		if($re && $die){ 
			self::_stop(__CLASS__, __FUNCTION__, $re);
		}
		return $re; 
	}

	static function _getStamp($isurl=1){
		$stamp = TIMESTAMP;
		$encMD5 = md5(self::$rnd_seed.$stamp);
		$encAuth = authcode($stamp,'');
		$isurl && $encAuth = urlencode($encAuth);
		$encBoth = $encMD5.self::$rnd_split.$encAuth;
		return $encBoth;
	}
	static function _chkStamp($fmid=''){ 
		$fmid || $fmid = self::_req('fmid');
		$a = explode(self::$rnd_split,self::_req($fmid.self::$rnd_varname)); 
		if(empty($a[0]) || empty($a[1])) return 'Error';
		$stamp = intval(authcode($a[1],'DECODE'));
		$encMD5 = md5(self::$rnd_seed.$stamp); 
		if(TIMESTAMP-$stamp>self::$rnd_timeout || $encMD5!==$a[0]){
			return 'Timeout';
		}
	}

	/*	获取: host(主域名+端口) 和 path(路径)
	--- Demo --- 
	$url1 = "http://m.08cms.com:808/shopinfo.d?m=fbyzm&mobile=13537432146&city=bj#aa=33";
	$url2 = "http://www.08cms.com:808/example/index.php/dir/test.php?aaa=bbb";
	$url3 = "http://192.168.1.11:888/house/dgpeace/_php_test.php?aaaa=bbb";
	$url4 = "http://[2001:410:0:1:250:fcee:e450:33ab]:8443/file.php?aa=bb"; 
	echo 'aab:<pre>'.var_dump(cls_Safefillter::_urlParse($url4)).'</pre><br>';
	*/
	static function _urlParse($url){	
		$aurl = parse_url($url); //var_dump($aurl);
		$top = cls_env::TopDomain(@$aurl['host']);
		if(!empty($top)){ //IP(含ipv6)
			$aurl['host'] = $top;
		}
		$host = @$aurl['host'].(isset($aurl['port']) ? ':'.$aurl['port'] : '');
		$path = empty($aurl['path']) ? '' : $aurl['path'];
		return array('h'=>$host,'p'=>$path);
	}
		
	static function _stop($class,$func,$msg){
		$msg = "$class::$func Fobidden : [$msg]";
		$ip = self::_userIP();
		if(self::$rnd_debug){ //调试
			$dmsg = date('Y-m-d H:i:s')." --- $msg";
			if(!is_dir(M_ROOT.'dynamic/debug/')) @mmkdir(M_ROOT.'dynamic/debug/',0);
			$logfile = M_ROOT.'dynamic/debug/safil_'.date('Y_md').'.sflog';
			!is_file($logfile) && @touch($logfile);
			$dold = "\n\r\n".@file_get_contents($logfile);
			$data = $dmsg." --- ref:".@$_SERVER["HTTP_REFERER"]." --- ua:".$_SERVER['HTTP_USER_AGENT']." --- ip:$ip";
			@file_put_contents($logfile,$data.$dold);
		}
		die($msg." --- ($ip@".date('Y-m-d H:i:s').')');
	}
	
	// 获取客户端IP地址
	static function _userIP($flag=1){
		$a = array('x'=>'HTTP_X_FORWARDED_FOR','r'=>'REMOTE_ADDR','c'=>'HTTP_CLIENT_IP'); //'r'=>'HTTP_X_REAL_FORWARDED_FOR',
		$ip = '';
		foreach($a as $k=>$v){
			$v = str_replace(' ','',$v);
			if(isset($_SERVER[$v]) && !strstr($ip,$_SERVER[$v])){
				$ip .= ';'.($flag ? "$k," : '').$_SERVER[$v];
			}
		}
		$ip = substr($ip,1);
		return $ip;
	}
	
	// Reuest数据
	static function _req($key){ //,$def='',$type='Title',$len=255
		if(isset($_POST[$key])){
			$val = $_POST[$key];
		}elseif(isset($_GET[$key])){
			$val = $_GET[$key];	
		}else{
			$val = '';
		} 
		$val = is_array($val) ? implode(',',$val) : $val; 
		//$val = preg_replace("/[^a-zA-Z0-9_|\~|\-|\.|\@]/",'',$val);
		$val = str_replace(array(">","<","'",'"',"\n","\r","\\",),'',$val);
		return $val; 
	}
		
	// --- End ----------------------------------------
	
}

