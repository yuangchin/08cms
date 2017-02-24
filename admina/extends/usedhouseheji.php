<?PHP

$isload = empty($isload) ? 1 : max(1,intval($isload));

$pid = empty($pid) ? 0 : max(0,intval($pid));//初始化合辑id，有可能使用其它id样式传进来，如$hejiid等，要转为使用pid
$_arc = new cls_arcedit; //商业地产-合辑兼容
$_arc->set_aid($pid,array('au'=>0,'ch'=>0));
switch ($_arc->archive['chid'])
{
    case 4:
        $chid = 3;
        break;
    case 115:
        $chid = 117;
        break;
    case 116:
        $chid = 118;
        break;
    default:
        echo "请指定正确的模型参数";
}
$arid = $_arc->archive['chid']==4 ? 3 : 36;//指定合辑项目id //$arid = 1;
if(!in_array($_arc->archive['chid'],array(4,115,116)));
$wherestr = $isload == 1 ? '' : "pid$arid = '0'";

# 清空CK插件ID与名称，如果升级该脚本时请继承下去
cleanCookies(array('fyid', 'lpmc'), true);
$_init = array(
'chid' => $chid,//模型id，必填
'url' => "?entry=$entry$extend_str&isload=$isload",//表单url，必填，不需要加入chid及pid
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
//'coids' => array(1),//手动设置允许类系，在会员中心特别需要指定
//'fields' => array(),//允许传入改装过的字段缓存
'isab' => $isload,//*** 是否合辑内管理：0为普通管理列表，1为辑内管理列表，2为加载内容列表
'pid' => $pid,//合辑id
'arid' => $arid,//*** 指定合辑项目id
'where' => $wherestr,
//'orderby' => "b.inorder DESC",//合辑内指定排序,文档表合辑记录则为"a.inorderxx DESC"，xx为合辑项目id

);

/******************/

$oL = new cls_archives($_init);
//头部文件及缓存加载
$oL->top_head();

if($isload==1){

	//搜索项目 ****************************
	//添加搜索项目：s_additem($key,$cfg)
	$oL->s_additem('keyword',array('fields' => array(),));//fields留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
	//$oL->s_additem('caid',array());
	//$oL->s_additem("ccid$k",array());
	$oL->s_additem('checked');
	$oL->s_additem('valid');
	//$oL->s_additem('inchecked',array('field' => 'b.incheck'));//指定合辑中辑内排序的搜索字段，如文档表记录则为a.incheckxx，xx为合辑项目id
	$oL->s_additem('orderby');
	
	//搜索sql及filter字串处理
	$oL->s_deal_str();

	//批量操作项目 ********************
	$oL->o_additem('inclear');
	//$oL->o_additem('incheck');
	//$oL->o_additem('unincheck');
	
	$oL->o_additem('delete');
	$oL->o_additem('check');
	$oL->o_additem('uncheck');
	//$oL->o_additem('readd');
	$oL->o_additem('static');
	$oL->o_additem('nstatic');
	//$oL->o_additem("ccid$k");
	
	if(!submitcheck('bsubmit')){
		
		//搜索显示区域 ****************************
		$oL->s_header();
		$oL->s_view_array();
		$oL->s_footer();
	
		//内容列表区 **************************
		$_tmp_aid = $db->result_one("SELECT aid FROM {$tblprefix}".atbl($chid)." WHERE pid$arid='0'"); //echo $_tmp_aid;
		$_tmp_load = empty($_tmp_aid) ? "" : " &nbsp; <a style=\"color:#C00\" href=\"?entry=extend&extend=$extend&pid=$pid&isload=2\" onclick=\"return floatwin('open_arcexit',this)\">>>加载内容</a>";
		$oL->m_header("$_tmp_load",1);
		
		//设置列表项目
		//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
		$oL->m_additem('selectid');
		$oL->m_additem('subject',array('len' => 40,));
		//$oL->m_additem('caid');
		$oL->m_additem('ccid1');
		$oL->m_additem('valid');
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
		$oL->m_additem('inorder',array('type' => 'input','title'=>'排序','w' => 3,));
		//$oL->m_additem('incheck',array('type'=>'checkbox','atitle'=>'有效','side' => 'L','width'=>50,));
		//$oL->m_additem('incheck',array('type'=>'bool','title'=>'有效',));
		$oL->m_additem('createdate',array('type'=>'date',));
		$oL->m_additem('enddate',array('type'=>'date',));
		$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>40,));
		$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=usedhousearchive&aid={aid}",'width'=>40,));
	
		//显示索引行，多行多列展示的话不需要
		$oL->m_view_top();
		
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
		$oL->guide_bm('','0');
		
	}else{
		//预处理，未选择的提示
		$oL->sv_header();
		//列表区中设置项的数据处理
		//$oL->sv_e_additem('clicks',array());
		$oL->sv_e_additem('inorder',array());
		$oL->sv_e_all();
		//批量操作项的数据处理
		$oL->sv_o_all();
		//结束处理
		$oL->sv_footer();
	}
}else{ // loader

	//搜索项目 ****************************
	//添加搜索项目：s_additem($key,$cfg)
	$oL->s_additem('keyword',array('fields' => array(),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
	//$oL->s_additem('caid',array());
	$oL->s_additem("ccid1",array());
	$oL->s_additem('checked');
	$oL->s_additem('valid');
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
		
		//设置列表项目
		$oL->m_additem('selectid');
		$oL->m_additem('subject',array('len' => 40,));
		$oL->m_additem('ccid1');
		$oL->m_additem('clicks',array('title'=>'点击',));
		//$oL->m_additem("ccid$k",array('view'=>'H',));
		
		$oL->m_additem('valid');
		$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
		$oL->m_additem('createdate',array('type'=>'date',));
		$oL->m_additem('enddate',array('type'=>'date',));

		$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=archiveinfo&aid={aid}",'width'=>40,));

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
	
}
?>