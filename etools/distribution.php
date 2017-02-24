<?php
include_once _08_INCLUDE_PATH."admin.fun.php";

//扩展参数
$exfenxiao = get_fxcfgs();

$cuid = 49;
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'e',
);
$oA = new cls_cuedit($_init);

$oA->add_init('','',array('setCols'=>1));

// 登录的经纪人,黑名单,推荐上限
$exinfo = $oA->fm_fenxiao_check($exfenxiao);

if(submitcheck('bsubmit')){

	$oA->sv_regcode("commu$cuid");
	$oA->sv_repeat(array(), 'both'); //check
	$oA->sv_set_fmdata();//设置$this->fmdata中的值
	$svinfo = $oA->sv_fenxiao_check($exfenxiao); //电话号码,分销资源等
	$oA->sv_items();//保存数据到数组，此时未执行数据库操作
	$oA->sv_insert(array('aids'=>",$svinfo[said],",'ayjs'=>",$svinfo[sayj],"));//执行insert, 附加参数 ,'ip'=>$onlineip,
	$oA->sv_upload();//上传处理
	//附加操作, 发短信, 自定义操作..... 
	//$oA->sv_repeat(array('aid'=>$aid,'tocid'=>$tocid), 'save');
	
	//已推荐 条数(可编辑)
	$oA->db->query("UPDATE {$tblprefix}".atbl(113)." SET tjs = tjs + 1 WHERE aid IN($svinfo[said])");
	
	$oA->sv_finish(array('message'=>'推荐成功！'));//结束时需要的事务，包括操作记录、成功提示等		
}

?>

