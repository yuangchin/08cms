<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('farchive')) cls_message::show($re); # 副件管理权限

# 分析副件分类
$fcaid = cls_fcatalog::InitID(@$fcaid);
if(!cls_fcatalog::Config($fcaid)) cls_message::show('请指定副件分类');
if($re = $curuser->NoBackPmByTypeid($fcaid,'fcaid')) cls_message::show($re);# 当前副件分类的后台管理权限


$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;

$fromsql = "FROM {$tblprefix}farchives a";
$wheresql = "WHERE a.fcaid='$fcaid'";
$filterstr = "&fcaid=$fcaid";

$checked = isset($checked) ? (int)$checked : '-1';
if($checked != -1){
	$wheresql .= " AND a.checked='$checked'";
	$filterstr .= "&checked=".$checked;
}

$valid = isset($valid) ? (int)$valid : '-1';
if($valid != -1){
	if($valid){
		$wheresql .= " AND a.startdate<'$timestamp' AND (a.enddate='0' OR a.enddate>'$timestamp')";
	}else{
		$wheresql .= " AND (a.startdate>'$timestamp' OR (a.enddate!='0' AND a.enddate<'$timestamp'))";
	}
	$filterstr .= "&valid=".$valid;
}


$keyword = empty($keyword) ? '' : $keyword;
if($keyword){
	$wheresql .= " AND (a.mname".sqlkw($keyword,1)." OR a.subject".sqlkw($keyword,1).")";
	$filterstr .= "&keyword=".rawurlencode(stripslashes($keyword));
}

$area_coid = cls_fcatalog::Config($fcaid,'farea'); //是否关联地区
if($area_coid){
	$farea = empty($farea) ? '0' : intval($farea);
	if($farea){ // farea LIKE '%,$farea,%'
		$wheresql .= " AND FIND_IN_SET('$farea',farea) "; 
		$filterstr .= "&farea=$farea";
	}
	$area_arr = cls_cache::Read('coclasses',$area_coid);
} 

$vflag = ''; //是否有 浏览连接
if(!cls_fcatalog::Config($fcaid,'ftype')){
	$fields = cls_fcatalog::Field($fcaid);
	foreach($fields as $k => $v){ //multitext,
		if(in_array($v['datatype'],array('htmltext','image','flash'))){
			$vflag = '(点击浏览图片或效果)';
			break;
		}
	}
}

$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
switch(cls_fcatalog::Config($fcaid,'ftype'))
{
    case 1 : $file = $path . 'adv_managements.php'; break;
    default : $file =  $path . 'farchives_list.php'; break;
}

if(is_file($file)) {
    include $file;
    exit;
} else {
    exit('系统错误，该管理页不存在！');
}