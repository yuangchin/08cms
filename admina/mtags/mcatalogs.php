<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? '10' : $mtag['setting']['limits']);
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	tabfooter();
	tabheader('设置列表项目');
	$sourcearr = array('0' => '全部空间栏目','2' => '激活栏目下的所有分类','1' => '手动指定栏目',);
	sourcemodule("空间栏目<input class=\"radio\" type=\"radio\" name=\"mtagnew[setting][listby]\" value=\"0\"".(empty($mtag['setting']['listby']) ? " checked" : "").">作为列表项",
				'mtagnew[setting][casource]',
				$sourcearr,
				empty($mtag['setting']['casource']) ? '0' : $mtag['setting']['casource'],
				'1',
				'mtagnew[setting][caids][]',
				cls_mcatalog::mcaidsarr(),
				(!empty($mtag['setting']['caids']) ? explode(',',$mtag['setting']['caids']) : array())
				);
	$sourcearr = array('0' => '栏目内全部分类',);
	trbasic("个人分类<input class=\"radio\" type=\"radio\" name=\"mtagnew[setting][listby]\" value=\"1\"".(!empty($mtag['setting']['listby']) ? " checked" : "").">作为列表项",'mtagnew[setting][ucsource]',empty($mtag['setting']['ucsource']) ? '' : $mtag['setting']['ucsource'],'text',array('guide' => '留空为激活栏目下所有空间分类，否则请指定一个空间栏目。'));
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	//数组参数的处理
	$idvars = array('caids');
	foreach($idvars as $k){
		if(empty($mtagnew['setting'][$k])){
			unset($mtagnew['setting'][$k]);
		}else $mtagnew['setting'][$k] = implode(',',$mtagnew['setting'][$k]);
	}
}
?>
