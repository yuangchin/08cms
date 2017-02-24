<?PHP
$chid = 104;

cls_env::SetG('chid',$chid);


$oA = new cls_archive();

//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();
$isadd && backnav('designNews','add');

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

$oA->allow_self(); 

/* 设置允许处理的类系，不设则按主表所有类系 */
resetCoids($oA->coids, array(42)); 
//resetCoids($oA->coids, array(19,31)); 
//print_r($oA->coids);

$style = " style='font-weight:bold;color:#F00'"; $valid_msg = "";
//发布数量限制
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
if($curuser->info['grouptype31']) $exconfigs = $exconfigs['gssendrules'][$curuser->info['grouptype31']][$chid];
if($curuser->info['grouptype32']) $exconfigs = $exconfigs['sjsendrules'][$curuser->info['grouptype32']][$chid];

$ntotal = cls_DbOther::ArcLimitCount($chid, '');
empty($exconfigs['total']) && $exconfigs['total'] = 999999;




if($isadd){ 
	
	$mchid = $curuser->info['mchid'];
	//*
	if($mchid == 3){//属于经纪公司
		$caid = 554; //找到栏目ID
	}else{
		$caid = 512;
	}//*/
	$caid = empty($caid) ? 0 : $caid;
	cls_env::SetG('caid',$caid);
	if($mchid==3 && empty($curuser->info['cmane'])){ 
		m_guide("mchid12_note",'fix');
		die();
	}elseif(($mchid==11 || $mchid==12) && empty($curuser->info['companynm'])){ 
		m_guide("mchid12_note",'fix');
		die();
	}elseif(($mchid==13) && empty($curuser->info['xingming'])){ 
		m_guide("mchid12_note",'fix');
		die();
	}

	// 限额控制：
	if(!empty($exconfigs['total']) && $exconfigs['total'] <= $ntotal){
		$oA->message("发布<span$style>限额已满</span>,不能再发布信息！<br>您的发布限额为：<span$style>$exconfigs[total]</span> 条");
	}

}

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;
#-----------------

if(!submitcheck('bsubmit')){
	
	#echo "<script type='text/javascript'>ck_edit_config.plugins_disable='08cms_paging_management';</script>";
	//$oA->fields_did[] = 'keywords';
	//$oA->fields_did[] = 'abstract';
	if($isadd){//添加才需要
		$oA->fm_pre_cns();
		$madd_msg = $oA->getmtips(array('check'=>1,'limit'=>array($exconfigs['total'],$ntotal)),'');
		$oA->fm_guide_bm($madd_msg,'fix'); 
		
	}
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("","?action=$action");
	
	//处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_album('pid');
	
	//处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_caid(array('hidden' => 1));
	
	//($coids)，处理分类，$coids：array(3,4,5)
	//$oA->fm_ccids(array(19,31));
	
	//($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array('subject','content'),0);
	$oA->fm_fields(array('keywords','abstract'),1);
	$oA->fm_fields(array('keywords','abstract'),0);
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid')
	//展示多个属性项，管理后台默认为array('createdate')，会员中心默认为array('ucid')
	$oA->fm_params(array());
	
	//处理剩余的有效字段，可以传入排除字段$nos
	//$oA->fm_fields_other(array());
	
	if($isadd){
		//需传入验证码类型，否则默认为'archive'
		$oA->fm_regcode('archive');
	}
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','fix');
	
}else{

	if($isadd){
		//需传入验证码类型，否则默认为'archive'
		$oA->sv_regcode("archive");
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
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();

	//要指定合辑id变量名$pidkey、合辑项目$arid
	//$oA->sv_album('pid3',3);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>

