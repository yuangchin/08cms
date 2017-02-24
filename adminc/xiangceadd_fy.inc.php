<?PHP
$chid = 121;//指定chid
$caid = 623;
cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

$fyimg_count = cls_env::mconfig('fyimg_count');
$fyimg_count = empty($fyimg_count) ? 20 : $fyimg_count;

//要指定合辑id变量名$pidkey、合辑项目$arid
if(!empty($pid)){
    //$_pid = @$pid || $fmdata['pid'];
    $_arc = new cls_arcedit; //商业地产-合辑兼容
    $_arc->set_aid($pid,array('au'=>0,'ch'=>0)); 
    $arid = in_array($_arc->archive['chid'],array(2,3)) ? 38 : 36;//指定合辑项目id
	//echo $arid;
	$row = $db->select('COUNT(*)')->from('#__'.atbl($chid))->where(array("pid$arid"=>$pid))->exec()->fetch();
	//echo "{$row['COUNT(*)']}>=$fyimg_count";
	if($row['COUNT(*)']>=$fyimg_count) cls_message::show("房源图片最多为[$fyimg_count]个，不能再添加。");
	else $fyimg_count = $fyimg_count-$row['COUNT(*)'];
	//print_r($row);
}else{
    $arid = 0;
    $pid = 0;
	cls_message::show("请指定楼盘。");
} 
cls_env::SetG('arid',$arid);
cls_env::SetG('pid',$pid);

$oA = new cls_archive();

//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 会员中心只能编辑本人发布的文档 */
// $oA->allow_self();

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
		$fields['thumb']['max'] = $fyimg_count;
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("","?action=$action&arid=$arid&pid=$pid");
	
	//处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_album('pid');
	
	//处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_caid(array('hidden' =>1));
	
	//($coids)，处理分类，$coids：array(3,4,5)
	$oA->fm_ccids(array());
	
	//$oA->fm_footer();
	
	//($title)，$title手动设置标题
	//$oA->fm_header('详情设置');
	
	//($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array(),0);
	
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
	$oA->fm_guide_bm('','0');
	
}else{
	$fmdata = &$GLOBALS['fmdata'];
	if(!$isadd){ // Edit
		$oA->sv_allow(); //分析权限，添加权限或后台管理权限
		$oA->sv_cns(array()); //类目处理，可传$coids：array(1,2)
		$oA->sv_fields(array()); //字段处理，可传$nos：array('ename1','ename2')
		$oA->sv_update();
		$oA->sv_upload(); //上传处理
		//$oA->sv_album('pid',$arid); //要指定合辑id变量名$pidkey、合辑项目$arid
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
				$oA->sv_upload($_pic); //echo ":$arid,";
				$oA->sv_album('pid',$arid); 
				$oA->sv_static(); 
				unset($oA->arc); // ??? 
			}} //die();
			cnt_imgnum($pid);
			$oA->message("文档添加完成",axaction(6,M_REFERER));
		}else{
			$oA->message('请上传图片！');	
		}
	}
	
}
?>

