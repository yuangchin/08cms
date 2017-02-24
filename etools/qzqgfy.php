<?php
include_once M_ROOT."./include/adminm.fun.php";
$tplurl = "{$cms_abs}template/{$templatedir}/"; 

$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.rawurlencode($forward);
$action = empty($action) ? "qiuzu" : $action;
$chids = array('qiugou'=>10,'qiuzu'=>9);
$caids = array('qiugou'=>10,'qiuzu'=>9);
$names = array('qiugou'=>'求购','qiuzu'=>'求租');


if(!in_array($action,array('qiugou','qiuzu'))) cls_Parse::Message('参数错误!');
$chid = $chids[$action];
$caid = $caids[$action];
cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);

$mchid = empty($curuser->info['mchid']) ? 0 : $curuser->info['mchid'];
if(in_array($mchid,array(1,2))){ // 普通会员与经纪人进入会员中心发布
	header("location:{$cms_abs}adminm.php?action=xuqiuarchive&chid=$chids[$action]");
}elseif(!empty($close_gpub)){
	cls_Parse::Message('发布需求，请注册成为普通会员或经纪人！','');	
}elseif(!empty($mchid)){
	$curuser->info['mid'] = 0;
}

$oA = new cls_archive();
$isadd = $oA->isadd = 1;
$oA->read_data();

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;

$sms = new cls_sms();


if(submitcheck('bsubmit')){
	
	$smskey = 'arcxqpub'; $ckkey = 'smscode_'.$smskey; 
	if($sms->smsEnable($smskey)){
		@$pass = smscode_pass($smskey,$msgcode,$fmdata['lxdh']); 
		if(!$pass){
			cls_message::show('手机确认码有误', M_REFERER);
		}
		msetcookie($ckkey, '', -3600);
		$tel_checked = 1;
	}else{ //需传入验证码类型，否则默认为'archive' 
		$oA->sv_regcode("archive_xq");
		$tel_checked = 0;
	}
	
	//发布数量限制
	$style = " style='font-weight:bold;color:#F00'";
	$count_gpub = empty($count_gpub) ? 3 : $count_gpub;
	$validday = empty($validday) ? 30 : $validday;
	$sql = "SELECT count(*) FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON c.aid=a.aid WHERE a.mid='0' AND c.lxdh='$fmdata[lxdh]' AND a.createdate>'".($timestamp-85400)."' ";
	$all_gpub = $db->result_one($sql); $all_gpub = empty($all_gpub) ? 0 : $all_gpub;
	if($all_gpub>=$count_gpub){
		$oA->message("本号码今天发布<span$style>限额已满</span>,不能再发布需求！");
	}
	
	//添加时预处理类目，可传$coids：array(1,2)
	$oA->sv_pre_cns(array());
   
	//增加一个文档
	//if(!$oA->sv_addarc()){ 
	empty($oA->arc) && $oA->arc = new cls_arcedit;
	$oA->aid = $oA->arc->arcadd($oA->chid,$oA->predata['caid']);
	if(!$oA->aid){ 
		//添加失败处理
		$oA->sv_fail();
	} 
//	die();
	//类目处理，可传$coids：array(1,2)
	$oA->sv_cns(array());

	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	//处理多个属性项，管理后台默认为array('createdate')，会员中心默认为array('ucid')
	$oA->sv_params(array());			

	// 手机短信认证了默认审核
	$tel_checked && $oA->arc->updatefield('checked',$tel_checked);
	
	$oA->sv_enddate();
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();

	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid3',3);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	//$oA->sv_finish();
	
	$curuser = cls_UserMain::CurUser();
	$checked = $curuser->pmautocheck($channel['autocheck']);
	$cmsg = ($checked || $tel_checked) ? "此信息已经由系统<span style='color:green;'>自动审核</span>！" : "<br>此信息<span style='color:red;'>需要管理员审核</span>才能在前台显示"; 
	//_tmp_sendok($cmsg,$action,$cms_abs,$tplurl);	
	if(empty($ismob)){
		cls_message::show("{$names[$action]} 添加完成！$cmsg",array('[返回]'=>"?fid=$fid&action=$action"));
	}else{
		cls_message::show("{$names[$caid]} 添加完成！$cmsg",array('[返回]'=>"?caid=$caid&addno=$addno"));	
	}
}





function addqzqg($oA,$caid,$chid,$action,$sms,$channel){		
		//$oA->fm_header("$channel[cname] - 发布","?fid=112&action=$action&chid=$chid");		
		trhidden('fmdata[caid]',$caid);
		$oA->fm_fields(array('subject'),0);
		$oA->fm_ccids(array(1)); 
	
		$oA->fm_fields(array('mj','zj','jtyq'));
		$oA->fm_fields(array('lxdh','xingming'));
		$oA->fm_fields(array(),0);	
		
		/* 前台控制
		if(!$sms->isClosed()){        
    		echo '<tr><td width="150px" class="item1"><b><font color="red"> * </font>手机确认码</b></td><td class="item2">';
    		echo '<span id="alert_msgcode" style="color:red"></span>';
    		echo '<input  type="text" size="20" id="msgcode" name="msgcode" value="" rule="text" must="1" mode="" regx="/^\s*\d{6}\s*$/" min="" offset="2" max="" rev="确认码"><a href="javascript:" onclick="sendCerCode(\'fmdata[lxdh]\',\'1\');"> 【点击获得确认码】</a>';
    		echo '</td></tr>';
		}else{
			$oA->fm_regcode('archive_xq');
		}*/
	
		//$oA->fm_footer('bsubmit');	
}
?>