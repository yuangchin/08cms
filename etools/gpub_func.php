<?php
include_once _08_INCLUDE_PATH."adminm.fun.php";

/*
游客发布(guest_publish) 房源: 
1. 把一些代码从tpl移到这里；避免模版文件中放很多php代码
2. 手机版,网页版共用一些代码
3. 注意：尽管如此，独立页 不能生成静态；
 - init
 - form
 - save
*/

$forward = cls_Parse::Get('_da.forward');
empty($forward) && $forward = M_REFERER;
$aid = cls_Parse::Get('_da.aid');

// 初始化:[网页/手机]版共用
//if(empty($actdo)){
	$actdo = empty($actdo) ? "" : $actdo; //null,save,
	$caid = empty($caid) ? "" : $caid;
	$action = empty($action) ? "chushou" : $action;
	if(!empty($ismob)){ //手机版发布
		if(!in_array($caid,array('3','4'))) cls_message::show('参数错误!');
		$chid = $caid==3 ? 3 : 2;
		$names = array('3'=>'二手房','4'=>'出租'); 
	}else{ 
		$chids = array('chushou'=>3,'chuzu'=>2);
		$caids = array('chushou'=>3,'chuzu'=>4);
		$names = array('chushou'=>'二手房','chuzu'=>'出租');
		if(!in_array($action,array('chushou','chuzu'))) cls_message::show('参数错误!');
		$chid = $chids[$action];
		$caid = $caids[$action];
	}

	cls_env::SetG('chid',$chid);
	cls_env::SetG('caid',$caid);
	
	$isadd = $actdo=='edit' ? 0 : 1;
	if($aid && $ismob){ 
		$curuser = cls_UserMain::CurUser();
		$arc = new cls_arcedit;
		$arc->set_aid($aid,array('au'=>0,'ch'=>1));
		$data = $arc->archive;
		if($data['caid']!=$caid || $data['mid']!=$curuser->info['mid']){
			cls_message::show("参数错误[aid=$aid]! ");
		}
		$actname = '编辑';
		$f2dis = cls_env::mconfig('fcdisabled2');
		$f3dis = cls_env::mconfig('fcdisabled3');
	}else{
		$actname = '发布';	
	}
	
	$mchid = empty($curuser->info['mchid']) ? 0 : $curuser->info['mchid'];
	if(in_array($mchid,array(1,2))){ // 普通会员与经纪人进入会员中心发布
		if(empty($ismob)){
			header("location:{$cms_abs}adminm.php?action={$action}add");
		#}else{
			#cls_message::show('仅限手机版游客发布房源！','');		
		}
	}elseif(!empty($close_gpub)){
		cls_message::show('发布房源，请注册成为普通会员或经纪人！','');	
	}elseif(!empty($mchid)){
		$curuser->info['mid'] = 0;
	}
	
	if ( empty($ck_plugins_enable) )
	{
		$ck_ = new _08House_Archive(); 
		// 定义CK要开启的插件，注：该值与CK插件名称相同，多个用逗号分隔，如果升级该脚本时请继承下去
		$ck_plugins_enable = ""; //{$ck_->__ck_plot_pigure},{$ck_->__ck_size_chart}
		cls_env::SetG('ck_plugins_enable',$ck_plugins_enable);
		unset($ck_);
	}
	
	$oA = new cls_archive();
	$oA->isadd = $isadd;
	//$oA->message("本号码今天发布<span$style>限额已满</span>,不能再发布房源！");

	$oA->read_data();
	resetCoids($oA->coids, array(9,19)); 
	
	/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
	$chid = &$oA->chid;
	$arc = &$oA->arc;
	$channel = &$oA->channel;
	$fields = &$oA->fields;
	$oA->fields['content']['mode'] = 1;
	
	// 
	$count_gpub = cls_env::mconfig('count_gpub'); //游客发布数量
	$count_gpub = empty($count_gpub) ? 3 : $count_gpub;
	
	$fyimg_count = cls_env::mconfig('fyimg_count');
	$fyimg_count = empty($fyimg_count) ? 20 : $fyimg_count;
	cls_env::SetG('fyimg_count',$fyimg_count);
	
	$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH); 
	$fyvalid = empty($exconfigs['fanyuan']['fyvalid']) ? 30 : $exconfigs['fanyuan']['fyvalid']; //租售有效期限
	$sms = new cls_sms();
	
//}

// ======== 手机版:编辑文档 - 保存
if(@$actdo=='edit'){ 

	//类目处理，可传$coids：array(1,2)
	$oA->sv_cns(array());
	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	//
	$oA->sv_params(array('subjectstr'));
	// 
	#$oA->sv_fyext($fmdata,$chid);
	//新增字段mchid，存放会员的模型ID，区分是个人发布还是经纪人发布
	#$oA->arc->updatefield('mchid',$curuser->info['mchid']);	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	//上传处理
	$oA->sv_upload();
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid3',3);
	//自动生成静态
	$oA->sv_static();
	echo $forward;
	cls_message::show('修改成功！',$forward);	
}

// ======== 添加文档 - 保存
if(@$actdo=='save'){  
	
	/*echo "<pre>:::\n";
	print_r($_POST['fmdata']['fythumb']);
	echo "\n\n fmdata:\n";
	print_r($fmdata['fythumb']);
	echo "\n\n _da.xx:\n";
	print_r(cls_env::GetG('fmdata.fythumb'));
	die('xxxx');*/
	
	$smskey = 'arcfypub'; $ckkey = 'smscode_'.$smskey; 
	if(empty($ismob) && $sms->smsEnable($smskey)){
		@$pass = smscode_pass($smskey,$msgcode,$fmdata['lxdh']);
		if(!$pass){
			cls_message::show('手机确认码有误', M_REFERER);
		}
		msetcookie($ckkey, '', -3600);
		$tel_checked = 1;
	}else{ //需传入验证码类型，否则默认为'archive' 
		$oA->sv_regcode("archive_fy");
		$tel_checked = 0;
	}
	
	//*/发布数量限制
	$style = " style='font-weight:bold;color:#F00'";
	$sql = "SELECT count(*) FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON c.aid=a.aid WHERE a.mid='0' AND c.lxdh='$fmdata[lxdh]' AND a.createdate>'".($timestamp-85400)."' ";
	$all_gpub = $db->result_one($sql); $all_gpub = empty($all_gpub) ? 0 : $all_gpub;
	if($all_gpub>=$count_gpub){
		$oA->message("本号码今天发布<span$style>限额已满</span>,不能再发布房源！");
	}//*/
	
	if(!empty($ismob)){ //手机版前台为text,后台为html
		$fmdata = &$GLOBALS[$oA->fmdata];
		$fmdata['content'] = nl2br($fmdata['content']);
	}
	//添加时预处理类目，可传$coids：array(1,2)
	$oA->sv_pre_cns(array());
	
	//分析权限，添加权限或后台管理权限
	//$oA->sv_allow();
	
	//增加一个文档
	//if(!$oA->sv_addarc()){ 
	empty($oA->arc) && $oA->arc = new cls_arcedit;
	$add_aid = $oA->aid = $oA->arc->arcadd($oA->chid,$oA->predata['caid']);
	if(!$oA->aid){ 
		//添加失败处理
		$oA->sv_fail();
	} 
	
	//类目处理，可传$coids：array(1,2)
	$oA->sv_cns(array());
	
	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	//处理多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	$oA->sv_params(array('createdate','enddate',));
	
	$oA->arc->updatefield('enddate',$timestamp+$fyvalid*86400); //处理上架
	// - 游客发布，不要这个
	//$oA->sv_fyext();
	
	// 手机短信认证了默认审核
	$tel_checked && $oA->arc->updatefield('checked',$tel_checked);

	//有效期
	$oA->sv_enddate();

	//新增字段mchid，存放会员的模型ID，区分是个人发布还是经纪人发布
	$oA->arc->updatefield('mchid',@$curuser->info['mchid']); 
	
	$oA->sv_update();
	
	//上传处理
	#$oA->sv_upload();
	
	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid3',3);
	
	//保存图片
	$fmdata['fythumb'] = cls_env::GetG('fmdata.fythumb'); 
	$imgscfg = array('chid'=>121,'caid'=>623,'pid'=>$add_aid,'arid'=>38,'max'=>$fyimg_count);
	$imgscfg['props'] = array(1=>'subject',2=>'lx');
	$mre = $oA->sv_images2arcs($fmdata,'thumb',$imgscfg,'fythumb');
	$db->update('#__'.atbl($chid), array('thumb' => @$mre[1]))->where("aid = $oA->aid")->exec();
	
	$oA->sv_fyext($fmdata,$chid);
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	//$oA->sv_finish();
	
	$curuser = cls_UserMain::CurUser();
	$checked = $curuser->pmautocheck($channel['autocheck']);
	$cmsg = ($checked || $tel_checked) ? "此信息已经由系统<span style='color:green;'>自动审核</span>！" : "<br>此信息<span style='color:red;'>需要管理员审核</span>才能在前台显示"; 

	if(empty($ismob)){
		cls_message::show("{$names[$action]} 添加完成！$cmsg",array('[返回]'=>"?fid=111&action=$action"));
	}else{
		cls_message::show("{$names[$caid]} 添加完成！$cmsg",array('[返回]'=>"?caid=$caid&addno=$addno"));	
	}
	//mclearcookie($ckkey);

}

//手机版-表单项的html
function form_item($cfg,$val=''){
	$a_field = new cls_field;
	$a_field->init($cfg,$val); //$a_field->isadd = 0;
	$varr = $a_field->varr('fmdata','addtitle');
	unset($a_field); //print_r($varr);
	return @$varr['frmcell'];
}

//网页版-游客发布表单
function form_page($oA,$caid,$action,$sms){ 
	
	$channel = &$oA->channel;
	$chid = $oA->chid;
	$fmdata = $oA->fmdata;
	$fields = &$oA->fields;
	$fyimg_count = cls_env::GetG('fyimg_count');
	// 前台控制
	//$oA->fm_header("$channel[cname] - 基本属性","info.php?fid=111&action=$action");
	//trhidden('tel_checked','');
	trhidden('actdo','save');	
	trhidden('fmdata[caid]',$caid);		
	$oA->fm_clpmc(1,1); // 小区名称 'lpmc'
	$oA->fm_chuxing(); // 户型 选择字段
	$oA->fm_rccid1(); // 区域-商圈1,2,
	$oA->fm_rccid3(); // 地铁-站点3,14
	if($chid==2) $oA->fm_czumode(); // 租赁方式,付款方式
	$oA->fm_cprice(); // 面积,价格
	$oA->fm_footer();
	
	$oA->fm_header('详情设置');
	$oA->fm_fields(array('subject')); //标题
	$oA->fm_fields(array('address','dt'),0); //地址/地图
	$oA->fm_ctypes(); // 类别/属性(fwjg-房屋结构,zxcd-装修程度,cx-朝向,fl-房龄)
	$oA->fm_clouceng(); // 楼层/楼型,
	$oA->fm_fields(array('louxing')); // 楼型
	$oA->fm_ccids(array('12','43','44')); //(物业类型)
	$oA->fm_fields(array('fwpt')); // 楼型

	$oA->fm_fields(array('content'));
	// echo form_item($fields['content'],'-aavvbb-'); 用这个修改具体配置
	
	$fythumb = $fields['thumb'];
	$fythumb['cname'] = '房源图片';  
	$fythumb['ename'] = 'fythumb';   
	$fythumb['datatype'] = 'images'; 
	$fythumb['issearch'] = '0'; //图片属性2:0-关闭,1-开启
	//$fythumb['imgComment'] = ''; //title_for_prop2
	$fythumb['min'] = '0';
	$fythumb['max'] = $fyimg_count; 
	$fythumb['guide'] = '';
	$fields['fythumb'] = $fythumb;

	$oA->fm_fields(array('fythumb'));
	
	$oA->fm_footer();
	
	$oA->fm_header('其它内容');
	$oA->fm_fields(array('lxdh','xingming'),0);  //联系人,房东信息
	
	/* 前台控制
	if(!$sms->isClosed()){
		echo '<tr><td width="150px" class="item1"><b><font color="red"> * </font>验证码</b></td><td class="item2">';
		echo '<span id="alert_msgcode" style="color:red"></span>';
		echo '<input  type="text" size="20" id="msgcode" name="msgcode" value="" rule="text" must="1" mode="" regx="/^\s*\d{6}\s*$/" min="" offset="2" max="" rev="确认码"><a href="javascript:" onclick="sendCerCode(\'fmdata[lxdh]\',\'1\');"> 【点击获得确认码】</a>';
		echo '</td></tr>';
	}else{
		$oA->fm_regcode('archive_fy');
	}
	$oA->fm_footer('bsubmit','确定并发布');
	//*/
	$oA->fm_fyext(); //扩展的js,房屋配套-全选
	$oA->fm_jsSetImgtype('fythumb');

}

?>