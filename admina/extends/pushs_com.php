<?PHP
/*
** 推送内容列表管理
** 
** 
*/
/* 参数初始化代码 */
$paid = cls_PushArea::InitID(@$paid);//接受外部传paid

#-----------------

$oL = new cls_pushs(array(
'paid' => $paid,//推送位id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid
'pre' => 'p.',//默认的推送信息表前缀
'from' => "",//sql中的FROM部分
'select' => "",//sql中的SELECT部分
//'where' => "p.pid3 = $pid355",
));

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('classid1',array('type'=>'field',));
$oL->s_additem('classid2',array('type'=>'field',));
$oL->s_additem('checked');
$oL->s_additem('valid');
$oL->s_additem('loadtype');
$oL->s_additem('orderby');
$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sqlall及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('refresh');//同步来源
$oL->o_additem('delete');
$oL->o_additem("classid1");
$oL->o_additem("classid2");

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	//$oL->s_view_array(array('keyword','orderby','checked',));//固定显示项
	//$oL->s_adv_point();//设置隐藏区
	$oL->s_view_array();
	$oL->s_footer();
	
	
	//显示列表区头部 ***************
	$oL->m_header();

	//设置列表项目
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 40,));
	$oL->m_additem("classid1",array('type'=>'field',));
	$oL->m_additem("classid2",array('type'=>'field',));
	$oL->m_additem('valid');
	$oL->m_additem('fixedorder');
	$oL->m_additem('vieworder');
	$oL->m_additem('checked',array('type' => 'bool','title'=>'审核',));
	$oL->m_additem('createdate',array('type'=>'date','title'=>'推送日期','view' => 'H',));
	$oL->m_additem('startdate',array('type'=>'date','title'=>'生效日期',));
	$oL->m_additem('enddate',array('type'=>'date','title'=>'到期日期',));
	$oL->m_additem('loadtype');
	$oL->m_additem('share');
	$oL->m_additem('detail');
//	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=push&paid={$paid}&pushid={pushid}",'width'=>40,));
	
	//显示索引行，多行多列展示的话不需要
	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区************
	$oL->o_header();
	
	//显示单选项
	$oL->o_view_bools();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('pushs_list','0');
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();
	
	//列表区中设置项的数据处理
	$oL->sv_e_additem('fixedorder',array());
	$oL->sv_e_additem('vieworder',array());
	$oL->sv_e_all();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>