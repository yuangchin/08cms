<?php
 
$cuid = 48; //接受外部传chid，但要做好限制
$caid = 2;
$chid = empty($chid) ? 4 : max(0,intval($chid)); //4;

$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid = empty($aid) ? 0 : max(0,intval($aid));
$aid_url = empty($aid)?'':"&aid=$aid&chid=$chid";


$aid_sql = empty($aid)?'':" AND cu.aid= '$aid'";
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$isreply = empty($isreply) ? 0 : 1;

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => $chid,
	//'caid' => $caid,
	'url' => "$aid_url", //表单url，必填，不需要加入mchid
	'select'=>'',
	'from'=>'',
	'where' => " $aid_sql AND cu.tocid=0 AND cu.mname !='' ", //附加条件,前面需要[ AND ]
);


if($cid && empty($isreply)){

	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("");		
		$oA->fm_items('comment');		
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
	
}elseif($cid && $isreply){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("","&cid=$cid&isreply=$isreply&chid=$chid");
		$oA->fm_replay($oA->predata);		
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_replay();
	}
}else{
	$oL = new $class($_init); 
	$oL->top_head();
    

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.mname'=>'留言者','a.subject' => '被评楼盘',)));
	$oL->s_additem('checked');
	$oL->s_additem('indays');
	$oL->s_additem('outdays');
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('delete',array('exkey'=>'tocid'));
	$oL->o_additem('check');
	$oL->o_additem('uncheck');
	//echo $oL->sqlall;

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
		
		//显示列表区头部 ***************
		$oL->m_header('', $aid, $aid ? " &nbsp; <a onclick=\"return floatwin('open_fnodes',this)\" href='?entry=extend&extend=$extend&chid=$chid'>全部评论&gt;&gt;</a>" : '');
		$oL->m_additem('selectid'); 
		$oL->m_additem('subject',array('len'=>40,'title'=>'被评楼盘')); 
        $oL->m_additem('mname',array('title'=>'留言者','width'=>80));	
        $oL->m_additem('recounts',array('url'=>"?entry=extend&extend=lply_replays&tocid={cid}",'title'=>'数量','winsize'=>'930,480','width'=>100)); // 回复数
        $oL->m_additem('replay',array('type'=>'url','title'=>'回复','mtitle'=>'回复','url'=>"?entry=extend&extend=lpliuyans&aid=$aid&cid={cid}&isreply=1&chid=$chid"));
		$oL->m_addgroup('{recounts}/{replay}','回复');//请注意分组不能嵌套，每项只能参与一次分组        
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
		$oL->m_additem('ip',array('type'=>'other','title'=>'来源IP'));
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'添加时间'));        
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&cid={cid}&chid=$chid",'width'=>40,));
		
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