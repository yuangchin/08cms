<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('fragment')) cls_message::show($re);
$frcatalogs = fetch_arr();
empty($action) && $action = 'frcatalogsedit';
backnav('fragment','catalog');
if($action == 'frcatalogsedit'){
	if(!submitcheck('bfrcatalogsedit') && !submitcheck('bfrcatalogadd')){
		tabheader('碎片分类管理','frcatalogsedit','?entry=frcatalogs&action=frcatalogsedit','7');
		trcategory(array('ID',array('分类名称','txtL'),'排序','删除'));
		foreach($frcatalogs as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"frcatalogsnew[$k][title]\" value=\"".mhtmlspecialchars($v['title'])."\" size=\"25\" maxlength=\"30\"></td>\n".
				"<td class=\"txtC w50\"><input type=\"text\" name=\"frcatalogsnew[$k][vieworder]\" value=\"$v[vieworder]\" size=\"2\"></td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip()\" href=\"?entry=frcatalogs&action=frcatalogdel&frcaid=$k\">删除</a></td>\n".
				"</tr>";
		}
		tabfooter('bfrcatalogsedit');
		tabheader('添加碎片分类','frcatalogadd','?entry=frcatalogs&action=frcatalogsedit');
		trbasic('分类名称','frcatalognew[title]','','text');
		tabfooter('bfrcatalogadd');
	}elseif(submitcheck('bfrcatalogsedit')){
		if(!empty($frcatalogsnew)){
			foreach($frcatalogsnew as $k => $v){
				$v['title'] = $v['title'] ? $v['title'] : $frcatalogs[$k]['title'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$db->query("UPDATE {$tblprefix}frcatalogs SET 
							title='$v[title]', 
							vieworder='$v[vieworder]' 
							WHERE frcaid='$k'
							");
			}
			cls_CacheFile::Update('frcatalogs');
		}
		adminlog('编辑碎片分类管理列表');
		cls_message::show('分类编辑完成', '?entry=frcatalogs&action=frcatalogsedit');
	}elseif(submitcheck('bfrcatalogadd')){
		$frcatalognew['title'] = trim(strip_tags($frcatalognew['title']));
		if(!$frcatalognew['title']) cls_message::show('分类资料不完全',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}frcatalogs SET 
				   	frcaid=".auto_insert_id('frcatalogs').",
					title='$frcatalognew[title]'
					");
		cls_CacheFile::Update('frcatalogs');
		adminlog('添加碎片分类');
		cls_message::show('碎片分类添加完成', '?entry=frcatalogs&action=frcatalogsedit');
	}
}elseif($action == 'frcatalogdel' && $frcaid) {
	if(!($frcatalog = $frcatalogs[$frcaid])) cls_message::show('请指定正确的碎片分类。');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=frcatalogs&action=frcatalogdel&frcaid=$frcaid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=frcatalogs&action=frcatalogsedit>返回</a>";
		cls_message::show($message);
	}
	$db->query("UPDATE {$tblprefix}fragments SET frcaid=0 WHERE frcaid='$frcaid'");
	$db->query("DELETE FROM {$tblprefix}frcatalogs WHERE frcaid='$frcaid'");
	cls_CacheFile::Update('frcatalogs');
	adminlog('删除碎片分类');
	cls_message::show('分类删除完成', '?entry=frcatalogs&action=frcatalogsedit');
}else cls_message::show('错误的文件参数');

function fetch_arr(){
	global $db,$tblprefix;
	$rets = array();
	$query = $db->query("SELECT * FROM {$tblprefix}frcatalogs ORDER BY vieworder,frcaid");
	while($r = $db->fetch_array($query)){
		$rets[$r['frcaid']] = $r;
	}
	return $rets;
}

?>
