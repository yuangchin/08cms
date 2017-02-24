<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('freeinfo')) cls_message::show($re);
include_once M_ROOT."include/fields.fun.php";
$rprojects = cls_cache::Read('rprojects');
if($action == 'fchannelsedit') {
	backnav('fchannel','channel');
	$fchannels = cls_fchannel::InitialInfoArray();
	if(!submitcheck('bsubmit')) {
		$TitleStr = "副件模型管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=fchanneladd\" onclick=\"return floatwin('open_fchanneldetail',this)\">添加模型</a>";
		tabheader($TitleStr,'fchannelsedit',"?entry=$entry&action=$action",'4');
		trcategory(array('ID','模型名称|L','删除','字段',));
		foreach($fchannels as $k => $v) {
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"fchannelnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w40\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=fchanneldel&chid=$k\">删除</a></td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=$entry&action=fchanneldetail&chid=$k\" onclick=\"return floatwin('open_fchannelsedit',this)\">字段</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bsubmit');
		a_guide('fchannelsedit');
	}else{
		if(isset($fchannelnew)) {
			foreach($fchannelnew as $k => $v) {
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $fchannels[$k]['cname'];
				$fchannels[$k]['cname'] = $v['cname'];
			}
			adminlog('编辑副件模型管理列表');
			cls_fchannel::SaveInitialCache($fchannels);
		}
		cls_message::show('副件模型编辑完成',"?entry=$entry&action=$action");
	}
}elseif($action =='fchanneladd'){
	echo _08_HTML::Title('添加副件模型');
	if(!submitcheck('bsubmit')){
		tabheader('添加副件模型','fmdata',"?entry=$entry&action=$action",2,0,1);
		trbasic('模型名称','fmdata[cname]','','text',array('validate' => makesubmitstr('fmdata[cname]',1,0,3,30)));
		tabfooter('bsubmit','添加');
	}else{
		$fmdata['cname'] = trim(strip_tags($fmdata['cname']));
		if(empty($fmdata['cname'])) cls_message::show('模型名称不完全',M_REFERER);
		if($chid = cls_fchannel::AddOne($fmdata)){
			adminlog('添加副件模型');
			cls_message::show('副件模型添加成功，请对此模型进行详细设置。',axaction(36,"?entry=$entry&action=fchanneldetail&chid=$chid"));
		}else{
			cls_message::show('副件模型添加不成功。');
		}
	}

}elseif($action == 'fchanneldel' && $chid){
	
	backnav('fchannel','channel');
	deep_allow($no_deepmode);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= '确认请点击：'."[<a href=?entry=$entry&action=$action&chid=$chid&confirm=ok>删除</a>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= '放弃请点击：'."[<a href=?entry=$entry&action=fchannelsedit>返回</a>]";
		cls_message::show($message);
	}
	
	cls_fchannel::DeleteOne($chid);

	adminlog('删除副件模型');
	cls_message::show('副件模型删除完成',"?entry=$entry&action=fchannelsedit");

}elseif($action == 'fchanneldetail' && $chid) {
	!($fchannel = cls_fchannel::InitialOneInfo($chid)) && cls_message::show('指定的模型不存在。');
	$fields = cls_FieldConfig::InitialFieldArray('fchannel',$chid);
	echo _08_HTML::Title($fchannel['cname'].'- 字段管理');
	if(!submitcheck('bsubmit')){
		tabheader("[".$fchannel['cname']."] - 字段编辑&nbsp; &nbsp; &nbsp; >><a href=\"?entry=$entry&action=fieldone&chid=$chid\" onclick=\"return floatwin('open_fchanneldetail',this)\">添加字段</a>",'fchanneldetail',"?entry=$entry&action=$action&chid=$chid",7,0,1);
		trcategory(array('启用','字段名称|L','排序','字段标识|L','数据表|L','字段类型|L','删除','编辑'));
		foreach($fields as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '').($v['issystem'] ? ' disabled' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
				"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars($k)."</td>\n".
				"<td class=\"txtL\">$v[tbl]</td>\n".
				"<td class=\"txtL w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".($v['issystem'] ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"")."></td>\n".
				"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=fieldone&chid=$chid&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a></td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
		a_guide('fchanneldetail');
	}else{
		if(!empty($delete)){
			$deleteds = cls_fieldconfig::DeleteField('fchannel',$chid,$delete);
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
				cls_fieldconfig::ModifyOneConfig('fchannel',$chid,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('fchannel',$chid);
		
		adminlog('详细修改副件模型字段');
		cls_message::show('模型修改完成',"?entry=$entry&action=$action&chid=$chid");
	}
}elseif($action == 'fieldone'){
	cls_FieldConfig::EditOne('fchannel',@$chid,@$fieldname);
}
