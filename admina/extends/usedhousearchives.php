<?PHP
$chid = 3;//必须定义，不接受从url的传参

#-----------------

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid
'pre' => 'a.',//默认的主表前缀
'from' => "{$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON a.aid=c.aid ",//sql中的FROM部分，可以通过这里JOIN其它表
'select' => "",//sql中的SELECT部分
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'fields' => array(),//允许传入改装过的字段缓存
));
// 以上,LEFT JOIN {$tblprefix}members，效率比INNER JOIN低点，防止没有关联会员下，列表中不出现。


# 清空CK插件ID与名称，如果升级该脚本时请继承下去
cleanCookies(array('fyid', 'lpmc'), true);


//头部文件及缓存加载
$oL->top_head();
$oL->resetCoids($oL->A['coids']); //根据 房产参数设置,控制类系

//搜索项目 ****************************
//s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array('a.subject' => '标题','a.keywords' => '关键词','a.mname' => '会员账号','a.aid' => '文档ID','c.lxdh'=>'联系电话'),));
$oL->s_additem('caid',array('hidden' => 1,));
$oL->s_additem('checked');
$oL->s_additem('valid');
foreach($oL->A['coids'] as $k){
	if(in_array($k,array(19))) continue;
	$oL->s_additem("ccid$k",array());
	if($k==3) $oL->s_additem("ccid14",array());
}
$oL->s_additem('mchid',array('pre'=>'a.'));
$oL->s_additem('orderby');
$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_addpushs();//推送项目

$oL->o_additem('delete');
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('valid');
$oL->o_additem('unvalid');
$oL->o_additem('readd');
$oL->o_additem('static');
$oL->o_additem('nstatic');


$oL->o_additem("ccid9");
//$oL->o_additem("ccid19",array('guide'=>'只对经纪人发布的房源有效。'));

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer_ex("?entry=extend&extend=export_excel&chid=$chid&filename=userhouse");
	if(empty($fcdisabled2)) RelCcjs($chid,1,2,1);
	if(empty($fcdisabled3)) RelCcjs($chid,3,14,2);

	//显示列表区头部 ***************
	$oL->m_header();
	
	//设置列表项目
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('selectid');
    $oL->m_additem('aid',array('type'=>'other','title'=>'ID'));
    $oL->m_additem('subject',array('len' => 40,));
	$oL->m_additem('clicks',array('title'=>'点击数','type'=>'input','width'=>50,'view'=>'S','w' => 3,));
    $oL->m_additem('ulpmc',array('title'=>'小区名称','width'=>80,));
    foreach($oL->A['coids'] as $k){
		if(in_array($k,array(19))) continue;
		$a = in_array($k,array(1,9)) ? array() : array('view'=>'H',);
		$oL->m_additem("ccid$k",$a);
		$k == 9 && $oL->m_additem('ccid9date',array('title'=>'置顶到期时间','type'=>'date','view'=>'H','width'=>100));
	}
	$oL->m_additem('atps',array('type'=>'ucount','title'=>'房源图片','url'=>"?entry=extend&extend=xiangce_fy&pid={aid}",'func'=>'gethjnum','arid'=>'38','chid'=>121,'width'=>28,));
    $oL->m_additem('yixiang',array('type'=>'ucount','title'=>'意向','url'=>"?entry=extend&extend=commu_yixiang&aid={aid}&caid=$caid",'func'=>'getjhnum','cuid'=>'46','width'=>28,));
	$oL->m_additem('jubao',array('type'=>'ucount','title'=>'举报','mtitle'=>'[{stat0}]','url'=>"?entry=extend&extend=jubaos&aid={aid}",'func'=>'getjhnum','cuid'=>4,'width'=>35,));
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	$oL->m_additem('createdate',array('type'=>'date','view'=>'H',));
	$oL->m_additem('refreshdate',array('type'=>'date',));	
	$oL->m_additem('enddate',array('type'=>'date',));
	$oL->m_additem('info',array('type'=>'url','view'=>'H','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>40,));
	$oL->m_additem('xingming',array('title'=>'用户名','width'=>40,));
	$oL->m_additem('mchid',array('title'=>'会员类型','width'=>80,)); 
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=usedhousearchive&aid={aid}",'width'=>40,));
	
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
	$oL->o_view_bools('', array(), 8);
	
	//显示推送位
	$oL->o_view_pushs();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('','0');
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();
	
	//列表区中设置项的数据处理
	$oL->sv_e_additem('clicks',array());
	$oL->sv_e_additem('vieworder',array());
	$oL->sv_e_additem('checked',array('type' => 'bool'));
	$oL->sv_e_all();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>