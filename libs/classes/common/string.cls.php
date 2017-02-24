<?php
/**
* 针对文本的处理方法
* 
*/
class cls_string{

	// 把中文和特殊字符用改装过的base64编码,适合用于url传输(比url编码短,且都是安全字符),获取时用这个来解码
	// url《!(),-.;@^_`~》安全字符12个; 在08cms系统中《/-》用以分割参数;
	// 万一得到一个.html或.php等字符串,又被08cms在某些地方处理掉？
	// $s : 原字符串, 支持数组
	// $de : 0-编码, 1-解码, a-解码返回数组
	static function urlBase64($s,$de=0,$a2='._'){
		if(is_array($s)){
			$str = ''; //格式与字段配置相同
			foreach($s as $k=>$v){
				$str .= (empty($str)?'':"\n")."$k=".str_replace(array("\n",'='),'',$v);
			}
			$s = $str; 
		}
		if($de){
			$s = str_pad(strtr($s,$a2,"+/"),strlen($s)%4,'=',STR_PAD_RIGHT);
			$s = base64_decode($s);
			if($de=='a'){
				$field = array('datatype'=>'select','fromcode'=>0,'innertext'=>$s);
				$s = cls_field::options($field); 
			}
		}else{ 
			$s = base64_encode($s);
			$s = rtrim(strtr($s,"+/",$a2),'=');
		}
		return $s;
	}
	
	// 计算字符串字节数，英文算一个字节,不管[GBK/utf-8]编码中文算两个字节
	// 用于字段长度检测
	public static function CharCount($str){
		global $mcharset;
		$ch = $mcharset=='utf-8' ? 3 : 2; //中文宽度
		$length = strlen(preg_replace('/[\x00-\x7F]/', '', $str)); 
		if($length){
			return strlen($str) - $length + intval($length / $ch) * 2;
		}else{
			return strlen($str);
		}
		//return strlen(preg_replace('/([x4e00-x9fa5])/u','**',$str));
	}
	
	// 按长度剪裁文本($length字节)，
	// 用于前台显示等宽字符串(中文按两个字节,utf-8也是这样)
	public static function CutStr($string, $length, $dot = ' ...') {
		global $mcharset;
		$strlen = strlen($string);
		if($strlen <= $length) {
			return $string;
		}
		$strcut = '';
		$n = $tn = $noc = 0;
		$length -= strlen($dot);
		if(strtolower($mcharset) == 'utf-8') {
			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t == 38){
					# the "&" char
					if(preg_match('/^&#?(\w+);/', substr($string, $n, 16), $match)){
						$noc += is_numeric($match[1]) && intval($match[1]) > 255 ? 2 : 1;
						$tn = strlen($match[0]);
						$n += $tn;
					}else{
						$tn = 1; $n++; $noc++;
					}
				}elseif($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc += 2;
				} elseif(224 <= $t && $t < 239) {
					$tn = 3; $n += 3; $noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc += 2;
				} else {
					$n++;
				}
				if($noc >= $length) {
					break;
				}
			}
		} else {
			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t == 38){
					# the "&" char
					if(preg_match('/^&#?(\w+);/', substr($string, $n, 16), $match)){
						$noc += is_numeric($match[1]) && intval($match[1]) > 255 ? 2 : 1;
						$tn = strlen($match[0]);
						$n += $tn;
					}else{
						$tn = 1; $n++; $noc++;
					}
				}else{
					$tn = $t > 127 ? 2 : 1;
					$n += $tn;
					$noc += $tn;
				}
				if($noc >= $length) {
					break;
				}
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
		return $strcut.$dot;
	}
	
	/**
	 * int WordCount(string $string[, bool $flag]) 字数计算
	 *
	 * @param	string	$string	被计算字符串
	 * @param	bool	$flag	为真则只计算多字节字符数
	 * @return	int				字符串字符数
	 *
	 * @remark	与 strlen 的区别是多字节字符只算一个字
	 *
	 **/
	public static function WordCount($string, $flag = false){
		global $mcharset;
		$n = $word = 0;
		$strlen = strlen($string);
		if(strncasecmp('utf', $mcharset, 3)){
			while($n < $strlen){
				$t = ord($string[$n]);
				if($t > 127){
					$n += 2;
					$word++;
				}else{
					$n++;
					$flag || $word++;
				}
			}
		}else{
			while($n < $strlen){
				$t = ord($string[$n]);
				if(194 <= $t && $t <= 223){
					$n += 2;
					$word++;
				}elseif(224 <= $t && $t < 239){
					$n += 3;
					$word++;
				}elseif(240 <= $t && $t <= 247){
					$n += 4;
					$word++;
				}elseif(248 <= $t && $t <= 251){
					$n += 5;
					$word++;
				}elseif($t == 252 || $t == 253){
					$n += 6;
					$word++;
				}else{
					$n++;
					$flag || $word++;
				}
			}
		}
		return $word;
	}
	
	
	
	/**
	 * string keywords(string $nstr, string $ostr) 切割处理关键词
	 *
	 * @param	string	$nstr	经过 addslashes 处理的逗号或空格分割的字符串
	 * @param	string	$ostr	逗号分割的字符串
	 * @return	string			经过 addslashes 处理的逗号分割的字符串
	 *
	 * @remark	分割符兼容半角和全角，当字符中有逗号时使用逗号分割，否则使用空格分割
	 *			每个关键字要求在 2 - 8 个汉字或 2 - 24 个字母之间
	 **/
	public static function keywords($nstr, $ostr=''){
		global $hotkeywords, $mcharset, $db, $tblprefix;
		if(empty($nstr))return '';
		$nstr = stripslashes($nstr);
		if(!strncasecmp('gb', $mcharset, 2)){
			#gbk, gb2312
			$comma = pack('C*', 163, 172);#逗号
			$blank = pack('C*', 161, 161);#空格
		}elseif(!strncasecmp('big', $mcharset, 3)){
			#big5, big5-HKSCS
			$comma = pack('C*', 161, 65);
			$blank = pack('C*', 161, 64);
		}else{
			#utf-8
			$comma = pack('C*', 239, 188, 140);
			$blank = pack('C*', 227, 128, 128);
		}
		$tstr = str_replace($comma, ',', $nstr);
		$isbk = strpos($tstr, ',') === false;
		$narr = array_unique(explode($isbk ? ' ' : ',', $isbk ? str_replace($blank, ' ', $nstr) : $tstr));
		$oarr = $ostr ? explode(',', $ostr) : array();
		$i = 0;
		$ret = $sqlstr = '';
		foreach($narr as $str){
			$str = trim(strip_tags($str));
			$len = strlen($str);
			if($len >= 2 && $len <= 24){
				$word = self::WordCount($str, 1);
				if($word == 0 || ($word >= 2 && $word <= 8)){
					#有汉字就必须是 2-8 个字
					$ret .= ($ret ? ',' : '') . $str;
					$hotkeywords && !in_array($str, $oarr) && $sqlstr .= ($sqlstr ? ',' : '') . "('" . addslashes($str) . "')";
	
					if(++$i >= 5){
						unset($narr,$oarr);
						break;
					}
				}
			}
		}
		$sqlstr && $db->query("INSERT INTO {$tblprefix}keywords (keyword) VALUES $sqlstr");
		return addslashes($ret);
	}
	
	//清理html文本中的样式、js等
	public static function HtmlClear($str){
		$str = preg_replace("/<sty.*?\\/style>|<scr.*?\\/script>|<!--.*?-->/is", '', $str);
		$str = preg_replace("/<\\/?(?:p|div|dt|dd|li)\b.*?>/is", '<br>', $str);
		$str = preg_replace("/\s+/", '', $str);
		$str = preg_replace("/<br\s*\\/?>/is", "\r\n", $str);
		$str = strip_tags($str);
	
		return str_replace(
			array('&lt;', '&gt;', '&nbsp;', '&quot;', '&ldquo;', '&rdquo;', '&amp;'),
			array('<','>', ' ', '"', '"', '"', '&'),
			$str
		);
	}
    
	/*
	//安全字串
	public static function SafeStr($string)
    {
		$searcharr = array("/(javascript|jscript|js|vbscript|vbs|about):/i","/on(mouse|exit|error|click|dblclick|key|load|unload|change|move|submit|reset|cut|copy|select|start|stop)/i","/<script([^>]*)>/i","/<iframe([^>]*)>/i","/<frame([^>]*)>/i","/<link([^>]*)>/i","/@import/i");
		$replacearr = array("\\1\n:","on\n\\1","&lt;script\\1&gt;","&lt;iframe\\1&gt;","&lt;frame\\1&gt;","&lt;link\\1&gt;","@\nimport");
		$string = preg_replace($searcharr,$replacearr,$string);
		$string = str_replace("&#","&\n#",$string);
		return $string;
	}*/
    
    /**
     * 安全字串（也可用于XSS）
     * 
     * @param  string $value 要过滤的值
     * @return string        返回已经过滤过的值
     * 
     * @since  1.0
     */
    public static function SafeStr($value)
    {
		
       // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
       // this prevents some character re-spacing such as <java\0script>
       // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    #   $value = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $value);
    
       // straight replacements, the user should never need these since they're normal characters
       // this prevents like <IMG SRC=@avascript:alert('XSS')>
       $search = 'abcdefghijklmnopqrstuvwxyz';
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $search .= '1234567890!@#$%^&*()';
       $search .= '~`";:?+/={}[]-_|\'\\';
       for ($i = 0; $i < strlen($search); $i++)
       {
          // ;? matches the ;, which is optional
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
    
          // @ @ search for the hex values
          $value = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $value); // with a ;
          // @ @ 0{0,7} matches '0' zero to seven times
          $value = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $value); // with a ;
       }
    
       // now the only remaining whitespace attacks are \t, \n, and \r
       #$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 
       #             'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'base');
       $ra1 = array('<javascript', '<vbscript', '<expression', '<applet', '<script', '<object', '<iframe',
	                '<frame', '<frameset', '<ilayer', '<bgsound', '<base');
                    
       $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 
           'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 
           'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 
           'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 
           'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 
           'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 
           'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 
           'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 
           'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 
           'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
           
       $ra = array_merge($ra1, $ra2);
    
       $found = true; // keep replacing as long as the previous round replaced something
       while ($found == true) 
       {
          $val_before = $value;
          for ($i = 0; $i < sizeof($ra); $i++) 
          {
             $pattern = '/';
             for ($j = 0; $j < strlen($ra[$i]); $j++) 
             {
                if ($j > 0) 
                {
                   $pattern .= '(';
                   $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                   $pattern .= '|';
                   $pattern .= '|(&#0{0,8}([9|10|13]);)';
                   $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
             }
             $pattern .= '/i';
             
             $replacement = substr($ra[$i], 0, 2).'<!--08CMS-->'.substr($ra[$i], 2); // add in <> to nerf the tag
             $value = preg_replace($pattern, $replacement, $value); // filter out the hex tags
             
             if ($val_before == $value)
             {
                // no replacements were made, so exit the loop
                $found = false;
             }
          }
       }
       
       return $value;
    }
    
    /**
     * 对经过了self::SafeStr 函数调用后的字符串还原
     * 
     * @param  string $string 要还原的字符串
     * @return string         还原后的字符串
     * 
     * @since  1.0
     */
    public static function RestoreSafeStr( $string )
    {
        # 只还原的替换普通字符的方法，编码上的替换没还原，因为还原编码上的字符意义不大。
        $string = str_replace('<!--08CMS-->', '', $string);
        return $string;
    }
    
    /**
     * 判断该字符是否为安全字串
     * 
     * @param  string $string 要判断的字符串
     * @return bool           如果是安全字串返回TRUE，否则返回FALSE
     * 
     * @since  1.0
     */
    public static function isSafeStr( $string )
    {
        $string2 = self::SafeStr($string);
        if ( $string === $string2 )
        {
            return true;
        }
        
        return false;
    }
	
	//编码转换
	public static function iconv($from,$to,$source){
		if(!is_array($source) && ($source === '')) return '';
		$from = strtolower($from);
		$to = strtolower($to);
		if($from == $to) return $source;
		if(is_array($source)){
			$re = array();
			foreach($source as $k => $v) $re[$k] = self::iconv($from,$to,$v);
			return $re;
		}elseif(is_int($source)){
			return $source;
		}else{
			if(($from == "big5" && $to == "gbk")||($from == "gbk" && $to == "big5")) $flag = false;
			else $flag = true;
			if(function_exists('mb_convert_encoding') && $to != 'pinyin' && $flag){
				return mb_convert_encoding($source,$to,$from);
			}elseif(function_exists('iconv') && $to != 'pinyin' && $flag){
				strcasecmp('utf8', $from) || $from = 'utf-8';
				strcasecmp('gb2312', $from) || $from = 'gbk';
				strcasecmp('utf8', $to) || $to = 'utf-8';
				strcasecmp('gb2312', $to) || $to = 'gbk';
				return iconv($from, $to."//IGNORE", $source);
			}else{
				if($to=='pinyin'){ // 拼音转换,专用
					return self::Pinyin($source);
				}else{ // chinese中拼音转换,不使用了
					include_once _08_INCLUDE_PATH."chinese.cls.php";
					$chs = new chinese();
					$from = str_replace("utf-8","utf8",$from);
					$from = str_replace("gbk","gb2312",$from);
					$to = str_replace("utf-8","utf8",$to);
					$to = str_replace("gbk","gb2312",$to);
					$charset = array("utf8","gb2312","big5","unicode","pinyin");
					if(!in_array($from,$charset) || !in_array($to,$charset)){
						return '';
					}else{
						$from = strtoupper($from);
						$to = strtoupper($to);
						return $chs->Convert($from,$to,$source);
					}
				}
			}
		}
	}
    
    /**
     * 判断一个字符串是不是UTF8编码
     * 详情请查看：{@link http://www.w3.org/International/questions/qa-forms-utf-8.zh-hans.php?changelang=zh-hans}
     * 
     * @param  string $string 要判断的字符串
     * @return bool           如果是UTF8返回TRUE，否则返回FALSE
     */
    public static function isUTF8($string)
    {
        return (bool) preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $string);
    }
	
	/**将字串转为拼音
	  *@param int $need_first_letter 是否要返回 字符串转化后的手写字母以及全拼组合成的字符串,两者用逗号分割开来
	  *								 eg:东湖花园  返回   dhhy,donghuhuayuan
	  */
	public static function Pinyin($_String,$need_first_letter = 0){	
		global $mcharset;
		include_once _08_INCLUDE_PATH."encoding/pinyin.table.php";
		$pytab = pycfgTab(); 
		$mcharset != 'gbk' && $_String = self::iconv($mcharset,'GB2312',$_String);
		$cset = 2; //GB2312中文占两个字节
		$py=""; 
		$first_letter = '';//取汉字转为拼音后，每个单词的首字母组合成的字符串
		$p = 0;
		$len = strlen($_String);
		for($i = 0;$i < $len; $i++){   
			$ch = substr($_String,$p,1);
			if(ord($ch)<160){ //160(10)=11xxxxxx(2)高位,表示两个字节的汉字
			  $py .= $ch;
			  $first_letter .= $ch;
			  $p++;
			}else{
			  $ch = substr($_String,$p,$cset);  
			  $py .= self::py__One($ch, $pytab); 
			  $first_letter .= substr(self::py__One($ch, $pytab),0,1);
			  $p += $cset; 
			}
			if($p>=$len) break;
		}   
		return empty($need_first_letter)?$py:addslashes(str_replace(array('(',')',' ','\\'),'',$first_letter.",".$py)); 
	}
	
	public static function py__One($chr, $tab=''){
	  if(empty($tab)){
		include_once _08_INCLUDE_PATH."encoding/pinyin.table.php";
		$tab = pycfgTab();
	  }
	  $p = strpos($tab,$chr); 
	  $t = substr($tab,0,$p);
	  $t = strrchr($t,"(");
	  $p = strpos($t,")");
	  $t = substr($t,1,$p-1);
	  return $t;   
	}
	
	//取得字串的首字母
	public static function FirstLetter($string, $number=0, $first=1){
		global $mcharset;
		$cset = 2; //GB2312中文占两个字节
		if($first) $mcharset != 'gbk' && $string = self::iconv($mcharset, 'GB2312', $string);
		$p = 0;
		for($i=0, $l = strlen($string); $i < $l; $i++){
			$_P = ord($_Z = $string{$i});
			if($_P>160){
				//$pytab = pycfgTab(); 
				$ch = self::py__One(substr($string,$p,$cset)); 
				if($ch){
					return strtoupper(substr($ch,0,1));
				}else{
					$p += $cset; 
					if($p>=$l) return '';
					self::FirstLetter(substr($string,$cset), $number, 0);
				}
			}elseif($_P >= 65 && $_P < 91){
				return $_Z;
			}elseif($_P >= 97 && $_P < 123){
				return chr($_P - 32);
			}elseif($number && $_P >= 48 && $_P < 58){
				return $_Z;
			}
		}
		return '';
	}
	
	/**
	*隐藏电话,手机,邮件,qq,ip的中间一部分
	*$char为替换的字符,默认为*
	*/
	public static function SubReplace($str,$char=''){
		$char = empty($char) ? '*' : $char;
		if(strpos($str,'@')>0){
			$a = explode('@',$str);
			$suf = '@'.$a[1];
			$str = $a[0];
		}else{
			$suf = '';
		}
		$len = strlen($str);
		if($len<3) return $str.$suf;
		if($len<6) $n = 2;
		else $n = 4;
		$start = ($len-6)<1 ? 1 : $len-6;
		$re = ''; for($i=0;$i < $n;$i++) $re .= $char;
		if (preg_match("/^[\x7f-\xff]+$/", $str)) {
			// 中文处理
			$len = self::WordCount($str);
			if($len<1) return $str.$suf;
			if($len<4) $n = 2;
			else $n = 4;
			$start = ($len-4)<1 ? 1 : $len-4;
			$re = ''; for($i=0;$i < $n;$i++) $re .= $char;
			$str = substr_replace($str,$re,$start*2);
		}else{
			$str = substr_replace($str,$re,$start,$n);
		}
		return $str.$suf;
	}

	public static function isEmail($email){
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}
	public static function isDate($date, $mode = 0) {
		if(!empty($date) && strlen($date) < 20 &&
			preg_match('/^([12][0-9]{3})-([01]?\d)-([0123]?\d)(?: ([012]?[0-9]):([0-9]{1,2}):([0-9]{1,2}))?$/', $date, $match) &&
			checkdate(intval($match[2]), intval($match[3]), intval($match[1]))){
				return $mode ? ($match[4] >= 0 && $match[4] < 24 && $match[5] >= 0 && $match[5] < 60 && $match[6] >= 0 && $match[6] < 60) : count($match) < 6;
		}
		return false;
	}

	//生成随机字串
	public static function Random($length, $types = 0) {
		if($types == 1) {
			$result = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$result = '';
			$chars = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
            if ( $types != 2 )
            {
                $chars .= '0123456789';
            }
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++) {
				$result .= $chars[mt_rand(0, $max)];
			}
		}
		return $result;
	}
	
    /**
     * 格式化一个字串，使其可作为PHP变量名或数据库字段名，（过滤'字母数字_'之外的字符)
     * 
     * @param  string $string 要过滤的文件参数
     */
    public static function ParamFormat( $string  = ''){
        return preg_replace('/[^\w]/', '', $string);
    }
    
    /**
     * 排序一个字符串
     * 
     * @param string $string   要排序的字符串
     * @param string $fcuntion 用于排序的方法，具体可用方法可查看数组的排序方法
     * @param mixed  $callable 对应排序方法的参数
     * 
     * @since nv50
     */
    public static function sort( &$string, $function = '', $callable = array() )
    {
        if ( empty($function) )
        {
            $function = 'sort';
        }
        $stringArray = str_split($string);
        if ( empty($callable) )
        {
            $function($stringArray);
        }
        else
        {
        	$function($stringArray, $callable);
        }
        
        $string = implode('', $stringArray);
    }
    
    /**
     * 对URI进行排序
     * 
     * @param string $uri 要排序的URI字符串
     * @since nv50
     */
    public static function sortURI( &$uri )
    {        
        parse_str($uri, $uriArray);
        ksort($uriArray);
        $uri = http_build_query($uriArray);
    }
}
