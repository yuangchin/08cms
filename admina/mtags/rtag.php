<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!submitcheck('bmtagadd') && !submitcheck('bmtagsdetail')){
	$template = cls_tpl::load(@$mtag['template'],0);
	trbasic('*模板文件名称','mtagnew[template]',empty($mtag['template']) ? '' : ((empty($iscopy) ? '' : 'cp_').$mtag['template']),'text',array('validate' => makesubmitstr('mtagnew[template]',1,0,3,30,'text','/^[a-zA-Z]{1}[a-zA-Z0-9-_.]+(\.html|\.htm)$/'),'guide' => "文件名称只允许包含字母、数字、下划线(-_)、点(.)等字符,并以字母开头，以htm或html为扩展名"));               
    $older = empty($iscopy)?(empty($mtag['template'])?'':$mtag['template']):'';
	$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC("ajax=check_mtagtemplate&val=%1&older={$older}/");
	echo _08_HTML::AjaxCheckInput('mtagnew[template]', $ajaxURL);
    templatebox('页面模板','templatenew',$template,30,110);
   	
	tabfooter();
}else{
	$mtagnew['template'] = trim($mtagnew['template']);
	if($re = _08_FilesystemFile::CheckFileName($mtagnew['template'])) cls_message::show($re,M_REFERER);
	cls_Array::array_stripslashes($templatenew);
	// 不管是否有扩展模版,这里都用cls_tpl::rel_path默认定位到当前模版目录; 如果url中isbase=1则定位到基础模版
	if(@!str2file($templatenew,cls_tpl::rel_path($mtagnew['template'],'get'))) cls_message::show('模板保存不成功。',M_REFERER);
}
