<?
!defined('M_COM') && exit('No Permission');
$currencys = cls_cache::Read('currencys');
$crid = empty($crid) ? 0 : max(0,intval($crid));
if($crid && empty($currencys[$crid])) cls_message::show('请指定正确的积分类型。');
if(empty($deal)){
	backnav('currency','record');
	$page = empty($page) ? 1 : max(1,intval($page));
	$mode = isset($mode) ? $mode : -1;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
	
	$wheresql = "WHERE mid='$memberid'";
	$fromsql = "FROM {$tblprefix}currency$crid";
	
	if($mode != -1) $wheresql .= " AND value".($mode ? '<' : '>')."0";
	$indays && $wheresql .= " AND createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND createdate<'".($timestamp - 86400 * $outdays)."'";
	
	$filterstr = '';
	foreach(array('indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('mode',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	
	echo form_str($action.'edit',"?action=$action&crid=$crid&page=$page");
	tabheader_e();
	echo "<tr><td class=\"item2\">";
	echo "<select style=\"vertical-align: middle;\" name=\"crid\" onchange=\"redirect('?action=$action&crid=' + this.options[this.selectedIndex].value);\">".makeoption(cridsarr(1),$crid)."</select>&nbsp; &nbsp; &nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"mode\">".makeoption(array('-1' => '增减模式','0' => '加值','1' => '减值',),$mode)."</select>&nbsp; ";
	echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
	echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
	echo strbutton('bfilter','筛选');
	tabfooter();
	
	$pagetmp = $page;
	do{
		$query = $db->query("SELECT * $fromsql $wheresql ORDER BY id DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);
	
	$itemstr = '';
	$sn = $pagetmp * $atpp;
	while($r = $db->fetch_array($query)){
		$sn ++;
		$modestr = $r['value'] > 0 ? '+' : '-';
		$createdatestr = date("$dateformat $timeformat", $r['createdate']);
		$currenystr = $crid ? $currencys[$crid]['cname'] : '现金';
		$dealnamestr = $r['fromid'] == 1 ? '管理员': (empty($r['fromname']) ? '-':$r['fromname']);
		$valuestr = abs($r['value']);
		$remarkstr = $r['remark'] ? "<a id=\"{$action}_info_$r[id]\" href=\"?action=$action&deal=remark&crid=$crid&id=$r[id]\" onclick=\"return showInfo(this.id,this.href)\">查看</a>" : '-';
		$itemstr .= "<tr><td class=\"item\">$sn</td>\n".
			"<td class=\"item2\">$dealnamestr</td>\n".
			"<td class=\"item\">$currenystr</td>\n".
			"<td class=\"item\">$modestr</td>\n".
			"<td class=\"item2\">$valuestr</td>\n".
			"<td class=\"item\">$createdatestr</td>\n".
			"<td class=\"item\">$remarkstr</td>\n".
			"</tr>\n";
	}
	
	tabheader(($crid ? $currencys[$crid]['cname'] : '现金').'变更日志','','',8);
	trcategory(array('序号',array('经手人','left'),'类型','加减',array('数量','left'),'操作日期','备注'));
	echo $itemstr;
	tabfooter();
	echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page,"?action=$action&crid=$crid$filterstr");
	m_guide("cur_notes",'fix');
}elseif($deal == 'remark' && $id){
	!($remark = $db->result_one("SELECT remark FROM {$tblprefix}currency$crid WHERE id='$id' AND mid='$memberid'")) && cls_message::show('请指定充扣值记录。');
	tabheader('积分变更备注');
	trbasic('备注说明','',$remark,'textarea');
	tabfooter();
}

?>