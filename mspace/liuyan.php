<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/adminm.fun.php";
$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.urlencode($forward);
$cuid = 5;

$mid = empty($mid) ? 0 : max(0,intval($mid));
if(!$mid) cls_message::show('请指定需要留言的对象。');
if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('留言功能已关闭。');
$auser = new cls_userinfo;
$auser->activeuser($mid);
if(!$auser->info['mid'] || !$auser->info['checked'] || !in_array($auser->info['mchid'],$commu['chids'])) cls_message::show('请指定需要留言的对象');
$fields = cls_cache::Read('cufields',$cuid);
if(!submitcheck('bsubmit')){
	_header();
	tabheader('我来说几句','commuadd',"?mid=$mid$forwardstr",2,1,1);
	$a_field = new cls_field;
	foreach($fields as $k => $v){
		if(in_array($k,array('reply'))) continue;
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
	if(!$curuser->pmbypmid($commu['pmid'])) cls_message::show('您没有留言权限。',axaction(2,M_REFERER));
	if(!empty($commu['repeattime']) && !empty($m_cookie["08cms_cuid_{$cuid}_{$mid}"])) cls_message::show('操作请不要过于频繁。',axaction(2,M_REFERER));
	#cookie判断当前是否已经操作过了。
	$sqlstr = "tomid='$mid',tomname='{$auser->info['mname']}',ip='$onlineip',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp'";
	if($curuser->pmautocheck($commu['autocheck'],'cuadd')) $sqlstr .= ",checked=1";
	$c_upload = new cls_upload;	
	$a_field = new cls_field;
	foreach($fields as $k => $v){
		if(isset($fmdata[$k])){
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
		cls_message::show('留言成功。',axaction(10,$forward));
	}else{
		$c_upload->closure(1);
		cls_message::show('留言不成功。',axaction(10,$forward));
	}
}
		

?>

