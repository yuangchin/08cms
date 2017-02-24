<?PHP
/*
** 推送加载管理
** 不同类型的推送位的加载入口脚本
** 
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!($pusharea = cls_PushArea::Config(@$paid))) exit('请指定推送位');
if(!empty($pusharea['script_load'])){
	_08_FilesystemFile::filterFileParam($pusharea['script_load']);
	include dirname(__FILE__).DS."{$pusharea['script_load']}";
}else{
	if(empty($pusharea['sourcetype'])) exit('来源类型未知');
	_08_FilesystemFile::filterFileParam($pusharea['sourcetype']);
	include dirname(__FILE__).DS."push_load_{$pusharea['sourcetype']}.php";
}
