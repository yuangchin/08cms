<?php
#@set_time_limit(0);
class http{
	var $link, $host, $port, $url, $status, $referer;
	var $ret, $content, $timestamp, $jump = 3, $timeout = 5;
	var $gets = array(), $cookie = '', $data;
	var $puts = array(), $cookies = array(), $datas = array();
	function open(){//连接到服务器
		$this->link && fclose($this->link);
		$this->timeout || $this->timeout = 0x7fffffff;
		if(!$this->link = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout))return false;
		socket_set_timeout($this->link, $this->timeout);
		return true;
	}
	function close(){
		$this->link && fclose($this->link);
		$this->link = 0;
	}
	function setHeader($key,$val){//添加自定义HTTP头
		$this->puts[$key]=$val;
	}
	function setCookie($key,$val){//发送COOKIE
		$key = urlencode($key);
		#去除重复的cookie
		$this->cookie && $this->cookie = preg_replace('/;\s*' . preg_quote($key, '/') . '=[^;]*/', '', ";$this->cookie");
		$this->cookie = "$key=" . urlencode($val) . $this->cookie;
	}
	function setCookies($str){//输入cookies字串
		if(!$str)return;
		if($this->cookie){
			if(!preg_match_all('/(?:^|;\s*)([^=]+)/', $str, $tmp))return;
			#去除重复的cookie
			$key = '';
			foreach($tmp[1] as $v)$key .= '|' . preg_quote($v, '/');
			$this->cookie = preg_replace('/;\s*(?:' . substr($key, 1) . ')=[^;]*/', '', ";$this->cookie");
		}
		$this->cookie = "$str$this->cookie";
	}
	function setData($key,$val){//POST数据
		$this->datas[]=array($key,$val);
	}
	function query($uri,$mode='HEAD',$jump=0){//发送HTTP请求，并获得响应数据
		$this->ret=$ret=false;
		$jump || $this->timestamp=time();
		$this->referer && $this->puts['Referer'] = $this->referer;
		if($uri{0}!='/'){
			$this->referer = $uri;
			$uri=parse_url($uri);
			if(empty($uri['host']))return false;
			$this->scheme = strtolower($uri['scheme']);
			$this->host=$this->scheme == 'https' ? "ssl://$uri[host]" : $uri['host'];
			$this->port=isset($uri['port']) ? $uri['port'] : ($this->scheme == 'https' ? 443 : 80);
			$path=isset($uri['path']) ? $uri['path'] : '/';
			$uri=isset($uri['query']) ? "$path?$uri[query]" : $path;
		}else{
			$this->referer = "$this->scheme://$this->host" . ($this->port != 80 ? ":$this->port" : '') . $uri;
			$path=strpos($uri,'?');
			$path=$path===false ? $uri : substr($uri,0,$path);
		}
		#未转到的字符，不知道还有哪些
#		$uri = str_replace(' ', '%20', $uri);
		$uri = rawurldecode($uri);
		if($jump > $this->jump || !$this->open())return false;
		$mode=strtoupper($mode);
		$this->putHeader($uri,$mode);//发送HTTP请求
		$this->gets=array();
		$flag = 0;
		while(!feof($this->link)){//获得HTTP头
			$meta = stream_get_meta_data($this->link);
			if($meta['timed_out'])return false;
			if($tmp = rtrim(fgets($this->link, 4096))){
				if(!$flag){
					$flag = 1;
					$tmp=explode(' ', $tmp);
					if(empty($tmp[1]) || !is_numeric($tmp[1]))return false;
					$this->status=$tmp[1];//HTTP状态码
				}else{
					$tmp=explode(':',$tmp, 2);
					$key=strtolower($tmp[0]);
					$tmp[1] = trim($tmp[1]);
					if($key=='set-cookie'){
						empty($this->gets[$key]) ? $this->gets[$key] = array($tmp[1]) : $this->gets[$key][] = $tmp[1];
						if(preg_match('/(.+?)=.+?(?=;|$)/', $tmp[1], $tmp)){
							#去除重复的cookie
							$this->cookie && $this->cookie = preg_replace('/;\s*' . preg_quote($tmp[1], '/') . '=[^;]*/', '', ";$this->cookie");
							$this->cookie = "$tmp[0]$this->cookie";
						}
					}else{
						$this->gets[$key] = $tmp[1];
					}
				}
			}elseif($flag){
				break;
			}
		}
		switch($this->status{0}){
		case '3'://重定向
			if(!empty($this->gets['location']) && $uri=$this->fullurl($this->gets['location'], $path)){
				$timeout = $this->timeout;
				$this->timeout = $timeout - time() + $this->timestamp;
				$ret = $this->query($uri, $mode, $jump+1);
				$this->timeout = $timeout;
			}
			break;
		case '2':
			$ret = true;
		}
		return $ret;
	}
	function size($url,$all=0){//取得远程文件大小
		$this->query($url);
		return isset($this->gets['content-length']) ? intval($this->gets['content-length']) : ($all && $this->content() ? strlen($this->content) : false);
	}
	function exists($url){//远程文件是否存在
		return $this->query($url);
	}
	function istext($url=''){
		$url && $this->query($url);
		return isset($this->status) && isset($this->gets['content-type']) && $this->status{0}=='2' && strpos(strtolower($this->gets['content-type']),'text')===0;
	}
	function content($url = '',$maxsize = 0,$mode = 'GET'){//获得远程内容
		$url && $ret = $this->query($url, $mode);
		if($url && !$ret)return false;
		if($this->ret)return $this->content !== false;
		$maxsize *= 1024;
		$this->ret = true;
		$this->content = '';
		$gzip = !empty($this->gets['content-encoding']) && preg_match('/\bgzip\b/', $this->gets['content-encoding']);
		if(!empty($this->gets['transfer-encoding']) && preg_match('/\bchunked\b/', $this->gets['transfer-encoding'])){
			/*
			 *	Transfer-Encoding: chunked
			 *	分块头的数据格式
			 *	chunk-size[;chunk-extension]CRLF
			 *	chunk-dataCRLF
			 *	重复上两行零次或多次
			 *	0CRLF
			 *	以下可有可无
			 *	*(entity-header CRLF)
			 *
			 */
			while(($tmpstr = fgets($this->link)) && $strlen = (int)hexdec(($split = strpos($tmpstr, ';')) ? substr($tmpstr, 0, $split) : $tmpstr)){
				$string = '';
				while($read = $strlen - strlen($string)){
					$string .= fread($this->link, $read);
				}
				fgets($this->link);	#丢掉后面的\r\n
				$this->content .= $string;
			}
			while(fgets($this->link, 8192));	#丢掉多余的头
		}else{
			if(!isset($this->gets['connection']) || strtolower($this->gets['connection']) != 'close'){
				$strlen = intval($this->gets['content-length']);
				while($read = $strlen - strlen($this->content)){$this->content .= fread($this->link, $read);}
			}else{
				while(!feof($this->link))$this->content .= fread($this->link, 8192);
			}
		}
		$meta = stream_get_meta_data($this->link);
		$gzip && $this->content = $this->gzdecode($this->content);
		if($meta['timed_out'] || ($maxsize && strlen($this->content) > $maxsize)){
			#超时或超过限制大小
			$this->content = false;
			$this->close();
			return false;
		}
		return true;
	}
	function fetchtext($url,$mode='GET'){//取得远程文本
		isset($this->puts['Accept-Encoding']) || $this->puts['Accept-Encoding'] = 'gzip, deflate';
		if(!$this->query($url,$mode) || !$this->istext()){
			$this->close();
			return '';
		}
		return $this->content() ? $this->content : '';
	}
	function savetofile($url,$savename,$maxsize=0,$mode='GET'){//取得远程文件保存到本地，会覆盖同名文件
		if(!$this->query($url,$mode))return false;
		if($maxsize && isset($this->gets['content-length']) && intval($this->gets['content-length']) > $maxsize * 1024 || !$this->content('',$maxsize))return false;
		if(!$fp = fopen($savename,'wb'))return false;
		fwrite($fp,$this->content);
		fclose($fp);
		return true;
	}
	function gzdecode($data){
		$flags = ord(substr($data, 3, 1));
		$headerlen = 10;
		$extralen = 0;
		$filenamelen = 0;
		if($flags & 4) {
			$extralen = unpack('v' ,substr($data, 10, 2));
			$extralen = $extralen[1];
			$headerlen += 2 + $extralen;
		}
		// Filename
		if($flags & 8)$headerlen = strpos($data, chr(0), $headerlen) + 1;
		// Comment
		if($flags & 16)$headerlen = strpos($data, chr(0), $headerlen) + 1;
		// CRC at end of file
		if($flags & 2)$headerlen += 2;
		$unpacked = @gzinflate(substr($data, $headerlen));
		if($unpacked === false)$unpacked = $data;
		return $unpacked;
	}
	function putHeader($uri,$mode){//发送HTTP头
		$str="$mode $uri HTTP/1.1\r\nHost: $this->host\r\nAccept: */*\r\nUser-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";

		foreach($this->puts as $k => $v)$str.="$k: $v\r\n";
		empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) || $str .= "Accept-Language: $_SERVER[HTTP_ACCEPT_LANGUAGE]\r\n";
		empty($this->cookie) || $str.="Cookie: $this->cookie\r\n";
		if($mode=='POST'){
			empty($this->data) && $this->data='';
			if(!empty($this->datas)){
				foreach($this->datas as $v)$this->data.="&$v[0]=".urlencode($v[1]);
				$this->datas=array();
				$this->data{0}=='&' && $this->data=substr($this->data,1);
			}
			$str.="Content-type: application/x-www-form-urlencoded\r\nContent-length: " . strlen($this->data) . "\r\n";
		}
		$str.="Connection: close\r\n\r\n";
		fputs($this->link, $str);
		if($mode=='POST' && $this->data)fputs($this->link, $this->data);//发送POST数据
	}
	function fullurl($u,$p){//地址修正
		if(!$u || strpos($u,'://'))return $u;
		if($u{0}=='?')$u="$p$u";elseif($u{0}!='/')$u=substr($p,0,strrpos($p,'/')+1).$u;
		while(($s=strpos($u,'/../'))!==false)$u=($s ? substr($u,0,strrpos(substr($u,0,$s),'/')+1) : '/').substr($u,$s+4);
		return $u;
	}
    
    /**
     * 清空缓存
     */ 
    public static function clearCache()
    {
        # 让它在过去就“失效”
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        # 永远是改动过的
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        # HTTP/1.1
        header("Cache-Control: no-store, no-cache , must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        # HTTP/1.0
        header("Pragma: no-cache");
        # IE6
        header("Cache-control: max-age=0");
    }
    
    /**
     * 获取某链接的HTTP头信息
     */
    public static function getHeaders( $url, $format = 0 )
    {
        $header = false;
        
        if ( function_exists('get_headers') )
        {
            $header = get_headers($url, $format);
        }
        
        return $header;
    }
}
?>