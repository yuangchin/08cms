<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide'=>'系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	tabfooter();

	tabheader('基本筛选设置');
	$chsourcearr = array('0' => '不排除','1' => '指定排除',);
/*	sourcemodule('排除以下文档模型',
				'mtagnew[setting][nochsource]',
				$chsourcearr,
				empty($mtag['setting']['nochids'][0]) ? 0 : 1,
				'1',
				'mtagnew[setting][nochids][]',
				cls_channel::chidsarr(1),
				!empty($mtag['setting']['nochids']) ? (is_array($mtag['setting']['nochids']) ? $mtag['setting']['nochids'] : explode(',',$mtag['setting']['nochids'])) : array()
				);*/
	$sourcearr = array('0' => '不限类目','2' => '激活类目','1' => '手动指定',);
	sourcemodule('允许栏目内容'."&nbsp;&nbsp;&nbsp;<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][caidson]\" value=\"1\"".(empty($mtag['setting']['caidson']) ? "" : " checked").">含子分类",
				'mtagnew[setting][casource]',
				$sourcearr,
				empty($mtag['setting']['casource']) ? '0' : $mtag['setting']['casource'],
				'1',
				'mtagnew[setting][caids][]',
				cls_catalog::ccidsarr(0,$sclass),
				empty($mtag['setting']['caids']) ? array() : (is_array($mtag['setting']['caids']) ? $mtag['setting']['caids'] : explode(',',$mtag['setting']['caids']))
				);
	foreach($cotypes as $k => $cotype){
		if($sclass && !coid_in_chid($k,$sclass)) continue;
		sourcemodule("$cotype[cname]"."&nbsp;&nbsp;&nbsp;<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][ccidson$k]\" value=\"1\"".(empty($mtag['setting']['ccidson'.$k]) ? "" : " checked").">含子分类",
					"mtagnew[setting][cosource$k]",
					$sourcearr,
					empty($mtag['setting']['cosource'.$k]) ? '0' : $mtag['setting']['cosource'.$k],
					'1',
					"mtagnew[setting][ccids$k][]",
					cls_catalog::ccidsarr($k,$sclass),
					empty($mtag['setting']['ccids'.$k]) ? array() : (is_array($mtag['setting']['ccids'.$k]) ? $mtag['setting']['ccids'.$k] : explode(',',$mtag['setting']['ccids'.$k]))
					);
	}
	tabfooter();
	tabheader('更多设置');
	$arr = array('' => '普通列表','in' => '指定id的辑内列表','belong' => '指定id的所属合辑列表','relate' => '指定id的关键词相关文档列表',);
	trbasic('指定列表模式','mtagnew[setting][mode]',makeoption($arr,empty($mtag['setting']['mode']) ? 0 : $mtag['setting']['mode']),'select');
	$arr = array(0 => '不设置',);foreach($abrels as $k => $v) $arr[$k] = $v['cname'];
	trbasic('指定合辑项目','mtagnew[setting][arid]',makeoption($arr,empty($mtag['setting']['arid']) ? 0 : $mtag['setting']['arid']),'select',array('guide' => '当模式为辑内列表或所属合辑列表时需要指定'));
	trbasic('指定相关id','mtagnew[setting][id]',empty($mtag['setting']['id']) ? '' : $mtag['setting']['id'],'text',array('guide' => '手动输入文档aid,输入为空默认为激活文档'));
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',
	'space' => '仅显示激活会员的文档',
	'ucsource' => '只显示激活个人分类的文档',
	'validperiod' => '只允许有效期内的内容',
	'detail' => '需要模型字段表的内容(仅当列表只允许单个模型时选择才有效)',
	);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	tabfooter();

	tabheader('高级选项');
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=archives&typeid=$sclass\" target=\"_blank\">生成</a>";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isfunc]\" name=\"mtagnew[setting][isfunc]\"".(empty($mtag['setting']['isfunc']) ? '' : ' checked').">字串来自函数";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isall]\" name=\"mtagnew[setting][isall]\"".(empty($mtag['setting']['isall']) ? '' : ' checked').">完整查询字串";
	trbasic('筛选查询字串'.$addstr,'mtagnew[setting][wherestr]',empty($mtag['setting']['wherestr']) ? '' : $mtag['setting']['wherestr'],'textarea',array('guide' => '函数格式：函数名(\'参数1\',\'参数2\')。完整查询字串包含select、from、where,不要含order及limit。'));
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
    $mtagnew['setting'][cls_mtags_archives::CHSOURCE] = (empty($mtagnew['setting'][cls_mtags_archives::CHIDS][0]) ? 1 : 2);
	@$mtagnew['setting']['ucsource'] = empty($mtagnew['setting']['space']) ? 0 : $mtagnew['setting']['ucsource'];
	$mtagnew['setting']['wherestr'] = empty($mtagnew['setting']['wherestr']) ? '' : trim($mtagnew['setting']['wherestr']);
	$mtagnew['setting']['isfunc'] = empty($mtagnew['setting']['isfunc']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['isall'] = empty($mtagnew['setting']['isall']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
	
	$idvars = array('caids','nochids');//数组参数的处理
	foreach($cotypes as $k => $cotype) $idvars[] = 'ccids'.$k;
	foreach($idvars as $k){
		if(empty($mtagnew['setting'][$k])){
			unset($mtagnew['setting'][$k]);
		}else $mtagnew['setting'][$k] = implode(',',$mtagnew['setting'][$k]);
	}
	if(empty($mtagnew['setting']['nochsource']) || !empty($mtagnew['setting'][cls_mtags_archives::CHSOURCE])) unset($mtagnew['setting']['nochids']);
	unset($mtagnew['setting']['nochsource']);
}