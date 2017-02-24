<?PHP

/* 参数初始化代码 */
$mchid = 2;//可手动指定，不接收外部参数
$pid4 = empty($pid)?0:max(1,intval($pid));
if(!in_array($mchid,array(0,1,2))) $mchid = 0;
$_init = array(
'mchid' => $mchid,//会员模型id，必填，可为0
'url' => "?entry=$entry$extend_str&pid=$pid4",//表单url，必填，不需要加入mchid
'from' => "{$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid = s.mid ",
'select'=>"m.*,s.szqy,s.xingming",
'where'=>" m.pid4=$pid4 AND m.incheck4='1' "
);
/******************/

$oL = new cls_members($_init);

//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
$oL->s_additem('keyword',array('fields' => array('m.mname' => '会员帐号','m.mid' => '会员ID')));
$oL->s_additem('checked');//审核

//搜索sql及filter字串处理 ****************
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_addpushs();//推送项目

$oL->o_additem('delete');
$oL->o_additem('check');
$oL->o_additem('uncheck');
$oL->o_additem('static');
$oL->o_additem('unstatic');

if(!submitcheck('bsubmit')){
	
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();
	$oL->s_footer();
	
	//列表区 ***************
	$oL->m_header();
	//设置列表项目
	$oL->m_additem('selectid');
	$oL->m_additem('subject',array('len' => 40,'field' => 'mname','title'=>'用户名','nourl'=>array(1,13)));//处理了会员标记及空间url的标题，nourl表示不需要空间url
	$oL->m_additem('regip',array('type'=>'regip','title'=>'注册IP','len' => 40,'view'=>'H'));
	$oL->m_additem('xingming',array('title'=>'姓名'));
	$oL->m_additem('mchid');//会员类型
	$oL->m_additem('szqy',array('type'=>'szqy','title'=>'所在区域'));    
	$oL->m_additem('checked',array('type'=>'bool','title'=>'审核',));
	$oL->m_additem('regdate',array('type'=>'date',));//注册时间
    $oL->m_additem('grouptype14date',array('title'=>'失效日期','type'=>'date',));//注册时间
	$oL->m_additem('lastvisit',array('type'=>'date','view'=>'H',));//上次登录时间
	$oL->m_additem('info',array('type'=>'url','title'=>'更多','mtitle'=>'更多','url'=>"?entry=extend&extend=memberinfo&mid={mid}",'width'=>30));
	$oL->m_additem('group',array('type'=>'url','title'=>'会员组','mtitle'=>'会员组','url'=>"?entry=extend&extend=membergroup&mid={mid}",'width'=>40,));
	$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?entry=extend&extend=memberedit&mid={mid}",'width'=>30,));
	$mchid != 1 && $oL->m_additem('static');//会员空间静态
	$oL->m_additem('trustee',array('title'=>'代管'));//会员中心代管

	
	//显示索引行
	$oL->m_view_top();
	
	//全部列表区处理，如果需要定制，尽量使用类中的细分方法
	$oL->m_view_main();
	
	//显示列表区尾部
	$oL->m_footer();
	
	//显示批量操作区************
	$oL->o_header();
	
	//显示单选项
	$oL->o_view_bools('',array(),8);
	
	//显示推送位
	$oL->o_view_pushs();
	
	//显示整行项
	$oL->o_view_rows();
	
	$oL->o_footer('bsubmit');
	$oL->guide_bm('','0');
	
	
}else{
	//预处理，未选择的提示
	$oL->sv_header();
	
	//批量操作项的数据处理
	$oL->sv_o_all();
	
	//结束处理
	$oL->sv_footer();
}
