<?php
!defined('M_COM') && exit('No Permission');
foreach(array('catalogs','channels',) as $k) $$k = cls_cache::Read($k);

$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$type = empty($type) ? '' : $type;
isset($table) || $table = -1;
$aids = empty($aids) ? '' : $aids;

$wheresql = "WHERE mid='".$curuser->info['mid']."'";
if(!empty($type)){
	$wheresql .= " AND type='$type'";
}
if(!empty($aids)){
	$aidsarr = array_filter(explode(',',$aids));
	$wheresql .= " AND aid ".multi_str($aidsarr);
}
$table != -1 && $wheresql .= ($wheresql ? " AND " : "")."tid='$table'";

$filterstr = '';
foreach(array('aids','type','table') as $k)$filterstr .= "&$k=".urlencode($$k);
if(!submitcheck('buserfilesedit')){
	//同include/upload.cls.php中closure函数的$tids变量对应
	$tabsarr = array('-1' => '全部类型',1 => '文档', 2 => '副件信息', 3 => '会员', 16 => '评论', 17 => '回复', 18 => '报价', 32 => '会员评论', 33 => '会员回复', '0' => '其它');
	$linkarr = array(1 => 'archive&aid=', 2 => 'farchive&aid=', 3 => 'memberinfo&mid=', 4 => 'marchive&maid=', 16 => 'comment&cid=', 17 => 'reply&cid=', 18 => 'offer&cid=', 32 => 'mcomment&cid=', 33 => 'mreply&cid=');
	$typearr = array('0' => '全部类型','image' => '图片','flash' => 'Flash','media' => '视频','file' => '其它',);
	echo form_str($action.'arcsedit',"?action=userfiles");
	tabheader_e();
	echo "<tr><td class=\"item2\">";
	echo '相关文档ID(多个ID用逗号隔开)'."&nbsp; <input class=\"text\" name=\"aids\" type=\"text\" value=\"$aids\" style=\"vertical-align: middle;\">&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"type\">".makeoption($typearr,$type)."</select>&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"table\">".makeoption($tabsarr,$table)."</select>&nbsp; ";
	echo strbutton('bfilter','筛选').'</td></tr>';
	tabfooter();

	$pagetmp = $page;
	do{
		$query = $db->query("SELECT * FROM {$tblprefix}userfiles $wheresql ORDER BY ufid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);
	$itemstr = '';
	while($item = $db->fetch_array($query)) {
		$item['createdate'] = date("$dateformat", $item['createdate']);
		$item['preview'] = ($item['type'] == 'image') ? "<a href=\"".cls_url::tag2atm($item['url'])."\" target=\"_blank\">".'预览'."</a>" : "-";
		$item['type'] = $typearr[$item['type']];
		$item['thumbedstr'] = $item['thumbed'] ? 'Y' : '-';
		$item['size'] = ceil($item['size'] / 1024);
		$item['source'] = $item['aid'] && $item['tid'] ? "<a href=\"?action=".$linkarr[$item['tid']]."$item[aid]\" target=\"_blank\" onclick=\"return floatwin('open_editbyatt',this)\">".'查看'."</a>" : "-";
		$itemstr .= "<tr><td align=\"center\" class=\"item1\" width=\"40\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid['$item[ufid]']\" value=\"$item[ufid]\">\n".
			"<td class=\"item2\">$item[filename]</td>\n".
			"<td class=\"item\" width=\"40\">$item[type]</td>\n".
			"<td class=\"item\" width=\"60\">$item[size]</td>\n".
			"<td class=\"item\" width=\"40\">$item[preview]</td>\n".
			"<td class=\"item\" width=\"50\">$item[thumbedstr]</td>\n".
			"<td class=\"item\" width=\"78\">$item[createdate]</td>\n".
			"<td class=\"item\" width=\"40\">$item[source]</td></tr>\n";
	}
	$itemcount = $db->result_one("SELECT count(*) FROM {$tblprefix}userfiles $wheresql");
	$multi = multi($itemcount, $mrowpp, $page, "?action=userfiles$filterstr");

	tabheader('附件列表','','',9);
	trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">".'删?',array('名称','left'),'类型','大小(K)','预览','缩略图','上传日期','来源'));
	echo $itemstr;
	tabfooter();
	echo $multi;
	echo "<br><input class=\"button\" type=\"submit\" name=\"buserfilesedit\" value=\"提交\"></form>";
}else{
	empty($selectid) && cls_message::show('请选择文档',"?action=userfiles&page=$page$filterstr");
	$query = $db->query("SELECT * FROM {$tblprefix}userfiles WHERE ufid ".multi_str($selectid)." AND mid='".$curuser->info['mid']."' ORDER BY ufid");
	while($r = $db->fetch_array($query)){
		atm_delete($r['url'],$r['type']);
		$curuser->updateuptotal(ceil($r['size'] / 1024),1);
	}
	$curuser->updatedb();
	$db->query("DELETE FROM {$tblprefix}userfiles WHERE ufid ".multi_str($selectid)." AND mid='".$curuser->info['mid']."'",'UNBUFFERED');
	cls_message::show('文档操作完成',"?action=userfiles&page=$page$filterstr");
}
?>