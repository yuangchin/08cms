<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	tabfooter();

	tabheader('基本筛选设置');
	foreach($grouptypes as $gtid => $grouptype){
		$ugidsarr = array('0' => '不限会员组') + ugidsarr($grouptype['gtid']);
		trbasic("$grouptype[cname]".'筛选','mtagnew[setting][ugid'.$gtid.']',makeoption($ugidsarr,empty($mtag['setting']['ugid'.$gtid]) ? 0 : $mtag['setting']['ugid'.$gtid]),'select');
	}
/*
	$chsourcearr = array('0' => '不限模型','1' => '激活模型','2' => '手动指定',);
	sourcemodule('会员模型限制',
				'mtagnew[setting][chsource]',
				$chsourcearr,
				empty($mtag['setting']['chsource']) ? '' : $mtag['setting']['chsource'],
				'2',
				'mtagnew[setting][chids][]',
				cls_mchannel::mchidsarr(),
				!empty($mtag['setting']['chids']) ? (is_array($mtag['setting']['chids']) ? $mtag['setting']['chids'] : explode(',',$mtag['setting']['chids'])) : array()
				);*/
	tabfooter();
	
	tabheader('更多设置');
	$arr = array('' => '普通列表','in' => '指定id的辑内列表','belong' => '指定id的所属合辑列表',);
	trbasic('指定列表模式','mtagnew[setting][mode]',makeoption($arr,empty($mtag['setting']['mode']) ? 0 : $mtag['setting']['mode']),'select');
	$arr = array(0 => '不设置',);foreach($abrels as $k => $v) $arr[$k] = $v['cname'];
	trbasic('指定合辑项目','mtagnew[setting][arid]',makeoption($arr,empty($mtag['setting']['arid']) ? 0 : $mtag['setting']['arid']),'select',array('guide' => '当模式为辑内列表或所属合辑列表时需要指定'));
	trbasic('指定相关id','mtagnew[setting][id]',empty($mtag['setting']['id']) ? '' : $mtag['setting']['id'],'text',array('guide' => '手动输入会员mid,输入为空默认为激活会员'));
	tabfooter();
	
	tabheader('高级选项');
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容','detail' => '需要模型字段表的内容(仅当列表只允许单个模型时选择才有效)',);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=members\" target=\"_blank\">生成</a>";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isfunc]\" name=\"mtagnew[setting][isfunc]\"".(empty($mtag['setting']['isfunc']) ? '' : ' checked').">字串来自函数";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isall]\" name=\"mtagnew[setting][isall]\"".(empty($mtag['setting']['isall']) ? '' : ' checked').">完整查询字串";
	trbasic('筛选查询字串'.$addstr,'mtagnew[setting][wherestr]',empty($mtag['setting']['wherestr']) ? '' : $mtag['setting']['wherestr'],'textarea',array('guide' => '函数格式：函数名(\'参数1\',\'参数2\')。完整查询字串包含select、from、where,不要含order及limit。'));
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
	
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['wherestr'] = empty($mtagnew['setting']['wherestr']) ? '' : trim($mtagnew['setting']['wherestr']);
	$mtagnew['setting']['isfunc'] = empty($mtagnew['setting']['isfunc']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));

	//数组参数的处理
	$idvars = array('chids',);
	foreach($idvars as $k){
		if(empty($mtagnew['setting'][$k])){
			unset($mtagnew['setting'][$k]);
		}else $mtagnew['setting'][$k] = implode(',',$mtagnew['setting'][$k]);
	}
}
?>
