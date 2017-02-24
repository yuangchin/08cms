<?PHP
/**
* 标识添加时或修改时表单提交后处理代码
* 添加标识：$TagCodeIsAdd = 1
*/
$TagCodeIsAdd = empty($TagCodeIsAdd) ? 0 : 1;

if(empty($mtagnew['ename'])){
	cls_message::show('标识资料不完全',M_REFERER);
}
if(empty($mtagnew['cname'])) $mtagnew['cname'] = '';
if(!preg_match("/[a-zA-Z][a-z_A-Z0-9]{2,31}/",$mtagnew['ename'])) {
	cls_message::show('标识英文名称 不合规范',M_REFERER);
}
$mtagnew['ename'] = trim(strtolower($mtagnew['ename']));
$usedename = array_keys($mtags);
if(!$TagCodeIsAdd && !$iscopy) $usedename = array_diff($usedename,array($tname));//修改标识时将自身名称排除
in_array($mtagnew['ename'], $usedename) && cls_message::show('标识英文ID重复',M_REFERER);
$tclass = $TagCodeIsAdd ? (empty($mtagnew['tclass']) ? '' : $mtagnew['tclass'])  : $mtag['tclass'];

list($modeAdd,$modeSave) = array($TagCodeIsAdd,1);
isset($tclass) && _08_FilesystemFile::filterFileParam($tclass);
include(dirname(__FILE__) . DS . ($tclass ? $tclass : 'rtag').".php");

$mtagnew['setting'] = empty($mtagnew['setting']) ? array() : $mtagnew['setting'];
if(!empty($mtagnew['setting'])){
	foreach($mtagnew['setting'] as $key => $val){
		if(in_array($key,$unsetvars) && empty($val)) unset($mtagnew['setting'][$key]);
		if(!empty($unsetvars1[$key]) && in_array($val,$unsetvars1[$key])) unset($mtagnew['setting'][$key]);
	}
}
$mtagnew['template'] = empty($mtagnew['template']) ? '' : $mtagnew['template'];
if(!$TagCodeIsAdd) $mtagnew['disabled'] = $iscopy || empty($mtag['disabled']) ? 0 : 1;

$mtag = array(
'cname' => $mtagnew['cname'],
'ename' => $mtagnew['ename'],
'tclass' => $tclass,
'template' => $mtagnew['template'],
'setting' => $mtagnew['setting'],
);
if(!empty($mtagnew['disabled'])) $mtag['disabled'] = 1;

if(empty($textid)) cls_CacheFile::Save($mtag,cls_cache::CacheKey($ttype,$mtagnew['ename']),$ttype);
mtags_update($mtags,$mtag);

if(!$TagCodeIsAdd && !$iscopy && $mtagnew['ename'] != $tname){
	//修改标识本身的英文名，需要删除原标识的缓存
	cls_CacheFile::Del($ttype,$tname,'');
	unset($mtags[$tname]);
}

if(empty($textid)) mtags_cache($mtags,$ttype);
adminlog($TagCodeIsAdd ? '添加模板标识' : ($iscopy ? '复制模板标识' : '修改模板标识'));
