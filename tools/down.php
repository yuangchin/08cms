<?php
# 取消支持下载模板，暂只支持文档附件的下载
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
cls_env::CheckSiteClosed();

# 初始化文档
$aid = empty($aid) ? 0 : max(0,intval($aid));
if(!$aid) cls_message::show('请指定正确的文档！');
$arc = new cls_arcedit();
$arc->set_aid($aid,array('au'=>0,'ch'=>1,));
if(empty($arc->aid))  cls_message::show('请指定正确的文档');
if(!$arc->archive['checked'])  cls_message::show('指定的文档未审'); 

# 初始化页面传参
$tname = empty($tname) ? '' : trim($tname); # 附件字段名称
$tmode = empty($tmode) ? false : true; # 是否单文件字段，false-下载集字段，true-单文件字段
$fid = empty($fid) ? 0 : max(0,intval($fid)); # 指定下载集内的附件序号，单文件可不指定

if(empty($arc->archive[$tname]))  cls_message::show('指定的附件不存在'); 
if(!cls_ArcMain::AllowDown($arc->archive))  cls_message::show('您没有当前文档的下载权限');

# 取得附件url
$url = '';
if(empty($tmode)){ #下载集字段
	if($temp = @unserialize($arc->archive[$tname])){
		$url = @$temp[$temparr['fid']]['remote'];
	}
}else{ # 单文件字段
	$temp = @explode('#',$arc->archive[$tname]);
	$url = @$temp[0];
}
if(empty($url)) cls_message::show('未找到指定的附件');
$url = cls_url::tag2atm($url);

# 下载扣积分处理
if($crids = $arc->arc_crids(1)){//需要对当前用户扣值//自动扣值
	$currencys = cls_cache::Read('currencys');
	$cridstr = '';
	foreach($crids as $k => $v){
		$cridstr .= ($cridstr ? ',' : '').abs($v).$currencys[$k]['unit'].$currencys[$k]['cname'];
	}
	if(!$curuser->crids_enough($crids)){
		cls_message::show('下载此附件需要支付积分 : &nbsp;:&nbsp;'.$cridstr.'<br><br>您没有下载此附件所需要的足够积分!');
	}
	$curuser->updatecrids($crids,0,'下载附件');
	$curuser->payrecord($arc->aid,1,$cridstr,1);
}

# 下载与统计
save_downs($aid,$arc->archive['chid']);//统计下载数
down_url($url);

function down_url($url){
	if(cls_url::islocal($url)){
		$url = cls_url::local_file($url);
		cls_atm::Down($url);
	}else{
		header("location:$url");
	}
	exit();
}
function save_downs($aid,$chid){//统计文档的下载数
	global $db,$tblprefix,$statweekmonth;
	if(!$aid || !$chid) return;
	$f = 'down';
	$db->query("UPDATE {$tblprefix}".atbl($chid)." SET $f=$f+1".($statweekmonth ? ",w$f=w$f+1,m$f=m$f+1" : '')." WHERE aid=$aid");
}