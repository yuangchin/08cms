<?php
 
$cuid = 37; //接受外部传chid，但要做好限制
$caid = empty($caid) ? 0 : max(1,intval($caid));
$chid = 106;
$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid = empty($aid)?0:max(1,intval($aid));
$aid_sql = empty($aid)?'':" AND a.aid='$aid'  ";
$answertype = empty($answertype)?1:max(1,intval($answertype));
$filterstr = empty($filterstr)?'':trim($filterstr);
$page = empty($page)?1:max(1,intval($page));
$answerTypeSql = '';
switch($answertype){
    case 1:
        $answerTypeSql = " AND cu.toaid=0 AND cu.tocid=0 ";
    break;
    case 2:
        $answerTypeSql = "  AND cu.toaid=0 AND cu.tocid>0 ";
    break;
    case 3:
        $answerTypeSql = " AND cu.toaid>0 AND cu.tocid=0";
    break;
}

$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "&aid=$aid&answertype=$answertype&page=$page", //表单url，必填，不需要加入mchid
	'select'=>" SELECT cu.*,cu.createdate AS cucreate,cu.mid as cu_mid,cu.mname as cu_mname ,a.aid,a.chid,a.caid,a.createdate,a.initdate,a.customurl,a.nowurl,a.subject,a.mid as twmid ",
	'from'=>" FROM {$tblprefix}commu_answers cu INNER JOIN {$tblprefix}archives22 a ON a.aid=cu.aid ",
	'where' => " $aid_sql $answerTypeSql ", //附加条件,前面需要[ AND ]
);


if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header("");		
		$oA->fm_items('',array(),array('noaddinfo'=>1));			
		$oA->fm_footer('');		
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
	
}else{
	$oL = new $class($_init); 
	
	$oL->top_head();
    
	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'问题名称','cu.content' => '内容',),'custom'=>1));
	$oL->s_additem('indays');
	$oL->s_additem('outdays');   
    
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('deleteAnswer',array('answertype'=>$answertype));
	$oL->o_additem('checkAnswer',array('answertype'=>$answertype));
	$oL->o_additem('uncheckAnswer',array('answertype'=>$answertype));	
    $oL->o_additem('isanswer',array('answertype'=>$answertype));
    $oL->o_additem('noanswer',array('answertype'=>$answertype));    

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
        $oL->s_footer();	
		
		//显示列表区头部 ***************
        $oL->m_header_ex($answertype,$entry,$extend_str,$filterstr,$aid);
		$oL->m_additem('selectid'); 
		$oL->m_additem('subject',array('len' => 40,'title'=>'问题名称','type'=>'url')); 
        $oL->m_additem('content',array('len' => 40,'title'=>'内容','side'=>'L'));
		$oL->m_additem('isanswer',array('type'=>'bool','title'=>'最佳答案'));
        $oL->m_additem('ask_type',array('title'=>'问答形式','width'=>80));        
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核'));
        $oL->m_additem('ip',array('type'=>'other','title'=>'来源IP'));
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'创建日期'));        
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&cid={cid}",'width'=>40,));
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