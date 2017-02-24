<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backnav('othertpl','tcah');
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);

if(!submitcheck('bsubmit')){
	tabheader('重建模板页面缓存',$actionid.'tplcache',"?entry=tplcache",2);
	trbasic('前台模板页面缓存','',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[common]\" value=\"1\">",'');
	trbasic('会员中心模板缓存','',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[mcenter]\" value=\"1\">",'');
	tabfooter('bsubmit');
	a_guide('tplcache');
}else{
	include_once _08_INCLUDE_PATH.'refresh.fun.php';
	
	if(!empty($arcdeal['common'])) clear_dir(TplCacheDirFile(''));
	if(!empty($arcdeal['mcenter'])) clear_dir(TplCacheDirFile('',1));
	cls_message::show('模板页面缓存重建完成！',M_REFERER);
}
?>