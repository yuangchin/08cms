<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	$datearr = array(
		''			=> '不显示日期',
		'y-m-d'		=> '年-月-日：09-04-07',
		'Y-m-d'		=> '年-月-日：2009-04-07',
		'm-d-y'		=> '月-日-年：04-07-09',
		'm-d-Y'		=> '月-日-年：04-07-2009',
		'y-m'		=> '年-月　 ：09-04',
		'Y-m'		=> '年-月　 ：2009-04',
		'm-d'		=> '　 月-日：04-07',
		'M-d'		=> '　 月-日：Apr-07',
		'F-d'		=> '　 月-日：April-07',
		'M-d-Y'		=> '月-日-年：Apr-07-09',
		'M-d-y'		=> '月-日-年：Apr-07-2009',
		'Y年m月d日'	=> 'XXXX年XX月XX日：2012年02月22日',
		'Y年m月'	=> 'XXXX年XX月　　 ：2012年02月',
		'm月d日'	=> '　　　 XX月XX日 ：04月07日',
	);
	$timearr = array(
		''			=> '不显示时间',
		'H:i:s'		=> '时:分:秒：14:07:05',
		'h:i:s a'	=> '时:分:秒：02:07:05 pm',
		'H:i'		=> '时:分　 ：14:07',
		'A h:i'		=> '时:分　 ：PM 02:07',
		'i:s'		=> '　 分:秒：07:05',
	);
	trbasic('*指定内容来源','mtagnew[setting][tname]',isset($mtag['setting']['tname']) ? $mtag['setting']['tname'] : '','text',array('guide' => '输入格式：字段名aa、变量$a[b]等。'));
	trbasic('日期显示格式','mtagnew[setting][date]',makeoption($datearr,empty($mtag['setting']['date']) ? '0' : $mtag['setting']['date']),'select');
	trbasic('时间显示格式','mtagnew[setting][time]',makeoption($timearr,empty($mtag['setting']['time']) ? '0' : $mtag['setting']['time']),'select');
	tabfooter();
}else{
	$mtagnew['setting']['tname'] = trim($mtagnew['setting']['tname']);
	if(empty($mtagnew['setting']['tname']) || !preg_match("/^[a-zA-Z_\$][a-zA-Z0-9_\[\]]*$/",$mtagnew['setting']['tname'])){
		mtag_error('内容来源设置不合规范');
	}
}
?>
