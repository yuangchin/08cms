<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('gather')) cls_message::show($re);
$channels = cls_cache::Read('channels');
$gmodels = cls_cache::Read('gmodels');
if($action == 'gmodeledit'){
	if(!submitcheck('bsubmit')){
		backnav('gmiss','model');
		tabheader("采集模型管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=gmodeladd\" onclick=\"return floatwin('open_gmodel',this)\">添加</a>",'gmodeledit',"?entry=$entry&action=$action",'5');
		trcategory(array('ID',array('采集模型','txtL'),'文档模型','<input class="checkbox" type="checkbox" name="chkall" onclick="deltip(this,0,checkall,this.form)">删?','编辑'));
		foreach($gmodels as $k => $v){
			$channelstr = @$channels[$v['chid']]['cname'];
			$editstr = "<a href=\"?entry=$entry&action=gmodeldetail&gmid=$k\" onclick=\"return floatwin('open_gmodel',this)\">详情</a>";
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" name=\"gmodelsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC\">$channelstr</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\">\n".
				"<td class=\"txtC w30\">$editstr</td></tr>\n";
		}
		tabfooter('bsubmit','修改');
		a_guide('gmodeledit');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}gmissions WHERE gmid='$k'")) continue;
				$db->query("DELETE FROM {$tblprefix}gmodels WHERE gmid=$k");
				unset($gmodelsnew[$k]);
			}
		}
		if(!empty($gmodelsnew)){
			foreach($gmodelsnew as $k => $v){
				$v['cname'] = empty($v['cname']) ? addslashes($gmodels[$k]['cname']) : $v['cname'];
				$db->query("UPDATE {$tblprefix}gmodels SET cname='$v[cname]' WHERE gmid=$k");
			}
		}
		cls_CacheFile::Update('gmodels');
		adminlog('编辑采集模型管理列表');
		cls_message::show('采集模型修改完成',axaction(6,"?entry=$entry&action=gmodeledit"));
	}
}elseif($action == 'gmodeladd'){
	if(!submitcheck('bsubmit')){
		tabheader('添加采集模型','gmodeladd',"?entry=$entry&action=$action");
		trbasic('采集模型名称','gmodeladd[cname]');
		trbasic('请指定采集的文档模型','gmodeladd[chid]',makeoption(cls_channel::chidsarr(0)),'select');
		tabfooter('bsubmit','添加');
	}else{
		$gmodeladd['cname'] = trim(strip_tags($gmodeladd['cname']));
		if(!$gmodeladd['cname']) cls_message::show('请输入采集模型名称!',M_REFERER);
		if(!$gmodeladd['chid']) cls_message::show('请选择文档模型或合辑类型!',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}gmodels SET cname='$gmodeladd[cname]',chid='$gmodeladd[chid]'");
		$gmid = $db->insert_id();
		cls_CacheFile::Update('gmodels');
		adminlog('添加采集模型');
		cls_message::show('采集模型添加完成',"?entry=$entry&action=gmodeldetail&gmid=$gmid");
	}

}elseif($action =='gmodeldetail' && $gmid){
	$gmodel = cls_cache::Read('gmodel',$gmid,'');
	empty($gmodel) && cls_message::show('请指定正确的采集模型');
	empty($channels[$gmodel['chid']]) && cls_message::show('采集模型关联的文档模型不存在');
	$gfields = empty($gmodel['gfields']) ? array() : $gmodel['gfields'];
	$fields = cls_cache::Read('fields',$gmodel['chid']);
    $cotypes = cls_cache::Read('cotypes');
    $cfields = array('caid'=>array('datatype'=>'select','cname'=>'栏目'));
    foreach($cotypes as $k=>$v){
        $cfields['ccid'.$k]['datatype'] = $v['asmode'] ? 'mselect' : 'select';
        $cfields['ccid'.$k]['cname'] = $v['cname'];
    }
    $fields = $cfields + $fields + array('jumpurl'=>array('datatype'=>'text','cname'=>'跳转URL'),'createdate'=>array('datatype'=>'text','cname'=>'添加时间'),'enddate'=>array('datatype'=>'text','cname'=>'到期时间'),'mname'=>array('datatype'=>'text','cname'=>'会员名称'));
	if(!submitcheck('bsubmit')){
		include_once M_ROOT."include/fields.fun.php";
		tabheader($gmodel['cname'].'-采集字段设置','gmodeldetail',"?entry=$entry&action=gmodeldetail&gmid=$gmid",'5');
		trcategory(array('采集','纯链接',array('字段名称','txtL'),'字段标识','字段类型'));
		foreach($fields as $k => $v){
			$islinkstr = ($v['datatype'] != 'text' || $k == 'createdate' || $k == 'mname' || $k == 'enddate') ? '-' : "<input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][islink]\" value=\"1\"".(!empty($gfields[$k]) ? ' checked' : '').">";
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".(isset($gfields[$k]) ? ' checked' : '')."></td>\n".
				"<td class=\"txtC w50\">$islinkstr</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtC\">$k</td>\n".
				"<td class=\"txtC w80\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
		a_guide('gmodeldetail');
	}else{
		foreach($fields as $k => $v){
			if(!empty($fieldsnew[$k]['available'])){
				$islink = empty($fieldsnew[$k]['islink']) ? 0 : 1;
				in_array($v['datatype'],array('image','flash','file','media','jumpurl')) && $islink = 1;
				$newgfields[$k] = $islink;
			}
		}
		$gfieldsnew = empty($newgfields) ? '' : addslashes(serialize($newgfields));
		$db->query("UPDATE {$tblprefix}gmodels SET gfields='$gfieldsnew' WHERE gmid='$gmid'");
		cls_CacheFile::Update('gmodels');
		cls_CacheFile::Update('gmissions');//保证在测试获取内容时与设置字段一至
		adminlog('详细修改采集模型');
		cls_message::show('采集模型编辑完成',axaction(6,"?entry=$entry&action=gmodeledit"));
	}
}
?>
