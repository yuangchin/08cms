<?php
 
$cuid = 5; //接受外部传chid，但要做好限制
$caid = empty($caid) ? 0 : max(1,intval($caid));

$cid = empty($cid) ? 0 : max(0,intval($cid));
$mid = $curuser->info['mid'];
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => "",
	'caid' => $caid,
	'url' => "", //表单url，必填，不需要加入mchid
	'select'=>" SELECT cu.* ",
	'from'=>"  FROM {$tblprefix}commu_liuyan cu ",
	'where' => " AND cu.tomid=$mid ", //附加条件,前面需要[ AND ]
);


if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header();		
		$oA->fm_items('',array(),array('noaddinfo'=>1));			
		$oA->fm_footer('bsubmit');
        $oA->guide_bm("<font color='red'>****小提示****：</font>店铺主人只可修改回复内容，留言内容修改无效",0);
	}else{
	    //提交后的处理
		$oA->sv_set_fmdata();//设置$this->fmdata中的值
		$oA->sv_items();//进行余下的所有项目处理，此时未执行数据库操作
		$oA->sv_retime('replydate','reply');
		$oA->sv_update();//执行自动操作及更新以上变更
		$oA->sv_finish(array());//结束时需要的事务，包括操作记录、成功提示等

	}
	
}else{
	$oL = new $class($_init); 
	
	
	$oL->top_head();
    
	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.mname'=>'留言者'),'custom'=>1));
    $oL->s_additem('checked');
	$oL->s_additem('indays');
	$oL->s_additem('outdays');   
    
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('delete');
    $oL->o_additem('check');
    $oL->o_additem('uncheck');


	if(!submitcheck('bsubmit')){
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();        
        $oL->s_footer();
        
		
		//显示列表区头部 ***************
		$oL->m_header();
		$oL->m_additem('selectid'); 
		$oL->m_additem('mname',array('len' => 40,'title'=>'留言者')); 
        $oL->m_additem('checked',array('title'=>'审核','type'=>'bool'));
		$oL->m_additem('createdate',array('type'=>'date','title'=>'留言时间'));    
        $oL->m_additem('replydate',array('type'=>'date','title'=>'回复时间'));    
		$oL->m_additem('detail',array('type'=>'url','title'=>'回复','mtitle'=>'回复','url'=>"?action=$action&cuid=$cuid&cid={cid}",'width'=>40,));
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