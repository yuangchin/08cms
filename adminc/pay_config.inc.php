<?php
!defined('M_COM') && exit('No Permission');
if($curuser->getTrusteeshipInfo()) cls_message::show('您是代管用户，当前操作仅原用户本人有权限！');
$poids = _08_factory::getInstance(_08_Loader::MODEL_PREFIX . 'PayGate_Pays')->getPays();
!isset($poids['alipay_direct']) && cls_message::show('支付宝即时到账功能已关闭');
$db = _08_factory::getDBO();
$row = $db->select()->from('#__pays_account')->where(array('id' => $curuser->info['mid']))->limit(1)->exec()->fetch();
if(!submitcheck('bsubmit')){
	tabheader("支付宝即时到帐配置",'myform',"?action=$action",2,1,1);
	trbasic('支付宝帐户','fmdata[alipay_seller_account]',$row['alipay_seller_account'] ? $row['alipay_seller_account'] : '','text',array('validate'=>'rule="text" must=1'));
	trbasic('合作者身份(PID) 	','fmdata[alipay_partnerid]',$row['alipay_partnerid'] ? $row['alipay_partnerid'] : '','text',array('validate'=>'rule="text" must=1'));
	trbasic('安全校验码(Key)','fmdata[alipay_partnerkey]',$row['alipay_partnerkey'] ? authcode($row['alipay_partnerkey'], 'DECODE', $curuser->info['salt']) : '','password',array('validate'=>'rule="text" must=1','guide'=>'请随意输入字母或者数字'));
	tabfooter('bsubmit');
}else{
	if(!empty($fmdata['alipay_seller_account']) && !empty($fmdata['alipay_partnerid']) && !empty($fmdata['alipay_partnerkey'])){
		$alipay_partnerkey = authcode($fmdata['alipay_partnerkey'], 'ENCODE', $curuser->info['salt']); #安全校验KEY
		if(!$row){
			$db->insert( '#__pays_account', 
        		array(
            	'alipay_seller_account' => $fmdata['alipay_seller_account'], 
            	'alipay_partnerid' => $fmdata['alipay_partnerid'], 
            		'id' => $curuser->info['mid'],
				'alipay_partnerkey'=> $alipay_partnerkey     
        		)
    		)->exec();
		}else{
			$db->query("UPDATE {$tblprefix}pays_account SET 
            	`alipay_seller_account`='{$fmdata['alipay_seller_account']}',  
            	`alipay_partnerid`='{$fmdata['alipay_partnerid']}',  
				`alipay_partnerkey`='{$alipay_partnerkey}',  
    		WHERE id='{$curuser->info['mid']}'");
		}
	}
	cls_message::show('配置成功!',"?action=$action");
}

		

?>