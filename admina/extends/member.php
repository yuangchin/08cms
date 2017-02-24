<?PHP
/**
* 管理后台的会员详情脚本
* 根据系统的需要可做分离、定制
*/


/* 参数初始化代码 */
$mid = empty($mid) ? 0 : max(0,intval($mid));
$_init = array(
	'mid' => $mid,//详情一定需要传入mid
);

#-----------------
$oA = new cls_member($_init);

$oA->TopHead();//文件头部

$oA->TopAllow();//分析操作权限

/*初始化设置项目*/
$oA->additem('mname');//帐号
$oA->additem('password');//密码
$oA->additem('email');//电子邮件
$oA->additem('mtcid');//空间模板方案
foreach($oA->fields as $k => $v){
	$oA->additem($k,array('_type' => 'field'));//后台架构字段
}

if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定mchid或mid
	$oA->fm_header("","?entry=extend$extend_str");
	
	$oA->fm_items('mname,password,email,mtcid');
	
	#$oA->fm_footer();
	
	#$oA->fm_header("其它设置");
	
	$oA->fm_items();
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	//提交后的处理
	$oA->sv_all_common();
}
