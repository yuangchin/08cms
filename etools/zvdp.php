<?php
	include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
	include_once M_ROOT."./include/adminm.fun.php";
	$aid = empty($aid) ? 0 : max(0,intval($aid));
	$inajax = empty($inajax) ? 0 : 1;
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.urlencode($forward);
	$cuid = 41;
	$fields = cls_cache::Read('cufields',$cuid);
	$ziduan = empty($ziduan) ? '0' : $ziduan;
	if(empty($ziduan) && !isset($fields[$ziduan])){ 
		cls_message::show('参数错误！',$forward);
	}
	foreach($fields as $k => $v){//判断是aid为$aid的资讯是否存在点评cookies，只要存在任何一个表情的cookies，都不能再次点评了，除非cookies失效
		if(!empty($m_cookie["08cms_cuid{$cuid}_dp_{$k}_$aid"])){
			$cookie_exit = 1;
		}
	}
	if(empty($cookie_exit)){ //这个cookie要与ajax/ajax_yuedu_xinqing.php一致
		msetcookie("08cms_cuid{$cuid}_dp_{$ziduan}_$aid",1,24 * 86400);
	}else {		
		if(isset($js) && $js) {
			exit('var face = 0;');
		} else {
			cls_message::show('您已经点评过了。',$forward);
		}
	}
	if(!$aid || !$db->result_one("SELECT aid FROM {$tblprefix}commu_zxdp WHERE aid='$aid'")){		 
		$db->query("INSERT INTO {$tblprefix}commu_zxdp SET aid = '$aid',".$ziduan."='1'");
	}else{
		$db->query("UPDATE {$tblprefix}commu_zxdp SET  ".$ziduan."= ".$ziduan." + 1 WHERE aid='$aid'");
	}
	if(isset($js) && $js) {
		exit('var face = 1;');
	} else {
		cls_message::show($inajax ? 'succeed' : '点评成功。',$forward);
	}

?>