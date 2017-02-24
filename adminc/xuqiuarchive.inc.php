<?PHP
$chid = empty($chid) ? 0 : $chid; //指定chid
$caid = $chid;


cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

$oA = new cls_archive();

//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();
$_choose = $chid == 9 ? 'qzadd' : 'qgadd'; 
$isadd && backnav('xuqiu',$_choose);

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 会员中心只能编辑本人发布的文档 */
$oA->allow_self();

/* 设置表单数据数组名，不设则默认为fmdata */
//$oA->setvar('fmdata','archivenew');

/* 设置允许处理的类系，不设则按主表所有类系 */
//$oA->setvar('coids',array(2,3,4));
if($isadd){		
	// 发布出租房源限额控制：
    $returnInfo = publishLimit($curuser,$chid,$oA);
    if(!empty($returnInfo['limitMessageStr'])) $oA->message($returnInfo['limitMessageStr']);	
}




/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;
#-----------------

if(!submitcheck('bsubmit')){
	if($isadd){//添加才需要	
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("","?action=$action");
	
	//处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_album('pid');
	
	//处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_caid(array('hidden'=>1));
	
	$oA->fm_fields(array('subject'),0);
	$oA->fm_ccids(array(1)); 
	$oA->fm_fields(array('mj','zj','jtyq'));
	
	if($isadd){
		$oA->fm_cfanddong(array('lxdh','xingming'));
	}else{		
		$oA->fm_fields(array('lxdh','xingming'));
	}
	$oA->fm_fields(array(),0);	
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid')
	//展示多个属性项，管理后台默认为array('createdate')，会员中心默认为array('ucid')
	$oA->fm_params(array());
	
	//处理剩余的有效字段，可以传入排除字段$nos
	//$oA->fm_fields_other(array());
	
	if($isadd){
		//需传入验证码类型，否则默认为'archive'
		$oA->fm_regcode('archive');
		$oA->fm_footer('bsubmit','立即发布');
	}else{
		//输入跟submitcheck(按钮名称)相同的值
		$oA->fm_footer('bsubmit');
	}
	
	
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	/*
	** 注意：数据处理端同样要严格指定哪些是需要处理的字段或类系!
	** 
	** 
	*/
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
	//处理多个属性项，管理后台默认为array('createdate')，会员中心默认为array('ucid')
	$oA->sv_params(array());
	
	$oA->sv_enddate();
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();

	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid',0);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>

