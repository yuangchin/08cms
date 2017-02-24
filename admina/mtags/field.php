<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	trbasic('* 来源字段英文标识','mtagnew[setting][tname]',isset($mtag['setting']['tname']) ? $mtag['setting']['tname'] : '','text',array('guide' => '输入字段的英文标识，不能带$或[]。'));
	$arr = array(
		'archive' => '文档',
		'member' => '会员',
		'farchive' => '副件',
		'catalog' => '栏目',
		'coclass' => '分类',
		'commu' => '交互',
		'push' => '推送位', 
	);
	trbasic('字段类型','mtagnew[setting][type]',makeoption($arr,empty($mtag['setting']['type']) ? '0' : $mtag['setting']['type']),'select');
	trbasic('多个标题只列出前几个','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? '' : $mtag['setting']['limits'],'text',array('guide' => '留空表示多个标题全部列出。'));
	tabfooter();
}else{
	$mtagnew['setting']['tname'] = trim($mtagnew['setting']['tname']);
	$mtagnew['setting']['limits'] = max(0,intval($mtagnew['setting']['limits']));
	if(empty($mtagnew['setting']['tname'])) cls_message::show('请输入来源字段英文标识');
	if(empty($mtagnew['setting']['tname']) || !preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/",$mtagnew['setting']['tname'])){
		mtag_error('内容来源设置不合规范');
	}
	$mtagnew['setting']['fname'] = $mtagnew['setting']['tname'];
}
?>
