<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	$arr = cls_cache::exRead('cfregcodes');
	trbasic('*验证码类型','mtagnew[setting][type]',makeoption($arr,empty($mtag['setting']['type']) ? '' : $mtag['setting']['type']),'select');
	$arr = array();
	$arr['js'] = '使用JS动态调用当前标识解析出来的内容';
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if(empty($mtagnew['setting']['type'])){
		mtag_error('验证码类型不合规范');
	}
}
?>
