<?PHP
$chid = isset($chid) ? max(0,intval($chid)) : 119;
$caid = isset($caid) ? max(0,intval($caid)) : 614;

in_array($chid,array(119,120)) || cls_message::show("请正确指定模型！",M_REFERER);

cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

$oA = new cls_archive();
//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();
switch($chid){

    case 119:
        $type = 'busrent_office';
        break;
    case 120:
        $type = 'busrent_shop';
        break;
    default:
        $type = 'busrent_office';
        break;
}

$isadd && backnav($type,'czfabu');

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

$ispid4 = empty($ispid4) ? 0 : 1; // ispid4相关判断为：经纪公司查看属下经纪人房源相关代码
if($ispid4){ //找到该经纪公司的所有经纪人    
    //当前用户是否有权限查看/修改文档
    hasPermissionCheckHouse($curuser,$oA);
}else{ /* 会员中心只能编辑本人发布的文档 */
	$oA->allow_self(); 
}

/* 设置允许处理的类系，不设则按主表所有类系 */
resetCoids($oA->coids, array(9,19)); 

if($isadd){ 
    //会员要填写了必填的会员信息，若启用手机认证，手机必须通过认证才能发布房源
    publishAfterCheckUserInfo($curuser,$chid);
    
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
		$oA->fm_guide_bm(empty($returnInfo['message'])?'':$returnInfo['message'],'fix'); 
		
	}
	//分析当前会员的权限
	$oA->fm_allow();
	
	$oA->fm_header("$channel[cname] - 基本属性","?action=$action&chid=$chid&ispid4=$ispid4");
	//$oA->fm_album('pid3'); //处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_caid(array('hidden' => 1)); //处理栏目，
    $oA->fm_ulpmc(); // 小区名称 'lpmc'
//     if(in_array($chid,array(2,3))) $oA->fm_chuxing(); // 户型 选择字段
	$oA->fm_rccid1(); // 区域-商圈1,2,
	$oA->fm_rccid3(); // 地铁-站点3,14
	$oA->fm_czumode(); // 租赁方式,付款方式
	$oA->fm_cprice(); // 面积,价格
	$oA->fm_footer();
	
	$oA->fm_header('详情设置');
	$oA->fm_fields(array('subject')); //标题
	$oA->fm_fields(array('address','dt'),0); //地址/地图
	$oA->fm_ctypes(); // 类别/属性(fwjg-房屋结构,zxcd-装修程度,cx-朝向,fl-房龄)
	$oA->fm_clouceng(); // 楼层/楼型,
	$oA->fm_fields(array('louxing')); // 楼型
	$oA->fm_ccids($oA->coids); //其它类系(物业类型)
	
	$skip1 = array('content','thumb'); //图文信息 array(content,thumb) +户型图,小区图
	$skip2 = array('lxdh','xingming'); //联系人,房东信息 //'fdname','fdtel','fdnote'
	$skip3 = array('keywords','abstract'); //关键字，摘要  
	$oA->fm_fields_other(array_merge($skip1,$skip2,$skip3)); //处理剩余的有效字段，可以传入排除字段$nos
	$oA->fm_fields($skip1,0); // 图文信息
	$oA->fm_footer();
	
	$oA->fm_header('其它内容');
	$oA->fm_cfanddong(array('lxdh','xingming'));
	$oA->fm_fields(array('keywords','abstract'),0);
	$oA->fm_fields($skip2,0); //房东信息
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid')
	$oA->fm_params(array('ucid','subjectstr'));
	if($isadd){ //需传入验证码类型，否则默认为'archive'
		$oA->fm_regcode('archive');
		$oA->fm_footer('bsubmit','立即发布');
	}else{
		$oA->fm_footer('bsubmit');
	}
	$oA->fm_fyext(); //扩展的js,房屋配套-全选
	
	
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
	
	$oA->sv_params(array('ucid','subjectstr'));
	
	if($isadd){ 
		if($sendtype){
			//$oA->arc->setend(-1); //上架
			$oA->sv_enddate();
		}else{
			$oA->arc->setend(0);//下架
		}
	}
	
	// 
	$oA->sv_fyext($fmdata,$chid);
	
	//新增字段mchid，存放会员的模型ID，区分是个人发布还是经纪人发布
	$oA->arc->updatefield('mchid',$curuser->info['mchid']);	
	
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

