<?PHP
$chid = 11;//指定chid
$caid = 11;
$arid = 3;
cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);
cls_env::SetG('arid',$arid);
#-----------------

$oA = new cls_archive();

//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 会员中心只能编辑本人发布的文档 */
$oA->allow_self();

/* 设置表单数据数组名，不设则默认为fmdata */
//$oA->setvar('fmdata','archivenew');

/* 设置允许处理的类系，不设则按主表所有类系 */
//$oA->setvar('coids',array(2,3,4));

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
		$fields['thumb']['datatype'] = 'images';
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("","?action=$action");
	
	$oA->fm_album('pid'); //处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_caid(array('hidden' => 1)); //处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_fields(array('subject'));
	$oA->fm_chuxing(); // 户型 选择字段
	$oA->fm_ccids(array(12)); //($coids)，处理分类，$coids：array(3,4,5)
	$oA->fm_fields(array('thumb'),1); //($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array('thumb'));
	$oA->fm_fields(array(),0); //处理剩余的有效字段，可以传入排除字段$nos
	
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
	$fmdata = &$GLOBALS['fmdata'];
	if(!$isadd){ // Edit
		$oA->sv_allow(); //分析权限，添加权限或后台管理权限
		$oA->sv_cns(array()); //类目处理，可传$coids：array(1,2)
		$oA->sv_fields(array()); //字段处理，可传$nos：array('ename1','ename2')
		$oA->sv_update();
		$oA->sv_upload(); //上传处理
		$oA->sv_album('pid',$arid); //要指定合辑id变量名$pidkey、合辑项目$arid
		$oA->sv_static(); //自动生成静态
		$oA->sv_finish(); //结束时需要的事务，包括自动生成静态，操作记录及成功提示
	}else{ // add
		$oA->sv_regcode('archive'); 
		$oA->sv_pre_cns(array()); 
		$oA->sv_allow(); 
		if(!empty($fmdata['thumb'])){
			$_s = str_replace("\r","\n",$fmdata['thumb']);
			$_a = explode("\n",$fmdata['thumb']);
			foreach($_a as $fmdata['thumb']){
			if(!empty($fmdata['thumb'])){
				if(!$oA->sv_addarc()){
					$_msg[] = $oA->sv_fail(1); 
				}
				$_msg[] = $oA->sv_cns(array(),1); 
				$_msg[] = $oA->sv_fields(array(),1); 
				$_pic = str_replace(array('##'," ","\t","\r","\n"),'',$fmdata['thumb']);
				//echo "($_pic)".strlen($_pic);
				$oA->arc->updatefield('thumb',$_pic,$fields['thumb']['tbl']);
				$oA->sv_update();
				$oA->sv_upload($_pic); 
				$oA->sv_album('pid',$arid); 
				$oA->sv_static(); 
				unset($oA->arc); // ??? 
			}} //die();
			$oA->message("文档添加完成",axaction(6,M_REFERER));
		}else{
			$oA->message('错误！');	
		}
	}
	
}
?>

