<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($action == 'ftpcheck'){
	include_once M_ROOT."include/ftp.fun.php";
	$checkmsg = '';
	$checkdir = substr(md5('08cms' + $timestamp), 12, 8);
	$checkfile = 'test.txt';
	@set_time_limit(0);
	$c_ftp = new cls_ftp;
	$conn_ret = $c_ftp->mconnect($ftp_host,$ftp_user,authcode($ftp_password,'DECODE',md5($authkey)),$ftp_dir,$ftp_port,$ftp_pasv,$ftp_timeout,$ftp_ssl);
	if($conn_ret == -1){
		$checkmsg = 'settings_remote_1';
	}elseif($conn_ret == -2){
		$checkmsg = 'settings_remote_2';
	}elseif($conn_ret == -3){
		$checkmsg = 'settings_remote_3';
	}elseif($conn_ret == -4){
		$checkmsg = 'settings_remote_4';
	}
	if(!$checkmsg){
		if(!$c_ftp->mmkdir($checkdir)){
			$checkmsg = 'settings_remote_mderr';
		}else{
			if(!(function_exists('ftp_chmod') && $c_ftp->mchmod(0777,$checkdir)) && !$c_ftp->msite("'CHMOD 0777 $checkdir'") && !@ftp_exec($c_ftp->conn_id,"SITE CHMOD 0777 $checkdir")){
				$checkmsg = 'settings_remote_chmoderr'.'\n';
			}
			$checkfile = $checkdir.'/'.$checkfile;
			if(!$c_ftp->mput($checkfile,M_ROOT.'robots.txt',FTP_BINARY)){
				$checkmsg .='settings_remote_uperr';
				$c_ftp->mdelete($checkfile);
				$c_ftp->mdelete($checkfile.'.uploading');
				$c_ftp->mdelete($checkfile.'.abort');
				$c_ftp->mrmdir($checkdir);
			}else{
				if(!@readfile($ftp_url.'/'.$checkfile)){
					$checkmsg .='settings_remote_geterr';
					$c_ftp->mdelete($checkfile);
					$c_ftp->mrmdir($checkdir);
				}else{
					if(!$c_ftp->mdelete($checkfile)){
						$checkmsg .= 'settings_remote_delerr';
					}else{
						$c_ftp->mrmdir($checkdir);
						$checkmsg ='settings_remote_ok';
					}
				}
			}
	
		}

	}
	echo '<script language="javascript" reload="1">alert(\''.addslashes($checkmsg).'\')</script>';

}elseif($action == 'dbcheck'){
	$checkmsg = '';
	cls_Array::array_stripslashes($dbsourcenew);
	if(in_str('********',$dbsourcenew['dbpw'])) $dbsourcenew['dbpw'] = authcode($dbsourcenew['dbpw0'],'DECODE',md5($authkey));
	$alertarr = array('add' => array('dbsourceadd','dbsourcesedit'),'edit' => array('dbsourcedetail','dbsourcedetail'),);
	$dbsourcenew['cname'] = trim(strip_tags($dbsourcenew['cname']));
	$dbsourcenew['dbhost'] = trim(strip_tags($dbsourcenew['dbhost']));
	$dbsourcenew['dbuser'] = trim(strip_tags($dbsourcenew['dbuser']));
	$dbsourcenew['dbname'] = trim(strip_tags($dbsourcenew['dbname']));
	if(empty($dbsourcenew['cname']) || empty($dbsourcenew['dbhost']) || empty($dbsourcenew['dbuser']) || empty($dbsourcenew['dbname'])){
		$checkmsg = '外部数据源资料不完全';
	}else{
		$s_db = & _08_factory::getDBO( 
            array('dbhost' => $dbsourcenew['dbhost'], 'dbuser' => $dbsourcenew['dbuser'], 'dbpw' => $dbsourcenew['dbpw'], 
                  'dbname' => $dbsourcenew['dbname'], 'pconnect' => 0, 'dbcharset' => $dbsourcenew['dbcharset'], 'flag' => false)
        );
		if(is_resource($s_db->link)){
			$checkmsg = '外部数据源连接正确';
		}else{
			$checkmsg = '外部数据源连接错误';
		}
		$s_db->close();
	}
	echo '<script language="javascript" reload="1">alert(\''.addslashes($checkmsg).'\')</script>';
}elseif($action == 'mailcheck'){
	$mail_to = trim(@$mconfigsnew['mail_to']);
	$mail_sign = trim(@$mconfigsnew['mail_sign']);
	$mail_smtp = trim(@$mconfigsnew['mail_smtp']);
	$mail_mode = trim(@$mconfigsnew['mail_mode']);
	$mail_port = trim(@$mconfigsnew['mail_port']);
	$mail_auth = trim(@$mconfigsnew['mail_auth']);
	$mail_from = trim(@$mconfigsnew['mail_from']);
	$mail_user = trim(@$mconfigsnew['mail_user']);
	$mail_pwd = trim(@$mconfigsnew['mail_pwd']);
	$mail_delimiter = trim(@$mconfigsnew['mail_delimiter']);
	$mail_silent = trim(@$mconfigsnew['mail_silent']);
	$checkmsg = mailto($mail_to,'08CMS测试邮件','08CMS测试邮件',array(),$mail_sign,1);
	if(!$checkmsg) $checkmsg = 'Email测试成功!';
	echo '<script language="javascript" reload="1">alert("'.preg_replace("/\n|\r\n?/","\\n",addslashes($checkmsg)).'");</script>';
}elseif($action == 'mtagcode'){

}
?>
