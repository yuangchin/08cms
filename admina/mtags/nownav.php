<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	$coidsarr = array('caid' => '栏目');
	foreach($cotypes as $k => $v) $v['sortable'] && $coidsarr["ccid$k"] = $v['cname'];
	trbasic('需要的类系因素','',makecheckbox('mtagnew[setting][coids][]',$coidsarr,empty($mtag['setting']['coids']) ? array() : explode(',',$mtag['setting']['coids']),5),'',array('guide' => '选择需要参与组成导航的类系因素，不选则包含所有类系因素。'));
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['coids'] = empty($mtagnew['setting']['coids']) ? '' : implode(',',$mtagnew['setting']['coids']);
}
?>
