<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('other')) cls_message::show($re);
$vcatalogs = cls_cache::Read('vcatalogs');
if($action == 'voteadd'){
	backnav('vote','add');
	!vcaidsarr() && cls_message::show('请先添加投票分类！',"?entry=vcatalogs&action=vcatalogsedit");
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.urlencode($forward);
	if(!submitcheck('bvoteadd')){
		tabheader('添加投票','voteadd',"?entry=votes&action=voteadd$forwardstr");
		trbasic('投票标题','votenew[subject]');
		trbasic('投票分类','votenew[caid]',makeoption(vcaidsarr()),'select');
		trbasic('投票说明','votenew[content]','','textarea');
		trbasic('结束时间','votenew[enddate]','','calendar');
		trbasic('是否多项选择','votenew[ismulti]','','radio');
		trbasic('禁止游客投票','votenew[onlyuser]','','radio');
		trbasic('限制重复投票','votenew[norepeat]','','radio');
		trbasic('重复投票时间(分钟)','votenew[timelimit]');
		tabfooter('bvoteadd','添加');
		a_guide('voteadd');
	}else{
		empty($votenew['subject']) && cls_message::show('资料不完全','?entry=votes&action=voteadd$forwardstr');
		$votenew['timelimit'] = max(0,intval($votenew['timelimit']));
		$votenew['enddate'] = empty($votenew['enddate']) ? 0 : strtotime($votenew['enddate']);
		$db->query("INSERT INTO {$tblprefix}votes SET 
					subject='$votenew[subject]',
					caid='$votenew[caid]',
					content='$votenew[content]',
					enddate='$votenew[enddate]',
					ismulti='$votenew[ismulti]',
					onlyuser='$votenew[onlyuser]',
					norepeat='$votenew[norepeat]',
					timelimit='$votenew[timelimit]',
					mid='$memberid',
					mname='".$curuser->info['mname']."',
					createdate='$timestamp'
					");
		cls_message::show('投票添加完成',$forward);
	}
}elseif($action == 'votesedit'){
	backnav('vote','admin');
	!vcaidsarr() && cls_message::show('请先添加投票分类！',"?entry=vcatalogs&action=vcatalogsedit");
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$viewdetail = empty($viewdetail) ? '' : $viewdetail;
	$caid = empty($caid) ? '0' : $caid;
	$checked = isset($checked) ? $checked : '-1';
	$subject = empty($subject) ? '' : $subject;
	$overdated = !isset($overdated) ? '-1' : $overdated;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
	$filterstr = '';
	foreach(array('viewdetail','caid','overdated','checked','subject','indays','outdays') as $k){
		$filterstr .= "&$k=".urlencode($$k);
	}
	if(!submitcheck('barcsedit')){
		$wheresql = '';
		$caid && $wheresql .= ($wheresql ? " AND " : "")."caid = '$caid'";
		if($checked != '-1') $wheresql .= ($wheresql ? " AND " : "")."checked='$checked'";
		if($overdated != '-1') $wheresql .= ($wheresql ? " AND " : "").($overdated ? "enddate>0 AND enddate<$timestamp" : "(endate=0 OR enddate>$timestamp)");
		$subject && $wheresql .= ($wheresql ? " AND " : "")."subject ".sqlkw($subject);
		$indays && $wheresql .= ($wheresql ? " AND " : "")."createdate>'".($timestamp - 86400 * $indays)."'";
		$outdays && $wheresql .= ($wheresql ? " AND " : "")."createdate<'".($timestamp - 86400 * $outdays)."'";
		$wheresql = empty($wheresql) ? '' : "WHERE ".$wheresql;

		$caidsarr = array('0' => '全部分类') + vcaidsarr();
		$checkedarr = array('-1' => '不限','0' => '未审投票','1' => '已审投票');
		$overdatedarr = array('-1' => '不限','0' => '未过期投票','1' => '未过期投票');
		tabheader('筛选投票'.viewcheck(array('name' => 'viewdetail', 'title' => '显示详细', 'value' => $viewdetail, 'body' => $actionid.'tbodyfilter')).'&nbsp; &nbsp; '.strbutton('bfilter','筛选'),'arcsedit',"?entry=votes&action=votesedit&page=$page");
		echo "<tbody id=\"tbodyfilter\" style=\"display: ".(empty($viewdetail) ? 'none' : '')."\">";
		trbasic('所属分类','caid',makeoption($caidsarr,$caid),'select');
		trbasic('是否审核投票','',makeradio('checked',$checkedarr,$checked),'');
		trbasic('是否过期投票','overdated',makeoption($overdatedarr,$overdated),'select');
		trbasic('投票标题','subject',$subject);
		trrange('添加日期',array('outdays',empty($outdays) ? '' : $outdays,'','&nbsp; '.'天前'.'&nbsp; -&nbsp; ',5),array('indays',empty($indays) ? '' : $indays,'','&nbsp; '.'天内',5));
		echo "</tbody>";
		tabfooter();


		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}votes $wheresql ORDER BY vieworder,vid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$itemvote = '';
		while($vote = $db->fetch_array($query)) {
			$vid = $vote['vid'];
			$vote['enddate'] = empty($vote['enddate']) ? '-' : date("$dateformat", $vote['enddate']);
			$vote['ismulti'] = empty($vote['ismulti']) ? '单选' : '多选';
			$vote['subject'] = mhtmlspecialchars($vote['subject']);
			$itemvote .= "<tr class=\"txt\">".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"votesnew[$vid][checked]\" value=\"1\"".(!empty($vote['checked']) ? ' checked' : '')."></td>\n".
				"<td class=\"txtC\">".$vote['vid']."</td>\n".
				"<td class=\"txtL\">$vote[subject]</td>\n".
				"<td class=\"txtC\">".$vcatalogs[$vote['caid']]['title']."</td>\n".
				"<td class=\"txtC w40\">$vote[ismulti]</td>\n".
				"<td class=\"txtC 1080\">$vote[enddate]</td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" name=\"votesnew[$vid][vieworder]\" value=\"$vote[vieworder]\" size=\"3\"></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$vid]\" value=\"$vid\" onclick=\"deltip()\">\n".
				"<td class=\"txtC w40\"><a href=\"?entry=votes&action=votedetail&vid=$vid\">详情</a></td></tr>\n";
		}
		$votecount = $db->result_one("SELECT count(*) FROM {$tblprefix}votes $wheresql");
		$multi = multi($votecount, $atpp, $page, "?entry=votes&action=votesedit$filterstr");

		tabheader('投票管理列表'."&nbsp;&nbsp;&nbsp;&nbsp;[<a href=\"?entry=votes&action=voteadd\">".'添加'."</a>]",'','',8);
		trcategory(array('审核','ID','投票标题','分类','类型','结束日期','排序',"<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"deltip(this,0,checkall,this.form, 'delete', 'chkall')\">删?",'编辑'));
		echo $itemvote;
		tabfooter();
		echo $multi;
		echo "<input class=\"button\" type=\"submit\" name=\"barcsedit\" value=\"提交\">".
			"</form>\n";
	}else{
		if(!empty($delete)){
			foreach($delete as $vid){
				$db->query("DELETE FROM {$tblprefix}voptions WHERE vid='$vid'");
				$db->query("DELETE FROM {$tblprefix}votes WHERE vid='$vid'");
				unset($votesnew[$vid]);
			}
		}
		if(!empty($votesnew)){
			foreach($votesnew as $vid => $votenew){
				$votenew['checked'] = empty($votenew['checked']) ? 0 : $votenew['checked'];
				$votenew['vieworder'] = max(0,intval($votenew['vieworder']));
				$db->query("UPDATE {$tblprefix}votes SET
					checked='$votenew[checked]',
					vieworder='$votenew[vieworder]'
					WHERE vid='$vid'");
			}
		}
		cls_message::show('投票修改完成', "?entry=votes&action=votesedit&page=$page$filterstr");
	}
}elseif($action == 'votedetail' && $vid){
	backnav('vote','admin');
	!vcaidsarr() && cls_message::show('请先添加投票分类！',"?entry=vcatalogs&action=vcatalogsedit");
	if(!($vote = $db->fetch_one("SELECT * FROM {$tblprefix}votes WHERE vid=".$vid))) cls_message::show('指定的投票不存在', "?entry=votes&action=votesedit");
	$voptions = array();
	$query = $db->query("SELECT * FROM {$tblprefix}voptions WHERE vid=".$vid." ORDER BY vieworder,vopid");
	while($voption = $db->fetch_array($query)){
		$voptions[$voption['vopid']] = $voption;
	}
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.urlencode($forward);
	if(!submitcheck('bvotedetail') && !submitcheck('bvoptionadd')){
		tabheader('编辑投票','votedetail',"?entry=votes&action=votedetail&vid=$vid$forwardstr");
		trbasic('投票标题','votenew[subject]',$vote['subject']);
		trbasic('投票分类','votenew[caid]',makeoption(vcaidsarr(),$vote['caid']),'select');
		trbasic('投票说明','votenew[content]',$vote['content'],'textarea');
		trbasic('投票结束日期','votenew[enddate]',(!empty($vote['enddate']) ? date('Y-n-j', $vote['enddate']) : ''),'calendar');
		trbasic('是否多项选择','votenew[ismulti]',$vote['ismulti'],'radio');
		trbasic('禁止游客投票','votenew[onlyuser]',$vote['onlyuser'],'radio');
		trbasic('不能重复投票','votenew[norepeat]',$vote['norepeat'],'radio');
		trbasic('重复投票时间间隔(分钟)','votenew[timelimit]',$vote['timelimit']);
		tabfooter();
		tabheader('投票选项','','',4);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"deltip(this,0,checkall,this.form, 'delete', 'chkall')\">".'删?','选项标题','票数','排序'));
		foreach($voptions as $vopid => $voption){
			echo "<tr class=\"txt\"><td class=\"txtC w50\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$vopid]\" value=\"$vopid\" onclick=\"deltip()\">\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"voptionsnew[$vopid][title]\" value=\"$voption[title]\" size=\"40\"></td>\n".
				"<td class=\"txtC w150\"><input type=\"text\" name=\"voptionsnew[$vopid][votenum]\" value=\"$voption[votenum]\" size=\"10\"></td>\n".
				"<td class=\"txtC w50\"><input type=\"text\" name=\"voptionsnew[$vopid][vieworder]\" value=\"$voption[vieworder]\" size=\"3\"></td></tr>\n";
		}
		tabfooter('bvotedetail');
		tabheader('添加投票选项','voptionadd',"?entry=votes&action=votedetail&vid=$vid$forwardstr");
		trbasic('选项标题','voptionadd[title]');
		trbasic('选项排序','voptionadd[vieworder]');
		tabfooter('bvoptionadd','添加');
		a_guide('votedetail');
	}elseif(submitcheck('bvotedetail')){
		$votenew['timelimit'] = max(0,intval($votenew['timelimit']));
		$votenew['enddate'] = !empty($votenew['enddate']) ? strtotime($votenew['enddate']) : 0;
		$votenew['subject'] = !empty($votenew['subject']) ? $votenew['subject'] : $vote['subject'];
		$db->query("UPDATE {$tblprefix}votes SET 
					subject='$votenew[subject]',
					caid='$votenew[caid]',
					enddate='$votenew[enddate]',
					content='$votenew[content]',
					ismulti='$votenew[ismulti]',
					onlyuser='$votenew[onlyuser]',
					norepeat='$votenew[norepeat]',
					timelimit='$votenew[timelimit]'
					WHERE vid='$vid'");
		if(!empty($delete)){
			foreach($delete as $vopid){
				$db->query("DELETE FROM {$tblprefix}voptions WHERE vopid='$vopid'");
				unset($voptionsnew[$vopid]);
			}
		}
		if(!empty($voptionsnew)){
			foreach($voptionsnew as $vopid => $voptionnew){
				$voptionnew['title'] = !empty($voptionnew['title']) ? $voptionnew['title'] : $voptions[$vopid]['title'];
				$voptionnew['vieworder'] = max(0,intval($voptionnew['vieworder']));
				$voptionnew['votenum'] = max(0,intval($voptionnew['votenum']));
				$db->query("UPDATE {$tblprefix}voptions SET
					title='$voptionnew[title]',
					vieworder='$voptionnew[vieworder]',
					votenum='$voptionnew[votenum]'
					WHERE vopid='$vopid'");
			}
			$counts = $db->result_one("SELECT SUM(votenum) FROM {$tblprefix}voptions WHERE vid='$vid'");
			$db->query("UPDATE {$tblprefix}votes SET totalnum='$counts' WHERE vid='$vid'");
		}
		cls_message::show('投票编辑完成',$forward);
	}elseif(submitcheck('bvoptionadd')){
		empty($voptionadd['title']) && cls_message::show('请输入选项标题',"?entry=votes&action=votedetail&vid=$vid$forwardstr");
		$voptionadd['vieworder'] = max(0,intval($voptionadd['vieworder']));
		$db->query("INSERT INTO {$tblprefix}voptions SET 
					title='$voptionadd[title]',
					vieworder='$voptionadd[vieworder]',
					vid=$vid
					");
		cls_message::show('选项添加完成',"?entry=votes&action=votedetail&vid=$vid$forwardstr");
	}
}
?>