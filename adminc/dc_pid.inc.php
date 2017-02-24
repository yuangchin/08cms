<?PHP
/* 页面参数初始化 *************************************/
$chid = 102; $arid = 31;
//初始化合辑id，只按受pid，如其它id样式传进来，要转为pid
$pid = empty($pid) ? 0 : max(0,intval($pid));
$isab = empty($isab) ? 1 : max(1,intval($isab));

$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?action=$action&isab=$isab",//表单url，必填，不需要加入chid及pid
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
'isab' => $isab,//*** 是否合辑内管理：0为普通管理列表，1为辑内管理列表，2为加载内容列表
'pid' => $pid,//合辑id
'arid' => $arid,//*** 指定合辑项目id
'pids_allow' => 'self',//*** pid允许的范围：在会员中心必填项，分析当前会员是否具有该合辑的管理权限
//'pids_allow' => '55,56,57',//调试用
);

#-----------------

$oL = new cls_archives($_init);
//头部文件及缓存加载
$oL->top_head();

if($isab==1){

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
	$oL->s_additem('caid',array('hidden' => 1,));
	$oL->s_additem('orderby');
	$oL->s_additem('valid');
	$oL->s_additem('indays');
	
	//搜索sql及filter字串处理
	$oL->s_deal_str();
	
	//批量操作项目 ********************

	$oL->o_additem('readd');//刷新	//$oL->o_additem('valid',array('days' => 30));//上架，days设置上架的天数，0则为无限期
	$oL->o_additem('inclear');//退出合辑
	
	$channels = cls_cache::Read('channels');
	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
		
		//显示列表区头部 ***************
		$_title = "[{$oL->album['subject']}] 内的 ".$oL->channel['cname'];		
		$_link2 = "<a style=\"color:#C00\" href=\"?action=designCase_a&arid=$arid&pid31=$pid\" onclick=\"return floatwin('open_arcexit',this)\">添加{$channels[$chid]['cname']}</a>";
		$oL->m_header("$_title - $_link2");
		
		$oL->m_additem('selectid');
		$oL->m_additem('subject',array('len' => 40,'mc'=>'1'));
		$oL->m_additem('clicks',array('title'=>'点击',));	
		$oL->m_additem('valid');
		$oL->m_additem('createdate',array('type'=>'date',));
		$oL->m_additem('enddate',array('type'=>'date',));
		$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?action=archiveinfo&aid={aid}",'width'=>40,));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?action=designCase_a&aid={aid}",'width'=>40,));
		
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
		$oL->guide_bm('','0');
		
	}else{
		//预处理，未选择的提示
		$oL->sv_header();
		
		//批量操作项的数据处理
		$oL->sv_o_all();
		
		//结束处理
		$oL->sv_footer();
	}
}else{
	//搜索项目 ****************************
	//s_additem($key,$cfg)
	$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
	$oL->s_additem('caid',array());
	$oL->s_additem('orderby');
	$oL->s_additem('valid');
	$oL->s_additem('indays');

	
	//搜索sql及filter字串处理
	$oL->s_deal_str();
	
	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
	
		//显示列表区头部 ***************
		$oL->m_header();
	
		$oL->m_additem('selectid');
		$oL->m_additem('subject',array('len' => 40,));
		$oL->m_additem('clicks',array('title'=>'点击',));
		$oL->m_additem('valid');
		$oL->m_additem('createdate',array('type'=>'date',));
		$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?action=archiveinfo&aid={aid}",'width'=>40,));
			
		//显示索引行，多行多列展示的话不需要
		$oL->m_view_top();
		
		//全部列表区处理，如果需要定制，尽量使用类中的细分方法
		$oL->m_view_main();
		
		//显示列表区尾部
		$oL->m_footer();
		
		$oL->o_end_form('bsubmit','加载');
		
		$oL->guide_bm('','0');
		
	}else{
		//专门针对加载的操作
		$oL->sv_o_load();
	}	
}
?>