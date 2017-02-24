<?php
!defined('M_COM') && exit('No Permission');
backnav('pm','send');
//$enable_uc && include_once M_ROOT.'adminm/pmuc.inc.php';
if(!submitcheck('bpmsend')){
	tabheader("发送短信",'pmsend','?action=pmsend',2,0,1);
	trbasic('标题','pmnew[title]','','text', array('validate' => makesubmitstr('pmnew[title]',1,0,0,80),'w'=>50));
	trbasic('发送至','pmnew[tonames]',empty($tonames) ? '' : $tonames,'text', array('guide' => '用逗号分隔多个会员名称','validate' => makesubmitstr('pmnew[tonames]',1,0,0,100),'w'=>50));
	trbasic('内容','pmnew[content]','','textarea', array('w' => 500,'h' => 300,'validate' => makesubmitstr('pmnew[content]',1,0,0,1000)));
	tr_regcode('pm');
	tabfooter('bpmsend');
	m_guide('sms_insite','fix');
}else{
	if(!regcode_pass('pm',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码输入错误！',M_REFERER);
	$pmnew['title'] = trim($pmnew['title']);
	$pmnew['tonames'] = trim($pmnew['tonames']);
	$pmnew['content'] = trim($pmnew['content']);
	if(empty($pmnew['title']) || empty($pmnew['content']) || empty($pmnew['tonames'])){
		cls_message::show('短信资料不完全',M_REFERER);
	}
	$tonames = array_filter(explode(',',$pmnew['tonames']));
	if($tonames){
		$query = $db->query("SELECT mid FROM {$tblprefix}members WHERE mname ".multi_str($tonames)." ORDER BY mid");
		$sqlstr = '';
        $uids = array();
		while($user = $db->fetch_array($query)){
			//收信数量限制分析
			$sqlstr .= ($sqlstr ? ',' : '')."('$pmnew[title]','$pmnew[content]','$user[mid]','$memberid','".$curuser->info['mname']."','$timestamp')";
            $uids[] = $user['mid'];
		}
		$sqlstr && $db->query("INSERT INTO {$tblprefix}pms (title,content,toid,fromid,fromuser,pmdate) VALUES $sqlstr");
        # 给WINDID用户发送短信
        cls_WindID_Send::getInstance()->send( $uids, $pmnew['content'], $memberid );
	}
	cls_message::show('短信发送成功','?action=pmsend');
}
?>