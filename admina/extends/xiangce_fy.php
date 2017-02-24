<?PHP

$chid = 121;//脚本固定针对某个模型
$setthumb = empty($setthumb) ? '' : $setthumb;

$pid = empty($pid) ? 0 : max(0,intval($pid));//初始化合辑id，有可能使用其它id样式传进来，如$hejiid等，要转为使用pid

$_arc = new cls_arcedit; //商业地产-合辑兼容
$_arc->set_aid($pid,array('au'=>0,'ch'=>0)); 

$chid_fy = $_arc->archive['chid'];
$arid = in_array($chid_fy,array(2,3)) ? 38 : 36;//指定合辑项目id //$arid = 1;
if(!in_array($_arc->archive['chid'],array(4,115,116))); 

$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid

'cols' => 3,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'coids' => array(1),//手动设置允许类系，在会员中心特别需要指定
//'fields' => array(),//允许传入改装过的字段缓存
'select' => "a.*,b.subject as fyname,b.ccid1 ",//sql中的SELECT部分
'from' => " {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}".atbl($_arc->archive['chid'])." b ON a.pid$arid = b.aid",//sql中的FROM部分
'where' => " a.chid='$chid' AND a.pid$arid='$pid' ",
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
$oL->s_additem('lx');

//搜索sql及filter字串处理
$oL->s_deal_str();

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
$oL->o_additem('delete');
// $oL->o_additem('caid',array('ids'=>array(7)));
$oL->s_additem('lx',array('type'=>'field',));
$oL->o_addpushs();//推送项目

if(!submitcheck('bsubmit')){
	
	//搜索显示区域 ****************************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	
	//内容列表区 **************************
	$hlinks = " &nbsp; <input class='checkbox' type='checkbox' onclick='chooseall(this)' value=''>全选";
	$hlinks .= " &nbsp; <a style=\"color:#C00\" href=\"?entry=extend&extend=xiangceadd&pid=$pid\" onclick=\"return floatwin('open_arcexit',this)\">&gt;&gt;添加相册</a>";
	$hlinks .= " &nbsp; <a style=\"color:#C00\" href=\"?entry=extend&extend=xiangceall&chid_fy=$chid_fy&isframe=1\" target='_blank'>&gt;&gt;所有相册</a>";
	$oL->m_header("$hlinks",1);
	$oL->m_additem('selectid');
	$oL->m_additem('caid');
	$oL->m_additem('subject',array('len' => 20, 'url'=>'#'));
	$oL->m_additem('thumb',array('type'=>'image','width'=>'100%','height'=>180));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'[编辑]','url'=>"?entry=extend&extend=xiangceadd&aid={aid}",'width'=>40,'view'=>'H'));
	$oL->m_additem('littlethumb',array('type'=>'url','title'=>'设为缩略图','mtitle'=>'[设为缩略图]','url'=>"?entry=extend&extend=xiangce_fy&pid=$pid&setthumb={aid}",'width'=>40,'view'=>'H'));
	$oL->m_additem('ccid1');
	$oL->m_additem('lx',array('type'=>'field',));
	$oL->m_additem('fyname',array('len' => 20,));	
	$oL->m_mcols_style("{thumb}<div style=\"clear:both;\"></div>{selectid}{subject}({lx}) <br>{detail}&nbsp;{littlethumb}&nbsp;&nbsp;<br/>[房源]{fyname}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} 
	
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
	$oL->o_view_upushs();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('&nbsp;&nbsp;&nbsp;1.选择操作项目>>删除，该操作既把本页面的数据删除，也把数据来源的文档表的对应数据删除。<br/>&nbsp;&nbsp;&nbsp;&nbsp;(比如：在本页面删除了相册A，那么在对应的栏目"楼盘相册"列表中的相册A也会被删除。)','1');
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();
	
	//列表区中设置项的数据处理
	#$oL->sv_e_additem('clicks',array());
	#$oL->sv_e_additem('inorder',array());
  	#$oL->sv_e_additem('incheck',array('type' => 'bool'));
	$oL->sv_e_all();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
?>