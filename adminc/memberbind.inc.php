<?PHP
/**
* 会员中心的会员修改密码脚本
* 根据系统的需要可做分离、定制
*/


/* 参数初始化代码 */
$_init = array(//会员中心可以不传入任何参数
	'noTrustee' => 1,//禁止代管人操作
);
#-----------------

$oA = new cls_member($_init);

backnav('account','bind');

$oA->TopHead();//文件头部

$oA->TopAllow();//分析操作权限

# 绑定QQ与新浪微博
$oA->additem('openid_sinauid');//openid
#-----------------


//($title,$url)，url中可不指定mchid或mid
$oA->fm_header("绑定我的同步帐号",'#');

$oA->fm_items();

//输入跟submitcheck(按钮名称)相同的值
$oA->fm_footer();

//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
$oA->fm_guide_bm('memberbind','0');

