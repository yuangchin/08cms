<?php
!defined('M_COM') && exit('No Permission');
$currencys = cls_cache::Read('currencys');
$grouptypes = cls_cache::Read('grouptypes');
$curuser->detail_data();
tabheader('我的基本情况');
trbasic('审核状态','',$curuser->info['checked'] ? '已审': '用户等待审核','');
trbasic('注册时间','',$curuser->info['regdate'] ? date("$dateformat   $timeformat",$curuser->info['regdate']) : '','');
trbasic('注册IP','',$curuser->info['regip'] ? $curuser->info['regip'] : '-','');
trbasic('上次登陆时间','',$curuser->info['lastvisit'] ? date("$dateformat   $timeformat",$curuser->info['lastvisit']) : '','');
trbasic('上次激活时间','',$curuser->info['lastactive'] ? date("$dateformat   $timeformat",$curuser->info['lastactive']) : '','');
trbasic('上次登陆IP','',$curuser->info['lastip'] ? $curuser->info['lastip'] : '-','');
trbasic('空间浏览数','',$curuser->info['msclicks'],'');
tabfooter();
tabheader('我参与的内容');
trbasic('文档数量','',$curuser->info['archives'],'');
trbasic('已审文档数量','',$curuser->info['checks'],'');
trbasic('已上传附件','',sizecount(1024 * $curuser->info['uptotal']),'');
$capacity = $curuser->upload_capacity();
trbasic('上传空间余量','',$capacity == -1 ? '不限量' : sizecount(1024 * $capacity),'');
trbasic('已下载附件','',sizecount(1024 * $curuser->info['downtotal']),'');
tabfooter();
tabheader('我的积分');
trbasic('现金帐户','',$curuser->info['currency0'].'元','');
foreach($currencys as $crid => $currency){
	trbasic($currency['cname'],'',$curuser->info['currency'.$crid].$currency['unit'],'');
}
tabfooter();
tabheader('我的会员组','','',4);
foreach($grouptypes as $k => $v){
	if($curuser->info["grouptype$k"]){
		$usergroups = cls_cache::Read('usergroups',$k);
		$date = !@$curuser->info["grouptype{$k}date"] ? '-' : date('Y-m-d',@$curuser->info["grouptype{$k}date"]);
		echo "<tr>\n".
			"<td width=\"15%\" class=\"item1\"><b>$v[cname]</b></td>\n".
			"<td width=\"35%\" class=\"item2\">".($usergroups[$curuser->info["grouptype$k"]]['cname'])."</td>\n".
			"<td width=\"15%\" class=\"item1\"><b>结束日期</b></td>\n".
			"<td width=\"35%\" class=\"item2\">$date</td>\n".
			"</tr>";
	}
}
tabfooter();
?>