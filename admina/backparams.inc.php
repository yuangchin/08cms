<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
foreach(array('mconfigs','currencys','commus','channels',) as $k) $$k = cls_cache::Read($k);
if($action == 'bkparams'){
	backnav('backarea','bkparam');
	if($re = $curuser->NoBackFunc('bkconfig')) cls_message::show($re);
	if(!submitcheck('bmconfigs')){
		tabheader('管理后台参数','cfview',"?entry=backparams&action=bkparams");
		trbasic('文档自动摘要长度','mconfigsnew[autoabstractlength]',$mconfigs['autoabstractlength']);
		trbasic('管理后台列表每页显示数','mconfigsnew[atpp]',$mconfigs['atpp']);
		trbasic('管理后台提示信息停留','mconfigsnew[amsgforwordtime]',$mconfigs['amsgforwordtime'],'text',array('guide' => '单位：毫秒'));
		trbasic('节点配置界面每行显示数','mconfigsnew[cnprow]',$mconfigs['cnprow'],'text',array('guide' => '此参数控制位置：模版风格-->类目节点管理-->节点组合方案-->选中某个方案的详情-->包含：栏目/类系名(手动指定)  中的列表项。'));
		trbasic('启用自动拼音','mconfigsnew[aeisablepinyin]',empty($mconfigs['aeisablepinyin']) ? 1 : 0,'radio');
		trbasic('启用浮动窗口','mconfigsnew[aallowfloatwin]',empty($mconfigs['aallowfloatwin']) ? 0 : 1,'radio');
		tabfooter();
		if($curuser->info['isfounder']){
			tabheader('管理后台操作权限');
			trbasic('启用重要架构保护','',makeradio('mconfigsnew[no_deepmode]',array(0 => '关闭',1 => '开启'),empty($mconfigs['no_deepmode']) ? 0 : 1),'',array('guide'=>'此设置仅创始人有权限，在架构保护模式下禁止添加或删除某些重要架构，避免误操作影响系统功能，平时请保持开启状态。'));
			if(1 != @$mconfigs['cms_idkeep']){//原先处于官方升级模式的话，不能在此设置
				trbasic('系统开发模式','',makeradio('mconfigsnew[cms_idkeep]',array(0 => '非开发模式',2 => '二次开发模式'),empty($mconfigs['cms_idkeep']) ? 0 : 2),'',
				array('guide' => '非开发模式：屏蔽部分重要架构的新增及删除、屏蔽对数据表的直接操作，如未涉及程序二次开发，强烈建议使用此模式。<br>
				二次开发模式：开放重要架构的新增及删除、可对部分数据表进行直接操作，请谨慎操作。此设置项仅创始人有权限。
				'));
			}
			tabfooter();
		}
		tabheader('管理后台登录设置');
		trbasic('后台登录状态忽略IP','',makeradio('mconfigsnew[no_cip]',array(1 => '是',0 => '否'),empty($mconfigs['no_cip']) ? 0 : 1),'',array('guide'=>'默认为[否]；如果“因客户端ip不断变化而经常跳出后台”可设置[是]。'));
		trbasic('登录最大尝试错误次数','mconfigsnew[amaxerrtimes]',$mconfigs['amaxerrtimes'],'text',array('guide'=>'留空表示不限错误次数，建议设为3，错误尝试过多会锁定该帐号。'));
		trbasic('闲置自动退出时间(分钟)','mconfigsnew[aminerrtime]',$mconfigs['aminerrtime'],'text',array('guide'=>'建议为60分钟，同时也是登录失败锁定时间。'));
		trbasic('管理后台允许IP列表','mconfigsnew[adminipaccess]',$mconfigs['adminipaccess'],'textarea',array('guide'=>'每行输入一个 IP，可为完整地址，也可是 IP 开头某几个字符，空表示不限制登录者IP'));
		tabfooter('bmconfigs');
	}else{
		if($curuser->info['isfounder']){
			$mconfigsnew['no_deepmode'] = empty($mconfigsnew['no_deepmode']) ? 0 : 1;
			if(1 != @$mconfigs['cms_idkeep']){//原先处于官方升级模式的话，不能在此设置	
				$mconfigsnew['cms_idkeep'] = empty($mconfigsnew['cms_idkeep']) ? 0 : 2;
			}	
		}
		$mconfigsnew['amaxerrtimes'] = max(1,intval($mconfigsnew['amaxerrtimes']));
		$mconfigsnew['aminerrtime'] = max(3,intval($mconfigsnew['aminerrtime']));
		$mconfigsnew['autoabstractlength'] = min(1000,max(10,intval($mconfigsnew['autoabstractlength'])));
		$mconfigsnew['atpp'] = max(5,intval($mconfigsnew['atpp']));
		$mconfigsnew['amsgforwordtime'] = max(0,intval($mconfigsnew['amsgforwordtime']));
		$mconfigsnew['cnprow'] = max(1,intval($mconfigsnew['cnprow']));
		$mconfigsnew['aeisablepinyin'] = empty($mconfigsnew['aeisablepinyin']) ? 1 : 0;
		saveconfig('view');
		adminlog('网站设置','页面与模板设置');
		cls_message::show('网站设置完成',"?entry=backparams&action=bkparams");
	}
}elseif($action == 'mcparams'){
	backnav('mcenter','mcparam');
	if($re = $curuser->NoBackFunc('mcconfig')) cls_message::show($re);
	if(!submitcheck('bmconfigs')){
		tabheader('会员中心参数','cfview',"?entry=backparams&action=mcparams");
		trbasic('会员中心目录','mconfigsnew[mc_dir]',$mconfigs['mc_dir'],'text',array('guide'=>'系统内置的会员中心目录为adminm，为了不影响升级，经过二次开发的会员中心请使用其它的目录。'));
		trbasic('会员中心提示信息停留(毫秒)','mconfigsnew[mmsgforwordtime]',$mconfigs['mmsgforwordtime']);
		trbasic('会员中心列表每页显示数','mconfigsnew[mrowpp]',$mconfigs['mrowpp']);
		trbasic('个人分类最大数量限制','mconfigsnew[maxuclassnum]',empty($mconfigs['maxuclassnum']) ? 0 : $mconfigs['maxuclassnum']);
		trbasic('个人分类字节长度限制','mconfigsnew[uclasslength]',$mconfigs['uclasslength']);
		trspecial('会员中心LOGO',specialarr(array('type' => 'image','varname' => 'mconfigsnew[mcenterlogo]','value' => $mconfigs['mcenterlogo'],'guide' => '最佳尺寸 260 X 50')));
		trbasic('启用浮动窗口','mconfigsnew[mallowfloatwin]',empty($mconfigs['mallowfloatwin']) ? 0 : $mconfigs['mallowfloatwin'],'radio');
		setPermBar('会员中心托管权限','mconfigsnew[g_apid]',empty($mconfigs['g_apid']) ? 0 : $mconfigs['g_apid'], 'other', 'open', '方案中允许的会员才可以将会员中心委托给其它人管理。');
        tabfooter('bmconfigs');
	}else{
		$mconfigsnew['mc_dir'] = strtolower(trim(strip_tags($mconfigsnew['mc_dir'])));
		$mconfigsnew['mc_dir'] = empty($mconfigsnew['mc_dir']) ? 'adminm' : $mconfigsnew['mc_dir'];
		$mconfigsnew['mmsgforwordtime'] = max(0,intval($mconfigsnew['mmsgforwordtime']));
		$mconfigsnew['mrowpp'] = max(5,intval($mconfigsnew['mrowpp']));
		$mconfigsnew['g_apid'] = (int)$mconfigsnew['g_apid'];
		$mconfigsnew['uclasslength'] = min(30,max(4,intval($mconfigsnew['uclasslength'])));
		$mconfigsnew['maxuclassnum'] = max(0,intval($mconfigsnew['maxuclassnum']));
		$c_upload = cls_upload::OneInstance();
		$mconfigsnew['mcenterlogo'] = upload_s($mconfigsnew['mcenterlogo'],$mconfigs['mcenterlogo'],'image');
		if($k = strpos($mconfigsnew['mcenterlogo'],'#')) $mconfigsnew['mcenterlogo'] = substr($mconfigsnew['mcenterlogo'],0,$k);
		$c_upload->saveuptotal(1);
		saveconfig('view');
		adminlog('网站设置','页面与模板设置');
		cls_message::show('网站设置完成',"?entry=backparams&action=mcparams");
	}
}

?>
