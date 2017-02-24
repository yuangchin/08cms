<?PHP
$chid = 11;//指定chid
$caid = 11;
$arid = empty($arid) ? 3 : max(3,intval($arid));//接受外部传arid，但要做好限制

cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);
cls_env::SetG('arid',$arid);

$oA = new cls_archive();
$isadd = $oA->isadd; /* 0为详情编辑，1为文档添加系 */

$oA->top_head();//文件头部
/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

$pchid = 4; //添加时-选择所属合辑(楼盘)

/* 设置允许处理的类系，不设则按主表所有类系 */
$oA->setvar('coids',array(1,12));

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;

#-----------------
if(!submitcheck('bsubmit')){
	
	if($isadd){//添加才需要
		$oA->fm_pre_cns(); //添加时预处理类目
		$todiqu=$oA->fm_find_album();
		$oA->predata['ccid1']=$todiqu['ccid1'];
	}
	
	$oA->fm_allow(); //分析当前会员的权限	
	$oA->fm_header("","?entry=extend$extend_str&arid=$arid"); //($title,$url)，url中可不指定chid或aid
    $oA->fm_ccids(array(1));
	$oA->fm_album('pid'); //处理合辑，请指定合辑id变量名，留空默认为pid
    $oA->fm_caid(array('hidden' => 1)); //处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_fields(array('thumb','tujis'),0);
	$oA->fm_fields(array('subject'));
	$oA->fm_chuxing(array(),1); // 户型 选择字段
	$oA->fm_ccids(array(12)); //($coids)，处理分类，$coids：array(3,4,5)
	$oA->fm_fields(array('abstract'),1); //($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array('abstract'));
	$oA->fm_fields(array(),0); //处理剩余的有效字段，可以传入排除字段$nos
	
	$oA->fm_footer('bsubmit');
	
	$oA->fm_guide_bm('','0');
	
}else{	

	if($isadd){
		$oA->sv_regcode('archive');
		$oA->sv_pre_cns(array());
		
	}
	$oA->sv_allow();
	if($isadd){
		if(!$oA->sv_addarc()){
			$oA->sv_fail();
		}
	}
	$oA->sv_cns(array());
	$oA->sv_fields(array());
	$oA->sv_params(array());
	$oA->sv_param('arctpls');
	$oA->sv_update();
	$oA->sv_upload();
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid',$arid);
	$oA->sv_static();
	$oA->sv_finish();

}
?>
