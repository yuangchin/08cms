<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('webparam')) cls_message::show($re);
$channels = cls_cache::Read('channels');
if(empty($action)) $action = 'pagecachesedit';
$pctypes = array(
	1 => '类目节点|index.php',
	2 => '文档页|archive.php',
	3 => '独立页|info.php',
	4 => '搜索页|search.php',
	5 => '会员节点|member/index.php',
	6 => '会员搜索|member/search.php',
	7 => '空间栏目|mspace/index.php',
	8 => '空间文档|mspace/archive.php',
	9 => 'js缓存|tools/js.php',
	);
if($action == 'pagecachesedit'){
	backnav('project','pagecache');
	if(!submitcheck('bsubmit')){
		tabheader("页面缓存方案管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=pagecacheadd\" onclick=\"return floatwin('open_pagecaches',this)\">添加方案</a>&nbsp; &nbsp; >><a href=\"?entry=rebuilds&action=pagecache\">清理缓存</a>",$actionid.'arcsedit',"?entry=$entry&action=$action");
		trcategory(array('ID',"<input class=\"checkbox\" type=\"checkbox\" name=\"chkallc\" onclick=\"checkall(this.form,'fmdata','chkallc')\">启用",'方案名称|L','页面类型|L','样板页','周期(s)','排序',"<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"deltip(this,$no_deepmode,checkall,this.form, 'delete', 'chkall')\">删?",'编辑'));
		$query = $db->query("SELECT * FROM {$tblprefix}pagecaches ORDER BY vieworder,pcid");
		while($item = $db->fetch_array($query)){
			$id = $item['pcid'];
			echo "<tr class=\"txt\">".
			"<td class=\"txtC w40\">$id</td>\n".
			"<td class=\"txtC w50\"><input type=\"checkbox\" class=\"checkbox\" ".(empty($item['available']) ? "" : " checked")." value=\"1\" name=\"fmdata[$id][available]\"></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"40\" name=\"fmdata[$id][cname]\" value=\"$item[cname]\"></td>\n".
			"<td class=\"txtL\">".$item['typeid'].'_'.@$pctypes[$item['typeid']]."</td>\n".
			"<td class=\"txtC\">".($item['demourl'] ? "<a href=\"".cls_url::view_url($item['demourl'])."\" target=\"_blank\">查看</a>" : '-')."</td>\n".
			"<td class=\"txtC\"><input type=\"text\" size=\"5\" name=\"fmdata[$id][period]\" value=\"$item[period]\"></td>\n".
			"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" name=\"fmdata[$id][vieworder]\" value=\"$item[vieworder]\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$id]\" value=\"$id\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
			"<td class=\"txtC w40\"><a onclick=\"return floatwin('open_pagecaches',this)\" href=\"?entry=$entry&action=pagecachedetail&pcid=$id\">详情</a></td></tr>\n";
			"</tr>\n";
		}
		tabfooter('bsubmit');
		a_guide('pagecachesedit');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}pagecaches WHERE pcid='$k'");
				unset($fmdata[$k]);
			}
		}
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['available'] = empty($v['available']) ? 0 : 1;
				$v['period'] = max(0,intval($v['period']));
				$v['vieworder'] = max(0,intval($v['vieworder']));
				if(!$v['cname']) continue;
				$db->query("UPDATE {$tblprefix}pagecaches SET cname='$v[cname]',available='$v[available]',period='$v[period]',vieworder='$v[vieworder]' WHERE pcid='$k'");
			}
		}
		adminlog('编辑页面缓存方案列表');
		cls_CacheFile::Update('pagecaches'); 
		cls_message::show('缓存方案编辑完成！', "?entry=$entry&action=$action");
	}

}elseif($action == 'pagecacheadd'){
	if(!submitcheck('bsubmit')){
		tabheader('添加页面缓存方案','pagecacheadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('方案名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,3,50),'w' => 50,));
		trbasic('页面类型','fmdata[typeid]',makeoption($pctypes),'select',array('guide' => '|后面为页面访问url入口脚本，其中会员频道及空间页面的目录会随便系统设置变化。'));
		tabfooter('bsubmit');
		a_guide('pagecachesedit');
	}else{
		$fmdata['cname'] = trim(strip_tags($fmdata['cname']));
		if(!$fmdata['cname'] || !$fmdata['typeid']) cls_message::show('资料不完全',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}pagecaches SET 
				   	pcid = ".auto_insert_id('pagecaches').",
					cname='$fmdata[cname]', 
					typeid='$fmdata[typeid]'
					");
		if($pcid = $db->insert_id()){
			adminlog('添加页面缓存方案');
			cls_CacheFile::Update('pagecaches');
			cls_message::show('缓存方案添加成功，请对方案进行详细设置。',"?entry=$entry&action=pagecachedetail&pcid=$pcid");
		}else cls_message::show('缓存方案添加不成功！', axaction(6,"?entry=$entry&action=pagecachesedit"));
	}
}elseif($action == 'pagecachedetail' && $pcid){
	!($pagecache = fetch_one($pcid)) && cls_message::show('指定的方案不存在。');
	if(!submitcheck('bsubmit')){
		tabheader('页面缓存方案详情','pagecacheadd',"?entry=$entry&action=$action&pcid=$pcid",2,0,1);
		trbasic('方案名称','fmdata[cname]',$pagecache['cname'],'text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,3,50),'w' => 50,));
		trbasic('页面类型','',$pagecache['typeid'].'_'.@$pctypes[$pagecache['typeid']],'',array('guide' => '|后面为页面访问url入口脚本，其中会员频道及空间页面的目录会随便系统设置变化。'));
		trbasic('示例页面url','fmdata[demourl]',$pagecache['demourl'],'text',array('validate'=>makesubmitstr('fmdata[cname]',0,0,3,255),'w' => 50,));
		trbasic('缓存周期',"fmdata[period]",$pagecache['period'],'text',array('guide'=>'单位：秒，留空为不缓存。','validate'=>makesubmitstr("fmdata[period]",1,'int',1,5)));
		trbasic('缓存起始页码',"fmdata[pagefrom]",$pagecache['pagefrom'],'text',array('guide'=>'请输入1-99之间的整数。','validate'=>makesubmitstr("fmdata[pagefrom]",0,'int',0,2)));
		trbasic('缓存最大页码',"fmdata[pageto]",$pagecache['pageto'],'text',array('guide'=>'请输入1-99之间的整数。','validate'=>makesubmitstr("fmdata[pageto]",0,'int',0,2)));
		$cfgs = &$pagecache['cfgs'];
		if(in_array($pagecache['typeid'],array(2,8))){
			trbasic('缓存以下模型的文档<br /><input class="checkbox" type="checkbox" name="chchkall" onclick="checkall(this.form,\'cfgsnew[chids]\',\'chchkall\')">全选','',makecheckbox('cfgsnew[chids][]',cls_channel::chidsarr(1),empty($cfgs['chids']) ? array() : explode(',',$cfgs['chids']),5),'');
			trbasic('缓存多少天内发布的',"cfgsnew[indays]",empty($cfgs['indays']) ? '' : $cfgs['indays'],'text',array('guide'=>'请输入1-999之间的整数，留空为不限。','validate'=>makesubmitstr("cfgsnew[indays]",0,'int',0,3)));
		}	
		trbasic("url中含以下字串则缓存<br><input class=\"checkbox\" type=\"checkbox\" name=\"cfgsnew[instrall]\" value=\"1\"".(empty($cfgs['instrall']) ? "" : " checked").">多个字串同时包含",'cfgsnew[instr]',@$cfgs['instr'],'text',array('validate'=>makesubmitstr('cfgsnew[instr]',0,0,3,100),'w' => 50,'guide' => '字串通常为 abc=123 格式中的一部分，多个字串以英文逗号分隔，留空则不限制当前项'));
		trbasic("url中含以下字串不缓存<br><input class=\"checkbox\" type=\"checkbox\" name=\"cfgsnew[nostrall]\" value=\"1\"".(empty($cfgs['nostrall']) ? "" : " checked").">多个字串同时包含",'cfgsnew[nostr]',@$cfgs['nostr'],'text',array('validate'=>makesubmitstr('cfgsnew[nostr]',0,0,1,100),'w' => 50,'guide' => '多个字串以英文逗号分隔，输入*表示不缓存有附加参数的页面，留空则不排除任何格式'));
		if(in_array($pagecache['typeid'],array(1,2,4))){
			trbasic('关闭同页面手机版缓存',"cfgsnew[nomobile]",@$cfgs['nomobile'],'radio');
		}	
		tabfooter('bsubmit');
	}else{
		$fmdata['cname'] = trim(strip_tags($fmdata['cname']));
		if(!$fmdata['cname']) cls_message::show('资料不完全',M_REFERER);
		$fmdata['demourl'] = preg_replace(u_regcode($cms_abs),'',trim($fmdata['demourl']));
		$fmdata['period'] = max(0,intval($fmdata['period']));
		$fmdata['pagefrom'] = min(99,max(1,intval($fmdata['pagefrom'])));
		$fmdata['pageto'] = min(99,max(1,intval($fmdata['pageto'])));
		if(!empty($cfgsnew['chids'])) $cfgsnew['chids'] = implode(',',$cfgsnew['chids']);
		$fmdata['cfgs'] = empty($cfgsnew) ? '' : addslashes(var_export($cfgsnew,TRUE));
		$db->query("UPDATE {$tblprefix}pagecaches SET 
					cname='$fmdata[cname]', 
					demourl='$fmdata[demourl]', 
					period='$fmdata[period]', 
					pagefrom='$fmdata[pagefrom]', 
					pageto='$fmdata[pageto]', 
					cfgs='$fmdata[cfgs]'
					WHERE pcid='$pcid'");
			adminlog('编辑页面缓存方案');
			cls_CacheFile::Update('pagecaches');
			cls_message::show('缓存方案设置成功！', axaction(6,"?entry=$entry&action=pagecachesedit"));
	}

}
function fetch_one($pcid){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}pagecaches WHERE pcid='$pcid'");
	if(empty($r['cfgs']) || !is_array($r['cfgs'] = @varexp2arr($r['cfgs']))) $r['cfgs'] = array();
	return $r;
}

?>