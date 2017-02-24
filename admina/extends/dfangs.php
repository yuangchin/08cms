<?php

$cuid = 8; //接受外部传chid，但要做好限制
$caid = 5;
$chid = 5;

$admadd = empty($admadd) ? '' : $admadd;
$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid_url = empty($aid)?'':"&aid=$aid";

$aid_sql = empty($aid)?'':" AND cu.aid= '$aid'";
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$isreply = empty($isreply) ? 0 : 1;

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "$aid_url", //表单url，必填，不需要加入mchid
	'select'=>' ,b.subject as loupan ',
	'from'=>" LEFT JOIN {$tblprefix}archives15 b ON b.aid=a.pid3 ",
	'where' => " $aid_sql ", //附加条件,前面需要[ AND ]
);

if($admadd){ 
	
	$_init = array(
		'cuid' => $cuid,//交互模型id
		'ptype' => 'a',
		'pchid' => $chid,
		'caid' => $caid,
		'url' => "$aid_url", //表单url，必填，不需要加入mchid
		'select'=>'',
		'from'=>'',
		'where' => "", //附加条件,前面需要[ AND ]
	);
	
	$oA = new cls_cuedit($_init);
	$oA->top_head(array('setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("新房团购  -  添加交互","?entry=extend$extend_str$aid_url&admadd=$admadd");		
		$oA->fm_dghx($aid);//订购户型
		$oA->fm_items('',array('dghx'));		
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		$oA->sv_set_fmdata();//设置$this->fmdata中的值
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_insert(array('aid'=>$aid,'ip'=>$onlineip,'istrue'=>0));//执行insert, 附加参数
		$oA->sv_upload();//上传处理
		$oA->sv_finish(array('message'=>'添加成功'));
	}

}elseif($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header("");		
		$oA->fm_dghx();//订购户型
		$oA->fm_items('',array('dghx'));		
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
	
}else if($aid){
	$oL = new $class($_init); 
	$oL->top_head();
    !isset($istrue) && $istrue = -1;

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'团购活动','b.subject' => '楼盘名称',),'custom'=>1));
    //筛选真假信息
    $oL->s_additem('istrue');
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
		//echo $oL->sqlall;
	//	$oL->s_footer();
        $oL->s_footer_ex("?entry=extend&extend=export_excel&chid=$chid&cuid=$cuid&filename=dfangs");
		
		//显示列表区头部 ***************
		$oL->m_header('', $aid, $aid ? " &nbsp; <a href='?entry=extend&extend=$extend='>全部团购&gt;&gt;</a>" : '');
		$oL->m_additem('selectid');	
		$oL->m_additem('mname',array('title'=>'会员'));
		$oL->m_additem('lxren',array('title'=>'报名者'));
		$oL->m_additem('lxdh',array('title'=>'联系电话'));
		$oL->m_additem('ip',array('type'=>'other','title'=>'来源IP'));
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'添加时间'));        
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
}else{
	$oL = new $class($_init); 
	$oL->top_head();   
    !isset($istrue) && $istrue = -1;

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'团购活动','b.subject' => '楼盘名称',),'custom'=>1));
    //筛选真假信息
    $oL->s_additem('istrue');
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
		$oL->s_footer_ex("?entry=extend&extend=export_excel&chid=$chid&cuid=$cuid&filename=dfangs");
		
		//显示列表区头部 ***************
		$oL->m_header( );
		$oL->m_additem('selectid'); 
		$oL->m_additem('subject',array('len'=>40,'title'=>'所属团购活动')); 
        $oL->m_additem('loupan',array('title'=>'楼盘'));	
		$oL->m_additem('mname',array('title'=>'会员'));
		$oL->m_additem('lxren',array('title'=>'报名者'));
		$oL->m_additem('xinbie',array('title'=>'性别'));
		$oL->m_additem('lxdh',array('title'=>'联系电话'));
		$oL->m_additem('ip',array('type'=>'other','title'=>'来源IP'));
        $oL->m_additem('istrue',array('type'=>'bool','title'=>'真实数据'));
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'添加时间'));        
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