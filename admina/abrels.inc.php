<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('cfcommu')) cls_message::show($re);
$typearr = array('文档','会员');
if($action == 'abrelsedit'){
	backnav('exconfig','abrel');
	$abrels = fetch_arr();
	if(!submitcheck('babrelsedit')){
		tabheader("合辑项目管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=abreladd\" onclick=\"return floatwin('open_abrelsedit',this)\">".'添加'."</a>",'abrelsedit',"?entry=$entry&action=$action",'7');
		trcategory(array('ID','启用',array('项目名称','txtL'),array('备注','txtL'),'项目类型',array('数据表','txtL'),'删除','编辑'));
		foreach($abrels as $k => $v){
			echo "<tr class=\"txt\">".
			"<td class=\"txtC w30\">$k</td>\n".
			"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"abrelsnew[$k][available]\" value=\"1\"".(empty($v['available']) ? '' : ' checked')."></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"20\" maxlength=\"20\" name=\"abrelsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"40\" maxlength=\"100\" name=\"abrelsnew[$k][remark]\" value=\"$v[remark]\"></td>\n".
			"<td class=\"txtC w100\">".$typearr[$v['source']].'=>'.$typearr[$v['target']]."</td>\n".
			"<td class=\"txtL\">".($v['tbl'] ? $v['tbl'] : ($v['source'] ? 'members' : "archives*".modpro(" >><a href=\"?entry=$entry&action=archivetbl&arid=$k\" onclick=\"return floatwin('open_abrelsedit',this)\">设置</a>")))."</td>\n".
			"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=abreldel&arid=$k\">删除</a></td>\n".
			"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=abreldetail&arid=$k\" onclick=\"return floatwin('open_abrelsedit',this)\">详情</a></td></tr>\n";
		}
		tabfooter('babrelsedit','修改');
		a_guide('abrelsedit');
	}else{
		if(!empty($abrelsnew)){
			foreach($abrelsnew as $k => $v){
				$v['cname'] = empty($v['cname']) ? $abrels[$k]['cname'] : $v['cname'];
				$v['remark'] = empty($v['remark']) ? $abrels[$k]['remark'] : $v['remark'];
				$v['available'] = empty($v['available']) ? 0 : 1;
				$db->query("UPDATE {$tblprefix}abrels SET cname='$v[cname]',remark='$v[remark]',available='$v[available]' WHERE arid='$k'");
			}
		}
		cls_CacheFile::Update('abrels');	
		adminlog('编辑合辑项目列表');
		cls_message::show('合辑项目编辑完成', "?entry=$entry&action=$action");
	}
}elseif($action == 'abreldel' && $arid) {
	backnav('exconfig','abrel');
	deep_allow($no_deepmode);
	if(!($abrel = fetch_one($arid))) cls_message::show('请指定正确的项目');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=\"?entry=$entry&action=$action&arid=$arid&confirm=ok\">删除</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= "放弃请点击>><a href=\"?entry=$entry&action=abrelsedit\">返回</a>";
		cls_message::show($message);
	}
	if($abrel['tbl']){
		$db->query("DELETE FROM {$tblprefix}$abrel[tbl] WHERE arid='$arid'",'SILENT');
	}else{
		$tbl = empty($abrel['source']) ? 'archives' : 'members';
		$db->query("ALTER TABLE {$tblprefix}$tbl DROP pid$arid",'SILENT');
		$db->query("ALTER TABLE {$tblprefix}$tbl DROP inorder$arid",'SILENT');
		$db->query("ALTER TABLE {$tblprefix}$tbl DROP incheck$arid",'SILENT');
	}
	$db->query("DELETE FROM {$tblprefix}abrels WHERE arid='$arid'",'SILENT');
	cls_CacheFile::Del('abfields',$arid);
	adminlog('删除合辑项目'.$abrel['cname']);
	cls_CacheFile::Update('abrels');
	cls_message::show('合辑删除完成',"?entry=$entry&action=abrelsedit");
}elseif($action == 'abreladd'){
	deep_allow($no_deepmode);
	if(!submitcheck('babreladd')){
		tabheader('添加合辑项目','abreladd',"?entry=$entry&action=abreladd");
		trbasic('项目名称','abrelnew[cname]');
		trbasic('备注','abrelnew[remark]','','text',array('w'=>50));
		trbasic('归辑来源类型','',makeradio('abrelnew[source]',$typearr),'',array('guide'=>'输入后不可更改'));
		trbasic('归辑目标类型','',makeradio('abrelnew[target]',$typearr),'',array('guide'=>'输入后不可更改'));
		trbasic('合辑记录数据表','abrelnew[tbl]','','text',array('guide'=>'输入后不可更改，输入表名则系统自动建立用于记录合辑关系的数据表，格式如aalbums_***等<br>留空则文档主表archives或会员表members需要有pid*、incheck*、inorder*来记录合辑关系。'));
		tabfooter('babreladd');
		a_guide('abreladd');
	}else{
		$abrelnew['cname'] = empty($abrelnew['cname']) ? '' : trim(strip_tags($abrelnew['cname']));
		empty($abrelnew['cname']) && cls_message::show('请输入项目名称',M_REFERER);
		$abrelnew['remark'] = empty($abrelnew['remark']) ? '' : trim(strip_tags($abrelnew['remark']));
		if($abrelnew['tbl'] = empty($abrelnew['tbl']) ? '' : trim(strip_tags($abrelnew['tbl']))){
			$tables = array();	
			$query = $db->query("SHOW TABLES FROM $dbname");
			while($r = $db->fetch_row($query)) $tables[] = $r[0];
			if(in_array("{$tblprefix}$abrelnew[tbl]",$tables)) cls_message::show('指定新建数据表已经占用',M_REFERER);
		}
		$db->query("INSERT INTO {$tblprefix}abrels SET arid=".auto_insert_id('abrels').",cname='$abrelnew[cname]',remark='$abrelnew[remark]',source='$abrelnew[source]',target='$abrelnew[target]',tbl='$abrelnew[tbl]'");
		$arid = $db->insert_id();
		if($abrelnew['tbl']){
			$db->query("CREATE TABLE {$tblprefix}$abrelnew[tbl] LIKE {$tblprefix}init_aalbum");
			$db->query("ALTER TABLE {$tblprefix}$abrelnew[tbl] COMMENT='$abrelnew[cname](合辑)关联表'");
		}else{
			if(!empty($abrelnew['source'])){
				$db->query("ALTER TABLE {$tblprefix}members ADD pid$arid mediumint(8) unsigned NOT NULL default '0'");
				$db->query("ALTER TABLE {$tblprefix}members ADD inorder$arid smallint(6) unsigned NOT NULL default '0'");
				$db->query("ALTER TABLE {$tblprefix}members ADD incheck$arid tinyint(1) unsigned NOT NULL default '0'");
			}
		}
		cls_CacheFile::Update('abrels');
		adminlog('添加合辑项目');
		cls_message::show('合辑项目添加成功，请详细配置。',"?entry=$entry&action=abreldetail&arid=$arid");
	}
}elseif($action == 'archivetbl' && $arid){//只分析数据表上是否有该字段
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	if(!($abrel = fetch_one($arid))) cls_message::show('请指定正确的合辑项目。');
	if($abrel['tbl'] || $abrel['source']) cls_message::show('合辑数据表非文档主表。');
	echo "<title>合辑字段应用到文档表</title>";
	foreach(array('channels','splitbls',) as $k) $$k = cls_cache::Read($k);
	if(!submitcheck('bsubmit')){
		tabheader($abrel['cname']."($arid) - 合辑字段应用到主表",'abreldetail',"?entry=$entry&action=$action&arid=$arid");
		trcategory(array('启用','ID',array('文档主表','txtL'),array('数据表','txtL'),array('文档模型','txtL')));
		foreach($splitbls as $k => $v){
			$channelstr = '';foreach($v['chids'] as $x) @$channels[$x]['cname'] && $channelstr .= $channels[$x]['cname']."($x),";
			$query = $db->query("DESCRIBE {$tblprefix}archives$k pid$arid");
			$available = $db->fetch_array($query) ? TRUE : FALSE;
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fmdata[$k][enabled]\" value=\"1\"".($available ? ' checked' : '')."></td>\n".
				"<td class=\"txtC w35\">$k</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtL\">archives$k</td>\n".
				"<td class=\"txtL\">".($channelstr ? $channelstr : '空')."</td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
	}else{
		foreach($splitbls as $k => $v){
			$query = $db->query("DESCRIBE {$tblprefix}archives$k pid$arid");
			$available = $db->fetch_array($query) ? TRUE : FALSE;
			$enabled = empty($fmdata[$k]['enabled']) ? FALSE : TRUE;
			if($enabled != $available){
				if($enabled){
					$db->query("ALTER TABLE {$tblprefix}archives$k ADD pid$arid mediumint(8) unsigned NOT NULL default '0'",'SILENT');
					$db->query("ALTER TABLE {$tblprefix}archives$k ADD inorder$arid smallint(6) unsigned NOT NULL default '0'",'SILENT');
					$db->query("ALTER TABLE {$tblprefix}archives$k ADD incheck$arid tinyint(1) unsigned NOT NULL default '0'",'SILENT');
				}else{
					$db->query("ALTER TABLE {$tblprefix}archives$k DROP pid$arid",'SILENT');
					$db->query("ALTER TABLE {$tblprefix}archives$k DROP inorder$arid",'SILENT');
					$db->query("ALTER TABLE {$tblprefix}archives$k DROP incheck$arid",'SILENT');
				}
			}
		}
		adminlog($abrel['cname']."合辑应用到主表");
		cls_message::show('合辑项目设置完成。',"?entry=$entry&action=$action&arid=$arid");
	}
	
}elseif($action == 'abreldetail' && $arid){
	if(!($abrel = fetch_one($arid))) cls_message::show('请指定正确的合辑项目。');
    _08_FilesystemFile::filterFileParam($arid);
	if(@!include("exconfig/abrel_$arid.php")){
		if(!submitcheck('babreldetail')) {
			tabheader('合辑项目设置-'.$abrel['cname'],'abreldetail',"?entry=$entry&action=$action&arid=$arid");
			trbasic('备注','abrelnew[remark]',$abrel['remark'],'text',array('w'=>50));
			trbasic('设置参数数组'.($abrel['cfgs0'] && !$abrel['cfgs'] ? '输入格式错误，请修正!' : ''),'abrelnew[cfgs0]',empty($abrel['cfgs']) ? (empty($abrel['cfgs0']) ? '' : $abrel['cfgs0']) : var_export($abrel['cfgs'],1),'textarea',array('w' => 500,'h' => 300,'guide'=>'以array()输入，数组内容需要是php规范'));
			trbasic('附加说明','abrelnew[content]',$abrel['content'],'textarea',array('w' => 500,'h' => 300,));
			tabfooter('babreldetail','修改');
			a_guide('abreldetail');
		}else{
			$abrelnew['cfgs0'] = empty($abrelnew['cfgs0']) ? '' : trim($abrelnew['cfgs0']);
			$abrelnew['cfgs'] = varexp2arr($abrelnew['cfgs0']);
			$abrelnew['remark'] = empty($abrelnew['remark']) ? '' : trim(strip_tags($abrelnew['remark']));
			$abrelnew['content'] = empty($abrelnew['content']) ? '' : trim($abrelnew['content']);
			$abrelnew['cfgs'] = !empty($abrelnew['cfgs']) ? addslashes(var_export($abrelnew['cfgs'],TRUE)) : '';
			$db->query("UPDATE {$tblprefix}abrels SET 
						remark='$abrelnew[remark]',
						content='$abrelnew[content]',
						cfgs0='$abrelnew[cfgs0]',
						cfgs='$abrelnew[cfgs]'
						WHERE arid='$arid'");
			cls_CacheFile::Update('abrels');
			adminlog('编辑合辑项目'.$abrel['cname']);
			cls_message::show('合辑项目设置完成。',"?entry=$entry&action=$action&arid=$arid");
		}
	}

}
function fetch_arr(){
	global $db,$tblprefix;
	$rets = array();
	$query = $db->query("SELECT * FROM {$tblprefix}abrels ORDER BY vieworder ASC,arid ASC");
	while($r = $db->fetch_array($query)){
		if(empty($r['cfgs']) || !is_array($r['cfgs'] = @varexp2arr($r['cfgs']))) $r['cfgs'] = array();
		$rets[$r['arid']] = $r;
	}
	return $rets;
}
function fetch_one($arid){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}abrels WHERE arid='$arid'");
	if(empty($r['cfgs']) || !is_array($r['cfgs'] = @varexp2arr($r['cfgs']))) $r['cfgs'] = array();
	return $r;
}
