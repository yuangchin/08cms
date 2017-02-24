<?PHP

//文档模型chid的初始化，尽可能手动确定某个id
$chid = 121;
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

//初始化合辑id，只按受pid，如其它id样式传进来，要转为pid
$setthumb = empty($setthumb) ? '' : $setthumb;
$pid = empty($pid) ? 0 : max(0,intval($pid));

$_arc = new cls_arcedit; //商业地产-合辑兼容
$_arc->set_aid($pid,array('au'=>0,'ch'=>0)); 

$chid_fy = $_arc->archive['chid'];
$arid = in_array($chid_fy,array(2,3)) ? 38 : 36;//指定合辑项目id //$arid = 1;
if(!in_array($_arc->archive['chid'],array(4,115,116))); 

$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?action=$action",//表单url，必填，不需要加入chid及pid

'cols' => 3,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'coids' => array(1),//手动设置允许类系，在会员中心特别需要指定
//'fields' => array(),//允许传入改装过的字段缓存
'select' => "a.* ",//sql中的SELECT部分
'from' => " {$tblprefix}".atbl($chid)." a ",//sql中的FROM部分
'where' => " a.chid='$chid' AND a.pid$arid='$pid' ",
'isab' => 1,//*** 是否合辑内管理：0为普通管理列表，1为辑内管理列表，2为加载内容列表
'pid' => $pid,//合辑id
'arid' => $arid,//*** 指定合辑项目id
'orderby' => "a.aid DESC",//合辑内指定排序,文档表合辑记录则为"a.inorderxx DESC"，xx为合辑项目id
//'pids_allow' => 'self',//*** pid允许的范围：在会员中心必填项，分析当前会员是否具有该合辑的管理权限
'pids_allow' => '-1',//所有
);


#-----------------

$oL = new cls_archives($_init);
//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('caid',array('hidden' => 1,));
$oL->s_additem('orderby');
//$oL->s_additem('shi',array('type'=>'field',));
//$oL->s_additem('ting',array('type'=>'field',));
$oL->s_additem('valid');
//$oL->s_additem("ccid$k",array());
$oL->s_additem('indays');
//$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();
//echo $oL->sqlall;

if($setthumb && $pid){
	$thumbarc = new cls_arcedit;
	$thumbarc->set_aid($setthumb);
	$upthumb = $thumbarc->archive['thumb'];
	$thumbarc->set_aid($pid);
	$thumbarc->updatefield('thumb',$upthumb);
	$thumbarc->updatedb();
	unset($thumbarc);
	$url = $oL->A['url']."&page={$oL->A['page']}".$oL->filterstr;
	$oL->message('缩略图设置成功。',$url);
}

//批量操作项目 ********************
$oL->o_additem('delete');//删除
//$oL->o_additem('readd');//刷新
//$oL->o_additem('valid',array('days' => 30));//上架，days设置上架的天数，0则为无限期
//$oL->o_additem('unvalid');//下架
//$oL->o_additem('incheck');//辑内有效
//$oL->o_additem('unincheck');//辑内无效
// $oL->o_additem('inclear');//退出合辑
//$oL->o_additem('caid');
//$oL->o_additem("ccid$k");

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	
	//内容列表区 **************************
	$oL->m_header(" &nbsp;<a style=\"color:#C00\" href=\"?action=xiangceadd_fy&pid=$pid\" onclick=\"return floatwin('open_arcexit',this)\">>>添加相册</a>",1);
	$oL->m_additem('lx',array('type'=>'field',));
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 20));
	$oL->m_additem('thumb',array('type'=>'image','width'=>'100%','height'=>180));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'[编辑]','url'=>"?action=xiangceadd_fy&aid={aid}",'width'=>40));
// 	$oL->m_additem('editself',array('title'=>'编辑','mtitle'=>'[编辑]','url'=>"?action=xiangceadd_fy&aid={aid}",'width'=>40));
	$oL->m_additem('littlethumb',array('type'=>'url','title'=>'设为缩略图','mtitle'=>'[设为缩略图]','url'=>"?action=$action&pid=$pid&setthumb={aid}",'width'=>40));
	$oL->m_mcols_style("{thumb}<div style=\"clear:both;\"></div>{selectid}{subject}({lx}) &nbsp;{detail}<br>{littlethumb}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} 
	
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区*******************************
	$oL->o_header();
	
	//显示单选项
	$oL->o_view_bools('合辑管理 ',array('inclear','incheck','unincheck',));
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
//	$oL->sv_e_additem('clicks',array());
//	$oL->sv_e_all();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>