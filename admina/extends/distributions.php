<?PHP
$chid = 113;//接受外部传chid，但要做好限制

#-----------------

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid
'pre' => 'a.',//默认的主表前缀
'from' => "",//sql中的FROM部分，可以通过这里JOIN其它表
'select' => "",//sql中的SELECT部分
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'fields' => array(),//允许传入改装过的字段缓存
));

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID','a.lpmc'=>'楼盘名称'),));//fields留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('caid',array('hidden' => 1,));
$oL->s_additem('checked');

foreach($oL->A['coids'] as $k){
	$oL->s_additem("ccid$k",array());
}

$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_addpushs();//推送项目

$oL->o_additem('delete');
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('static');
$oL->o_additem('nstatic');

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	

	//显示列表区头部 ***************
	$oL->m_header();
	
	//设置列表项目
	
	$oL->m_additem('selectid');
    $oL->m_additem('aid',array('type'=>'other','title'=>'ID'));
    $oL->m_additem('subject',array('len' => 40,));
    $oL->m_additem('lpmc',array('title'=>'所属楼盘'));
    $oL->m_additem('kprq',array('title'=>'开盘日期'));
    $oL->m_additem('enddate',array('type'=>'date','title'=>'分销活动结束时间'));
	$oL->m_additem('yds',array('title'=>'已预订','mtitle'=>'{yds}套'));
	$oL->m_additem('tjs',array('title'=>'已推荐','mtitle'=>'{tjs}次'));	
	$oL->m_additem('yj',array('title'=>'佣金'));	
	$oL->m_additem('tel',array('title'=>'咨询电话')); 
	$oL->m_additem('clicks',array('type' => 'input','title'=>'点击数','width'=>50,'view'=>'H','w' => 3,));	
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	$oL->m_additem('createdate',array('type'=>'date','view'=>'H'));

	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>40,));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=distribution&aid={aid}",'width'=>40,));	
	
	//显示索引行，多行多列展示的话不需要
	$oL->m_view_top();
	
	//全部列表区处理
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
	
	//列表区中设置项的数据处理
	$oL->sv_e_additem('clicks',array());
	$oL->sv_e_all();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>