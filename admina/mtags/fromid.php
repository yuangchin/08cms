<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	$typearr = array(
	'chid' => '文档模型',
	'mchid' => '会员模型',
	'caid' => '栏目',
	'mctid' => '认证类型',
	);
	foreach($cotypes as $k => $v) $typearr['ccid'.$k] = '分类-'.$v['cname'];
	foreach($grouptypes as $k => $v) $typearr['grouptype'.$k] = '会员组-'.$v['cname'];
	trbasic('指定ID来源类型','mtagnew[setting][type]',makeoption($typearr,empty($mtag['setting']['type']) ? '' : $mtag['setting']['type']),'select');
	trbasic('指定ID（空为激活ID）','mtagnew[setting][id]',isset($mtag['setting']['id']) ? $mtag['setting']['id'] : 0,'text');
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
}
?>
