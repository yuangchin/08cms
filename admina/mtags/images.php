<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('* 指定内容来源','mtagnew[setting][tname]',isset($mtag['setting']['tname']) ? $mtag['setting']['tname'] : '','text',array('guide' => '输入格式：字段名aa、变量$a[b]等。'));
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('数量限制','mtagnew[setting][limits]',isset($mtag['setting']['limits']) ? $mtag['setting']['limits'] : '');
	trbasic('图片宽度限制','mtagnew[setting][maxwidth]',isset($mtag['setting']['maxwidth']) ? $mtag['setting']['maxwidth'] : '');
	trbasic('图片高度限制','mtagnew[setting][maxheight]',isset($mtag['setting']['maxheight']) ? $mtag['setting']['maxheight'] : '');
	$arr = array(0 => '不生成缩略图',1 => '最佳化剪裁图片',2 => '保留完整图片',);
	trbasic('按设定尺寸生成缩略图','',makeradio('mtagnew[setting][thumb]',$arr,isset($mtag['setting']['thumb']) ? $mtag['setting']['thumb'] : 0),'');
	trbasic('缩略图是否补白','',makeradio('mtagnew[setting][padding]',array(1=>'是',0=>'否'),isset($mtag['setting']['padding']) ? $mtag['setting']['padding'] : 1),'',array('guide'=>'默认补白(保留完整图片)。'));
	trspecial('补缺图片url',specialarr(array('type' => 'image','varname' => 'mtagnew[setting][emptyurl]','value' => isset($mtag['setting']['emptyurl']) ? $mtag['setting']['emptyurl'] : '',)));
	trbasic('补缺图片说明','mtagnew[setting][emptytitle]',isset($mtag['setting']['emptytitle']) ? $mtag['setting']['emptytitle'] : '');
	tabfooter();
	if(empty($_infragment)){
		tabheader('标识分页设置');
		trbasic('启用列表分页','mtagnew[setting][mp]',empty($mtag['setting']['mp']) ? 0 : $mtag['setting']['mp'],'radio');
		trbasic('总结果数(空为不限)','mtagnew[setting][alimits]',isset($mtag['setting']['alimits']) ? $mtag['setting']['alimits'] : '');
		trbasic('是否简易的分页导航','mtagnew[setting][simple]',empty($mtag['setting']['simple']) ? '0' : $mtag['setting']['simple'],'radio');
		trbasic('分页导航的页码长度','mtagnew[setting][length]',isset($mtag['setting']['length']) ? $mtag['setting']['length'] : '');
		tabfooter();
	}	
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['tname'] = trim($mtagnew['setting']['tname']);
	if(empty($mtagnew['setting']['tname']) || !preg_match("/^[a-zA-Z_\$][a-zA-Z0-9_\[\]]*$/",$mtagnew['setting']['tname'])){
		mtag_error('内容来源设置不合规范');
	}
	$mtagnew['setting']['maxwidth'] = max(0,intval($mtagnew['setting']['maxwidth']));
	$mtagnew['setting']['maxheight'] = max(0,intval($mtagnew['setting']['maxheight']));
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['alimits'] = max(0,intval($mtagnew['setting']['alimits']));
	$mtagnew['setting']['length'] = max(0,intval($mtagnew['setting']['length']));
	$c_upload = cls_upload::OneInstance();	
	$mtagnew['setting']['emptyurl'] = upload_s($mtagnew['setting']['emptyurl'],isset($mtag['setting']['emptyurl']) ? $mtag['setting']['emptyurl'] : '','image');
	if($k = strpos($mtagnew['setting']['emptyurl'],'#')) $mtagnew['setting']['emptyurl'] = substr($mtagnew['setting']['emptyurl'],0,$k);
	$c_upload->closure(2);
	$c_upload->saveuptotal(1);
}
?>
