<?PHP
/* 参数初始化代码 */
$chid = 121;//必须定义，不接受从url的传参

$chid_fy = cls_env::GetG('chid_fy'); //$_REQUEST['chid_fy'] ? $_REQUEST['chid_fy']:2;
$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str".'&chid_fy='.$chid_fy,//表单url，必填，不需要加入chid及pid
'pre' => '',//默认的主表前缀
'from' => " {$tblprefix}".atbl($chid)." a LEFT JOIN {$tblprefix}".atbl($chid_fy)." b ON a.pid38 = b.aid",//sql中的FROM部分
'select' => "a.*,b.subject as fyname,b.aid as lpaid,b.ccid1 ",//sql中的SELECT部分
'cols' => 5,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'fields' => array(),//允许传入改装过的字段缓存
));
//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array('a.aid' => '相册ID','a.subject' => '相册标题','b.aid' => '房源ID','b.subject' => '房源名称','a.mname'=>'会员账号'),));
// $oL->s_additem('caid');
$oL->s_additem('checked');
$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sql及filter字串处理
if(!empty($chid_fy)) $oL->oS->wheres[] = "b.chid = '$chid_fy'";
$oL->s_deal_str(); //print_r($oL);

//批量操作项目 ********************
$oL->o_additem('delete');
$oL->o_additem('static');
$oL->o_additem('nstatic');
// $oL->o_additem('caid',array('ids'=>array(7)));
$oL->o_addpushs();//推送项目
$oL->o_additem('check');
$oL->o_additem('uncheck');
if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	

	//显示列表区头部 ***************
	$oL->m_header("房源图片&nbsp;&nbsp;内容管理&nbsp;&nbsp;&nbsp;<input class='checkbox' type='checkbox' onclick='chooseall(this)' value=''>全选");
	
	//设置列表项目
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('caid');
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 20));
	$oL->m_additem('thumb',array('type'=>'image','width'=>'100%','height'=>180));
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'[详情]','url'=>"?entry=extend&extend=xiangceadd&aid={aid}",'width'=>40));
	$oL->m_additem('ccid1');
	$oL->m_additem('lx',array('type'=>'field',));
	$oL->m_additem('fyname',array('len' => 20,));
	$oL->m_mcols_style("{thumb}<div style=\"clear:both;\"></div>{selectid}{subject}({lx}) &nbsp;审核({checked})<br>{detail} &nbsp;{fyname}");
	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	
	$oL->m_view_main();
    
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区************
	$oL->o_header();
	
	//显示单选项
	$oL->o_view_bools();
	
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

