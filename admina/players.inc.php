<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('project')) cls_message::show($re);
$players = cls_cache::Read('players');
$ptypearr = array('media' => '视频播放器','flash' => 'Flash播放器');
backnav('project','player');
if($action == 'playersedit'){
	if(!submitcheck('bplayersedit') && !submitcheck('bplayeradd')) {
		tabheader('播放器管理','playersedit','?entry=players&action=playersedit','7');
		trcategory(array('有效','播放器名称|L','播放器类型','默认播放文件格式','排序','删除','详情'));
		foreach($players as $plid => $player){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"playersnew[$plid][available]\" value=\"1\"".(!empty($player['available']) ? ' checked' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" maxlength=\"30\" name=\"playersnew[$plid][cname]\" value=\"$player[cname]\"></td>\n".
				"<td class=\"txtC w100\">".$ptypearr[$player['ptype']]."</td>\n".
				"<td class=\"txtC\"><input type=\"text\" size=\"25\" maxlength=\"50\" name=\"playersnew[$plid][exts]\" value=\"$player[exts]\"></td>\n".
				"<td class=\"txtC w50\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"playersnew[$plid][vieworder]\" value=\"$player[vieworder]\"></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(!empty($player['issystem']) ? ' disabled' : " name=\"delete[$plid]\" value=\"$plid\" onclick=\"deltip(this,$no_deepmode)\"")."></td>\n".
				"<td class=\"txtC w50\"><a href=\"?entry=players&action=playerdetail&plid=$plid\">设置</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bplayersedit','修改');
	
		tabheader('添加播放器','playeradd','?entry=players&action=playersedit');
		trbasic('播放器名称','playeradd[cname]');
		trbasic('播放器类型','playeradd[ptype]',makeoption($ptypearr),'select');
		trbasic('默认播放文件格式','playeradd[exts]');
		tabfooter('bplayeradd','添加');
		a_guide('playersedit');
	}
	elseif(submitcheck('bplayeradd')){
		if(!$playeradd['cname']) {
			cls_message::show('请输入播放器名称', '?entry=players&action=playersedit');
		}
		if(preg_match("/[^a-z,A-Z0-9]+/",$playeradd['exts'])){
			cls_message::show('文件扩展名不合规范', '?entry=players&action=playersedit');
		}
		$playeradd['exts'] = strtolower($playeradd['exts']);
	
		$db->query("INSERT INTO {$tblprefix}players SET 
					plid=".auto_insert_id('players').",
					cname='$playeradd[cname]',
					ptype='$playeradd[ptype]',
					exts='$playeradd[exts]',
					available='1'
					");
		cls_CacheFile::Update('players');
		adminlog('添加视频播放器','添加视频播放器');
		cls_message::show('播放器添加完成','?entry=players&action=playersedit');
	
	}elseif(submitcheck('bplayersedit')){
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $plid){
				$db->query("DELETE FROM {$tblprefix}players WHERE plid=$plid");
				unset($playersnew[$plid]);
			}
		}
		foreach($playersnew as $plid => $playernew){
			$playernew['cname'] = empty($playernew['cname']) ? $players[$plid]['cname'] : $playernew['cname'];
			$playernew['exts'] = preg_match("/[^a-z,A-Z0-9]+/",$playernew['exts']) ? $players[$plid]['exts'] : strtolower($playernew['exts']);
			$playernew['available'] = empty($playernew['available']) ? 0 : $playernew['available'];
			$db->query("UPDATE {$tblprefix}players SET 
						cname='$playernew[cname]',
						exts='$playernew[exts]',
						available='$playernew[available]',
						vieworder='$playernew[vieworder]' 
						WHERE plid='$plid'");
		}
		cls_CacheFile::Update('players');
		adminlog('编辑视频播放器列表','编辑视频播放器列表');
		cls_message::show('播放器编辑完成','?entry=players&action=playersedit');
	}
}elseif($action == 'playerdetail' && !empty($plid)){
	empty($players[$plid]) && cls_message::show('请指定正确的播放器','?entry=players&action=playersedit');
	$player = cls_cache::Read('player',$plid);
	if(!submitcheck('bplayerdetail')){
		tabheader('播放器设置','playerdetail','?entry=players&action=playerdetail&plid='.$plid);
		trbasic('播放器名称','playernew[cname]',$player['cname'],'text');
		trbasic('播放器类型','',$ptypearr[$player['ptype']],'');
		trbasic('默认播放文件格式','playernew[exts]',$player['exts'],'text');
		echo "<tr class=\"txt\"><td class=\"txtL\">".'播放器模板'."</td><td class=\"txtL\"><textarea rows=\"25\" name=\"playernew[template]\" id=\"playernew[template]\" cols=\"100\">".mhtmlspecialchars(str_replace("\t","    ",$player['template']))."</textarea></td></tr>";
		tabfooter('bplayerdetail');
		a_guide('playerdetail');
	}else{
		if(!$playernew['template']) {
			cls_message::show('请输入播放器模板','?entry=players&action=playerdetail&plid='.$plid);
		}
		$playernew['cname'] = empty($playernew['cname']) ? $players[$plid]['cname'] : $playernew['cname'];
		$playernew['exts'] = preg_match("/[^a-z,A-Z0-9]+/",$playernew['exts']) ? $players[$plid]['exts'] : strtolower($playernew['exts']);
		$db->query("UPDATE {$tblprefix}players SET 
					cname='$playernew[cname]',
					exts='$playernew[exts]',
					template='$playernew[template]' 
					WHERE plid='$plid'");
		cls_CacheFile::Update('players');
		adminlog('详细修改视频播放器','详细修改视频播放器');
		cls_message::show('播放器修改完成','?entry=players&action=playersedit');

	}
}

?>
