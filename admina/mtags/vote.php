<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	$arr = array(
		'archives' => '文档',
		'members' => '会员',
		'farchives' => '副件',
		'catalogs' => '栏目',
		'coclass' => '分类',
	);
	echo "<tr class=\"txt\"><td class=\"txt txtright fB borderright\">投票类型</td>\n";
	echo "<td class=\"txtL\">\n";
	echo "<input class=\"radio\" type=\"radio\" name=\"mtagnew[setting][type]\" value=\"\" onclick=\"\$id('vote_type1').style.display = '';\$id('vote_type2').style.display = 'none';\"".(empty($mtag['setting']['type']) ? ' checked' : '').">".'独立投票'."\n";
	$i = 1;
	foreach($arr as $k => $v){
		echo "<input class=\"radio\" type=\"radio\" name=\"mtagnew[setting][type]\" value=\"$k\" onclick=\"\$id('vote_type1').style.display = 'none';\$id('vote_type2').style.display = '';\"".(@$mtag['setting']['type'] == $k ? ' checked' : '').">$v\n";
		echo $i % 6 ? '' : '<br>';
		$i ++;

	}
	echo "</td></tr>\n";
	echo "<tbody id=\"vote_type1\" style=\"display:".(empty($mtag['setting']['type']) ? '' : 'none')."\">";
	trbasic('手动指定投票ID','mtagnew[setting][vid]',empty($mtag['setting']['vid']) ? '0' : $mtag['setting']['vid'],'text',array('guide' => '留空为激活投票。'));
	echo "</tbody>";
	echo "<tbody id=\"vote_type2\" style=\"display:".(!empty($mtag['setting']['type']) ? '' : 'none')."\">";
	trbasic('*内容来源记录id','mtagnew[setting][id]',isset($mtag['setting']['id']) ? $mtag['setting']['id'] : '','text');
	trbasic('*指定投票字段标识','mtagnew[setting][fname]',isset($mtag['setting']['fname']) ? $mtag['setting']['fname'] : '','text');
	echo "</tbody>";	
	trbasic('投票选项列表结果限制','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? '10' : $mtag['setting']['limits']);
	trbasic('是否启用JS动态内容调用','mtagnew[setting][js]',empty($mtag['setting']['js']) ? 0 : $mtag['setting']['js'],'radio');
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
}else{
	$mtagnew['setting']['fname'] = trim($mtagnew['setting']['fname']);
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if(!empty($mtagnew['setting']['type']) && (empty($mtagnew['setting']['id']) || empty($mtagnew['setting']['fname']) || !preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/",$mtagnew['setting']['fname']))){
		mtag_error('请输入完整的资料');
	}
	$mtagnew['setting']['vid'] = trim($mtagnew['setting']['vid']);
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
}
?>
