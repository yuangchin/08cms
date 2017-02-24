<?php
!defined('M_COM') && exit('No Permission');
$currencys = cls_cache::Read('currencys');
backnav('payonline','other');
if($curuser->getTrusteeshipInfo()) cls_message::show('您是代管用户，当前操作仅原用户本人有权限！');
if(!submitcheck('bpayother')){
	if(!$oldmsg = $db->fetch_one("SELECT * FROM {$tblprefix}pays WHERE mid='$memberid' ORDER BY pid DESC LIMIT 0,1")) $oldmsg = array();
	$pmodearr = array('0' => '上门支付','2' => '银行转账','3' => '邮局汇款');
	tabheader("现金支付信息通知管理员",'payother','?action=payother',2,1,1);
	trbasic('支付方式','',makeradio('paynew[pmode]',$pmodearr),'');
	trbasic('支付金额(元)','paynew[amount]', '', 'text', array('validate' => makesubmitstr('paynew[amount]',1,'number',0,15)));
	trbasic('联系人名字','paynew[truename]',empty($oldmsg['truename']) ? '' : $oldmsg['truename'],'text', array('validate' => makesubmitstr('paynew[truename]',0,0,0,80),'w'=>50));
	trbasic('联系电话','paynew[telephone]',empty($oldmsg['telephone']) ? '' : $oldmsg['telephone'],'text', array('validate' => makesubmitstr('paynew[telephone]',0,0,0,30),'w'=>50));
	trbasic('联系Email','paynew[email]',empty($oldmsg['email']) ? '' : $oldmsg['email'],'text', array('validate' => makesubmitstr('paynew[email]',0,'email',0,100),'w'=>50));
	trbasic('备注','paynew[remark]',empty($oldmsg['remark']) ? '' : $oldmsg['remark'],'textarea', array('validate' => makesubmitstr('paynew[remark]',0,0,0,200)));
	trspecial('支付凭证',specialarr(array('type' => 'image','varname' => 'paynew[warrant]','value' => '',)));
	tr_regcode('payonline');
	tabfooter('bpayother');
	m_guide("pay_notes",'fix');
}else{
	if(!regcode_pass('payonline',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码输入错误！','?action=payother');
	$paynew['amount'] = max(0,round(floatval($paynew['amount']),2));
	empty($paynew['amount']) && cls_message::show('请输入支付数量','?action=payother');
	$paynew['truename'] = trim(strip_tags($paynew['truename']));
	$paynew['telephone'] = trim(strip_tags($paynew['telephone']));
	$paynew['email'] = trim(strip_tags($paynew['email']));
	$c_upload = cls_upload::OneInstance();
	$paynew['warrant'] = upload_s($paynew['warrant'],'','image');
	$c_upload->saveuptotal(1);
	$db->query("INSERT INTO {$tblprefix}pays SET
				 mid='".$memberid."',
				 mname='".$curuser->info['mname']."',
				 pmode='$paynew[pmode]',
				 amount='$paynew[amount]',
				 truename='$paynew[truename]',
				 telephone='$paynew[telephone]',
				 email='$paynew[email]',
				 remark='$paynew[remark]',
				 warrant='$paynew[warrant]',
				 senddate='$timestamp',
				 ip='$onlineip'
				 ");
	$c_upload->closure(1, $db->insert_id(), 'pays');
	cls_message::show('现金充值通知发送成功,请等待管理员处理','?action=pays');
}
?>
