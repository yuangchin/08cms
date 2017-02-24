<?php
 
$cuid = 3; //接受外部传chid，但要做好限制
$caid = 2;
$chid = empty($chid) ? 4 : max(0,intval($chid)); //4;

$cid = empty($cid) ? 0 : max(0,intval($cid));
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$aid = empty($aid) ? 0 : max(1,intval($aid));
$aid_str = empty($aid)?'':" AND cu.aid='$aid' ";
$aid_url = empty($aid)?'':"&aid=$aid&chid=$chid";

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => $chid,
	//'caid' => $caid,
	'url' => $aid_url, //表单url，必填，不需要加入mchid
	'select'=>' ',
	'from'=>'',
	'where' => $aid_str, //附加条件,前面需要[ AND ]
);


if($cid){

	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("","&caid=$caid&chid=$chid");
		$oA->fm_items();
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
	$oL->s_additem('keyword',array('fields' => array('cu.ndxm'=>'意向者','a.subject' => '意向楼盘',)));
	$oL->s_additem('checked');
	$oL->s_additem('lpdyfl',array());
	$oL->s_additem('indays');
	$oL->s_additem('outdays');
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 

	//批量操作项目 ********************
	$oL->o_additem('delete');
	$oL->o_additem('check');
	$oL->o_additem('uncheck');
	$oL->o_additem('issms');
	
	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		//$oL->s_footer();
		$oL->s_footer_ex("?entry=extend&extend=export_excel&chid=$chid&cuid=$cuid&filename=kfhdbm$aid_url");
		
		//显示列表区头部 ***************
		$oL->m_header('', $aid, $aid ? " &nbsp; <a href='?entry=extend&extend=$extend&chid=$chid'>全部意向&gt;&gt;</a>" : '');
		$oL->m_additem('selectid'); 
		$oL->m_additem('subject',array('len'=>40,'title'=>'意向楼盘')); 
	    $oL->m_additem('ndxm',array('title'=>'意向者','side'=>'L'));
		$oL->m_additem('xinbie',array('title'=>'性别','side'=>'L'));	
		$oL->m_additem('sjhm',array('title'=>'联系电话','side'=>'L'));
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
		$oL->m_additem('ip',array('type'=>'other','title'=>'来源IP'));
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'添加时间'));        
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&cid={cid}&chid=$chid",'width'=>40,));
		
		$oL->m_view_top(); //显示索引行，多行多列展示的话不需要
		$oL->m_view_main(); 
		$oL->m_footer(); //显示列表区尾部
		
		$oL->o_header(); //显示批量操作区************
		$oL->o_view_bools(); //显示单选项
		$oL->o_view_rows();
		
		$oL->o_footer('bsubmit');
		$oL->guide_bm('','0');
		
	}else{
		
		$oL->sv_header(); //预处理，未选择的提示
		$oL->sv_o_all(); //批量操作项的数据处理
		$oL->sv_footer(); //结束处理
		
	}
			
}

?>