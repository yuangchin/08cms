<?php
 
$cuid = 50; //接受外部传chid，但要做好限制
$caid = empty($caid) ? 0 : max(1,intval($caid));
$chid = 113; 
$cid = empty($cid) ? 0 : max(0,intval($cid));
$mid = $curuser->info['mid'];
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => '',
	'caid' => $caid,
	'url' => "", //表单url，必填，不需要加入mchid
	'select'=>" SELECT cu.* ",
	'from'=>"  FROM {$tblprefix}commu_customers cu INNER JOIN {$tblprefix}commu_customer b ON cu.lxdh=b.lxdh",
	'where' => " AND b.mid=$mid GROUP BY cu.lxdh", //附加条件,前面需要[ AND ]
);


if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));
    $oA->items_did[] = 'valid';
	if(!submitcheck('bsubmit')){
		$oA->fm_header();		
		$oA->fm_items('',array(),array('noaddinfo'=>1));			
		$oA->fm_footer('bsubmit');
	}else{
	    //提交后的处理
		$oA->sv_all_common();
	}
	
}else{
	$oL = new $class($_init); 
	
	
	$oL->top_head();
    
	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.xingming'=>'被推荐人姓名'),'custom'=>1));
	$oL->s_additem('indays');
	$oL->s_additem('outdays');   
    
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('delete');

    echo $oL->sqlall;
	if(!submitcheck('bsubmit')){
        backnav('distribution','manage');
        
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();        
        $oL->s_footer();
        
		
		//显示列表区头部 ***************
		$oL->m_header();
		$oL->m_additem('selectid'); 
	//	$oL->m_additem('subject',array('len' => 40,'title'=>'楼盘分销标题','type'=>'url','url'=>"{$cms_abs}mspace/archive.php?mid={mid}&aid={aid}")); 
	
		$oL->m_additem('xingming',array('title'=>'被推荐人'));
        $oL->m_additem('lxdh',array('title'=>'被推荐人电话'));	
		$oL->m_additem('createdate',array('type'=>'date','title'=>'推荐日期'));        
        //$oL->m_additem('tjdqsj',array('type'=>'date','title'=>'到期日期'));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?action=$action&cuid=$cuid&cid={cid}&chid=$chid",'width'=>40,));
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