<?php
 
$cuid = 36;
$caid = empty($caid)?3:max(1,intval($caid));
$chid = $caid==3 ? 3 : 2;
$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid = empty($aid) ? 0 : max(0,intval($aid));




$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$isreply = empty($isreply) ? 0 : 1;
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "", //表单url，必填，不需要加入mchid
	'select'=>" SELECT cu.*,cu.createdate AS cucreate,cu.chid as cu_chid,a.aid,a.createdate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject as ex_subject,a.mid ",
	'from'=>" FROM {$tblprefix}commu_weituo cu LEFT JOIN {$tblprefix}".atbl(4)." a ON a.aid=cu.pid ",
	'where' => " AND cu.chid='$chid' ", //附加条件,前面需要[ AND ]
);


if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header("");			
		$oA->fm_items('');
		$oA->fm_footer('bsubmit');
        $oA->fm_header("委托记录查看");	
        $oA->fm_wt_info($cid);
        $oA->fm_footer('');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
	
}else{
	$oL = new $class($_init); 
	$oL->top_head();

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'小区名称','cu.mname' => '委托者',),'custom'=>1)); 
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
		$oL->m_header( );
		$oL->m_additem('selectid'); 
		$oL->m_additem('ex_subject',array('len'=>40,'title'=>'小区名称'));        
		$oL->m_additem('mname',array('title'=>'会员'));
		$oL->m_additem('wtlx',array('title'=>'委托类型','width'=>90));
        $oL->m_additem('mj',array('title'=>'面积','mtitle'=>"{mj}平方米"));
        $oL->m_additem('shi',array('title'=>'室','mtitle'=>"{shi}室"));
        $oL->m_additem('ting',array('title'=>'厅','mtitle'=>"{ting}厅"));
        $oL->m_additem('wei',array('title'=>'卫','mtitle'=>"{wei}卫"));
		
		if($chid==2){//出租
			$oL->m_additem('zj',array('title'=>'租金','mtitle'=>"{zj}元/月"));
		}elseif($chid==3){//出售
			$oL->m_additem('zj',array('title'=>'总价','mtitle'=>"{zj}万元"));
		}
		

        $oL->m_addgroup('{mj}/{shi}/{ting}/{wei}/{zj}','基本信息');//请注意分组不能嵌套，每项只能参与一次分组
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'委托时间'));        
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