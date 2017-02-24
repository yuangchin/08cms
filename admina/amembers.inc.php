<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
foreach(array('mchannels','catalogs','cotypes','mtconfigs','channels','grouptypes','currencys','rprojects','amconfigs',) as $k) $$k = cls_cache::Read($k);
if($action == 'edit'){
	backnav('backarea','amember');
	if($re = $curuser->NoBackFunc('amember')) cls_message::show($re);
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$keyword = empty($keyword) ? '' : $keyword;
	$ugid2 = empty($ugid2) ?  0 : max(0,intval($ugid2));
	$wheresql = 'WHERE m.grouptype2'.($ugid2 ? "='$ugid2'" : '<>0');
	$fromsql = "FROM {$tblprefix}members m";

	$keyword && $wheresql .= " AND m.mname ".sqlkw($keyword);
	$ugid2 && $wheresql .= " AND m.grouptype2='$ugid2'";

	$filterstr = '';
	foreach(array('keyword','ugid2') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	$wheresql && $wheresql = 'WHERE '.substr($wheresql,5);

	if(!submitcheck('bsubmit')){
		echo form_str($actionid.'memberedit',"?entry=$entry&action=$action&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索用户名\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"ugid2\">".makeoption(array('0' => '不限管理组') + ugidsarr(2),$ugid2)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();
		//列表区
		tabheader("管理员列表 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=add\" onclick=\"return floatwin('open_amembers',this)\">添加管理员</a>",'','',10);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('管理员帐号','txtL'),);
		$cy_arr[] = '管理组';
		$cy_arr[] = '到期日期';
		$cy_arr[] = array('附加管理角色','txtL');
		$cy_arr[] = '工作统计';
		$cy_arr[] = '审核';
		$cy_arr[] = '最近访问';
		trcategory($cy_arr);


		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * $fromsql $wheresql ORDER BY mid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);

		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[mid]]\" value=\"$r[mid]\">";
			$mnamestr = $r['mname'];
			$checkstr = $r['checked'] ? 'Y' : '-';
			$arr = cls_cache::Read('usergroups',2);
			$ugid2str = @$arr[$r['grouptype2']]['cname'];
			$enddatestr = !$r['grouptype2date'] ? '-': date('Y-m-d',$r['grouptype2date']);
			$amcids = $r['amcids'] ? explode(',',$r['amcids']) : array();
			$amcidstr = '';foreach($amcids as $k) !empty($amconfigs[$k]) && $amcidstr .= $amconfigs[$k]['cname'].' ';
			$lastvisitstr = $r['lastvisit'] ? date('Y-m-d',$r['lastvisit']) : '-';

			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td><td class=\"txtL 80\">$mnamestr</td>\n";
			$itemstr .= "<td class=\"txtC\">$ugid2str</td>\n";
			$itemstr .= "<td class=\"txtC\">$enddatestr</td>\n";
			$itemstr .= "<td class=\"txtL\">$amcidstr</td>\n";
			$itemstr .= "<td class=\"txtC\"><a id=\"{$actionid}_info_$r[mid]\" href=\"?entry=workstat&mid=$r[mid]&mname=$r[mname]\" onclick=\"return floatwin('open_$action',this)\">查看</a></td>\n";
			$itemstr .= "<td class=\"txtC w35\">$checkstr</td>\n";
			$itemstr .= "<td class=\"txtC w80\">$lastvisitstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
		$multi = multi($counts, $atpp, $page, "?entry=$entry&action=$action$filterstr");
		echo $itemstr;
		tabfooter();
		echo $multi;
		//操作区
		tabheader('操作项目');
		$s_arr = array();
		$s_arr['check'] = '审核';
		$s_arr['uncheck'] = '解审';
		if($s_arr){
			$soperatestr = '';
			foreach($s_arr as $k => $v) $soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\">$v &nbsp;";
			trbasic('选择操作项目','',$soperatestr,'');
		}
		$str = "<select style=\"vertical-align: middle;\" name=\"arcugid2\">".makeoption(array('0' => '解除管理组') + ugidsarr(2))."</select>&nbsp; <input type=\"text\" size=\"15\" id=\"arcugid2date\" name=\"arcugid2date\" value=\"\" class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\" />";
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[ugid2]\" value=\"1\">设置管理组",'',$str,'');
		$arr = array();foreach($amconfigs as $k => $v) $arr[$k] = $v['cname'];
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[amcids]\" value=\"1\">附加管理角色",'',makecheckbox("arcamcids[]",$arr,array(),5),'',array('guide' => '与所在管理组设置的管理角色同时生效。'));
		tabfooter('bsubmit');
		a_guide('amembers');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=$entry&action=$action&page=$page$filterstr");
		if(empty($selectid)) cls_message::show('请选择会员',"?entry=$entry&action=$action&page=$page$filterstr");
		$actuser = new cls_userinfo;
		foreach($selectid as $id){
			$actuser->activeuser($id);
			if(!empty($arcdeal['check'])){
				$actuser->check(1);
			}elseif(!empty($arcdeal['uncheck'])){
				$actuser->check(0);
			}
			if(!empty($arcdeal['ugid2'])){
				$actuser->handgroup(2,$arcugid2,!$arcugid2 || !cls_string::isDate($arcugid2date) ? 0 : strtotime($arcugid2date));
			}
			if(!empty($arcdeal['amcids'])){
				$actuser->updatefield('amcids',empty($arcamcids) ? '' : implode(',',$arcamcids));
			}
			$actuser->updatedb();
			$actuser->init();
		}
		unset($actuser);
		adminlog('管理员管理','会员列表管理操作');
		cls_message::show('管理员操作完成。',"?entry=$entry&action=$action&page=$page$filterstr");
	}
}elseif($action == 'add'){
	if($re = $curuser->NoBackFunc('amember')) cls_message::show($re);
	if(!submitcheck('bsubmit')){
		tabheader('添加管理员', 'addadmin', "?entry=$entry&action=$action",2,1,1);
		trbasic('用户名称*', 'mname','','text',array('validate'=>' rule="text" must="1" min="3" max="15"','guide' => '需要是已成功注册的会员。'));
		$str = "<select style=\"vertical-align: middle;\" name=\"ugid2\" rule=\"must\">".makeoption(ugidsarr(2))."</select>&nbsp; <input type=\"text\" size=\"15\" id=\"ugid2date\" name=\"ugid2date\" value=\"\" class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\" />";
		trbasic('设置管理组*', '',$str,'',array('guide' => '留空为永久有效；或设置一个>当天日期的时间；时间≤当天日期时，相当于解除管理组系，导致添加管理员失败。'));
		tabfooter('bsubmit');
	}else{
		$mname = trim(strip_tags($mname));
		if(empty($mname) || empty($ugid2)) cls_message::show('请输入会员帐号及管理组',M_REFERER);
		$actuser = new cls_userinfo;
		$actuser->activeuserbyname($mname);
		if(!$actuser->info['mid'] || $actuser->info['isfounder']) cls_message::show('请指定正确的会员。',M_REFERER);
		$actuser->handgroup(2,$ugid2,!$ugid2 || !cls_string::isDate($ugid2date) ? 0 : strtotime($ugid2date));
		$actuser->updatedb();
		adminlog('添加后台管理员');
		cls_message::show('管理员添加成功',axaction(6,M_REFERER));
	}
}

?>
