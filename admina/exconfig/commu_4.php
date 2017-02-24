<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!submitcheck('bcommudetail')) {
	tabheader('交互项目设置-'.$commu['cname'],'commudetail',"?entry=$entry&action=$action&cuid=$cuid",2,0,1);
	setChidsBar(@$commu['cfgs']['chids'],'chid');
	setPermBar('举报权限设置', 'communew[cfgs][pmid]', @$commu['cfgs']['pmid'], 'cuadd', 'open', '');
    trbasic('重复发送时间间隔(分钟)','communew[cfgs][repeattime]',@$commu['cfgs']['repeattime'],'text',array('validate' => " rule=\"int\" min=\"0\"",'w' => 10,'guide' => '单位：分钟'));
	trbasic('积分设置','','加积分(添加)：<input type="text" min="0" rule="int" value="'.@$commu['cfgs']['acurrency'].'" name="communew[cfgs][acurrency]" id="communew[cfgs][acurrency]" size="10"> 扣积分(删除)：<input type="text" min="0" rule="int" value="'.@$commu['cfgs']['ccurrency'].'" name="communew[cfgs][ccurrency]" id="communew[cfgs][ccurrency]" size="10">','',array('guide'=>'设置添加评论加积分，被管理员删除扣积分。'));
	trbasic('备注','communew[remark]',$commu['remark'],'text',array('w'=>50));
	trbasic('附加说明','communew[content]',$commu['content'],'textarea',array('w' => 500,'h' => 300,));
	tabfooter('bcommudetail','修改');
}else{
	empty($communew['cfgs']['chids']) && $communew['cfgs']['chids'] = array();
    $communew['cfgs']['chids'] = array_filter($communew['cfgs']['chids']);
	$communew['content'] = empty($communew['content']) ? '' : trim($communew['content']);
	$communew['remark'] = empty($communew['remark']) ? '' : trim(strip_tags($communew['remark']));
	$communew['cfgs'] = !empty($communew['cfgs']) ? addslashes(var_export($communew['cfgs'],TRUE)) : '';
 	$cfgs = ",cfgs='$communew[cfgs]'";
	$db->query("UPDATE {$tblprefix}acommus SET 
				remark='$communew[remark]',
				content='$communew[content]' $cfgs
				WHERE cuid='$cuid'");
	cls_CacheFile::Update('commus');
	adminlog('编辑交互项目'.$commu['cname']);
	cls_message::show('交互项目设置完成。',axaction(6,"?entry=$entry&action=$action&cuid=$cuid"));
}

?>
