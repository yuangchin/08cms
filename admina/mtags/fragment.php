<?php
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	trbasic('碎片内容提取url','mtagnew[setting][url]',empty($mtag['setting']['url']) ? '' : $mtag['setting']['url'],'text',array('guide' => '通过碎片所在系统的碎片管理中查看调用url','w' => 80));
	trbasic('内容缓存周期(秒)','mtagnew[setting][ttl]',empty($mtag['setting']['ttl']) ? 0 : $mtag['setting']['ttl'],'text',array('guide' => '单位：秒。仅扩展缓存开启，模板调试模式关闭的情况下有效。'));
	trbasic('内容提取超时时间','mtagnew[setting][timeout]',empty($mtag['setting']['timeout']) ? 0 : $mtag['setting']['timeout'],'text',array('guide' => '单位：秒，超过设置时间则放弃读取。'));
	tabfooter();
}else{
	if(empty($mtagnew['setting']['url'])) mtag_error('请输入碎片内容提取url');
	$mtagnew['setting']['ttl'] = max(0,intval($mtagnew['setting']['ttl']));
}
?>
