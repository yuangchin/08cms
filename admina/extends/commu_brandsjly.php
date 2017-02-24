<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('commu') || cls_message::show('no_apermission');
$cuid = 34;
array_intersect($a_cuids,array(-1,$cuid)) || cls_message::show('没有指定交互内容的管理权限');
$commu = cls_cache::Read('commu',$cuid);
$cid = empty($cid) ? 0 : max(0,intval($cid));
if($cid){
	if(!($row = $db->fetch_one("SELECT c.* FROM {$tblprefix}$commu[tbl] c WHERE c.cid='$cid'"))) cls_message::show('指定的留言记录不存在。');
	$fields = cls_cache::Read('cufields',$cuid);
	$row['mspacehome'] = cls_Mspace::IndexUrl($row);
	tabheader("留言记录",'','',2,1,1);
	$a_field = new cls_field;
	foreach($fields as $k => $v){
		$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
		$a_field->trfield('fmdata');
	}
	unset($a_field);
	tabfooter();
}else{
	$mchid = empty($mchid) ? 0 : max(0,intval($mchid));
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$checked = isset($checked) ? $checked : '-1';
	$keyword = empty($keyword) ? '' : $keyword;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));

	$selectsql = "SELECT cu.*";
	$wheresql = '';
	$fromsql = "FROM {$tblprefix}$commu[tbl] cu";

	if($checked != -1) $wheresql .= " AND cu.checked='$checked'";
	$keyword && $wheresql .= " AND (cu.mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' OR cu.tomname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%')";
	$indays && $wheresql .= " AND cu.createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND cu.createdate<'".($timestamp - 86400 * $outdays)."'";
	if($wheresql = substr($wheresql,5)) $wheresql = " WHERE $wheresql";

	$filterstr = '';
	foreach(array('mchid','keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('checked',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	if(!submitcheck('bsubmit')){
		echo form_str($actionid.'arcsedit',"?entry=extend$extend_str&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索商家或留言会员\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('-1' => '审核状态','0' => '未审','1' => '已审'),$checked)."</select>&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader('留言列表','','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('商家会员','txtL'),);
		$cy_arr[] = '留言会员';
		$cy_arr[] = '昵称';
		$cy_arr[] = '审核';
		$cy_arr[] = '留言时间';
		$cy_arr[] = '明细';
		trcategory($cy_arr);

		$pagetmp = $page;
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$u = new cls_userinfo;
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$u->activeuser($r['tomid']);
			$tomnamestr = $r['tomid'] ? "<a href=\"{$u->info['mspacehome']}\" target=\"_blank\">$r[tomname]</a>" : $r['tomname'];
			$r['mspacehome'] = cls_Mspace::IndexUrl($r);
			$mnamestr = $r['mid'] ? "<a href=\"$r[mspacehome]\" target=\"_blank\">$r[mname]</a>" : $r['mname'];
			$checkstr = $r['checked'] ? 'Y' : '-';
			$adddatestr = date('Y-m-d',$r['createdate']);
			$editstr = "<a href=\"?entry=extend$extend_str&cid=$r[cid]\" onclick=\"return floatwin('open_commentsedit',this)\">详情</a>";

			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td><td class=\"txtL\">$tomnamestr</td>\n";
			$itemstr .= "<td class=\"txtC\">$mnamestr</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[nicheng]</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$checkstr</td>\n";
			$itemstr .= "<td class=\"txtC w100\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		unset($u);
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$atpp,$page, "?entry=$entry$extend_str$filterstr");

		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除';
		$s_arr['check'] = '审核';
		$s_arr['uncheck'] = '解审';
		if($s_arr){
			$str = '';
			$i = 1;
			foreach($s_arr as $k => $v){
				$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"".($k=='delete'?' onclick="deltip()"':'').">$v &nbsp;";
				if(!($i % 5)) $str .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$str,'');
		}
		tabfooter('bsubmit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
		if(empty($selectid)) cls_message::show('请选择留言记录。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
				continue;
			}
			if(!empty($arcdeal['check'])){
				$db->query("UPDATE {$tblprefix}$commu[tbl] SET checked='1' WHERE cid='$k'");
			}elseif(!empty($arcdeal['uncheck'])){
				$db->query("UPDATE {$tblprefix}$commu[tbl] SET checked='0' WHERE cid='$k'");
			}
		}
		adminlog('留言列表管理');
		cls_message::show('留言批量操作成功。',"?entry=$entry$extend_str&page=$page$filterstr");
	}
}
?>