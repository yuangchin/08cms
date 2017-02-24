<?php
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT.'./include/common.fun.php';
$inajax = empty($inajax) ? 0 : 1;
$aid = empty($aid) ? 0 : max(0,intval($aid));
$isatm = empty($isatm) ? 0 : 1;
!$aid && cls_message::show('confchoosarchi');
!$memberid && cls_message::show('nousernosubper');

$commu = cls_cache::Read('commu',8);
empty($commu) && cls_message::show('choosecommuitem');
if(empty($commu['ucadd'])){
	!$curuser->pmbypmid($commu['setting']['apmid']) && cls_message::show('younoitempermis');
	$arc = new cls_arcedit();
	!$arc->set_aid($aid,array('au'=>0)) && cls_message::show('choosearchive');
	!$arc->archive['checked'] && cls_message::show('poinarcnoche'); 
	
	$stritem = $isatm ? 'attachment' : 'archive';
	if(!($crids = $arc->arc_crids($isatm))) cls_message::show("youalrpurchasestritem",'',$stritem); 
	
	$cridstr = '';
	foreach($crids['total'] as $k => $v) $cridstr .= ($cridstr ? ',' : '').abs($v).$currencys[$k]['unit'].$currencys[$k]['cname'];
	if(!$curuser->crids_enough($crids['total'])) cls_message::show('younopurcstriwanenocurr','',$stritem);
	$curuser->updatecrids($crids['total'],0,"购买$stritem");
	$curuser->payrecord($arc->aid,$isatm,$cridstr,1);
	if(!empty($crids['sale'])){
		$actuser = new cls_userinfo;
		$actuser->activeuser($arc->archive['mid']);
		foreach($crids['sale'] as $k => $v) $crids['sale'][$k] = -$v;
		$actuser->updatecrids($crids['sale'],1,"出售$stritem");
		unset($actuser);
	}
	cls_message::show($inajax ? 'succeed' : 'operatesucceed');
}else include(M_ROOT.$commu['ucadd']);
?>