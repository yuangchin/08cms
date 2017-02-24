<?PHP
/*
** 合辑内的文档列表管理，archives_pid.php仅用于示例样本，不建议投入正式使用
** 注意两种合辑关系：1、文档表记录pid 2、合辑关系表，特别体现在select及from的设置 
** 合辑内的管理不再分析是否具体栏目管理权限
*/ 
/* 参数初始化代码 */
$chid = 11;//脚本固定针对某个模型
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

$pid = empty($pid) ? 0 : max(0,intval($pid));//初始化合辑id，有可能使用其它id样式传进来，如$hejiid等，要转为使用pid
$arid = 2;

$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid

'cols' => 3,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'coids' => array(1),//手动设置允许类系，在会员中心特别需要指定
//'fields' => array(),//允许传入改装过的字段缓存

'select' => "a.*,d.subject as lpname,d.ccid1 ",//sql中的SELECT部分
'from' => " {$tblprefix}aalbums b INNER JOIN {$tblprefix}".atbl($chid)." a ON a.aid=b.inid INNER JOIN {$tblprefix}".atbl(5)." c ON b.pid = c.aid INNER JOIN {$tblprefix}".atbl(4)." d ON c.pid3 = d.aid",//sql中的FROM部分
'where' => " a.chid='$chid' AND b.pid='$pid' ",
'isab' => 1,//*** 是否合辑内管理：0为普通管理列表，1为辑内管理列表，2为加载内容列表
'pid' => $pid,//合辑id
'arid' => $arid,//*** 指定合辑项目id
'orderby' => "a.aid DESC",//合辑内指定排序,文档表合辑记录则为"a.inorderxx DESC"，xx为合辑项目id

);


/******************/

$oL = new cls_archives($_init);

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//添加搜索项目：s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array(),));//fields留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')


//搜索sql及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_additem('inclear');

if(!submitcheck('bsubmit')){
	
	//搜索显示区域 ****************************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();

	//内容列表区 **************************
	// 此处不要 - 添加，只需加载　它所属的　楼盘下的 户型 即可。
	$oL->m_header("  &nbsp;<a style=\"color:#C00\" href=\"?entry=extend&extend=huxing_load&pid=$pid&arid=2\" onclick=\"return floatwin('open_arcexit',this)\">>>加载户型</a> ",1);	
	// &nbsp;<a style=\"color:#C00\" href=\"?entry=extend&extend=huxingarchive&pid=$pid&arid=$arid\" onclick=\"return floatwin('open_arcexit',this)\">>>添加户型</a>
	
	$oL->m_additem('thumb',array('type'=>'image','width'=>'100%','height'=>180));
	$oL->m_additem('selectid',array('id'=>'s{aid}'));
	$oL->m_additem('subject',array('len' => 20,));
	$oL->m_additem('shi',array('type'=>'field',));
	$oL->m_additem('ting',array('type'=>'field',));
	$oL->m_additem('chu',array('type'=>'field',));	
	$oL->m_additem('ccid1');	
	$oL->m_additem('lpname',array('len'=>'20'));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'[编辑]','color'=>'red','url'=>"?entry=extend&extend=huxingarchive&aid={aid}",'width'=>40,));
	//$oL->m_addgroup('{shi}/{ting}/{chu}','{shi}/{ting}/{chu}');//请注意分组不能嵌套，每项只能参与一次分组
	//$oL->m_mcols_style("{thumb}<br>{selectid} &nbsp;{shi}/{ting}/{chu}&nbsp;{detail}<br>{subject}<br/>[{ccid1}]{lpname}");
	$oL->m_mcols_style("{thumb}<div style=\"clear:both;\"></div>{selectid} &nbsp;{shi}/{ting}/{chu}&nbsp;{detail}<br>{subject}");
	
	//显示索引行，多行多列展示的话不需要
	//$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区*******************************
	$oL->o_header();
	
	//显示单选项
	$oL->o_view_bools('合辑管理 ',array('inclear','incheck','unincheck',));
	$oL->o_view_bools();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('&nbsp;&nbsp;&nbsp;1.合辑管理>>清除，该操作是把数据在本页面列表中删除，可以通过"加载户型"操作加载进来。<br/>&nbsp;<br/>','1');
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();

	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>
