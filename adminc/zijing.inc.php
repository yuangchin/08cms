<?php
!defined('M_COM') && exit('No Permission');

$arid = 4;$schid = 2;$tchid = 3;
if(!($abrel = cls_cache::Read('abrel',$arid)) || empty($abrel['available'])) cls_message::show('不存在或关闭的合辑项目。');
$cuid = 10;
if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('公司资金管理功能已关闭。');
$curuser->detail_data();

if(empty($deal)){//公司查看分配记录，经纪人查看被分配记录。
	backnav('company','cash');
	if($curuser->info['mchid'] == $tchid){
		$page = !empty($page) ? max(1, intval($page)) : 1;
		submitcheck('bfilter') && $page = 1;
		$mode = isset($mode) ? $mode : -1;
		$indays = empty($indays) ? 0 : max(0,intval($indays));
		$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
		$keyword = empty($keyword) ? '' : $keyword;
		$wheresql = "WHERE mid='$memberid'";
		$fromsql = "FROM {$tblprefix}$commu[tbl]";
		
		if($mode != -1) $wheresql .= " AND zj".($mode ? '<' : '>')."0";
		$indays && $wheresql .= " AND createdate>'".($timestamp - 86400 * $indays)."'";
		$outdays && $wheresql .= " AND createdate<'".($timestamp - 86400 * $outdays)."'";
		$keyword && $wheresql .= " AND tomname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";
		$filterstr = '';
		foreach(array('keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
		foreach(array('mode',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	
		echo form_str($action.'newform',"?action=$action&page=$page");
		tabheader_e();
		echo "<tr><td class=\"item2\">";
		echo "<div clas='filter'><input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索经纪人\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"mode\">".makeoption(array('-1' => '不限方式','0' => '分配','1' => '提取',),$mode)."</select>&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</div></td></tr>";
		tabfooter();
		//列表区
		$sum = $db->result_one("SELECT SUM(zj) $fromsql $wheresql");
		$sum = $sum ? $sum : 0;
		tabheader("公司资金日志 &nbsp;(支出小计:$sum)",'','',10);
		
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * $fromsql $wheresql ORDER BY cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		$sn = $pagetmp * $atpp;
		while($r = $db->fetch_array($query)){
			$sn ++;
			$modestr = $r['zj'] < 0 ? '提取' : '分配';
			$createdatestr = date("$dateformat $timeformat", $r['createdate']);
			$valuestr = abs($r['zj']);
			$remarkstr = $r['remark'] ? "<a id=\"{$action}_info_$r[cid]\" href=\"?action=$action&deal=remark&cid=$r[cid]\" onclick=\"return showInfo(this.id,this.href)\">查看</a>" : '-';
			$itemstr .= "<tr><td class=\"item\">$sn</td>\n".
				"<td class=\"item2\">$r[tomname]</td>\n".
				"<td class=\"item\">$modestr</td>\n".
				"<td class=\"item2\">$valuestr</td>\n".
				"<td class=\"item\">$createdatestr</td>\n".
				"<td class=\"item\">$remarkstr</td>\n".
				"</tr>\n";
		}
		trcategory(array('序号',array('经纪人','left'),'方式',array('数量','left'),'操作日期','备注'));
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action$filterstr");
	}else{
		$page = !empty($page) ? max(1, intval($page)) : 1;
		submitcheck('bfilter') && $page = 1;
		$mode = isset($mode) ? $mode : -1;
		$indays = empty($indays) ? 0 : max(0,intval($indays));
		$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
		$wheresql = "WHERE tomid='$memberid'";
		$fromsql = "FROM {$tblprefix}$commu[tbl]";
		
		if($mode != -1) $wheresql .= " AND zj".($mode ? '<' : '>')."0";
		$indays && $wheresql .= " AND createdate>'".($timestamp - 86400 * $indays)."'";
		$outdays && $wheresql .= " AND createdate<'".($timestamp - 86400 * $outdays)."'";
		$filterstr = '';
		foreach(array('indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
		foreach(array('mode',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	
		echo form_str($action.'newform',"?action=$action&page=$page");
		tabheader_e();
		echo "<tr><td class=\"item2\">";
		echo "<select style=\"vertical-align: middle;\" name=\"mode\">".makeoption(array('-1' => '不限方式','0' => '分配','1' => '提取',),$mode)."</select>&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();
		//列表区
		$sum = $db->result_one("SELECT SUM(zj) $fromsql $wheresql");
		$sum = $sum ? $sum : 0;
		tabheader("公司资金日志 &nbsp;(收入小计:$sum)",'','',10);
		
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * $fromsql $wheresql ORDER BY cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		$sn = $pagetmp * $atpp;
		while($r = $db->fetch_array($query)){
			$sn ++;
			$modestr = $r['zj'] < 0 ? '提取' : '分配';
			$createdatestr = date("$dateformat $timeformat", $r['createdate']);
			$valuestr = abs($r['zj']);
			$remarkstr = $r['remark'] ? "<a id=\"{$action}_info_$r[cid]\" href=\"?action=$action&deal=remark&cid=$r[cid]\" onclick=\"return showInfo(this.id,this.href)\">查看</a>" : '-';
			$itemstr .= "<tr><td class=\"item\">$sn</td>\n".
				"<td class=\"item2\">$r[mname]</td>\n".
				"<td class=\"item\">$modestr</td>\n".
				"<td class=\"item2\">$valuestr</td>\n".
				"<td class=\"item\">$createdatestr</td>\n".
				"<td class=\"item\">$remarkstr</td>\n".
				"</tr>\n";
		}
		trcategory(array('序号',array('经纪公司','left'),'方式',array('数量','left'),'操作日期','备注'));
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action$filterstr");
	}
}elseif($deal == 'fp'){//分配
	if($curuser->info['mchid'] != $tchid) cls_message::show('请先注册为公司会员。');
	if(!($mid = empty($mid) ? 0 : max(0,intval($mid)))) cls_message::show('您指定了错误的公司经纪人。');
	$curuser->info['currency0'] || cls_message::show("您现金帐户的余额为0。&nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>");
	$au = new cls_userinfo;
	$au->activeuser($mid);
	if(!$au->info['mid'] || $au->info["pid$arid"] != $memberid || !$au->info["incheck$arid"]) cls_message::show('您指定了错误的公司经纪人。');
	if(!submitcheck('bsubmit')){
		tabheader("公司内部资金-{$curuser->info['cmane']}","newform","?action=$action&deal=$deal&mid=$mid",2,0,1);
		trbasic('公司现金帐户','',$curuser->info['currency0']." &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
		trbasic("{$au->info['mname']}现金帐户",'',$au->info['currency0'],'');
		trbasic("给{$au->info['mname']}分配资金",'fmdata[zj]','','text',array('validate' => " rule=\"int\" min=\"1\" max=\"{$curuser->info['currency0']}\"",'w' => 10,));
		trbasic('附加说明','fmdata[remark]','','textarea');
		tabfooter('bsubmit','分配');
	}else{
		if(!($fmdata['zj'] = max(0,intval($fmdata['zj'])))) cls_message::show('请输入分配金额。');
		$fmdata['zj'] = min($fmdata['zj'],$curuser->info['currency0']);
		$fmdata['remark'] = empty($fmdata['remark']) ? '' : trim(strip_tags($fmdata['remark']));
		$curuser->updatecrids(array(0 => -$fmdata['zj']),1,"公司向{$au->info['mname']}分配资金",2);
		$au->updatecrids(array(0 => $fmdata['zj']),1,'公司分配的资金',2);
		$zj = $fmdata['zj'];
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET tomid='$mid',tomname='{$au->info['mname']}',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',zj='$zj',remark='$fmdata[remark]'");
		cls_message::show('资金分配成功。',axaction(6,"?action=agents"));
	}
}elseif($deal == 'cq'){//提取
	if($curuser->info['mchid'] != $tchid) cls_message::show('请先注册为公司会员。');
	if(!($mid = empty($mid) ? 0 : max(0,intval($mid)))) cls_message::show('您指定了错误的公司经纪人。');
	$au = new cls_userinfo;
	$au->activeuser($mid);
	if(!$au->info['mid'] || $au->info["pid$arid"] != $memberid || !$au->info["incheck$arid"]) cls_message::show('您指定了错误的公司经纪人。');
	if(!submitcheck('bsubmit')){
		tabheader("公司内部资金-{$curuser->info['cmane']}","newform","?action=$action&deal=$deal&mid=$mid",2,0,1);
		trbasic('公司现金帐户','',$curuser->info['currency0']." &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
		trbasic("{$au->info['mname']}现金帐户",'',$au->info['currency0'],'');
		trbasic("提取资金到公司",'fmdata[zj]','','text',array('validate' => " rule=\"int\" min=\"1\" max=\"{$au->info['currency0']}\"",'w' => 10,));
		trbasic('附加说明','fmdata[remark]','','textarea');
		tabfooter('bsubmit','分配');
	}else{
		if(!($fmdata['zj'] = max(0,intval($fmdata['zj'])))) cls_message::show('请输入提取金额。');
		$fmdata['zj'] = min($fmdata['zj'],$au->info['currency0']);
		$fmdata['remark'] = empty($fmdata['remark']) ? '' : trim(strip_tags($fmdata['remark']));
		$au->updatecrids(array(0 => -$fmdata['zj']),1,'公司提取的资金',2);
		$curuser->updatecrids(array(0 => $fmdata['zj']),1,"公司从{$au->info['mname']}提取资金",2);
		$zj = -$fmdata['zj'];
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET tomid='$mid',tomname='{$au->info['mname']}',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',zj='$zj',remark='$fmdata[remark]'");
		cls_message::show('资金提取成功。',axaction(6,"?action=agents"));
	}
}elseif($deal == 'remark' && $cid){
	!($remark = $db->result_one("SELECT remark FROM {$tblprefix}$commu[tbl] WHERE cid='$cid' AND ".($curuser->info['mchid'] == $tchid ? 'mid' : 'tomid')."='$memberid'")) && cls_message::show('请指定公司资金支配记录。');
	tabheader('公司资金支配备注');
	trbasic('备注说明','',$remark,'textarea');
	tabfooter();
}
?>
