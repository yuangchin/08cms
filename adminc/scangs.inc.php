<?php
!defined('M_COM') && exit('No Permission');
$cuid = 6;
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');

$channels = cls_cache::Read('channels');
$uclasses = cls_Mspace::LoadUclasses($memberid);
$ucidsarr = array();foreach($uclasses as $k => $v) if($v['cuid'] == $cuid) $ucidsarr[$k] = $v['title'];
submitcheck('bfilter') && $page = 1;

$ucid = empty($ucid) ? 0 : max(0,intval($ucid));
$chid = empty($chid) ? 3 : max(0,intval($chid));

if(in_array($chid,array(106,13))){ $navid = ''; }
elseif($chid>=9 && $chid<=10){ $navid = 'scxuqiu'; }
elseif($chid>=115 && $chid<=120){ $navid = 'scshye'; }
elseif($chid>=2 && $chid<=3){ $navid = 'scangs'; }
else { cls_message::show('参数错误。'); }

$page = !empty($page) ? max(1, intval($page)) : 1;
$ccid1 = empty($ccid1) ? 0 : max(0,intval($ccid1));
$keyword = empty($keyword) ? '' : $keyword;

$aTabCols = " ".aurl_fields().",caid".($chid==106 ? '' : ',ccid1')." ";
$selectsql = "SELECT cu.*,cu.createdate AS cucreat".$aTabCols."";
$wheresql = " WHERE cu.mid='$memberid'";
$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}".atbl($chid)." a ON a.aid=cu.aid";

if($ucid) $wheresql .= " AND cu.ucid='$ucid'";
if($chid) $wheresql .= " AND a.chid='$chid'";
if($ccid1 && $cnsql = cnsql(1,sonbycoid($ccid1,1),'a.')) $wheresql .= " AND $cnsql";
$keyword && $wheresql .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";

$filterstr = '';
foreach(array('keyword','chid') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
if(!submitcheck('bsubmit')){
	$navid && backnav($navid,"ch$chid");	
	echo form_str('newform',"?action=$action&page=$page");
	tabheader_e();
	echo "<tr><td class=\"item2\"><input name='chid' type='hidden' value='$chid'>";
	echo "<div class='filter'><input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\"  size=\"20\" placeholder=\"请输入标题\" style=\"vertical-align: middle;\" title=\"搜索\">&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"ucid\">".makeoption(array(0 => '收藏分类') + $ucidsarr,$ucid)."</select>&nbsp; ";	
	if(!in_array($chid,array(106))) echo '<span>'.cn_select("ccid1",array('value' => $ccid1,'coid' => 1,'notip' => 1,'addstr' => '不限地域','vmode' => 0,'framein' => 1,)).'</span>&nbsp; ';
	echo strbutton('bfilter','筛选');
	echo "</div></td></tr>";
	tabfooter();
	tabheader("我收藏的文档&nbsp; <a href=\"?action=uclasses&cuid=$cuid\" onclick=\"return floatwin('open_uclasses',this)\">>>收藏分类</a>",'','',9);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('收藏标题','left'),);
	$cy_arr[] = '分类';
	$cy_arr[] = '类型';
	if(!in_array($chid,array(106))) $cy_arr[] = '地域';
	$cy_arr[] = '收藏时间';
	trcategory($cy_arr);
	
	$pagetmp = $page;
	do{
		$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);

	$itemstr = ''; 
	while($r = $db->fetch_array($query)){
		$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
		$subjectstr = "<a href=\"".cls_ArcMain::Url($r)."\" target=\"_blank\">$r[subject]</a>";
		$ucidstr = empty($ucidsarr[$r['ucid']]) ? '-' : $ucidsarr[$r['ucid']];
		$channelstr = @$channels[$r['chid']]['cname'];
		$coclasses = cls_cache::Read('coclasses',1);
		$ccid1str = @$coclasses[$r['ccid1']]['title'];
		$adddatestr = date('Y-m-d',$r['cucreat']);

		$itemstr .= "<tr><td class=\"item\" width=\"40\">$selectstr</td><td class=\"item2\">$subjectstr</td>\n";
		$itemstr .= "<td class=\"item\">$ucidstr</td>\n";
		$itemstr .= "<td class=\"item\">$channelstr</td>\n";
		if(!in_array($chid,array(106))) $itemstr .= "<td class=\"item\">$ccid1str</td>\n";
		$itemstr .= "<td class=\"item\" width=\"100\">$adddatestr</td>\n";
		$itemstr .= "</tr>\n";
	}
	echo $itemstr;
	tabfooter();
	$sqlx = "SELECT count(*) $fromsql $wheresql "; 
	if(!$chid){
		$sqlt1 = str_replace("{$tblprefix}archives","{$tblprefix}archives11",$sqlx);
		$sqlt2 = str_replace("{$tblprefix}archives","{$tblprefix}archives16",$sqlx);
		$sqln = $db->result_one($sqlt1) + $db->result_one($sqlt2);
	}else{
		$sqln = $db->result_one($sqlx);
	} 
	echo multi($sqln,$mrowpp,$page, "?action=$action$filterstr");
	
	tabheader('批量操作');
	trbasic("<label><input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\"> 删除收藏</label>",'','将选中资料从收藏夹中删除','');
	trbasic("<label><input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[ucid]\" value=\"1\"> 收藏分类</label>",'arcucid',makeoption(array('0' => '取消分类') + $ucidsarr),'select');
	tabfooter('bsubmit');
}else{
	
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
	if(empty($selectid)) cls_message::show('请选择资料。',axaction(1,M_REFERER));
	foreach($selectid as $k){
		$k = empty($k) ? 0 : max(0,intval($k));
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
			continue;
		}
		if(!empty($arcdeal['ucid'])){
			$db->query("UPDATE {$tblprefix}$commu[tbl] SET ucid='$arcucid' WHERE cid='$k'");
		}
	}
	
	cls_message::show('收藏批量操作成功。',"?action=$action&page=$page$filterstr");
	
	
}

?>