<?PHP
/**
* 管理后台用于修改管理员本人的密码
*/


/* 参数初始化代码 */
$_init = array(//会员中心可以不传入任何参数
	'mid' => $curuser->info['mid'],//禁止代管人操作
);
#-----------------

$oA = new cls_member($_init);
$oA->TopHead(array('isself' => 1,));//文件头部
//$oA->TopAllow();//分析操作权限-修改自己的pw，不需要权限

/*初始化设置项目-->*/
$oA->additem('password_self');//带旧密码验证及二次输入密码
#-----------------

if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定mchid或mid
	$oA->fm_header("修改我的密码","?entry=extend$extend_str");
	
	$oA->fm_items();
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('bmemberpwd','0');
	
}else{
	//提交后的处理
	$oA->sv_all_password_self();
}
