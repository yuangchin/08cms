<?PHP
/**
* 会员中心的会员详情脚本
* 根据系统的需要可做分离、定制
*/


/* 参数初始化代码 */
$_init = array(//会员中心可以不传入任何参数
);
#-----------------

$oA = new cls_member($_init);

$oA->TopHead();//文件头部

$oA->TopAllow();//分析操作权限

/*初始化设置项目-->*/
$grouptypes = cls_cache::Read('grouptypes');
foreach($grouptypes as $k => $v) {//会员中心仅用户手动组有效
	$oA->additem('ugid'.$k,array('_type' => 'ugid','onlyset' => 1));//会员组
}

$oA->additem('email');//电子邮件
$oA->additem('mtcid');//空间模板方案
foreach($oA->fields as $k => $v){//后台架构字段
	$oA->additem($k,array('_type' => 'field'));
}
$oA->additem('webcall');//400电话设置
//$oA->items_did[] = 'mtcid';
//隐藏经纪人模型中的字段：黑名单
$oA->items_did[] = 'blacklist';
// 专家字段-屏蔽
// mtcid - 只一套模版-屏蔽
$mfexp = array('dantu','ming','danwei','quaere','mtcid');
foreach($mfexp as $k){//后台架构字段
	$oA->items_did[] = $k;
}

//如果是二手房、出租房源跳转过来的链接，提交之后直接返回发布页面
$type = empty($type)?'':$type;
$typeStr = strstr(M_REFERER,'chuzuadd')?"&type=chuzuadd":(strstr(M_REFERER,'chushouadd')?'&type=chushouadd':'');

$curuser = cls_UserMain::CurUser(); 
$lxdh = $curuser->info['lxdh'];

if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定mchid或mid
	$oA->fm_header("","?action=$action$typeStr");
	
	//$oA->fm_items('email,image,xingming,szqy,lxdh,companynet,companyadr');
	$oA->fm_items();
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	echo "<script type='text/javascript'>_08cms_validator.init('ajax','fmdata[lxdh]',{url:'{$cms_abs}"._08_Http_Request::uri2MVC("ajax=checkUserPhone&old=$lxdh&val=%1")."'});</script>";
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
    //上传头像增加积分、会员中心发布页面跳转过来完善资料后自动跳回原来发布页面
    $oA->sv_all_common_ex($type);
}
?>