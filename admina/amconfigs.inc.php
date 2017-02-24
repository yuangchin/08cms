<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
foreach(array('channels','fchannels','mchannels','catalogs','fcatalogs','cotypes','mtpls','aurls','linknodes',) as $k) $$k = cls_cache::Read($k);
$amconfigs = cls_DbOther::CacheArray(array('tbl' => 'amconfigs','key' => 'amcid','orderby' => 'vieworder',));
$linkitemtype = array(
	'a' => '常规链接',
	'm' => '会员链接',
);
if($action == 'amconfigadd'){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!submitcheck('bsubmit')){
		tabheader('后台管理角色添加','amconfigadd',"?entry=$entry&action=$action");
		trbasic('后台管理角色名称','fmdata[cname]');
		trbasic('备注','fmdata[remark]','','text',array('w'=>50));
		tabfooter('bsubmit','添加');
		a_guide('amconfigsedit');
	}else{
		$fmdata['cname'] = trim(strip_tags($fmdata['cname']));
		$fmdata['remark'] = trim(strip_tags($fmdata['remark']));
		if(empty($fmdata['cname'])) cls_message::show('请输入后台角色资料名称。',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}amconfigs SET amcid=".auto_insert_id('amconfigs').",cname='$fmdata[cname]',remark='$fmdata[remark]'");
		$amcid = $db->insert_id();
		adminlog('添加后台管理角色');
		cls_CacheFile::Update('amconfigs');
		cls_message::show('后台角色添加完成，请继续做详细设置。',"?entry=$entry&action=amconfigdetail&amcid=$amcid");
	}
}elseif($action == 'amconfigsedit'){
	backnav('backarea','config');
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!submitcheck('bsubmit')){
		tabheader("后台管理角色管理 &nbsp;<a href=\"?entry=$entry&action=amconfigadd\" onclick=\"return floatwin('open_amconfigsedit',this)\">>>添加</a>",'amconfigsedit',"?entry=$entry&action=$action",4);
		trcategory(array('序号',array('角色名称','txtL'),array('备注','txtL'),'排序','删除','编辑'));
		$ii = 0;
		foreach($amconfigs as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\">".++$ii."</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\" size=\"20\" maxlength=\"60\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][remark]\" value=\"$v[remark]\" size=\"40\" maxlength=\"50\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
				"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=amconfigdetail&amcid=$k\" onclick=\"return floatwin('open_amconfigsedit',this)\">详情</a></td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
		a_guide('amconfigsedit');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}amconfigs WHERE amcid='$k'");
				unset($fmdata[$k]);
			}
		}
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = empty($v['cname']) ? $amconfigs[$k]['cname'] : $v['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$v['remark'] = trim(strip_tags($v['remark']));
				$db->query("UPDATE {$tblprefix}amconfigs SET cname='$v[cname]',remark='$v[remark]',vieworder='$v[vieworder]' WHERE amcid='$k'");
			}
		}
		adminlog('编辑后台管理角色管理列表');
		cls_CacheFile::Update('amconfigs');
		cls_message::show('后台角色修改完成', "?entry=amconfigs&action=amconfigsedit");
	}
}elseif($action == 'amconfigdetail' && !empty($amcid)){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	empty($amconfigs[$amcid]) && cls_message::show('请指定正确的管理后台角色');
	$amconfig = $amconfigs[$amcid];
	echo "<title>管理角色设置 - $amconfig[cname]</title>";
	$menus = cls_cache::Read('mnmenus');
	if(!submitcheck('bamconfigdetail')){
		foreach(array('menus','funcs','caids','fcaids','mchids','cuids','checks','extends') as $var) $amconfig[$var] = array_filter(explode(',',$amconfig[$var]));
		tabheader('管理后台显示以下菜单&nbsp; &nbsp; &nbsp; <input class="checkbox" type="checkbox" name="mchkall" onclick="checkall(this.form,\'menusnew\',\'mchkall\')">全选','amconfigdetail',"?entry=$entry&action=$action&amcid=$amcid",6);
		if($cocsmenus = cls_cache::exRead('cocsmenus')){
			$na = array();foreach($cocsmenus as $k => $v) $na[$k] = $v['label'];
			trbasic('扩展节点区','',makecheckbox("extendsnew[]",$na,empty($amconfig['extends']) ? array() : $amconfig['extends'],5),'');
		}
		foreach($menus as $k1 => $v1){
			$menusarr = array();foreach($v1['childs'] as $k2 => $v2) $menusarr[$k2] = $v2['title'];
			trbasic($v1['title'],'',makecheckbox("menusnew[]",$menusarr,empty($amconfig['menus']) ? array() : $amconfig['menus'],5),'');
		}
		tabfooter();

		tabheader('拥有以下功能的管理权限&nbsp; &nbsp; &nbsp; <input class="checkbox" type="checkbox" name="fchkall" onclick="checkall(this.form,\'funcsnew\',\'fchkall\')">全选');
		$arr = cls_cache::exRead('amfuncs');
		foreach($arr as $k => $v) trbasic($k,'',makecheckbox("funcsnew[]",$v,empty($amconfig['funcs']) ? array() : $amconfig['funcs'],6),'');
		tabfooter();

		$caidsarr = $cuidsarr = $fcaidsarr = $mchidsarr = array('-1' => '<b>全部</b>');
		tabheader('允许管理以下栏目中的文档&nbsp; &nbsp; &nbsp; <input class="checkbox" type="checkbox" name="cachkall" onclick="checkall(this.form,\'caid0snew\',\'cachkall\')">全选');
		$catalogs = cls_cache::Read('catalogs');
		foreach($catalogs as $k => $v) $caidsarr[$k] = $v['title'].'('.$v['level'].')';
		echo "<tr><td class=\"txt txtleft\">".makecheckbox("caid0snew[]",$caidsarr,empty($amconfig['caids']) ? array() : $amconfig['caids'])."</td><tr>";
		tabfooter();

		tabheader('其它的内容管理权限');
		foreach(array('commus','matypes','mcommus',) as $k) $$k = cls_cache::Read($k);
		$checkarr = array(-1 => '<b>全部</b>','adel' => '删除常规内容','acheck' => '审核常规内容','mdel' => '删除会员','mcheck' => '审核会员','fdel' => '删除副件','fcheck' => '审核副件',);
		trbasic('内容管理权限','',makecheckbox("checksnew[]",$checkarr,empty($amconfig['checks']) ? array() : $amconfig['checks'],7),'',array('guide'=>'常规内容包含文档及交互，副件包含广告等，会员包含各类商家'));
		foreach($commus as $k => $v) $cuidsarr[$k] = $v['cname'];
		$fcaidsarr += cls_fcatalog::fcaidsarr();
		$mchidsarr += cls_mchannel::mchidsarr();
		trbasic('允许管理以下分类的副件<br /><input class="checkbox" type="checkbox" name="fachkall" onclick="checkall(this.form,\'fcaidsnew\',\'fachkall\')">全选','',makecheckbox('fcaidsnew[]',$fcaidsarr,empty($amconfig['fcaids']) ? array() : $amconfig['fcaids'],5,1),'');
		trbasic('允许管理以下类型的会员<br /><input class="checkbox" type="checkbox" name="mcchkall" onclick="checkall(this.form,\'mchidsnew\',\'mcchkall\')">全选','',makecheckbox('mchidsnew[]',$mchidsarr,empty($amconfig['mchids']) ? array() : $amconfig['mchids'],8,1),'');
		trbasic('允许管理以下交互内容<br /><input class="checkbox" type="checkbox" name="ychkall" onclick="checkall(this.form,\'cuidsnew\',\'ychkall\')">全选','',makecheckbox('cuidsnew[]',$cuidsarr,empty($amconfig['cuids']) ? array() : $amconfig['cuids'],8,1),'');
		tabfooter('bamconfigdetail');
		a_guide('amconfigsedit');
	}else{
		$extendsnew = empty($extendsnew) ? '' : implode(',',$extendsnew);
		$menusnew = empty($menusnew) ? '' : implode(',',$menusnew);
		$funcsnew = empty($funcsnew) ? '' : implode(',',$funcsnew);
		$checksnew = empty($checksnew) ? '' : (in_array('-1',$checksnew) ? '-1' : implode(',',$checksnew));
		foreach(array('caid0snew','fcaidsnew','mchidsnew','cuidsnew',) as $var) $$var = empty($$var) ? '' : (in_array('-1',$$var) ? '-1' : implode(',',$$var));
		$db->query("UPDATE {$tblprefix}amconfigs SET
		menus='$menusnew',
		funcs='$funcsnew',
		checks='$checksnew',
		extends='$extendsnew',
		caids='$caid0snew',
		fcaids='$fcaidsnew',
		cuids='$cuidsnew',
		mchids='$mchidsnew'
		WHERE amcid='$amcid'");
		adminlog('详细修改后台管理角色');
		cls_CacheFile::Update('amconfigs');
		cls_message::show('管理后台角色设置完成',axaction(6,"?entry=amconfigs&action=amconfigsedit"));
	}
}elseif($action == 'amconfigcaedit'){
	backnav('backarea','caedit');
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	tabheader('后台管理节点区设置');
	trbasic('常规内容区','',"<a href=\"?entry=$entry&action=amconfigablock\" onclick=\"return floatwin('open_fnodes',this)\">>>节点设置</a> &nbsp; &nbsp;<a href=\"?entry=$entry&action=amconfigmdflink&linktype=a\" onclick=\"return floatwin('open_fnodes',this)\">>>管理链接</a>",'');
	trbasic('会员内容区','',"<a href=\"?entry=$entry&action=amconfigmblock\" onclick=\"return floatwin('open_mnodes',this)\">>>节点设置</a> &nbsp; &nbsp;<a href=\"?entry=$entry&action=amconfigmdflink&linktype=m\" onclick=\"return floatwin('open_fnodes',this)\">>>管理链接</a>",'');
	tabfooter();
	a_guide('amconfigcaedit');
}elseif($action == 'amconfigablock'){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	$catalogs = cls_cache::Read('catalogs');
	$anodes = @unserialize($db->result_one("SELECT content FROM {$tblprefix}variables WHERE variable='anodes' LIMIT 1"));
	empty($anodes) && $anodes = array();
	if(!submitcheck('bsubmit')){
		echo form_str('amconfigablock', "?entry=amconfigs&action=amconfigablock");
		echo "<div class=\"conlist1\">常规管理区节点设置</div>";
		$catalogs = array('-1' => array('title' => '全部栏目','level' => 0)) + $catalogs;
		echo '<script type="text/javascript">var cata = [';
		foreach($catalogs as $caid => $catalog){
			$aurlstr = '';
			$tcaid = $caid == -1 ? 0 : $caid;
			if(empty($anodes[$tcaid])){
				$aurlstr = '无效节点';
			}else{
				$aurlsarr = explode(',',$anodes[$tcaid]);
				foreach($aurlsarr as $k) isset($aurls[$k]['name']) && $aurlstr .= ($aurlstr ? ',' : '').$k.'-'.@$aurls[$k]['name'];
			}
			
			echo "[$catalog[level],$caid,'" . str_replace("'", "\\'", mhtmlspecialchars($catalog['title'])) . "','$aurlstr'],";
		}
		empty($treesteps) && $treesteps = '';
		echo <<<DOT
];
document.write(tableTree({data:cata,step:'$treesteps'.split(',')[0],html:{
		head: '<td class="txtC" width="40"><input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form,\'selectid\',\'chkall\')"></td>'
			+ '<td class="txtL" width="240"%code%><b>内容管理节点</b> %input%</td>'
			+ '<td class="txtL"><b>节点管理链接</b></td>',
		cell:[1,1],
		rows: '<td class="txtC" width="40"><input class="checkbox" name="selectid['
				+ '%1%]" value="%1%" type="checkbox" onclick="tableTree.setChildBox()" /></td>'
			+ '<td width="240" class="txtL">%ico%%2%</td>'
			+ '<td class="txtL">%3%</td>'
		},
	callback : true
}));
DOT;
		echo '</script>';

		tabheader("操作项目 &nbsp; &nbsp;<a href=\"?entry=$entry&action=amconfigmdflink&linktype=a\" onclick=\"return floatwin('open_alinks',this)\">>>管理链接设置</a>");
		$aurlsarr = array();
		foreach($aurls as $k => $v) $v['type'] == 'a' && $aurlsarr[$v['auid']] = "$v[auid]-<b title=\"$v[mark]\">$v[name]</b>" . ($v['mark'] ? "-$v[mark]" : '');
		//要按[排序]设置为准, 把功能类似的放在一起；
		//ksort($aurlsarr); // 如果这样,单纯找ID要方便些。
		if(empty($aurlsarr)){
			$str = "<a href=\"?entry=$entry&action=amconfigaddlink&linktype=a\" onclick=\"return floatwin('open_alinks',this)\">>>添加管理链接</a>";
		}else{
			$str = "<b>设置模式</b>：<select id=\"select_mode\" name=\"select_mode\" style=\"vertical-align: top;\">".makeoption(array(0 => '重新设置',1 => '加入所选',2 => '移除所选',))."</select><br>";
			$str .= makecheckbox('arcauids[]',$aurlsarr,array(),1);
		}
		trbasic('设置节点的管理链接','', $str,'');
		tabfooter('bsubmit'); 
		a_guide('amconfigblock');
	}else{
		
		if(!empty($selectid)){
			$arcauids = empty($arcauids) ? array() : $arcauids;
			foreach($selectid as $id){
				$tid = $id == -1 ? 0 : $id;
				$old_ids = empty($anodes[$tid]) ? array() : explode(',',$anodes[$tid]);
				$new_ids =	empty($select_mode) ? $arcauids : ($select_mode == 1 ? array_filter(array_merge($old_ids,$arcauids)) : array_diff($old_ids,$arcauids));			
				$new_ids = array_filter($new_ids);
				if($new_ids){
					$new_ids = array_unique($new_ids);
					$anodes[$tid] = implode(',',$new_ids);
				}else unset($anodes[$tid]);
			}
		}
		foreach($anodes as $k => $v) if($k && empty($catalogs[$k])) unset($anodes[$k]);
		$anodes = empty($anodes) ? '' : addslashes(serialize($anodes));
		$db->query("UPDATE {$tblprefix}variables SET
		content='$anodes'
		WHERE variable='anodes'");
		adminlog('详细修改后台管理节点区');
		cls_CacheFile::Update('linknodes');
		cls_message::show('后台管理节点区设置完成',M_REFERER);
	}
}elseif($action == 'amconfigmblock'){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	$mnodes = unserialize($db->result_one("SELECT content FROM {$tblprefix}variables WHERE variable='mnodes' LIMIT 1"));
	if(!submitcheck('bsubmit')){
		$mchannels = cls_cache::Read('mchannels');
		$mchidsarr = array(0 => '全部模型') + cls_mchannel::mchidsarr();
		tabheader('会员内容区节点设置','amconfigmblock','?entry=amconfigs&action=amconfigmblock',6);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('会员模型节点','txtL'),array('节点管理链接','txtL')));
		foreach($mchidsarr as $mchid => $title){
			$aurlstr = '';
			if(empty($mnodes[$mchid])){
				$aurlstr = '无效节点';
			}else{
				$aurlsarr = explode(',',$mnodes[$mchid]);
				foreach($aurlsarr as $k) isset($aurls[$k]['name']) && $aurlstr .= ($aurlstr ? ',' : '').$k.'-'.@$aurls[$k]['name'];
			}
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$mchid]\" value=\"$mchid\"></td>\n".
				"<td class=\"txtL\">$title</td>\n".
				"<td class=\"txtL\">$aurlstr</td>\n".
				"</tr>\n";
		}
		tabfooter();
		tabheader("操作项目 &nbsp; &nbsp;<a href=\"?entry=amconfigs&action=amconfigmdflink&linktype=m\" onclick=\"return floatwin('open_alinks',this)\">>>节点管理链接</a>");
		$aurlsarr = array();
		foreach($aurls as $k => $v)$v['type'] == 'm' && $aurlsarr[$v['auid']] = "$v[auid]-<b title=\"$v[mark]\">$v[name]</b>" . ($v['mark'] ? "-$v[mark]" : '');
		if(empty($aurlsarr)){
			$str = "<a href=\"?entry=amconfigs&action=amconfigaddlink&linktype=f\" onclick=\"return floatwin('open_alinks',this)\">>>添加管理链接</a>";
		}else{
			$str = "<b>设置模式</b>：<select id=\"select_mode\" name=\"select_mode\" style=\"vertical-align: top;\">".makeoption(array(0 => '重新设置',1 => '加入所选',2 => '移除所选',))."</select><br>";
			$str .= makecheckbox('arcauids[]',$aurlsarr,array(),1);
		}
		trbasic('设置节点的管理链接','', $str,'');
		tabfooter('bsubmit');
		a_guide('amconfigblock');
	}else{
		if(!empty($selectid)){
			$arcauids = empty($arcauids) ? array() : $arcauids;
			foreach($selectid as $id){
				$old_ids = empty($mnodes[$id]) ? array() : explode(',',$mnodes[$id]);
				$new_ids =	empty($select_mode) ? $arcauids : ($select_mode == 1 ? array_filter(array_merge($old_ids,$arcauids)) : array_diff($old_ids,$arcauids));			
				$new_ids = array_filter($new_ids);
				if($new_ids){
					$new_ids = array_unique($new_ids);
					$mnodes[$id] = implode(',',$new_ids);
				}else unset($mnodes[$id]);
			}
		}
		
		foreach($mnodes as $k => $v) if($k && empty($mchannels[$k])) unset($mnodes[$k]);
		$mnodes = empty($mnodes) ? '' : addslashes(serialize($mnodes));
		$db->query("UPDATE {$tblprefix}variables SET
		content='$mnodes'
		WHERE variable='mnodes'");
		adminlog('详细修改后台管理节点区');
		cls_CacheFile::Update('linknodes');
		cls_message::show('后台管理节点区设置完成',M_REFERER);
	}
}elseif($action == 'amconfigaddlink'){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	deep_allow($no_deepmode);
	if(!submitcheck('bsubmit')){
		(empty($linktype) || !in_array($linktype, array('a','m'))) && cls_message::show('无效的linktype参数');
		if(!empty($auid)){
			$linkitem = $db->fetch_one("SELECT * FROM {$tblprefix}aurls WHERE auid='$auid' AND type='$linktype' LIMIT 1");
			$linkitem || cls_message::show('无效的参数');
		}
		tabheader((empty($auid) ? '添加' : '修改') . '管理链接', 'amconfigeditlink',"?entry=amconfigs&action=$action&linktype=$linktype" . (empty($linkitem['auid']) ? '' : "&auid=$linkitem[auid]"),6);
		trbasic('链接名称','linkitemnew[name]', empty($linkitem['name']) ? '' : $linkitem['name'], 'text', array('validate' => " rule=\"must\" rev=\"链接名称\""));
		trbasic('链接地址','linkitemnew[link]',empty($linkitem['link']) ? '?entry=脚本&action=区块' : $linkitem['link'],'text',array('guide'=>'请不要包含变化的ID值参数，如栏目模型等','w'=>50,'validate' => " rule=\"must\" rev=\"链接地址\""));
		trbasic('链接备注','linkitemnew[mark]',empty($linkitem['mark']) ? '' : $linkitem['mark'],'textarea');
		tabfooter('bsubmit');
	}else{
		$sql = "name='$linkitemnew[name]',link='$linkitemnew[link]',mark='$linkitemnew[mark]'";
		if(empty($auid)){
			$sql = "INSERT INTO {$tblprefix}aurls SET auid=".auto_insert_id('aurls').",type='$linktype',$sql";
		}else{
			$sql = "UPDATE {$tblprefix}aurls SET $sql WHERE auid='$auid' LIMIT 1";
		}
		$db->query($sql);
		cls_CacheFile::Update('aurls');
		cls_message::show('管理链接' . (empty($auid) ? '添加' : '修改') . '成功！',axaction(6,"?entry=amconfigs&action=amconfigmdflink&linktype=$linktype"));
	}
}elseif($action == 'amconfigmdflink'){
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!submitcheck('bsubmit')){
		$where = empty($linktype) ? '' : "type='$linktype'";
		$where && $where = " WHERE $where";
		echo form_str($actionid.'linkitemfilter',"?entry=$entry&action=$action&linktype=$linktype");

		tabheader($linkitemtype[$linktype].'管理'. "&nbsp;&nbsp;<a href=\"?entry=amconfigs&action=amconfigaddlink&linktype=$linktype\" onclick=\"return floatwin('open_alink',this)\">添加管理链接</a>");
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">", '链接ID', '名字|L', '链接|L', '排序', '管理'));
		$query = $db->query("SELECT * FROM {$tblprefix}aurls $where ORDER BY vieworder ASC");
		while($row = $db->fetch_array($query)){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$row[auid]]\" value=\"$row[auid]\"></td>\n".
				"<td class=\"txtC\">$row[auid]</td>\n".
				"<td class=\"txtL\">$row[name]</td>\n".
				"<td class=\"txtL\">$row[link]</td>\n".
				"<td class=\"txtC w40\"><input class=\"w40\" type=\"text\" name=\"vieworder[$row[auid]]\" value=\"$row[vieworder]\"></td>\n".
				"<td class=\"txtC\"><a href=\"?entry=amconfigs&action=amconfigaddlink&linktype=$linktype&auid=$row[auid]\" onclick=\"return floatwin('open_alink',this)\">修改</a></td>\n".
				"</tr>\n";
		}
		tabfooter();

		//操作区
		tabheader('操作项目');
		trbasic('选择操作项目','', "<input class=\"checkbox\" type=\"checkbox\" name=\"linkdeal[delete]\" id=\"linkdeal[delete]\" value=\"1\" onclick=\"deltip(this,$no_deepmode)\"><label for=\"linkdeal[delete]\">删除</label> &nbsp;",'');
		tabfooter('bsubmit');
		a_guide('amconfigablock');
	}else{
		empty($vieworder) && $vieworder = array();
		if(!empty($linkdeal) && !empty($selectid)){
			foreach($selectid as $auid){
				if(!empty($linkdeal['delete']) && deep_allow($no_deepmode)){
					unset($vieworder[$auid]);
					$db->query("DELETE FROM {$tblprefix}aurls WHERE auid='$auid' "); //LIMIT 1,执行escape_old_sql()后加limit不能通过
				}
			}
		}
		asort($vieworder);
		$index = 0;
		foreach($vieworder as $k => $v){
			$db->query("UPDATE {$tblprefix}aurls SET vieworder=" . ++$index . " WHERE auid='$k' LIMIT 1");
		}
		cls_CacheFile::Update('aurls');
		cls_message::show('批量操作完成',M_REFERER);		
	}

}
?>