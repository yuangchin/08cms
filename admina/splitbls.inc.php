<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('channel')) cls_message::show($re);
foreach(array('channels','cotypes',) as $k) $$k = cls_cache::Read($k);
$splitbls = fetch_arr();
if(empty($action)){
	backnav('channel','dbsplit');
	tabheader("文档主表管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=splitbls\" onclick=\"return floatwin('open_splitbls',this)\">管理主表</a>",'',"",'10');
	trcategory(array('模型ID',array('文档模型','txtL'),array('主表ID','txtL'),array('主表名称','txtL'),array('数据表名称','txtL'),modpro('字段')));
	foreach($channels as $k => $v){
		$splitstr = empty($v['stid']) ? '系统表' : $splitbls[$v['stid']]['cname'];
		$tblstr = 'archives'.(empty($v['stid']) ? '' : $v['stid']);
		echo "<tr class=\"txt\">".
			"<td class=\"txtC w60\">$k</td>\n".
			"<td class=\"txtL\">".mhtmlspecialchars($v['cname'])."</td>\n".
			"<td class=\"txtL w60\">$v[stid]</td>\n".
			"<td class=\"txtL\">$splitstr</td>\n".
			"<td class=\"txtL\">$tblstr</td>\n".
			modpro("<td class=\"txtC w40\"><a href=\"?entry=$entry&action=clearfields&stid=$v[stid]&chid=$k\" onclick=\"return floatwin('open_splitbls',this)\">设置</a></td>\n").
			"</tr>\n";
	}
	tabfooter();
	a_guide('channelsplit');
}elseif($action == 'splitbls'){
	if(!submitcheck('bsubmit')){
		tabheader("分表管理",'splitbls',"?entry=$entry&action=$action",'10');
		trcategory(array('分表ID',array('分表名称','txtL'),array('数据表','txtL'),'排序','关闭静态',array('文档模型','txtL'),'删除',));
		foreach($splitbls as $k => $v){
			$channelstr = '';foreach($v['chids'] as $x) @$channels[$x]['cname'] && $channelstr .= $channels[$x]['cname']."($x),";
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w60\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"15\" maxlength=\"30\" name=\"fmdata[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtL\">archives$k</td>\n".
				"<td class=\"txtC w80\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w60\"><input class=\"checkbox\" type=\"checkbox\" name=\"fmdata[$k][nostatic]\" value=\"1\"".($v['nostatic'] ? " checked" : "")."></td>\n".
				"<td class=\"txtL\">".($channelstr ? $channelstr : '空')."</td>\n".
				"<td class=\"txtC w30\">".($channelstr ? '-' : "<a onclick=\"return deltip()\" href=\"?entry=$entry&action=del&stid=$k\">删除</a>")."</td>\n".
				"</tr>\n";
		}
		tabfooter('bsubmit');
		a_guide('splitbls');
	}else{
		if(isset($fmdata)){
			foreach($fmdata as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $splitbls[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$v['nostatic'] = empty($v['nostatic']) ? 0 : 1;
				$db->query("UPDATE {$tblprefix}splitbls SET cname='$v[cname]',vieworder='$v[vieworder]',nostatic='$v[nostatic]' WHERE stid='$k'");
			}
			adminlog('编辑分表管理列表');
			cls_CacheFile::Update('splitbls');
		}
		cls_message::show('分表管理编辑完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'del' && $stid){
	$splitbl = $splitbls[$stid];
	echo "<title>删除文档主表 - 主表ID：$stid</title>";
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&stid=$stid&confirm=ok>删除</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= "放弃请点击>><a href=?entry=$entry&action=splitbls>返回</a>";
		cls_message::show($message);
	}
	foreach($splitbl['chids'] as $k => $v) if(empty($channels[$v])) unset($splitbl['chids'][$k]); 
	empty($splitbl['chids']) || cls_message::show('当前分表关联了文档模型，不能删除。',"?entry=$entry&action=splitbls");
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}archives$stid")) cls_message::show('删除分表，请先删除分表中所有文档信息。',"?entry=$entry&action=splitbls");
	$db->query("DROP TABLE IF EXISTS {$tblprefix}archives$stid",'SILENT');
	$db->query("DELETE FROM {$tblprefix}splitbls WHERE stid='$stid'",'SILENT');
	//清除相关缓存
	adminlog('删除文档分表-'.$splitbl['cname']);
	cls_CacheFile::Update('splitbls');
	cls_message::show('指定的文档分表已成功删除。',"?entry=$entry&action=splitbls");
}elseif($action == 'clearfields' && $stid && $chid){
	//用于清理一些不需要用的属性字段，直接操作数据库，后台架构的字段与类系不在些范围
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	if(!($splitbl = $splitbls[$stid])) cls_message::show('指定的文档主表不存在。');
	if(!($fields = cls_cache::Read('fields',$chid))) cls_message::show('请指定文档模型。');
	echo "<title>清理多余字段 - 文档主表：archives$stid</title>";
	$nowtbl = "archives$stid";
	
	$fieldsarr = array();
	$query = $db->query("SHOW FULL COLUMNS FROM {$tblprefix}$nowtbl");
	while($field = $db->fetch_array($query)) $fieldsarr[] = $field;	
	
	$nodels = array('aid','arctpls','caid','chid','clicks','checked','color','createdate','customurl',
	'dpmid','editor','editorid','enddate','fsalecp','initdate','jumpurl','letter','from_mid','from_mname',
	'mclicks','mid','mname','needstatics','nowurl','downs','mdowns','wdowns','plays','mplays','wplays',
	'refreshdate','relatedaid','rpmid','salecp','subject','tid','ucid','updatedate','vieworder','wclicks',
	);
	// 保护afields基本字段
	$qaf = $db->query("SELECT ename FROM {$tblprefix}afields WHERE tbl='".atbl($stid)."'");
	while($field = $db->fetch_array($qaf)) $nodels[] = $field['ename']; //print_r($nodels);	 
	foreach($cotypes as $k => $v){
		$nodels[] = "ccid$k";
		$nodels[] = "ccid{$k}date";
	}
	foreach($fields as $k => $v){
		$nodels[] = $k;
		if($v['datatype'] == 'map'){
			$nodels[] = "{$k}_0";
			$nodels[] = "{$k}_1";
		}
	}
	if(!submitcheck('bsubmit')){
		tabheader($splitbl['cname']." - 字段清理 - 所在主表$nowtbl",'channeldetail',"?entry=$entry&action=$action&stid=$stid&chid=$chid");
		trcategory(array('删除','序号',array('标识','txtL'),array('字段属性','txtL')));
		foreach($fieldsarr as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$v[Field]]\" value=\"$v[Field]\"".(in_array($v['Field'],$nodels) ? ' disabled' : '')." onclick=\"deltip()\"></td>\n".
				"<td class=\"txtC w35\">$k</td>\n".
				"<td class=\"txtL w120\">".mhtmlspecialchars($v['Field'])."</td>\n".
				"<td class=\"txtL\">$v[Type]</td>\n".
				"</tr>";
		}
		tabfooter('bsubmit','删除');
		a_guide('clearfields');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				if(!in_array($k,$nodels)){
					$db->query("ALTER TABLE {$tblprefix}$nowtbl DROP $k",'SILENT');
				}
			}
		}
		adminlog('清理'.$nowtbl.'数据表字段');
		cls_message::show('字段清理完成!',"?entry=$entry&action=$action&stid=$stid&chid=$chid");
	}
}
function fetch_arr(){
	$do = cls_cache::exRead('cachedos',1);
	return cls_DbOther::CacheArray($do['splitbls']);
}
?>
