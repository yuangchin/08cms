<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
empty($mtag['setting']['listby']) && $mtag['setting']['listby'] = '1';
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? '10' : $mtag['setting']['limits']);
	tabfooter();

	tabheader('列表项目设置');
	$narr = array(
	'0' => array('cname' => '文档模型','arr' => cls_channel::chidsarr(),),
	'1' => array('cname' => '会员模型','arr' => cls_mchannel::mchidsarr(),),
	);
	foreach($grouptypes as $k => $v) $narr[10+$k] = array('cname' => $v['cname'],'arr' => ugidsarr($k),);
	$caco_same_fix = 'caco_same_fix_';
	$caco_diff_fix = 'caco_diff_fix_';
	$cacoarr = array();foreach($narr as $k => $v) $cacoarr[$k] = $v['cname'];
	trbasic('作为列表项','',makeradio('mtagnew[setting][listby]', $cacoarr, $mtag['setting']['listby'],5,"single_list_set(this, '$caco_same_fix')"), '');
	
	$sourcearr = array(0 => '全部',1 => '手动指定',);
	foreach($narr as $k => $v){
		sourcemodule($v['cname'],"mtagnew[setting][source$k]",$sourcearr,empty($mtag['setting']['source'.$k]) ? 0 : $mtag['setting']['source'.$k],
		'1',
		"mtagnew[setting][ids$k][]",$v['arr'],empty($mtag['setting']['ids'.$k]) ? array() : explode(',',$mtag['setting']['ids'.$k]),
		'25%',
		$mtag['setting']['listby'] == $k,$caco_same_fix.$k);
	}
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));

	//数组参数的处理
	$idvars = array('ids0','ids1','ids2',);
	foreach($grouptypes as $k => $v) $idvars[] = 'ids'.(10+$k);
	foreach($idvars as $k){
		if(empty($mtagnew['setting'][$k])){
			unset($mtagnew['setting'][$k]);
		}else $mtagnew['setting'][$k] = implode(',',$mtagnew['setting'][$k]);
	}
}
?>
