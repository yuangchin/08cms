<?PHP
/*
** 管理后台脚本，用于会员列表管理
** 兼容不限模型的会员列表，会有指定模型与不分模型的处理差别
** 管理员的管理被排除，即不处理管理组系的特征
*/
foreach(array('grouptypes','mctypes',) as $k) $$k = cls_cache::Read($k);

/* 参数初始化代码 */
$mchid = empty($mchid) ? 0 : max(0,intval($mchid));//可手动指定，不接收外部参数
$_init = array(
'mchid' => $mchid,//会员模型id，必填，可为0
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入mchid
'from' => "",//sql中FROM之后的完整部分，可以通过这里JOIN其它表，例：members m LEFT JOIN members_sub s ON (s.mid=m.mid) LEFT JOIN members_$mchid c ON (c.mid=m.mid)  
);
$mctypes = cls_cache::Read('mctypes'); //如果需要循环认证类型可加这行
/******************/

$oL = new cls_members($_init);

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array('m.mname' => '会员帐号','m.mid' => '会员ID','m.regip'=>'注册IP')));//fields留空则默认为array('m.mname' => '会员帐号','m.mid' => '会员ID')
$oL->s_additem('nchid');//使用nchid来做模型筛选，而不使用mchid，mchid是固定的，当前脚本指定mchid时，本项自动隐藏。
$oL->s_additem('checked');//审核
$oL->s_additem('mctid');//认证，如无有效认证类型，则自动隐藏
$oL->s_additem('orderby');//排序
$oL->s_additem('indays');//多少天内注册
$oL->s_additem('outdays');//多少天前注册
foreach($grouptypes as $k => $v){//定制时，需指定id显示会员组搜索项，不处理管理组系
	$oL->s_additem("ugid$k");//会员组
}
# $oL->s_additem('shi',array('type'=>'field',));//使用可选字段搜索,不限模型时只能搜通用字段

//搜索sql及filter字串处理 ****************
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_addpushs();//推送项目

$oL->o_additem('delete');
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('static');
$oL->o_additem('unstatic');
foreach($grouptypes as $k => $v){//定制时，需指定id调出批量设置项，不处理管理组系
	$oL->o_additem("ugid$k");//会员组
}
#$oL->o_additem('validperiod',array('value' => 30));
#$oL->o_additem('vieworder');


if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array(array('keyword','orderby','nchid','checked','mctid','indays','outdays',));//基本显示项，定制时将重要的会员组显示上来
	$oL->s_adv_point();//设置隐藏区
	$oL->s_view_array();//隐藏区中显示其余项
	$oL->s_footer();
	
	//列表区 ***************
	$oL->m_header();
	//设置列表项目
	$oL->m_additem('selectid');
	$oL->m_additem('mid',array('url'=>'#'));
	$oL->m_additem('subject',array('len' => 40,'field' => 'mname','nourl'=>array(1)));
	//处理了会员标记及空间url的标题，//nourl=1:不需要空间url，array(1,2,3):mchid为1,2,3的不要空间url
	$oL->m_additem('mchid');//会员类型
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	foreach($grouptypes as $k => $v){//定制脚本时，需指定id调整显示方式及位置
		$oL->m_additem("ugid$k");//会员组，将view设为空则默认显示
	}
	foreach($mctypes as $k => $v){//定制脚本时，需指定id调整显示方式及位置
		$oL->m_additem("mctid$k");//会员认证，将view设为空则默认显示
	}
	#$oL->m_additem('shi',array('type'=>'field',));//选择性字段
	$oL->m_additem('regdate',array('type'=>'date',));//注册时间
	$oL->m_additem('regip',array('view'=>'H',));//注册IP
	$oL->m_additem('lastvisit',array('type'=>'date','view'=>'H',));//上次登录时间
	//$oL->m_additem('weixin',array('type'=>'weixin','mcache'=>'new_car_dealers')); //配置配置
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=memberinfo&mid={mid}",'width'=>40,));
	$oL->m_additem('group',array('type'=>'url','title'=>'会员组','mtitle'=>'会员组','url'=>"?entry=extend&extend=membergroup&mid={mid}",'width'=>40,));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=member&mid={mid}",'width'=>40,));
	$oL->m_additem('static');//会员空间静态
	$oL->m_additem('trustee');//会员中心代管
	
	//$oL->m_mcols_style("{selectid} &nbsp;{subject}<br>{shi}/{ting}/{chu}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} &nbsp;{subject}"
	
	//显示索引行
	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区************
	$oL->o_header();
	
	//显示单选项
	$oL->o_view_bools();
	
	//显示推送位
	$oL->o_view_pushs();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('','0');
	
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
