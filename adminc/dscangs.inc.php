<?php
!defined('M_COM') && exit('No Permission');

empty($mchannels) && $mchannels = cls_cache::Read('mchannels');

$cuid = 11;
$szqyarr = cls_cache::Read('coclasses',1); foreach($szqyarr as $k => $v) $szqyarr[$k] = $v['title'];
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
$uclasses = cls_Mspace::LoadUclasses($memberid);
$ucidsarr = array();foreach($uclasses as $k => $v) if($v['cuid'] == $cuid) $ucidsarr[$k] = $v['title'];

$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$ucid = empty($ucid) ? 0 : max(0,intval($ucid));
$mchid = empty($mchid) ? 0 : max(0,intval($mchid));
$szqy = empty($szqy) ? 0 : max(0,intval($szqy));
$keyword = empty($keyword) ? '' : $keyword;

$selectsql = "SELECT cu.*,m.*,s.szqy";
$wheresql = " WHERE cu.mid='$memberid'";
$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}members m ON m.mid=cu.tomid INNER JOIN {$tblprefix}members_sub s ON s.mid=cu.tomid";

if($ucid) $wheresql .= " AND cu.ucid='$ucid'";
if($mchid) $wheresql .= " AND m.mchid='$mchid'";
if($szqy && $caccsql = caccsql('s.szqy',sonbycoid($szqy,1),0)) $wheresql .= " AND $caccsql";
$keyword && $wheresql .= " AND cu.tomname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";

$filterstr = '';
foreach(array('keyword',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
if(!submitcheck('bsubmit')){
	
	echo form_str('newform',"?action=$action&page=$page");
	tabheader_e();
	echo "<tr><td class=\"item2\">";
	echo "<div class='filter'><input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"20\" placeholder=\"请输入标题\" style=\"vertical-align: middle;\" title=\"搜索店铺\">&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"ucid\">".makeoption(array(0 => '收藏分类') + $ucidsarr,$ucid)."</select>&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"mchid\">".makeoption(array(0 => '不限类型',2 => '经纪人',3 => '经纪公司',11 => '装修公司',12 => '品牌商家'),$mchid)."</select>&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"szqy\">".makeoption(array(0 => '不限地域') + $szqyarr,$szqy)."</select>&nbsp; ";
	echo strbutton('bfilter','筛选');
	echo "</div></td></tr>";
	tabfooter();
	tabheader("我收藏的店铺&nbsp; <a href=\"?action=uclasses&cuid=$cuid\" onclick=\"return floatwin('open_uclasses',this)\">>>收藏分类</a>",'','',9);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('店铺名称','left'),);
	$cy_arr[] = '分类';
	$cy_arr[] = '类型';
	$cy_arr[] = '地域';
	$cy_arr[] = '收藏时间';
	trcategory($cy_arr);
	
	$pagetmp = $page;

	do{
		$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);
	$u = new cls_userinfo;
	$itemstr = '';
	while($r = $db->fetch_array($query)){
		$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
		$u->activeuser($r['tomid']); $u->detail_data();
		$title = @$u->info['companynm']; $title || $title = @$u->info['cname'].@$u->info['xingming'];
		$mnamestr = "<a href=\"{$u->info['mspacehome']}\" target=\"_blank\">$title</a>";
		$ucidstr = empty($ucidsarr[$r['ucid']]) ? '-' : $ucidsarr[$r['ucid']];
		$mchannelstr = @$mchannels[$r['mchid']]['cname'];
		$szqystr = @$szqyarr[$r['szqy']];
		$adddatestr = date('Y-m-d',$r['createdate']);

		$itemstr .= "<tr><td class=\"item\" width=\"40\">$selectstr</td><td class=\"item2\">$mnamestr</td>\n";
		$itemstr .= "<td class=\"item\">$ucidstr</td>\n";
		$itemstr .= "<td class=\"item\">$mchannelstr</td>\n";
		$itemstr .= "<td class=\"item\">$szqystr</td>\n";
		$itemstr .= "<td class=\"item\" width=\"100\">$adddatestr</td>\n";
		$itemstr .= "</tr>\n";
	}
	echo $itemstr;
	tabfooter();
	echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action$filterstr");
	
	tabheader('批量操作');
	trbasic("<label><input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\"> 删除收藏</label>",'','将选中店铺从收藏夹中清除','');
	trbasic("<label><input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[ucid]\" value=\"1\"> 收藏分类</label>",'arcucid',makeoption(array('0' => '取消分类') + $ucidsarr),'select');
	tabfooter('bsubmit');
}else{
	
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
	if(empty($selectid)) cls_message::show('请选择店铺。',axaction(1,M_REFERER));
	foreach($selectid as $k){
		$k = empty($k) ? 0 : max(0, intval($k));
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
			continue;
		}
		if(!empty($arcdeal['ucid'])){
			$db->query("UPDATE {$tblprefix}$commu[tbl] SET ucid='$arcucid' WHERE cid='$k'");
		}
	}
	cls_message::show('收藏店铺批量操作成功。',"?action=$action&page=$page$filterstr");
}

?>