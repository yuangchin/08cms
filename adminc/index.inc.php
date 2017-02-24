<?php
!defined('M_COM') && exit('No Permission');
foreach(array('grouptypes','currencys','mchannels','commus','mcommus') as $k) $$k = cls_cache::Read($k);
$curuser->sub_data();

$usergroupstr = '';
foreach($grouptypes as $k => $v){
	if($curuser->info['grouptype'.$k]){
		$usergroups = cls_cache::Read('usergroups',$k);
		$usergroupstr .=  '<font class="cBlue">'.$usergroups[$curuser->info['grouptype'.$k]]['cname'].'</font> &nbsp;';
	}
}
$repugradestr = '您的信用等级是';
$currencystr='现金帐户'.' : <font class="cRed">'.$curuser->info['currency0'].'</font><font class="cBlue"> '.'元'.'</font>&nbsp; ';
foreach($currencys as $v){
	$tmp = $curuser->info['currency'.$v['crid']];
	$currencystr .= " $v[cname] : <font class=\"cRed\">$tmp</font><font class=\"cBlue\"> $v[unit]</font>&nbsp; ";
}
$friendnum = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}mfriends WHERE mid='$memberid' AND checked=1");
$friendstr = '';
$query = $db->query("SELECT * FROM {$tblprefix}mfriends WHERE mid='$memberid' AND checked=1 ORDER BY cid DESC LIMIT 0,10");
while($row = $db->fetch_array($query)){
	$friendstr .= "<li><a href=\"{$mspaceurl}index.php?mid=$row[fromid]\" target=\"_blank\">$row[fromname]</a></li>";
}
$msgstr = '';
$pmstat = pmstat();
$msgstr .= '<tr><td>'.'收到的'.'短信'." : <font class=\"cRed\">$pmstat[1]</font></td>";
$msgstr .= '<td>'.'未读'." : <font class=\"cRed\">$pmstat[0]</font></td></tr>";
$query = $db->query("SELECT cuid,COUNT(cid) AS cids,SUM(uread) AS ureads FROM {$tblprefix}replys cu INNER JOIN {$tblprefix}archives a ON cu.aid=a.aid WHERE a.mid='$memberid' GROUP BY cu.cuid");
while($row = $db->fetch_array($query)){
	$msgstr .= '<tr><td>'.'收到的'.@$commus[$row['cuid']]['cname']." : <font class=\"cRed\">$row[cids]</font></td>";
	$msgstr .= '<td>'.'未读'." : <font class=\"cRed\">".($row['cids'] - $row['ureads'])."</font></td></tr>";
}
$query = $db->query("SELECT cuid,COUNT(cid) AS cids,SUM(uread) AS ureads FROM {$tblprefix}mreplys WHERE mid='$memberid' GROUP BY cuid");
while($row = $db->fetch_array($query)){
	$msgstr .= '<tr><td>'.'收到的'.@$mcommus[$row['cuid']]['cname']." : <font class=\"cRed\">$row[cids]</font></td>";
	$msgstr .= '<td>'.'未读'." : <font class=\"cRed\">".($row['cids'] - $row['ureads'])."</font></td></tr>";
}
$statearr = array('0' => '等待商家确认','1' => '等待付款','2' => '等待发货','3' => '已发货','-1' => '完成','-2' => '取消');
$query = $db->query("SELECT state,COUNT(oid) AS orders FROM {$tblprefix}orders WHERE tomid='$memberid' GROUP BY state");
while($row = $db->fetch_array($query)){
	$msgstr .= '<tr><td>'.$statearr[$row['state']].'的订单'."</td><td><font class=\"cRed\">$row[orders]</font></td></tr>";
}
?>
		<div class="index_con">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top">
			<div class="w100border left">
				<dl class="userinfo">
					<dt>您好，<font class="red"><?=$curuser->info['mname']?></font> 欢迎登陆！<div class="right lineheight200 cGray"> <?='上次登陆IP'?>:<?=$curuser->info['lastip']?> &nbsp; <?='上次登陆时间'?>:<?=date('Y-m-d H:i',$curuser->info['lastvisit'])?></div></dt>							
				</dl>
				<div class="blank6"></div>
				<ul class="userinfo">
					<dl class="info2 left txtleft lineheight300">
						<dd><?='您目前的身份是：'.$usergroupstr?></dd>
						<dd><?='您的会员类型是：<font class="cBlue">'.$mchannels[$curuser->info['mchid']]['cname'].'</font>'?></dd>
						<dd><?=$repugradestr?></dd>
						<dd><?=$currencystr?><?php if(($commu = cls_cache::Read('commu',9)) && !empty($commu['available'])){?><br /><div align="right"><b class="spreadlink" id="get_spread" onclick="return showInfo(this.id,'?action=spread',450,108)"><?='邀请好友,获得积分'?></b></div><?php }?></dd>
					</dl>
					<div class="blank18"></div>
				</ul>
				<ul class="userinfo">
					<h3 class="infotitle"><img src="<?=MC_ROOTURL.'images/message1.gif'?>" width="22" height="18" align="absmiddle" /> <?='消息中心'?><font style="font-weight:100;">>><a href="?action=pmsend"><?='发送短信'?></a>&nbsp;</font></h3>
					<table width="100%" border="0" cellspacing="0" cellpadding="0"><?=$msgstr?></table>
				</ul>
				<div class="blank6"></div>
			</div>
					</td>
					<td valign="top" width="265">
			<div class="info_Statistics w100border txtleft">
				<ul class="userinfo">
					<h1 class="infotitle"><?='信息统计'?></h1>
					<div class="blank6"></div>
					<li><?='会员添加文档数量 '.$curuser->info['archives']?></li>
					<li><?='会员已审文档数量 '.$curuser->info['checks']?></li> 
					<li><?='会员评论数 '.$curuser->info['comments']?></li>
					<li><?='会员文档订阅数量 '.$curuser->info['subscribes']?></li>
					<li><?='会员附件订阅数量 ' . $curuser->info['fsubscribes']?></li>
					<li><?="会员已上传附件 {$curuser->info['uptotal']} (K)"?></li>
					<li><?="会员已下载附件 {$curuser->info['downtotal']} (K)"?></li>
				</ul>
				<ul class="userinfo">
					<h1 class="infotitle"><?='好友列表'?>(<?=$friendnum?>)</h1>
					<div class="blank6"></div>
					<?=$friendstr?>
				</ul>
				<div class="blank9"></div>
			</div>
					</td>
				</tr>
			</table>
		</div>

	