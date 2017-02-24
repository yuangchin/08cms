<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('* 指定内容来源','mtagnew[setting][tname]',isset($mtag['setting']['tname']) ? $mtag['setting']['tname'] : '','text',array('guide' => '输入格式：字段名aa、变量$a[b]等。'));
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('数量限制','mtagnew[setting][limits]',isset($mtag['setting']['limits']) ? $mtag['setting']['limits'] : '');
	trbasic('播放器宽度','mtagnew[setting][width]',isset($mtag['setting']['width']) ? $mtag['setting']['width'] : '');
	trbasic('播放器高度','mtagnew[setting][height]',isset($mtag['setting']['height']) ? $mtag['setting']['height'] : '');
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['tname'] = trim($mtagnew['setting']['tname']);
	if(empty($mtagnew['setting']['tname']) || !preg_match("/^[a-zA-Z_\$][a-zA-Z0-9_\[\]]*$/",$mtagnew['setting']['tname'])){
		mtag_error('内容来源设置不合规范');
	}
	$mtagnew['setting']['width'] = max(0,intval($mtagnew['setting']['width']));
	$mtagnew['setting']['height'] = max(0,intval($mtagnew['setting']['height']));
	$mtagnew['setting']['limits'] = max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? '10' : $mtagnew['setting']['limits'];
}
?>
