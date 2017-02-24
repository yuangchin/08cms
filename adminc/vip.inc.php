<?php
!defined('M_COM') && exit('No Permission');
$type = empty($type) ? 'vipgs' : $type;
if($type == 'vipgs'){
	$ngtid = 31;$nchid = 11;$nugid = 102;
	$mchid = $curuser->info['mchid'];
	if($mchid != $nchid) cls_message::show('您需要先注册为装修公司会员才可以升级。');
	$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
	if(!($rules = @$exconfigs[$type])) cls_message::show('系统没有定义装修公司升级规则。');
	if(!submitcheck('bsubmit')){
		tabheader('装修公司升级','gtexchagne',"?action=$action&type=$type");
		trbasic('您目前所属组','',($curuser->info["grouptype$ngtid"] == $nugid ? 'VIP公司' : '普通公司').' &nbsp;到期时间：'.($curuser->info["grouptype{$ngtid}date"] ? date('Y-m-d H:i',$curuser->info["grouptype{$ngtid}date"]) : '永久'),'');
		trbasic('刷新次数余额','',$curuser->info['freerefnum'].' 次','');
		trbasic('现金帐户余额','',$curuser->info['currency0']."元 &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
		if(($curuser->info["grouptype$ngtid"] == $nugid) && !$curuser->info["grouptype{$ngtid}date"]){
			trbasic('升级说明','','您是永久的VIP公司，不需要升级。','');
			tabfooter();
		}else{
			$str = '';foreach($rules as $k => $v) $v['available'] && $str .= "<input class=\"radio\" type=\"radio\" name=\"exchangekey\" value=\"$k\" checked> &nbsp;$v[title] &nbsp;价格：<b>$v[price]</b> 元 &nbsp;有效期：<b>$v[month]</b> 个月 &nbsp;赠送 <b>$v[refnum]</b> 次刷新数量<br>";
			trbasic('升级或续费','',"<br>$str<br>",'');
			tabfooter('bsubmit');
			$mgdes = @$exconfigs['upmemberhelp'][$ngtid];
			$mgdes['des'] = implode('<p>',explode("\r\n",$mgdes['des']));
			empty($mgdes) ? '' : m_guide($mgdes['des'],'fix');
		}
	}else{
		$exchangekey = max(0,intval($exchangekey));
		if(!($rule = @$rules[$exchangekey])) cls_message::show('请指定升级为哪种VIP公司。',M_REFERER);
		if($curuser->info['currency0'] < $rule['price']) cls_message::show('您的现金帐户余额不足，请充值。',M_REFERER);
		$curuser->updatefield('freerefnum',$curuser->info['freerefnum'] + $rule['refnum']);
		$curuser->updatefield("grouptype{$ngtid}date",($curuser->info["grouptype$ngtid"] == $nugid ? $curuser->info["grouptype{$ngtid}date"] : $timestamp) + $rule['month'] * 30 * 86400);
		$curuser->updatefield("grouptype$ngtid",$nugid);
		$curuser->updatecrids(array(0 => -$rule['price']),1,'装修公司会员升级。');
		cls_message::show('VIP公司升级成功。',M_REFERER);
	
	}
}elseif($type == 'vipsj'){
	$ngtid = 32;$nchid = 12;$nugid = 104;
	$mchid = $curuser->info['mchid'];
	if($mchid != $nchid) cls_message::show('您需要先注册为品牌商家会员才可以升级。');
	$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
	if(!($rules = @$exconfigs[$type])) cls_message::show('系统没有定义品牌商家升级规则。');
	if(!submitcheck('bsubmit')){
		tabheader('品牌商家升级','gtexchagne',"?action=$action&type=$type");
		trbasic('您目前所属组','',($curuser->info["grouptype$ngtid"] == $nugid ? 'VIP商家' : '普通商家').' &nbsp;到期时间：'.($curuser->info["grouptype{$ngtid}date"] ? date('Y-m-d H:i',$curuser->info["grouptype{$ngtid}date"]) : '永久'),'');
		trbasic('刷新次数余额','',$curuser->info['freerefnum'].' 次','');
		trbasic('现金帐户余额','',$curuser->info['currency0']."元 &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
		if(($curuser->info["grouptype$ngtid"] == $nugid) && !$curuser->info["grouptype{$ngtid}date"]){
			trbasic('升级说明','','您是永久的VIP商家，不需要升级。','');
			tabfooter();
		}else{
			$str = '';foreach($rules as $k => $v) $v['available'] && $str .= "<input class=\"radio\" type=\"radio\" name=\"exchangekey\" value=\"$k\" checked> &nbsp;$v[title] &nbsp;价格：<b>$v[price]</b> 元 &nbsp;有效期：<b>$v[month]</b> 个月 &nbsp;赠送 <b>$v[refnum]</b> 次刷新数量<br>";
			trbasic('升级或续费','',"<br>$str<br>",'');
			tabfooter('bsubmit');
			$mgdes = @$exconfigs['upmemberhelp'][$ngtid];
			$mgdes['des'] = implode('<p>',explode("\r\n",$mgdes['des']));
			empty($mgdes) ? '' : m_guide($mgdes['des'],'fix');
		}
	}else{
		$exchangekey = max(0,intval($exchangekey));
		if(!($rule = @$rules[$exchangekey])) cls_message::show('请指定升级为哪种VIP商家。',M_REFERER);
		if($curuser->info['currency0'] < $rule['price']) cls_message::show('您的现金帐户余额不足，请充值。',M_REFERER);
		$curuser->updatefield('freerefnum',$curuser->info['freerefnum'] + $rule['refnum']);
		$curuser->updatefield("grouptype{$ngtid}date",($curuser->info["grouptype$ngtid"] == $nugid ? $curuser->info["grouptype{$ngtid}date"] : $timestamp) + $rule['month'] * 30 * 86400);
		$curuser->updatefield("grouptype$ngtid",$nugid);
		$curuser->updatecrids(array(0 => -$rule['price']),1,'品牌商家会员升级。');
		cls_message::show('VIP商家升级成功。',M_REFERER);
	
	}	
}
?>
