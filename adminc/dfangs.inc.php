<?php
!defined('M_COM') && exit('No Permission');
$cuid = 8;
$chid = 11; //户型
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
$catalogs = cls_cache::Read('catalogs');


$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
if($cid){
	if(!($row = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] WHERE cid='$cid'"))) cls_message::show('指定的网购记录不存在。');
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		$arc = new cls_arcedit;
		$arc->set_aid($row['aid'],array('chid'=>5,'au'=>0));
		$aid = $arc->aid;
		tabheader("网购记录编辑 &nbsp;<a href=\"".cls_ArcMain::Url($arc->archive)."\" target=\"_blank\">>>{$arc->archive['subject']}</a>",'newform',"?action=$action&cid=$cid",2,1,1);
		
		$fromsql = "FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}aalbums b ON b.inid=a.aid"; 
		$wheresql = "WHERE b.pid='$row[aid]' "; 
		$query = $db->query("SELECT a.*,b.* $fromsql $wheresql");
		$str = "";
		while($r = $db->fetch_array($query)){
			$checked = strstr("($row[dghx])",$r['aid'])?'checked="checked"':'';
			$str .= "<input type='checkbox' name='fmdata[dghx][]' value='$r[aid]' id='dghx_$r[aid]' $checked />$r[subject]\n";
		} 
		trbasic('订购户型','',$str,''); 
		$a_field = new cls_field;
		foreach($fields as $k => $v){
		  if($k!='dghx'){
			$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
			$a_field->trfield('fmdata');
		  }
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
		
		cls_message::show('网购记录编辑完成',axaction(6,M_REFERER));
	}
}elseif($aid){
	$arc = new cls_arcedit;
	$arc->set_aid($aid,array('chid'=>5,'au'=>0));
	if(!$arc->aid) cls_message::show('指定的文档不存在。');
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$keyword = empty($keyword) ? '' : $keyword;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
	
	$selectsql = "SELECT cu.*";
	$wheresql = " WHERE cu.aid='$aid'";
	$fromsql = "FROM {$tblprefix}$commu[tbl] cu";
	
	$keyword && $wheresql .= " AND cu.mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";
	$indays && $wheresql .= " AND cu.createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND cu.createdate<'".($timestamp - 86400 * $outdays)."'";
	
	$filterstr = '';
	foreach(array('keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	if(!submitcheck('bsubmit')){
		echo form_str('arcsedit',"?action=$action&aid=$aid&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索订房会员或联系人\">&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader("网上订购列表-<a style=\"color:#C00\" href=\"".cls_ArcMain::Url($arc->archive)."\" target=\"_blank\">{$arc->archive['subject']}</a>",'','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('会员','txtL'),);
		$cy_arr[] = array('联系人','txtL');
		$cy_arr[] = array('电话','txtL');
		$cy_arr[] = '添加时间';
		$cy_arr[] = '编辑';
		trcategory($cy_arr);
		
		$pagetmp = $page;
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$mnamestr = $r['mname'];
			$lxrenstr = cls_string::CutStr($r['lxren'],20);
			$lxdhstr = cls_string::CutStr($r['lxdh'],30);
			$adddatestr = date('Y-m-d',$r['createdate']);
			$editstr = "<a href=\"?action=$action&cid=$r[cid]\" onclick=\"return floatwin('open_commentsedit',this)\">详情</a>";
			$itemstr .= "<tr class=\"txt\"><td class=\"item\" >$selectstr</td><td class=\"item\">$mnamestr</td>\n";
			$itemstr .= "<td class=\"item\">$lxrenstr</td>\n";
			$itemstr .= "<td class=\"item\">$lxdhstr</td>\n";
			$itemstr .= "<td class=\"item\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"item\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$atpp,$page, "?action=$action&aid=$aid$filterstr");
		
		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除';
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
		if(empty($selectid)) cls_message::show('请选择网购记录。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
				continue;
			}
		}
		
		cls_message::show('网购批量操作成功。',axaction(0,M_REFERER));
	}
}else{
	$caid = empty($caid) ? 0 : max(0,intval($caid));
	$ccid1 = empty($ccid1) ? 0 : max(0,intval($ccid1));
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$keyword = empty($keyword) ? '' : $keyword;
	$indays = empty($indays) ? '' : max(0,intval($indays));
	$outdays = empty($outdays) ? '' : max(0,intval($outdays));
	
	$mchid = $curuser->info['mchid'];
	$sql_ids = "SELECT loupan FROM {$tblprefix}members_$mchid WHERE mid='$memberid'"; 
	$loupanids = $db->result_one($sql_ids); if($loupanids) $loupanids = substr($loupanids,1); 
	if(empty($loupanids)) $loupanids = 0; //echo "<BR>,$loupanids,";
	
	$selectsql = "SELECT cu.*,cu.createdate AS ucreatedate,a.createdate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject,a.ccid1,b.subject AS loupan";
	$wheresql = "WHERE a.aid IN(SELECT aid FROM {$tblprefix}".atbl(5)." WHERE chid='5' AND pid3 IN($loupanids))";
	$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}".atbl(5)." a ON a.aid=cu.aid INNER JOIN {$tblprefix}".atbl(4)." b ON b.aid=a.pid3";
	
	if($caid && $cnsql = cnsql(0,sonbycoid($caid),'a.')) $wheresql .= " AND $cnsql";
	if($ccid1 && $cnsql = cnsql(1,sonbycoid($ccid1,1),'a.')) $wheresql .= " AND $cnsql";
	$keyword && $wheresql .= " AND (a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' OR b.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%')";
	$indays && $wheresql .= " AND cu.createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND cu.createdate<'".($timestamp - 86400 * $outdays)."'";
	$wheresql = empty($no_list) ? ($wheresql ? 'WHERE '.substr($wheresql,5) : '') : 'WHERE 0';
	
	$filterstr = '';
	foreach(array('caid','ccid1','keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	if(!submitcheck('bsubmit')){
		echo form_str('arcsedit',"?action=$action&page=$page");
		trhidden('caid',$caid);
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索订购活动或楼盘\">&nbsp; ";
		echo '<span>'.cn_select("ccid1",array('value' => $ccid1,'coid' => 1,'notip' => 1,'addstr' => '地域','vmode' => 0,'framein' => 1,)).'</span>&nbsp; ';
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader(($caid ? @$catalogs[$caid]['title'] : '全部').'-网上订房列表','','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('所属订购活动','txtL'),);
		$cy_arr[] = array('所属楼盘','txtL');
		$cy_arr[] = array('会员','txtL');
		$cy_arr[] = '添加时间';
		$cy_arr[] = '编辑';
		trcategory($cy_arr);
		
		$pagetmp = $page; //echo "$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp";
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$subjectstr = "<a href=\"".cls_ArcMain::Url($r)."\" target=\"_blank\">$r[subject]</a>";
			$loupanstr = $r['loupan'];
			$mnamestr = $r['mname'];
			$catalogstr = @$catalogs[$r['caid']]['title'];
			$adddatestr = date('Y-m-d',$r['ucreatedate']);
			$editstr = "<a href=\"?action=$action&cid=$r[cid]\" onclick=\"return floatwin('open_commentsedit',this)\">详情</a>";
	
			$itemstr .= "<tr class=\"txt\"><td class=\"item\" >$selectstr</td><td class=\"item2\">$subjectstr</td>\n";
			$itemstr .= "<td class=\"item\">$loupanstr</td>\n";
			$itemstr .= "<td class=\"item\">$mnamestr</td>\n";
			$itemstr .= "<td class=\"item w100\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"item w35\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$atpp,$page, "?action=$action$filterstr");
		
		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除';
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
		if(empty($selectid)) cls_message::show('请选择网购记录。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			$k = empty($k) ? 0 : max(0, intval($k));
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
				continue;
			}
		}
		
		cls_message::show('网购批量操作成功。',"?action=$action&page=$page$filterstr");
		
		
	}
}
?>
