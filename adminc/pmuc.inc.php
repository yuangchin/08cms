<?php
(defined('M_COM') && $enable_uc) || exit('No Permission');
/*
只能发送单个信息，发送多个失败，可能用用户名不能发多个，而只能用id
*/
$page = isset($page) ? $page : 1;
$page = max(1, intval($page));

cls_ucenter::init();
list($uid,$username) = uc_get_user($curuser->info['mname']);
$boxs=array('newpm', 'privatepm', 'systempm', 'announcepm');
$boxl=array('未读短信', '会员短信', '系统短信', 'UC短信');//添加缓存机会
$new = uc_pm_checknew($uid, 4);
$new['privatepm'] = $new['newprivatepm'];
$new['systempm'] = $new['newpm'] - $new['privatepm'];
$action=='pmbox' && $box = !empty($box) && in_array($box, $boxs) ? $box : ($new['newpm'] ? 'newpm' : 'privatepm');
$l = count($boxs);
$urlsarr = array('pmsend' => array('发送短信', '?action=pmsend'));
for($i = 0; $i < $l; $i++)$urlsarr[$boxs[$i]] = array($boxl[$i].($new[$boxs[$i]]?('('.$new[$boxs[$i]].')'):''), "?action=pmbox&box=$boxs[$i]&page=$page");
url_nav($urlsarr,'pmbox'==$action ? $box : 'pmsend',6);

if($action=='pmsend'){
	if(!submitcheck('bpmsend')){//发送框
		tabheader("发送短信",'pmsend',"?action=pmsend&box=$box&page=$page",2,0,1);
		trbasic('标题','pmnew[title]','','text', array('validate' => makesubmitstr('pmnew[title]',1,0,0,80),'w'=>50));
		trbasic('发送至','pmnew[tonames]',empty($tonames) ? '' : $tonames,'text', array('guide' => '用逗号分隔多个会员名称','validate' => makesubmitstr('pmnew[tonames]',1,0,0,100),'w'=>50));
		trbasic('内容','pmnew[content]','','textarea', array('w' => 500,'h' => 300,'validate' => makesubmitstr('pmnew[content]',1,0,0,1000)));
		tr_regcode('pm');
		tabfooter('bpmsend');
	}else{//发送短信
		if(!regcode_pass('pm',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误',M_REFERER);
		$pmnew['title'] = trim($pmnew['title']);
		$pmnew['tonames'] = trim($pmnew['tonames']);
		$pmnew['content'] = trim($pmnew['content']);
		if(empty($pmnew['content']) || empty($pmnew['tonames'])){
			cls_message::show('短信内容不完整',M_REFERER);
		}
		$tos=array_filter(explode(',',$pmnew['tonames']));$count=0;
		$pmnew['title'] = $pmnew['title'] ? $pmnew['title'] : ($pmnew['content'] ? $pmnew['content'] : '');
		foreach($tos as $to)if(uc_pm_send($uid,$to,$pmnew['title'],$pmnew['content'],1,0,1))$count++;
		$count ? cls_message::show($count.'短信发送成功',"?action=pmbox&box=$box&page=$page") : cls_message::show('短信发送错误',M_REFERER);
	}
}elseif(empty($fid)&&empty($pmid)){
	if(!submitcheck('bpmbox')){//各收件箱
			$ucpm = uc_pm_list($uid, $page, $mrowpp, 'inbox', $box, 30);
			tabheader("短信列表",'pmsedit',"?action=pmbox&box=$box&page=$page",6);
			trcategory(array($box=='announcepm'?'':("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"checkall(this.form, '', 'chkall')\">".'删?'),array('标题','left'),'发信人','状态','发送日期','内容'));
			if($ucpm['data']){
				foreach($ucpm['data'] as $pm){
					echo "<tr title=\"".mhtmlspecialchars($pm['message'])."\">\n<td align=\"left\" width=\"40\">".($box=='announcepm'?'':"<input class=\"checkbox\" type=\"checkbox\" name=\"".($pm['msgformid']?"fids[$pm[msgformid]]\" value=\"$pm[msgform]":"pmids[$pm[pmid]]\" value=\"$pm[pmid]").'">')."</td>\n".
						"<td class=\"item2\">".mhtmlspecialchars($pm['subject'])."</td>\n".
						"<td align=\"center\" width=\"120\">".($pm['msgfromid'] ? $pm['msgfrom'] : '系统短信')."</td>\n".
						"<td align=\"center\" width=\"40\">".($box=='announcepm'?'-':($pm['new'] ? '未读' : '已读'))."</td>\n".
						"<td align=\"center\" width=\"80\">".date($dateformat, $pm['dateline'])."</td>\n".
						"<td align=\"center\" width=\"40\"><a href=\"?action=pmbox&box=$box&page=$page&".($pm['msgfromid']?"fid=$pm[msgfromid]":"pmid=$pm[pmid]")."\">".'查看'."</a></td></tr>\n";
				}
			}else{
				echo '<tr class="item2" height="50"><td align="center" colspan="6">'.'没有短信'.'</td></tr>';
			}
			echo multi($ucpm['count'],$mrowpp,$page,"?action=pmbox");
			$box=='announcepm'?tabfooter():tabfooter('bpmbox','删除');
	}else{//删除
		empty($fids) && empty($pmids) && cls_message::show('请选择删除项目',"?action=pmbox&box=$box&page=$page");
		is_array($fids) || $fids=array($fids);
		is_array($pmids) || $pmids=array($pmids);
		if($fids) {
			uc_pm_deleteuser($uid, $fids);
		}
		if($pmids) {
			uc_pm_delete($uid, 'inbox', $pmids);
		}
		cls_message::show('短信息删除操作完成',"?action=pmbox&box=$box&page=$page");
	}
}else{//阅读短信
	$days = array(1=>'今天',3=>'最近三天',4=>'本周',5=>'所有');
	$day = isset($day) && array_key_exists($day,$days) ? $day : 3;

	$ucpm = empty($fid) ? uc_pm_view($uid, $pmid, 0, $day) : uc_pm_view($uid, '', $fid, $day);//$ucpm=uc_pm_view($uid, $pmid, 0, 3);
//	exit(var_export($ucpm));
	empty($ucpm) && cls_message::show('没有新短信');
	$fuser = '';
	foreach($ucpm as $pm)if($pm['msgfrom']!=$curuser->info['mname']){$fuser=$pm['msgfrom'];break;}

	if($fuser){
		$str='';
		foreach($days as $k => $v)$str.='&nbsp;'.($day==$k?$v:"<a href=\"?action=pmbox&box=$box&page=$page&fid=$fid&day=$k\">$v</a>");
		tabheader("与 $fuser 的短消息记录：$str".($fuser ? "&nbsp;&nbsp;>><a href=\"?action=pmsend&box=$box&page=$page&tonames=".rawurlencode($pm['msgfrom'])."\">".'回复'."</a>" : ''));
		tabfooter();
	}

	tabheader('内容');
	$pm=end($ucpm);
	if($fuser==$pm['msgfrom']){
		array_pop($ucpm);
		$fuser ? trbasic('发信人','',($pm['new']?'[<b style="color:red">new</b>]':'').$fuser,'') : trbasic('标题','',($pm['msgtoid'] && $pm['new']?'[<b style="color:red">new</b>]':'').($pm['subject'] ? $pm['subject'] : '系统短信'),'');
		trbasic('发送时间','',date("$dateformat $timeformat",$pm['dateline']),'');
		$fuser && trbasic('标题','',mhtmlspecialchars($pm['subject']),'');
		trbasic('内容','',mnl2br(mhtmlspecialchars($pm['message'])),'');
	}
	if(!empty($ucpm)){
		echo '<tr><td class="item2" colspan="2"><b>'.'历史短信'.'</b></td></tr>';
		foreach($ucpm as $pm){
			echo '<tr><td class="item2" colspan="2">'.($fuser==$pm['msgfrom']?(($pm['new']?'[<b style="color:red">new</b>]':'').("$pm[msgfrom] 在 " . date("$dateformat $timeformat",$pm['dateline']) . ' 说：')):('您在 ' . date("$dateformat $timeformat",$pm['dateline']) . ' 说：')).'</td></tr>'.
				 '<tr><td class="item2" colspan="2">'.($pm['subject'] ? '<b>'.mhtmlspecialchars($pm['subject']).'</b><br />' : '').mnl2br(mhtmlspecialchars($pm['message'])).'</td></tr>';
		}
	}
	tabfooter();
	echo "<input class=\"button\" type=\"submit\" name=\"\" value=\"返回\" onclick=\"redirect('?action=pmbox&box=$box&page=$page')\">\n";
}
?>
