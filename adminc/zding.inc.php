<?php
!defined('M_COM') && exit('No Permission');

$ngtid = 14;
$nmchid = array(1,2,13);
$nugid = 8;
$ncoid = 9;
$nccid = 204;
$nchids = array(2,3,107,117,118,119,120);

if(!in_array($curuser->info['mchid'],$nmchid)) cls_message::show('无权限使用本功能！');
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
if(!($rules = @$exconfigs['zding'])) cls_message::show('系统没有置顶规则。');

$arc = new cls_arcedit;
$arc->set_aid($aid,array('au'=>0));
!$arc->aid && cls_message::show('选择文档');
$arc->archive['mid'] == $memberid || cls_message::show('您只能置顶自已发布的房源。');
$chid = $arc->archive['chid'];
if(!in_array($chid,$nchids) || !$arc->archive['checked']) cls_message::show('只能置顶已审的房源。');

$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.urlencode($forward);
if(!submitcheck('bsubmit')){
	tabheader("房源置顶-{$arc->archive['subject']}","{$action}newform","?action=$action$forwardstr&aid=$aid",2,0,1);
	trbasic('当前置顶状态','',$arc->archive["ccid$ncoid"] == $nccid ? "<font color=\"#FF0000\">置顶<font>" : '未置顶','');
	trbasic('置顶到期时间','',$arc->archive["ccid{$ncoid}date"] ? date('Y-m-d H:i',$arc->archive["ccid{$ncoid}date"]) : '-','');
	trbasic('房源置顶天数余额','',$curuser->info['freezds'].' 天','');
	trbasic('现金帐户余额','',$curuser->info['currency0']."元 &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
	if(($arc->archive["ccid$ncoid"] == $nccid) && !$arc->archive["ccid{$ncoid}date"]){
		trbasic('置顶说明','','本房源已永久置顶。','');
	}else{
		$str = "如果您拥有房源置顶余额，优先使用此余额，按置顶天数扣除余额。<br>";
		$str .= "置顶一次不能少于 $rules[minday] 天，房源置顶每天 $rules[price] 元。<br>";
		$str .= "如果本房源本是置顶房源，则为续期。<br>";
		trbasic('置顶说明','',$str,'');
		trbasic('房源置顶天数','zdingday','','text',array('w' => 10,'validate' => makesubmitstr('zdingday',1,0,$rules['minday'],'','int')));
		trbasic('本次置顶应扣费用','',"<div id='payment_instr'>-</div>",'');
		tabfooter('bsubmit');
	}

}else{
	$zdingday = empty($zdingday) ? 0 : max(0,intval($zdingday));
	if($zdingday < $rules['minday']) cls_message::show("升级天数不能少于 $rules[minday] 天。",M_REFERER);
	$needfreezds = 0;$needcurrency0 = $zdingday * $rules['price'];
	if($curuser->info['freezds']){
		$needfreezds = min($curuser->info['freezds'],$zdingday);
		$needcurrency0 -= $needfreezds * $rules['price'];
	}
	if($curuser->info['currency0'] < $needcurrency0) cls_message::show('您的现金帐户余额不足，请充值。',M_REFERER);
	$curuser->updatefield('freezds',$curuser->info['freezds'] - $needfreezds);
	$curuser->updatecrids(array(0 => -$needcurrency0),1,'购买房源置顶。');
	
	$arc->updatefield("ccid{$ncoid}date",($arc->archive["ccid{$ncoid}"] == $nccid ? $arc->archive["ccid{$ncoid}date"] : $timestamp) + $zdingday * 86400);
	$arc->updatefield("ccid$ncoid",$nccid);
	if($arc->archive["enddate"] && $arc->archive["enddate"] < $arc->archive["ccid{$ncoid}date"]) $arc->updatefield("enddate",$arc->archive["ccid{$ncoid}date"]);
	$arc->updatedb();
	cls_message::show('房源置顶成功。',axaction(6,$forward));
}

		//加载jq库
		echo cls_phpToJavascript::loadJQuery();
	?>
<script type="text/javascript">
var zd_day = <?php echo empty($curuser->info['freezds'])?0:$curuser->info['freezds'];?>;
var cash = <?php echo empty($curuser->info['currency0'])?0:$curuser->info['currency0'];?>;
var pay_each_day = <?php echo empty($rules['price'])?0:$rules['price'];?>;
$("#zdingday").keyup(function(){
	var pay_instr = '';
	if(isNaN($("#zdingday").val())){
		pay_instr = "<font color=\"#FF0000\">房源置顶天数应输入数字</font>";
	}else{		
		val = $("#zdingday").val();
		if(zd_day - val >=0){
			pay_instr = '应扣置顶天数' + val + '天，置顶天数余额为' + (zd_day - val) + '天';
		}else{			
			shou_pay = (val - zd_day) * pay_each_day;//还需要付的现金数额
			pay_instr = '应扣置顶天数' + zd_day + '天，置顶天数余额为0,应扣现金' + shou_pay + '元，剩余现金' + (cash - shou_pay) + '元。';
			if(cash - shou_pay < 0 ){			
				pay_instr = pay_instr  + "<br/><font color=\"#FF0000\">费用不足以支付本次操作，请充值或者重新操作。</font>";
			}
		}
	}

	$("#payment_instr").html(pay_instr);
})
	
</script>
