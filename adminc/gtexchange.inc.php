<?php
!defined('M_COM') && exit('No Permission');
$currencys = cls_cache::Read('currencys');
$grouptypes = cls_cache::Read('grouptypes');

$mchid = $curuser->info['mchid'];
$cashgtids = array();
foreach($grouptypes as $k => $v){
	if($v['mode'] == 3 && !in_array($mchid,explode(',',$v['mchids']))){
		if(empty($gtid) || $gtid == $k) $cashgtids[$k] = $v;
	}
}
empty($cashgtids) && cls_message::show('请先添加有效的积分兑换会员组');
if(!submitcheck('bgtexchange')){
	foreach($cashgtids as $k => $v){
		$usergroups = cls_cache::Read('usergroups',$k);
		$ugidsarr = array();
		foreach($usergroups as $x => $y){
			if(in_array($mchid,explode(',',$y['mchids']))){
				$ugidsarr[$x] = $y['cname'].'('.$y['currency'].')';
				if($x == $curuser->info['grouptype'.$k]){
					if(!$curuser->info['grouptype'.$k.'date']) unset($ugidsarr[$x]);
					break;
				}
			}
		}
		$crname = empty($v['crid']) ? '现金': $currencys[$v['crid']]['cname'];
		tabheader('使用 '.$crname.' 购买 '.$v['cname'].' 中的会员组','gtexchagne'.$k,"?action=gtexchange&gtid=$k");
		trbasic('您拥有的 '.$crname.' 数量为','',$curuser->info['currency'.$v['crid']],'');
		trbasic('您所属的会员组是','',$curuser->info['grouptype'.$k] ? $usergroups[$curuser->info['grouptype'.$k]]['cname'] : '-','');
		trbasic('当前会员组结束日期','',$curuser->info['grouptype'.$k.'date'] ? date($dateformat,$curuser->info['grouptype'.$k.'date']) : '-','');
		$ugidsarr && trbasic('请选择要购买的组','exchangeugid',makeoption($ugidsarr),'select');
		$ugidsarr ? tabfooter('bgtexchange','兑换') : tabfooter();
	}
}else{
	(empty($gtid) || empty($grouptypes[$gtid]) || in_array($mchid,explode(',',$grouptypes[$gtid]['mchids']))) && cls_message::show('请指定会员组体系',M_REFERER);
	$grouptype = $grouptypes[$gtid];
	$crid = $grouptype['crid']; 
	$usergroups = cls_cache::Read('usergroups',$gtid);
	(empty($exchangeugid) || empty($usergroups[$exchangeugid]) || !in_array($mchid,explode(',',$usergroups[$exchangeugid]['mchids']))) && cls_message::show('请指定会员组',M_REFERER);
	$curuser->info['currency'.$crid] < $usergroups[$exchangeugid]['currency'] && cls_message::show('没有足够积分',M_REFERER);
	$usergroup = cls_cache::Read('usergroup',$gtid,$exchangeugid);
	if($curuser->info['grouptype'.$gtid] == $exchangeugid){//续期
		if($usergroup['limitday'] && $curuser->info['grouptype'.$gtid.'date']){
			$curuser->updatefield('grouptype'.$gtid.'date',$curuser->info['grouptype'.$gtid.'date'] + $usergroup['limitday'] * 86400);
		}else{
			$curuser->updatefield('grouptype'.$gtid.'date',0);
		}
	}else{//变更
		$curuser->updatefield('grouptype'.$gtid,$exchangeugid);
		if($usergroup['limitday']){
			$curuser->updatefield('grouptype'.$gtid.'date',$timestamp + $usergroup['limitday'] * 86400);
		}else{
			$curuser->updatefield('grouptype'.$gtid.'date',0);
		}
	}
	$curuser->updatecrids(array($crid => -$usergroup['currency']),1,'积分兑换会员组');
	cls_message::show('积分兑换会员组完成',M_REFERER);
}
?>
