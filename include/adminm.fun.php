<?php
/*
* 会员中心专用函数，核心函数
* 扩展系统专用或带有样式的函数，另外存入到 MC_ROOTDIR."func/main.php"
*/
!defined('M_COM') && exit('No Permission');
define('MC_DIR',empty($mc_dir) ? 'adminm' : $mc_dir);
define('MC_ROOTDIR',M_ROOT.MC_DIR.'/');
define('MC_ROOTURL',$cms_abs.MC_DIR.'/');
include_once M_ROOT."include/admin.fun.php";
include_once MC_ROOTDIR."func/main.php";
//会员中心的函数入口，功能性函数，多种模板共用的函数，不涉及风格的函数在这里

#站内信息统计
function pmstat(){
	static $stat = array();
	global $db, $tblprefix, $memberid;
	if(!$stat){
		$row = $db->fetch_one("SELECT COUNT(pmid) AS pms,SUM(viewed) AS views FROM {$tblprefix}pms WHERE toid='$memberid'");
		$stat[0] = $row['pms'] - $row['views'];
		$stat[1] = $row['pms'];
	}
	return $stat;
}
// 会员中心-显示提示
// key提示ID,或提示文本本身"
// type: 显示模式;
//     默认空-直接显示内容;
//     tip-可隐藏的提示框;
//     fix-固定的提示框
// 可用占位符变量,如:{$cms_version},请确保全局中有定义过
function m_guide($key,$type=''){ //$mguide
	if(empty($key)) return;
	if(preg_match("/^[a-zA-Z][a-z_A-Z0-9]{2,31}$/",$key)){ // 后台可管理的注释
		$file = M_ROOT.'dynamic/mguides/'.$key.'.php';
		if(is_file($file)){
			include $file;
			if(empty($mguide)) return;
			$msg = "<!--$key-->$mguide";
		}else{
			if(!_08_DEBUGTAG) return;
			$msg = "<span style='color:#F0F;'>暂无提示! </span>请联系管理员, 可在 [管理后台&gt;&gt;系统设置&gt;&gt;会员中心&gt;&gt;会员中心注释] 处, 添加ID为 [{$key}] 的注释";
		}
	}else{ // 普通文本,处理一个唯一key值,用于tip状态下显示
		$msg = $key;
		$key = md5($key);
	}
	if($type=='fix'){ // 固定的提示框
    	$msg = "<div id='tipm_ptop_bot' class='tipm_botmsg tipm_tclose_out'>$msg</div>";
	}elseif($type=='tip'){ // 可隐藏的提示框
		$msg = "<div id='tipm_ptop_lamp_$key' class='tipm_topen_out'>
		<div class='tipm_topen' onclick='ftip_open(\"$key\")'>&nbsp;</div></div>
		<div id='tipm_ptop_msg_$key' class='tipm_tclose_out'>
		<div class='tipm_tclose' onclick='ftip_close(\"$key\")'>&nbsp;</div>$msg</div>
		<script type='text/javascript'>ftip_inti('$key');</script>";
	}
	// 处理-占位符变量(要支持数组参考#(\[[a-z_A-Z0-9]{1,32}\])*#)
	$msg = str_replace('{$','{',$msg);
	$msg = key_replace($msg,array(array()));
	echo $msg;
}
