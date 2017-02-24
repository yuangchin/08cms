<?php
/**
 * 直播信息列表
 *
 * @author icms <icms@foxmail.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 *
 */ 
$cuid = 101; //接受外部传chid，但要做好限制
$caid = 606;
$chid = 114;
$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid = empty($aid) ? 0 : max(1,intval($aid));
$aid_sql = empty($aid)?'':" AND a.aid='$aid'  ";

$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$isreply = empty($isreply) ? 0 : 1;
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "&aid=$aid", //表单url，必填，不需要加入mchid
	'select'=>'',
	'from'=>"",
	'where' => " $aid_sql ", //附加条件,前面需要[ AND ]
);
//echo $_init['from'];

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
    !isset($istrue) && $istrue = -1;

	//搜索项目 **************************** 'b.subject' => '直播文档',
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'直播文档','cu.content' => '直播内容',)));

	$oL->s_additem('indays');
	$oL->s_additem('outdays');
    
   	//批量操作项目 ********************
	$oL->o_additem('delete');
    $oL->o_additem('check');
    $oL->o_additem('uncheck');
    
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
		
		//显示列表区头部 ***************
		$oL->m_header( );
		$oL->m_additem('selectid'); 
		$oL->m_additem('subject',array('title'=>'直播文档')); 
	
		$oL->m_additem('speeker',array('title'=>'发言人'));

		$oL->m_additem('content',array('title'=>'内容'));
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核'));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&cid={cid}&aid={aid}",'width'=>40,));
		
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