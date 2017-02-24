<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('mcconfig')) cls_message::show($re);
$permissions = cls_cache::Read('permissions');
if($action == 'mmtypeadd'){
	if(!submitcheck('bmmtypeadd')){
		tabheader('添加会员中心菜单分类','mmtypeadd',"?entry=mmenus&action=mmtypeadd");
		trspecial('菜单小图标',specialarr(array('type' => 'image','varname' => 'mmtypenew[menuimage]')));
		trbasic('分类名称','mmtypenew[title]','','text');
		trbasic('分类排序','mmtypenew[vieworder]','','text');
		tabfooter('bmmtypeadd');
		a_guide('mmtypeadd');
	}else{
		$mmtypenew['title'] = trim(strip_tags($mmtypenew['title']));
		$mmtypenew['vieworder'] = max(0,intval($mmtypenew['vieworder']));
		!$mmtypenew['title'] && cls_message::show('请输入会员中心菜单分类标题!');
		$db->query("INSERT INTO {$tblprefix}mmtypes SET 
					mtid=".auto_insert_id('mmtypes').",
					menuimage='$mmtypenew[menuimage]',
					title='$mmtypenew[title]', 
					vieworder='$mmtypenew[vieworder]'
					");
		$mtid = $db->insert_id();
		if(!empty($mmtypenew['menuimage'])){
			$source = str_replace($cms_abs,'./',$mmtypenew['menuimage']);
			$tofile = './adminm/images/bigmenu'.$mtid.substr($mmtypenew['menuimage'],(strlen($mmtypenew['menuimage'])-4));
			cls_upload::image_resize($source,20,20,$tofile,1);
		}	
		adminlog('会员中心菜单分类添加');
		cls_CacheFile::Update('mmenus');
		cls_message::show('会员中心菜单分类添加完成', "?entry=mmenus&action=mmenusedit");
	}
}elseif($action == 'mmenuadd' && $mtid){
	$mtid = max(0,intval($mtid));
	$mtidsarr = array();
	$query = $db->query("SELECT * FROM {$tblprefix}mmtypes ORDER BY vieworder,mtid");
	while($row = $db->fetch_array($query)){
		$mtidsarr[$row['mtid']] = $row['title'];
	}
	if(!submitcheck('bmmenuadd')){
		tabheader('添加会员中心菜单项目','mmenuadd',"?entry=mmenus&action=mmenuadd&mtid=$mtid");
		trbasic('所属分类','mmenunew[mtid]',makeoption($mtidsarr,$mtid),'select');
		trbasic('菜单项目名称','mmenunew[title]','','text');
		trbasic('菜单项目链接','mmenunew[url]','','text',array('w'=>50));
		//trbasic('菜单显示权限设置','mmenunew[pmid]',makeoption(pmidsarr('menu')),'select',array('guide' => '系统设置=>方案管理=>权限方案=>菜单'));
		setPermBar('菜单显示权限设置', 'mmenunew[pmid]', '', $source='menu', $soext='open', $guide='');
        trbasic('菜单项目排序','mmenunew[vieworder]','','text');
		trbasic('新窗口打开链接','mmenunew[newwin]',0,'radio');
		trbasic('链接加入onclick字串','mmenunew[onclick]','','text',array('w'=>50));
		trbasic('菜单备注','mmenunew[remark]','','textarea');
		tabfooter('bmmenuadd');
		a_guide('mmenuadd');
	}else{
		$mmenunew['title'] = trim(strip_tags($mmenunew['title']));
		$mmenunew['url'] = trim(strip_tags($mmenunew['url']));
		$mmenunew['onclick'] = trim($mmenunew['onclick']);
		$mmenunew['vieworder'] = max(0,intval($mmenunew['vieworder']));
		(!$mmenunew['title'] || !$mmenunew['url']) && cls_message::show('请输入菜单标题与链接!',axaction(1,M_REFERER));
		!$mmenunew['mtid'] && cls_message::show('请指定会员中心菜单所属分类!');
		$db->query("INSERT INTO {$tblprefix}mmenus SET 
					mnid=".auto_insert_id('mmenus').",
					title='$mmenunew[title]', 
					url='$mmenunew[url]', 
					mtid='$mmenunew[mtid]', 
					pmid='$mmenunew[pmid]', 
					newwin='$mmenunew[newwin]', 
					onclick='$mmenunew[onclick]', 
					vieworder='$mmenunew[vieworder]',
					remark='$mmenunew[remark]'
					");
	
		adminlog('添加会员中心菜单项目');
		cls_CacheFile::Update('mmenus');
		cls_message::show('会员中心菜单项目添加完成', axaction(6,"?entry=mmenus&action=mmenusedit"));
	}
}elseif($action == 'mmenusedit'){
	backnav('mcenter','c');
	
	if(!submitcheck('bmmenusedit')){
		tabheader('会员中心菜单'."&nbsp; &nbsp; >><a href=\"?entry=mmenus&action=mmtypeadd\">".'添加菜单分类'.'</a>','mmenusedit',"?entry=mmenus&action=mmenusedit",'9');
		trcategory(array('菜单ID',array('标题','txtL'),'启用','排序',array('描述','txtL'),'添加','编辑','删除'));
		$query = $db->query("SELECT * FROM {$tblprefix}mmtypes ORDER BY vieworder,mtid");
		$i = 0;
		while($mmtype = $db->fetch_array($query)){
			$mtid = $mmtype['mtid'];
			$i ++;
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w50\">[$mtid]</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"mmtypesnew[$mtid][title]\" value=\"$mmtype[title]\" size=\"25\"></td>\n".
				"<td class=\"txtC w30\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" name=\"mmtypesnew[$mtid][vieworder]\" value=\"$mmtype[vieworder]\" size=\"4\"></td>\n".
				"<td class=\"txtL\"></td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=mmenus&action=mmenuadd&mtid=$mtid\" onclick=\"return floatwin('open_mmenusedit',this)\">+菜单</a></td>\n".
				"<td class=\"txtC w40\">-</td>\n".
				"<td class=\"txtC w40\"><a onclick=\"return deltip()\" href=\"?entry=mmenus&action=mmtypedel&mtid=$mtid\">删除</a></td>\n".
				"</tr>";
			$query1 = $db->query("SELECT * FROM {$tblprefix}mmenus WHERE mtid='$mtid' ORDER BY vieworder,mnid");
			while($row = $db->fetch_array($query1)){
				$mnid = $row['mnid'];
				$i ++;
				echo "<tr class=\"txt\">\n".
					"<td class=\"txtC w50\">$mnid</td>\n".
					"<td class=\"txtL\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type=\"text\" name=\"mmenusnew[$mnid][title]\" value=\"$row[title]\" size=\"25\"></td>\n".
					"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"mmenusnew[$mnid][available]\" value=\"1\"".($row['available'] ? " checked" : "")."></td>\n".
					"<td class=\"txtC w40\"><input type=\"text\" name=\"mmenusnew[$mnid][vieworder]\" value=\"$row[vieworder]\" size=\"4\"></td>\n".
					"<td class=\"txtL\"><input type=\"text\" name=\"mmenusnew[$mnid][description]\" value=\"$row[description]\" size=\"40\"></td>\n".
					"<td class=\"txtC w40\">-</td>\n".
					"<td class=\"txtC w40\"><a href=\"?entry=mmenus&action=mmenudetail&mnid=$mnid\" onclick=\"return floatwin('open_mmenusedit',this)\">详情</a></td>\n".
					"<td class=\"txtC w40\"><a onclick=\"return deltip()\" href=\"?entry=mmenus&action=mmenudel&mnid=$mnid\">删除</a></td>\n".
					"</tr>";
			}
		}
		tabfooter('bmmenusedit');
		a_guide('mmenusedit');
	}else{
		if(!empty($mmtypesnew)){
			foreach($mmtypesnew as $k => $v){
				$v['title'] = trim(strip_tags($v['title']));
				$v['vieworder'] = empty($v['vieworder']) ? 0 : max(0,intval($v['vieworder']));
				$sqlstr = "vieworder='$v[vieworder]'";
				$v['title'] && $sqlstr .= ",title='$v[title]'";
				$db->query("UPDATE {$tblprefix}mmtypes SET $sqlstr WHERE mtid='$k'");
			}
		}
		if(!empty($mmenusnew)){
			foreach($mmenusnew as $k => $v){
				$v['title'] = trim(strip_tags($v['title']));
				$v['description'] = trim(strip_tags($v['description']));
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$v['available'] = empty($v['available']) ? 0 : 1;
				$sqlstr = "vieworder='$v[vieworder]',available='$v[available]'";
				$v['title'] && $sqlstr .= ",title='$v[title]'";
				isset($v['description']) && $sqlstr .= ",description='$v[description]'";
				$db->query("UPDATE {$tblprefix}mmenus SET $sqlstr WHERE mnid='$k'");
			}
		}
		adminlog('编辑会员中心菜单列表');
		cls_CacheFile::Update('mmenus');
		cls_message::show('会员中心菜单编辑完成', "?entry=mmenus&action=mmenusedit");
	}
}elseif($action == 'mmenudetail' && $mnid){
	if(!($mmenu = $db->fetch_one("SELECT * FROM {$tblprefix}mmenus WHERE mnid='$mnid'"))) cls_message::show('请指定正确的会员中心菜单项目');
	if(!submitcheck('bmmenudetail')){
		tabheader('编辑会员中心菜单项目详情','mmenudetail',"?entry=mmenus&action=mmenudetail&mnid=$mnid");
		$mtidsarr = array();
		$query = $db->query("SELECT * FROM {$tblprefix}mmtypes ORDER BY vieworder,mtid");
		while($row = $db->fetch_array($query)){
			$mtidsarr[$row['mtid']] = $row['title'];
		}
		trbasic('所属分类','mmenunew[mtid]',makeoption($mtidsarr,$mmenu['mtid']),'select');
		trbasic('菜单项目名称','mmenunew[title]',$mmenu['title'],'text');
		trbasic('菜单项目链接','mmenunew[url]',$mmenu['url'],'text',array('w'=>50));
		setPermBar('菜单显示权限设置', 'mmenunew[pmid]', @$mmenu['pmid'], 'menu', 'open', '');
        trbasic('菜单项目排序','mmenunew[vieworder]',$mmenu['vieworder'],'text');
		trbasic('新窗口打开链接','mmenunew[newwin]',$mmenu['newwin'],'radio');
		trbasic('链接加入onclick字串','mmenunew[onclick]',$mmenu['onclick'],'text',array('w'=>50));
		trbasic('菜单备注','mmenunew[remark]',$mmenu['remark'],'textarea');
		tabfooter('bmmenudetail');
		a_guide('mmenudetail');
	}else{
		$mmenunew['title'] = trim(strip_tags($mmenunew['title']));
		$mmenunew['url'] = trim(strip_tags($mmenunew['url']));
		$mmenunew['onclick'] = trim($mmenunew['onclick']);
		$mmenunew['vieworder'] = max(0,intval($mmenunew['vieworder']));
		$mmenunew['mtid'] = empty($mmenunew['mtid']) ? 0 : max(0,intval($mmenunew['mtid']));
		(!$mmenunew['title'] || !$mmenunew['url']) && cls_message::show('请输入会员中心菜单标题与链接!');
		!$mmenunew['mtid'] && cls_message::show('请指定会员中心菜单所属分类!');
		$db->query("UPDATE {$tblprefix}mmenus SET 
					title='$mmenunew[title]', 
					url='$mmenunew[url]', 
					mtid='$mmenunew[mtid]', 
					pmid='$mmenunew[pmid]', 
					newwin='$mmenunew[newwin]', 
					onclick='$mmenunew[onclick]', 
					vieworder='$mmenunew[vieworder]',
					remark='$mmenunew[remark]'
					WHERE mnid='$mnid'");
		adminlog('编辑会员中心菜单项目详情');
		cls_CacheFile::Update('mmenus');
		cls_message::show('菜单项目修改完成', axaction(6,"?entry=mmenus&action=mmenusedit"));
	}
}elseif($action == 'mmtypedel' && $mtid){
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}mmenus WHERE mtid='$mtid'")){
		cls_message::show('菜单分类没有相关联的菜单项目才能删除', "?entry=mmenus&action=mmenusedit");
	}
	$db->query("DELETE FROM {$tblprefix}mmtypes WHERE mtid='$mtid'");
	adminlog('删除会员中心菜单分类');
	cls_CacheFile::Update('mmenus');
	cls_message::show('菜单分类删除完成', "?entry=mmenus&action=mmenusedit");
}elseif($action == 'mmenudel' && $mnid){
	$db->query("DELETE FROM {$tblprefix}mmenus WHERE mnid='$mnid'");
    $file = _08_FilesystemFile::getInstance();
	$file->delFile(M_ROOT."dynamic/mguides/mguide_$mnid.php");
	adminlog('删除会员中心菜单项目');
	cls_CacheFile::Update('mmenus');
	cls_message::show('菜单项目删除完成', "?entry=mmenus&action=mmenusedit");
}
?>