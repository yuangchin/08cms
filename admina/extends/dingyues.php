<?php
 
$cuid = 7; //接受外部传chid，但要做好限制
$caid = empty($caid) ? 0 : max(0,intval($caid));
$chid = cls_cubasic::caid2chid($caid);
$mid = empty($mid) ? 0 : max(0,intval($mid));


$select_str = empty($mid) ? " SELECT cu.mid,cu.mname,MAX(cu.senddate) AS senddate,COUNT(cu.cid) AS total,SUM(cu.new) AS newnum,SUM(cu.old) AS oldnum,SUM(cu.rent) AS rentnum " : " SELECT cu.*,cu.createdate AS ucreatedate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject " ; 
$from_str = empty($mid) ? " FROM {$tblprefix}commu_gz  cu " : " FROM {$tblprefix}commu_gz cu INNER JOIN {$tblprefix}".atbl(4)." a ON a.aid=cu.aid " ;
$where_str = empty($mid) ? " AND (new!=0 OR old!=0 OR rent!=0)  GROUP BY cu.mid" : " AND cu.mid='$mid' " ;
$orderby_str = empty($mid) ? " cu.mid ASC " : "" ;

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "", //表单url，必填，不需要加入mchid
	'select'=>$select_str,
	'from'=>$from_str,
	'where' =>$where_str, //附加条件,前面需要[ AND ]
	'orderby' =>$orderby_str,
);


if($mid){
	aheader();
	$modearr = array('new' => '新增动态','old' => '新增二手房','rent' => '新增出租',);
	$query = $db->query(" $select_str $from_str $where_str");
	$content = '';
	while($r = $db->fetch_array($query)){
		cls_ArcMain::Url($r,-1);
		$content .= "\n[$r[subject]]";
		foreach($modearr as $k => $v){
			$url = $k == 'new' ? $r['arcurl'] : ($k == 'old' ? $r['arcurl8'].'&fang=mai' : $r['arcurl8'].'&fang=zhu');
			$r[$k] && $content .= "&nbsp; >><a href=\"$url\" target=\"_blank\">$v</a> ";
		}
		$mname = $r['mname'];
	}
	$content || cls_message::show('指定的会员没有楼盘信息。');
	$na = array('mid' => $mid,'mname' => $mname,'content' => $content);
	tabheader("楼盘邮件预览");
	trbasic('邮件标题','',splang('dingyue_subject',$na),'');
	trbasic('邮件内容','',nl2br(splang('dingyue_content',$na)),'');
	tabfooter();
}else{
	$oL = new cls_culist($_init); 
	$oL->top_head();

	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.mid'=>'会员ID','cu.mname' => '会员名称',)));
	$oL->s_additem('checked');
	$oL->s_additem('indays');
	$oL->s_additem('outdays');
	//搜索sql及filter字串处理
	$oL->s_deal_str(); 
	
	//批量操作项目 ********************
	$oL->o_additem('del_lpdy');
	$oL->o_additem('send_email');

	if(!submitcheck('bsubmit')){
		
		//搜索区域 ******************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
		
		//显示列表区头部 ***************
		$oL->m_header('',''," &nbsp; &nbsp; &gt;&gt;<a href='?entry=splangs&action=splangsedit'>编辑邮件模板</a>" );
		$oL->m_additem('selectmid');
		$oL->m_additem('mid',array('title'=>'会员ID','side'=>'C'));		
        $oL->m_additem('mname',array('title'=>'会员名称','side'=>'C'));	
		$oL->m_additem('total',array('title'=>'楼盘','side'=>'C'));	
        $oL->m_additem('newnum',array('title'=>'新增动态','side'=>'C'));
		$oL->m_additem('oldnum',array('title'=>'新增二手房源','side'=>'C'));
		$oL->m_additem('rentnum',array('title'=>'新增出租房源','side'=>'C'));
		$oL->m_additem('senddate',array('title'=>'最近发送','type'=>'date','side'=>'C'));
       
		$oL->m_additem('detail',array('type'=>'url','title'=>'邮件预览','mtitle'=>'预览','url'=>"?entry=extend$extend_str&cuid=$cuid&caid=$caid&mid={mid}",'width'=>80,));
		
		$oL->m_view_top(); //显示索引行，多行多列展示的话不需要
		$oL->m_view_main(); 
		$oL->m_footer(); //显示列表区尾部
		
		$oL->o_header(); //显示批量操作区************
		$oL->o_view_bools(); //显示单选项
		
		$oL->o_footer('bsubmit');
		$oL->guide_bm('','0');
		
	}else{
		
		$oL->sv_header(); //预处理，未选择的提示
		$oL->sv_o_all_lpdy(); //批量操作项的数据处理
		$oL->sv_footer(); //结束处理
		
	}
			
}

?>