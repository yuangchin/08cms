<?php

//扩展参数
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
$exfenxiao = $exconfigs['distribution']; // Array ( [num] => 3 [pnum] => 100 [vtime] => 15 [unvnum] => 10 [fxwords] => msg ) 
$exfenxiao['num'] = empty($exfenxiao['num']) ? 3 : max(1,intval($exfenxiao['num']));
$exfenxiao['vtime'] = empty($exfenxiao['vtime']) ? 15 : max(3,intval($exfenxiao['vtime']));

$cuid = 50; //接受外部传chid，但要做好限制
$cid = empty($cid) ? 0 : max(0,intval($cid));

$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'e',
	'pchid' => 0,
	'url' => "", //表单url，必填，不需要加入mchid
	'select'=>"",
	'from'=>"",
	'where' => "", //附加条件,前面需要[ AND ]
	'fnoedit' => array('xingming','dianhua','jine'),
);


if($cid){
	
	$_init['cid'] = $cid;
	$oA = new $class($_init);
	$oA->top_head(array('chkData'=>1,'setCols'=>1));

	if(!submitcheck('bsubmit')){
		$oA->fm_header("");	
		$oA->fm_items(array(),array(),array('noaddinfo'=>1));
		trbasic('提取日期','',date('Y-m-d H:i:s',$oA->predata['createdate']),'');
		$oA->fm_footer('bsubmit'); //bsubmit
		
		$dmy = get_yjdetail(get_fxlist($oA->predata['fxids'],''));
		$dsub = get_yjdetail(get_fxlist($oA->predata['fxidp'],''),'');

		if($dmy[0]){
			$cy_arr = array();
			tabheader("[{$oA->predata['mname']}]分销推荐买房获得的佣金明细",'','',6);
			$cy_arr[] = '被推荐姓名';
			$cy_arr[] = '联系电话';
			$cy_arr[] = '楼盘名称';
			$cy_arr[] = '佣金(元)';
			$cy_arr[] = '经纪人';
			$cy_arr[] = '备注';
			trcategory($cy_arr);
			echo $dmy[1];
			tabfooter();
		}
		if($dsub[0]){
			$cy_arr = array();
			tabheader("从下级经济人获得的佣金明细",'','',6);
			$cy_arr[] = '被推荐姓名';
			$cy_arr[] = '联系电话';
			$cy_arr[] = '楼盘名称';
			$cy_arr[] = '佣金(元)';
			$cy_arr[] = '下级经纪人';
			$cy_arr[] = '备注';
			trcategory($cy_arr);
			echo $dsub[1];
			tabfooter();
		}
	}else{
		//提交后的处理
		$oA->sv_set_fmdata();//设置$this->fmdata中的值
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_update();//执行自动操作及更新以上变更
		$oA->sv_upload();//上传处理
		$oA->sv_finish();//结束时需要的事务，包括操作记录、成功提示等
	}
	
}else{
	$oL = new $class($_init); 
	
	$oL->top_head();
    
	//搜索项目 **************************** 'a.subject'=>'分销活动',
	$oL->s_additem('keyword',array('fields' => array('cu.xingming' => '提款人','cu.dianhua' => '联系电话','cu.mname' => '会员帐号'),'custom'=>1));
	$oL->s_additem('status',array('xtype' =>'field'));
	$oL->s_additem('indays');
	$oL->s_additem('outdays');   
    
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('delete');
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
		$oL->m_additem('xingming',array());
        $oL->m_additem('dianhua',array());
		$oL->m_additem('jine',array('title'=>'金额(元)')); 
		$oL->m_additem('cucreate',array('type'=>'date','title'=>'提取时间'));        
		$oL->m_additem('status',array('type'=>'field','empty'=>'<span style="color:#999">未支付</span>')); 
		$oL->m_additem('mname',array('title'=>'会员帐号','width'=>80));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&cid={cid}",));
		$oL->m_view_top(); //显示索引行，多行多列展示的话不需要
		$oL->m_view_main(); 
		$oL->m_footer(); //显示列表区尾部
		
		$oL->o_header(); //显示批量操作区************
		$oL->o_view_bools(); //显示单选项
		
		$oL->o_footer(''); //
		$oL->guide_bm('','0');
		
	}else{
		
		$oL->sv_header(); //预处理，未选择的提示
		$oL->sv_o_all(); //批量操作项的数据处理
		$oL->sv_footer(); //结束处理
		
	}
			
}

?>