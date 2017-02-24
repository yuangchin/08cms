<?PHP
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('cfcommu')) cls_message::show($re);
include_once M_ROOT."include/fields.fun.php";
if($action == 'commusedit'){
	backnav('exconfig','commu');
	$commus = cls_commu::InitialInfoArray();
	if(!submitcheck('bcommusedit')){
		tabheader('交互项目管理'."&nbsp; &nbsp; >><a href=\"?entry=$entry&action=commuadd\" onclick=\"return floatwin('open_commusedit',this)\">".'添加'."</a>",'commusedit',"?entry=$entry&action=$action",'7');
		trcategory(array('ID','启用',array('项目名称','txtL'),array('备注','txtL'),array('数据表','txtL'),'删除','字段','编辑'));
		foreach($commus as $cuid => $commu){
			echo "<tr class=\"txt\">".
			"<td class=\"txtC w30\">$cuid</td>\n".
			"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"commusnew[$cuid][available]\" value=\"1\"".(empty($commu['available']) ? '' : ' checked')."></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"20\" maxlength=\"20\" name=\"commusnew[$cuid][cname]\" value=\"$commu[cname]\"></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"50\" maxlength=\"100\" name=\"commusnew[$cuid][remark]\" value=\"$commu[remark]\"></td>\n".
			"<td class=\"txtL\">$commu[tbl]</td>\n".
			"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=commudel&cuid=$cuid\">删除</a></td>\n".
			"<td class=\"txtC w30\">".(!$commu['tbl'] ? '-' : "<a href=\"?entry=$entry&action=commufields&cuid=$cuid\" onclick=\"return floatwin('open_commusedit',this)\">字段</a>")."</td>\n".
			"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=commudetail&cuid=$cuid\" onclick=\"return floatwin('open_commusedit',this)\">详情</a></td></tr>\n";
		}
		tabfooter('bcommusedit','修改');
	}else{
		if(!empty($commusnew)){
			foreach($commusnew as $k => $v){
				$v['cname'] = empty($v['cname']) ? $commus[$k]['cname'] : $v['cname'];
				$v['remark'] = empty($v['remark']) ? $commus[$k]['remark'] : $v['remark'];
				$v['available'] = empty($v['available']) ? 0 : 1;
				$db->query("UPDATE {$tblprefix}acommus SET cname='$v[cname]',remark='$v[remark]',available='$v[available]' WHERE cuid='$k'");
			}
		}
		cls_CacheFile::Update('commus');
		adminlog('编辑交互项目列表');
		cls_message::show('交互项目编辑完成', "?entry=$entry&action=$action");
	}
}elseif($action == 'commudel' && $cuid) {
	backnav('exconfig','commu');
	deep_allow($no_deepmode,"?entry=$entry&action=commusedit");
	if(!($commu = cls_commu::InitialOneInfo($cuid))) cls_message::show('请指定正确的项目');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&cuid=$cuid&confirm=ok>删除</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= "放弃请点击>><a href=?entry=$entry&action=commusedit>返回</a>";
		cls_message::show($message);
	}
	$commu['tbl'] && $db->query("DROP TABLE IF EXISTS {$tblprefix}$commu[tbl]",'SILENT');
	cls_fieldconfig::DeleteOneSourceFields('commu',$cuid);
	$db->query("DELETE FROM {$tblprefix}acommus WHERE cuid='$cuid'",'SILENT');
	cls_CacheFile::Update('commus');
	
	adminlog('删除交互项目'.$commu['cname']);
	cls_message::show('删除交互项目完成',"?entry=$entry&action=commusedit");
}elseif($action == 'commuadd'){
	deep_allow($no_deepmode);
	if(!submitcheck('bcommuadd')){
		tabheader('添加交互项目','commuadd',"?entry=$entry&action=commuadd");
		trbasic('项目名称','communew[cname]');
		trbasic('备注','communew[remark]','','text',array('w'=>50));
		trbasic('交互记录数据表','communew[tbl]');
		tabfooter('bcommuadd');
	}else{
		$communew['cname'] = empty($communew['cname']) ? '' : trim(strip_tags($communew['cname']));
		empty($communew['cname']) && cls_message::show('标识资料不完全',M_REFERER);
		$communew['remark'] = empty($communew['remark']) ? '' : trim(strip_tags($communew['remark']));
		if($communew['tbl'] = empty($communew['tbl']) ? '' : trim(strip_tags($communew['tbl']))){
			  $db->query("CREATE TABLE {$tblprefix}$communew[tbl] (
			  cid mediumint(8) unsigned NOT NULL auto_increment,
			  mid mediumint(8) unsigned NOT NULL default '0',
			  mname varchar(15) NOT NULL default '',
			  createdate int(10) unsigned NOT NULL default '0',
			  checked tinyint(1) unsigned NOT NULL default '0',
			  ucid mediumint(8) unsigned NOT NULL default '0',
			  PRIMARY KEY (cid))".(mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM DEFAULT CHARSET=$dbcharset" : " TYPE=MYISAM"));
		}
		$db->query("INSERT INTO {$tblprefix}acommus SET cuid=".auto_insert_id('acommus').",cname='$communew[cname]',remark='$communew[remark]',tbl='$communew[tbl]'");
		$cuid = $db->insert_id();
		if($communew['tbl']){
			$db->query("ALTER TABLE {$tblprefix}$communew[tbl] ADD cuid smallint(6) unsigned NOT NULL default '$cuid' AFTER ucid");
			$db->query("ALTER TABLE {$tblprefix}$communew[tbl] COMMENT='$communew[cname](交互)表'");
		}
		cls_CacheFile::Update('commus');
		adminlog('添加交互项目');
		cls_message::show('交互项目添加成功，请详细配置。', axaction(36, "?entry=$entry&action=commudetail&cuid=$cuid"));
	}
}elseif($action == 'commudetail' && $cuid){
	if(!($commu = cls_commu::InitialOneInfo($cuid))) cls_message::show('请指定正确的交互项目。');
	if(@!include("exconfig/commu_$cuid.php")){
		if(!submitcheck('bcommudetail')) {
			tabheader('交互项目设置-'.$commu['cname'],'commudetail',"?entry=$entry&action=$action&cuid=$cuid");
			trbasic('备注','communew[remark]',$commu['remark'],'text',array('w'=>50));
			trbasic('设置参数数组'.($commu['cfgs0'] && !$commu['cfgs'] ? '输入格式错误，请修正!' : ''),'communew[cfgs0]',empty($commu['cfgs']) ? (empty($commu['cfgs0']) ? '' : $commu['cfgs0']) : var_export($commu['cfgs'],1),'textarea',array('w' => 500,'h' => 300,'guide'=>'以array()输入，数组内容需要是php规范'));
			trbasic('附加说明','communew[content]',$commu['content'],'textarea',array('w' => 500,'h' => 300,));
			tabfooter('bcommudetail','修改');
		}else{
			$communew['cfgs0'] = empty($communew['cfgs0']) ? '' : trim($communew['cfgs0']);
			$communew['cfgs'] = varexp2arr($communew['cfgs0']);
			$communew['remark'] = empty($communew['remark']) ? '' : trim(strip_tags($communew['remark']));
			$communew['content'] = empty($communew['content']) ? '' : trim($communew['content']);
			$communew['cfgs'] = !empty($communew['cfgs']) ? addslashes(var_export($communew['cfgs'],TRUE)) : '';
			$db->query("UPDATE {$tblprefix}acommus SET
						remark='$communew[remark]',
						content='$communew[content]',
						cfgs0='$communew[cfgs0]',
						cfgs='$communew[cfgs]'
						WHERE cuid='$cuid'");
			cls_CacheFile::Update('commus');
			adminlog('编辑交互项目'.$commu['cname']);
			cls_message::show('交互项目设置完成。',axaction(36, "?entry=$entry&action=$action&cuid=$cuid"));
		}
	}

}elseif($action == 'commufields' && $cuid){
	if(!($commu = cls_commu::InitialOneInfo($cuid))) cls_message::show('请指定正确的项目');
	if(!$commu['tbl']) cls_message::show('指定的交互项目没有指定记录表。');
	$fields = cls_fieldconfig::InitialFieldArray('commu',$cuid);
	if(!submitcheck('bcommudetail')){
		tabheader($commu['cname']."-字段管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=fieldone&cuid=$cuid\" onclick=\"return floatwin('open_fielddetail',this)\">添加字段</a>",'commudetail',"?entry=$entry&action=$action&cuid=$cuid");
		trcategory(array('有效',array('字段名称','txtL'),'排序',array('字段标识','txtL'),array('数据表','txtL'),'字段类型','删除','编辑'));
		foreach($fields as $k => $v){
		echo "<tr class=\"txt\">\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '')."></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
			"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
			"<td class=\"txtL\">".mhtmlspecialchars($k)."</td>\n".
			"<td class=\"txtL\">$v[tbl]</td>\n".
			"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
			"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=fieldone&cuid=$cuid&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a></td>\n".
			"</tr>";
		}
		tabfooter('bcommudetail');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			$deleteds = cls_fieldconfig::DeleteField('commu',$cuid,$delete);
			foreach($deleteds as $k){
				unset($fieldsnew[$k]);
			}
		}
		if(!empty($fieldsnew)){
			foreach($fieldsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = !$v['cname'] ? $fields[$k]['cname'] : $v['cname'];
				$v['available'] = empty($v['available']) ? 0 : 1;
				$v['vieworder'] = max(0,intval($v['vieworder']));
				cls_fieldconfig::ModifyOneConfig('commu',$cuid,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('commu',$cuid);
		
		adminlog('编辑交互项目'.$commu['cname'].'字段列表');
		cls_message::show('交互项目字段编辑完成。',"?entry=$entry&action=$action&cuid=$cuid");
	}
}elseif($action == 'fieldone' && $cuid){
	cls_FieldConfig::EditOne('commu',@$cuid,@$fieldname);

}
