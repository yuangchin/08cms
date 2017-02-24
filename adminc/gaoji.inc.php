<?php
!defined('M_COM') && exit('No Permission');
$ngtid = 14;$nchid = 2;$nugid = 8;
$mchid = $curuser->info['mchid'];
if($mchid != $nchid) cls_message::show('您需要先注册为经纪人会员才可以升级。');
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
if(!($rules = @$exconfigs['gaoji'])) cls_message::show('系统没有定义经纪人升级规则。');
if(!submitcheck('bsubmit')){
	tabheader('经纪人升级','gtexchagne',"?action=$action");
	trbasic('您目前所属组','',($curuser->info["grouptype$ngtid"] == $nugid ? '高级经纪人' : '普通经纪人').' &nbsp;到期时间：'.($curuser->info["grouptype{$ngtid}date"] ? date('Y-m-d H:i',$curuser->info["grouptype{$ngtid}date"]) : '永久'),'');
	trbasic('房源置顶余额','',$curuser->info['freezds'].' 天','');
	trbasic('预约刷新余额','',$curuser->info['freeyys'].' 条','');
	trbasic('现金帐户余额','',$curuser->info['currency0']." 元 &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
	if(($curuser->info["grouptype$ngtid"] == $nugid) && !$curuser->info["grouptype{$ngtid}date"]){
		trbasic('升级说明','','您是永久的高级经纪人，不需要升级。','');
		tabfooter();
	}else{
		$str = '';foreach($rules as $k => $v) $v['available'] && $str .= "<input class=\"radio\" type=\"radio\" name=\"exchangekey\" value=\"$k\" checked> &nbsp;$v[title] &nbsp;价格：<b>$v[price]</b> 元 &nbsp;有效期：<b>$v[month]</b> 个月 &nbsp;赠送 <b>$v[zds]</b> 天房源置顶 &nbsp;赠送 <b>$v[yys]</b> 条房源预约刷新条数<br>";
		trbasic('升级或续费','',"<br>$str<br>",'');
		tabfooter('bsubmit');
		$mgdes = @$exconfigs['upmemberhelp'][$ngtid];
		$mgdes['des'] = implode('<p>',explode("\r\n",$mgdes['des']));
		empty($mgdes) ? '' : m_guide($mgdes['des'],'fix');
	}
}else{
	$exchangekey = max(0,intval($exchangekey));
	if(!($rule = @$rules[$exchangekey])) cls_message::show('请指定升级为哪种高级经纪人。',M_REFERER);
	if($curuser->info['currency0'] < $rule['price']) cls_message::show('您的现金帐户余额不足，请充值。',M_REFERER);
	$curuser->updatefield('freezds',$curuser->info['freezds'] + $rule['zds']);
	$curuser->updatefield('freeyys',$curuser->info['freeyys'] + $rule['yys']);
	$curuser->updatefield("grouptype{$ngtid}date",($curuser->info["grouptype$ngtid"] == $nugid ? $curuser->info["grouptype{$ngtid}date"] : $timestamp) + $rule['month'] * 30 * 86400);
	$curuser->updatefield("grouptype$ngtid",$nugid);
	$curuser->updatecrids(array(0 => -$rule['price']),1,'经纪人会员升级。');
	cls_message::show('经纪人升级成功。',M_REFERER);

}
?>
