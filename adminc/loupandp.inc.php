<?php
!defined('M_COM') && exit('No Permission');
$cuid = 1; 
$cid = empty($cid) ? 0 : max(0,intval($cid));
$pid = empty($pid) ? 0 : max(0,intval($pid));
$reid = empty($reid) ? 0 : max(0,intval($reid));
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit';

$sql_ids = "SELECT loupan FROM {$tblprefix}members_13 WHERE mid='$memberid'"; 
$loupanids = $db->result_one($sql_ids); if($loupanids) $loupanids = substr($loupanids,1); 
if(empty($loupanids)) $loupanids = 0; //echo $loupanids;

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => 4,
	'url' => "&pid=$pid&reid=$reid", //以&开始, $action,$entry,$extend_str,$cuid不用输入
	'select'=> "", //
	'where' => " AND cu.aid IN($loupanids) ", //附加条件,前面需要[ AND ]
	'from' => "", //
); 

if($cid){

	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("","");
		$oA->add_pinfo(array('pid'=>$oA->predata['aid']));
		$oA->fm_items();
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
	
}else{
	
	// *** 所有交互列表，一个交互对象下的列表，回复列表，单个交互编辑 共用脚本
	// 组sql的条件,放在"new $class()"之前
	if($pid){
		$_init['where']	= " AND cu.tocid=0 AND cu.aid='$pid'";
	}elseif($reid){
		$_init['where']	= " AND cu.tocid='$reid'";
	}else{
			
	}
	$oL = new $class($_init); 
	$oL->top_head();
	// "new $class()"之后再判断title等
	if($pid){
		$title = "评论列表 --- &gt;&gt;".$oL->getPLink($pid, array());
	}elseif($reid){
		$title = "回复列表";
	}else{
		$title = "";
	}

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.content'=>'留言内容','a.subject' => '被评楼盘',)));
	$oL->s_additem('checked');
	$oL->s_additem('indays');
	$oL->s_additem('outdays');
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('delete',array('exkey'=>'tocid'));
	$oL->o_additem('delbad',array('exkey'=>'tocid')); //删除(扣积分)
	$oL->o_additem('check');
	$oL->o_additem('uncheck');

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
		
		//显示列表区头部 ***************
		$oL->m_header($title);
		$oL->m_additem('selectid'); 
		empty($pid) && empty($reid) && $oL->m_additem('subject',array('len'=>40,)); // *** pid不为空则显示交互对象
		$oL->m_additem('content',array('len'=>30,'title'=>'留言内容','side'=>'L'));
		$oL->m_additem('mname',array('title'=>'会员','side'=>'L'));
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
		empty($reid) && $oL->m_additem('recounts',array('url'=>"?action=$action&pid=$pid&reid=$reid&cuid=$cuid&reid={cid}",'winsize'=>'640,480')); // *** reid空则显示回复数
		$oL->m_additem('cucreate',array('type'=>'date',));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?action=$action&pid=$pid&reid=$reid&cuid=$cuid&cid={cid}",'width'=>40,));
		
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