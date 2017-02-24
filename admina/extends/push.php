<?PHP
/*
** 推送添加与详情编辑的入口脚本，处理推送位设置的扩展脚本
** 
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!($pusharea = cls_PushArea::Config(@$paid))) exit('请指定推送位');
if(!empty($pusharea['script_detail'])){
	_08_FilesystemFile::filterFileParam($pusharea['script_detail']);
	include dirname(__FILE__).DS."{$pusharea['script_detail']}";
}else{
	include dirname(__FILE__).DS."push_com.php";
}
