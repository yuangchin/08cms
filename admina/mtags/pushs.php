<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? 10 : $mtag['setting']['limits']);
	trbasic('从第几条记录开始显示','mtagnew[setting][startno]',empty($mtag['setting']['startno']) ? '' : $mtag['setting']['startno'],'text',array('guide'=>'设置按当前设置的第几条记录开始，默认为0。'));
	tabfooter();
	
	$pafields = cls_PushArea::Field(empty($mtag['setting']['paid'])?$sclass:$mtag['setting']['paid']);
	$sarr = array();
	for($i = 1;$i < 3;$i++){
		if($v = @$pafields["classid$i"]){
			$sarr[$i]['title'] = $v['cname']." - classid$i";
			$sarr[$i]['options'] = array(0 => '不关联分类',) + cls_field::options_simple($v);
		}
	}
	if($sarr){
		tabheader('分类设置');
		foreach($sarr as $k => $v){
			$str = "<select onchange=\"setIdWithS(this)\" id=\"mselect_mtagnew[setting][classid$k]\" style=\"vertical-align: middle;\">".makeoption($v['options'],@$mtag['setting']['classid'.$k])."</select>";
			$str .= "<input type=\"text\" value=\"".@$mtag['setting']['classid'.$k]."\" onfocus=\"setIdWithI(this)\" name=\"mtagnew[setting][classid$k]\" id=\"mtagnew[setting][classid$k]\"/>";
			trbasic($v['title'],'',$str,'',array('guide' => '可手动输入$xxx或$v[xxx]等激活参数(页面或上级标识传递的可用变量)'));
		}
		tabfooter();
	}
	
	tabheader('高级设置');
	$arr = array();
	empty($_infragment) && $arr['js'] = '使用JS动态调用当前标识解析出来的内容';
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=pushs&typeid=$sclass\" target=\"_blank\">生成</a>";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isfunc]\" name=\"mtagnew[setting][isfunc]\"".(empty($mtag['setting']['isfunc']) ? '' : ' checked').">字串来自函数";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isall]\" name=\"mtagnew[setting][isall]\"".(empty($mtag['setting']['isall']) ? '' : ' checked').">完整查询字串";
	trbasic('筛选查询字串'.$addstr,'mtagnew[setting][wherestr]',empty($mtag['setting']['wherestr']) ? '' : $mtag['setting']['wherestr'],'textarea',array('guide' => '函数格式：函数名(\'参数1\',\'参数2\')。完整查询字串包含select、from、where,不要含order及limit。'));
	trbasic('查询缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if(!isset($mtagnew['setting'][cls_mtags_pushs::SCLASS_VAL]) || $mtagnew['setting'][cls_mtags_pushs::SCLASS_VAL] == '') mtag_error('请指定正确的推送位');
	$mtagnew['setting']['startno'] = trim($mtagnew['setting']['startno']);
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['wherestr'] = empty($mtagnew['setting']['wherestr']) ? '' : trim($mtagnew['setting']['wherestr']);
	$mtagnew['setting']['isfunc'] = empty($mtagnew['setting']['isfunc']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['isall'] = empty($mtagnew['setting']['isall']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
}
