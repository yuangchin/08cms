<?php

// *** 商家留言 共用脚本 
$cuid = 5; //接受外部传chid，但要做好限制
$mchid = empty($mchid) ? 2 : max(2,intval($mchid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$pid = empty($pid) ? 0 : max(0,intval($pid));
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit'; 
$corpid = $mchid==2 ? 'xingming' : 'cmane';
//$corpnm = $mchid==2 ? '姓名' : '公司名';

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'm',
	'pchid' => $mchid,
	'url' => "?entry=$entry$extend_str&mchid=$mchid&cuid=$cuid", //表单url，必填，不需要加入mchid
	'select'=> ",$corpid ", //
	'where' => '', //附加条件,前面需要[ AND ]
	'from' => " INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid INNER JOIN {$tblprefix}members_$mchid c ON c.mid=m.mid ", //
);


if($cid){ 

	$_init['cid'] = $cid;
	$oA = new $class($_init);  
	$oA->top_head();
	if(empty($oA->predata)) $oA->message('不存在数据！'); // print_r($oA->predata);
	
	$oA->additems();
	//二手车商家,不要车型
	$pinfo = $oA->getPInfo('m',$oA->predata['tomid']); 
	
	if(!submitcheck('bsubmit')){
		$oA->fm_header("","?entry=extend$extend_str&mchid=$mchid&cuid=$cuid&pid=$pid");
		//if($pinfo['mchid']==2){
			//$oA->fm_zychexing();
		//}
		$oA->items_did[] = 'chexing';
		$oA->fm_items();
		$oA->fm_footer('bsubmit');
		$oA->guide_bm('','0');
	}else{
		//提交后的处理
		
		$oA->sv_set_fmdata();//设置$this->fmdata中的值
		$oA->sv_items();//进行余下的所有项目处理，此时未执行数据库操作
		$oA->sv_retime('replydate','reply');
		$oA->sv_update();//执行自动操作及更新以上变更
		$oA->sv_upload();//上传处理
		$oA->sv_finish(array());//结束时需要的事务，包括操作记录、成功提示等

	}
	
}else{
	
	// *** 所有交互列表，一个交互对象下的列表，回复列表，单个交互编辑 共用脚本
	// 组sql的条件,放在"new $class()"之前
	if($pid){
		$_init['where']	= " AND cu.tomid='$pid'";
	}else{
			
	}
	$oL = new $class($_init); 
	$oL->top_head();
	if(!in_array($mchid,array(2,3))) $oL->message('错误！');
	// "new $class()"之后再判断title等
	if($pid){
		$pinfo = $oL->getPInfo($oL->ptype,$pid); 
		$link = htmlspecialchars($pinfo['mname']);
		$link = "<a href=\"$pinfo[mspacehome]\" target='_blank'>$link&gt;&gt;</a>";
		$title = "评论列表 --- $link ";
	}else{
		$title = "";
	}

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.content'=>'留言内容',$corpid => '被评会员',)));
	//$oL->s_additem('diyu',array('type'=>'field'));
	$oL->s_additem('checked');
	$oL->s_additem('indays');
	$oL->s_additem('outdays');
	//搜索sql及filter字串处理
	$oL->s_deal_str(); //echo $oL->sqlall;
	
	//批量操作项目 ********************
	$oL->o_additem('delete',array());
	$oL->o_additem('delbad',array()); //删除(扣积分)
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
		empty($pid) && $oL->m_additem('subject',array('len'=>40,'field'=>$corpid)); // *** pid不为空则显示交互对象
		$oL->m_additem('content',array('len'=>30,'title'=>'留言内容','side'=>'L'));
		$oL->m_additem('cu_mname',array('title'=>'会员','side'=>'L'));
		//$oL->m_additem('diyu',array('type'=>'field','title'=>'地区','side'=>''));
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
		$oL->m_additem('cucreate',array('type'=>'date',)); 
		$oL->m_additem('replydate',array('type'=>'date','title'=>'回复时间',));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend$extend_str&cuid=$cuid&cid={cid}",'width'=>40,));
		
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