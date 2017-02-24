<?PHP
/*
** 管理后台脚本，兼容了文档添加与详情编辑，如果拆分两者脚本，可在详情脚本中去除添加专用部分的代码
** 如通过url传入$chid，可基本兼容不同模型的文档操作
*/
/* 参数初始化代码 */
$chid = 113;//指定chid
$arid = 33;
$pchid = 4; //添加时-选择所属合辑(楼盘)

cls_env::SetG('chid',$chid);
cls_env::SetG('arid',$arid);

$oA = new cls_archive();

/* 0为详情编辑，1为文档添加系 */
$isadd = $oA->isadd;

$oA->top_head();//文件头部

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;
#-----------------

if(!submitcheck('bsubmit')){
	
	if($isadd){//添加才需要
		//添加时预处理类目
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("楼盘分销-基本情况","?entry=extend$extend_str");
	
	//处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->lpfx_to_building(); // 小区名称 'lpmc'
	//$oA->fm_album('pid');
	$oA->fm_caid(array('hidden'=>1));	
	$oA->fm_fields(array('subject'),0);
	$oA->fm_fields(array('keywords'),0);
	$oA->fm_fields(array('abstract'),0);
	$oA->fm_fields(array('thumb'),0);	
	$oA->fm_ccids(array());
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('楼盘分销-详情');
	$oA->fm_fields(array('kprq'),0);
    $oA->fm_enddate('分销活动结束时间');//到期时间
	$oA->fm_fields(array('yhsm'),0);
	$oA->fm_fields(array('yj'),0);
	$oA->fm_fields(array('tel'),0);
	$oA->fm_fields(array('yds'),0);
	$oA->fm_fields(array('tjs'),0);
	$oA->fm_fields(array('deal'),0);
	$oA->fm_fields(array(),0);	
	$oA->fm_footer();	

	$oA->fm_header('扩展设置','',array('hidden'=>1));	
	$oA->fm_params(array());	
	$oA->fm_footer('bsubmit');
	$oA->fm_guide_bm('','0');
}else{
	if($isadd){
		//需传入验证码类型，否则默认为'archive'
		$oA->sv_regcode('archive');
		//添加时预处理类目，可传$coids：array(1,2)
		$oA->sv_pre_cns(array());
		if(empty(${$oA->fmdata}['pid33'])) $oA->message("请选择有效的楼盘",M_REFERER);
	}
	
	//分析权限，添加权限或后台管理权限
	$oA->sv_allow();
	
	if($isadd){
		//增加一个文档
		if(!$oA->sv_addarc()){
			//添加失败处理
			$oA->sv_fail();
		}
	}
	
	//类目处理，可传$coids：array(1,2)
	$oA->sv_cns(array());
	
	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	//处理多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	$oA->sv_params(array('enddate','createdate','clicks','jumpurl','customurl','relate_ids'));
	$oA->sv_param('arctpls');
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
	
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid33',33);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>
