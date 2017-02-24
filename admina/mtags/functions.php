<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
#	$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? 10 : $mtag['setting']['limits']);
	trbasic('* 列表内容来自PHP函数返回值','mtagnew[setting][func]',empty($mtag['setting']['func']) ? '' : $mtag['setting']['func'],'text',array('w' => 50,'guide' => '格式：函数名(\'参数1\',\'参数2\'...)。返回值为内容数组，当前页码为$_mp[\'nowpage\']。'));
	if(empty($_infragment)){
		$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
		$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
		trbasic('当前标识的更多设置','',$str,'');
	}
	tabfooter();
	
	if(empty($_infragment)){
		tabheader('标识分页设置');
		trbasic('启用列表分页','mtagnew[setting][mp]',empty($mtag['setting']['mp']) ? 0 : $mtag['setting']['mp'],'radio');
		trbasic('* 总结果数来自PHP函数返回值','mtagnew[setting][mpfunc]',empty($mtag['setting']['mpfunc']) ? '' : $mtag['setting']['mpfunc'],'text',array('w' => 50,'guide' => '格式：函数名(\'参数1\',\'参数2\'...)。返回值为总结果数量。'));
		trbasic('总结果数(空为不限)','mtagnew[setting][alimits]',isset($mtag['setting']['alimits']) ? $mtag['setting']['alimits'] : '');
		trbasic('是否简易的分页导航','mtagnew[setting][simple]',empty($mtag['setting']['simple']) ? '0' : $mtag['setting']['simple'],'radio');
		trbasic('分页导航的页码长度','mtagnew[setting][length]',isset($mtag['setting']['length']) ? $mtag['setting']['length'] : '');
		tabfooter();
	}	
}else{
	$mtagnew['setting']['func'] = trim($mtagnew['setting']['func']);
	$mtagnew['setting']['mpfunc'] = trim($mtagnew['setting']['mpfunc']);
	
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['alimits'] = max(0,intval($mtagnew['setting']['alimits']));
	$mtagnew['setting']['length'] = max(0,intval($mtagnew['setting']['length']));
	
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
#	if(empty($mtagnew['setting']['func'])) mtag_error('请输入标识函数返回值');
	if(!empty($mtagnew['setting']['mp']) && empty($mtagnew['setting']['mpfunc'])) mtag_error('请输入标识函数！');
}
?>
