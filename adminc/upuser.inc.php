<?php
!defined('M_COM') && exit('No Permission');

$currencys = cls_cache::Read('currencys');
$mctypes = cls_cache::Read('mctypes');

$type = empty($type) ? 'mchid2' : $type;
$mchid = str_replace("mchid","",$type); 
$ochid = $curuser->info['mchid'];
$atype = array(
	'2'=>'经纪人',
	'3'=>'经纪公司',
	'11'=>'装修公司',
	'12'=>'品牌商家'
);
$lnktype = ''; $msgnow = '';
foreach($atype as $k=>$v){
	if($ochid == $k) cls_message::show("您已经是$v, 不能升级");
	$lnktype .= " | <a href='?action=upuser&type=mchid$k'>$v</a>";
	if($type=="mchid$k") $msgnow = $v;
}

tabheader("<div style='width:420px; float:right'>切换类型:$lnktype</div>升级为$msgnow",'','');
trbasic('说明：','','普通会员，可以转为 经纪人，装修公司，品牌商家，其它会员不可以相互转换；点右上切换升级类型。','');
tabfooter();

$mchannel = cls_cache::Read('mchannel',$mchid);
$mfields = cls_cache::Read('mfields',$mchid);
// 问答专家-处理字段
$mfexp = array('dantu','ming','danwei','quaere','blacklist');
foreach($mfexp as $k){//后台架构字段
	unset($mfields[$k]);
}
// 排除会员认证字段
foreach($mctypes as $k => $v){
	if(strstr(",$v[mchids],",",$mchid,")){ //允许的会员模型
		unset($mfields[$v['field']]);
	}
}

$autocheck = $mchannel['autocheck'] == 1 ? 1 : 0;
$row = $db->fetch_one("SELECT * FROM {$tblprefix}mtrans WHERE mid='$memberid' AND checked='0'");
//判断
if(empty($row['contentarr'])){
	$contentarr = array();
	$createdate = $timestamp;
}else{
	$contentarr = unserialize($row['contentarr']);
	$createdate = $row['createdate'];
}
$contentarr = empty($row['contentarr']) ? array() : unserialize($row['contentarr']);
unset($row['contentarr']);

$curuser = cls_UserMain::CurUser(); 
$lxdh = $curuser->info['lxdh'];

if(!submitcheck('bsubmit')){
	tabheader("升级说明",$action,"?action=$action",2,1,1);
	trbasic('申请时间','',date("Y-m-d H:m",$createdate),'');
	trbasic('附加说明','fmdata[remark]',empty($row['remark']) ? '' : $row['remark'],'textarea');
	empty($row['reply']) || trbasic('管理员回复','',$row['reply'],'textarea',array('guide' => '不可更改'));
	$autocheck && trbasic('升级说明','','<font color="#2255DD">提交后自动升级为'.$msgnow."</font>",'');
	tabfooter();
	
	tabheader('详细设置');
	$a_field = new cls_field;

	foreach($mfields as $k => $field){
		if(!$field['issystem']){
			empty($contentarr[$k]) || $contentarr[$k] = stripslashes($contentarr[$k]);
			$a_field->init($field,empty($contentarr[$k]) ? (empty($curuser->info[$k]) ? '' : $curuser->info[$k]) : $contentarr[$k]);
			$a_field->trfield('fddata');
		}
	}

	trhidden('type',$type);
	tabfooter('bsubmit');
	echo "<script type='text/javascript'>_08cms_validator.init('ajax','fmdata[lxdh]',{url:'{$cms_abs}"._08_Http_Request::uri2MVC("ajax=checkUserPhone&old=$lxdh&val=%1")."'});</script>";

}else{
	$c_upload = new cls_upload;	
	$a_field = new cls_field;
	foreach($mfields as $k => $v){
		if(!$v['issystem'] && isset($fddata[$k])){
			empty($contentarr[$k]) || $contentarr[$k] = stripslashes($contentarr[$k]);
			$a_field->init($v,empty($contentarr[$k]) ? (empty($curuser->info[$k]) ? '' : $curuser->info[$k]) : $contentarr[$k]);
			$fddata[$k] = $a_field->deal('fddata','mcmessage',M_REFERER);
			if($autocheck){
				@$curuser->updatefield($k,$fddata[$k],$v['tbl']);
				if($arr = multi_val_arr($fddata[$k],$v)) foreach($arr as $x => $y) $curuser->updatefield($k.'_'.$x,$y,$v['tbl']);
			}
		}
	}
	unset($a_field);
	
	if($autocheck){
		$db->query("DELETE FROM {$tblprefix}members_$ochid WHERE mid='$memberid'");
		$db->query("INSERT INTO {$tblprefix}members_$mchid SET mid='$memberid'");
		if($mchid == 2){//如果是普通会员升级为经纪人，则二手房、出租房源中对应的mid的mchid字段也要修改成2
			$db->query("UPDATE {$tblprefix}".atbl(2)." SET mchid = '2' WHERE mid = '$memberid'");
			$db->query("UPDATE {$tblprefix}".atbl(3)." SET mchid = '2' WHERE mid = '$memberid'");
		}
		$curuser->updatefield('mchid',$mchid);
		$crids = array();foreach($currencys as $k => $v) $v['available'] && $v['initial'] && $crids[$k] = $v['initial'];
		$crids && $curuser->updatecrids($crids,0,'会员注册初始积分。');
		$curuser->updatefield('checked',1);
		$curuser->nogroupbymchid();//模型变更以后清理不需要的组定义
		$curuser->groupinit();			
		$curuser->updatefield('mtcid',($mtcid = array_shift(array_keys($curuser->mtcidsarr()))) ? $mtcid : 0);
		$curuser->autoletter();
		$curuser->updatedb();
		if($row){
			$db->query("UPDATE {$tblprefix}mtrans SET toid='$mchid',fromid='$ochid',contentarr='',remark='',reply='',checked='1' WHERE mid='$memberid' AND checked='0'");
		}else{
			$db->query("INSERT INTO {$tblprefix}mtrans SET mid='$memberid',mname='".$curuser->info['mname']."',toid='$mchid',fromid='$ochid',contentarr='',remark='',checked='1',createdate='$timestamp'");
		}
	}else{
		$fmdata['remark'] = trim($fmdata['remark']);
		$fddata = empty($fddata) ? '' : addslashes(serialize($fddata));
		if($row){
			$db->query("UPDATE {$tblprefix}mtrans SET fromid='$ochid',toid='$mchid',contentarr='$fddata',remark='$fmdata[remark]' WHERE mid='$memberid' AND checked='0'");
		}else{
			$db->query("INSERT INTO {$tblprefix}mtrans SET mid='$memberid',mname='".$curuser->info['mname']."',fromid='$ochid',toid='$mchid',contentarr='$fddata',remark='$fmdata[remark]',checked='0',createdate='$timestamp'");
		}
	}
	$c_upload->closure(1,$memberid,'members');
	$c_upload->saveuptotal(1);
	($autocheck && $mchid==2) ? cls_message::show('会员升级,您可以继续升级为VIP会员',"?action=gaoji") : cls_message::show('会员升级申请成功提交，请等待管理员审核',M_REFERER);
}

?>

