<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(empty($mtag['setting']['listby'])) {
    $mtag['setting']['listby'] = 'ca';
    $sclass = 0;
} else {
    $sclass = ($mtag['setting']['listby'] == 'ca' ? 0 : str_replace('co', '', $mtag['setting']['listby']));
}

if(!$modeSave){
	$_tt = empty($sclass) ? '栏目' : '分类';
	$sourcearr = array('0' => '全部顶级'.$_tt,'4' => '全部二级'.$_tt,'5' => '全部三级'.$_tt,'1' => '手动指定','2' => '激活'.$_tt.'的下级'.$_tt,'3' => '自定查询字串',);
	if(empty($sclass)){
		sourcemodule('列表项设置',
			'mtagnew[setting][casource]',
			$sourcearr + ridsarr(0),
			empty($mtag['setting']['casource']) ? '0' : $mtag['setting']['casource'],
			'1',
			'mtagnew[setting][caids][]',
			cls_catalog::ccidsarr(0),
			empty($mtag['setting']['caids']) ? array() : (is_array($mtag['setting']['caids']) ? $mtag['setting']['caids'] : explode(',',$mtag['setting']['caids']))
		);
	}else{
		sourcemodule('列表项设置',
			"mtagnew[setting][cosource$sclass]",
			$sourcearr + ridsarr($sclass),
			empty($mtag['setting']['cosource'.$sclass]) ? '0' : $mtag['setting']['cosource'.$sclass],
			'1',
			"mtagnew[setting][ccids$sclass][]",
			cls_catalog::ccidsarr($sclass),
			empty($mtag['setting']['ccids'.$sclass]) ? array() : explode(',',$mtag['setting']['ccids'.$sclass])
		);
	}
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? '10' : $mtag['setting']['limits']);
	trbasic('从第几条记录开始显示','mtagnew[setting][startno]',empty($mtag['setting']['startno']) ? '' : $mtag['setting']['startno'],'text',array('guide' => '设置按当前设置的第几条记录开始，默认为0。'));
	tabfooter();
	
	tabheader('节点的交叉属性');
	if(!empty($sclass)){
		$inheritarr = array('0' => '非关联项','active' => '激活栏目',);
		$inheritarr = $inheritarr + cls_catalog::ccidsarr(0);
		trbasic('栏目','mtagnew[setting][cainherit]',makeoption($inheritarr,empty($mtag['setting']['cainherit']) ? '0' : $mtag['setting']['cainherit']),'select');
	}
	foreach($cotypes as $k => $v){
		if($v['sortable'] && $sclass != $k) {
			$inheritarr = array('0' => '非关联项','active' => '激活分类',);
			$inheritarr = $inheritarr + cls_catalog::ccidsarr($k);
			trbasic("$v[cname]","mtagnew[setting][coinherit$k]",makeoption($inheritarr,empty($mtag['setting']['coinherit'.$k]) ? '0' : $mtag['setting']['coinherit'.$k]),'select');
		}
	}
	tabfooter();
	tabheader('高级选项');
	if(empty($_infragment)){
		$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
		$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
		trbasic('当前标识的更多设置','',$str,'');
	}
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=catalogs&typeid=$sclass\" target=\"_blank\">生成</a>";
	trbasic('排序字串'.$addstr,'mtagnew[setting][orderstr]',empty($mtag['setting']['orderstr']) ? '' : $mtag['setting']['orderstr'],'text',array('w' => 50));
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isfunc]\" name=\"mtagnew[setting][isfunc]\"".(empty($mtag['setting']['isfunc']) ? '' : ' checked').">字串来自函数";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isall]\" name=\"mtagnew[setting][isall]\"".(empty($mtag['setting']['isall']) ? '' : ' checked').">完整查询字串";
	trbasic('筛选查询字串'.$addstr,'mtagnew[setting][wherestr]',empty($mtag['setting']['wherestr']) ? '' : $mtag['setting']['wherestr'],'textarea',array('guide' => '函数格式：函数名(\'参数1\',\'参数2\')。完整查询字串包含select、from、where,不要含order及limit。'));
	trbasic('强制索引字串','mtagnew[setting][forceindex]',empty($mtag['setting']['forceindex']) ? '' : $mtag['setting']['forceindex'],'text',array('guide' => '格式举例：0.mclicks,在设置前请确认当前的查询中涉及表中建有mclicks的索引。'));
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
	
	if(empty($_infragment)){
		tabheader('标识分页设置');
		trbasic('启用列表分页','mtagnew[setting][mp]',empty($mtag['setting']['mp']) ? 0 : $mtag['setting']['mp'],'radio');
		trbasic('总结果数(空为不限)','mtagnew[setting][alimits]',isset($mtag['setting']['alimits']) ? $mtag['setting']['alimits'] : '');
		trbasic('是否简易的分页导航','mtagnew[setting][simple]',empty($mtag['setting']['simple']) ? '0' : $mtag['setting']['simple'],'radio');
		trbasic('分页导航的页码长度','mtagnew[setting][length]',isset($mtag['setting']['length']) ? $mtag['setting']['length'] : '');
		tabfooter();
	}
}else{//?????????????????????????过滤非listby的参数
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if(!isset($mtagnew['setting'][cls_mtags_catalogs::SCLASS_VAL]) || $mtagnew['setting'][cls_mtags_catalogs::SCLASS_VAL] == '') mtag_error('请指定正确的类目项目');
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['alimits'] = max(0,intval(@$mtagnew['setting']['alimits']));
	$mtagnew['setting']['length'] = max(0,intval(@$mtagnew['setting']['length']));
	$mtagnew['setting']['startno'] = trim($mtagnew['setting']['startno']);
	$mtagnew['setting']['orderstr'] = empty($mtagnew['setting']['orderstr']) ? '' : trim($mtagnew['setting']['orderstr']);
	$mtagnew['setting']['wherestr'] = empty($mtagnew['setting']['wherestr']) ? '' : trim($mtagnew['setting']['wherestr']);
	$mtagnew['setting']['isfunc'] = empty($mtagnew['setting']['isfunc']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['isall'] = empty($mtagnew['setting']['isall']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));

	//数组参数的处理
	$idvars = array('caids');
	foreach($cotypes as $k => $cotype) $idvars[] = 'ccids'.$k;
	foreach($idvars as $k){
		if(empty($mtagnew['setting'][$k])){
			unset($mtagnew['setting'][$k]);
		}else $mtagnew['setting'][$k] = implode(',',$mtagnew['setting'][$k]);
	}
	$mtagnew['setting']['forceindex'] = trim($mtagnew['setting']['forceindex']);
	if(empty($mtagnew['setting']['forceindex'])) unset($mtagnew['setting']['forceindex']);
}
function ridsarr($coid = 0){
	$cnrels = cls_cache::Read('cnrels');
	$ret = array();
	foreach($cnrels as $k => $v){
		if(($coid > 0 && in_array($coid,array($v['coid'],$v['coid1']))) || ($coid <= 0 && ($v['coid'] <= 0 || $v['coid1'] <= 0))) $ret[-$k] = '['.'关联'.']'.$v['cname'];
	}
	return $ret;
}