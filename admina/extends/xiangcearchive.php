<?PHP
$chid = 7;//指定chid
$caid = empty($caid) ? 7 : $caid;
$pid = empty($pid) ? 0 : $pid;

cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

//要指定合辑id变量名$pidkey、合辑项目$arid
if(!empty($pid)){
    //$_pid = @$pid || $fmdata['pid'];
    $_arc = new cls_arcedit; //商业地产-合辑兼容
    $_arc->set_aid($pid,array('au'=>0,'ch'=>0)); 
    $arid = @$_arc->archive['chid']==4 ? 3 : 36;//指定合辑项目id
}else{
    $arid = 0;
} 
cls_env::SetG('arid',$arid);

$oA = new cls_archive();
$isadd = $oA->isadd; /* 0为详情编辑，1为文档添加系 */

$oA->top_head();//文件头部
/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

if(empty($aid)){
	$pchid = empty($_arc->archive['chid']) ? $oA->message('参数错误！') : $_arc->archive['chid'];//4; //添加时-选择所属合辑(楼盘)
}

/* 设置允许处理的类系，不设则按主表所有类系 */
$oA->setvar('coids',array(0));

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;

#-----------------

if(!submitcheck('bsubmit')){
	
	if($isadd){//添加才需要
		$fields['thumb']['datatype'] = 'images';
		$oA->fm_pre_cns(); //添加时预处理类目
	}
	
	$oA->fm_allow(); //分析当前会员的权限
	
	$oA->fm_header("","?entry=extend$extend_str&arid=$arid&pid=$pid"); //($title,$url)，url中可不指定chid或aid
	
	$oA->fm_album('pid'); //处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_caid(array('hidden' => (@$pid=='-1') ? 1 : 0)); //处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_fields(array('subject'));
	//$oA->fm_chuxing(); // 户型 选择字段
	$oA->fm_ccids(array(12)); //($coids)，处理分类，$coids：array(3,4,5)
	$oA->fm_fields(array('thumb'),1); //($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array('thumb'));
	//$oA->fm_fields_other(); //处理剩余的有效字段，可以传入排除字段$nos
	
	$oA->fm_footer('bsubmit');
	
	$oA->fm_guide_bm('','0');
	
}else{	
	if(!$isadd){ // Edit
		$oA->sv_allow(); //分析权限，添加权限或后台管理权限
		$oA->sv_cns(array()); //类目处理，可传$coids：array(1,2)
		$oA->sv_fields(array()); //字段处理，可传$nos：array('ename1','ename2')
		$oA->sv_update();
		$oA->sv_upload(); //上传处理
		$oA->sv_album('pid'); //要指定合辑id变量名$pidkey、合辑项目$arid
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
			}} 
			$oA->message("文档添加完成",axaction(6,M_REFERER));
		}else{
			$oA->message('错误！');	
		}
	}
}
?>
