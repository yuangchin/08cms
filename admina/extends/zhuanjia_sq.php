<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('commu') || cls_message::show('no_apermission');
$cuid = 42; 
array_intersect($a_cuids,array(-1,$cuid)) || cls_message::show('没有指定交互内容的管理权限'); 
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');

$catalogs = cls_cache::Read('catalogs');

$page = !empty($page) ? max(1, intval($page)) : 1;
$mchid = empty($mchid) ? 0 : max(0,intval($mchid));
submitcheck('bfilter') && $page = 1;
$caid = empty($caid) ? 0 : max(0,intval($caid));

$keyword = empty($keyword) ? '' : $keyword;
$indays = empty($indays) ? 0 : max(0,intval($indays));
$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
$checked = empty($checked)?'0':($checked == 1?'1':'-1');


$selectsql = "SELECT cu.*,s.ming,s.dantu,s.quaere,s.danwei ";
$wheresql = '';
$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}members_sub s ON s.mid=cu.mid";
$fromsql .= " INNER JOIN {$tblprefix}members m ON m.mid=s.mid";

$wheresql .= " AND cu.checked=''";
$keyword && $wheresql .= " AND s.ming LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";
$indays && $wheresql .= " AND cu.createdate>'".($timestamp - 86400 * $indays)."'";
$outdays && $wheresql .= " AND cu.createdate<'".($timestamp - 86400 * $outdays)."'";

$wheresql .= (empty($caid) || $caid==516) ? '' : " AND s.quaere like '%$caid%'";
$wheresql .= (empty($mchid) || $mchid==516) ? '' : " AND m.mchid='$mchid'";

$wheresql = empty($no_list) ? ($wheresql ? 'WHERE '.substr($wheresql,5) : '') : 'WHERE 0';

$filterstr = '';
foreach(array('mchid','keyword','indays','outdays') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
foreach(array('checked',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
if(!submitcheck('bsubmit')){
	echo form_str($actionid.'arcsedit',"?entry=extend$extend_str&page=$page&mchid=$mchid");
	tabheader_e();
	trhidden('caid',$caid);
	echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索活动\">&nbsp; ";
	echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"2\" style=\"vertical-align: middle;\">天前&nbsp; ";
	echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"2\" style=\"vertical-align: middle;\">天内&nbsp; ";
	echo strbutton('bfilter','筛选');
	tabfooter();
	tabheader('专家团申请列表','','',9);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('专家名称','txtL'),);
	$cy_arr[] = '专家头像';
	$cy_arr[] = '擅长领域';
	$cy_arr[] = '所属单位';
	$cy_arr[] = '审核专家';
	$cy_arr[] = '添加时间';
	trcategory($cy_arr);
	
	$pagetmp = $page;	
	do{
		$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);

	$itemstr = '';
	while($r = $db->fetch_array($query)){		
		$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";		
		$catalogstr = @$catalogs[$r['caid']]['title'];
		$ming = $r['ming'];
		$danwei = $r['danwei'];
		$pic = $r['dantu'] ? "<a href='$r[dantu]' target='_blank'>有</a>" : '-';		
		if($r['quaere']){
			$sc_quaere = '';$gap = '';
			foreach(explode(',',$r['quaere']) as $x){
				if($x){
					$sc_quaere .= $gap.cls_catalog::cnstitle($x,0,$catalogs);
					$gap = ',';
				}
			}			
		}else{ $sc_quaere = '-'; }
		$checkstr = $r['checked'] ? 'Y' : '-';
		$adddatestr = date('Y-m-d',$r['createdate']);
		$editstr = "<a href=\"?entry=extend$extend_str&cid=$r[cid]\" onclick=\"return floatwin('open_commentsedit',this)\">详情</a>";
		
		$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td><td class=\"txtL\">$ming</td>\n";
		$itemstr .= "<td class=\"txtC\">$pic</td>\n";
		$itemstr .= "<td class=\"txtC\">$sc_quaere</td>\n";
		$itemstr .= "<td class=\"txtC\">$danwei</td>\n";
		$itemstr .= "<td class=\"txtC w80\">$checkstr</td>\n";
		$itemstr .= "<td class=\"txtC w100\">$adddatestr</td>\n";
		$itemstr .= "</tr>\n";
	}
	echo $itemstr;
	tabfooter();
	echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$atpp,$page, "?entry=$entry$extend_str$filterstr");
	
	tabheader('批量操作');
	$s_arr = array();
	$s_arr['check'] = '审核专家';		
	$s_arr['delete'] = '删除申请';
	if($s_arr){
		$str = '';
		$i = 1;
		foreach($s_arr as $k => $v){
			$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\" ".($k=='delete'?"onclick=\"deltip()\"":'').">$v &nbsp;";
			if(!($i % 5)) $str .= '<br>';
			$i ++;
		}
		trbasic('选择操作项目','',$str,'');
	}
	tabfooter('bsubmit');
}else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
	if(empty($selectid)) cls_message::show('请选择专家申请记录。',axaction(1,M_REFERER));
	foreach($selectid as $k){
		if(!empty($arcdeal['check'])){
			$mid = $db->result_one("SELECT mid FROM {$tblprefix}$commu[tbl] WHERE cid='$k'");
			$db->query("UPDATE {$tblprefix}members SET grouptype34='106' WHERE mid='$mid'");			
		}
		if(!empty($arcdeal['delete']) || !empty($arcdeal['check'])){	
			$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'");
		}
	}	
	adminlog('专家申请列表管理');
	cls_message::show('专家申请批量操作成功。',"?entry=$entry$extend_str&page=$page$filterstr");	
}

?>