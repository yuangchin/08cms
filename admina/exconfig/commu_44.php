<?

(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!submitcheck('bcommudetail')) {
	tabheader('交互项目设置-'.$commu['cname'],'commudetail',"?entry=$entry&action=$action&cuid=$cuid",2,0,1);
    setPermBar('发表权限设置', 'communew[cfgs][pmid]', @$commu['cfgs']['pmid'], 'cuadd', 'open', '');
	setPermBar('自动审核权限设置', 'communew[cfgs][autocheck]', @$commu['cfgs']['autocheck'], 'cuadd', 'check', '');
    trbasic('重复评论时间间隔(分钟)','communew[cfgs][repeattime]',@$commu['cfgs']['repeattime'],'text',array('validate' => " rule=\"int\" min=\"-1\"",'w' => 10,'guide' => '单位：分钟(-1为不设间隔)'));
	trbasic('提交不同印象个数','communew[cfgs][totalnum]',@$commu['cfgs']['totalnum'],'text',array('validate' => " rule=\"int\" min=\"0\"",'w' => 10,'guide' => '点击<添加印象>按钮提交印象的个数'));
	trbasic('前台显示印象个数','communew[cfgs][yxnum]',@$commu['cfgs']['yxnum'],'text',array('validate' => " rule=\"int\" min=\"0\"",'w' => 10,'guide' => '前台楼盘内容页显示印象个数'));
	trbasic('备注','communew[remark]',$commu['remark'],'text',array('w'=>50));
	trbasic('附加说明','communew[content]',$commu['content'],'textarea',array('w' => 500,'h' => 300,));
	tabfooter('bcommudetail','修改');
}else{
	empty($communew['cfgs']['chids']) && $communew['cfgs']['chids'] = array();
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
