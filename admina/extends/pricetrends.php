<?php
 
$cuid = 47; //接受外部传chid，但要做好限制
$caid = empty($caid) ? 0 : max(0,intval($caid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$area = empty($area) ? 0 : max(0,intval($area));
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$fill_sites = empty($fill_sites) ? 0 : max(0,intval($fill_sites));
$del_month = empty($del_month) ? 0 : max(0,intval($del_month));
$baseurl = "?entry=extend&extend=pricetrends&caid=$caid"; 

switch($caid){
    case 2:
        $chid = 4;//楼盘
        $avg_field = 'dj';//均价
        $price_unit = '元/M<sup>2</sup>';//价格单位
        $price_title = '均价';
    break;
    case 3:
        $chid = 3;//出售
        $avg_field = 'dj';//均价
        $price_unit = '元/M<sup>2</sup>';//价格单位
        $price_title = '均价';
    break;
    case 4:
        $chid = 2;//出租
        $avg_field = 'zj';//单价
        $price_unit = '元/月';//价格单位
        $price_title = '总价';
    break;
    default:
        $chid = 4;//楼盘
        $avg_field = 'dj';//均价
        $price_unit = '元/M<sup>2</sup>';//价格单位
        $price_title = '均价';
    break;
}

//自动对前十二个月的价格进行处理
price_trend($chid,$avg_field,$cuid);
$tblprefix = cls_env::getBaseIncConfigs('tblprefix');

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "", //表单url，必填，不需要加入mchid
	'select'=>' SELECT cu.cid, cu.month, cu.price, cu.area ',
	'from'=>" FROM {$tblprefix}commu_pricetrend cu ",
	'where' => " AND cu.chid = '$chid' AND area='$area' ", //附加条件,前面需要[ AND ]
	'orderby' => " month DESC "
);

if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("","&caid=$caid");
		$oA->fm_items('','',array('noaddinfo'=>1));
		$oA->fm_reference_price($avg_field,$chid,$oA->predata);//当前月的参考价
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		$oA->sv_all_common();
	}
}elseif($fill_sites){
	aheader();
	price_sites($chid,$avg_field); 
	cls_message::show('补全分站数据完成！',$baseurl);
}elseif($del_month){
	aheader(); 
	$db->query("DELETE FROM {$tblprefix}commu_pricetrend WHERE month='$del_month' ");
	echo "<script>floatwin('close_arcdel',this);</script>";
	cls_message::show('删除该月数据完成！',axaction(6,M_REFERER));	
}else{
	$oL = new $class($_init); 
	$oL->top_head(); 

	//搜索项目 ****************************
	//搜索sql及filter字串处理
	$oL->s_deal_str(); //echo $oL->sqlall;
	//批量操作项目 ********************
	$oL->o_additem('delete',array('title'=>'删除当前地区所选项'));
	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		//$oL->s_view_array();
		$oL->s_footer_area($baseurl);
		
		//显示列表区头部 ***************
		$oL->m_header( );
		$oL->m_additem('selectid'); 
		$oL->m_additem('month',array('type'=>'date','fmt'=>'Y-m','len'=>40,'title'=>'月份','side'=>'L')); 
		$oL->m_additem('area',array('type'=>'trendarea','title'=>'地区','side'=>'L')); 
        $oL->m_additem('price',array('title'=>$price_title,'mtitle'=>"{price}$price_unit",'side'=>'L'));	      
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&caid=$caid&cid={cid}",'side'=>'L',));
		$oL->m_additem('del',array('type'=>'url','title'=>'删除该月数据','mtitle'=>'(含所有地区)','url'=>"?entry=extend$extend_str&caid=$caid&del_month={month}",'side'=>'L'));
		
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