<?PHP
/*
** 管理后台脚本，用于文档列表管理，archives.php作为开发基础样本，不建议投入正式使用
** chid尽量手动初始化，可以根据分类或栏目不同而初始化不同的chid
** 
*/

/* 参数初始化代码 */
$chid = 9;//必须定义，不接受从url的传参
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

#-----------------

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str",//表单url，必填，不需要加入chid及pid
'pre' => 'a.',//默认的主表前缀
'from' => "",//sql中的FROM部分，可以通过这里JOIN其它表
'select' => "",//sql中的SELECT部分
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'fields' => array(),//允许传入改装过的字段缓存
));

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array('a.subject' => '标题','a.aid' => '文档ID','a.mname'=>'会员账号'),));//fields留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
#$oL->s_additem('caid',array('ids'=>array(2),)); //ids为允许列出的指定id栏目和子栏目
$oL->s_additem('caid',array('hidden' => 1,));
$oL->s_additem('checked');
$oL->s_additem('valid');
foreach($oL->A['coids'] as $k){
	#$oL->s_additem("ccid$k",array('ids'=>array(),));   //ids为允许列出的指定id分类和子分类
	#$oL->s_additem("ccid3",array('skip'=>1)); //skip不输出html搜索项参数，
	$oL->s_additem("ccid$k",array());
}
$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_addpushs();//推送项目
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('delete');
$oL->o_additem('valid');
$oL->o_additem('unvalid');
$oL->o_additem('static');
$oL->o_additem('nstatic');
$oL->o_additem('readd');

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer_ex("?entry=extend&extend=export_excel&chid=$chid&filename=qiuzu");
	

	//显示列表区头部 ***************
	$oL->m_header();
	
	//设置列表项目
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('selectid');
    $oL->m_additem('aid',array('type'=>'other','title'=>'ID'));
    $oL->m_additem('subject',array('len' => 40,));
	foreach($oL->A['coids'] as $k){
		$oL->m_additem("ccid$k");
	}	
	$oL->m_additem('mj',array('type'=>'other','title'=>'面积','mtitle'=>'{mj}m<sup>2</sup>'));
	$oL->m_additem('zj',array('type'=>'other','title'=>'价格/每月','mtitle'=>'{zj}元'));
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	//$oL->m_additem('valid');
	$oL->m_additem('clicks',array('type'=>'input','view'=>'H','title'=>'点击数','width'=>50,'w' => 3,));
	$oL->m_additem('createdate',array('type'=>'date',));
	$oL->m_additem('enddate',array('type'=>'date',));
	$oL->m_additem('mname',array('title'=>'会员','mtitle'=>'{mname}'));
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>40,));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=qiuzuarchive&aid={aid}",'width'=>40,));
	
	$oL->m_addgroup('{shi}/{ting}/{chu}','{shi}/{ting}/{chu}');//请注意分组不能嵌套，每项只能参与一次分组
	//$oL->m_mcols_style("{selectid} &nbsp;{subject}<br>{shi}/{ting}/{chu}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} &nbsp;{subject}"
	
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