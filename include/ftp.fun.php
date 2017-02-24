<?php
!defined('M_COM') && exit('No Permission');
#@set_time_limit(0);
class cls_ftp{
	var $conn_id = 0;
	function __construct(){
		$this->cls_ftp();
	}
	function cls_ftp(){
	}
	function mconnect($fhost,$fuser,$fpassword,$fpath,$fport = 21,$fpasv = 0,$timeout = 0,$fssl = 0){
		$func = $fssl && function_exists('ftp_ssl_connect') ? 'ftp_ssl_connect' : 'ftp_connect';
		if($func == 'ftp_connect' && !function_exists('ftp_connect')){
			$this->conn_id = 0;
			return -1;
		}
		if(!($this->conn_id = @$func($fhost,$fport,20))){
			$this->conn_id = 0;
			return -2;
		}
		if($timeout && function_exists('ftp_set_option')) @ftp_set_option($this->conn_id, FTP_TIMEOUT_SEC, $timeout);
		if(@!ftp_login($this->conn_id,$fuser, $fpassword)){
			$this->conn_id = 0;
			return -3;
		}
		@ftp_pasv($this->conn_id,$fpasv ? true : false);
		if(!$this->mchdir($fpath)){
			$this->conn_id = 0;
			return -4;
		}
		return $this->conn_id;
	}
	function mchdir($directory){
		if(!$this->conn_id) return false;
		return @ftp_chdir($this->conn_id,$directory);
	}
	function mmkdir($directory){
		if(!$this->conn_id) return false;
		return @ftp_mkdir($this->conn_id,$directory);
	}
	
	function mrmdir($directory){
		if(!$this->conn_id) return false;
		return @ftp_rmdir($this->conn_id,$directory);
	}
	
	function mput($remote_file,$local_file,$mode,$startpos = 0){//$mode:FTP_ASCII/FTP_BINARY
		if(!$this->conn_id) return false;
		$startpos = intval($startpos);
		return @ftp_put($this->conn_id,$remote_file,$local_file,$mode,$startpos);
	}
	
	function msize($remote_file){
		if(!$this->conn_id) return false;
		return @ftp_size($this->conn_id,$remote_file);
	}
	
	function mclose(){
		return @ftp_close($this->conn_id);
	}
	
	function mdelete($path){
		if(!$this->conn_id) return false;
		return @ftp_delete($this->conn_id,$path);
	}
	
	function mget($local_file,$remote_file,$mode,$resumepos = 0){
		if(!$this->conn_id) return false;
		return @ftp_get($this->conn_id,$local_file,$remote_file,$mode,$resumepos);
	}
	
	
	function msite($cmd){
		if(!$this->conn_id) return false;
		return @ftp_site($this->conn_id,$cmd);
	}
	
	function mchmod($mode,$filename){
		if(!$this->conn_id) return false;
		$mode = intval($mode);
		if(function_exists('ftp_chmod')){
			return @ftp_chmod($this->conn_id,$mode,$filename);
		}else{
			return @ftp_site($this->conn_id,'CHMOD '.$mode.' '.$filename);
		}
	}
}
function ftp_upload(&$target,&$url){
    static $deal_arr=array();$pwd = false;
	global $dir_userfile,$c_ftp,$ftp_enabled,$ftp_host,$ftp_port,$ftp_user,$ftp_password,$ftp_timeout,$ftp_pasv,$ftp_ssl,$ftp_dir,$authkey,$dir_userfile;
	if(!$ftp_enabled) return;
	if(empty($c_ftp->conn_id)){
		$c_ftp = new cls_ftp;
		$c_ftp->mconnect($ftp_host,$ftp_user,authcode($ftp_password,'DECODE',md5($authkey)),'/',$ftp_port,$ftp_pasv,$ftp_timeout,$ftp_ssl);
		if(!$c_ftp->conn_id) return 0;
	}
    $ftpurl = (in_array($ftp_dir,array('.','')) ? '' : '/' . $ftp_dir) . '/' . $url;
    if($deal_cnt = count($deal_arr)){        
        if($deal_arr[$deal_cnt-1] == $ftpurl){
            $pwd = true;
        }else{
            $deal_arr[] = $ftpurl;
        }
    }else{
        $deal_arr[] = $ftpurl;
    }
    #var_dump($deal_arr);
	$tmp = explode('/', $ftpurl);
	$count = count($tmp); $dest = $tmp[--$count];
	if(!$pwd && $count > 0){
		$i=0;
		while($i<$count){
			if(!$c_ftp->mchdir($tmp[$i])){
				$c_ftp->mmkdir($tmp[$i]);
				$c_ftp->mchmod(0777,$tmp[$i]);
				if(!$c_ftp->mchdir($tmp[$i])) return;
				$c_ftp->mput('index.htm',M_ROOT.$dir_userfile.'/index.htm', FTP_BINARY);
				$c_ftp->mput('index.html',M_ROOT.$dir_userfile.'/index.html', FTP_BINARY);
			}
			$i++;
		}
	}
	$nowdir = str_replace('/'.$dest,'',$ftpurl);
	$c_ftp->mchdir($nowdir); // 连续多次循环操作中,切换到当前目录    
	if(!$c_ftp->mput($dest,$target,FTP_BINARY)) return 0;
    $file = _08_FilesystemFile::getInstance();
	$file->delFile($target);
	return 1;
}
function ftp_del($path){
	global $c_ftp,$ftp_enabled,$ftp_host,$ftp_port,$ftp_user,$ftp_password,$ftp_timeout,$ftp_pasv,$ftp_ssl,$ftp_dir,$authkey;
	if(!$ftp_enabled) return;
	$path = $ftp_dir.'/'.$path;
	if(empty($c_ftp->conn_id)){
		$c_ftp = new cls_ftp;
		$c_ftp->mconnect($ftp_host,$ftp_user,authcode($ftp_password,'DECODE',md5($authkey)),'/',$ftp_port,$ftp_pasv,$ftp_timeout,$ftp_ssl);
		if(!$c_ftp->conn_id) return 0;		
	}
	return $c_ftp->mdelete($path);		
}
?>