<?PHP

/* 参数初始化代码 */
$chid = 11;//必须定义，不接受从url的传参
//$chid = empty($chid) ? 0 : max(0,intval($chid));//接受外部传chid，但要做好限制

#-----------------

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?action=$action",//表单url，必填，不需要加入chid及pid
'pre' => 'a.',//默认的主表前缀
'where' => "a.mid='{$curuser->info['mid']}'",//sql中的初始化where，限定为自已的文档
'from' => "",//sql中的FROM部分
'select' => "",//sql中的SELECT部分
'cols' => 3,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
'coids' => array(1,),//手动设置允许类系
//'fields' => array(),//允许传入改装过的字段缓存
));
//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//s_additem($key,$cfg)
$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$oL->s_additem('caid',array('ids' => array(502,504)));
$oL->s_additem('orderby');
//$oL->s_additem('shi',array('type'=>'field',));
//$oL->s_additem('valid');
foreach($oL->A['coids'] as $k){
	//$oL->s_additem("ccid$k",array());
}
//$oL->s_additem('checked');
$oL->s_additem('indays');
$oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_additem('delete');//删除
$oL->o_additem('static',array('title'=>'生成静态'));
//$oL->o_additem('valid',array('days' => 30));//上架，days设置上架的天数，0则为无限期
//$oL->o_additem('unvalid');//下架
//$oL->o_additem('caid');

foreach($oL->A['coids'] as $k){
	//$oL->o_additem("ccid$k");
}
// $oL->o_additem("ccid19",array('limit'=>6,'title'=>'推荐位')); //类系限额操作

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	backnav('loupanbar','huxing');
	$oL->s_header();
	//$oL->s_view_array(array('keyword'));//固定显示项
	//$oL->s_adv_point();//设置隐藏区
	$oL->s_view_array();
	$oL->s_footer();
	

	//显示列表区头部 ***************
	$oL->m_header();
	
	//设置列表项目
	//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
	
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 20));
	$oL->m_additem('thumb',array('type'=>'image','width'=>210,'height'=>180));
	
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'[编辑]','url'=>"?action=huxingadd&aid={aid}",'width'=>40,'view'=>'H'));
	$oL->m_mcols_style("{thumb}<br>{selectid}{subject} &nbsp;{detail}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} &nbsp;{subject}"
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
	$oL->guide_bm("本列表只显示/管理自己添加的楼盘户型；添加入口在：管理楼盘 - 具体某个楼盘内 添加。",'fix');
	
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