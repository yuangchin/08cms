<?php
die('不用了');
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/adminm.fun.php";

set_time_limit(0);
$chid = empty($chid) ? 0 : max(1,intval($chid));
$cuid = empty($cuid) ? 0 : max(1,intval($cuid));
$aid  = empty($aid) ? 0 : max(1,intval($aid));
$td_num = 5;//每行单元格的个数
$filename = empty($filename) ? 'Excel' : trim($filename);//excel的文件名
$where_str = empty($q)?'':stripslashes(trim($q));
cls_env::deRepGlobalValue($where_str);
$p = empty($p)?'':stripslashes(trim($p));

//防篡改
//（点击导出excel，判断一次链接是否被篡改，提交表单，跳转后，再次判断链接是否被篡改）
($p != md5(urlencode($where_str).$authkey)) && exit('No Permission');


if($cuid){
	//array_intersect($a_cuids,array(-1,$cuid)) || cls_message::show('没有指定交互内容的管理权限');
	if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
}

if(!empty($chid) && !empty($cuid)){//用来判断链接传递过来的交互是不是属于某个模型的
	!in_array($chid,$commu['chids']) && !empty($commu['chids']) && cls_message::show("ID为".$chid."的文档模型与ID为".$cuid."的交互不对应。");
}

$excel = new cls_exportexcel;
if(!empty($fmdata) && count($fmdata)<2){	
	cls_message::show('请选择导出项目。');
}	
$excel->ExportExcel($filename,$fmdata,$where_str);




?>
