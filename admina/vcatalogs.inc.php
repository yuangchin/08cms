<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('other')) cls_message::show($re);
$vcatalogs = cls_cache::Read('vcatalogs');
backnav('vote','vcata');
if($action == 'vcatalogsedit'){
	if(!submitcheck('bvcatalogsedit') && !submitcheck('bvcatalogadd')){
		tabheader('投票分类管理','vcatalogsedit','?entry=vcatalogs&action=vcatalogsedit','6');
		trcategory(array('序号','分类名称','排序','删除'));
		$k = 0;
		foreach($vcatalogs as $caid => $vcatalog) {
			$k ++;
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"vcatalogsnew[$caid][title]\" value=\"".mhtmlspecialchars($vcatalog['title'])."\" size=\"25\" maxlength=\"30\"></td>\n".
				"<td class=\"txtC w50\"><input type=\"text\" name=\"vcatalogsnew[$caid][vieworder]\" value=\"$vcatalog[vieworder]\" size=\"2\"></td>\n".
				"<td class=\"txtC w50\"><a onclick=\"return deltip()\" href=\"?entry=vcatalogs&action=vcatalogdelete&caid=$caid\">[删除]</a></td>\n".
				"</tr>";
		}
		tabfooter('bvcatalogsedit');
		tabheader('添加投票分类','vcatalogadd','?entry=vcatalogs&action=vcatalogsedit');
		trbasic('分类名称','vcatalogadd[title]','','text');
		tabfooter('bvcatalogadd','添加');
		a_guide('vcatalogsedit');
	}elseif(submitcheck('bvcatalogsedit')){
		if(!empty($vcatalogsnew)){
			foreach($vcatalogsnew as $caid => $vcatalognew){
				$vcatalognew['title'] = $vcatalognew['title'] ? $vcatalognew['title'] : $vcatalogs[$caid]['title'];
				$vcatalognew['vieworder'] = max(0,intval($vcatalognew['vieworder']));
				if(($vcatalognew['title'] != $vcatalogs[$caid]['title']) || ($vcatalognew['vieworder'] != $vcatalogs[$caid]['vieworder'])){
					$db->query("UPDATE {$tblprefix}vcatalogs SET 
								title='$vcatalognew[title]', 
								vieworder='$vcatalognew[vieworder]' 
								WHERE caid='$caid'
								");
				}
			}
			cls_CacheFile::Update('vcatalogs');
		}
		cls_message::show('分类编辑完成', '?entry=vcatalogs&action=vcatalogsedit');
	}elseif(submitcheck('bvcatalogadd')){
		empty($vcatalogadd['title']) && cls_message::show('资料不完全','?entry=vcatalogs&action=vcatalogsedit');
		$db->query("INSERT INTO {$tblprefix}vcatalogs SET title='$vcatalogadd[title]'");
		cls_CacheFile::Update('vcatalogs');
		cls_message::show('投票分类添加完成', '?entry=vcatalogs&action=vcatalogsedit');
	}
}elseif($action == 'vcatalogdelete' && $caid) {
	if(!submitcheck('confirm')) {
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=vcatalogs&action=vcatalogdelete&caid=$caid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=vcatalogs&action=vcatalogsedit>返回</a>";
		cls_message::show($message);
	}
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}votes WHERE caid='$caid'")) cls_message::show('分类没有相关联的投票才能删除', '?entry=vcatalogs&action=vcatalogsedit');
	$db->query("DELETE FROM {$tblprefix}vcatalogs WHERE caid='$caid'");
	cls_CacheFile::Update('vcatalogs');
	cls_message::show('分类删除完成', '?entry=vcatalogs&action=vcatalogsedit');
}

?>
