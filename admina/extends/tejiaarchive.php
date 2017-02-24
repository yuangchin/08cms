<?PHP

/* 参数初始化代码 */
$chid = 107;
$caid = 559;
cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

$oA = new cls_archive();

/* 0为详情编辑，1为文档添加系 */
$isadd = $oA->isadd;

$oA->top_head();//文件头部
$pchid = 4; //添加时-选择所属合辑(楼盘)


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
	$oA->fm_header("特价房 - 基本信息","?entry=extend$extend_str");
	
	//处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_album('pid',0,"ajax=exarc_list"); 
	
	//标题
	$oA->fm_fields(array('subject','keywords','abstract'),0);
	
	//处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_caid(array('hidden' => 1));
	
	//($coids)，处理分类，$coids：array(3,4,5)
	//$oA->fm_ccids();
	$oA->fm_rccid1(); // 1,2,
	$oA->fm_rccid3(); // 3,14
	$oA->fm_fields(array('lh','fh'));	
	$oA->fm_footer();	

	
	//($title)，$title手动设置标题
	$oA->fm_header('详情设置');
	//总价、面积、单价
	$oA->fm_cprice();	
	$oA->fm_chuxing(); // 户型 选择字段
	$oA->fm_clouceng(); // 楼层组合: (2合一组合)
	$oA->fm_fields(array('louxing',),0); // 楼型,房屋结构	
	$oA->fm_fields(array('zxcd','cx'));
	$oA->fm_ccids(array('18','9')); 
	$oA->fm_ccids(array());  
	//($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	
	
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('其他');
	$oA->fm_fields(array(),0);

	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	if($isadd){
		//需传入验证码类型，否则默认为'archive'
		$oA->sv_regcode('archive');
		
		//添加时预处理类目，可传$coids：array(1,2)
		$oA->sv_pre_cns(array());
		
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
	$oA->sv_params(array());
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
	
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid',1);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>
