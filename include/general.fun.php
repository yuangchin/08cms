<?PHP
!defined('M_COM') && exit('No Permisson');
@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'compatibility_201308.php';//加载暂时保留的旧版本兼容(函数)脚本
@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'exgeneral.fun.php';

//加载扩展系统中的通用函数

/**
 * 返回文档主表数组，需要保留
 *
 * @param  string $tbl  ='archives'返回所有文档主表
 * @return array  $re   返回文档主表数组
 */
function m_tblarr($tbl){
	$re = array($tbl);
	$na = array_keys(cls_cache::Read('splitbls'));
	if($na && $tbl == 'archives'){
		foreach($na as $k) $re[] = $tbl.$k;
	}
	return $re;
}

/**
 * 把php数组形式的代码字符串，转化为php数组
 *
 * 主要用于转化字段等扩展配置的代码字串（如：array(1='好',2='很好',3='非常好',)）
 * Demo : $fieldnew['cfgs'] = varexp2arr($fieldnew['cfgs0']);
 *
 * @param  string $str   php代码字符串
 * @return array  $re    返回对应的数组
 */
function varexp2arr($str = ''){
	if(!$str) return array();
	@eval("\$ret = ".stripslashes($str).";");
	return empty($ret) || !is_array($ret) ? array() : $ret;
}


/**
 * 获取用逗号分开的系列id中的第一个ID
 *
 * @param  string   $id    多个用逗号分开的id字符串(如'23,68,89')
 * @return int      $rets  ID(这里是23)
 */
function cnoneid($id){
	return intval(ltrim($id,','));
}

/**
 * 说明：
 *
 * @param  int     $addno   
 * @param  string  $addnostr
 * @return string  ---
 */
function arc_addno($addno = 0,$addnostr = ''){
	return empty($addnostr) ? ($addno ? $addno : '') : $addnostr;
}

/**
 * 获取扩展脚本的入口地址
 *
 * @param  string  $str 扩展脚本标识
 * @return string  $str 如果没有找到，则返回false
 */
function exentry($str = ''){
	if(!$str || !($arr = cls_cache::cacRead('exscripts',_08_EXTEND_SYSCACHE_PATH)) || empty($arr[$str]) || !is_file(M_ROOT.$arr[$str])) return false;
	return M_ROOT.$arr[$str];
}


/**
 * 判断类系$coid在$chid对应的文档主表上是否有效
 *
 * @param  int   $coid     类系项目ID
 * @return int   $chid     文档模型ID
 */
function coid_in_chid($coid,$chid){
	if(!$coid || !$chid || !($channel = cls_channel::Config($chid)) || !($stid = $channel['stid'])) return false;
	$splitbls = cls_cache::Read('splitbls');
	if(!in_array($coid,@$splitbls[$stid]['coids'])) return false;
	return true;
}


/**
 * 把$var按JSON数据编码，注：该方法如果$var里包含有字符：<script>之类的会出错，请注意这点。
 *
 * @param  array  $var     把$var按JSON数据编码，可以是object,array,string
 * @param  bool   $is_a    是否为数组
 * @param  bool   $idx     是否为一组id组成的字符串
 * @return array  $caccnt  编码后的JSON字符串
 */
function jsonEncode($var, $is_a = 0, $idx = 0){
	static $slashes = "\\\"\r\n";
	if(is_string($var)){
		return '"' . addcslashes($var, $slashes) . '"';
	}elseif(is_numeric($var)){
		return $var;
	}elseif(is_bool($var)){
		return $var ? 'true' : 'false';
	}elseif(is_null($var)){
		return 'null';
	}else{
		is_object($var) && $var = get_object_vars($var);
		if(is_array($var)){
			$keys = array_keys($var);
			$val = implode('', $keys);
			if(!$is_a && (!$val || is_numeric($val))){
				$let = '';
				if($idx){
					for($k = 0, $v = max($keys); $k < $v; $k++)$let .= ',' . (isset($var[$k]) ? jsonEncode($var[$k], $is_a, $idx) : '');
				}else{
					foreach($keys as $k)$let .= ',' . jsonEncode($var[$k], $is_a, 0);
				}
				return '[' . substr($let, 1) . ']';
			}else{
				$let = '';
				foreach($var as $k => $v)$let .= ',"' . addcslashes($k, $slashes) . '":' . jsonEncode($v, $is_a, $idx);
				return '{' . substr($let, 1) . '}';
			}
		}
	}
	return '"unknow"';
}

/**
 * 根据数组arr，组sql子句
 *
 * @param  array  $arr     类系项目id
 * @param  bool   $chid    1: NOT IN，0: IN（默认）
 * @return array  $caccnt  返回的数组，如：IN ('1','2','3')
 */
function multi_str($arr = array(),$no = 0){
	if(count($arr) == 1) return ($no ? '!=' : '=')."'".array_shift($arr)."'";
	else return ($no ? 'NOT ' : '')."IN (".mimplode($arr).")";
}

/**
 * 读html缓存
 *
 * @param  string  $cacfile  缓存文件
 * @return string  $caccnt   读取的内容,string或array等
 */
function read_htmlcac($cacfile){
	return (@include $cacfile) ? $caccnt : '';
}

/**
 * 保存html缓存
 *
 * @param  int    $cnt      缓存数据
 * @param  string $cacfile  缓存文件
 * @return NULL   ---       ---
 */
function save_htmlcac($cnt,$cacfile){
	str2file("<?php\ndefined('M_COM') || exit('No Permission');\n\$caccnt = '".addcslashes($cnt,'\'\\')."';",$cacfile);
}

/**
 * 创建目录，并设置权限
 *
 * @param  string   $dir     路径
 * @param  bool     $create  是否生成inex.html,index.htm文件
 * @param  bool     $isfile  ---
 * @return bool     ---      true:操作成功，false:操作失败，
 */
function mmkdir($dir,$create=1,$isfile=0){
	if(is_dir($dir)) return true;
	if($isfile){
		return mmkdir(dirname($dir),0);
	}else{
		if(!mmkdir(dirname($dir),0) || @!mkdir($dir,0777)) return false;
		if($create) foreach(array('htm','html') as $var) @touch($dir.'/index.'.$var);
		return true;
	}
}

/**
 * 清理目录
 *
 * @param  string   $dir     路径
 * @param  bool     $self    是否清除目录本身
 * @param  string    $expstr  忽略的目录，以逗号分隔多个目录
 * @return NULL     ---     
 */
function clear_dir($dir,$self = false,$expstr = ''){
	if(empty($dir)) return false;
	if(is_dir($dir)){
		$exp_arr = array('.','..',);
		if($expstr) foreach(explode(',',$expstr) as $v) $exp_arr[] = $v;
		$p = @opendir($dir);
		while(false !== ($f = @readdir($p))){
			if(!in_array($f,$exp_arr)) clear_dir("$dir/$f",true,$expstr);
		}
		@closedir($p);
		if($self) @rmdir($dir);
	}elseif(is_file($dir)) 
    {
        $file = _08_FilesystemFile::getInstance();
        $file->delFile($dir);
    }
}

/**
 * 替换标签中，sql语句中的变量
 *
 * @param string   $str
 * @param array    &$temparr
 * @return string  $str
 */
function sqlstr_replace($str,&$temparr){
	return preg_replace("/\{\\$(.+?)\}/ies","sqlstrval('\\1',\$temparr)",$str);
}

/**
 * 替换标签中，sql语句中的变量
 *
 * @param string   $tname
 * @param array    &$temparr
 * @return string  $tname
 */
function sqlstrval($tname,&$temparr){
	global $timestamp,$G;
	$temparr['timestamp'] = $timestamp;
	if(isset($temparr[$tname])){
		return $temparr[$tname];
	}elseif(isset($G[$tname])){
		return $G[$tname];
	}else return '';
}

/**
 * 说明：
 *
 * @param string   $tname
 * @param array    &$sarr
 * @return string  $tname
 */
function btagval($tname,&$sarr){
	$btags = cls_cache::Read('btags');
	if(isset($sarr[$tname])){
		return str_tagcode($sarr[$tname]);
	}elseif(isset($btags[$tname])){
		return str_tagcode($btags[$tname]);
	}else return _08_DEBUGTAG ? "{ \$$tname}" : '';
}

/**
 * 说明：
 *
 * @param string   &$source
 * @param bool     $decode
 * @return string  $source
 */
function str_tagcode(&$source,$decode=0){
	return $decode ? str_replace(array(' $','? }'),array('$','?}'),$source) : str_replace(array('$','?}'),array(' $','? }'),$source);
}

/**
 * 发送邮件
 *
 * @param string   $to      收件地址  
 * @param string   $subject 主题
 * @param string   $msg     内容  
 * @param array    $sarr    ---
 * @param string   $from    发件人地址    
 * @param bool     $ischeck 是否保存发送记录
 * @return string  $ret 
 */
function mailto($to,$subject,$msg,$sarr=array(),$from = '',$ischeck=0){
	include_once M_ROOT.'include/mail.fun.php';
	$ret = sys_mail($to,splang($subject,$sarr),splang($msg,$sarr),$from);
	if(!$ischeck && $ret){
		global $timestamp;
		$curuser = cls_UserMain::CurUser();
		$record = mhtmlspecialchars($timestamp."\t".$curuser->info['mid']."\t".$curuser->info['mname']."\t".$ret);
		record2file('smtp',$record);
	}
	return $ret;
}
/**
 * 说明：
 *
 * @param string   $key  
 * @param array    &$sarr
 * @return string  $ret 
 */
function splang($key,&$sarr){
	$ret = $key;
	$splangs = cls_cache::Read('splangs');
	if(isset($splangs[$key])) $ret = preg_replace("/\{\\$(.+?)\}/ies","btagval('\\1',\$sarr)",$splangs[$key]);
	return $ret;
}

/**
 * 说明：
 *
 * @param array   $arr  
 * @return array  $arr  
 */
function marray_flip_keys($arr) {
	$arr2 = array();
	$arrkeys = array_keys($arr);
	list(, $first) = each(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k=>$v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
}

/**
 * 以带可变参数{$page}的文件名来删除多页同名文件
 *
 * @param  string  $filepre  文件名
 * @param  string  $num      分页数目
 * @return NULL    ---       节点信息
 */
function m_unlink($filepre='',$num=50){
	if(!$filepre) return;
    $file = _08_FilesystemFile::getInstance();
	for($i = 1;$i <= $num;$i++)
    {
		if(!$file->delFile(M_ROOT.cls_url::m_parseurl($filepre,array('page' => $i,))))
        {
            break;
        }
	}
}

/**
 * 根据会员节点字串，得到节点目录
 * 
 * @param  string $cnstr    节点字串
 * @return string $dirname  节点目录
 */
function mcn_dir($cnstr){
	if(!$cnstr) return '';
	$var = array_map('trim',explode('=',$cnstr));
	if($var[0] == 'caid'){
		$arr = cls_cache::Read('catalogs');
	}elseif(in_str('ccid',$var[0])) $arr = cls_cache::Read('coclasses',str_replace('ccid','',$var[0]));
	return empty($arr[$var[1]]['dirname']) ? $var[0].'_'.$var[1] : $arr[$var[1]]['dirname'];
}

/**
 * 获取文档url所需要的字段
 * 用于 INNER JOIN中，只选取需要的字段，避免其它字段干扰
 * @param  string $fix 字段前缀(表别名)
 *
 * @return string fields_str 字段列表，包含[,]号前缀
 * a.mid添加记录:core:svn:1856,2014-03-19,home:svn:2202; 2016-03-24peace又还原：可能多方面出问题:
 *   1. 可能与连表的mid冲突, 需要时根据情况扩展,核心中不添加
 *   2. 之前多处已经使用as subpro类似别名避免连表后多个subject,所以后续修改请把a.subject放最后
 */
function aurl_fields($fix='a.'){
	$fstr = ",a.aid,a.chid,a.caid,a.createdate,a.initdate,a.customurl,a.nowurl,a.subject";
	if($fix!='a.') $fstr = str_replace('a.',$fix,$fstr);
	return $fstr;
}

/**
 * 获取会员空间url所需要的字段
 * 用于 INNER JOIN中，只选取需要的字段，避免其它字段干扰
 * @param  string $fix 字段前缀(表别名)
 *
 * @return string fields_str 字段列表，包含[,]号前缀
 */
function murl_fields($fix='m.'){
	$fstr = ",m.mid,m.mchid,m.dirname,m.mspacepath,m.mname,m.msrefreshdate"; 
	if($fix!='m.') $fstr = str_replace('m.',$fix,$fstr);
	return $fstr;
}

/**
 * 把字符串转化为正则表达式
 *
 * @param  string    $str     原字符串
 * @return string    $str     处理后的字符串
 */
function u_regcode($str){
	return "/^".preg_quote($str,"/")."/i";
}

/**
 * 检查字符串$source是否包含子字符串$me
 *
 * @param  string    $me      子字符串
 * @param  string    $source  要搜索的字符串
 * @return bool      ---      包含1,不包含0
 */
function in_str($me,$source){
	return !(strpos($source,$me) === FALSE);
}

/**
 * 把字符串以document.write的js形式输出
 *
 * @param  string    $content   要输出的内容
 * @return NULL      ---        无返回
 */
function js_write($content){
	$content = cls_phpToJavascript::JsWriteCode($content);
    echo $content;
}

/**
 * 把操作记录写如文件
 *
 * @param  string    $rname     记录类型(同时确定文件名格式)
 * @param  string    $record    操作记录内容
 * @return NULL      ---        无返回
 */
function record2file($rname,$record){
	global $timestamp;
	$recorddir = M_ROOT.'dynamic/records/';
	$recordfile_pre = $recorddir.date('Ym',$timestamp).'_'.$rname;
	$recordfile = $recordfile_pre.'.php';
	if(@filesize($recordfile) > 1024*1024){
		$dir = opendir($recorddir);
		$length = strlen($rname);
		$maxid = $id = 0;
		while($file = readdir($dir)){
			if(in_str($recordfile_pre,$file)){
				$id = intval(substr($file,$length +8,-4));
				($id > $maxid) && ($maxid = $id);
			}
		}
		closedir($dir);
		$recordfilebk = $recordfile_pre.'_'.($maxid +1).'.php';
		@rename($recordfile,$recordfilebk);
	}
	if($fp = @fopen($recordfile, 'a')){
		@flock($fp, 2);
		$record = is_array($record) ? $record : array($record);
		foreach($record as $tmp) {
			fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
		}
		fclose($fp);
	}
}

/**
 * 查找文件列表
 *
 * @param  string    $absdir     相对路径
 * @param  string    $str        关键字，为空表示全部
 * @param  bool      $inc        0按扩展名查询，1按包含字串查询
 * @return array     $tempfiles  返回文件列表
 */
function findfiles($absdir,$str='',$inc=0){//$inc 0按扩展名查询，1按包含字串查询
	$tempfiles = array();
	if(is_dir($absdir)){
		if($tempdir = opendir($absdir)){
			while(($tempfile = readdir($tempdir)) !== false){
				if(filetype($absdir."/".$tempfile) == 'file'){
					if(!$str){
						$tempfiles[] = $tempfile;
					}elseif(!$inc && mextension($tempfile) == $str){
						$tempfiles[] = $tempfile;
					}elseif($inc && in_str($str,$tempfile)){
						$tempfiles[] = $tempfile;
					}
				}
			}
			closedir($tempdir);
		}
	}
	return $tempfiles;
}

/**
 * 把文本格式化输出（处理连续空格,换行,退格）
 *
 * @param  string    $absdir     相对路径
 * @param  string    $str        关键字，为空表示全部
 * @param  bool      $inc        0按扩展名查询，1按包含字串查询
 * @return array     $tempfiles  返回文件列表
 */
function mnl2br($string){
	return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'),$string));
}

/**
 * 读出文件内容为字符串
 *
 * @param  string     $filename  文件名
 * @return string     $str       失败返回false
 */
function file2str($filename){
	if(!is_file($filename)) return false;
	return @file_get_contents($filename);
}

/**
 * 把内容写如指定文件
 *
 * @param  string     $result    内容
 * @param  string     $filename  文件名
 * @return bool       ---        true/false返回写入文件是否成功
 */
function str2file($string,$filename){
	if(!mmkdir($filename,0,1) || (false !== stripos($filename, '..'))) return false;
	// $re = file_put_contents($filename,$string); 
	// file_put_contents() 函数 与依次调用 fopen()，fwrite() 以及 fclose() 功能一样；但效率与稳定性 都比后者差多了。
	$handle = @fopen($filename,"wb");
	if($handle){
		$re = fwrite($handle,$string);
		fclose($handle);
		return $re;	
	}else{
		return false;	
	}
}

/**
 * 检测 手机确认码 是否正确
 *
 * @param  string     $mod      手机短信模块ID
 * @param  string     $msgcode  输入的确认码
 * @param  string     $tel      输入的电话号码(可为空不认证,不为空同时认证电话号码是否相同)
 * @return bool       ---       true/false
 */
function smscode_pass($mod,$msgcode='',$msgtel=''){
	global $m_cookie;
	$timestamp = TIMESTAMP;
	$ckkey = 'smscode_'.$mod;
	@list($stamp, $svcode, $tel) = maddslashes(explode("\t", authcode($m_cookie[$ckkey],'DECODE')),1);
	@$pass = !empty($svcode) && (TIMESTAMP - intval($stamp))<3600 && $svcode===$msgcode; // && $fmdata['lxdh']===$tel; 
	if(!empty($msgtel)) $pass = $pass && ($msgtel===$tel);
	return $pass;
}

/**
 * 检测 验证码 是否正确
 *
 * @param  string     $rname    验证码项目名称
 * @param  string     $code     输入的验证码
 * @return bool       ---       true/false
 */
function regcode_pass($rname,$code=''){
	global $m_cookie,$cms_regcode,$verify;
	$timestamp = TIMESTAMP;
	if(!$cms_regcode || !in_array($rname,explode(',',$cms_regcode))) return true;
	empty($verify) && $verify = '08cms_regcode';
	@list($inittime, $initcode) = maddslashes(explode("\t", @authcode($m_cookie[$verify],'DECODE')),1);
	mclearcookie($verify);#验证码错误也清除，防注册机，发帖机爆力破解...
	mclearcookie('t_t');
	
	if(($timestamp - $inittime) > 1800 || strtolower($initcode) != strtolower($code)){
		return false;
	}
	return true;
}

/**
 * 加密/解密字符串
 *
 * @param  string     $string    原始字符串
 * @param  string     $operation 操作选项: DECODE：解密；其它为加密
 * @param  string     $key       混淆码
 * @return string     $result    处理后的字符串
 */
function authcode($string, $operation = '', $key = '') {
	global $authorization;
	$key = md5($key ? $key : $authorization);
	$key_length = strlen($key);

	$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);

	$rndkey = $box = array();
	$result = '';

	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
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
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}

}

/**
 * 跳转处理
 *
 * @param  string   $s                 跳转url
 * @param  bool     $replace            ---
 * @return string   $http_response_code ---
 */
function mheader($s,$replace = true,$http_response_code = 0){
	$s = str_replace(array("\r","\n"),'',$s);
	@header($s,$replace,$http_response_code);
	if(preg_match('/^\s*location:/is',$s)) exit();
}

/**
 * 在指定的预定义字符(',",\,NULL)前添加反斜杠，支持数组，注意：如不是用于GPC，要让force=1
 *
 * @param  string   $s     原始字符串，可以是数组
 * @param  bool     $force 强制选项
 * @return string   $s     处理后的字符串
 */
function maddslashes($s, $force = 0) {
	return cls_env::maddslashes($s, $force);
}

/**
 * 判断指定的文件是否是通过 HTTP POST 上传的
 *
 * @param  string   $file   文件
 * @return bool     ---     返回:1,0
 */
function mis_uploaded_file($file){
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}

/**
 * 用[',']连接数组，且前后加上[,]
 *
 * @param  array    $arr    原始数组
 * @return string   ---     连接后的字符串如：'23','43','3434'
 */
function mimplode($arr){
	return empty($arr) ? '' : "'".implode("','", is_array($arr) ? $arr : array($arr))."'";
}
/**
 * 输出信息并退出
 *
 * @param  string $message   要输出的信息
 * @return NULL   ---        ---
 */
function mexit($message = ''){
	echo $message;
	output();
	exit();
}
/**
 * 清除缓冲区内容
 *
 * @param  bool $force   是否强制清除
 * @return NULL ---    ---
 */
function m_clear_ob($force = 0){
	global $phpviewerror;
	if($force || $phpviewerror != 3){
		ob_end_clean();		
		cls_env::mob_start();
	}
}
/**
 * 压缩输出
 *
 * @param  string $var       变量名
 * @param  bool   $allowget  是否检测get提交(未用)
 * @return bool   ---        编码后的字符串
 */
function output(){
	$content = ob_get_clean();
	cls_env::mob_start();
	echo $content;
}

/**
 * 检查表单提交
 *
 * @param  string $var       变量名
 * @param  bool   $allowget  是否检测get提交(未用)
 * @return bool   ---        编码后的字符串
 */
function submitcheck($var, $allowget = 0)
{
    # 提交表单时
    if ( !empty($GLOBALS[$var]) )
    {/*   // todo 暂时用验证码方式代替不处理
        # 验证CSRF，只验证POST
        if ( isset($_POST[$var]) )
        {
            $cookie = cls_env::_COOKIE();
            if ( !isset($cookie[cls_env::_08_HASH]) || !isset($_POST[cls_env::_08_HASH]) )
            {
                return false;
            }
            if ( $_POST[cls_env::_08_HASH] != $cookie[cls_env::_08_HASH] )
            {
                cls_message::show('表单超时或非法提交！');
            }
        }
        
        # 通过验证后重新生成一个HASH值，目前该方式还是会不太合适，因为验证后不代表表单提交成功
        cls_env::getHashValue(true);*/
        
        return true;
    }    
    
	return false;
}
/**
 * 清除cookie
 *
 * @param  string $ckname   cookie名
 * @return NULL   ---       ---
 */
function mclearcookie($ckname='userauth'){
    $ckname = preg_replace('/[^\w]/', '', $ckname);
	msetcookie($ckname,'',-86400 * 365);
}
/**
 * 设置cookie
 *
 * @param  string $ckname   cookie名
 * @param  string $ckvalue  值
 * @param  string $cklife   保存期限(s)
 * @return NULL   ---       ---
 */
function msetcookie($ckname, $ckvalue, $cklife = 0, $httponly = false) {
	global $ckpre, $ckdomain, $ckpath,$cms_top;
    $ckname = preg_replace('/[^\w]/', '', $ckname);
	$ckdomain = getCookieDomain();
	setcookie($ckpre.$ckname, $ckvalue, $cklife ? TIMESTAMP + $cklife : 0, $ckpath, $ckdomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0, $httponly);
}

function getCookieDomain()
{
	global $ckdomain, $cms_top;
	$ckdomain = empty($ckdomain) && !empty($cms_top) ? '.'.$cms_top : $ckdomain;
    return $ckdomain;
}

/**
 * 编码HTML字符编码，对htmlspecialchars扩展，可以传array参数
 *
 * 编码结果：HTML字符编码：&"'<> --> &amp; &quot; &#039; &lt; &gt;
 *
 * @param  string $string   原字符串，array或string
 * @param  string $quotes   选项：2:仅编码双引号; 3:编码双引号和单引号(默认); 0:不编码任何引号;
 * @return string $string   编码后的字符串
 */
function mhtmlspecialchars($string, $quotes = ENT_QUOTES, $delete_rep = false) {
	if(is_array($string)) {
		foreach($string as $key => $val) $string[$key] = mhtmlspecialchars($val, $quotes, $delete_rep);
	} else { // 2:ENT_COMPAT:默认,仅编码双引号; 3:ENT_QUOTES:编码双引号和单引号; 0:ENT_NOQUOTES:不编码任何引号;
        if ( $delete_rep )
        {
            $string = str_replace(array(' ', '%20', '%27', '*', '\'', '"', '/', ';', '#', '--'), '', $string);
        }
		$string = htmlspecialchars($string, $quotes);
	}
	return $string;
}
/**
* 编码HTML字符编码，可以传array参数，但保留一些特殊字符如&amp;
 *
 * @param  string $string   原字符串，array或string
 * @return string $string   编码后的字符串
 */
function mhtmlspecialkeep($string) { // htmlspecialchars编码部分字符，保留某些特殊字符
	if(is_array($string)) {
		foreach($string as $key => $val) $string[$key] = mhtmlspecialkeep($val);
	} else {
		$string = preg_replace(
		'/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', 
		'&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string)
		);
	}
	return $string;
}
/**
 * 获取扩展文件名
 *
 * @param  string $filename   原文件名
 * @return string ---         扩展文件名
 */
function mextension($filename) {
	return trim(substr(strrchr($filename, '.'), 1, 10));
}

function misuploadedfile($file) {
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}
/**
 * 当前ip是否允许(访问或操作)
 *
 * @param  string $ip         IP地址
 * @param  string $accesslist 允许的IP地址列表
 * @return bool   ---         0:禁止, 1:允许
 */
function ipaccess($ip, $accesslist) {
	//注意各浏览器下对回车换行处理不同,所以"\r\n", "\r", "\n"都要转化为'|'
	return preg_match("/^(".str_replace(array("\r\n", "\r", "\n", ' '), array('|', '|', '|', ''), preg_quote($accesslist, '/')).")/", $ip);
}
/**
 * 也面执行完后,跳转操作处理
 * Demo: amessage('信息添加失败',axaction(6,"path/file.php?"));
 *
 * @param  string $mode       浮动窗操作指令代码
 * @param  string $url        跳转url
 * @return string $re         url或js代码
 */
function axaction($mode, $url = ''){
	global $infloat, $handlekey;
    $url = cls_env::repGlobalURL($url);
	if(!$infloat)return $url;
	$ret = '';
    $handlekey === 0 && $handlekey = '';
	if((!$mode || $mode & 32) && $url){//0或包含32，本窗口跳转
		$ret .= "floatwin('update_$handlekey','$url');";
	}
	if($mode & 1){//包含1，刷新本窗口
		$ret .= "floatwin('update_$handlekey');";
	}
	if($mode & 2){//包含2，关闭本窗口
		$ret .= "floatwin('close_$handlekey',-1);";
	}
	$ret = 'javascript:' . ($ret ? ('setDelay(\'' . str_replace("'", "\\'", $ret) . '\',t);') : '');
	if($mode & 4){//包含4，刷新父窗口
		$ret .= "floatwin('updateparent_$handlekey');";
	}
	if($mode & 16){//包含16，刷新父父窗口，要在关闭父窗口前刷新
		$ret .= "floatwin('updateup2_$handlekey',-1);";
	}
	if($mode & 8){//包含8，关闭父窗口
		$ret .= "floatwin('closeparent_$handlekey',-1);";
	}
    if ( $mode & 64 ) // 关闭本窗口并跳转父窗口
    {        
		$ret .= "floatwin('closelocation_$handlekey', '$url');";
    }
	return $ret;
}
/**
 * 根据会员组,判断是否隐藏联系方式的一部分
 *
 * @param bool $hid_connect 1:是,0否
 */
function is_hidden_connect(){
	$curuser = cls_UserMain::CurUser();
	$hid_connect = false;
	$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
	$hid_configs = $exconfigs['hid_connect'];
	if(!empty($hid_configs['groups'])){
		$hid_groups = explode(',',$hid_configs['groups']);
		foreach($hid_groups as $g){
		  $g = $curuser->info["grouptype$g"];
		  if($g && strpos($hid_configs['hid'],$g)){
			 $hid_connect = true;
			 break;
		  }
		}
	}
	return $hid_connect;
}

/**
 * 清除指定的COOKIE值
 *
 * @param array $cookies 要清空的COOKIE名
 * @param bool  $strpos  是否使用strpos匹配名称
 */
function cleanCookies($cookies, $strpos = false)
{
    global $m_cookie;
    settype($cookies, 'array');
    foreach($cookies as $k)
    {
        if ( $strpos != false )
        {
            $keys = array_keys($m_cookie);
            foreach ($keys as $key) 
            {
                if ( false !== strpos($key, $k) )
                {
                    mclearcookie($key);
                }
            }
            
        }
        else
        {
        	if(isset($m_cookie[$k]))
            {
                mclearcookie($k);
            }
        }        
    }
}

/**
 * 取得用户定制或系统扩展的入口脚本
 * 未定义入口或入口脚本不存在，都返回空值
 * 对于本身就是官方扩展的脚本，只需要分析是否有用户定制入口，此时定义onlycustom为1
 * querys 为入口传入的参数源数组，不传入则默认为$_SERVER['QUERY_STRING']得到的数组
 */
function extend_script($main = '',$onlycustom = 0,$querys = array()){
	foreach(array('extendscripts','customscripts',) as $k) $$k = cls_cache::cacRead($k,_08_EXTEND_SYSCACHE_PATH);
	if(!$querys || !is_array($querys)) $querys = cls_env::_GET();
	foreach(array('custom','extend') as $var){
		if($onlycustom && $var == 'extend') break;
		if($cfg = &${$var.'scripts'}[$main]){
			foreach($cfg as $k => $v){//检查所有定义的入口
				if($v && is_array($v)){
					foreach($v as $key => $val){
						if(is_array($val) && $val){
							if(empty($querys[$key]) || !in_array($querys[$key],$val)) break 2;
						}elseif($val){
							if(empty($querys[$key]) || $querys[$key] != $val) break 2;
						}elseif(!empty($querys[$key])) break 2;
					}
				}
				return $k;
			}
		}
	}
	return '';
}

/**
 * 读取选中文本的缓存文件
 *
 * @param string $file 文件名称（不带后缀）
 * @param string $path 缓存路径
 * @return array 返回文件内容数组
 */
function read_select_file($file, $path = '')
{
    empty($path) && $path = _08_TEMP_TAG_CACHE;
    _08_FileSystemPath::checkPath($path, true);
    return cls_cache::cacRead($file, $path, true);
}


/**
 * 处理搜索关键字，处理后：可搜索%_特殊字符，
 * demo: AND (a.subject ".sqlkw($keyword).")";
 *
 * @param  string $keyword 要转换的字符串
 * @param  string $multi =1时，*，空格当成通配符处理
 * @return string $sqlstr 返回sql子字符串，包含 LIKE
 */
function sqlkw($keyword,$multi=1){
	$keyword = addcslashes($keyword,'%_');
	$multi && $keyword = str_replace(array(' ','*'),'[08cmsKwBlank]',$keyword);
	return " LIKE '%$keyword%' ";
}

/**
 *  对$content中的占位符{xxx}进行替换($arr['xxx'])
 *
 * @param  string  $content  原始内容
 * @param  array   $arr      值数组
 * @param  int     $prefix   占位符前缀
 * @param  int     $suffix   占位符后缀
 * @return string  $content  替换后的内容
 */
function key_replace($content = '',$arr = array(),$prefix = '{',$suffix = '}'){
	if(!$content || !$arr) return $arr;
	return preg_replace("/\{(.+?)\}/ies","_key_replace('\\1',\$arr,\$prefix,\$suffix)",$content);
}
// 支持$GLOBALS如$timestamp,$cms_abs; 格式如：{pre}enddate < '{timestamp}'
// 优先找$arr的键值，没有找到则找$GLOBALS变量
function _key_replace($key = '',$arr = array(),$prefix = '{',$suffix = '}'){
	if(!$key || (!isset($arr[$key]) && !isset($GLOBALS[$key]))) return $prefix.$key.$suffix;	
	return isset($arr[$key]) ? $arr[$key] : $GLOBALS[$key];
}

/**
 * 选择性字段从数据库储存值得到显示值
 *
 * @param  string  $val   数据库储存值
 * @param  array   $field 字段配置信息
 * @param  int     $num   对多选,限制个数,默认0不限
 * @return string  $str   显示值
 */
function view_field_title($val,$field,$num = 0){
	if(!$val || !$field || !in_array($field['datatype'],array('select','mselect','cacc',))) return $val;
	if(in_array($field['datatype'],array('mselect','select',))){
		$tmp = explode("\n",$field['innertext']);
		$arr = array();
		foreach($tmp as $v){
			$t = explode('=',str_replace(array("\r","\n"),'',$v));
			$t[1] = isset($t[1]) ? $t[1] : $t[0];
			$arr[$t[0]] = $t[1];
		}
		$multi = $field['datatype'] == 'mselect' ? 1 : 0;
	}elseif($field['datatype'] == 'cacc'){
		$arr = empty($field['coid']) ? cls_cache::Read('catalogs') : cls_cache::Read('coclasses',$field['coid']);
		foreach($arr as $k => $v) $arr[$k] = $v['title'];
		$multi = empty($field['max']) ? 0 : 1;
	}else return $val;
	if($multi){
		$vals = explode($field['datatype'] == 'cacc' ? ',' : "\t",$val);
		$ret = '';$i = 1;
		foreach($vals as $k){
			if(isset($arr[$k])){
				if(!empty($num) && ++$i > $num) break;
				$ret .= ($ret ? ' ' : '').$arr[$k];
			}
		}
		return $ret;
	}else return @$arr[$val];
}

/**
 * 根据权限方案分析操作权限, 如需返回无权限的原因，请使用mem_noPm
 *
 * @param  array  $info  会员的主表信息
 * @param  int    $pmid  权限方案ID
 * @return bool   $str   只返回true(有权限)/false(无权限)
 */
function mem_pmbypmid($info = array(),$pmid = 0){
	return _mem_noPm($info,$pmid) ? false : true;
}


// 获取str首字母
function autoletter($str = ''){
	if(!$str) return '';
	return cls_string::FirstLetter($str);
}

// 说明：系统首页的静态格式，$Nodemode：1为手机版
function idx_format($Nodemode = 0){
	return $Nodemode ? '' : cls_env::GetG('homedefault');
}
/**
 * 多选项目的多模式设置
 * @param  int  $info  设置模式：0－全新设置，1－添加模式，2－移除模式
 * @param  int  $limit  多选的数量限制
 * @param  string  $nids  新选择的选项字串
 * @param  string  $oids  原选项字串
 * @param  int  $both  是否在返回字串的两端加上','
 * @return string 设置完成后的选择字串
 */
function idstr_mode($mode,$limit,$nids,$oids,$both = 0){
	if($mode && $limit){
		$nids = array_filter(explode(',',$nids));
		$oids = array_filter(explode(',',$oids));
		$nids = $mode == 1 ? array_unique(array_merge($oids,$nids)) : array_diff($oids,$nids);
		$nids && $nids = array_slice($nids,-$limit,$limit);
		return $nids ? ($both ? (','.implode(',',$nids).',') : implode(',',$nids)) : '';
	}else return $nids;
}

//$infoid=文档/会员/交互/类系:id
//$modid=chid,mchid,cuid,coid 分别代表 文档/会员/交互/类系 模型ID
//$type=a,m,cu,co 分别代表 文档/会员/交互/类系 类型
//$field=clicks 字段
function view_count($infoid,$modid,$type,$field){
	global $cms_abs;
	echo cls_phpToJavascript::str_js_src($cms_abs . _08_Http_Request::uri2MVC("ajax=view_count&infoid={$infoid}&modid={$modid}&type={$type}&field={$field}"));
}

#################### 兼容函数 ##################################################

/**
 * 根据ip，计算ip对应的物理地址
 *
 * @param  string $ip IP地址
 * @return string $re 返回物理地址名称
 */
function ipaddress($ip) {
	return cls_ipAddr::conv($ip,'local');
}

####################以下函数从/include/common.fun.php移植过来##################
if ( !function_exists('js_callback') )
{
    function js_callback($var = 'succeed'){
    	global $callback;
    	if($callback){
    		ob_clean();
    		header("Content-Type: application/javascript;charset=".cls_env::getBaseIncConfigs('mcharset'));
    		mexit("js_callback(" . jsonEncode($var) . ",'$callback')");
    	}
    }
}

##############################################################################

