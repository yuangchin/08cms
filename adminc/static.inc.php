<?PHP
/**
* 会员中心的商家空间静态操作
* 根据系统的需要可做分离、定制
*/


/* 参数初始化代码 */
@set_time_limit(0);
$_init = array(//会员中心可以不传入任何参数
);

#-----------------
$oA = new cls_member($_init);

$oA->TopHead();//文件头部

$oA->TopAllow();//分析操作权限

/*初始化设置项目*/
$oA->additem('mtcid');//空间模板方案
$oA->additem('static_state');//会员空间静态状态
$oA->additem('mspacepath');//空间静态目录

if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定mchid或mid
	$oA->fm_header("会员静态空间","?action=static");
	
	$oA->fm_items();
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('mem_static','fix');
	
}else{
	//提交后的处理
	$oA->sv_all_static();
}
?>