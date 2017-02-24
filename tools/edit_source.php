<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."include/adminm.fun.php";
if(!$curuser->isadmin()) cls_message::show('您没有设置权限！');
$source = cls_cache::cacRead('mysource');
if(!submitcheck('bsubmit')){
	_header(' ◇文章来源管理');
	tabheader('每行保存一个来源','edit_mysource');
	trbasic('来源内容','mysource',implode("\r\n",$source),'textarea',array('w'=>'300','h'=>300));
	tabfooter('bsubmit','保存');
	_footer();
}else{
	_header();
	empty($mysource) && cls_message::show('来源内容为空',axaction(6,M_REFERER));
	$mysource = array_unique(explode("\r\n",$mysource));
	$mysource = array_diff($mysource,array(null,'null','',' '));
	cls_CacheFile::cacSave($mysource,'mysource');
	cls_message::show('保存成功',axaction(2,M_REFERER));
}
?>

