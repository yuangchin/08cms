<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!submitcheck('bcommudetail')) {
	tabheader('交互项目设置-'.$commu['cname'],'commudetail',"?entry=$entry&action=$action&cuid=$cuid",2,0,1);
	#trbasic('允许对以下模型回答','',makecheckbox('communew[cfgs][chids][]',chidsarr(0),@$commu['cfgs']['chids'],5),'');
	trhidden('communew[cfgs][chids][]',106);
    setPermBar('发表权限设置', 'communew[cfgs][pmid]', @$commu['cfgs']['pmid'], 'cuadd', 'open', '');
    setPermBar('自动审核权限设置', 'communew[cfgs][autocheck]', @$commu['cfgs']['autocheck'], 'cuadd', 'check', '');
    trbasic('是否允许重复回答','communew[cfgs][repeatanswer]',@$commu['cfgs']['repeatanswer'],'radio',array('guide'=>'是表示允许重复回答一个问题，否表示重复回答一个问题。'));
	#trbasic('问答有效天数','communew[cfgs][validday]',@$commu['cfgs']['validday'],'text',array('w'=>10,'guide'=>'设置问答信息的有效天数，0为不限。'));
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
	$db->query("UPDATE {$tblprefix}acommus SET 
				remark='$communew[remark]',
				content='$communew[content]',
				cfgs='$communew[cfgs]'
				WHERE cuid='$cuid'");
	cls_CacheFile::Update('commus');
	adminlog('编辑交互项目'.$commu['cname']);
	cls_message::show('交互项目设置完成。',axaction(6,"?entry=$entry&action=$action&cuid=$cuid"));
}

?>
