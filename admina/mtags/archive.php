<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide'=>'系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	$arr = array(0 => '不设置',-1 => '激活模型',) + cls_channel::chidsarr(1);
/*	trbasic('指定文档模型','mtagnew[setting][chid]',makeoption($arr,empty($mtag['setting']['chid']) ? 0 : $mtag['setting']['chid']),'select');*/
	trbasic('指定来源id','mtagnew[setting][id]',empty($mtag['setting']['id']) ? '' : $mtag['setting']['id'],'text',array('guide' => '手动输入文档aid,输入为空默认为激活文档'));
	$arr = array(0 => '不设置',);foreach($abrels as $k => $v) $arr[$k] = $v['cname'];
	trbasic('指向所属合辑','mtagnew[setting][arid]',makeoption($arr,empty($mtag['setting']['arid']) ? 0 : $mtag['setting']['arid']),'select');
	$arr = array();
	empty($_infragment) && $arr['js'] = '使用JS动态调用当前标识解析出来的内容';
	$arr['detail'] = '需要模型字段表的内容';
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	empty($_infragment) && setPermBar('浏览权限设置', 'mtagnew[setting][pmid]', empty($mtag['setting']['pmid']) ? 0 : $mtag['setting']['pmid'], $source='tpl', $soext='open', '在标识模板中以[#pm#]分隔，前部分为有权限显示模板，后部分为无权限显示模板。');
    trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
}
?>
