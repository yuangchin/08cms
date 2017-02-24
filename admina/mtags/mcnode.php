<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	$arr = array('caid' => '栏目');
	foreach(array('grouptypes','mctypes',) as $k) $$k = cls_cache::Read($k);
	foreach($cotypes as $k => $v) !$v['self_reg'] && $arr['ccid'.$k] = $v['cname'];
	foreach($grouptypes as $k => $v) !$v['issystem'] && $arr['ugid'.$k] = $v['cname'];
#	$arr['mcnid'] = '自定义节点';
#	trbasic('指定会员节点类型','mtagnew[setting][cnsource]',makeoption($arr,isset($mtag['setting']['cnsource']) ? $mtag['setting']['cnsource'] : '0'),'select');
	trbasic('* 指定节点属性id','mtagnew[setting][cnid]',empty($mtag['setting']['cnid']) ? '' : $mtag['setting']['cnid']);
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
}else{
	$mtagnew['setting']['cnid'] = trim($mtagnew['setting']['cnid']);
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if(empty($mtagnew['setting']['cnid'])) mtag_error('指定节点属性id');
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
}
?>
