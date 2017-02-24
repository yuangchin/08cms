<?PHP
/*
** 推送位载入文档的列表管理，这是通用的文档载入推荐位的正式使用脚本
** 
** 
*/ 
/* 参数初始化代码 */
$paid = cls_PushArea::InitID(@$paid);//初始化推荐位ID
if(!($pusharea = cls_PushArea::Config($paid))) exit('请指定正确的推送位');
if($pusharea['sourcetype'] != 'archives') exit('推送位来源应为文档类型');

$chid = $pusharea['sourceid'];//文档模型chid
//判断-来自以下栏目
$idarr = array();
$pusharea['smallids'] && $idarr = array_filter(explode(',',$pusharea['smallids']));
//判断-启用模型表
$from = (isset($pusharea['sourceadv']) && !empty($pusharea['sourceadv']))?"{$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON a.aid = c.aid":"";
$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及paid
'isab' => 3,//*** 操作模式设置：0为普通管理列表，1为辑内管理列表，2为加载内容列表，3为推送位加载管理
'paid' => $paid,//*** 指定推荐位id
'from' => $from,
);


/******************/

$oL = new cls_archives($_init);

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//添加搜索项目：s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('caid',array('ids'=>$idarr));
foreach($oL->A['coids'] as $k){
	$oL->s_additem("ccid$k",array());
}
$oL->s_additem('orderby');

//搜索sqlall、acount、filter字串处理
$oL->s_deal_str();

if(!submitcheck('bsubmit')){
	
	//搜索显示区域 ****************************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	

	//内容列表区 **************************
	$oL->m_header();
	
	//设置列表项目
	$oL->m_additem('selectid');
	$urlfrom = @$pusharea['sourcefields']['url']['from'];
	$ismcurl = strstr($urlfrom,'{marcurl}') ? 1 : 0; //定义了url为marcurl,则用marcurl作为连接; 在此规则之外,就扩展脚本吧
	$oL->m_additem('subject',array('len' => 40,'mc'=>$ismcurl));
	$oL->m_additem('caid');
	$oL->m_additem('clicks',array('title'=>'点击',));
	foreach($oL->A['coids'] as $k){
		$oL->m_additem("ccid$k",array('view'=>'H',));
	}
	
	$oL->m_additem('createdate',array('type'=>'date',));
	$oL->m_additem('mname',array('title'=>'会员',));
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>40,));
	
	//显示索引行，多行多列展示的话不需要
	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	$oL->o_end_form('bsubmit','加载');
	$oL->guide_bm('','0');
	
}else{
	//专门针对加载的操作
	$oL->sv_o_pushload();
}
?>