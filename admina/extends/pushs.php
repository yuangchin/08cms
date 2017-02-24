<?PHP
/*
** 推送信息管理的入口脚本，处理推送位设置的扩展脚本
** 
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!($pusharea = cls_PushArea::Config(@$paid))) exit('请指定推送位');
if(!empty($pusharea['script_admin'])){
	_08_FilesystemFile::filterFileParam($pusharea['script_admin']);
	include dirname(__FILE__).DS."{$pusharea['script_admin']}";
}else{
	include dirname(__FILE__).DS."pushs_com.php";
}
