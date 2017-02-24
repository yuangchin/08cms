<?php
!defined('M_COM') && exit('No Permission');
backnav('pm','box');
$page = isset($page) ? $page : 1;
$page = max(1, intval($page));
$pmid = intval(@$pmid);
if(empty($pmid)){
	if(!submitcheck('bpmbox')){
		$wheresql = "WHERE toid=$memberid";
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}pms $wheresql ORDER BY pmid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
			$pagetmp--;
		}while(!$db->num_rows($query) && $pagetmp);
		$itempm = '';
		while($pm = $db->fetch_array($query)){
			$pmid = $pm['pmid'];
			$pm['viewed'] = empty($pm['viewed']) ? 'Y' : '-';
			$pm['pmdate'] = date($dateformat,$pm['pmdate']);
			$itempm .= "<tr><td align=\"center\" class=\"item1\" width=\"40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$pmid]\" value=\"$pmid\">\n".
				"<td class=\"item2\">".mhtmlspecialchars($pm['title'])."</td>\n".
				"<td class=\"item\" width=\"120\">$pm[fromuser]</td>\n".
				"<td class=\"item\" width=\"40\">$pm[viewed]</td>\n".
				"<td class=\"item\" width=\"80\">$pm[pmdate]</td>\n".
				"<td class=\"item\" width=\"40\"><a href=\"?action=pmbox&pmid=$pmid&page=$page\">".'查看'."</a></td></tr>\n";
		}
		$pmcount = $db->result_one("SELECT count(*) FROM {$tblprefix}pms $wheresql");
		$multi = multi($pmcount,$mrowpp,$page,"?action=pmbox");
	
		tabheader("短信列表",'pmsedit',"?action=pmbox&page=$page",6);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"checkall(this.form, 'delete', 'chkall')\">".'删?','标题','发信人','未读','发送日期','内容'));
		echo $itempm;
		tabfooter();
		echo $multi;
		echo "<input class=\"button\" type=\"submit\" name=\"bpmbox\" value=\"删除\">".
			"</form>\n";
		m_guide('sms_insite','fix');
	
	}else{
		empty($delete) && cls_message::show('请选择删除项目',"?action=pmbox&page=$page");
		$pmidstr = "pmid ".multi_str($delete);
		$db->query("DELETE FROM {$tblprefix}pms WHERE $pmidstr",'UNBUFFERED');
        cls_WindID_Send::getInstance()->deleteMessages($memberid, $delete); #同步删除WINDID私信
		cls_message::show('短信删除操作完成',"?action=pmbox&page=$page");
	}
}else{
	$pm = $db->fetch_one("SELECT * FROM {$tblprefix}pms WHERE toid=$memberid AND pmid='$pmid'");
	empty($pm) && cls_message::show('请指定短信',"?action=pmbox&page=$page");
	tabheader('短信内容设置');
	trbasic('标题','',mhtmlspecialchars($pm['title']),'');
	trbasic('发信人','',$pm['fromuser']."&nbsp;  &nbsp; &nbsp; &nbsp;>><a href=\"?action=pmsend&tonames=".rawurlencode($pm['fromuser'])."\">回复</a>",'');
	trbasic('发送时间','',date("$dateformat $timeformat",$pm['pmdate']),'');
	trbasic('内容','','<br>'.mnl2br(mhtmlspecialchars($pm['content'])).'<br>&nbsp;','');
	tabfooter();
	$query = $db->query("UPDATE {$tblprefix}pms SET viewed='1' WHERE pmid='$pmid'");
	echo "<input class=\"button\" type=\"submit\" name=\"\" value=\"返回\" onclick=\"redirect('"."?action=pmbox&page=$page"."')\">\n";	

}
?>