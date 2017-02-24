<?php
!defined('M_COM') && exit('No Permission');

$chid = 105; $cuid = 35;
$commu = cls_cache::Read('commu',$cuid);
$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$keyword = empty($keyword) ? '' : $keyword;

$selectsql = "SELECT cu.*,a.subject,a.chid,a.caid,a.customurl ";
$wheresql = " WHERE cu.mid='$memberid'";
$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}".atbl($chid)." a ON cu.aid=a.aid";

$keyword && $wheresql .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";

$filterstr = '';
foreach(array('keyword',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
$cid = empty($cid) ? 0 : max(0,intval($cid));

if($cid){
	if(!($row = $db->fetch_one("SELECT c.* FROM {$tblprefix}$commu[tbl] c WHERE c.cid='$cid'"))) cls_message::show('指定的报名记录不存在。');
	$row['mspacehome'] = cls_Mspace::IndexUrl($row);
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		tabheader("报名记录",'newform',"?action=$action&cid=$cid",2,1,1);
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
			$a_field->trfield('fmdata');
		}
		unset($a_field);
		tabfooter('bsubmit');
	}else{//数据处理
		$sqlstr = '';
		$c_upload = new cls_upload;
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
				$fmdata[$k] = $a_field->deal('fmdata','mcmessage',axaction(2,M_REFERER));
				$sqlstr .= ",$k='$fmdata[$k]'";
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $sqlstr .= ",{$k}_x='$y'";
			}
		}
		unset($a_field);
		$sqlstr = substr($sqlstr,1);
		$sqlstr && $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr  WHERE cid='$cid'");
		$c_upload->closure(1,$cid,"commu$cuid");
		$c_upload->saveuptotal(1);
		cls_message::show('报名记录编辑完成',axaction(6,M_REFERER));
	}
} else {
	if(!submitcheck('bsubmit')){
		echo form_str('newform',"?action=$action&page=$page");
		tabheader_e();
		echo "<tr><td class=\"item2\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索用户名\">&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader("预约量房列表",'','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('团购活动','left'),);
		$cy_arr[] = '手机';
		$cy_arr[] = '楼盘';
		$cy_arr[] = '审核';
		$cy_arr[] = '创建日期';
		$cy_arr[] = '明细';
		trcategory($cy_arr);

		$pagetmp = $page;
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$r['arcurl'] = cls_ArcMain::Url($r);
			$subjectstr = "<a href=\"$r[arcurl]\" target=\"_blank\">".mhtmlspecialchars($r['subject'])."</a>";
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$checkstr = $r['checked'] ? 'Y' : '-';
			$adddatestr = date('Y-m-d',$r['createdate']);
			$editstr = "<a href=\"?action=$action&cid=$r[cid]\" onclick=\"return floatwin('open_arcexit',this)\">编辑</a>";
			$itemstr .= "<tr><td class=\"item\" width=\"40\">$selectstr</td><td class=\"item2\">$subjectstr</td>\n";
			$itemstr .= "<td class=\"item\">$r[tel]</td>\n";
			$itemstr .= "<td class=\"item\">$r[szloupan]</td>\n";
			$itemstr .= "<td class=\"item\">$checkstr</td>\n";
			$itemstr .= "<td class=\"item\" width=\"100\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"item\" width=\"100\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action$filterstr");

		tabheader('批量操作');
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\"> 删除预约",'','将选中记录从列表中清除','');
		tabfooter('bsubmit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
		if(empty($selectid)) cls_message::show('请选择项目。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
				continue;
			}
		}
		cls_message::show('预约量房批量操作成功。',"?action=$action&page=$page$filterstr");
	}
}

?>