<?PHP
//文档模型chid的初始化，尽可能手动确定某个id
$chid = 1;
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

//初始化合辑id，只按受pid，如其它id样式传进来，要转为pid
$pid = empty($pid) ? 0 : max(0,intval($pid));

$_arc = new cls_arcedit; //商业地产-合辑兼容
$_arc->set_aid($pid,array('au'=>0,'ch'=>0)); 

$arid = $_arc->archive['chid']==4 ? 1 : 35;//指定合辑项目id
//echo ":::$arid";

$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?action=$action&pid=$pid",//表单url，必填，不需要加入chid及pid

'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'coids' => array(1),//手动设置允许类系，在会员中心特别需要指定
//'fields' => array(),//允许传入改装过的字段缓存

'isab' => 2,//*** 是否合辑内管理：0为普通管理列表，1为辑内管理列表，2为加载内容列表
'pid' => $pid,//合辑id
'arid' => $arid,//*** 指定合辑项目id
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
$oL->s_additem('caid',array('ids' => array(502,504)));
$oL->s_additem('orderby');
//$oL->s_additem('shi',array('type'=>'field',));
//$oL->s_additem('ting',array('type'=>'field',));
$oL->s_additem('valid');
//$oL->s_additem("ccid$k",array());
$oL->s_additem('indays');
//$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	

	//显示列表区头部 ***************
	$oL->m_header();
	
	//设置列表项目，如果列表项中包含可设置项，需要在数据储存时，加入设置项的处理
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 40,));
	//$oL->m_additem('caid');
	$oL->m_additem('clicks',array('title'=>'点击',));
	//$oL->m_additem("ccid$k",array('view'=>'H',));
	$oL->m_additem('valid');
	//$oL->m_additem('shi',array('type'=>'field',));
	$oL->m_additem('createdate',array('type'=>'date',));
	//$oL->m_additem('refreshdate',array('type'=>'date','view'=>'H',));	
	//$oL->m_additem('enddate',array('type'=>'date',));
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?action=archiveinfo&aid={aid}",'width'=>40,));
	
	//$oL->m_addgroup('{shi}/{ting}','{shi}/{ting}');//请注意分组不能嵌套，每项只能参与一次分组
	//$oL->m_mcols_style("{selectid} &nbsp;{subject}<br>{shi}/{ting]/{chu}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} &nbsp;{subject}"
	
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
	$oL->sv_o_load();
}
?>