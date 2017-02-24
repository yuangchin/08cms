<?php
!defined('M_COM') && exit('No Permission');
empty($webcall_enable) && cls_message::show('请先启用400电话');
if(empty($webcallpmid) || !$curuser->pmbypmid($webcallpmid)) cls_message::show('您没有400电话的设置权限。');

$id = empty($id) ? 0 : max(0,intval($id));
$page = !empty($page) ? max(1, intval($page)) : 1;
$wheresql = "WHERE a.mid='$memberid'";
$fromsql = "FROM {$tblprefix}webcall a";
$red_doc = "<font color='red'> * </font>";

if(!isset($option)){
	if(!submitcheck('bsubmit')){
		$id && $wheresql .= " AND a.id='$id'";
		$r = $db->fetch_one("SELECT a.* $fromsql $wheresql");
		empty($r) && cls_message::show('请先申请400分机号码',axaction(2, "?action=$action&option=apply"));
		tabheader("400电话设置".(empty($webcall_small_admin) ? '' : "&nbsp;>><a target=\"_blank\" href=\"$webcall_small_admin)\" >400分机管理</a>"),'webcalladd',"?action=$action&id=$id",2,1,1);
		switch($r['state']){
			case -1:
				$statestr = '未通过';
				break;
			case 0:
				$statestr = '审核中';
				break;
			case 1:
				$statestr = '已审核';
				break;
		}
		$createdatestr = $r['createdate'] ? date('Y-m-d',$r['createdate']) : '-';
		$r['state']!=0 && $checkdatestr = $r['checkdate'] ? date('Y-m-d',$r['checkdate']) : '-';

		trhidden('fmdata[id]', $r['id']);
		trhidden('fmdata[state]', $r['state']);
		$r['state']==1 && trbasic('400电话号码','',$webcall_big.'-'.$r['extcode'],'');
		$r['state']==1 && trbasic('免费拨打url','',$r['webcallurl'],'');
		trbasic($red_doc.'企业名称','fmdata[suppliername]',$r['suppliername'],'text',array('validate' => makesubmitstr('fmdata[suppliername]',1,0,'',20,'text')));
		trbasic($red_doc.'企业地址','fmdata[address]',$r['address'],'text',array('validate' => makesubmitstr('fmdata[address]',1,0,'',100,'text')));
		trbasic('邮编','fmdata[postcode]',$r['postcode'],'text',array('validate' => makesubmitstr('fmdata[postcode]',0,'int',6,6,'text')));
		trbasic('管理帐号','fmdata[username]',$r['username'],'');
		trbasic($red_doc.'联系人','fmdata[contactman]',$r['contactman'],'text',array('validate' => makesubmitstr('fmdata[contactman]',1,0,'',20,'text')));
		trbasic('性别','',makeradio('fmdata[sex]',array(1 => '男',0 => '女'),$r['sex']),'');
		trbasic($red_doc.'身份证','fmdata[contactidcard]',$r['contactidcard'],'text',array('validate' => makesubmitstr('fmdata[contactidcard]',1,0,'',20,'text','/^([1-9][0-9]{13,16}[0-9A-Z])$/')));
		trbasic($red_doc.'联系电话','fmdata[phone]',$r['phone'],'text',array('validate' => makesubmitstr('fmdata[phone]',1,0,'',20,'text')));
		trbasic('联系手机','fmdata[mobilephone]',$r['mobilephone'],'text',array('validate' => makesubmitstr('fmdata[mobilephone]',0,'int',11,11,'text')));
		trbasic($red_doc.'电子邮箱','fmdata[contactmail]',$r['contactmail'],'text',array('validate' => makesubmitstr('fmdata[contactmail]',1,0,'',100,'text','/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/')));
		trbasic('法人','fmdata[artiperson]',$r['artiperson'],'text',array('validate' => makesubmitstr('fmdata[artiperson]',0,0,'',50,'text')));
		trbasic('营业执照号码','fmdata[licence]',$r['licence'],'text',array('validate' => makesubmitstr('fmdata[licence]',0,0,'',20,'text')));
		trbasic('税务登记号','fmdata[taxnumber]',$r['taxnumber'],'text',array('validate' => makesubmitstr('fmdata[taxnumber]',0,0,'',20,'text')));
		trbasic('状态','',$statestr,'');
		$r['state']==-1 && trbasic('备注','',$r['remark'],'');
		trbasic('创建日期','',$createdatestr,'');
		$r['state']!=0 && trbasic('审核日期','',$checkdatestr,'');
		$r['state']!=1 && tabfooter('bsubmit','提交');
		$r['state']==1 && tabfooter();
	} else {
		//不能修改 已审核 的记录
		$fmdata['state']==1 && cls_message::show('不能修改状态为已审核的记录');

		//!is_numeric($fmdata['postcode']) && cls_message::show('邮编必须是数字',axaction(2,M_REFERER));
		//!is_numeric($fmdata['contactidcard']) && cls_message::show('身份证必须是数字',axaction(2,M_REFERER));
		if(!preg_match('/([1-9][0-9]{13,16}[0-9A-Z])/',$fmdata['contactidcard'])) cls_message::show('身份证号码不合法',axaction(2,M_REFERER)); 

		$db->query("UPDATE {$tblprefix}webcall SET 
		suppliername='{$fmdata['suppliername']}',
		address='{$fmdata['address']}',
		contactman='{$fmdata['contactman']}',
		phone='{$fmdata['phone']}',
		mobilephone='{$fmdata['mobilephone']}',
		artiperson='{$fmdata['artiperson']}',
		licence='{$fmdata['licence']}',
		taxnumber='{$fmdata['taxnumber']}',
		contactidcard='{$fmdata['contactidcard']}',
		contactmail='{$fmdata['contactmail']}',
		postcode='{$fmdata['postcode']}',
		sex='{$fmdata['sex']}'
		WHERE id='".(int)$fmdata['id']."' AND mid='$memberid'");

		cls_message::show('修改成功',axaction(6,M_REFERER));
	}
} elseif($option=='apply') {
	$isapply = $db->result_one("SELECT 1 FROM {$tblprefix}webcall WHERE mid='$memberid'");

	$isapply==1 && cls_message::show('已经申请过400电话分机，不能再申请',axaction(2,M_REFERER));

	if(!submitcheck('bsubmit')){

		tabheader('400电话&nbsp; -&nbsp; 申请分机','webcalladd',"?action=$action&option=$option",2,1,1);

		trbasic($red_doc.'企业名称','fmdata[suppliername]','','text',array('validate' => makesubmitstr('fmdata[suppliername]',1,0,'',20,'text')));
		trbasic($red_doc.'企业地址','fmdata[address]','','text',array('validate' => makesubmitstr('fmdata[address]',1,0,'',100,'text')));
		trbasic('邮编','fmdata[postcode]','','text',array('validate' => makesubmitstr('fmdata[postcode]',0,'int',6,6,'text')));
		trbasic($red_doc.'管理帐号','fmdata[username]','','text',array('guide'=>'4-20位(数字，字母和下划线)','validate' => makesubmitstr('fmdata[username]',1,0,4,20,'text')));
		trbasic($red_doc.'管理密码','fmdata[pwd]','','password',array('guide'=>'请务必牢记帐号密码，成功申请后系统将删除密码信息','validate' => makesubmitstr('fmdata[pwd]',1,0,'','','text')));
		trbasic($red_doc.'确认密码','fmdata[cfmpwd]','','password', array('validate' => ' rule="comp" must="1" vid="fmdata[pwd]"'));

		trbasic($red_doc.'联系人','fmdata[contactman]','','text',array('validate' => makesubmitstr('fmdata[contactman]',1,0,'',20,'text')));
		trbasic('性别','',makeradio('fmdata[sex]',array(1 => '男',0 => '女'),1),'');
		trbasic($red_doc.'身份证','fmdata[contactidcard]','','text',array('validate' => makesubmitstr('fmdata[contactidcard]',1,0,'',20,'text','/^([1-9][0-9]{13,16}[0-9A-Z])$/')));
		trbasic($red_doc.'联系电话','fmdata[phone]','','text',array('validate' => makesubmitstr('fmdata[phone]',1,0,'',20,'text')));
		trbasic('联系手机','fmdata[mobilephone]','','text',array('validate' => makesubmitstr('fmdata[mobilephone]',0,'int',11,11,'text')));
		trbasic($red_doc.'电子邮箱','fmdata[contactmail]','','text',array('validate' => makesubmitstr('fmdata[contactmail]',1,0,'',100,'text','/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/')));

		trbasic('法人','fmdata[artiperson]','','text',array('validate' => makesubmitstr('fmdata[artiperson]',0,0,'',50,'text')));
		trbasic('营业执照号码','fmdata[licence]','','text',array('validate' => makesubmitstr('fmdata[licence]',0,0,'',20,'text')));
		trbasic('税务登记号','fmdata[taxnumber]','','text',array('validate' => makesubmitstr('fmdata[taxnumber]',0,0,'',20,'text')));

		tabfooter('bsubmit','提交');

	} else {
		foreach($fmdata as $k=>$v){
			if(in_array($k, array('mobilephone','artiperson','licence','taxnumber','postcode'))) continue;
			empty($v) && cls_message::show('请完整填写资料',axaction(2,M_REFERER));
		}
		//!is_numeric($fmdata['postcode']) && cls_message::show('邮编必须是数字',axaction(2,M_REFERER));
		//!is_numeric($fmdata['contactidcard']) && cls_message::show('身份证必须是数字',axaction(2,M_REFERER));
		if(!preg_match('/([1-9][0-9]{13,16}[0-9A-Z])/',$fmdata['contactidcard'])) cls_message::show('身份证号码不合法',axaction(2,M_REFERER));
		
		(4>strlen($fmdata['username']) || strlen($fmdata['username'])>20) && cls_message::show('管理帐号必须是4-20位',axaction(2,M_REFERER));
		($fmdata['pwd']!=$fmdata['cfmpwd']) && cls_message::show('两次输入的密码不一致',axaction(2,M_REFERER));

		$db->query("INSERT INTO {$tblprefix}webcall (mid,mname,suppliername,address,username,pwd,contactman,phone,mobilephone,artiperson,licence,taxnumber,contactidcard,contactmail,postcode,sex,createdate,state) 
		VALUES ('$memberid','{$curuser->info['mname']}','{$fmdata['suppliername']}','{$fmdata['address']}','{$fmdata['username']}','{$fmdata['pwd']}','{$fmdata['contactman']}','{$fmdata['phone']}','{$fmdata['mobilephone']}','{$fmdata['artiperson']}','{$fmdata['licence']}','{$fmdata['taxnumber']}','{$fmdata['contactidcard']}','{$fmdata['contactmail']}','{$fmdata['postcode']}','{$fmdata['sex']}','$timestamp','0')");

		cls_message::show('申请提交成功',axaction(6, "?action=$action"));
	}
}
?>