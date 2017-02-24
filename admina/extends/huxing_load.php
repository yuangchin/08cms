<?PHP
 
/* 参数初始化代码 */
$chid = 11;//脚本固定针对某个模型
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

$pid = empty($pid) ? 0 : max(0,intval($pid));//初始化合辑id，有可能使用其它id样式传进来，如$hejiid等，要转为使用pid

//$arid = 3;//指定合辑项目id
$arid = empty($arid) ? 0 : max(0,intval($arid));//接受外部传chid，但要做好限制
//echo "\$arid=".$arid;

$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str&arid=$arid",//表单url，必填，不需要加入chid及pid

'cols' => 3,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'coids' => array(1),//手动设置允许类系，在会员中心特别需要指定
//'fields' => array(),//允许传入改装过的字段缓存

'where'=>"a.pid3 in (SELECT pid3 FROM {$tblprefix}".atbl(5)." WHERE aid = '$pid') AND a.aid NOT IN(SELECT inid FROM {$tblprefix}aalbums WHERE arid='2' AND pid = '$pid')",
//'select' => "a.*,b.subject as lpname,b.ccid1 ",
// 'from' => " {$tblprefix}".atbl(11)." a INNER JOIN  {$tblprefix}".atbl(4)." b ON a.pid3=b.aid ",

'isab' => 2,//*** 是否合辑内管理：0为普通管理列表，1为辑内管理列表，2为加载内容列表
'pid' => $pid,//合辑id
'arid' => $arid,//*** 指定合辑项目id
);

/******************/

$oL = new cls_archives($_init);

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//添加搜索项目：s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('caid',array());
//$oL->s_additem("ccid$k",array());
$oL->s_additem('orderby');

//搜索sql及filter字串处理
$oL->s_deal_str();

if(!submitcheck('bsubmit')){
	
	//搜索显示区域 ****************************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	
	//内容列表区 **************************
	$oL->m_header();
	
	$oL->m_additem('thumb',array('type'=>'image','width'=>210,'height'=>180));
	$oL->m_additem('selectid',array('id'=>'s{aid}'));
	$oL->m_additem('subject',array('len' => 20,));
	$oL->m_additem('shi',array('type'=>'field',));
	$oL->m_additem('ting',array('type'=>'field',));
	$oL->m_additem('chu',array('type'=>'field',));	
	//$oL->m_additem('ccid1');	
	//$oL->m_additem('lpname',array('len'=>'20'));	
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'[编辑]','color'=>'red','url'=>"?entry=extend&extend=huxingarchive&aid={aid}",'width'=>40,));
	//$oL->m_addgroup('{shi}/{ting}/{chu}','{shi}/{ting}/{chu}');//请注意分组不能嵌套，每项只能参与一次分组
	//$oL->m_mcols_style("{thumb}<br>{selectid} &nbsp;{shi}/{ting}/{chu}&nbsp;{detail}<br>{subject}<br/>[{ccid1}]{lpname}");
	$oL->m_mcols_style("{thumb}<br>{selectid} &nbsp;{shi}/{ting}/{chu}&nbsp;{detail}<br>{subject}");
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	$oL->o_end_form('bsubmit','加载');
	$oL->guide_bm('','0');
	
}else{
	//专门针对加载的操作
	$oL->sv_o_load();
}
?>