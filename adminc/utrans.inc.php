<?php
!defined('M_COM') && exit('No Permission');
foreach(array('mchannels','uprojects','grouptypes',) as $k) $$k = cls_cache::Read($k);
if(!isset($utran['toid'])){
	$notranspro = true;
	foreach($grouptypes as $gtid => $grouptype){
		if(!$grouptype['issystem'] && $grouptype['mode'] == 1){
			$toidsarr = array();
			$usergroups = cls_cache::Read('usergroups',$gtid);
			foreach($uprojects as $k => $v){
				if(($v['sugid'] == $curuser->info["grouptype$gtid"]) && ($v['gtid'] == $gtid)){
					if($v['tugid'] && empty($usergroups[$v['tugid']])) continue;
					$toidsarr[$v['tugid']] = $v['tugid'] ? $usergroups[$v['tugid']]['cname'] : '组外会员';
				}
			}
			if($toidsarr){
				$isold = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}utrans WHERE mid='$memberid' AND checked='0' AND gtid='$gtid'");
				$nowugstr = '&nbsp; '.'您所在组'.'&nbsp;:&nbsp;'.($curuser->info["grouptype$gtid"] ? $usergroups[$curuser->info["grouptype$gtid"]]['cname'] : '组外会员');
				tabheader("[$grouptype[cname]]变更申请$nowugstr","utrans$gtid","?action=utrans");
				trhidden('gtid',$gtid);
				trbasic('变更目标会员组','utran[toid]',makeoption($toidsarr),'select');
				tabfooter('bsubmit', $isold ? '修改' : '申请');
				$notranspro = false;
			}
		}
	}	
	$notranspro && cls_message::show('没有您可用的变更方案！');
}else{
	if(empty($gtid)) cls_message::show('请指定正确的会员组体系!');
	foreach($uprojects as $k => $v){
		if($v['ename'] == $curuser->info["grouptype$gtid"].'_'.$utran['toid']) $uproject = $v;
	}
	if(empty($uproject)) cls_message::show('您不能申请指定的会员组!');
	$sugid = $curuser->info["grouptype$gtid"];
	$tugid = $utran['toid'];
	$mchid = $curuser->info['mchid'];
	if(in_array($mchid,explode(',',$grouptypes[$gtid]['mchids']))) cls_message::show('您所属的会员模型不能申请此会员组!');
	if($tugid && (!($usergroup = cls_cache::Read('usergroup',$gtid,$tugid)) || !in_array($mchid,explode(',',$usergroup['mchids'])))) cls_message::show('您所属的会员模型不能申请此会员组!');
	//分析是已有更新申请还是新的申请
	$isold = false;
	//仅需要读出上次申请时间，备注与回复出来
	if($minfos = $db->fetch_one("SELECT * FROM {$tblprefix}utrans WHERE mid='$memberid' AND checked='0' AND gtid='$gtid'")){
		$isold = true;
	}
	$minfos['fromid'] = $curuser->info["grouptype$gtid"];
	$minfos['toid'] = $utran['toid'];
	if(!submitcheck('butran')){
		$usergroups = cls_cache::Read('usergroups',$gtid);
		$submitstr = '';
		tabheader('会员组申请方式'.'&nbsp; -&nbsp; '.$grouptypes[$gtid]['cname'],'utrans',"?action=utrans",2,1,1);
		trbasic('会员级变更方式','',(!$sugid ? '组外会员': $usergroups[$sugid]['cname']).'&nbsp; ->&nbsp; '.(!$tugid ? '组外会员': $usergroups[$tugid]['cname']),'');
		trhidden('utran[toid]',$tugid);
		trhidden('gtid',$gtid);
		trbasic('申请时间','',date("Y-m-d H:i",$isold ? $minfos['createdate'] : $timestamp),'');
		trbasic('备注','utran[remark]',empty($minfos['remark']) ? '' : $minfos['remark'],'textarea');
		$isold && trbasic('管理员回复'.@noedit(1),'',$minfos['reply'],'textarea');
		tabfooter('butran');
	}else{
		//需要检查一下，当前会员是否允许加入到新的会员组
		$omchid = $curuser->info['mchid'];//原模型
		if($uproject['autocheck']){
			$curuser->updatefield("grouptype$gtid",$tugid);
			$curuser->updatedb();
			if($isold){
				$db->query("UPDATE {$tblprefix}utrans SET toid='$tugid',fromid='$sugid',remark='',reply='',checked='1' WHERE mid='$memberid' AND checked='0' AND gtid='$gtid'");
			}else{
				$db->query("INSERT INTO {$tblprefix}utrans SET mid='$memberid',mname='".$curuser->info['mname']."',gtid='$gtid',toid='$tugid',fromid='$sugid',remark='',checked='1',createdate='$timestamp'");
			}
		}else{
			$utran['remark'] = trim($utran['remark']);
			if($isold){
				$db->query("UPDATE {$tblprefix}utrans SET toid='$tugid',fromid='$sugid',remark='$utran[remark]' WHERE mid='$memberid' AND checked='0' AND gtid='$gtid'");
			}else{
				$db->query("INSERT INTO {$tblprefix}utrans SET mid='$memberid',mname='".$curuser->info['mname']."',gtid='$gtid',toid='$tugid',fromid='$sugid',remark='$utran[remark]',checked='0',createdate='$timestamp'");
			}
		}
		cls_message::show($uproject['autocheck'] ? '会员组设置成功' : '请等待管理员审核！',"?action=utrans");
	}
}
?>
