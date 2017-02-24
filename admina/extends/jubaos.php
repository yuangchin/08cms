<?php
$cuid = 4; 
$caid = empty($caid)?3:max(1,intval($caid));
$chid = $caid==3 ? 3 : 2;
$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid = empty($aid)?0:max(1,intval($aid));

$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$isreply = empty($isreply) ? 0 : 1;
$aid_sql = empty($aid)?'':" AND a.aid='$aid'  ";

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "&aid=$aid", //表单url，必填，不需要加入mchid
	'select'=>'',
	'from'=>'',
	'where' => " $aid_sql ", //附加条件,前面需要[ AND ]
);


if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header("");			
		$oA->fm_items('');		
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
	
}else{
	$oL = new $class($_init); 
	$oL->top_head();

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'房源名称','cu.mname' => '举报者',),'custom'=>1));
    $oL->s_additem('leixing');
	$oL->s_additem('indays');
	$oL->s_additem('outdays');
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('delete');

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
		
		//显示列表区头部 ***************
		$oL->m_header('', $aid, $aid ? " &nbsp; <a href='?entry=extend&extend=$extend='>全部举报&gt;&gt;</a>" : '');
		$oL->m_additem('selectid'); 
		if(empty($aid)) $oL->m_additem('subject',array('len'=>40,'title'=>'被举报房源')); 
 	
		$oL->m_additem('mname',array('title'=>'举报者','width'=>60));
		$oL->m_additem('leixing',array('title'=>'举报类型','width'=>90));
		$oL->m_additem('ip',array('type'=>'other','title'=>'来源IP'));
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'举报时间'));        
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&cid={cid}",'width'=>40,));
	    $oL->m_additem('detail2',array('type'=>'url','title'=>'更正','mtitle'=>'房源','url'=>"?entry=extend&extend=".($caid == 4 ? 'chuzuarchive' : 'usedhousearchive')."&aid={aid}",'width'=>40,));
		
		$oL->m_view_top(); //显示索引行，多行多列展示的话不需要
		$oL->m_view_main(); 
		$oL->m_footer(); //显示列表区尾部
		
		$oL->o_header(); //显示批量操作区************
		$oL->o_view_bools(); //显示单选项
		
		$oL->o_footer('bsubmit');
		$oL->guide_bm('','0');
		
	}else{
		
		$oL->sv_header(); //预处理，未选择的提示
		$oL->sv_o_all(); //批量操作项的数据处理
		$oL->sv_footer(); //结束处理
		
	}
			
}

?>