<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('channel')) cls_message::show($re);
include_once M_ROOT."include/fields.fun.php";
foreach(array('rprojects','commus','cotypes','permissions','splitbls',) as $k) $$k = cls_cache::Read($k);
if($ex = exentry('channels')){
	include($ex);
	entryfooter();
}
$channels = cls_channel::InitialInfoArray();
if($action == 'channeledit'){
	backnav('channel','channel');
	echo _08_HTML::Title('文档模型管理');
	if(!submitcheck('bchanneledit')){
		tabheader("文档模型管理".modpro(" &nbsp; &nbsp;>><a href=\"?entry=$entry&action=channeladd\" onclick=\"return floatwin('open_channeledit',this)\">添加</a>"),'channeledit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID','启用','模型名称|L','备注|L','排序',modpro('删除'),'字段',modpro('类系'),'设置','扩展',));
		foreach($channels as $k => $channel){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"channelnew[$k][available]\" value=\"1\"".($channel['available'] ? " checked" : "")."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"15\" maxlength=\"30\" name=\"channelnew[$k][cname]\" value=\"$channel[cname]\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"channelnew[$k][remark]\" value=\"$channel[remark]\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"channelnew[$k][vieworder]\" value=\"$channel[vieworder]\"></td>\n".
				modpro("<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=channeldel&chid=$k\">删除</a></td>\n").
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=channelfields&chid=$k\" onclick=\"return floatwin('open_channeledit',this)\">字段</a></td>\n".
				modpro("<td class=\"txtC w30\"><a href=\"?entry=$entry&action=channelcotypes&chid=$k\" onclick=\"return floatwin('open_channeledit',this)\">类系</a></td>\n").
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=channeldetail&chid=$k\" onclick=\"return floatwin('open_channeledit',this)\">设置</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=channeladv&chid=$k\" onclick=\"return floatwin('open_channeledit',this)\">高级</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bchanneledit','修改');
		a_guide('channeledit');
	}else{
		if(isset($channelnew)){
			foreach($channelnew as $k => $v){
				$v['available'] = isset($v['available']) ? $v['available'] : 0;
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $channels[$k]['cname'];
				$v['remark'] = trim(strip_tags($v['remark']));
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$db->query("UPDATE {$tblprefix}channels SET cname='$v[cname]',remark='$v[remark]',vieworder='$v[vieworder]',available='$v[available]' WHERE chid='$k'");
			}
			adminlog('编辑文档模型列表');
			cls_CacheFile::Update('channels');
		}
		cls_message::show('文档模型编辑完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'channeladd'){
	echo _08_HTML::Title('添加文档模型');
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	deep_allow($no_deepmode);
	if(!submitcheck('bsubmit')){
		$submitstr = '';
		tabheader('添加文档模型',$action,"?entry=$entry&action=$action",2,0,1);
		trbasic('模型名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,3,30)));
		trbasic('指定文档主表','fmdata[stid]',makeoption(array(0 => '新增主表') + stidsarr()),'select',array('guide' => '设置后不可变更，请谨慎选择。在数据库中文档主表格式如"archives*"(*为主表ID)。'));
		trbasic('新增主表名称','fmdata[stname]','','text',array('validate'=>makesubmitstr('fmdate[stname]',0,0,3,30),'guide' => '如留空则默认为模型名称。'));
		trbasic('备注说明','fmdata[remark]','','text',array('w'=>50));
		tabfooter('bsubmit','添加');
		a_guide('channeladd');
	}else{
		!($fmdata['cname'] = trim(strip_tags($fmdata['cname']))) && cls_message::show('请输入文档模型名称');
		$fmdata['remark'] = trim(strip_tags($fmdata['remark']));
		$stid = max(0,intval(@$fmdata['stid']));
		$fmdata['stname'] = trim(strip_tags($fmdata['stname']));
		$fmdata['stname'] || $fmdata['stname'] = $fmdata['cname'];
		$newstid = false;
		if(!$stid){
			$db->query("INSERT INTO {$tblprefix}splitbls SET stid = ".auto_insert_id('splitbls').",cname='$fmdata[stname]'");
			if(!($stid = $db->insert_id())) cls_message::show('新增文档主表不成功。');
			$newstid = true;
			$db->query("CREATE TABLE {$tblprefix}archives$stid LIKE {$tblprefix}init_archives");
			$db->query("ALTER TABLE {$tblprefix}archives$stid COMMENT='$fmdata[stname](文档)主表'");
		}
		$db->query("INSERT INTO {$tblprefix}channels SET
				   	chid = ".auto_insert_id('channels').",
					cname='$fmdata[cname]',
					stid='$stid',
					remark='$fmdata[remark]'
					");
		if($chid = $db->insert_id()){
			$db->query("CREATE TABLE {$tblprefix}archives_$chid (
						aid mediumint(8) unsigned NOT NULL default '0',
						PRIMARY KEY (aid))".(mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM DEFAULT CHARSET=$dbcharset" : " TYPE=MYISAM"));
			$db->query("UPDATE {$tblprefix}splitbls SET chids=CONCAT(chids,',$chid,') WHERE stid='$stid'");
			$db->query("ALTER TABLE {$tblprefix}archives_$chid COMMENT='$fmdata[stname](文档)模型表'");
			$arcinitfields = cls_cache::cacRead('arcinitfields','',1);
			foreach($arcinitfields as $kk => $vv){
				$sqlstr = "ename='$kk',`type`='a',tpid='$chid',iscommon=1,available=1,tbl='archives$stid'";
				foreach($vv as $k => $v) $sqlstr .= ",`$k`='".addslashes($v)."'";
				$db->query("INSERT INTO {$tblprefix}afields SET $sqlstr");
			}
			cls_CacheFile::Update('splitbls');
			cls_CacheFile::Update('channels');
			cls_CacheFile::Update('fields',$chid);
			adminlog('添加文档模型-'.$fmdata['cname']);
			cls_message::show('文档模型添加成功，请对此模型进行详细设置。',"?entry=$entry&action=channeldetail&chid=$chid");
		}else{
			$newstid && $db->query("DELETE FROM {$tblprefix}splitbls WHERE stid='$stid'");
			$db->query("DROP TABLE {$tblprefix}archives$stid");
			cls_message::show('文档模型添加不成功。');
		}
	}

}elseif($action == 'channeldel' && $chid && isset($channels[$chid])) {
	$channel = $channels[$chid];
	echo _08_HTML::Title("删除文档模型 - 模型ID：$chid");
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	deep_allow($no_deepmode);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&chid=$chid&confirm=ok>删除</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= "放弃请点击>><a href=?entry=$entry&action=channeledit>返回</a>";
		cls_message::show($message);
	}
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl($channel['stid'],1)." WHERE chid='$chid'")){
		cls_message::show('删除该模型，请先删除所有与该模型相关的文档。',"?entry=$entry&action=channeledit");
	}
	$db->query("DROP TABLE IF EXISTS {$tblprefix}archives_$chid",'SILENT');
	$db->query("DELETE FROM {$tblprefix}channels WHERE chid='$chid'",'SILENT');
	cls_fieldconfig::DeleteOneSourceFields('channel',$chid);

	//如果所在主表没有不再被其它模型使用时，则自动删除
	$db->query("UPDATE {$tblprefix}splitbls SET chids=REPLACE(chids,',$chid,',',') WHERE stid='$channel[stid]'",'SILENT');
	if($db->result_one("SELECT chids FROM {$tblprefix}splitbls WHERE stid='$channel[stid]'")==','){
		$db->query("DROP TABLE IF EXISTS {$tblprefix}archives".$channel['stid']."",'SILENT');
		$db->query("DELETE FROM {$tblprefix}splitbls WHERE stid='$channel[stid]'",'SILENT');
	}

	//清除相关缓存
	cls_CacheFile::Update('channels');
	cls_CacheFile::Update('splitbls');
	adminlog('删除文档模型-'.$channel['cname']);
	cls_message::show('指定的文档模型已成功删除。',"?entry=$entry&action=channeledit");
}elseif($action == 'channeldetail' && $chid){
	!($channel = cls_channel::InitialOneInfo($chid)) && cls_message::show('指定的文档模型不存在。');
	echo _08_HTML::Title("文档模型基本设置 - $channel[cname]");
	if(!submitcheck('bchanneldetail')){
		tabheader($channel['cname'].'-基本设置','channeldetail',"?entry=$entry&action=channeldetail&chid=$chid");
		setPermBar('内容发布权限设置', 'channelnew[apmid]', @$channel['apmid'] ,'aadd', 'open', '选择权限方案，则方案中允许的会员才能发布本模型文档。');
        setPermBar('自动审核权限设置', 'channelnew[autocheck]', @$channel['autocheck'], 'chk', 'check', '选择权限方案，则方案中允许的会员发布的文档会自动审核，其余需手动审核。');
        trbasic('内容页自动生成静态','channelnew[autostatic]',$channel['autostatic'],'radio',array('guide' => '选择是，则文档添加或编辑完成后,自动更新该文档的内容静态页。'));        
		trbasic('关闭内容页静态自动更新','channelnew[noautostatic]',$channel['noautostatic'],'radio',array('guide' => '静态自动更新比较占用服务器资源，请根据页面情况选择。'));
		$dmin = intval(@$channel['click_defmin']);
		$dmax = intval(@$channel['click_defmax']);
		$defclick = "<input type='text' size='8' id='channelnew[click_defmin]' name='channelnew[click_defmin]' value='$dmin' rule='int' must='0' regx='' min='' max='' rev=''> ";
		$defclick .= "<input type='text' size='8' id='channelnew[click_defmax]' name='channelnew[click_defmax]' value='$dmax' rule='int' must='0' regx='' min='' max='' rev=''>";
		trbasic('默认点击数','',$defclick,'',array('guide' => '添加文档时，默认的默认点击数；系统会在此设置区间随机设置一个数值。'));
		tabfooter('bchanneldetail');
	}else{
		$db->query("UPDATE {$tblprefix}channels SET
			apmid='$channelnew[apmid]',
			click_defmin='$channelnew[click_defmin]',
			click_defmax='$channelnew[click_defmax]',
			autocheck='$channelnew[autocheck]',
			autostatic='$channelnew[autostatic]',
			noautostatic='$channelnew[noautostatic]'
			WHERE chid='$chid'");
		cls_CacheFile::Update('channels');
		adminlog('编辑文档模型-'.$channel['cname']);
		cls_message::show('模型编辑完成!',axaction(6,"?entry=$entry&action=channeledit"));
	}
}elseif($action == 'channeladv' && $chid){
	!($channel = cls_channel::InitialOneInfo($chid)) && cls_message::show('指定的文档模型不存在。');
	echo _08_HTML::Title("文档模型高级扩展 - $channel[cname]");
	if(@!include("exconfig/channel_$chid.php")){
		if(!submitcheck('bchanneldetail')){
			tabheader($channel['cname'].'-高级扩展设置','channeldetail',"?entry=$entry&action=channeladv&chid=$chid");
			trbasic('设置参数数组'.($channel['cfgs0'] && !$channel['cfgs'] ? '<br>输入格式错误，请更正!' : ''),'channelnew[cfgs0]',empty($channel['cfgs']) ? (empty($channel['cfgs0']) ? '' : $channel['cfgs0']) : var_export($channel['cfgs'],TRUE),'textarea',array('w' => 500,'h' => 300,'guide'=>'以array()输入，数组内容需要是php规范'));
			trbasic('附加说明','channelnew[content]',$channel['content'],'textarea',array('w' => 500,'h' => 300,));
			tabfooter('bchanneldetail');
		}else{
			$channelnew['cfgs0'] = empty($channelnew['cfgs0']) ? '' : trim($channelnew['cfgs0']);
			$channelnew['cfgs'] = varexp2arr($channelnew['cfgs0']);
			$channelnew['content'] = empty($channelnew['content']) ? '' : trim($channelnew['content']);
			$channelnew['cfgs'] = !empty($channelnew['cfgs']) ? addslashes(var_export($channelnew['cfgs'],TRUE)) : '';
			$db->query("UPDATE {$tblprefix}channels SET
						content='$channelnew[content]',
						cfgs0='$channelnew[cfgs0]',
						cfgs='$channelnew[cfgs]'
						WHERE chid='$chid'");
			cls_CacheFile::Update('channels');
			adminlog('编辑文档模型-'.$channel['cname']);
			cls_message::show('模型编辑完成!',axaction(6,"?entry=$entry&action=channeledit"));
		}
	}
}elseif($action == 'channelcotypes' && $chid){//只分析数据表上是否有该字段
	!($channel = cls_channel::InitialOneInfo($chid)) && cls_message::show('指定的文档模型不存在。');
	echo _08_HTML::Title("类系字段 - $channel[cname]");
	if(!($stid = $channel['stid']) || empty($splitbls[$stid])) cls_message::show('模型没有指定分表');
	$nowtbl = "archives$stid";
	if(!submitcheck('bsubmit')){
		tabheader($channel['cname']." - 类系字段 - 所在主表$nowtbl",'channeldetail',"?entry=$entry&action=$action&chid=$chid");
		trcategory(array('启用','ID',array('类系名称','txtL'),'自动',array('数据字段','txtL'),array('备注','txtL'),'多选','期限',));
		foreach($cotypes as $k => $v){
			$fieldstr = $v['self_reg'] ? '-' : "ccid$k".($v['emode'] ? ",ccid{$k}date" : '');
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fmdata[$k][available]\" value=\"1\"".(in_array($k,@$splitbls[$stid]['coids']) ? ' checked' : '')."></td>\n".
				"<td class=\"txtC w35\">$k</td>\n".
				"<td class=\"txtL w120\">".mhtmlspecialchars($v['cname'])."</td>\n".
				"<td class=\"txtC w40\">".($v['self_reg'] ? 'Y' : '-')."</td>\n".
				"<td class=\"txtL w120\">$fieldstr</td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars(@$v['remark'])."</td>\n".
				"<td class=\"txtC w40\">".($v['asmode'] ? $v['asmode'] : '-')."</td>\n".
				"<td class=\"txtC w40\">".($v['emode'] ? 'Y' : '-')."</td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
	}else{
		$coids = array();
		foreach($cotypes as $k => $v){
			$available = empty($fmdata[$k]['available']) ? 0 : 1;
			$available && $coids[] = $k;
			if(!$v['self_reg']){
				if(in_array($k,@$splitbls[$stid]['coids']) != $available){
					if($available){
						if($v['asmode']){
							$db->query("ALTER TABLE {$tblprefix}$nowtbl ADD ccid$k varchar(255) NOT NULL default ''",'SILENT');
						}else{
							$db->query("ALTER TABLE {$tblprefix}$nowtbl ADD ccid$k smallint(6) unsigned NOT NULL default 0",'SILENT');
							@$v['emode'] && $db->query("ALTER TABLE {$tblprefix}$nowtbl ADD ccid{$k}date int(10) unsigned NOT NULL default 0 AFTER ccid$k",'SILENT');
						}
					}else{
						$db-> query("ALTER TABLE {$tblprefix}$nowtbl DROP ccid$k",'SILENT');
						$db-> query("ALTER TABLE {$tblprefix}$nowtbl DROP ccid{$k}date",'SILENT');
					}
				}
			}
		}
		@sort($coids);
		$coids = empty($coids) ? '' : implode(',',$coids);
		$db->query("UPDATE {$tblprefix}splitbls SET coids='$coids' WHERE stid='$stid'");
		cls_CacheFile::Update('splitbls');
		adminlog('编辑'.$channel['cname'].'类系字段列表');
		cls_message::show('模型编辑完成!',"?entry=$entry&action=$action&chid=$chid");
	}
}elseif($action == 'channelfields' && $chid){
	!($channel = cls_channel::InitialOneInfo($chid)) && cls_message::show('指定的文档模型不存在。');
	$fields = cls_fieldconfig::InitialFieldArray('channel',$chid);
	echo _08_HTML::Title("字段管理 - $channel[cname]");
	if(!submitcheck('bchanneldetail')){
		tabheader($channel['cname']."-字段管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=fieldone&chid=$chid\" onclick=\"return floatwin('open_fielddetail',this)\">添加字段</a>",'channeldetail',"?entry=$entry&action=$action&chid=$chid");
		trcategory(array('启用','字段名称|L','排序','字段标识|L','数据表|L','字段类型','删除','编辑'));
		foreach($fields as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '').(!empty($v['issystem']) ? ' disabled' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
				"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars($k)."</td>\n".
				"<td class=\"txtL\">$v[tbl]</td>\n".
				"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(empty($v['iscustom']) ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"")."></td>\n".
				"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=fieldone&chid=$chid&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a></td>\n".
				"</tr>";
		}
		tabfooter();
		tabheader($channel['cname'].'-字段相关管理');
		$abstractarr = $thumbarr = $keywordsarr = $newsarr = $sizearr = $letterarr = array('0' => '不设置');
		foreach($fields as $k => $v){
			if($v['available']){
				($k!='abstract') && in_array($v['datatype'],array('multitext','htmltext')) && $abstractarr[$k] = $v['cname'].' '.$k;
				($k!='thumb') && in_array($v['datatype'],array('image','images','multitext','htmltext')) && $thumbarr[$k] = $v['cname'].' '.$k;
				($k!='keywords') && in_array($v['datatype'],array('multitext','htmltext')) && $keywordsarr[$k] = $v['cname'].' '.$k;
				in_array($v['datatype'],array('multitext','htmltext')) && $newsarr[$k] = $v['cname'].' '.$k;
				in_array($v['datatype'],array('image','flash','media','file','images','flashs','medias','files',)) && $sizearr[$k] = $v['cname'].' '.$k;
				$v['datatype'] == 'text' && $letterarr[$k] = $v['cname'].' '.$k;
			}
		}
		trbasic('自动首字母来源字段','channelnew[autoletter]',makeoption($letterarr,$channel['autoletter']),'select');
		trbasic('自动摘要来源字段','channelnew[autoabstract]',makeoption($abstractarr,$channel['autoabstract']),'select');
		trbasic('自动缩略图来源字段','channelnew[autothumb]',makeoption($thumbarr,$channel['autothumb']),'select');
		trbasic('自动关键词来源字段','channelnew[autokeyword]',makeoption($keywordsarr,$channel['autokeyword']),'select');
		trbasic('全文搜索来源字段','channelnew[fulltxt]',makeoption($newsarr,$channel['fulltxt']),'select');
		tabfooter('bchanneldetail');
	}else{
		if(!empty($delete)){
			$deleteds = cls_fieldconfig::DeleteField('channel',$chid,$delete);
			foreach($deleteds as $k){
				unset($fieldsnew[$k]);
			}
		}
		if(!empty($fieldsnew)){
			foreach($fieldsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $fields[$k]['cname'];
				$v['available'] = $fields[$k]['issystem'] || !empty($v['available']) ? 1 : 0;
				$v['vieworder'] = max(0,intval($v['vieworder']));
				cls_fieldconfig::ModifyOneConfig('channel',$chid,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('channel',$chid);
		
		$db->query("UPDATE {$tblprefix}channels SET
			autoletter='$channelnew[autoletter]',
			autoabstract='$channelnew[autoabstract]',
			autokeyword='$channelnew[autokeyword]',
			autothumb='$channelnew[autothumb]',
			fulltxt='$channelnew[fulltxt]'
			WHERE chid='$chid'");
		cls_CacheFile::Update('channels');
		adminlog('编辑'.$channel['cname'].'字段列表');
		cls_message::show('模型编辑完成!',"?entry=$entry&action=$action&chid=$chid");
	}
}elseif($action == 'fieldone'){
	cls_FieldConfig::EditOne('channel',@$chid,@$fieldname);

}