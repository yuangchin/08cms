<?php
!defined('M_COM') && exit('No Permission');
backnav('payonline','online');
if($curuser->getTrusteeshipInfo()) cls_message::show('您是代管用户，当前操作仅原用户本人有权限！');
$poids = _08_factory::getInstance(_08_Loader::MODEL_PREFIX . 'PayGate_Pays')->getPays();
empty($poids) && !empty($deal) && cls_message::show('没有有效的在线支付接口');
$extra_param = empty($jumpurl) ? '' : '&jumpurl=' . rawurlencode($jumpurl);
if(empty($deal)){
	if(empty($poids)){
		tabheader("在线支付");
		trbasic('','',"<br>非常抱歉，网站至少需要开通一个支付接口才可以在线支付。<br><br>您可以选择 &nbsp;<a href=\"?action=payother$extra_param\">>>其它支付</a>",'');
		tabfooter();
	}else{
		tabheader("在线支付",'paynew',"?action=payonline&deal=confirm$extra_param",2,0,1);
        trhidden('WIDdefaultbank', '');
        trhidden('WIDdefaultbank_name', '');
		$amount = empty($amount) ? '' : max(0,round($amount,2));
		if(!$oldmsg = $db->fetch_one("SELECT * FROM {$tblprefix }pays WHERE mid='$memberid' ORDER BY pid DESC LIMIT 0,1")) $oldmsg = array();
		trbasic('支付接口','',makeradio('paynew[poid]',$poids),'');
		trbasic('支付金额','paynew[amount]',$amount,'text',array('guide' => '支付金额(人民币)', 'validate' => makesubmitstr('paynew[amount]',1,'number',0,15)));
		trbasic('联系人名字','paynew[truename]',empty($oldmsg['truename']) ? '' : $oldmsg['truename'],'text', array('validate' => makesubmitstr('paynew[truename]',0,0,0,80),'w'=>50));
		trbasic('联系电话','paynew[telephone]',empty($oldmsg['telephone']) ? '' : $oldmsg['telephone'],'text', array('validate' => makesubmitstr('paynew[telephone]',0,0,0,30),'w'=>50));
		trbasic('联系Email','paynew[email]',empty($oldmsg['email']) ? '' : $oldmsg['email'],'text', array('validate' => makesubmitstr('paynew[email]',0,'email',0,100),'w'=>50));
		tr_regcode('payonline');
		tabfooter('bsubmit','继续');
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC("ajax=show_bank&datatype=content");
        echo <<<EOT
        <script type="text/javascript">
            var bankObject = document.getElementById('_paynew[poid]alipay_direct_bankpay');
            if ( bankObject )
            {
                var htmlSpan = document.createElement("span");
                htmlSpan.id = 'bankname';
                htmlSpan.style.color = 'red';
                bankObject.parentNode.appendChild(htmlSpan);
                bankObject.onclick = function() {
                    selectBank(this.value);
                }
                function selectBank( _value )
                {
                    uploadwin('show_bank', function(data){}, 0, 0, 0, 0, 0, 800, 715, '$ajaxURL');
                }
            }            
        </script>
EOT;
	}
	m_guide("pay_notes",'fix');
}elseif($deal == 'confirm'){
	if(!regcode_pass('payonline',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码输入错误！','?action=payonline');
	$paynew['amount'] = max(0,round(floatval($paynew['amount']),2));
	empty($paynew['amount']) && cls_message::show('请输入支付金额','?action=payonline');
	array_key_exists($paynew['poid'], $poids) || cls_message::show('支付模式错误或信息不完整','?action=payonline');
	$paynew['truename'] = trim(strip_tags($paynew['truename']));
	$paynew['telephone'] = trim(strip_tags($paynew['telephone']));
	$paynew['email'] = trim(strip_tags($paynew['email']));
	tabheader('确认付款信息','paynew',"?action=payonline&deal=send$extra_param");
	
    if ( isset($paynew['poid']) && (false !== stripos($paynew['poid'], 'alipay')) )
    {
        echo '<tr><td colspan="2">';
        $payname = '支付宝';
        if ( empty($WIDdefaultbank) )
        {
            $WIDdefaultbank = '';
        }
        else
        {
        	$WIDdefaultbank = trim($WIDdefaultbank);
            empty($WIDdefaultbank_name) && $WIDdefaultbank_name = '';
            $payname .= "网银（<span style='color:red;'>{$WIDdefaultbank_name}</span>）";
        }
        trhidden('paynew[defaultbank]', empty($WIDdefaultbank) ? '' : trim($WIDdefaultbank));
        echo '</td></tr>';
        trbasic( '支付接口','', $payname, '' );
    }
    else
    {
    	trbasic('支付接口','',$poids[$paynew['poid']],'');
    }
    
	trbasic('支付金额','',$paynew['amount'],'',array('guide' => '支付金额(人民币)'));
	trbasic('联系人名字','',$paynew['truename'],'');
	trbasic('联系电话','',$paynew['telephone'],'');
	trbasic('联系Email','',$paynew['email'],'');
	echo "<tr><td colspan=\"2\"><input type=\"hidden\" name=\"paynew[poid]\" value=\"$paynew[poid]\">\n";
	echo "<input type=\"hidden\" name=\"paynew[amount]\" value=\"$paynew[amount]\">\n";
	echo "<input type=\"hidden\" name=\"paynew[truename]\" value=\"$paynew[truename]\">\n";
	echo "<input type=\"hidden\" name=\"paynew[telephone]\" value=\"$paynew[telephone]\">\n";
	echo "<input type=\"hidden\" name=\"paynew[email]\" value=\"$paynew[email]\"></td></tr>\n";
	tabfooter('bsubmit','确认并付款');
}elseif($deal == 'send'){
	(empty($paynew) || !array_key_exists($paynew['poid'], $poids)) && cls_message::show('支付模式错误或信息不完整','?action=payonline');
    $paynew['subject'] = $hostname;
    $paynew['callback'] = _08_CMS_ABS . str_replace('&deal=send', '', substr($_SERVER['REQUEST_URI'], strlen($cmsurl)));
    _08_factory::getPays($paynew['poid'])->send($paynew);
}elseif($deal == 'receive'){
	$pid = empty($pid) ? 0 : (int)$pid;
	empty($pid) && cls_message::show('请指定正确的支付');
	if(!$item = $db->fetch_one("SELECT * FROM {$tblprefix }pays WHERE pid='$pid'")) cls_message::show('请指定正确的支付记录');
	$flagarr = array(
	0 => '会员现金支付保存成功！',
	2 => '从在线支付接口返回支付失败的消息',
	3 => '支付金额和记录不相同，请等待管理员处理！',
	4 => '存在的支付记录，请不要重复操作',
	5 => '现金收到，会员现金自动保存不成功，请通知管理员！',
	6 => '现金收到，自动充值功能关闭，请等待管理员联系！',
	);
	tabheader('在线支付消息查看');
	trbasic('支付结果状态','',$flagarr[$flag],'');
	trbasic('支付金额(人民币)','',$item['amount'],'');
	trbasic('手续费(人民币)','',$item['handfee'],'');
	trbasic('支付接口','',$item['poid'] ? $poids[$item['poid']] : '-','');
	trbasic('支付命令编号','',$item['ordersn'] ? $item['ordersn'] : '-','');
	trbasic('消息发送时间','',date("$dateformat $timeformat",$item['senddate']),'');
	trbasic('现金到达时间','',$item['receivedate'] ? date("$dateformat $timeformat",$item['receivedate']) : '-','');
#	trbasic('积分保存时间','',$item['transdate'] ? date("$dateformat $timeformat",$item['transdate']) : '-','');
	tabfooter();
}
?>