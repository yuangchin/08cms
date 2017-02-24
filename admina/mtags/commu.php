<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide'=>'系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	$arr = array(0 => '设置交互项目');foreach($commus as $k => $v) $v['tbl'] && $arr[$k] = $v['cname'];
/*	trbasic('*指定交互项目','mtagnew[setting][cuid]',makeoption($arr,empty($mtag['setting']['cuid']) ? 0 : $mtag['setting']['cuid']),'select');*/
	trbasic('指定来源记录id','mtagnew[setting][id]',empty($mtag['setting']['id']) ? '' : $mtag['setting']['id'],'text',array('guide' => '手动输入交互记录cid,输入为空默认为激活交互记录'));
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	setPermBar('浏览权限设置', 'mtagnew[setting][pmid]', empty($mtag['setting']['pmid']) ? 0 : $mtag['setting']['pmid'], 'tpl', 'open', '在标识模板中以[#pm#]分隔，前部分为有权限显示模板，后部分为无权限显示模板。');
    tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
}
?>
