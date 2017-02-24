<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide'=>'系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? 10 : $mtag['setting']['limits']);
	trbasic('从第几条记录开始显示','mtagnew[setting][startno]',empty($mtag['setting']['startno']) ? '' : $mtag['setting']['startno'],'text',array('guide'=>'设置按当前设置的第几条记录开始，默认为0。'));
	echo "<script>function setdisabled(showid,hideid){var showobj=\$id(showid),hideobj=\$id(hideid),sinput=showobj.getElementsByTagName('input');hinput=hideobj.getElementsByTagName('input');showobj.style.display='';hideobj.style.display='none';for(var i=0;i<sinput.length;i++){sinput[i].disabled=false}for(var i=0;i<hinput.length;i++){hinput[i].disabled=true}}</script>";
	echo "<script>window.onload = function(){setdisabled(".(empty($mtag['setting']['ids'])?"'ids_mod1','ids_mod2'":"'ids_mod2','ids_mod1'").");}</script>";
	$str = "<input class=\"radio\" type=\"radio\" name=\"select_mode\" value=\"0\" onclick=\"setdisabled('ids_mod1','ids_mod2');\"".(empty($mtag['setting']['ids']) ? ' checked' : '').">常规设置 &nbsp;\n";
	$str .= "<input class=\"radio\" type=\"radio\" name=\"select_mode\" value=\"1\" onclick=\"setdisabled('ids_mod2','ids_mod1');\"".(empty($mtag['setting']['ids']) ? '' : ' checked').">手动指定id<br>\n";
	trbasic('列表内容设置方式>>','',$str,'');
	tabfooter();
	echo "<div id=\"ids_mod2\" style=\"display:".(empty($mtag['setting']['ids']) ? 'none' : '')."\">";
	tabheader('手动指定设置');
	trbasic('*手动指定列表id','mtagnew[setting][ids]',empty($mtag['setting']['ids']) ? '' : $mtag['setting']['ids'],'text',array('guide' => '指定多个id使用半角逗号分隔，如：5,80,600','w' => 50,));
	aboutarchive(empty($mtag['setting']['ids']) ? '' : $mtag['setting']['ids'],'tagarchives');
	tabfooter();
	
	tabheader('更多设置');
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=archives&typeid=$sclass\" target=\"_blank\">生成</a>";
	trbasic('排序字串'.$addstr,'mtagnew[setting][orderstr]',empty($mtag['setting']['orderstr']) ? '' : $mtag['setting']['orderstr'],'text',array('w' => 50));
	trbasic('强制索引字串','mtagnew[setting][forceindex]',empty($mtag['setting']['forceindex']) ? '' : $mtag['setting']['forceindex'],'text',array('guide' => '格式举例：a.mclicks,在设置前请确认当前的查询中包含a别名的表，及该表中建有mclicks的索引。'));
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	$arr = array();
	empty($_infragment) && $arr['js'] = '使用JS动态调用当前标识解析出来的内容';
	$arr['validperiod'] = '只允许有效期内的内容';
	$arr['detail'] = '需要模型字段表的内容(仅当列表只允许单个模型时选择才有效)';
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('更多其它设置','',$str,'');
	tabfooter();
	echo '</div>';	

	echo "<div id=\"ids_mod1\" style=\"display:".(empty($mtag['setting']['ids']) ? '' : 'none')."\">";
	tabheader('基本筛选设置');
/*
	$chsourcearr = array('0' => '不排除','1' => '指定排除',);
	sourcemodule('排除以下文档模型',
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
				cls_catalog::ccidsarr(0, $sclass),
				empty($mtag['setting']['caids']) ? array() : (is_array($mtag['setting']['caids']) ? $mtag['setting']['caids'] : explode(',',$mtag['setting']['caids']))
				);
    if (isset($entry) && ($entry !== 'fragments'))
    {
	    echo "<input type=\"hidden\" id='mt_chids' name=\"mtagnew[setting][chids][]\" value=\"{$sclass}\">\n";
    }
    
	foreach($cotypes as $k => $cotype){
		if(!empty($sclass) && !coid_in_chid($k,$sclass)) continue;
		$coname = "$cotype[cname]".(empty($cotype['self_reg']) ? '' : '<!--is_self_reg-->'); //自动类系标记,用于函数里判断并加注释
		sourcemodule("$coname"."&nbsp;&nbsp;&nbsp;<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][ccidson$k]\" value=\"1\"".(empty($mtag['setting']['ccidson'.$k]) ? "" : " checked").">含子分类",
					"mtagnew[setting][cosource$k]",
					$sourcearr,
					empty($mtag['setting']['cosource'.$k]) ? '0' : $mtag['setting']['cosource'.$k],
					'1',
					"mtagnew[setting][ccids$k][]",
					cls_catalog::ccidsarr($k),
					empty($mtag['setting']['ccids'.$k]) ? array() : (is_array($mtag['setting']['ccids'.$k]) ? $mtag['setting']['ccids'.$k] : explode(',',$mtag['setting']['ccids'.$k]))
					);
	}
	tabfooter();
	tabheader('更多设置');
	$arr = array('' => '普通列表','in' => '指定id的辑内列表','belong' => '指定id的所属合辑列表','relate' => '指定id的相关文档列表',);
	trbasic('指定列表模式','mtagnew[setting][mode]',makeoption($arr,empty($mtag['setting']['mode']) ? 0 : $mtag['setting']['mode']),'select');
	$arr = array(0 => '不设置',);foreach($abrels as $k => $v) $arr[$k] = $v['cname'];
	trbasic('指定合辑项目','mtagnew[setting][arid]',makeoption($arr,empty($mtag['setting']['arid']) ? 0 : $mtag['setting']['arid']),'select',array('guide' => '当模式为辑内列表或所属合辑列表时需要指定'));
	trbasic('指定相关id','mtagnew[setting][id]',empty($mtag['setting']['id']) ? '' : $mtag['setting']['id'],'text',array('guide' => '手动输入文档aid,输入为空默认为激活文档'));
	$arr = array();
	empty($_infragment) && $arr['js'] = '使用JS动态调用当前标识解析出来的内容';
	$arr['space'] = '仅显示激活会员的文档';
	$arr['ucsource'] = '只显示激活个人分类的文档';
	$arr['validperiod'] = '只允许有效期内的内容';
	$arr['detail'] = '需要模型字段表的内容(仅当列表只允许单个模型时选择才有效)';
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('更多其它设置','',$str,'');
	tabfooter();

	tabheader('高级选项');
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=archives&typeid=$sclass".(empty($new_action) ? '' : $new_action). "\" target=\"_blank\">生成</a>";
	trbasic('排序字串'.$addstr,'mtagnew[setting][orderstr]',empty($mtag['setting']['orderstr']) ? '' : $mtag['setting']['orderstr'],'text',array('w' => 50));
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isfunc]\" name=\"mtagnew[setting][isfunc]\"".(empty($mtag['setting']['isfunc']) ? '' : ' checked').">字串来自函数";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isall]\" name=\"mtagnew[setting][isall]\"".(empty($mtag['setting']['isall']) ? '' : ' checked').">完整查询字串";
	trbasic('筛选查询字串'.$addstr,'mtagnew[setting][wherestr]',empty($mtag['setting']['wherestr']) ? '' : $mtag['setting']['wherestr'],'textarea',array('guide' => '函数格式：函数名(\'参数1\',\'参数2\')。完整查询字串包含select、from、where,不要含order及limit。'));
	trbasic('强制索引字串','mtagnew[setting][forceindex]',empty($mtag['setting']['forceindex']) ? '' : $mtag['setting']['forceindex'],'text',array('guide' => '格式举例：a.mclicks,在设置前请确认当前的查询中包含a别名的表，及该表中建有mclicks的索引。'));
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
	echo '</div>';	
	
	if(empty($_infragment)){
		tabheader('标识分页设置');
		trbasic('启用列表分页','mtagnew[setting][mp]',empty($mtag['setting']['mp']) ? 0 : $mtag['setting']['mp'],'radio');
		trbasic('总结果数(空为不限)','mtagnew[setting][alimits]',isset($mtag['setting']['alimits']) ? $mtag['setting']['alimits'] : '');
		trbasic('是否简易的分页导航','mtagnew[setting][simple]',empty($mtag['setting']['simple']) ? '0' : $mtag['setting']['simple'],'radio');
		trbasic('分页导航的页码长度','mtagnew[setting][length]',isset($mtag['setting']['length']) ? $mtag['setting']['length'] : '');
		tabfooter();
	}
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if(!empty($select_mode) && empty($mtagnew['setting']['ids'])) mtag_error('请手动指定id');
	if(!isset($mtagnew['setting'][cls_mtags_archives::CHIDS][0]) || $mtagnew['setting'][cls_mtags_archives::CHIDS][0] == '') mtag_error('请指定正确的文档模型项目');
    $mtagnew['setting'][cls_mtags_archives::CHSOURCE] = (empty($mtagnew['setting'][cls_mtags_archives::CHIDS][0]) ? 1 : 2);
	$mtagnew['setting']['ucsource'] = empty($mtagnew['setting']['ucsource']) ? 0 : $mtagnew['setting']['ucsource'];
	$mtagnew['setting']['startno'] = trim($mtagnew['setting']['startno']);
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['alimits'] = max(0,intval(@$mtagnew['setting']['alimits']));
	$mtagnew['setting']['length'] = max(0,intval(@$mtagnew['setting']['length']));
	$mtagnew['setting']['orderstr'] = empty($mtagnew['setting']['orderstr']) ? '' : trim($mtagnew['setting']['orderstr']);
	$mtagnew['setting']['wherestr'] = empty($mtagnew['setting']['wherestr']) ? '' : trim($mtagnew['setting']['wherestr']);
	$mtagnew['setting']['isfunc'] = empty($mtagnew['setting']['isfunc']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['isall'] = empty($mtagnew['setting']['isall']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
	
	$idvars = array('caids','nochids');//数组参数的处理
    if(empty($select_mode)) $idvars[] = 'chids';
	foreach($cotypes as $k => $v) $idvars[] = 'ccids'.$k;
	foreach($idvars as $k){
		if(empty($mtagnew['setting'][$k])){
			unset($mtagnew['setting'][$k]);
		}else $mtagnew['setting'][$k] = @implode(',',$mtagnew['setting'][$k]);
	}
	if(@$mtagnew['setting'][cls_mtags_archives::CHSOURCE] != 2) unset($mtagnew['setting']['chids']);
	if(empty($mtagnew['setting']['nochsource']) || !empty($mtagnew['setting'][cls_mtags_archives::CHSOURCE])) unset($mtagnew['setting']['nochids']);
	unset($mtagnew['setting']['nochsource']);
	$mtagnew['setting']['forceindex'] = trim($mtagnew['setting']['forceindex']);
	if(empty($mtagnew['setting']['forceindex'])) unset($mtagnew['setting']['forceindex']);
	if(empty($select_mode)){
		unset($mtagnew['setting']['ids']);
	}else{
		$idvars = array('caids','caidson','nochsource','nochids','wherestr','isfunc','isall','space','ucsource','mode','arid','id',);
		foreach($cotypes as $k => $v){
			$idvars[] = 'ccids'.$k;
			$idvars[] = 'ccidson'.$k;
		}
		foreach($idvars as $k) unset($mtagnew['setting'][$k]);
	}
}


