<?php
!defined('M_COM') && exit('No Permission');
foreach(array('crprojects','currencys') as $k) $$k = cls_cache::Read($k);
backnav('currency','exchange');
if($curuser->getTrusteeshipInfo()) cls_message::show('您是代管用户，当前操作仅原用户本人有权限！');
if($enable_uc){ 
	$outextcredits = @unserialize($outextcredits);
	$outextcredits || $outextcredits = array();
}
if(!submitcheck('bcrexchange')){
	$cridsarr = cridsarr(1);
	foreach($crprojects as $crpid => $crproject){
		tabheader($cridsarr[$crproject['scrid']].'&nbsp;&nbsp;兑换为&nbsp;&nbsp;'.$cridsarr[$crproject['ecrid']],'crexchagne'.$crpid,"?action=crexchange");
		trbasic('您拥有的 '.$cridsarr[$crproject['scrid']].' 数量为','',$curuser->info['currency'.$crproject['scrid']],'');
		trbasic('您拥有的 '.$cridsarr[$crproject['ecrid']].' 数量为','',$curuser->info['currency'.$crproject['ecrid']],'');
		trbasic('兑换比例','',$crproject['scurrency'].'&nbsp; '.$cridsarr[$crproject['scrid']].'&nbsp; :&nbsp; '.$crproject['ecurrency'].'&nbsp; '.$cridsarr[$crproject['ecrid']],'');
		trbasic('兑换数量'.'('.$cridsarr[$crproject['scrid']].')','exchangesource');
		echo "<input type=\"hidden\" name=\"crpid\" value=\"$crpid\">";
		tabfooter('bcrexchange','兑换');
		$_list = 1;
	}
	if($enable_uc){
		foreach($outextcredits as $k => $v){
			tabheader($cridsarr[$v['creditsrc']].'&nbsp;&nbsp;兑换为&nbsp;&nbsp;'.$v['title'],'ocrexchagne'.$k,"?action=crexchange");
			trbasic('您拥有的 '.$cridsarr[$v['creditsrc']].' 数量为','',$curuser->info['currency'.$v['creditsrc']],'');
			trbasic('兑换比例','',$v['ratiosrc' ].'&nbsp; :&nbsp; '.$v['ratiodesc' ],'');
			trbasic('兑换数量'.'('.$cridsarr[$v['creditsrc']].')','exchangesource');
			echo "<input type=\"hidden\" name=\"ocrpid\" value=\"$k\">";
			echo "<input type=\"hidden\" name=\"isout\" value=\"1\">";
			tabfooter('bcrexchange','兑换');
			$_list = 1;
		}
	}
	empty($_list) && cls_message::show('没有可用的积分兑换项目');
	m_guide("cur_notes",'fix');
}else{
	if(empty($isout)){
		(empty($crpid) || empty($crprojects[$crpid])) && cls_message::show('请指定当前兑换方案');
		$exchangesource = max(0,intval($exchangesource));
		!$exchangesource && cls_message::show('请输入兑换数量');
		$crproject = $crprojects[$crpid];
		($exchangesource < $crproject['scurrency']) && cls_message::show('兑换数量少于兑换基数');
		if($exchangesource > $curuser->info['currency'.$crproject['scrid']]) cls_message::show('兑换数量大于拥有总数');
		$num = floor($exchangesource / $crproject['scurrency']);
		$curuser->updatecrids(array($crproject['scrid'] => -$crproject['scurrency'] * $num),0,'积分兑换积分');
		$curuser->updatecrids(array($crproject['ecrid'] => $crproject['ecurrency'] * $num),0,'积分兑换积分');
		$curuser->updatedb();
		cls_message::show('积分兑换完成',"?action=crexchange");
	}else{
		empty($outextcredits[$ocrpid]) && cls_message::show('请指定UCenter积分兑换项目');
		$exchangesource = max(0,intval($exchangesource));
		!$exchangesource && cls_message::show('请输入兑换数量');
		$outcredit = $outextcredits[$ocrpid];
		($exchangesource < $outcredit['ratiosrc']) && cls_message::show('兑换数量少于兑换基数');
		if($exchangesource > $curuser->info['currency'.$outcredit['creditsrc']]) cls_message::show('兑换数量大于拥有总数');
		$num = floor($exchangesource / $outcredit['ratiosrc']);
		
		cls_ucenter::init();
		$ucresult = uc_get_user($curuser->info['mname']);
		if(!is_array($ucresult)) cls_message::show('UCenter中没有当前会员资料！');
		$uid = $ucresult[0];
		$ucresult = uc_credit_exchange_request($uid,$outcredit['creditsrc'],$outcredit['creditdesc'],$outcredit['appiddesc'],$outcredit['ratiodesc'] * $num);
		if(!$ucresult) cls_message::show('积分兑换失败',"?action=crexchange");
		
		$curuser->updatecrids(array($outcredit['creditsrc'] => -$outcredit['ratiosrc'] * $num),0,'货币兑换货币');
		$curuser->updatedb();
		cls_message::show('积分兑换完成',"?action=crexchange");
	}
}
?>
