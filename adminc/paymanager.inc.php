<?php
!defined('M_COM') && exit('No Permission');
$currencys = cls_cache::Read('currencys');
if(!submitcheck('bpaymanager')){
	$curuser->detail_data();
	$pmodearr = array('0' => '货到付款','1' => '站内帐户支付','2' => '支付宝支付','3' => '财付通支付');
	$omodearr = array('0' => '必需确认','1' => '无需确认');
	$payarr = array();
	for($i = 0; $i < 32; $i++)if($curuser->info['paymode'] & (1 << $i))$payarr[] = $i;
	for($i = 1; $i < 4; $i++)${"sp$i"} = $curuser->info["shipingfee$i"];
	tabheader('我的支付方式:','paymanager','?action=paymanager',2,1,1);
	trbasic('支付方式','',makecheckbox('paymodenew[]',$pmodearr,$payarr),'');
	trbasic('订单模式','',makeradio('ordermodenew',$omodearr,$curuser->info['ordermode']),'');
	trbasic('<input name="spmd[1]" type="checkbox" class="checkbox" value="1"'.($sp1<0?'':' checked="checked"').' />'.'平邮','shipingfee[1]',$sp1<0?0:$sp1, 'text', array('validate' => makesubmitstr('shipingfee[1]',0,'number',0,10)));
	trbasic('<input name="spmd[2]" type="checkbox" class="checkbox" value="1"'.($sp2<0?'':' checked="checked"').' />'.'特快专递EMS','shipingfee[2]',$sp2<0?0:$sp2, 'text', array('validate' => makesubmitstr('shipingfee[2]',0,'number',0,10)));
	trbasic('<input name="spmd[3]" type="checkbox" class="checkbox" value="1"'.($sp3<0?'':' checked="checked"').' />'.'其它快递公司','shipingfee[3]',$sp3<0?0:$sp3, 'text', array('validate' => makesubmitstr('shipingfee[3]',0,'number',0,10)));
	trbasic('支付宝帐号','alipay_account',$curuser->info['alipay'],'text', array('validate' => makesubmitstr('alipay_account',0,'email',0,100),'w'=>50));
	trbasic('支付宝合作商户ID','alipay_partner',$curuser->info['alipid'], 'text', array('validate' => makesubmitstr('alipay_partner',0,'number',16,16)));
	trbasic('支付宝密钥','alipay_keyt',$curuser->info['alikeyt'],'text',array('w'=>50));
	trbasic('财付通帐号','tenpay_account',$curuser->info['tenpay'],'text',array('w'=>50));
	trbasic('财付通密钥','tenpay_keyt',$curuser->info['tenkeyt'],'text',array('w'=>50));

	tabfooter('bpaymanager');
	m_guide("pay_notes",'fix');
}else{
	$pmode = 0;
	empty($paymodenew) && $paymodenew = array();
	foreach($paymodenew as $v)$pmode = $pmode | (1 << $v);
	foreach($shipingfee as $k => $v)$shipingfee[$k] = empty($spmd[$k])?-1:max(0, round(floatval($v),2));
	$alipay_account = substr(trim(strip_tags($alipay_account)), 0, 50);
	$alipay_partner = substr(trim($alipay_partner), 0, 16);
	is_numeric($alipay_partner) || $alipay_partner = '';
	$alipay_keyt = substr(trim(strip_tags($alipay_keyt)), 0, 50);
	$tenpay_account = substr(trim(strip_tags($tenpay_account)), 0, 50);
	$tenpay_keyt = substr(trim(strip_tags($tenpay_keyt)), 0, 50);
	$db->query("UPDATE {$tblprefix}members_sub SET
				 ordermode='$ordermodenew',
				 shipingfee1='$shipingfee[1]',
				 shipingfee2='$shipingfee[2]',
				 shipingfee3='$shipingfee[3]',
				 paymode='$pmode',
				 alipay='$alipay_account',
				 alipid='$alipay_partner',
				 alikeyt='$alipay_keyt',
				 tenpay='$tenpay_account',
				 tenkeyt='$tenpay_keyt'
				 WHERE mid=$memberid
				 ");
	cls_message::show('支付方式设置完成','?action=paymanager');
}
?>
