<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('project')) cls_message::show($re);
foreach(array('permissions','grouptypes','mctypes',) as $k) $$k = cls_cache::Read($k);
if($action == 'permissionsedit'){
	backnav('project','pm');
	if(!submitcheck('bpermissionsedit') && !submitcheck('bpermissionsadd')) {
		tabheader('权限方案管理','permissionsedit','?entry=permissions&action=permissionsedit','6');
		$ii = 0;
		foreach($permissions as $k => $v){
			if(!($ii % 15)) trcategory(array('方案ID',array('方案名称','txtL'),'浏览','文档','副件','交互','审核','附件','菜单','模板','其它','排序','删除','详情'));
			$ii ++;
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" name=\"permissionsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][aread]\" value=\"1\"".($v['aread'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][aadd]\" value=\"1\"".($v['aadd'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][fadd]\" value=\"1\"".($v['fadd'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][cuadd]\" value=\"1\"".($v['cuadd'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][chk]\" value=\"1\"".($v['chk'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][down]\" value=\"1\"".($v['down'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][menu]\" value=\"1\"".($v['menu'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][tpl]\" value=\"1\"".($v['tpl'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permissionsnew[$k][other]\" value=\"1\"".($v['other'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"permissionsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w60\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=permissions&action=permissionsdetail&pmid=$k\" onclick=\"return floatwin('open_permissionsedit',this)\">详情</a></td></tr>\n";
		}
		tabfooter('bpermissionsedit','修改');

		tabheader('添加权限方案','permissionsadd','?entry=permissions&action=permissionsedit');
		trcategory(array('&nbsp;',array('方案名称','txtL'),'浏览','文档','副件','交互','审核','附件','菜单','模板','其它','排序','&nbsp;','&nbsp;'));
		echo "<tr class=\"txt\">".
			"<td class=\"txtC w40\">&nbsp;</td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"30\" name=\"permadd[cname]\" value=\"\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[aread]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[aadd]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[fadd]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[cuadd]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[chk]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[down]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[menu]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[tpl]\" value=\"1\"></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"permadd[other]\" value=\"1\"></td>\n".
			"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"permadd[vieworder]\" value=\"0\"></td>\n".
			"<td class=\"txtC w60\">-</td>\n".
			"<td class=\"txtC w40\">-</td></tr>\n";	
		tabfooter('bpermissionsadd','添加');
		a_guide('permissionsedit');
	}elseif(submitcheck('bpermissionsadd')){
		if(!$permadd['cname']) cls_message::show('资料不完全',M_REFERER);
		$sqla = '';
		foreach(array('aread','aadd','fadd','cuadd','chk','down','menu','other','tpl','vieworder') as $var) 
		$sqla .= ",$var='".(empty($permadd[$var]) ? 0 : $permadd[$var])."'";
		$db->query("INSERT INTO {$tblprefix}permissions SET 
					pmid=".auto_insert_id('permissions').",
					cname='$permadd[cname]'$sqla
					");
		adminlog('添加权限方案');
		cls_CacheFile::Update('permissions');
		cls_message::show('方案添加完成',M_REFERER);
	}elseif(submitcheck('bpermissionsedit')){
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}permissions WHERE pmid=$k");
				unset($permissionsnew[$k]);
			}
		}
		foreach($permissionsnew as $k => $v){
			$v['cname'] = !$v['cname'] ? $permissions[$k]['cname'] : $v['cname'];
			$sqlstr = '';
			foreach(array('aread','aadd','fadd','cuadd','chk','down','menu','other','tpl',) as $var) $sqlstr .= "$var='".(empty($v[$var]) ? 0 : 1)."',";
			$v['vieworder'] = max(0,intval($v['vieworder']));
			$db->query("UPDATE {$tblprefix}permissions SET 
						cname='$v[cname]',
						$sqlstr
						vieworder='$v[vieworder]'
						WHERE pmid='$k'");
		}
		adminlog('编辑权限方案管理列表');
		cls_CacheFile::Update('permissions');
		cls_message::show('方案修改完成',M_REFERER);
	}
}
if($action == 'permissionsdetail' && $pmid){
	$permission = $permissions[$pmid];
	if(!submitcheck('bpermissionsdetail')) {
		tabheader('内容权限方案','permissionsdetail','?entry=permissions&action=permissionsdetail&pmid='.$pmid);
		trbasic('方案名称','',$permission['cname'],'');
		
		$ugidsarr = array('-1' => array('<b>全部会员</b>',0));
		foreach($grouptypes as $k => $v){
			if(!$v['forbidden']){
				$ugids = ugidsarr($k);
				foreach($ugids  as $x => $y) $ugidsarr[$x] = array($y,$k);
			}
		}
		
		$oldarr = empty($permission['ugids']) ? array() : explode(',',$permission['ugids']);
		$str = '';$oldgtid = 0;$i = 1;
		foreach($ugidsarr as $k => $v){
			if($v[1] != $oldgtid){
				$str .= "<br /><b>{$grouptypes[$v[1]]['cname']}</b>($v[1])：<br />";
				$i = 1;
			}
			$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"ugidsnew[$k]\" value=\"$k\"".(in_array($k,$oldarr) ? " checked" : "").">$v[0] &nbsp;";
			if(!($i % 6)) $str .= '<br />';
			$oldgtid = $v[1];
			$i ++;
		}
		trbasic("以下会员组拥有权限<br><input type=\"checkbox\" name=\"chkall_ugidsnew\" onclick=\"checkall(this.form,'ugidsnew','chkall_ugidsnew')\">全选", '',$str,'',
		array('guide' => '会员在所选组之一即视为有权限。最终权限需要同时符合认证权限设置。'));
		
		$mctidsarr = array();
		foreach($mctypes as $k => $v) $mctidsarr[$k] = $v['cname'];
		trbasic("以下认证会员有权限<br><input type=\"checkbox\" name=\"chkall_mctidsnew\" onclick=\"checkall(this.form,'mctidsnew','chkall_mctidsnew')\">全选",'',
		"<input class=\"checkbox\" type=\"checkbox\" name=\"mctidmodenew\"  value=\"$k\"".(empty($permission['mctidmode']) ? "" : " checked")."><b>同时通过以下认证才能权限</b>，不选为认证之一<br>".
		makecheckbox('mctidsnew[]',$mctidsarr,empty($permission['mctids']) ? array() : explode(',',$permission['mctids']),5),'',
		array('guide' => '留空为完全开放。最终权限需要同时符合会员组权限设置。'));

		unset($ugidsarr['-1']);
		$oldarr = empty($permission['fugids']) ? array() : explode(',',$permission['fugids']);
		$str = '';$oldgtid = 0;$i = 1;
		foreach($ugidsarr as $k => $v){
			if($v[1] != $oldgtid){
				$str .= ($oldgtid ? '<br />' : '')."<b>{$grouptypes[$v[1]]['cname']}</b>($v[1])：<br />";
				$i = 1;
			}
			$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"fugidsnew[$k]\" value=\"$k\"".(in_array($k,$oldarr) ? " checked" : "").">$v[0] &nbsp;";
			if(!($i % 6)) $str .= '<br />';
			$oldgtid = $v[1];
			$i ++;
		}
		trbasic("以下会员组无权限<br><input type=\"checkbox\" name=\"chkall_fugidsnew\" onclick=\"checkall(this.form,'fugidsnew','chkall_fugidsnew')\">全选", '',$str,'',
		array('guide' => '会员在所选组之一即视为无权限，此设置为最终否决权限。'));
		
		tabfooter('bpermissionsdetail','修改');
		a_guide('permissionsdetail');
	}
	else{
		foreach(array('ugidsnew',) as $var){
			$$var = empty($$var) ? array() : (in_array('-1',$$var) ? array(-1) : $$var);
			$$var = empty($$var) ? '' : implode(',',$$var);
		}
		foreach(array('mctidsnew','fugidsnew',) as $var){
			$$var = empty($$var) ? '' : implode(',',$$var);
		}
		$mctidmodenew = empty($mctidmodenew) ? 0 : 1;
		$db->query("UPDATE {$tblprefix}permissions SET ugids='$ugidsnew',mctids='$mctidsnew',mctidmode='$mctidmodenew',fugids='$fugidsnew' WHERE pmid='$pmid'");
		adminlog('详细修改权限方案');
		cls_CacheFile::Update('permissions');
		cls_message::show('方案修改完成', axaction(6,'?entry=permissions&action=permissionsedit'));
	}
}

?>
