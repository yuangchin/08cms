<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
//backallow('commu') || cls_message::show('no_apermission');	
set_time_limit(0);
$chid = empty($chid) ? 0 : max(1,intval($chid));
$cuid = empty($cuid) ? 0 : max(1,intval($cuid));
$mchid = empty($mchid) ? 0 : max(1,intval($mchid));
$aid  = empty($aid) ? 0 : max(1,intval($aid));
$filename = empty($filename) ? 'Excel' : trim($filename);//excel的文件名
$where_str = empty($q)?'':stripslashes(trim($q)); 
$p = empty($p)?'':stripslashes(trim($p)); 
if(!empty($cuid)){
	backallow('commu') || cls_message::show('no_apermission');	
}elseif(!empty($mchid)){
	backallow('member') || cls_message::show('no_apermission');
}else{
	backallow('normal') || cls_message::show('no_apermission');	
}


//echo ",$authkey,";
//防篡改
//（点击导出excel，判断一次链接是否被篡改，提交表单，跳转后，再次判断链接是否被篡改）
($p != md5($where_str.$authkey)) && exit('No Permission');
//$where_str = cls_string::urlBase64($where_str,1);


if($cuid){
	array_intersect($a_cuids,array(-1,$cuid)) || cls_message::show('没有指定交互内容的管理权限');
	if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
}

if(!empty($chid) && !empty($cuid)){//用来判断链接传递过来的交互是不是属于某个模型的
	!in_array($chid,$commu['chids']) && !empty($commu['chids']) && cls_message::show("ID为".$chid."的文档模型与ID为".$cuid."的交互不对应。");
}


$excel = new cls_exportexcel;
if(!submitcheck('bsubmit')){
	aheader();	
	$excel->ShowFieldsTable($chid,$cuid,$mchid,"请选择导出数据的项目",$where_str,"?entry=extend$extend_str");
	a_guide('exportexcel');
}else{
	if(count($fmdata)<2){
		aheader(); 
	 	cls_message::show('请选择导出项目。',"?entry=$entry$extend_str&chid=$chid&cuid=$cuid&mchid=$mchid&aid=$aid&q=$q&p=$p");
	}
	$excel->ExportExcel($filename,$fmdata,cls_string::urlBase64($where_str,1));
}

?>
