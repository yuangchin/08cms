<?php
!defined('M_COM') && exit('No Permission');
$cuid = 7;
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
$modearr = array('new' => '新房动态','old' => '二手房源','rent' => '出租房源',);
$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$ccid1 = empty($ccid1) ? 0 : max(0,intval($ccid1));
$keyword = empty($keyword) ? '' : $keyword;

$selectsql = "SELECT cu.*,cu.createdate AS ucreatedate,a.createdate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject,a.ccid1";
$wheresql = " WHERE cu.mid='$memberid'";
$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}".atbl(4)." a ON a.aid=cu.aid";

if($ccid1 && $cnsql = cnsql(1,sonbycoid($ccid1,1),'a.')) $wheresql .= " AND $cnsql";
$keyword && $wheresql .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";

$filterstr = '';
foreach(array('keyword',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
if(!submitcheck('bsubmit')){
	echo form_str('newform',"?action=$action&page=$page");
	tabheader_e();
	echo "<tr><td class=\"item2\">";
	echo "<div class='filter'><input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"20\" placeholder=\"请输入标题\" style=\"vertical-align: middle;\" title=\"搜索楼盘\">&nbsp; ";
	echo '<span>'.cn_select("ccid1",array('value' => $ccid1,'coid' => 1,'notip' => 1,'addstr' => '搜索地域','vmode' => 0,'framein' => 1,)).'</span>&nbsp; ';
	echo strbutton('bfilter','筛选');
	echo "</div></td></tr>";
	tabfooter();
	tabheader('我关注的楼盘','','',9);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('楼盘名称','left'),);
	$cy_arr[] = '地域';
	$cy_arr[] = array('订阅楼盘动态','left');
	$cy_arr[] = '加入关注';
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
		$adddatestr = date('Y-m-d',$r['ucreatedate']);
		$coclasses = cls_cache::Read('coclasses',1);
		$ccid1str = @$coclasses[$r['ccid1']]['title'];
		$gzstr = '';foreach($modearr as $k => $v) $r[$k] && $gzstr .= $v.' &nbsp;';

		$itemstr .= "<tr><td class=\"item\" width=\"40\">$selectstr</td><td class=\"item2\">$subjectstr</td>\n";
		$itemstr .= "<td class=\"item\">$ccid1str</td>\n";
		$itemstr .= "<td class=\"item2\">$gzstr</td>\n";
		$itemstr .= "<td class=\"item\" width=\"100\">$adddatestr</td>\n";
		$itemstr .= "</tr>\n";
	}
	echo $itemstr;
	tabfooter();
	echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action$filterstr");
	
	tabheader('批量操作');
	$s_arr = array();
	$s_arr['delete'] = '删除';
	foreach($modearr as $k => $v){
		$s_arr[$k] = "订阅$v";
		$s_arr["un$k"] = "退订$v";
	}
	if($s_arr){
		$str = '';
		$i = 1;
		foreach($s_arr as $k => $v){
			$str .= "<label><input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"".($k=='delete'?' onclick="deltip()"':'').">$v</label> &nbsp;";
			if(!($i % 5)) $str .= '<br>';
			$i ++;
		}
		trbasic('选择操作项目','',$str,'');
	}
	tabfooter('bsubmit');
	m_guide('订阅指定楼盘的相关内容后，您可以在Email中收到指定内容的最新变化信息。');
	
}else{
	
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
	if(empty($selectid)) cls_message::show('请选择关注的楼盘。',axaction(1,M_REFERER));
	foreach($selectid as $k){
		$k = empty($k) ? 0 : max(0, intval($k));
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
			continue;
		}
		$sqlstr = '';
		foreach($modearr as $x => $y){
			if(!empty($arcdeal[$x])){
				$sqlstr .= ",$x=1";
			}elseif(!empty($arcdeal["un$x"])){
				$sqlstr .= ",$x=0";
			}
		}
		if($sqlstr = substr($sqlstr,1)) $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr WHERE cid='$k'");
	}
	cls_message::show('关注楼盘批量操作成功。',"?action=$action&page=$page$filterstr");
}

?>