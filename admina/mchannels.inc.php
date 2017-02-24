<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
include_once M_ROOT."include/fields.fun.php";
foreach(array('rprojects','cotypes',) as $k) $$k = cls_cache::Read($k);
if($action == 'mchannelsedit'){
	backnav('mchannel','channel');
	$mchannels = cls_mchannel::InitialInfoArray();
	if(!submitcheck('bmchannelsedit')){
		tabheader("会员模型管理&nbsp; &nbsp; >><a href=\"?entry=mchannels&action=mchanneladd\" onclick=\"return floatwin('open_mchanneledit',this)\">添加</a> &nbsp; &nbsp;<a href=\"?entry=amconfigs&action=amconfigmblock\" onclick=\"return floatwin('open_fnodes',this)\">>>后台节点</a>",'mchanneledit','?entry=mchannels&action=mchannelsedit','10');
		trcategory(array('ID','有效','模型名称|L','删除','字段','编辑','管理'));
		foreach($mchannels as $k => $mchannel){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"mchannelnew[$k][available]\" value=\"1\"".($mchannel['available'] ? " checked" : "").($mchannel['issystem'] ? ' disabled' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"mchannelnew[$k][cname]\" value=\"$mchannel[cname]\"></td>\n".
				"<td class=\"txtC w30\">".($mchannel['issystem'] ? '-' : "<a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=mchannels&action=mchanneldel&mchid=$k\">删除</a>")."</td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=mchannels&action=mchannelfields&mchid=$k\" onclick=\"return floatwin('open_mchanneledit',this)\">字段</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=mchannels&action=mchanneldetail&mchid=$k\" onclick=\"return floatwin('open_mchanneledit',this)\">详情</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=mchannels&action=mchanneladv&mchid=$k\" onclick=\"return floatwin('open_mchanneledit',this)\">高级</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bmchannelsedit','修改');
		a_guide('mchannelsedit');
	}else{
		if(isset($mchannelnew)){
			foreach($mchannelnew as $k => $v) {
				$v['available'] = isset($v['available']) ? $v['available'] : 0;
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $mchannels[$k]['cname'];
				if(($v['cname'] != $mchannels[$k]['cname']) || ($v['available'] != $mchannels[$k]['available'])) {
					$db->query("UPDATE {$tblprefix}mchannels SET cname='$v[cname]', available='$v[available]' WHERE mchid='$k'");
				}
			}
			adminlog('编辑会员模型列表');
			cls_CacheFile::Update('mchannels');
			cls_message::show('会员模型编辑完成',"?entry=mchannels&action=mchannelsedit");
		}
	}
}elseif($action == 'mchanneladd'){
	if(!submitcheck('bmchanneladd')){
		tabheader('添加会员模型','mchanneladd','?entry=mchannels&action=mchanneladd',2,0,1);
		trbasic('会员模型名称','mchanneladd[cname]','','text',array('validate'=>makesubmitstr('mchanneladd[cname]',1,0,3,30)));
		tabfooter('bmchanneladd','添加');
	}else{
		$mchanneladd['cname'] = trim(strip_tags($mchanneladd['cname']));
		empty($mchanneladd['cname']) && cls_message::show('资料不完全', '?entry=mchannels&action=mchanneledit');
		$db->query("INSERT INTO {$tblprefix}mchannels SET mchid=".auto_insert_id('mchannels').",cname='$mchanneladd[cname]'");
		if($mchid = $db->insert_id()){
			$db->query("CREATE TABLE {$tblprefix}members_$mchid (
						mid mediumint(8) unsigned NOT NULL default '0',
						PRIMARY KEY (mid))".(mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM DEFAULT CHARSET=$dbcharset" : " TYPE=MYISAM"));

			$query = $db->query("SELECT * FROM {$tblprefix}afields WHERE type='m' AND tpid='0' ORDER BY vieworder,fid");
			while($r = $db->fetch_array($query)){
				$sqlstr = "tpid='$mchid'";
				foreach($r as $k => $v) if(!in_array($k,array('fid','tpid'))) $sqlstr .= ",`$k`='".addslashes($v)."'";
				$db->query("INSERT INTO {$tblprefix}afields SET $sqlstr");
			}
			cls_CacheFile::Update('mchannels');
			cls_CacheFile::Update('mfields',$mchid);
		}
		adminlog('添加会员模型');
		cls_message::show('会员模型添加完成',"?entry=mchannels&action=mchanneldetail&mchid=$mchid");
	}

}elseif($action == 'mchanneldetail' && $mchid) {
	!($mchannel = cls_mchannel::InitialOneInfo($mchid)) && cls_message::show('指定的会员模型不存在。');
	if(!submitcheck('bmchanneldetail')){
		$autocheckarr = array(0 => '手动审核',1 => '自动审核',2 => 'Email激活');
		tabheader("[$mchannel[cname]]".'会员模型设置','mchanneldetail','?entry=mchannels&action=mchanneldetail&mchid='.$mchid,'4');
		trbasic('注册会员审核方式','',makeradio('mchannelnew[autocheck]',$autocheckarr,$mchannel['autocheck']),'');
		tabfooter('bmchanneldetail');
		a_guide('mchanneldetail');
	}else{
		$db->query("UPDATE {$tblprefix}mchannels SET
			autocheck='$mchannelnew[autocheck]'
			WHERE mchid='$mchid'");
		adminlog('详细修改会员模型');
		cls_CacheFile::Update('mchannels');
		cls_message::show('模型修改完成', '?entry=mchannels&action=mchanneldetail&mchid='.$mchid);
	}
}elseif($action == 'mchanneladv' && $mchid){
	!($mchannel = cls_mchannel::InitialOneInfo($mchid)) && cls_message::show('指定的文档模型不存在。');
	if(@!include("mchannels/mchannel_$mchid.php")){
		if(!submitcheck('bmchanneldetail')){
			tabheader($mchannel['cname'].'-高级扩展设置','mchanneldetail',"?entry=$entry&action=mchanneladv&mchid=$mchid");
			trbasic('设置参数数组'.($mchannel['cfgs0'] && !$mchannel['cfgs'] ? '<br>输入格式错误，请更正!' : ''),'mchannelnew[cfgs0]',empty($mchannel['cfgs']) ? (empty($mchannel['cfgs0']) ? '' : $mchannel['cfgs0']) : var_export($mchannel['cfgs'],1),'textarea',array('w' => 500,'h' => 300,'guide'=>'以array()输入，数组内容需要是php规范'));
			trbasic('附加说明','mchannelnew[content]',$mchannel['content'],'textarea',array('w' => 500,'h' => 300,));
			tabfooter('bmchanneldetail');
		}else{
			$mchannelnew['cfgs0'] = empty($mchannelnew['cfgs0']) ? '' : trim($mchannelnew['cfgs0']);
			$mchannelnew['cfgs'] = varexp2arr($mchannelnew['cfgs0']);
			$mchannelnew['content'] = empty($mchannelnew['content']) ? '' : trim($mchannelnew['content']);
			$mchannelnew['cfgs'] = !empty($mchannelnew['cfgs']) ? addslashes(var_export($mchannelnew['cfgs'],TRUE)) : '';
			$db->query("UPDATE {$tblprefix}mchannels SET
						content='$mchannelnew[content]',
						cfgs0='$mchannelnew[cfgs0]',
						cfgs='$mchannelnew[cfgs]'
						WHERE mchid='$mchid'");
			cls_CacheFile::Update('mchannels');
			adminlog('编辑文档模型-'.$mchannel['cname']);
			cls_message::show('模型编辑完成!',"?entry=$entry&action=mchanneladv&mchid=$mchid");
		}
	}
}elseif($action == 'mchanneldel' && $mchid) {
	deep_allow($no_deepmode);
	$mchannel = $mchannels[$mchid];
	if($mchannel['issystem']) cls_message::show('系统模型不能删除', '?entry=mchannels&action=mchannelsedit');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= '确认请点击：'."[<a href=?entry=mchannels&action=mchanneldel&mchid=$mchid&confirm=ok>删除</a>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= '放弃请点击：'."[<a href=?entry=mchannels&action=mchannelsedit>返回</a>]";
		cls_message::show($message);
	}
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}members WHERE mchid='$mchid'")){
		cls_message::show('模型没有相关联的会员才能删除', '?entry=mchannels&action=mchannelsedit');
	}
	$customtable = 'members_'.$mchid;
	$db->query("DROP TABLE IF EXISTS {$tblprefix}$customtable",'SILENT');
	$db->query("DELETE FROM {$tblprefix}mchannels WHERE mchid='$mchid'",'SILENT');
	cls_fieldconfig::DeleteOneSourceFields('mchannel',$mchid);
	
	//清除相关缓存
	adminlog('删除会员模型');
	cls_CacheFile::Update('mchannels');
	cls_message::show('会员模型删除完成',"?entry=mchannels&action=mchannelsedit");
}elseif($action == 'fieldone'){
	cls_FieldConfig::EditOne('mchannel',@$mchid,@$fieldname);

}elseif($action == 'mchannelfields' && $mchid) {
	!($mchannel = cls_mchannel::InitialOneInfo($mchid)) && cls_message::show('指定的会员模型不存在。');
	$fields = cls_fieldconfig::InitialFieldArray('mchannel',$mchid);
	if(!submitcheck('bmchanneldetail')){
		tabheader($mchannel['cname'].'-'.'字段管理'."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;>><a href=\"?entry=mchannels&action=fieldone&mchid=$mchid\" onclick=\"return floatwin('open_fielddetail',this)\">添加字段</a>",'mchanneldetail','?entry=mchannels&action=mchannelfields&mchid='.$mchid,'8');
		trcategory(array('启用','字段名称','排序','字段标识','字段类型','数据表|L','删除','编辑'));
		foreach($fields as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '').(!empty($v['issystem']) ? ' disabled' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
				"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC\">".mhtmlspecialchars($k)."</td>\n".
				"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"<td class=\"txtL\">".$v['tbl']."</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(!empty($v['iscommon']) ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"")."></td>\n".
				"<td class=\"txtC w50\">".($v['issystem'] ? '-' : "<a href=\"?entry=$entry&action=fieldone&mchid=$mchid&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a>")."</td>\n".
				"</tr>";
		}
		tabfooter();
		tabheader($mchannel['cname'].'-字段相关管理');
		$letterarr = array('0' => '不设置');
		foreach($fields as $k => $v){
			if($v['available']){
				$v['datatype'] == 'text' && $letterarr[$k] = $v['cname'];
			}
		}
		trbasic('自动首字母来源字段','mchannelnew[autoletter]',makeoption($letterarr,$mchannel['autoletter']),'select');
		tabfooter('bmchanneldetail');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			$deleteds = cls_fieldconfig::DeleteField('mchannel',$mchid,$delete);
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
				cls_fieldconfig::ModifyOneConfig('mchannel',$mchid,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('mchannel',$mchid);
		
		$db->query("UPDATE {$tblprefix}mchannels SET
			autoletter='$mchannelnew[autoletter]'
			WHERE mchid='$mchid'");
		adminlog('详细修改会员模型');
		cls_CacheFile::Update('mchannels');
		cls_message::show('模型修改完成', '?entry=mchannels&action=mchannelfields&mchid='.$mchid);
	}
}elseif($action == 'initmfieldsedit'){
	backnav('mchannel','field');
	$fields = cls_fieldconfig::InitialFieldArray('mchannel',0);
	if(!submitcheck('binitmfieldsedit')){
		tabheader('会员通用字段管理'."&nbsp; &nbsp; >><a href=\"?entry=mchannels&action=fieldone\" onclick=\"return floatwin('open_fielddetail',this)\">添加</a>",'initmfieldsedit','?entry=mchannels&action=initmfieldsedit','5');
		trcategory(array('序号','字段名称|L','字段标识','字段类型','数据表|L','删除','编辑'));
		$ii = 0;
		foreach($fields as $k => $v) {
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\">".++$ii."</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
				"<td class=\"txtC\">".mhtmlspecialchars($k)."</td>\n".
				"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"<td class=\"txtL\">".$v['tbl']."</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(empty($v['iscustom']) ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"")."></td>\n".
				"<td class=\"txtC w60\">".($v['issystem'] ? '-' : "<a href=\"?entry=$entry&action=fieldone&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a>")."</td>\n".
				"</tr>";
		}
		tabfooter('binitmfieldsedit');
		a_guide('initmfieldsedit');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			$deleteds = cls_fieldconfig::DeleteField('mchannel',0,$delete);
			foreach($deleteds as $k){
				unset($fieldsnew[$k]);
			}
		}
		foreach($fieldsnew as $k => $v){
			$v['cname'] = trim($v['cname']) ? trim($v['cname']) : $fields[$k]['cname'];
			cls_fieldconfig::ModifyOneConfig('mchannel',0,$v,$k);
		}
		cls_fieldconfig::UpdateCache('mchannel',0);
		
		adminlog('编辑会员通用信息字段管理列表');
		cls_message::show('字段编辑完成','?entry=mchannels&action=initmfieldsedit');
	}
}
