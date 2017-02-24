<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/adminm.fun.php";
$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.urlencode($forward);
$cuid = 32;
$mid = empty($mid) ? 0 : max(0,intval($mid));
if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('免费量房功能已关闭。');

$fields = cls_cache::Read('cufields',$cuid);
if(!submitcheck('bsubmit')){
	_header();
	tabheader('请填写免费量房的消息','commuadd',"?$forwardstr",2,1,1);
	$a_field = new cls_field;
	foreach($fields as $k => $v){
		$a_field->init($v);
		$a_field->isadd = 1;
		$a_field->trfield('fmdata');
	}
	unset($a_field);
	tr_regcode("commu$cuid");
	tabfooter('bsubmit');
	_footer();
}else{//数据处理
	_header();
	if(!regcode_pass("commu$cuid",empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误',axaction(2,M_REFERER));
	if(!$curuser->pmbypmid($commu['pmid'])) cls_message::show('您没有量房权限。',axaction(2,M_REFERER));
	$companyIds = empty($companyIds) ? array() : explode(',',$companyIds[0]);
	array_pop($companyIds);
	if(empty($companyIds)) cls_message::show('请指定装修公司。');
	if(!empty($commu['repeattime']) && !empty($m_cookie["08cms_cuid_{$cuid}_{$mid}"])) cls_message::show('操作请不要过于频繁。',axaction(2,M_REFERER));
	#cookie判断当前是否已经操作过了。
	$auser = new cls_userinfo;	
	
	foreach($companyIds as $m){		
		$auser->activeuser($m);	
		if(!$auser->info['mid'] || !$auser->info['checked'] || !in_array($auser->info['mchid'],$commu['chids'])) cls_message::show('请选择装修公司。');
		$sqlstr = "tomid='$m',tomname='{$auser->info['mname']}',ip='$onlineip',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp'";
		if($curuser->pmautocheck($commu['autocheck'],'cuadd')) $sqlstr .= ",checked=1";
		$c_upload = new cls_upload;	
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				if($k=='fengge' && !is_array($fmdata[$k])) $fmdata[$k] = explode("\t",$fmdata[$k]);
				$a_field->init($v);
				$fmdata[$k] = $a_field->deal('fmdata','mcmessage',axaction(2,M_REFERER));
				$sqlstr .= ",$k='$fmdata[$k]'";
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $sqlstr .= ",{$k}_x='$y'";
			}
		}
		unset($a_field);		
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET $sqlstr");
		if($cid = $db->insert_id()){
			if(!empty($commu['repeattime'])) msetcookie("08cms_cuid_{$cuid}_{$mid}",1,$commu['repeattime'] * 60);
		#设置操作成功后设置cookie
			$c_upload->closure(1,$cid,"commu$cuid");
			$c_upload->saveuptotal(1);
			unset($c_upload);
		}
	}
	cls_message::show('免费量房提交成功。',axaction(10,$forward));
}
		

?>

