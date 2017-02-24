<?PHP
/*
** 管理后台脚本，用于会员列表管理
** 兼容不限模型的会员列表，会有指定模型与不分模型的处理差别
** 管理员的管理被排除，即不处理管理组系的特征
*/

/* 参数初始化代码 */
$paid = cls_PushArea::InitID(@$paid);//初始化推荐位ID
if(!($pusharea = cls_PushArea::Config($paid))) exit('请指定正确的推送位');
if($pusharea['sourcetype'] != 'members') exit('推送位来源应为会员类型'); 

$_init = array(
'mode' => 'pushload',
'paid' => $paid,//推送位id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入mchid
'select' => " m.*,s.szqy,s.xingming,s.image ",
'from' => "{$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid = s.mid ",
//sql中FROM之后的完整部分，可以通过这里JOIN其它表，例：members m LEFT JOIN members_sub s ON (s.mid=m.mid) LEFT JOIN members_$mchid c ON (c.mid=m.mid)

);
/******************/

$oL = new cls_members($_init);

//针对经纪公司所做的SQL搜索经纪公司名称
$oL->A['select'] = $oL->A['select']." ,c.* ";
$oL->A['from'] =  $oL->A['from']." INNER JOIN {$tblprefix}members_".$oL->A['mchid']." c ON c.mid=m.mid ";

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array(),));//fields留空则默认为array('m.mname' => '会员帐号','m.mid' => '会员ID')

$grouptypes = cls_cache::Read('grouptypes');
foreach($grouptypes as $k => $v){//定制时，需指定id显示会员组搜索项，不处理管理组系
	$oL->s_additem("ugid$k");//会员组
}
$oL->s_additem('szqy');
$oL->s_additem('orderby');//排序
$oL->s_additem('indays');//多少天内注册



//搜索sql及filter字串处理 ****************
$oL->s_deal_str();


if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	
	//列表区 ***************
	$oL->m_header();
	//设置列表项目
	$oL->m_additem('selectid');
	
	$oL->m_additem('subject',array('len' => 40,'field' => 'mname','pic'=>'image'));//处理了会员标记及空间url的标题，nourl表示不需要空间url	
	
	$oL->A['mchid']==2 && $oL->m_additem('xingming',array('title'=>'经纪人名字','mtitle'=>'{xingming}','len' => 40));
	$oL->A['mchid']==3 && $oL->m_additem('cmane',array('title'=>'经纪公司名称','mtitle'=>'{cmane}','len' => 40));
	foreach($grouptypes as $k => $v){//定制脚本时，需指定id调整显示方式及位置
		$oL->m_additem("ugid$k");//会员组，将view设为空则默认显示
	}
	$oL->m_additem('regdate',array('type'=>'date',));//注册时间
	$oL->m_additem('lastvisit',array('type'=>'date','view'=>'H',));//上次登录时间 
    $oL->m_additem('szqy',array('type'=>'szqy','title'=>'所在区域','view'=>'S'));
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=memberinfo&mid={mid}",'width'=>40,'view'=>'H',));
	
	//显示索引行

	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	$oL->o_end_form('bsubmit','加载');
	$oL->guide_bm('','0');
	
	
}else{
	//推送加载的操作
	$oL->sv_o_pushload();
	
}
