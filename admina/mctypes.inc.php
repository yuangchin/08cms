<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
$mctypes = fetch_arr();
$mcmodearr = array(0 => '普通',1 => '手机',);
if($action == 'mctypesedit'){
	backnav('mchannel','mctype');
	if(!submitcheck('bmctypesedit')){
		$str = " &nbsp; &nbsp;>><a href=\"?entry=$entry&action=mctypeadd\" onclick=\"return floatwin('open_mctypesedit',this)\">添加认证类型</a>";
		$str .= " &nbsp; &nbsp;>><a href=\"?entry=sms_admin&action=setapi&isframe=1\" target=\"_blank\">手机参数设置</a>";
		tabheader("认证类型管理$str",'mctypesedit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID','启用','类型名称|L','模式','备注|L','图标','排序','删除','设置'));
		foreach($mctypes as $k => $v){
			$modestr = @$mcmodearr[$v['mode']];
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"mctypesnew[$k][available]\" value=\"1\"".($v['available'] ? " checked" : "")."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"15\" maxlength=\"30\" name=\"mctypesnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w30\">$modestr</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"mctypesnew[$k][remark]\" value=\"$v[remark]\"></td>\n".
				"<td class=\"txtC\"><img src=\"$v[icon]\" border=\"0\" onload=\"if(this.height>20) {this.resized=true; this.height=20;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"mctypesnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=mctypedel&mctid=$k\">删除</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=mctypedetail&mctid=$k\" onclick=\"return floatwin('open_mctypesedit',this)\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bmctypesedit','修改');
		a_guide('mctypesedit');
	}else{
		if(!empty($mctypesnew)){
			foreach($mctypesnew as $k => $v){
				$v['available'] = empty($v['available']) ? 0 : 1;
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $mctypes[$k]['cname'];
				$v['remark'] = trim(strip_tags($v['remark']));
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$db->query("UPDATE {$tblprefix}mctypes SET cname='$v[cname]',remark='$v[remark]',vieworder='$v[vieworder]',available='$v[available]' WHERE mctid='$k'");
			}
			adminlog('编辑认证类型列表');
			cls_CacheFile::Update('mctypes');
		}
		cls_message::show('认证类型编辑完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'mctypeadd'){
	if(!submitcheck('bsubmit')){
		tabheader('添加认证类型','mctypeadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('类型名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,3,30)));
		trbasic('备注','fmdata[remark]','','text',array('w'=>50));
		trbasic('认证模式','',makeradio('fmdata[mode]',$mcmodearr),'',array('guide'=>'选择后不可更改。'));
		tabfooter('bsubmit','添加');
	}else{
		!($fmdata['cname'] = trim(strip_tags($fmdata['cname']))) && cls_message::show('请输入认证类型名称');
		$fmdata['remark'] = trim(strip_tags($fmdata['remark']));
		$db->query("INSERT INTO {$tblprefix}mctypes SET 
					mctid=".auto_insert_id('mctypes').",
					cname='$fmdata[cname]', 
					remark='$fmdata[remark]',
					mode='$fmdata[mode]'
					");
		if($mctid = $db->insert_id()){
			$db->query("ALTER TABLE {$tblprefix}members ADD mctid$mctid smallint(6) unsigned NOT NULL default 0", 'SILENT');
			cls_CacheFile::Update('mctypes');
		}
		adminlog('添加认证类型-'.$fmdata['cname']);
		cls_message::show('认证类型添加成功，请以此类型进行详细设置。',"?entry=$entry&action=mctypedetail&mctid=$mctid");
	}

}elseif($action == 'mctypedetail' && $mctid){
	$mctype = fetch_one($mctid);
	if(!submitcheck('bsubmit')){
		tabheader($mctype['cname'].'-基本设置','mctypedetail',"?entry=$entry&action=$action&mctid=$mctid",2,1,1);
		trbasic('类型名称','fmdata[cname]',$mctype['cname'],'text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,3,30)));
		trbasic('是否自动审核','fmdata[autocheck]',$mctype['autocheck'],'radio');
		trbasic('是否可自主解审','fmdata[uncheck]',$mctype['uncheck'],'radio');
		if($mctype['mode']){ //
			trbasic('号码是否唯一','fmdata[isunique]',@$mctype['isunique'],'radio');
		}
		trbasic('备注','fmdata[remark]',$mctype['remark'],'text',array('w'=>50));
		$_guide = empty($mctype['mode']) ? '' :  '是否开启<a href="?entry=sms_admin&action=enable&&isframe=1" target="_blank">手机短信认证</a>？请设置[confirm'.$mctid.']会员手机认证相关参数。'; 
		trbasic('认证模式','',$mcmodearr[$mctype['mode']],'',array('guide'=>$_guide)); 
		/*
		if($mctype['mode']){
			$msg = !empty($mctype['msg']) ? $mctype['msg'] : '您的确认码为%s。本信息自动发送，请勿回复。08CMS';
			trspecial('短信内容模版',specialarr(array('type' => 'multitext','varname' => 'fmdata[msg]','value' => $msg,)));
		}*/
		trspecial('认证显示图标',specialarr(array('type' => 'image','varname' => 'fmdata[icon]','value' => $mctype['icon'],)));
		trbasic('认证内容字段','fmdata[field]',$mctype['field'],'text',array('guide'=>'只能添加单个字段，需要是会员模型中存在的字段','validate'=>makesubmitstr('fmdata[field]',1,0,1,30)));
		trbasic('允许以下类型会员认证','',makecheckbox('fmdata[mchids][]',cls_mchannel::mchidsarr(),empty($mctype['mchids']) ? array() : explode(',',$mctype['mchids']),5),'');
		trbasic('奖励积分类型','fmdata[crid]',makeoption(array(0 => '现金') + cridsarr(),$mctype['crid']),'select');
		trbasic('奖励积分值','fmdata[award]',$mctype['award'],'text',array('validate' => " rule=\"int\" min=\"0\"",'w' => 10,));
		tabfooter('bsubmit');
	}else{
		!($fmdata['cname'] = trim(strip_tags($fmdata['cname']))) && cls_message::show('请输入认证类型名称');
		!($fmdata['field'] = trim(strip_tags($fmdata['field']))) && cls_message::show('请输入认证内容字段');
		$fmdata['remark'] = trim(strip_tags($fmdata['remark']));
		$fmdata['msg'] = trim(strip_tags(@$fmdata['msg']));
		$fmdata['award'] = max(0,intval($fmdata['award']));
		$fmdata['icon'] = upload_s($fmdata['icon'],$mctype['icon'],'image');
		if($k = strpos($fmdata['icon'],'#')) $fmdata['icon'] = substr($fmdata['icon'],0,$k);
		$fmdata['mchids'] = empty($fmdata['mchids']) ? '' : implode(',',$fmdata['mchids']);
		$db->update('#__mctypes',array('cname' => "$fmdata[cname]", 'remark'=>"$fmdata[remark]",'msg'=>"$fmdata[msg]",
							'icon'=>"$fmdata[icon]",'field'=>"$fmdata[field]",'mchids'=>"$fmdata[mchids]",
							'crid'=>"$fmdata[crid]",'award'=>"$fmdata[award]",'autocheck'=>"$fmdata[autocheck]",
							'uncheck'=>"$fmdata[uncheck]",'isunique'=>intval(@$fmdata['isunique'])))->where('mctid='.$mctid)->exec();
							
		cls_CacheFile::Update('mctypes');
		adminlog('编辑认证类型-'.$mctype['cname']);
		cls_message::show('类型编辑完成!',axaction(6,"?entry=$entry&action=mctypesedit"));
	}
}elseif($action == 'mctypedel' && $mctid) {
	$mctype = $mctypes[$mctid];
	deep_allow($no_deepmode);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&mctid=$mctid&confirm=ok>删除</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= "放弃请点击>><a href=?entry=$entry&action=mctypesedit>返回</a>";
		cls_message::show($message);
	}
	$db->query("DELETE FROM {$tblprefix}mcerts WHERE mctid='$mctid'",'SILENT');
	$db->query("DELETE FROM {$tblprefix}mctypes WHERE mctid='$mctid'",'SILENT');
	$db->query("ALTER TABLE {$tblprefix}members DROP mctid$mctid", 'SILENT'); 
	adminlog('删除认证类型-'.$mctype['cname']);
	cls_CacheFile::Update('mctypes');
	cls_message::show('指定的认证类型已成功删除。',"?entry=$entry&action=mctypesedit");
}
function fetch_arr(){
	global $db,$tblprefix;
	$rets = array();
	$query = $db->query("SELECT * FROM {$tblprefix}mctypes ORDER BY vieworder,mctid");
	while($r = $db->fetch_array($query)){
		$rets[$r['mctid']] = $r;
	}
	return $rets;
}

function fetch_one($mctid){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}mctypes WHERE mctid='$mctid'");
	return $r;
}

?>
