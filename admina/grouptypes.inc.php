<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
foreach(array('currencys','mchannels',) as $k) $$k = cls_cache::Read($k);
if($action == 'grouptypesedit'){
	backnav('mchannel','grouptype');
	if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
	$grouptypes = fetch_arr();
	if(!submitcheck('bgrouptypesadd') && !submitcheck('bgrouptypesedit')){
		$modearr = array('0' => '用户手动','1' => '管理手动','2' => '积分基数','3' => '积分兑换',);
		$cridsarr = array(0 => '不设置') + cridsarr();
		$itemstr = '';
		foreach($grouptypes as $k => $v){
			$modestr = $modearr[$v['mode']];
			$cridstr = empty($v['crid']) || empty($cridsarr[$v['crid']]) ? '-' : $cridsarr[$v['crid']];
			if(empty($v['crid']) && $v['mode'] == 3) $cridstr = '现金';
			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$k</td>\n".
					"<td class=\"txtL\"><input type=\"text\" size=\"25\" maxlength=\"30\" name=\"grouptypesnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
					"<td class=\"txtC\">$modestr</td>\n".
					"<td class=\"txtC\">$cridstr</td>\n".
					"<td class=\"txtC\">".($v['issystem'] || $v['mode'] != 1 ? '-' : "<a href=\"?entry=grouptypes&action=uprojects&gtid=$k\" onclick=\"return floatwin('open_grouptypesedit',this)\">设置</a>")."</td>\n".
					"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".($v['issystem'] ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"")."></td>\n".
					"<td class=\"txtC w40\"><a href=\"?entry=grouptypes&action=grouptypedetail&gtid=$k\" onclick=\"return floatwin('open_grouptypesedit',this)\">设置</a></td>\n".
					"<td class=\"txtC w40\"><a href=\"?entry=usergroups&action=usergroupsedit&gtid=$k\" onclick=\"return floatwin('open_grouptypesedit',this)\">管理</a></td></tr>\n";
		}
		tabheader('编辑会员组体系','grouptypesedit','?entry=grouptypes&action=grouptypesedit','7');
		trcategory(array('ID','组系名称|L','处理模式','相关积分','组变更方案','删除','详情','会员组'));
		echo $itemstr;
		tabfooter('bgrouptypesedit','修改');

		tabheader('添加会员组体系','grouptypesadd','?entry=grouptypes&action=grouptypesedit');
		trbasic('组系名称','grouptypeadd[cname]');
		trbasic('处理模式','grouptypeadd[mode]',makeoption($modearr),'select');
		trbasic('相关积分类型','grouptypeadd[crid]',makeoption($cridsarr),'select');
		tabfooter('bgrouptypesadd','添加');
		a_guide('grouptypesedit');
	}elseif(submitcheck('bgrouptypesadd')){
		if(empty($grouptypeadd['cname']) || (($grouptypeadd['mode'] == 2) && empty($grouptypeadd['crid']))){
			cls_message::show('会员组体系资料不完全','?entry=grouptypes&action=grouptypesedit');
		}
		$grouptypeadd['crid'] = $grouptypeadd['mode'] < 2 ? 0 : $grouptypeadd['crid'];
		$db->query("INSERT INTO {$tblprefix}grouptypes SET
					gtid=".auto_insert_id('grouptypes').",
					cname='$grouptypeadd[cname]',
					mode='$grouptypeadd[mode]',
					crid='$grouptypeadd[crid]'");
		if(!$gtid = $db->insert_id()){
			cls_message::show('会员组体系保存时发生错误','?entry=grouptypes&action=grouptypesedit');
		}else{
			$addfieldid = 'grouptype'.$gtid;
			$addfielddate = 'grouptype'.$gtid.'date';
			$db->query("ALTER TABLE {$tblprefix}members ADD $addfieldid smallint(6) unsigned NOT NULL default 0", 'SILENT');
			$db->query("ALTER TABLE {$tblprefix}members ADD $addfielddate int(10) unsigned NOT NULL default 0", 'SILENT');
		}
		adminlog('添加会员组体系');
		cls_CacheFile::Update('grouptypes');
		cls_message::show('会员组体系添加完成',"?entry=grouptypes&action=grouptypesedit");
	}elseif(submitcheck('bgrouptypesedit')){
		if(!empty($delete) && deep_allow($no_deepmode)){		    
            $file = _08_FilesystemFile::getInstance();
			foreach($delete as $gtid) {
				if(empty($grouptypes[$gtid]['issystem'])){
					if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}usergroups WHERE gtid='$gtid'")) continue;//包含相关会员组时不能删除
					$db->query("DELETE FROM {$tblprefix}grouptypes WHERE gtid='$gtid'","SILENT");
					$deletefield = 'grouptype'.$gtid;
					$deletefielddate = 'grouptype'.$gtid.'date';
					$db->query("ALTER TABLE {$tblprefix}members DROP $deletefield,DROP $deletefielddate","SILENT");
					$file->delFile(M_ROOT."dynamic/cache/usergroups$gtid.cac.php");
					unset($grouptypesnew[$gtid]);
				}
			}
		}

		if(!empty($grouptypesnew)){
			foreach($grouptypesnew as $gtid => $grouptype){
				if(empty($grouptypes[$gtid]['issystem'])){
					$grouptype['cname'] = empty($grouptype['cname']) ? $grouptypes[$gtid]['cname'] : $grouptype['cname'];
					if($grouptype['cname'] != $grouptypes[$gtid]['cname']){
						$db->query("UPDATE {$tblprefix}grouptypes SET
									cname='$grouptype[cname]'
									WHERE gtid='$gtid'");
					}
				}
			}
		}
		adminlog('编辑会员组体系管理列表');
		cls_CacheFile::Update('grouptypes');
		cls_message::show('会员组体系编辑完成',"?entry=grouptypes&action=grouptypesedit");
	}
}elseif($action == 'grouptypedetail' && $gtid){
	if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
	if(!($grouptype = fetch_one($gtid))) cls_message::show('请指定正确的会员组系');
	if(!submitcheck('bgrouptypedetail')){
		tabheader('编辑会员组体系','grouptypedetail',"?entry=grouptypes&action=grouptypedetail&gtid=$gtid");
		$modearr = array('0' => '用户手动','1' => '管理手动','2' => '积分基数','3' => '积分兑换',);
		$cridsarr = array(0 => $grouptype['mode'] == 3 ? '现金': '不设置') + cridsarr();
		trbasic('组系名称','grouptypenew[cname]',$grouptype['cname']);
		if($grouptype['issystem']){
			trbasic('处理模式','',$modearr[$grouptype['mode']],'');
			trbasic('相关积分类型','',$cridsarr[$grouptype['crid']],'');
		}else{
			trbasic('处理模式','grouptypenew[mode]',makeoption($modearr,$grouptype['mode']),'select');
			trbasic('相关积分类型','grouptypenew[crid]',makeoption($cridsarr,$grouptype['crid']),'select');
		}
		trbasic('在以下模型中禁止使用','',makecheckbox('grouptypenew[mchids][]',cls_mchannel::mchidsarr(),!empty($grouptype['mchids']) ? explode(',',$grouptype['mchids']) : array(),5),'');
		tabfooter('bgrouptypedetail','修改');
		a_guide('grouptypedetail');
	}else{
		$grouptypenew['mode'] = empty($grouptypenew['mode']) ? 0 : $grouptypenew['mode'];
		$grouptypenew['crid'] = empty($grouptypenew['crid']) ? 0 : $grouptypenew['crid'];
		if(empty($grouptypenew['cname']) || (($grouptypenew['mode'] == 2) && empty($grouptypenew['crid']))){
			cls_message::show('会员组体系资料不完全',M_REFERER);
		}
		$grouptypenew['crid'] = $grouptypenew['mode'] < 2 ? 0 : $grouptypenew['crid'];
		$grouptypenew['mchids'] = !empty($grouptypenew['mchids']) ? implode(',',$grouptypenew['mchids']) : '';
		$sqlstr = $grouptype['issystem'] ? '' : "mode='$grouptypenew[mode]',crid='$grouptypenew[crid]',";
		$db->query("UPDATE {$tblprefix}grouptypes SET
					cname='$grouptypenew[cname]',
					$sqlstr
					mchids='$grouptypenew[mchids]'
					WHERE gtid='$gtid'");
		adminlog('详情修改会员组体系');
		cls_CacheFile::Update('grouptypes',$gtid);
		cls_message::show('会员组体系编辑完成',"?entry=grouptypes&action=grouptypedetail&gtid=$gtid");
	}
}elseif($action == 'uprojects' && $gtid){
	if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
	if(!($grouptype = fetch_one($gtid))) cls_message::show('请指定正确的会员组系');
	if($grouptype['issystem'] || $grouptype['mode'] != 1)  cls_message::show('只有管理手动的组系才可以定义变更方案。');
	$uprojects = fetch_parr();
	if(!submitcheck('bsubmit')){
		$ugidsarr = array(0 => '组外会员') + ugidsarr($gtid);

		$nuprojects = array();foreach($uprojects as $k => $v) $v['gtid'] == $gtid && $nuprojects[$k] = $v;
		tabheader("会员组变更方案&nbsp; -&nbsp; $grouptype[cname]"."&nbsp; &nbsp; >><a href=\"?entry=$entry&action=uprojectadd&gtid=$gtid\" onclick=\"return floatwin('open_uprojects',this)\">添加方案</a>",'uprojects',"?entry=$entry&action=uprojects&gtid=$gtid",'10');
		trcategory(array(array('方案名称','txtL'),array('来源会员组','txtL'),array('目标会员组','txtL'),'自动审核','删除','编辑'));
		foreach($nuprojects as $k => $v){
			echo "<tr class=\"txt\">".
				"<td class=\"txtL\"><input type=\"text\" size=\"20\" maxlength=\"30\" name=\"uprojectsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtL\">".$ugidsarr[$v['sugid']]."</td>\n".
				"<td class=\"txtL\">".$ugidsarr[$v['tugid']]."</td>\n".
				"<td class=\"txtC w60\"><input class=\"checkbox\" type=\"checkbox\" name=\"uprojectsnew[$k][autocheck]\" value=\"1\"".($v['autocheck'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=uprojectdetail&gtid=$gtid&upid=$k\" onclick=\"return floatwin('open_uprojects',this)\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bsubmit');
		a_guide('grouptypedetail');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}uprojects WHERE upid='$k'");
				unset($uprojectsnew[$k]);
			}
		}
		if(!empty($uprojectsnew)){
			foreach($uprojectsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $uprojects[$k]['cname'];
				$v['autocheck'] = empty($v['autocheck']) ? 0 : 1;
				$db->query("UPDATE {$tblprefix}uprojects SET cname='$v[cname]',autocheck='$v[autocheck]' WHERE upid='$k'");
			}
		}
		cls_CacheFile::Update('uprojects');
		adminlog('修改会员组变更方案');
		cls_message::show('会员组变更方案修改完成',"?entry=$entry&action=uprojects&gtid=$gtid");
	}
}elseif($action == 'uprojectadd' && $gtid){
	if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
	!($ugidsarr = ugidsarr($gtid)) && cls_message::show('请先添加有效的会员组');
	$uprojects = fetch_parr();
	if(!submitcheck('buprojectadd')){
		$ugidsarr = array(0 => '组外会员') + $ugidsarr;
		tabheader('添加会员组变更方案',"uprojectadd","?entry=$entry&action=uprojectadd&gtid=$gtid",2,0,1);
		trbasic('方案名称','uprojectnew[cname]','','text',array('validate'=>makesubmitstr('uprojectnew[cname]',1,0,3,30)));
		trbasic('来源会员组','uprojectnew[sugid]',makeoption($ugidsarr),'select');
		trbasic('目标会员组','uprojectnew[tugid]',makeoption($ugidsarr),'select');
		trbasic('会员组变更自动审核','uprojectnew[autocheck]',0,'radio');
		tabfooter('buprojectadd','添加');
		a_guide('uprojectadd');
	}else{
		$uprojectnew['cname'] = trim(strip_tags($uprojectnew['cname']));
		if(!$uprojectnew['cname']) cls_message::show('请输入方案名称!',M_REFERER);
		if($uprojectnew['sugid'] == $uprojectnew['tugid']) cls_message::show('来源会员组与目标会员组相同!',M_REFERER);
		$uprojectnew['ename'] = $uprojectnew['sugid'].'_'.$uprojectnew['tugid'];
		$usedcnames = array();foreach($uprojects as $v) $usedcnames[] = $v['ename'];
		if(in_array($uprojectnew['ename'],$usedcnames)) cls_message::show('方案重复定义!',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}uprojects SET
					cname='$uprojectnew[cname]',
					ename='$uprojectnew[ename]',
					gtid='$gtid',
					sugid='$uprojectnew[sugid]',
					tugid='$uprojectnew[tugid]',
					autocheck='$uprojectnew[autocheck]'
					");
		cls_CacheFile::Update('uprojects');
		adminlog('添加会员组变更方案');
		cls_message::show('会员组变更方案添加完成',axaction(6,"?entry=$entry&action=uprojects&gtid=$gtid"));
	}
}elseif($action == 'uprojectdetail' && $gtid && $upid){
	if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
	!($ugidsarr = ugidsarr($gtid)) && cls_message::show('请先添加有效的会员组');
	!($uproject = fetch_pone($upid)) && cls_message::show('请指定正确的会员模型变更方案');
	$uprojects = fetch_parr();
	if(!submitcheck('buprojectdetail')){
		$ugidsarr = array(0 => '组外会员') + $ugidsarr;
		tabheader('编辑会员组变更方案',"uprojectdetail","?entry=$entry&action=uprojectdetail&gtid=$gtid&upid=$upid",2,0,1);
		trbasic('方案名称','uprojectnew[cname]',$uproject['cname'],'text',array('validate'=>makesubmitstr('uprojectnew[cname]',1,0,3,30)));
		trbasic('来源会员组','uprojectnew[sugid]',makeoption($ugidsarr,$uproject['sugid']),'select');
		trbasic('目标会员组','uprojectnew[tugid]',makeoption($ugidsarr,$uproject['tugid']),'select');
		trbasic('会员组变更自动审核','uprojectnew[autocheck]',$uproject['autocheck'],'radio');
		tabfooter('buprojectdetail');
		a_guide('uprojectdetail');
	}else{
		$uprojectnew['cname'] = trim(strip_tags($uprojectnew['cname']));
		if(!$uprojectnew['cname']) cls_message::show('请输入方案名称!',M_REFERER);
		if($uprojectnew['sugid'] == $uprojectnew['tugid']) cls_message::show('来源模型与目标模型相同!',M_REFERER);
		$uprojectnew['ename'] = $uprojectnew['sugid'].'_'.$uprojectnew['tugid'];
		$usedcnames = array();foreach($uprojects as $v) $usedcnames[] = $v['ename'];
		if(($uprojectnew['ename'] != $uproject['ename']) && in_array($uprojectnew['ename'],$usedcnames)) cls_message::show('方案重复定义!',M_REFERER);
		$db->query("UPDATE {$tblprefix}uprojects SET
					cname='$uprojectnew[cname]',
					ename='$uprojectnew[ename]',
					sugid='$uprojectnew[sugid]',
					tugid='$uprojectnew[tugid]',
					autocheck='$uprojectnew[autocheck]'
					WHERE upid='$upid'
					");
		cls_CacheFile::Update('uprojects');
		adminlog('修改会员组变更方案');
		cls_message::show('会员组变更方案修改完成',axaction(6,"?entry=$entry&action=uprojects&gtid=$gtid"));
	}
}
function fetch_arr(){
	global $db,$tblprefix;
	$rets = array();
	$query = $db->query("SELECT * FROM {$tblprefix}grouptypes ORDER BY gtid");
	while($r = $db->fetch_array($query)){
		$rets[$r['gtid']] = $r;
	}
	return $rets;
}

function fetch_one($gtid){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}grouptypes WHERE gtid='$gtid'");
	return $r;
}
function fetch_parr(){
	global $db,$tblprefix;
	$rets = array();
	$query = $db->query("SELECT * FROM {$tblprefix}uprojects ORDER BY upid");
	while($r = $db->fetch_array($query)){
		$rets[$r['upid']] = $r;
	}
	return $rets;
}

function fetch_pone($upid){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}uprojects WHERE upid='$upid'");
	return $r;
}

?>
