<?PHP

/* 参数初始化代码 */
$chid = 107;//指定chid
$caid = 559;
$pchid = 4;
$arid = 1;

cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);
cls_env::SetG('pchid',$pchid);
cls_env::SetG('arid',$arid);

#-----------------

$oA = new cls_archive();

//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();


/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();
$pchid = 4; //添加时-选择所属合辑(楼盘)
/* 会员中心只能编辑本人发布的文档 */
$oA->allow_self();

/* 设置表单数据数组名，不设则默认为fmdata */
//$oA->setvar('fmdata','archivenew');

/* 设置允许处理的类系，不设则按主表所有类系 */
$oA->setvar('coids',array(1,2,3,14,18));

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;
#-----------------

if(!submitcheck('bsubmit')){	
	
	if($isadd){//添加才需要
		//添加时预处理类目
		//会员中心设置提示信息,如下a,b样式。limit,valid数据，请先计算出。
		//a: $madd_msg = $oA->getmtips(array('check'=>1,'limit'=>array($rules['total'],$total),'valid'=>array($rules['valid'],$valid),),'');
		//   $oA->fm_guide_bm("madd_ch02",'fix'); //madd_ch02中这是占位符号{$madd_msg},则$madd_msg会自动加到madd_ch02中去。
		//b: $msg = $oA->getmtips(array('check'=>1,'limit'=>array($rules['total'],$total),),'');
		//   $oA->fm_guide_bm($msg,'fix');
		backnav('tejia','add');
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("特价房 - 基本信息","?action=$action");
	if($isadd){ $oA->fm_mylpSelect(); } //@$arc->archive['xxx']
	//echo '<input type="hidden" id="fmdata[pid]" name="fmdata[pid]" value="589868">';
	
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
	$oA->fm_ccids(array('18')); 	
	//($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	
	
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('其他');
	$oA->fm_fields(array(),0);
	
	if($isadd){
		//需传入验证码类型，否则默认为'archive'
		$oA->fm_regcode('archive');
	}
	
	//输入跟submitcheck(按钮名称)相同的值
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
	//处理多个属性项，管理后台默认为array('createdate')，会员中心默认为array('ucid')
	$oA->sv_params(array());
	
	$oA->sv_enddate();//上架
	/*
	if($isadd){ 
		if($sendtype){
			$oA->sv_enddate();//上架
		}else{
			$oA->arc->setend(0);//下架
		}
	}*/
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();

	//要指定合辑id变量名$pidkey、合辑项目$arid
	if($isadd){ 
		$oA->sv_album('pid',1);
	}
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>

