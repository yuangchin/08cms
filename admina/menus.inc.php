<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
$updatepage = '<br/><br/><div>是否刷新窗口应用更新后的菜单？&nbsp;&nbsp;<a href="' . "?isframe=1&entry=$entry&action=menusedit"  . '" target="_top">是</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:" onclick="return floatwin(\'close_\')">否</a></div>';
if($action == 'mtypeadd'){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!submitcheck('bmtypeadd')){
		tabheader('添加菜单分类','mtypeadd',"?entry=menus&action=mtypeadd");
		trbasic('分类名称','mtypenew[title]','','text');
		trbasic('分类默认链接','mtypenew[url]','','text',array('w'=>50));
		trbasic('分类排序','mtypenew[vieworder]','','text');
		tabfooter('bmtypeadd');
		a_guide('mtypeadd');
	}else{
		$mtypenew['title'] = trim(strip_tags($mtypenew['title']));
		$mtypenew['url'] = trim(strip_tags($mtypenew['url']));
		$mtypenew['vieworder'] = max(0,intval($mtypenew['vieworder']));
		!$mtypenew['title'] && cls_message::show('请输入菜单分类标题!');
		$db->query("INSERT INTO {$tblprefix}mtypes SET 
					mtid=".auto_insert_id('mtypes').",
					title='$mtypenew[title]', 
					url='$mtypenew[url]', 
					vieworder='$mtypenew[vieworder]'
					");
	
		adminlog('添加菜单分类');
		cls_CacheFile::Update('menus');
		cls_message::show("后台菜单分类添加完成$updatepage", axaction(6,"?entry=menus&action=menusedit"), $updatepage ? 2000 : 1250);
	}
}elseif($action == 'menuadd' && $mtid){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	$mtid = max(0,intval($mtid));
	$mtidsarr = array();
	$query = $db->query("SELECT * FROM {$tblprefix}mtypes WHERE fixed=0 ORDER BY vieworder,mtid");
	while($row = $db->fetch_array($query)) $mtidsarr[$row['mtid']] = $row['title'];
	if(!submitcheck('bmenuadd')){
		tabheader('添加菜单项','menuadd',"?entry=menus&action=menuadd&mtid=$mtid");
		trbasic('所属分类','menunew[mtid]',makeoption($mtidsarr,$mtid),'select');
		trbasic('菜单项名称','menunew[title]','','text');
		trbasic('菜单项链接','menunew[url]','','text',array('w'=>50));
		trbasic('菜单项排序','menunew[vieworder]','','text');
		trbasic('菜单项备注','menunew[remark]','','textarea');
		tabfooter('bmenuadd');
		a_guide('menuadd');
	}else{
		$menunew['title'] = trim(strip_tags($menunew['title']));
		$menunew['url'] = trim(strip_tags($menunew['url']));
		$menunew['vieworder'] = max(0,intval($menunew['vieworder']));
		(!$menunew['title'] || !$menunew['url']) && cls_message::show('请输入菜单标题与链接!');
		!$menunew['mtid'] && cls_message::show('请指定菜单所属分类!');
		$db->query("INSERT INTO {$tblprefix}menus SET 
					mnid=".auto_insert_id('menus').",
					title='$menunew[title]', 
					url='$menunew[url]', 
					remark='$menunew[remark]', 
					mtid='$menunew[mtid]', 
					vieworder='$menunew[vieworder]'
					");
	
		adminlog('添加后台菜单项');
		cls_CacheFile::Update('menus');
		cls_message::show("后台菜单项添加完成$updatepage", axaction(6,"?entry=menus&action=menusedit"), $updatepage ? 2000 : 1250);
	}
}elseif($action == 'menusedit'){
	backnav('backarea','m');
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!submitcheck('bmenusedit')){
		tabheader("后台菜单管理&nbsp; &nbsp; >><a href=\"?entry=menus&action=mtypeadd\" onclick=\"return floatwin('open_menusedit',this)\">添加分类</a>",'menusedit',"?entry=menus&action=menusedit",'8');
		trcategory(array('菜单ID','标题','启用','排序','添加','编辑','删除'));
		$i = 0;
		$query = $db->query("SELECT * FROM {$tblprefix}mtypes ORDER BY vieworder");
		while($mtype = $db->fetch_array($query)){
			$mtid = $mtype['mtid'];
			$i ++;
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w50\">[$mtid]</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"mtypesnew[$mtid][title]\" value=\"$mtype[title]\" size=\"25\"></td>\n".
				"<td class=\"txtC w30\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" name=\"mtypesnew[$mtid][vieworder]\" value=\"$mtype[vieworder]\" size=\"4\"></td>\n".
				"<td class=\"txtC w40\">".($mtype['fixed'] ? '' : "<a href=\"?entry=menus&action=menuadd&mtid=$mtid\" onclick=\"return floatwin('open_menusedit',this)\">+菜单</a>")."</td>\n".
				"<td class=\"txtC w40\">".($mtype['fixed'] ? '-' : ("<a href=\"?entry=menus&action=mtypedetail&mtid=$mtid\" onclick=\"return floatwin('open_menusedit',this)\">详情</a>"))."</td>\n".
				"<td class=\"txtC w40\">".($mtype['fixed'] ? '-' : ("<a onclick=\"return deltip()\" href=\"?entry=menus&action=mtypedel&mtid=$mtid\">删除</a>"))."</td>\n".
				"</tr>";
			$query1 = $db->query("SELECT * FROM {$tblprefix}menus WHERE mtid='$mtid' AND isbk=0 ORDER BY vieworder");
			while($row = $db->fetch_array($query1)){
				$mnid = $row['mnid'];
				$i ++;
				echo "<tr class=\"txt\">\n".
					"<td class=\"txtC w50\">$mnid</td>\n".
					"<td class=\"txtL\">&nbsp; &nbsp; &nbsp; &nbsp; <input type=\"text\" name=\"menusnew[$mnid][title]\" value=\"$row[title]\" size=\"25\"></td>\n".
					"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"menusnew[$mnid][available]\" value=\"1\"".($row['available'] ? " checked" : "")."></td>\n".
					"<td class=\"txtC w40\"><input type=\"text\" name=\"menusnew[$mnid][vieworder]\" value=\"$row[vieworder]\" size=\"4\"></td>\n".
					"<td class=\"txtC w40\">-</td>\n".
					"<td class=\"txtC w40\">".($row['fixed'] ? '-' : "<a href=\"?entry=menus&action=menudetail&mnid=$mnid\" onclick=\"return floatwin('open_menusedit',this)\">详情</a>")."</td>\n".
					"<td class=\"txtC w40\">".($row['fixed'] ? '-' : "<a onclick=\"return deltip()\" href=\"?entry=menus&action=menudel&mnid=$mnid\">删除</a>")."</td>\n".
					"</tr>";
			}
		}
		tabfooter('bmenusedit');
		a_guide('menusedit');
	}else{
		if(!empty($mtypesnew)){
			foreach($mtypesnew as $k => $v){
				$v['title'] = trim(strip_tags($v['title']));
				$v['vieworder'] = empty($v['vieworder']) ? 0 : max(0,intval($v['vieworder']));
				$sqlstr = "vieworder='$v[vieworder]'";
				$v['title'] && $sqlstr .= ",title='$v[title]'";
				$db->query("UPDATE {$tblprefix}mtypes SET $sqlstr WHERE mtid='$k'");
			}
		}
		if(!empty($menusnew)){
			foreach($menusnew as $k => $v){
				$v['title'] = trim(strip_tags($v['title']));
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$v['available'] = empty($v['available']) ? 0 : 1;
				$sqlstr = "vieworder='$v[vieworder]',available='$v[available]'";
				$v['title'] && $sqlstr .= ",title='$v[title]'";
				$db->query("UPDATE {$tblprefix}menus SET $sqlstr WHERE mnid='$k'");
			}
		}
		adminlog('编辑菜单项列表');
		cls_CacheFile::Update('menus');
		cls_message::show("菜单项编辑完成$updatepage", "?entry=menus&action=menusedit");
	}
}elseif($action == 'mtypedetail' && $mtid){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!($mtype = $db->fetch_one("SELECT * FROM {$tblprefix}mtypes WHERE mtid='$mtid'"))) cls_message::show('请指定正确的菜单分类');
	if(!submitcheck('bmtypedetail')){
		tabheader('编辑菜单分类','mtypedetail',"?entry=menus&action=mtypedetail&mtid=$mtid");
		trbasic('分类名称','mtypenew[title]',$mtype['title'],'text');
		trbasic('分类默认链接','mtypenew[url]',$mtype['url'],'text',array('w'=>50));
		trbasic('分类排序','mtypenew[vieworder]',$mtype['vieworder'],'text');
		tabfooter('bmtypedetail');
		a_guide('mtypedetail');
	}else{
		$mtypenew['title'] = trim(strip_tags($mtypenew['title']));
		$mtypenew['url'] = trim(strip_tags($mtypenew['url']));
		$mtypenew['vieworder'] = max(0,intval($mtypenew['vieworder']));
		!$mtypenew['title'] && cls_message::show('请输入菜单分类标题!');
		$db->query("UPDATE {$tblprefix}mtypes SET 
					title='$mtypenew[title]', 
					url='$mtypenew[url]', 
					vieworder='$mtypenew[vieworder]'
					WHERE mtid='$mtid'");
	
		adminlog('编辑菜单分类详情');
		cls_CacheFile::Update('menus');
		cls_message::show("菜单分类修改完成$updatepage", axaction(6,"?entry=menus&action=menusedit"), $updatepage ? 2000 : 1250);
	}
}elseif($action == 'menudetail' && $mnid){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!($menu = $db->fetch_one("SELECT * FROM {$tblprefix}menus WHERE mnid='$mnid'"))) cls_message::show('请指定正确的菜单项');
	if(!submitcheck('bmenudetail')){
		tabheader('编辑菜单项','menudetail',"?entry=menus&action=menudetail&mnid=$mnid");
		$mtidsarr = array();
		$query = $db->query("SELECT * FROM {$tblprefix}mtypes WHERE fixed=0 ORDER BY vieworder");
		while($row = $db->fetch_array($query)) $mtidsarr[$row['mtid']] = $row['title'];
		trbasic('所属分类','menunew[mtid]',makeoption($mtidsarr,$menu['mtid']),'select');
		trbasic('菜单项名称','menunew[title]',$menu['title'],'text');
		trbasic('菜单项链接','menunew[url]',$menu['url'],'text',array('w'=>50));
		trbasic('菜单项排序','menunew[vieworder]',$menu['vieworder'],'text');
		trbasic('菜单项备注','menunew[remark]',$menu['remark'],'textarea');
		tabfooter('bmenudetail');
		a_guide('menudetail');
	}else{
		$menunew['title'] = trim(strip_tags($menunew['title']));
		$menunew['url'] = trim(strip_tags($menunew['url']));
		$menunew['vieworder'] = max(0,intval($menunew['vieworder']));
		$menunew['mtid'] = empty($menunew['mtid']) ? 0 : max(0,intval($menunew['mtid']));
		(!$menunew['title'] || !$menunew['url']) && cls_message::show('请输入菜单标题与链接!');
		!$menunew['mtid'] && cls_message::show('请指定菜单所属分类!');
		$db->query("UPDATE {$tblprefix}menus SET 
					title='$menunew[title]', 
					url='$menunew[url]', 
					remark='$menunew[remark]', 
					mtid='$menunew[mtid]', 
					vieworder='$menunew[vieworder]'
					WHERE mnid='$mnid'");
		adminlog('编辑菜单项详情');
		cls_CacheFile::Update('menus');
		cls_message::show("菜单项修改完成$updatepage", axaction(6,"?entry=menus&action=menusedit"), $updatepage ? 2000 : 1250);
	}
}elseif($action == 'mtypedel' && $mtid){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}menus WHERE mtid='$mtid'")){
		cls_message::show('只能删除空的菜单分类。', "?entry=menus&action=menusedit");
	}
	$db->query("DELETE FROM {$tblprefix}mtypes WHERE mtid='$mtid' AND fixed='0'");
	adminlog('删除菜单分类');
	cls_CacheFile::Update('menus');
	cls_message::show("菜单分类删除完成$updatepage", axaction(6,"?entry=menus&action=menusedit"), $updatepage ? 2000 : 1250);
}elseif($action == 'menudel' && $mnid){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	$db->query("DELETE FROM {$tblprefix}menus WHERE mnid='$mnid' AND fixed='0'");
	adminlog('删除菜单项');
	cls_CacheFile::Update('menus');
	cls_message::show("菜单项删除完成$updatepage", axaction(6,"?entry=menus&action=menusedit"), $updatepage ? 2000 : 1250);
}
?>