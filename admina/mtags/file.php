<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('* 指定内容来源','mtagnew[setting][tname]',isset($mtag['setting']['tname']) ? $mtag['setting']['tname'] : '','text',array('guide' => '可输入字段名aa或变量$a[b]。如标识用于下载、播放页如flash.php等(页面指定来源)，请留空。'));
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['tname'] = trim($mtagnew['setting']['tname']);
	if(!empty($mtagnew['setting']['tname']) && !preg_match("/^[a-zA-Z_\$][a-zA-Z0-9_\[\]]*$/",$mtagnew['setting']['tname'])){
		mtag_error('内容来源设置不合规范');
	}
}
?>
