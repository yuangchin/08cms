<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('extract') || cls_message::show('您没有当前项目的管理权限。');


if(empty($deal)){
	if(!submitcheck('bextedit')){
	$css = array('L' => 'txtL', 'R' => 'txtR', 'C' => 'txtC');
	$membercname = '会员名称';
	$checkstate = '审核状态';
		
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	
	$wheresql = ' 1=1';
	$u_lists = array('mname', 'integral', 'total', 'rate', 'checkdate', 'createdate', 'view');
		
	$mname && $wheresql .= " AND mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($mname,'%_'))."%'";
	isset($checked) || $checked = '-1';
	$checked != '-1' && $wheresql .= ' AND checkdate' . ($checked ? '!' : '') . '=0';
	$datefield = $dmode ? 'checkdate' : 'createdate';
	if($date1 && preg_match("/\s*(\d{4})-(\d{1,2})-(\d{1,2})(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?\s*$/", $date1, $match)){
		$date = mktime(empty($match[4]) ? 0 : $match[4], empty($match[5]) ? 0 : $match[5], empty($match[6]) ? 0 : $match[6], $match[2], $match[3], $match[1]);
		$date && $date > 0 && $wheresql .= " AND $datefield>='$date'";
	}
	if($date2 && preg_match("/\s*(\d{4})-(\d{1,2})-(\d{1,2})(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?\s*$/", $date2, $match)){
		$date = mktime(empty($match[4]) ? 24 : $match[4], empty($match[5]) ? 59 : $match[5], empty($match[6]) ? 59 : $match[6], $match[2], $match[3], $match[1]);
		$date && $date > 0 && $wheresql .= " AND $datefield<='$date'";
	}
	echo form_str('extract_list',"?$_SERVER[QUERY_STRING]");
	//搜索区块
	tabheader_e();
	echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	//关键词固定显示
	echo $membercname."&nbsp; <input class=\"text\" name=\"mname\" type=\"text\" value=\"$mname\" size=\"8\" style=\"vertical-align: middle;\">&nbsp; ";
	$checkarr = array('-1' => '不限', '0' => '未审', '1' => '已审');
	echo "<select style=\"vertical-align: middle;\" name=\"checked\">" . makeoption($checkarr, $checked) . "</select>&nbsp; ";
	$dmodearr = array('0' => '申请时间', '1' => '审核时间');
	echo "<select style=\"vertical-align: middle;\" name=\"dmode\">" . makeoption($dmodearr, $dmode) . "</select>&nbsp; " .
		"<input id=\"extract_date1\" name=\"date1\" type=\"text\" value=\"$date1\" class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\" style=\"vertical-align: middle;width:120px\">&nbsp; -&nbsp; " .
		"<input id=\"extract_date2\" name=\"date2\" type=\"text\" value=\"$date2\" class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\" style=\"vertical-align: middle;width:120px\">&nbsp; " .
		"<input class=\"btn\" type=\"submit\" name=\"bfilter\" id=\"bfilter\" value=\"筛选\">&nbsp;" .
		"</td></tr>";
	tabfooter();

	$pagetmp = $page;
	do{
		$query = $db->query("SELECT * FROM {$tblprefix}extracts WHERE $wheresql ORDER BY $datefield DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	}while(!$db->num_rows($query) && $pagetmp);
	$count = $db->result_one("SELECT count(*) FROM {$tblprefix}extracts WHERE $wheresql");
	$view = '信息';
	tabheader('提现记录列表', '', '', count($u_lists) + 1);
	$cy_arr = array();
	$cy_arr[] = '<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form, \'selectid\', \'chkall\')">';
	in_array('mname',$u_lists) && $cy_arr[] = array($membercname, $css['L']);
	in_array('integral',$u_lists) && $cy_arr[] = '提现数量';
		in_array('total',$u_lists) && $cy_arr[] = '提现获得';
		in_array('rate',$u_lists) && $cy_arr[] = '提现率(%)';
		in_array('checkdate',$u_lists) && $cy_arr[] = '审核时间';
		in_array('createdate',$u_lists) && $cy_arr[] = '申请时间';
#			in_array('delstate',$u_lists) && $cy_arr[] = '删除状态';
		in_array('view',$u_lists) && $cy_arr[] = $view;
		trcategory($cy_arr);
		while($item = $db->fetch_array($query)){
#				$checked = $item['checked'] ? 'Y' : '-';
#				$delete = $item['delstate'] ? 'Y' : '-';
			$checkdate = $item['checkdate'] ? date('Y-m-d', $item['checkdate']) : '-';
			$createdate = date('Y-m-d', $item['createdate']);
			$itemstr = '<tr class="txt">';
			$itemstr .= "<td class=\"$css[C] w40\" ><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$item[eid]]\" value=\"$item[eid]\"></td>\n";
			in_array('mname',$u_lists) && $itemstr .= "<td class=\"$css[L]\">$item[mname]</td>\n";
			in_array('integral',$u_lists) && $itemstr .= "<td class=\"$css[C]\">$item[integral]</td>\n";
			in_array('total',$u_lists) && $itemstr .= "<td class=\"$css[C]\">$item[total]</td>\n";
			in_array('rate',$u_lists) && $itemstr .= "<td class=\"$css[C]\">$item[rate]%</td>\n";
			in_array('checkdate',$u_lists) && $itemstr .= "<td class=\"$css[C]\">$checkdate</td>\n";
			in_array('createdate',$u_lists) && $itemstr .= "<td class=\"$css[C]\">$createdate</td>\n";
#				in_array('delstate',$u_lists) && $itemstr .= "<td class=\"$css[C]\">$delete</td>\n";
			in_array('view',$u_lists) && $itemstr .= "<td class=\"$css[C]\"><a href=\"?$_SERVER[QUERY_STRING]&deal=check&eid=$item[eid]\" onclick=\"return floatwin('open_extractview',this)\">$view</a></td>\n";
			$itemstr .= "</tr>\n";
			echo $itemstr;
		}
		tabfooter();
		echo multi($count, $atpp, $page, preg_replace("/[?&]page=\d+$|([?&])page=\d+&/", '$1', "?$_SERVER[QUERY_STRING]"));
		
		tabheader('操作项目');
		trbasic('选择操作项目', '', '<input class="checkbox" type="checkbox" name="extdeal[delete]" id="extdeal_delete" value="1 onclick=\"deltip()\""><label for="extdeal_delete" >' . '删除' . '</label>&nbsp;<input class="checkbox" type="checkbox" name="extdeal[check]" id="extdeal_check" value="1"><label for="extdeal_check" >' . '审核'. '</label>&nbsp;', '');
		tabfooter('bextedit');

}else{

		$empty_item = 'selectoperateitem';
	empty($extdeal) && cls_message::show($empty_item , axaction(1, M_REFERER));
	empty($selectid) && cls_message::show('请选择提现记录', axaction(1, M_REFERER));
	$wheresql	= '';
	$user = new cls_userinfo;
	foreach($selectid as $eid){
		if(!empty($extdeal['delete'])){
			if($row = $db->fetch_one("SELECT mid,integral,checkdate FROM {$tblprefix}extracts WHERE eid='$eid'$wheresql LIMIT 0,1")){
/*						if($isadmin){
							$sql = $row['delstate'] == 2 || !$row['checked'] ? "DELETE FROM {$tblprefix}extracts" :($row['delstate'] == 0 ? "UPDATE {$tblprefix}extracts SET delstate=1" : '');
						}else{
							$sql = $row['delstate'] == 1 || !$row['checked'] ? "DELETE FROM {$tblprefix}extracts" :($row['delstate'] == 0 ? "UPDATE {$tblprefix}extracts SET delstate=2" : '');
						}
						$sql && $db->query("$sql WHERE eid='$eid'");*/
				if($row['checkdate'] == 0){
					$user->activeuser($row['mid']);
					$user->updatecrids(array( '0' => $row['integral']), 1);
				}
				$db->query("DELETE FROM {$tblprefix}extracts WHERE eid='$eid'");
			}
			continue;
		}
		$db->query("UPDATE {$tblprefix}extracts SET checkdate=$timestamp WHERE checkdate=0 AND eid='$eid'");
	}
	!empty($extdeal['delete']) && adminlog('提现管理','提现操作');
	cls_message::show('批量操作完成', M_REFERER);
}
}elseif($deal=='check'){
	if(submitcheck('bconfirm')){
		$db->query("UPDATE {$tblprefix}extracts SET checkdate=$timestamp WHERE eid='$eid' AND checkdate=0");
		cls_message::show($db->affected_rows() ? '提现申请审核完成' : '无效的提现记录', axaction(6, $forward ? $forward : M_REFERER));
	}
	$extract || $extract = $db->fetch_one("SELECT * FROM {$tblprefix}extracts WHERE eid='$eid' LIMIT 0,1");
	$extract || cls_message::show('无效的提取记录');
	tabheader('提现记录审核');
	trbasic('申请时间', '', date('Y-m-d H:i:s', $extract['createdate']), '');
	trbasic('审核时间', '', $extract['checkdate'] ? date('Y-m-d H:i:s', $extract['checkdate']) : '-', '');
	trbasic('提现数量', '', $extract['integral'] . '元', '');
	trbasic('提现率(%)', '', $extract['rate'] . '%', '');
	trbasic('提现获得', '', $extract['total'] . '元', '');
	trbasic('备注', '', str_replace("\n", '<br />', htmlspecialchars($extract['remark'])), '');
	tabfooter();
	if(!$extract['checkdate'])echo '<form action="?' . $_SERVER['QUERY_STRING'] . '" method="post"><input class="bigButton" type="submit" name="bconfirm" value="审核"></form>';
}
?>