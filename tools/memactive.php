<?php
/*
** 本脚本用于会员激活操作
*/
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT.'include/adminm.fun.php';
$forward = mhtmlspecialchars(empty($forward) ? M_REFERER : $forward);
$action = mhtmlspecialchars(empty($action) ? 'sendemail' : $action);

if($action == 'sendemail'){//重新发送会员激活的电子邮件
	_header();
	$mname = empty($mname) ? '' : $mname;
	$email = empty($email) || !cls_string::isEmail($email) ? '' : $email;
	if(empty($mname) || empty($email) || !cls_string::isEmail($email)){//手动添加资料发送激活邮件
		tabheader('重发激活邮件','newemail',"?action=$action&forward=$forward",2,0,1);
		trbasic('会员名称','mname',$mname);
		trbasic('邮件地址','email',$email);//可以是非注册邮箱
		tabfooter('bsubmit');
		mexit();
	}else{
		if(!($info = $db->fetch_one("SELECT mid,mname FROM {$tblprefix}members WHERE mname='$mname' AND checked=2"))) cls_message::show('指定会员不存在或不需要邮件激活');
		cls_userinfo::SendActiveEmail(array('mid' => $info['mid'],'mname' => $info['mname'],'email' => $email));
		cls_message::show('会员激活邮件已发送到您的邮箱，请进入邮箱激活', $forward);
	}
}elseif($action == 'emailactive'){//通过点击邮件中的url进行新注册的会员的激活处理
	_header();
	if(!($mid = max(0,intval(@$mid))) || !($confirmid = trim(@$confirmid))) cls_message::show('无效参数');
	if(!($info = $db->fetch_one("SELECT m.mid,s.confirmstr FROM {$tblprefix}members m,{$tblprefix}members_sub s WHERE m.mid='$mid' AND s.mid='$mid' AND m.checked=2"))) cls_message::show('指定会员不存在或不需要邮件激活');
	if(!$info['confirmstr']) cls_message::show('激活确认码不正确');
	
	list($_dateline,$_type,$_confirmid) = explode("\t", $info['confirmstr']);
	if($_type == 2 && $_confirmid == $confirmid){
		$db->query("UPDATE {$tblprefix}members SET checked=1 WHERE mid='$mid'");
		$db->query("UPDATE {$tblprefix}members_sub SET confirmstr='' WHERE mid='$mid'");
		cls_message::show('会员激活成功',$cms_abs);
	}else cls_message::show('激活确认码不正确');
	
}elseif($action == 'memcert'){//邮箱认证
	_header();
	$memcerts = cls_cache::Read('memcerts');
	(empty($crid) || empty($confirm) || !($record = $db->fetch_one("SELECT mcid,certdata FROM {$tblprefix}mcrecords WHERE crid='$crid' AND checktime=0 LIMIT 0,1"))) && cls_message::show('无效的请求');
	$certdata = unserialize($record['certdata']);
	if(!($k = $memcerts[0][$record['mcid']]['email']) || $certdata['codes'][$k]['v'] != $confirm || $certdata['codes'][$k]['e'] >= 3){
		$k && $certdata['codes'][$k]['e']++ < 3 && $db->query("UPDATE {$tblprefix}mcrecords SET certdata='".addslashes(serialize($certdata))."' WHERE crid=$crid");
		cls_message::show($k && $certdata['codes'][$k]['e'] >= 3 ? '错误次数太多' : '无效的请求');
	}else{
		if(empty($certdata['flags'][$k])){
			$certdata['flags'][$k] = 1;
			$db->query("UPDATE {$tblprefix}mcrecords SET certdata='".addslashes(serialize($certdata))."' WHERE crid=$crid");
		}
		cls_message::show('邮箱认证成功');
	}
}