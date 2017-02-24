<?PHP
/* 参数初始化代码 */
$chid = 4;//必须定义，不接受从url的传参
$caid = empty($caid) ? 0 : $caid;
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid
'pre' => 'a.',//默认的主表前缀
'from' => $tblprefix.atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON c.aid=a.aid ",//sql中的FROM部分
'select' => "",//sql中的SELECT部分
'where' => "(c.leixing='0' OR c.leixing='2')", //楼盘条件
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
'orderby' => "", //a.refreshdate DESC 
//'fields' => array(),//允许传入改装过的字段缓存
));

//头部文件及缓存加载
$oL->top_head();
$oL->resetCoids($oL->A['coids']); //根据 房产参数设置,控制类系

//搜索项目 ****************************
$oL->s_additem('keyword');
$oL->s_additem('checked');
foreach($oL->A['coids'] as $k){
	if(in_array($k,array(18,41))) continue;
	$oL->s_additem("ccid$k",array());
	if($k==3) $oL->s_additem("ccid14",array());
}
$oL->s_additem('orderby');
$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str(); //echo $oL->sqlall;

//批量操作项目 ********************
$oL->o_additem('delete');
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('static');
$oL->o_additem('nstatic');
$oL->o_additem('readd');
//$oL->o_additem("ccid18");
//$oL->o_additem("ccid41");
$oL->o_addpushs();//推送项目
$oL->o_additem("leixing");

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	//$oL->s_view_array(array('keyword','orderby','checked','ccid41',));//固定显示项
	//$oL->s_adv_point();//设置隐藏区
	$oL->s_view_array();
	$oL->s_footer();
	

	//显示列表区头部 ***************
	$oL->m_header('小区 内容管理');
	
	//设置列表项目
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len'=>60,'addno'=>7));
	foreach($oL->A['coids'] as $k){		
		if(in_array($k,array(18,41))) continue;
		//if(in_array($k,array(7,8))) $icon = 1;
		//else                        $icon = 0;
		if(in_array($k,array(1)))   $view = '';
		else                        $view = 'H';
		$oL->m_additem("ccid$k",array('view'=>$view));
	}	
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));	

	$oL->m_additem('azxs',array('type'=>'ucount','title'=>'资讯','url'=>"?entry=extend&extend=zixuns_pid&pid={aid}",'func'=>'gethjnum','arid'=>'1','chid'=>1,'width'=>28,));
    $oL->m_additem('atps',array('type'=>'ucount','title'=>'相册','url'=>"?entry=extend&extend=xiangces_pid&pid={aid}",'func'=>'gethjnum','arid'=>'3','chid'=>7,'width'=>28,));
	$oL->m_additem('ahss',array('type'=>'ucount','title'=>'户型','url'=>"?entry=extend&extend=huxings_pid&pid={aid}",'func'=>'gethjnum','arid'=>'3','chid'=>11,'width'=>28,));
	$oL->m_additem('aesfys',array('type'=>'url','title'=>'二手','mtitle'=>'[{lpesfsl}]','url'=>"?entry=extend&extend=usedhouseheji&pid={aid}",'width'=>28,));
	$oL->m_additem('aczfys',array('type'=>'url','title'=>'出租','mtitle'=>'[{lpczsl}]','url'=>"?entry=extend&extend=chuzuheji&pid={aid}",'width'=>28,));
	$oL->m_additem('azbs',array('type'=>'ucount','title'=>'周边','url'=>"?entry=extend&extend=peitaos_pid&pid={aid}",'func'=>'gethjnum','arid'=>'1','chid'=>8,'width'=>28,));

	$oL->m_additem('refreshdate',array('type'=>'date',));	
	$oL->m_additem('updatedate',array('type'=>'date','view'=>'H',));
	//$oL->m_additem('createdate',array('type'=>'date',));
	
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>30,'view'=>'H',));
	$oL->m_additem('dj',array('type'=>'url','mtitle'=>'价格','url'=>"?entry=extend&extend=jiagearchive&aid={aid}&isnew=0",'width'=>60));
	$oL->m_additem('detail',array('type'=>'url','mtitle'=>'详情','url'=>"?entry=extend&extend=xiaoquadd&aid={aid}",'width'=>60));
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
	
	//显示推送位
	$oL->o_view_pushs();
	
	//显示整行项
	$oL->o_view_rows();
	//trbasic('<input type="checkbox" value="1" name="arcdeal[leixing]" class="checkbox">&nbsp;楼盘小区属性','','<select style="vertical-align: middle;" name="arcleixing">'.makeoption(array('0'=>'楼盘与小区','1'=>'楼盘','2'=>'小区')).'</select>','');

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