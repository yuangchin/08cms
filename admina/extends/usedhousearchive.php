<?PHP
$chid = 3;
$caid = 3;
cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

$oA = new _08House_Archive();

# CK插件配置，如果升级该脚本时请继承下去
$ck_plugins_enable = "{$oA->__ck_plot_pigure},{$oA->__ck_size_chart},{$oA->__ck_paging_management}";

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
	$oA->fm_header("$channel[cname] - 基本属性","?entry=extend$extend_str");
	$oA->fm_ccids(array('9','19'));
	//$oA->fm_album('pid3'); //处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_caid(array('hidden' => 1)); //处理栏目，
	
	$oA->fm_clpmc(); // 小区名称 'lpmc'
	$oA->fm_chuxing(); // 户型 选择字段
	$oA->fm_rccid1(); // 区域-商圈1,2,
	$oA->fm_rccid3(); // 地铁-站点3,14
	//$oA->fm_czumode(); // 租赁方式,付款方式
	$oA->fm_cprice(); // 面积,价格
	$oA->fm_footer();
	
	$oA->fm_header('详情');	
	$oA->fm_fields(array('subject')); //标题
	$oA->fm_fields(array('address','dt'),0); //地址/地图
	$oA->fm_ctypes(); // 类别/属性(fwjg-房屋结构,zxcd-装修程度,cx-朝向,fl-房龄)
	$oA->fm_clouceng(); // 楼层/楼型,
	$oA->fm_fields(array('louxing')); // 楼型
	$oA->fm_ccids($oA->coids); //其它类系(物业类型)
	
	$skip1 = array('content','thumb'); //图文信息 array(content,thumb) +户型图,小区图
	$skip2 = array('lxdh','xingming','fdname','fdtel','fdnote'); //联系人,房东信息
	$skip3 = array('keywords','abstract'); //关键字，摘要  
	$oA->fm_fields_other(array_merge($skip1,$skip2,$skip3)); //处理剩余的有效字段，可以传入排除字段$nos
	$oA->fm_fields($skip1,0); // 图文信息
	$oA->fm_footer();
	
	$oA->fm_header('联系方式');
	$oA->fm_cfanddong(array('lxdh','xingming'));
	$oA->fm_fields($skip2,0); 
	$oA->fm_footer();
	
	$oA->fm_header('扩展设置','',array('hidden'=>1));	
	$oA->fm_fields($skip3,0);
	$oA->fm_params(array('clicks','createdate','arctpls','customurl','subjectstr'));

	$oA->fm_footer('bsubmit');
	$oA->fm_fyext(); //扩展的js,房屋配套-全选
	
	$oA->fm_guide_bm('','0');
	
}else{

	if($isadd){
		$oA->sv_regcode('archive');
		$oA->sv_pre_cns(array());
	}
	$oA->sv_allow(); //分析权限，添加权限或后台管理权限
	if($isadd){
		if(!$oA->sv_addarc()){
			$oA->sv_fail();
		}
	}
	
	//类目处理，可传$coids：array(1,2)
	$oA->sv_cns(array());
	
	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	//处理多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	$oA->sv_params(array('clicks','createdate','arctpls','customurl','subjectstr'));
	$oA->sv_fyext($fmdata);
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
	
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid3',3);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>
