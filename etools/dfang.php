<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/common.fun.php";
include_once M_ROOT."./include/adminm.fun.php";

$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.urlencode($forward);
$cuid = 8; $chid = 11; //户型
$aid = empty($aid) ? 0 : max(0,intval($aid));
if(!$aid) cls_message::show('请指定需要订房的对象。');
if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('当前功能关闭。');
$arc = new cls_arcedit;
$arc->set_aid($aid,array('chid'=>5,'au'=>0));
if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])) cls_message::show('请指定需要订房的对象');
$fields = cls_cache::Read('cufields',$cuid);
$ht_str = empty($ht)?'':'&ht=1';//后台添加链接的时候，带上参数ht，用以去掉验证码
if(!submitcheck('bsubmit')){
	_header();
	tabheader('我要订房','commuadd',"?aid=$aid$forwardstr$ht_str",2,1,1);
	
	$fromsql = "FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}aalbums b ON b.inid=a.aid"; 
	$wheresql = "WHERE b.pid='$aid' "; 
	$query = $db->query("SELECT a.*,b.* $fromsql $wheresql");
	$str = "";
	while($r = $db->fetch_array($query)){
		$str .= "<input type='checkbox' name='fmdata[dghx][]' value='$r[aid]' id='dghx_$r[aid]'/>$r[subject]\n";
	} 

	trbasic('订购户型','',$str,''); 
	$a_field = new cls_field;
	foreach($fields as $k => $v){	
	  if($k!='dghx'){
		$a_field->init($v);
		$a_field->isadd = 1;
		$a_field->trfield('fmdata');
	  }
	}
	unset($a_field);
	empty($ht) && tr_regcode("commu$cuid");
	tabfooter('bsubmit');
	_footer();
}else{//数据处理
	_header();
	if(empty($ht)){
    	if(!regcode_pass("commu$cuid",empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误',axaction(2,M_REFERER));
	}
	if(!$curuser->pmbypmid($commu['pmid'])) cls_message::show('您没有网上订房的权限。',axaction(2,M_REFERER));
	
	if(!empty($curuser->info['isfounder']) || @in_array(-1,$a_funcs) || @in_array('normal',$a_funcs)) ; // 创始者或者后台管理员
	else if(!empty($commu['repeattime']) && !empty($m_cookie["08cms_cuid_{$cuid}_{$aid}"])) cls_message::show('操作请不要过于频繁。',axaction(2,M_REFERER));
	#cookie判断当前是否已经操作过了。
	$sqlstr = "aid='$aid',ip='$onlineip',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',checked=1";
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
		if(!empty($commu['repeattime'])) msetcookie("08cms_cuid_{$cuid}_{$aid}",1,$commu['repeattime'] * 60);
		#设置操作成功后设置cookie
		$c_upload->closure(1,$cid,"commu$cuid");
		$c_upload->saveuptotal(1);
		$curuser->basedeal("commu$cuid",1,1,"发表$commu[cname]",1);
		$db->query("UPDATE  {$tblprefix}archives_5 SET hdnum = hdnum + 1 where aid = '$aid' and hdnum != '0'");
		cls_message::show('网上订房成功。',axaction(2,M_REFERER));
	}else{
		$c_upload->closure(1);
		cls_message::show('网上订房不成功。',axaction(2,M_REFERER));
	}
}
		

?>

