<?php
!defined('M_COM') && exit('No Permission');
$sms = new cls_sms();
$mctypes = cls_cache::Read('mctypes');
$mchid = $curuser->info['mchid'];
$mfields = cls_cache::Read('mfields',$mchid);
isset($mctid ) && $mctid = (int)$mctid;
if(empty($deal)){
	$itemstr = '';
	$sn = 1;
	foreach($mctypes as $k => $v){
		if($v['available'] && in_array($mchid,explode(',',$v['mchids'])) && isset($mfields[$v['field']])){
			$statestr = '-';
			if($curuser->info["mctid$k"]){
				$statestr = '认证已审';
			}elseif($db->result_one("SELECT COUNT(*) FROM {$tblprefix}mcerts WHERE mctid='$k' AND mid='$memberid' AND checkdate=0")) $statestr = "申请中 &nbsp;<a href=\"?action=$action&deal=cancel&mctid=$k\">>>取消</a>";
			$itemstr .= "<tr>\n".
				"<td class=\"item\" width=\"40\">$sn</td>\n".
				"<td class=\"item2\">$v[cname]</td>\n".
				"<td class=\"item\"><img src=\"$v[icon]\" border=\"0\" onload=\"if(this.height>20) {this.resized=true; this.height=20;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\"></td>\n".
				"<td class=\"item2\">$v[remark]</td>\n".
				"<td class=\"item2\">$statestr</td>\n".
				"<td class=\"item\" width=\"40\"><a title='申请/解审/查看详情' href=\"?action=$action&deal=detail&mctid=$k\" onclick=\"return floatwin('open_mcerts',this)\">操作</a></td>\n".
				"</tr>\n";
			$sn ++;
		}
	}
	tabheader('我的认证管理','','',10);
	if($itemstr){
		trcategory(array('序号',array('类型','left'),'图标',array('备注','left'),array('认证状态','left'),'详情'));
		echo $itemstr;
	}else echo "<tr><td class=\"item\" colspan=\"10\"><br>没有您需要认证的项目。<br><br></td></tr>";
	tabfooter();
}elseif($deal == 'detail'){
	if(empty($mctid) || !($mctype = @$mctypes[$mctid])) cls_message::show('请指定认证类型。');
	if(!$mctype['available'] || !in_array($mchid,explode(',',$mctype['mchids'])) || !isset($mfields[$mctype['field']])) cls_message::show('无效的认证类型。');
	$curuser->detail_data();
	$mcfield = &$mfields[$mctype['field']];
	$flag = 0;$flagstr = '请提交认证申请。';
	if($curuser->info["mctid$mctid"]){
		$flag = 1;$flagstr = '认证已通过。';
	}elseif($oldrow = $db->fetch_one("SELECT * FROM {$tblprefix}mcerts WHERE mctid='$mctid' AND mid='$memberid' AND checkdate=0")){
		$flag = 2;$flagstr = '认证正在申请中，请等待管理员审核。';
	}
	if(!submitcheck('bsubmit')&&!submitcheck('buncheck')){
		tabheader("会员认证 - $mctype[cname]", 'memcert_need', "?action=$action&deal=$deal&mctid=$mctid&t=$timestamp",2,1,1);
		trbasic('认证状态','',$flagstr,''); $jstag = 'script'; 
		echo "<$jstag type='text/javascript' src='{$cms_abs}include/sms/cer_code.js'></$jstag>";
		$a_field = new cls_field;
		$a_field->init($mcfield,$flag == 2 ? $oldrow['content'] : $curuser->info[$mctype['field']]);

		if(!$flag && $mctype['mode']==1){ //未认证
			if(!$sms->smsEnable($mctid)){ //关闭-手机短信接口
				$msgcode = cls_string::Random(6, 1);
				msetcookie('08cms_msgcode', authcode("$timestamp\t$msgcode", 'ENCODE'));
				trhidden('msgcode',$msgcode);
			}else{
				$varname = "fmdata[$mctype[field]]"; 
				$inputstr = '<input type="text" size="10" id="msgcode" name="msgcode" rule="text" must="1" type="int" min="6" max="6" offset="1" rev="确认码"/>&nbsp;&nbsp;';
				$a_field->field['guide'] .=<<< EOT
<tr><td width="25%" class="item1"><b>确认码</b></td>
<td class="item2">$inputstr
<a id="tel_code" href="javascript:" onclick="sendCerCode('$varname','$mctid','tel_code');">【点击获得确认码】</a>
<a id="tel_code_rep" style="color:#CCC; display:none"><span id="tel_code_rep_in">60</span>秒后重新获取</a> 
<span id="alert_msgcode" style="color:red"></span>
<input name="is_check_code" type="hidden" value="1" />
</td></tr>
EOT;
			}
		}
		if($flag==1 && !$mctype['mode']){ //直接显示图片
			$val = view_checkurl($curuser->info[$mctype['field']]);
			$val = '<a href="'.$val.'" target="_blank"><img src="'.$val.'" width="240" /></a>';
			echo "<tr><td width='150px' class='item1'><b>".$mfields[$mctype['field']]['cname']."</b></td><td>$val</td></tr>";
		}else{
			$a_field->trfield('fmdata');
		}
		if($flag==1&&empty($mctypes[$mctid]['autocheck'])&&!empty($mctypes[$mctid]['uncheck'])){ 
			tabfooter('buncheck','解审');
		}elseif($flag==2){
			tabfooter('buncheck','取消申请');
		}elseif(empty($flag)){
			tabfooter('bsubmit');
		}
		if(@$mctype['mode'] && @$mctype['isunique']){
			$paras = "&mctid=$mctid&mchid=".$curuser->info['mchid']."&oldval=".@$curuser->info[$mctype['field']]."&method=1&val=%1";
			echo "<$jstag type='text/javascript'>var ctel = \$id('fmdata[$mctype[field]]');</$jstag>";
			$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC("ajax=checkUnique$paras");
			echo _08_HTML::AjaxCheckInput("fmdata[$mctype[field]]", $ajaxURL);
			#echo _08_HTML::AjaxCheckInput("fmdata[$mctype[field]]","{$cms_abs}tools/ajax.php?action=checkUnique$paras");
		}
		//tabfooter($flag ? 'buncheck' : 'bsubmit');
	}elseif(submitcheck('buncheck')){
		#解审 
		$curuser->updatefield("mctid$mctid",0); #$au->updatefield($mctype['field'],'',$a_field->field['tbl']);
		if($mctype['award'])$curuser->updatecrids(array($mctype['crid'] => -$mctype['award']),0,"$mctype[cname] 扣分");
		$curuser->updatedb();
		$db->query("DELETE FROM {$tblprefix}mcerts WHERE mctid='$mctid' AND mid='$memberid'");
		cls_message::show('解审认证完成。',axaction(6,"?action=$action"));
		//echo "uncheck!"; die('');
	}else{
		$c_upload = cls_upload::OneInstance();	
		$a_field = new cls_field;
		$a_field->init($mcfield,$flag == 2 ? $oldrow[$mctype['field']] : $curuser->info[$mctype['field']]);
		$content = $a_field->deal('fmdata','cls_message::show',M_REFERER);
		$msgcode = empty($msgcode) ? '' : trim($msgcode);
		$checkdate = 0;
		if(!empty($is_check_code)){
			@list($inittime, $initcode) = maddslashes(explode("\t", authcode($m_cookie['08cms_msgcode'],'DECODE')),1);
			if($timestamp - $inittime > 1800 || $initcode != $msgcode) cls_message::show('手机确认码有误', M_REFERER);
			$mctype['autocheck'] = 1; //手机短信认证,强制审核
		}
		$db->query("INSERT INTO {$tblprefix}mcerts SET mid='$memberid',mname='{$curuser->info['mname']}',mctid='$mctid',createdate='$timestamp',checkdate='$checkdate',content='$content',msgcode='$msgcode'");
		if($mcid = $db->insert_id()){
			$c_upload->closure(1,$mcid,"mcerts");
			$c_upload->saveuptotal(1);
			if($mctype['autocheck']){
				$curuser->updatefield($mctype['field'],$content,$a_field->field['tbl']);
				$curuser->updatefield("mctid$mctid",$mctid); //直接审核
				if($mctype['award']) $curuser->updatecrids(array($mctype['crid'] => $mctype['award']),0,"$mctype[cname] 加分");
				$curuser->updatedb();
				$db->query("UPDATE {$tblprefix}mcerts SET checkdate='$timestamp',content='$content' WHERE mcid='$mcid'");
				cls_message::show('认证成功。',axaction(6,"?action=$action"));
			}else{
				cls_message::show('认证申请成功。',axaction(6,"?action=$action"));
			}
		}else{
			$c_upload->closure(1);
			cls_message::show('认证申请不成功。',axaction(6,"?action=$action"));
		}
	}
}elseif($deal == 'cancel'){
	if(empty($mctid)) cls_message::show('您要删除的申请记录不存在。');
	if($db->query("DELETE FROM {$tblprefix}mcerts WHERE mctid='$mctid' AND mid=$memberid AND checkdate=0")){
			cls_message::show('认证申请删除成功', M_REFERER);
	}else cls_message::show('认证申请删除失败', M_REFERER);
}
?>