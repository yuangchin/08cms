<?PHP

/* 参数初始化代码 */
$mchid = empty($mchid) ? 0 : max(0,intval($mchid));//可手动指定，不接收外部参数
if(!in_array($mchid,array(0,1,2))) $mchid = 0;
$_init = array(
'mchid' => $mchid,//会员模型id，必填，可为0
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入mchid
'from' => "{$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid = s.mid ",
'select'=>"*,s.szqy ",
//sql中FROM之后的完整部分，可以通过这里JOIN其它表，例：members m LEFT JOIN members_sub s ON (s.mid=m.mid) LEFT JOIN members_$mchid c ON (s.mid=m.mid)  
);
/******************/

$oL = new cls_members($_init);

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array('m.mname' => '会员帐号','m.mid' => '会员ID','s.ming' => '专家名称','s.lxdh' => '联系电话'),));//fields留空则默认为array('m.mname' => '会员帐号','m.mid' => '会员ID')
$oL->s_additem('nchid');//使用nchid来做模型筛选，而不使用mchid，mchid是固定的，当前脚本指定mchid时，本项自动隐藏。
$oL->s_additem('checked');//审核
$oL->s_additem('mctid');//认证，如无有效认证类型，则自动隐藏
$oL->s_additem('orderby');//排序
$oL->s_additem('indays',array('title'=>'天内注册'));//多少天内注册
$oL->s_additem('outdays',array('title'=>'天前注册'));//多少天前注册
$oL->s_additem('gtype_enddate',array('groupnum'=>14,'title'=>'天内失效'));//多少天内失效

$grouptypes = cls_cache::Read('grouptypes');
foreach($grouptypes as $k => $v){//定制时，需指定id显示会员组搜索项，不处理管理组系
	$oL->s_additem("ugid$k");//会员组
}
# $oL->s_additem('shi',array('type'=>'field',));//使用可选字段搜索,不限模型时只能搜通用字段

//搜索sql及filter字串处理 ****************
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_addpushs();//推送项目

$oL->o_additem('delete');
//$oL->o_additem('delkeep');
$oL->o_additem('check');
$oL->o_additem('uncheck');
if($mchid!=1){
$oL->o_additem('static');
$oL->o_additem('unstatic');
}
foreach($grouptypes as $k => $v){//定制时，需指定id调出批量设置项，不处理管理组系
	$oL->o_additem("ugid$k");//会员组
}

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array(array('keyword','checked','mctid','indays','outdays','gtype_enddate'));//基本显示项，定制时将重要的会员组显示上来
	$oL->s_adv_point();//设置隐藏区
	$oL->s_view_array();//隐藏区中显示其余项
	$sfmethod = $mchid>1 ? 's_footer_ex' : 's_footer';
	$oL->$sfmethod("?entry=extend&extend=export_excel&mchid=$mchid&filename=member$mchid"); //$oL->s_footer();
	
	//列表区 ***************
	$oL->m_header();
	//设置列表项目
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 40,'field' => 'mname','title'=>'用户名','nourl'=>array(1,13)));//处理了会员标记及空间url的标题，nourl表示不需要空间url
	$oL->m_additem('regip',array('type'=>'regip','title'=>'注册IP','len' => 40,'view'=>'H'));
	$mchid == 2 && $oL->m_additem('xingming',array('title'=>'姓名'));
	$oL->m_additem('mchid');//会员类型
	$oL->m_additem('szqy',array('type'=>'szqy','title'=>'所在区域'));
    $mchid == 2 && $oL->m_additem('ssgs',array('type'=>'ssgs','title'=>'所属公司','len'=>18));
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	foreach($grouptypes as $k => $v){//定制脚本时，需指定id调整显示方式及位置
		if($k== 14) $oL->m_additem("ugid$k",array('view'=>'S'));
		else $oL->m_additem("ugid$k",array('view'=>'H'));//会员组，将view设为空则默认显示
	}
	
	$mctypes = cls_cache::Read('mctypes');    
	foreach($mctypes as $k => $v){//定制脚本时，需指定id调整显示方式及位置
		$oL->m_additem("mctid$k",array('view'=>'H'));//会员认证，将view设为空则默认显示
	}
	#$oL->m_additem('shi',array('type'=>'field',));//选择性字段	
	$oL->m_additem('regdate',array('type'=>'date',));//注册时间
    $oL->m_additem('grouptype14date',array('title'=>'失效日期','type'=>'date',));//注册时间
	$mchid == 2 && $oL->m_additem('weixin',array('type'=>'weixin','mcache'=>'brokers'));
	$oL->m_additem('lastvisit',array('type'=>'date','view'=>'H',));//上次登录时间
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=memberinfo&mid={mid}",'width'=>30));
	$oL->m_additem('group',array('type'=>'url','title'=>'会员组','mtitle'=>'会员组','url'=>"?entry=extend&extend=membergroup&mid={mid}",'width'=>40,));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=memberedit&mid={mid}",'width'=>30,));
	$mchid != 1 && $oL->m_additem('static');//会员空间静态
	$oL->m_additem('trustee',array('title'=>'代管'));//会员中心代管
	
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
	$oL->o_view_bools('',array(),8);
	
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
