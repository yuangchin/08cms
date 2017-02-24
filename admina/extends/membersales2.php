<?PHP

/* 参数初始化代码 */
$mid = empty($mid) ? 0 : max(0,intval($mid));
$mchid = 13;//如限制模型定制，可手动指定模型id

if($mid){
	$_init = array(
		'mid' => $mid,//详情一定需要传入mid
	);
}else{
	$_init = array(
		'mchid' => $mchid,//添加一定需要传入mchid
	);
}

#-----------------
$oA = new cls_member($_init);

$oA->TopHead();//文件头部

$oA->TopAllow();//分析操作权限

/*初始化设置项目*/
$oA->additem('mname');//帐号
$oA->additem('password');//密码
$oA->additem('email');//电子邮件
$oA->additem('loupan');//loupan,管理的楼盘
$oA->additem('xiezilou');//loupan,管理的写字楼
$oA->additem('shaopu');//loupan,管理的商铺

$mfexp = array('dantu','ming','danwei','quaere');
foreach($oA->fields as $k => $v){
	if(!in_array($k,$mfexp))
	$oA->additem($k,array('_type' => 'field'));//后台架构字段
}

if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定mchid或mid
	$oA->fm_header("","?entry=extend$extend_str");
	
	$oA->fm_items('mname,password,email');//指定分组项
	
	//$oA->fm_footer();
	
	//$oA->fm_header("其它设置");
	
	$oA->fm_items('xingming,loupan,xiezilou,shaopu');
	//trbasic('管理的楼盘','',getArchives('4',$actuser->info['loupan'],8,'loupan[]','楼盘'),'');
	$oA->fm_items();//剩余项
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	//提交后的处理
	$oA->sv_all_common();
	
	//$actuser->updatefield('loupan',$loupan,"members_$mchid");
}
