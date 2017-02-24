<?php

//扩展参数
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
$exfenxiao = $exconfigs['distribution']; // Array ( [num] => 3 [pnum] => 100 [vtime] => 15 [unvnum] => 10 [fxwords] => msg ) 
$exfenxiao['num'] = empty($exfenxiao['num']) ? 3 : max(1,intval($exfenxiao['num']));
$exfenxiao['vtime'] = empty($exfenxiao['vtime']) ? 15 : max(3,intval($exfenxiao['vtime']));

$cuid = 49; $mid = $curuser->info['mid']; //接受外部传chid，但要做好限制
$chid = 113;
$cid = empty($cid) ? 0 : max(0,intval($cid));
#$aid = empty($aid)?0:max(1,intval($aid));
$aid_url = empty($aid)?'':"&aid=$aid";
$aid_sql = empty($aid)?'':" AND a.aid LIKE '%,$aid,%'  ";

$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'e',
	'pchid' => 0,
	'url' => "$aid_url", //表单url，必填，不需要加入mchid
	'select'=>"",
	'from'=>"",
	'where' => " AND cu.mid='$mid' ",
	'fnoedit' => array('yxprice'),
);


if($cid){
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header("");	
		$oA->fm_items('xingming,dianhua',array(),array('noaddinfo'=>1));
		$oA->fma_fxlpnames($exfenxiao);	
		$oA->fma_fxyongjin('yjbase');
		$oA->fma_fxyongjin('yjextra'); 
		$oA->fm_items();
		//*
		if($oA->predata['status']=='3'){
			echo "</table>\n".(defined('M_MCENTER') ? '' : '<br />');
			echo '<div align="center"><input type="button" name="bsubmit" value="(成交结算)状态不能提交更新" disabled style="background:#999;"></div> ';
			echo "</form>\n";
		}else{
			$oA->fm_footer('bsubmit');		
		}//*/
		#$oA->fm_footer('bsubmit');	
	}else{
		//提交后的处理
		$oA->sv_set_fmdata();//设置$this->fmdata中的值
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_excom('okaid','okaid',1); 
		if(!empty($oA->fmdata['okayj'])){
			$oA->sv_excom('okayj',$oA->fmdata['okayj']); 
		}
		if($oA->fmdata['status']=='3'){ //确认时间
			$oA->sv_excom('oktime',TIMESTAMP); 
		}
		$oA->sv_update();//执行自动操作及更新以上变更
		$oA->sv_upload();//上传处理
		$oA->sv_finish();//结束时需要的事务，包括操作记录、成功提示等
	}
	
}else{
	$oL = new $class($_init); 
	
	$oL->top_head();
    
	//搜索项目 **************************** 'a.subject'=>'分销活动',
	$oL->s_additem('keyword',array('fields' => array('cu.xingming' => '被推荐人','cu.dianhua' => '联系电话'),'custom'=>1));
	$oL->s_additem('status',array('xtype' =>'field'));
	$oL->s_additem('indays');
	$oL->s_additem('outdays');   
    
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	//$oL->o_additem('delete');
	//$oL->o_additem('check');
	//$oL->o_additem('uncheck');	

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
        //$oL->s_footer_ex("?entry=extend&extend=export_excel&chid=$chid&cuid=$cuid&filename=jztgbm");	
		$oL->s_footer();	
		
		//显示列表区头部 ***************
		$oL->m_header();
		//$oL->m_additem('selectid'); 
		$oL->m_additem('xingming',array('title'=>'被推荐人姓名'));
        $oL->m_additem('dianhua',array());
		$oL->m_additem('cucreate',array('type'=>'date','view'=>'H'));        
		$oL->m_additem('fxend',array('type'=>'udate','title'=>'到期时间','showEnd'=>1,'dbkey'=>'cucreate','offset'=>$exfenxiao['vtime']*86400));   
		$oL->m_additem('status',array('type'=>'field','title'=>'推荐状态','empty'=>'<span style="color:#999">预约看房</span>')); 
		$oL->m_additem('fxlpnames',array('view'=>'')); 
		$oL->m_additem('fxyongjin',array()); //trbasic('上级提取','','是/否','');  
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?action=$action&cuid=$cuid&cid={cid}",));
		$oL->m_view_top(); //显示索引行，多行多列展示的话不需要
		$oL->m_view_main(); 
		$oL->m_footer(); //显示列表区尾部
		
		$oL->o_header(); //显示批量操作区************
		$oL->o_view_bools(); //显示单选项
		
		$oL->o_footer(''); //bsubmit
		$oL->guide_bm('','0');
		
	}else{
		
		$oL->sv_header(); //预处理，未选择的提示
		$oL->sv_o_all(); //批量操作项的数据处理
		$oL->sv_footer(); //结束处理
		
	}
			
}

?>
