<?php
 
$cuid = 45; //接受外部传chid，但要做好限制
$caid = 560;
$chid = 110;

$admadd = empty($admadd) ? '' : $admadd;
$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$aid_url = empty($aid)?'':"&aid=$aid";

$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$isreply = empty($isreply) ? 0 : 1;
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "&aid=$aid", //表单url，必填，不需要加入mchid
	'select'=>' SELECT cu.*,cu.createdate AS cucreate,cu.aid as cuaid,cu.mid as cu_mid,cu.mname as cu_mname ,a.aid,a.chid,a.caid,a.createdate,a.initdate,a.customurl,a.nowurl,a.subject ',
	'from'=>" FROM {$tblprefix}commu_kanfang cu LEFT JOIN {$tblprefix}archives15 a ON cu.yxlp = a.aid ",
	'where' => " AND ".(empty($aid) ? "1=1" : "cu.aid = $aid")." ", //附加条件,前面需要[ AND ]
);

if($admadd){

	$_init = array(
		'cuid' => $cuid,//交互模型id
		'ptype' => 'a',
		'pchid' => 0,
		'caid' => $caid,
		'url' => "$aid_url", //表单url，必填，不需要加入mchid
		'select'=>'',
		'from'=>'',
		'where' => "", //附加条件,前面需要[ AND ]
	);
	
	$oA = new cls_cuedit($_init);
	$oA->top_head(array('setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("","?entry=extend$extend_str$aid_url&admadd=$admadd");
		$oA->fm_items('');		
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		$oA->sv_set_fmdata();//设置$this->fmdata中的值
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_insert(array('aid'=>$aid,'istrue'=>0,'checked'=>1));//执行insert, 附加参数
		$oA->sv_upload();//上传处理
		$oA->sv_finish(array('message'=>'添加成功'));
	}

}elseif($cid){
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

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('a.subject'=>'楼盘名称','tel'=>'电话'),'custom'=>0)); //'b.subject' => '楼盘名称',
    //筛选真假信息
    $oL->s_additem('istrue');
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
		$oL->s_footer_ex("?entry=extend&extend=export_excel&chid=110&cuid=$cuid&aid=$aid&filename=kfhdbm");
		
		//显示列表区头部 ***************
		$oL->m_header('',''," &nbsp; <a href='?entry=extend&extend=$extend='>全部意向&gt;&gt;</a>"); 
		$oL->m_additem('selectid'); 
		$oL->m_additem('subject',array('title'=>'楼盘名称')); 
		//$oL->m_additem('yxlp'); 
	
		$oL->m_additem('xingming',array('title'=>'姓名'));

		$oL->m_additem('xingbie',array('title'=>'性别'));
		$oL->m_additem('tel',array('title'=>'电话'));
		//$oL->m_additem('qq',array('title'=>'QQ'));	
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'报名时间'));
        $oL->m_additem('istrue',array('type'=>'bool','title'=>'真实数据'));        
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核'));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&cid={cid}&aid={cuaid}",'width'=>40,));
		
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