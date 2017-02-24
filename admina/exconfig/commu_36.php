<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!submitcheck('bcommudetail')) {
	tabheader('交互项目设置-'.$commu['cname'],'commudetail',"?entry=$entry&action=$action&cuid=$cuid",2,0,1);
	#setChidsBar(@$commu['cfgs']['chids'],'chid');
	$issms = makeradio('communew[cfgs][issms]',array('否','是'),$commu['cfgs']['issms']);
	$smsfee = makeradio('communew[cfgs][smsfee]',array('sadm'=>'管理员','get'=>'被委托者'),$commu['cfgs']['smsfee']);
	trbasic('是否开启手机短信通知','',$issms,'',array('guide'=>'短信接收者为房源发布者，既经纪人或个人(普通会员)。'));
	trbasic('短信费用承担者','',$smsfee,'');
	trbasic('短信通知内容模版','communew[cfgs][smscon]',$commu['cfgs']['smscon'],'textarea',array('w'=>300,'h' => 100,'guide'=>'不超过180字，建议控制在70个字内；超过70个字符，约按每70字扣一条短信费用；<br>模版变量请用\'{$xxx}\'形式，支持交互字段'));
	#trbasic('发表权限设置','communew[cfgs][pmid]',makeoption(pmidsarr('cuadd'),@$commu['cfgs']['pmid']),'select');
	#trbasic('重复发送时间间隔(分钟)','communew[cfgs][repeattime]',@$commu['cfgs']['repeattime'],'text',array('validate' => " rule=\"int\" min=\"0\"",'w' => 10,'guide' => '单位：分钟'));
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
