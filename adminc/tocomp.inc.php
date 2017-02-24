<?php
!defined('M_COM') && exit('No Permission');

$arid = 4;$schid = 2;$tchid = 3;
if(!($abrel = cls_cache::Read('abrel',$arid)) || empty($abrel['available'])) cls_message::show('不存在或关闭的合辑项目。');
if($curuser->info['mchid'] != $schid) cls_message::show('请先注册为经纪人。');
$curuser->detail_data();
$info = &$curuser->info;

if($info["pid$arid"] && $info["incheck$arid"]){
	
	backnav('company','manage');
	$au = new cls_userinfo;
	$au->activeuser($info["pid$arid"]);
	if($au->info['mid'] && $au->info['checked'] && $au->info['mchid'] == $tchid){
		$au->detail_data();
		tabheader('我的经纪公司');
		trbasic('公司名称','',$au->info['cmane'],'');
		trbasic('联系电话','',$au->info['lxdh'],'');
		trbasic('公司地址','',$au->info['caddress'],'');
		trbasic('公司店铺','',"<a href=\"".$au->info['mspacehome']."\" target=\"_blank\">>>进去逛逛</a>",'');
		tabfooter();
	}else{
		$curuser->exit_comp();
		cls_message::show('未找到您所属的经纪公司。',"?action=$action");
	}
}elseif(empty($deal)){
	$page = empty($page) ? 1 : max(1, intval($page));
	submitcheck('bfilter') && $page = 1;
	$szqy = empty($szqy) ? 0 : max(0,intval($szqy));
	$keyword = empty($keyword) ? '' : $keyword;
	$wheresql = " WHERE m.mchid='$tchid' AND m.checked=1";
	$fromsql = " FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid INNER JOIN {$tblprefix}members_$tchid c ON c.mid=m.mid";
	$keyword && $wheresql .= " AND (c.cmane LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' OR m.mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%')";
	if($szqy && $cnsql = caccsql('s.szqy',sonbycoid($szqy,1))) $wheresql .= " AND $cnsql";
	
	$filterstr = '';
	foreach(array('keyword','szqy',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	
	$szqyarr = array();if($arr = cacc_arr('m',$tchid,'szqy')) foreach($arr as $k => $v) $szqyarr[$k] = $v['title'];
	echo form_str($action,"?action=$action&page=$page");
	tabheader_e();
	echo "<tr><td class=\"item2\">";
	echo "&nbsp; <input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\">&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"szqy\">".makeoption(array(0 => '不限地域') + $szqyarr,$szqy)."</select>&nbsp; ";
	echo strbutton('bfilter','筛选');
	echo '</td></tr>';
	tabfooter();
	
	$pagetmp = $page;
	do{
		$query = $db->query("SELECT m.*,s.*,c.* $fromsql $wheresql ORDER BY m.mid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		$pagetmp--;
	}while(!$db->num_rows($query) && $pagetmp);
	
	$addstr = '';
	if($info["pid$arid"] && $ninfo = $db->fetch_one("SELECT m.*,c.* $fromsql WHERE m.mid='".$info["pid$arid"]."' AND m.mchid='$tchid' AND m.checked=1")){
		$info['mspacehome'] = cls_Mspace::IndexUrl($info);
		$addstr = " &nbsp; &nbsp;正在申请加入:<a href=\"".$info['mspacehome']."\" target=\"_blank\">$ninfo[cmane]</a>";
	}
	tabheader('申请加入经纪公司'.$addstr,'','',10);
	trcategory(array('ID',array('经纪公司','left'),array('会员','left'),'地域','公司店铺','申请'));
	while($row = $db->fetch_array($query)){
		$row['mspacehome'] = cls_Mspace::IndexUrl($row);
		$dpstr = "<a href=\"".$row['mspacehome']."\" target=\"_blank\">逛逛</a>";
		$szqystr = $row['szqy'] ? $szqyarr[$row['szqy']] : '-';
		$jrstr = $info["pid$arid"] == $row['mid'] ? "<a href=\"?action=$action&deal=qx\"><b>取消</b></a>" : "<a href=\"?action=$action&deal=jr&mid=$row[mid]\">加入</a>";
		echo "<tr>\n".
			"<td class=\"item\" width=\"40\">$row[mid]</td>\n".
			"<td class=\"item2\">$row[cmane]</td>\n".
			"<td class=\"item2\" width=\"100\">$row[mname]</td>\n".
			"<td class=\"item\" width=\"60\">$szqystr</td>\n".
			"<td class=\"item\" width=\"60\">$dpstr</td>\n".
			"<td class=\"item\" width=\"60\">$jrstr</td>\n".
			"</tr>\n";
	}
	tabfooter();
	echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page,"?action=$action$filterstr");
}elseif($deal == 'jr'){
	if(!($mid = empty($mid) ? 0 : max(0,intval($mid)))) cls_message::show('请指定要加入的公司。',M_REFERER);
	$k = $curuser->ag2comp($mid);
	cls_message::show($k ? '申请成功，请等待公司审核。' : '申请不成功。',M_REFERER);
}elseif($deal == 'qx'){
	$curuser->exit_comp();
	cls_message::show('成功取消申请。',M_REFERER);
}

?>
