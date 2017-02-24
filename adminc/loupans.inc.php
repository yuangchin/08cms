<?php

$chid = $lpchid = empty($lpchid) ? 4 : $lpchid;
$chid = in_array($chid,array(4,115,116)) ? $chid : cls_messag::show('参数错误！');
$mchid = $curuser->info['mchid'];
$lpfields = array(4=>'loupan',115=>'xiezilou',116=>'shaopu');
$lpcoids = array(4=>array(1,6,12,18,41),115=>array(1,46,47),116=>array(1,48,49));
$lpfield = $lpfields[$chid];
//*
$sql_ids = "SELECT $lpfield FROM {$tblprefix}members_$mchid WHERE mid='$memberid'"; 
$loupanids = $db->result_one($sql_ids); if($loupanids) $loupanids = substr($loupanids,1); 
if(empty($loupanids)) $loupanids = 0;

#-----------------

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?action=$action&lpchid=$chid",//表单url，必填，不需要加入chid及pid
'pre' => 'a.',//默认的主表前缀
'where' => "a.aid IN($loupanids) ",//sql中的初始化where，限定为自已的文档
'from' => $tblprefix.atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON c.aid=a.aid ",//sql中的FROM部分
'select' => "",//sql中的SELECT部分
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
'coids' => $lpcoids[$lpchid],//手动设置允许类系 2,3,14,
//'fields' => array(),//允许传入改装过的字段缓存
));
//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('indays');
$oL->s_additem('outdays');



cls_cache::Load('mconfigs');
$total_refreshes = $mconfigs['salesrefreshes'];
$refresh = $db->result_one("SELECT refreshes FROM {$tblprefix}members WHERE mid = '$memberid'");
$refresh = empty($refresh)?'0':$refresh;
$style = " style='font-weight:bold;color:#F00'";
$msgstr = "今日刷新:<span$style>$refresh/$total_refreshes</span>条";
$re_refresh = $total_refreshes - $refresh; $re_refresh = $re_refresh<0 ? 0 : $re_refresh;




//搜索sql及filter字串处理
$oL->s_deal_str();

$oL->o_additem('readd',array('limit'=>$re_refresh,'time'=>0,'fieldname'=>'refreshes'));
$oL->o_additem('static',array('title'=>'生成静态')); //静态,开启才生效

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	backnav($chid==4 ? 'loupanbar' : 'loupanbus','loupan');
	$oL->guide_bm($msgstr,'fix');
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();

	//显示列表区头部 ***************

	$oL->m_header();
	
	//设置列表项目，如果列表项中包含可设置项，需要在数据储存时，加入设置项的处理
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 40,));

	foreach($oL->A['coids'] as $k){
		$view = in_array($k,array(18,41,46,47,48)) ? '' : 'H';
		$oL->m_additem("ccid$k",array('view'=>$view,));
	}
	$oL->m_additem('valid');

	$oL->m_additem('azxs',array('type'=>'ucount','title'=>'资讯','url'=>"?action=zixuns_pid&pid={aid}",'func'=>'gethjnum','arid'=>($chid==4 ? '1' : 35),'chid'=>1,'width'=>28,));
	$oL->m_additem('atps',array('type'=>'ucount','title'=>'相册','url'=>"?action=xiangces_pid&pid={aid}",'func'=>'gethjnum','arid'=>($chid==4 ? '3' : 36),'chid'=>7,'width'=>28,));
	
    if($chid==4){
        $oL->m_additem('ahss',array('type'=>'ucount','title'=>'户型','url'=>"?action=huxings_pid&pid={aid}",'func'=>'gethjnum','arid'=>'3','chid'=>11,'width'=>28,));
	   $oL->m_additem('ahds',array('type'=>'url','title'=>'活动','mtitle'=>'[{ahds}]','url'=>"?action=loupanhd&pid={aid}",'width'=>30,));
    }
	
    $oL->m_additem('ayss',array('type'=>'url','title'=>'意向','mtitle'=>'[{ayss}]','url'=>"?action=louyx&aid={aid}&chid=$chid",'width'=>30,));
	//$oL->m_additem('adps',array('type'=>'url','title'=>'点评','mtitle'=>'点评','url'=>"?action=loupandp&pid={aid}",'width'=>30,));
	$oL->m_additem('liuyan',array('type'=>'ucount','title'=>'点评','url'=>"?action=loupandp&pid={aid}",'func'=>'getjhnum','cuid'=>'1','chid'=>4,'width'=>28,));	
	$oL->m_additem('pinfen',array('type'=>'url','title'=>'评分','mtitle'=>'查看','url'=>"?action=loupan_pinfen&aid={aid}&chid=$chid",'width'=>30,));
	$oL->m_additem('weixin',array('type'=>'url','title'=>'微信','mtitle'=>'配置', 'url'=>"?action=weixin&tab=property&aid={aid}",'cuid'=>'44','width'=>28,'umode'=>1));
	#$oL->m_additem('weixin',array('type'=>'weixin','mcache'=>'property'));
	
	$oL->m_additem('refreshdate',array('type'=>'date',));
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?action=archiveinfo&aid={aid}",'width'=>30,'view'=>'H'));
	$oL->m_additem('dj',array('type'=>'url','mtitle'=>'价格','url'=>"?action=jiagearchive&aid={aid}&isnew=1",'width'=>60));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?action=loupane&aid={aid}",'width'=>60,));
	$oL->m_addgroup('{detail}&nbsp;{dj}','编辑');

	
	//显示索引行，多行多列展示的话不需要
	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区************
	$oL->o_header();
	
	//显示单选项
	//$oL->o_view_bools('单行标题',array('bool1','bool2',));
	$oL->o_view_bools();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('','0');
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>

