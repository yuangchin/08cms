<?PHP
$chid = 4;//指定chid
cls_env::SetG('chid',$chid);
#-----------------

$oA = new cls_archive();

/* 0为详情编辑，1为文档添加系 */
$isadd = $oA->isadd;

$oA->top_head();//文件头部


/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 设置表单数据数组名，不设则默认为fmdata */
//$oA->setvar('fmdata','archivenew');

/* 设置允许处理的类系，不设则按主表所有类系 */
//$oA->setvar('coids',array(2,3,4));

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel; 

$skip = 'onlyold'; //忽略onlyold,onlynew
$ftemp = &$oA->fields; //cls_cache::Read('fields',$chid);
$fields = array(); 
foreach($ftemp as $k => $field){
	if($field['cname']){
		//$field['cname'] = str_replace('楼盘','小区',$field['cname']);
		if(!isset($field[$skip])) $fields[$k] = $field; 
	}
} 
unset($fields['jgjj'],$fields['jdjj']);
if(!$isadd) unset($fields['dj'],$fields['bdsm']);
$oA->fields = $fields;
$oA->fields_did[] = 'stpic';
#-----------------

if(!submitcheck('bsubmit')){
	
	if($isadd){//添加才需要
		//添加时预处理类目
		$oA->fm_pre_cns();
	}
	//分析当前会员的权限
	$oA->fm_allow();
	$a1 = array('thumb','loupanlogo','lphf','lppmtu'); //图
	$a2 = array('keywords','abstract');
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("$channel[cname] - 基本属性","?entry=extend$extend_str");
	$oA->fm_album('pid'); //处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_caid(array('hidden'=>1)); //处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_fields(array('subject')); //标题
	$oA->fm_fields($a2);
	$isadd && $oA->fm_lpExist(); //js
	$oA->fm_ccids(array('12','18')); //物业类型,销售状态
	$oA->fm_fields(array('kpsj','kprq','jfrq','dj','bdsm',)); //开盘说明,开盘日期,交房日期,均价
    !$oA->isadd && $oA->fm_dj_edit_url();//价格编辑的跳转链接
    $oA->fm_kp_info();//开盘时间的设定自动赋值给开盘说明的JS
	$oA->fm_fields(array('zxcd','lcs','tslp')); //装修程度,楼层,特色楼盘,
	$oA->fm_ccids(array('41')); //楼盘置顶
	$oA->fm_footer();
	
	$oA->fm_header("$channel[cname] - 地理信息");
	$oA->fm_rccid1(); // 1,2,区域
	$oA->fm_rccid3(); // 3,14,地铁
	$oA->fm_fields(array('hxs','address','jtxl','dt','pano')); //环线,楼盘地址,交通线路,地图,街景,街景场景ID
	$oA->fm_footer();
	
	$oA->fm_header("$channel[cname] - 服务信息");
        $oA->fm_fields(array('tel','extcode','sldz')); //销售电话,分机号,售楼地址
	$oA->isadd && $oA->fm_webcallExist(); //检测400js

	$oA->fm_fields(array('wyf','wygs','wydz')); //物业费,物业公司,物业地址
	$oA->fm_relalbum('6',13, '楼盘开发商');
	$oA->fm_fields(array('xkzh','ltbk','qtbz')); //许可证号,论坛板块,其他备注
	$oA->fm_footer();
	
	
	$oA->fm_header("$channel[cname] - 其它属性");
	$oA->fm_ccids(); //显示其它类系
	$oA->fm_fields(array_merge($a1,$a2,array('content')),1); //排除项目,放后面
	$oA->fm_footer();
	
	$oA->fm_header('图文说明');
	$oA->fm_relalbum('5',12, '楼盘视频');
	$oA->fm_fields_other(array_merge($a2,array('content'))); //图
	$oA->fm_fields(array('content'));
	$oA->fm_footer();
	
	$oA->fm_header('扩展设置','',array('hidden'=>1));
	
	$oA->fm_params(array('createdate','clicks','jumpurl','relate_ids','subjectstr'));
	$oA->fm_customurl();//楼盘文档页静态保存格式
	$oA->fm_param('arctpls',array('addnums'=>array(1,2,3,4,5,6,11)));	
	$oA->fm_footer('bsubmit');
	
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
	$oA->sv_params(array('createdate','clicks','jumpurl','relate_ids','subjectstr'));
	$oA->sv_param('arctpls');
	$oA->sv_customurl();
	
	//执行自动操作及更新以上变更    
	isset($pid5) && $arc->updatefield('pid5',$pid5);
	isset($pid6) && $arc->updatefield('pid6',$pid6);
    //开发商名称
    isset($fmdata['kfsname']) && $arc->updatefield('kfsname',$fmdata['kfsname']);
	$isadd && $arc->updatefield('leixing',1,"archives_$chid");
    $oA->arc->archive['ccid18']=='198' && $oA->arc->archive['vieworder']<=1000 && $arc->updatefield('vieworder',$oA->arc->archive['vieworder']+999);
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid',0);
	
	//自动把范围内的周边合辑到楼盘，该范围在后台房产参数里面设置
	if($oA->isadd){ 
		$dj = max(0,floatval($fmdata['dj']));
		if($dj){
			$sql = "highest='$dj',average='$dj',lowest='$dj',message='初始均价'";
			$sql .= ",aid=".$oA->aid.",isnew=1,createdate='$timestamp'";
			$sql = "INSERT INTO {$tblprefix}housesrecords SET $sql";
			$db->query($sql);
		}
		$oA->sv_zhoubian($fmdata,$oA->aid,$chid);
	}
	
	//自动生成静态,结束时需要的事务
	$oA->sv_static();
	$oA->sv_finish();
}
?>
