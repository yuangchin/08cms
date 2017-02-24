<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('normal') || cls_message::show('您没有当前项目的管理权限。');

$mchannels = cls_cache::Read('mchannels');
$catalogs = cls_cache::Read('catalogs');
$grouptypes = cls_cache::Read('grouptypes');

$mchid = empty($mchid) ? 0 : max(0,intval($mchid));
$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$viewdetail = empty($viewdetail) ? 0 : 1;
$checked = empty($checked)?'0':($checked == 1?'1':'-1');
$grouptype34 = isset($grouptype34) ? $grouptype34 : '106';
$keyword = empty($keyword) ? '' : $keyword;
$indays = empty($indays) ? 0 : max(0,intval($indays));
$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
$wheresql = '';
$fromsql = "FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid";

$caid = empty($caid) ? 0 : $caid;

//类型范围
if(!empty($mchid)){
	if(!array_intersect(array(-1,$mchid),$a_mchids)) $no_list = 1;
	else $wheresql .= " AND m.mchid='$mchid'";
}elseif(empty($a_mchids)){
	$no_list = 1;
}elseif(!in_array(-1,$a_mchids) && $a_mchids) $wheresql .= ($wheresql ? ' AND ' : '')."m.mchid ".multi_str($a_mchids);
if($grouptype34 != -1) $wheresql .= " AND m.grouptype34='$grouptype34'";
//搜索关键词处理
$mode_keyword = empty($mode_keyword) ? 'ming' : $mode_keyword;
if($keyword){
	if(in_array($mode_keyword,array('ming','mname','mid'))) {
		$mode = $mode_keyword == 'ming' ? "s.$mode_keyword" : "m.$mode_keyword";
		$keyword && $wheresql .= " AND ($mode ".sqlkw($keyword).")";
	
	}
}
$indays && $wheresql .= " AND m.regdate>'".($timestamp - 86400 * $indays)."'";
$outdays && $wheresql .= " AND m.regdate<'".($timestamp - 86400 * $outdays)."'";

$filterstr = '';
foreach(array('mchid','keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
foreach(array('checked',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
foreach(array('grouptype34',) as $k) $$k != -1 && $grouptype34 .= "&$k=".$$k;

$grouptypes = cls_cache::Read('grouptypes');
foreach($grouptypes as $k => $v){
	${"ugid$k"} = empty(${"ugid$k"}) ? 0 : ${"ugid$k"}; 
	if(${"ugid$k"}){
		$filterstr .= "&ugid$k=".${"ugid$k"};
		$wheresql .= " AND m.grouptype$k='".${"ugid$k"}."'";
	}
}
$wheresql .= (empty($caid) || $caid==516) ? '' : " AND s.quaere like '%$caid%'";
//$wheresql .= (empty($mchid) || $mchid==516) ? '' : " AND m.mchid='$mchid'";
$wheresql = empty($no_list) ? ($wheresql ? 'WHERE '.substr($wheresql,5) : '') : 'WHERE 0';
//echo $wheresql;

if(!submitcheck('bsubmit')){
	
	echo form_str($actionid.'memberedit',"?entry=$entry$extend_str&page=$page&mchid=$mchid");
	tabheader_e();
	trhidden('mchid',$mchid);
	echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	echo "<select style=\"vertical-align: middle;\" name=\"mode_keyword\">".makeoption(array('ming' => '专家名称','mname' => '专家账号','mid' => '专家ID'),$mode_keyword)."</select>&nbsp; ";
	echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索用户名\">&nbsp; ";
	echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\" title=\"注册\">天前&nbsp; ";
	echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\" title=\"注册\">天内&nbsp; ";

	echo strbutton('bfilter','筛选');
	echo "</td></tr>";
	tabfooter();
	//列表区	
	tabheader("会员列表",'','',10);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('会员名称','txtL'),);
	$cy_arr[] = '专家名称';
	$cy_arr[] = '擅长领域';
	$cy_arr[] = '推荐专家';
	$cy_arr[] = '会员类型';
	$cy_arr[] = '注册IP';
	$cy_arr[] = '注册日期';
	$cy_arr[] = '更多';
	$cy_arr[] = '会员组';
	$cy_arr[] = '详情';
	$cy_arr[] = '代管';
	trcategory($cy_arr);


	$pagetmp = $page; //echo "SELECT m.*,s.* $fromsql $wheresql";
	do{
		$query = $db->query("SELECT m.*,s.* $fromsql $wheresql ORDER BY m.mid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);

	$itemstr = '';
	while($r = $db->fetch_array($query)){ // info.php?fid=107&mid=767
		$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[mid]]\" value=\"$r[mid]\">";
		$mnamestr ="<a href='{$cms_abs}info.php?fid=107&mid=$r[mid]' target=\"_blank\">". $r['mname'].($r['isfounder'] ? '-创始人': '').'</a>';
		$mingstr = $r['ming'] ? ($r['grouptype34'] ? "$r[ming]" : "<span class='tips1' title='未审核'>$r[ming]</span>") : '-';
		
		$mchannelstr = @$mchannels[$r['mchid']]['cname'];
		$checkstr = $r['checked'] == 1 ? 'Y' : '-';
		foreach($grouptypes as $k => $v){
			${'ugid'.$k.'str'} = '-';
			if($r['grouptype'.$k]){
				$usergroups = cls_cache::Read('usergroups',$k);
				${'ugid'.$k.'str'} = @$usergroups[$r['grouptype'.$k]]['cname'];
			}
		}
		if($r['quaere']){
				$sc_quaere = '';$gap = '';
				foreach(explode(',',$r['quaere']) as $x){
					if($x){
						$sc_quaere .= $gap.cls_catalog::cnstitle($x,0,$catalogs);
						$gap = ',';
					}
				}			
		}else{ $sc_quaere = '-'; }
		$regipstr = $r['regip'];
		$regdatestr = $r['regdate'] ? date('Y-m-d',$r['regdate']) : '-';
		$lastvisitstr = $r['lastvisit'] ? date('Y-m-d',$r['lastvisit']) : '-';
		$viewstr = "<a id=\"{$actionid}_info_$r[mid]\" href=\"?entry=extend&extend=memberinfo&mid=$r[mid]\" onclick=\"return showInfo(this.id,this.href)\">查看</a>";
		$editstr = "<a href=\"?entry=extend&extend=memberexpert&mid=$r[mid]\" onclick=\"return floatwin('open_memberedit',this)\">详情</a>";
		$groupstr = "<a href=\"?entry=extend&extend=membergroup&mid=$r[mid]\" onclick=\"return floatwin('open_memberedit',this)\">会员组</a>";

		$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td><td class=\"txtL\">$mnamestr</td>\n";
		$itemstr .= "<td class=\"txtC\">$mingstr</td>\n";
		$itemstr .= "<td class=\"txtC\">$sc_quaere</td>\n";

		$itemstr .= "<td class=\"txtC\">$ugid35str</td>\n";
		$itemstr .= "<td class=\"txtC\">$mchannelstr</td>\n";		
			

		$itemstr .= "<td class=\"txtC\">$regipstr</td>\n";
		
		$itemstr .= "<td class=\"txtC\">$regdatestr</td>\n";

		$itemstr .= "<td class=\"txtC\">$viewstr</td>\n";
		$itemstr .= "<td class=\"txtC\">$groupstr</td>\n";
		$itemstr .= "<td class=\"txtC\">$editstr</td>\n";
		$itemstr .= "<td class=\"txtC\"><a href=\"adminm.php?from_mid=$r[mid]\" target=\"_blank\">代管</a></td>\n";
		$itemstr .= "</tr>\n";
	}
	$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$multi = multi($counts, $atpp, $page, "?entry=$entry$extend_str$filterstr");
	echo $itemstr;
	tabfooter();
	echo $multi;
	
	//操作区
	tabheader('操作项目');	
	foreach($grouptypes as $k => $v){
	if(in_array($k,array(34,35))){
		if(($v['mode'] < 2) && $k != 2){
			$ugidsarr = $k==34?array('0' => '解除会员组'):array('0' => '解除会员组') + ugidsarr($k,'',1);
			trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[gtid$k]\" value=\"1\">设置$v[cname]",'arcugid'.$k,makeoption($ugidsarr),'select');
		}
	}}
	tabfooter('bsubmit');

}else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=$entry$extend_str&page=$page$filterstr");
	if(empty($selectid)) cls_message::show('请选择会员',"?entry=$entry$extend_str&page=$page$filterstr");

	$actuser = new cls_userinfo;	
	$ucdels = array();
	$clumn_arr = array('dantu','danwei','ming','quaere');
	
	foreach($selectid as $id){
		$actuser->activeuser($id);		
		foreach($grouptypes as $k => $v){
			if(($v['mode'] < 2) && !empty($arcdeal['gtid'.$k]) && $k != 2){
				$actuser->handgroup($k,${"arcugid$k"},-1);
				if($k == 34){
					foreach($clumn_arr as $h){
						$actuser->updatefield($h,'','members_sub');
					}
					$actuser->updatefield("grouptype34",0,'members');
				}
			}
		}
		$actuser->updatedb();
		$actuser->init();
	}
	unset($actuser);
	
	if($enable_uc && $ucdels){
		$uc_action = 'ucdels';include(M_ROOT.'./include/ucenter/uc.inc.php');
	}
	adminlog('会员管理','会员列表管理操作');
	cls_message::show('会员操作完成',"?entry=$entry$extend_str&page=$page$filterstr");
}

?>
