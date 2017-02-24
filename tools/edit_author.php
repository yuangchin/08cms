<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."include/adminm.fun.php";
if(!$curuser->isadmin()) cls_message::show('您没有设置权限！');
$author = cls_cache::cacRead('myauthor');
if(!submitcheck('bsubmit')){
	_header(' ◇文章作者管理');
	tabheader('把作者姓名用半角逗号","分开','edit_mysource');
	trbasic('作者','myauthor',implode(",",$author),'textarea',array('w'=>'300','h'=>300));
	tabfooter('bsubmit','保存');
	_footer();
}else{
	_header();
	empty($myauthor) && cls_message::show('来源内容为空',axaction(6,M_REFERER));
	$myauthor = str_replace("，",',',$myauthor);
	$myauthor = array_unique(explode(",",$myauthor));
	$myauthor = array_diff($myauthor,array(null,'null','',' '));	
	cls_CacheFile::cacSave($myauthor,'myauthor');
	cls_message::show('保存成功',axaction(2,M_REFERER));
}
?>

