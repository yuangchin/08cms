<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
foreach(array('channels','fchannels','mchannels','matypes',) as $k) $$k = cls_cache::Read($k);
$bclass = empty($bclass) ? 'common' : $bclass;
$isApp = true;//是否应用阶段

aheader();
$bclasses = array(
	'common' => '通用信息',
	'archive' => '文档相关',
	'cnode' => '类目相关',
	'freeinfo' => '副件相关',
	'commu' => '交互相关',
	'member' => '会员相关',
	'other' => '其它',
);
$datatypearr = array(
	'text' => '单行文本',
	'multitext' => '多行文本',
	'htmltext' => 'Html文本',
	'image' => '单图',
	'images' => '图集',
	'flash' => 'Flash',
	'flashs' => 'Flash集',
	'media' => '视频',
	'medias' => '视频集',
	'file' => '单点下载',
	'files' => '多点下载',
	'select' => '单项选择',
	'mselect' => '多项选择',
	'cacc' => '类目选择',
	'date' => '日期(时间戳)',
	'int' => '整数',
	'float' => '小数',
	'map' => '地图',
	'vote' => '投票',
	'texts' => '文本集',
);
if(empty($action)){	
	echo '<div class="itemtitle"><h3>原始标识管理</h3></div>';

	$arr = array();
	foreach($bclasses as $k => $v) $arr[] = $bclass == $k ? "<b>-$v-</b>" : "<a href=\"?entry=btagnames&bclass=$k\">$v</a>";
	echo tab_list($arr,count($bclasses),0);

	$sclasses = array();
	if($bclass == 'archive'){
		foreach($channels as $chid => $channel){
			$sclasses[$chid] = $channel['cname'];
		}
	}elseif($bclass == 'cnode'){
		$sclasses = array(
			'catalog' => '栏目',
			'coclass' => '分类',
		);
	}elseif($bclass == 'freeinfo'){
		foreach($fchannels as $chid => $channel){
			$sclasses[$chid] = $channel['cname'];
		}
	}elseif($bclass == 'member'){
		foreach($mchannels as $chid => $channel){
			$sclasses[$chid] = $channel['cname'];
		}
	}elseif($bclass == 'commu'){
		$commus = cls_cache::Read('commus');
		$sclasses = $isApp ? array() : array('' => '通用标识');
		foreach($commus as $v){
			$sclasses[$v['cuid']] = $v['cname'];
		}
	}elseif($bclass == 'other'){
		$sclasses = array(
			'mp' => '分页',
			'attachment' => '附件',
			'vote' => '投票',
		);
	}
	
	if(!submitcheck('bbtagnamesedit') && !submitcheck('bbtagnamesadd')){
		tabheader("添加$bclasses[$bclass]标识名称",'btagnamesadd',"?entry=btagnames&bclass=$bclass");
		trbasic('标识名称','btagnameadd[cname]','', 'text', array('validate' => ' onfocus="initPinyin(\'btagnameadd[ename]\')"'));
		trbasic('英文名称','btagnameadd[ename]','', 'text', array('addstr' => ' <input type="button" value="自动拼音" onclick="autoPinyin(\'btagnameadd[cname]\',\'btagnameadd[ename]\')" />'));
		trbasic('字段类型','btagnameadd[datatype]',makeoption($datatypearr),'select');
		in_array($bclass,array('commu','other')) && trbasic('标识类别','btagnameadd[sclass]',makeoption($sclasses),'select');
		tabfooter('bbtagnamesadd','添加');
	
		tabheader("$bclasses[$bclass]标识",'btagnamesedit',"?entry=btagnames&bclass=$bclass",'6');
		trcategory(array('<input class="checkbox" type="checkbox" name="chkall" onclick="deltip(this,0,checkall,this.form)">删?','标识名称','英文名称','类型','字段类型','排序'));
		$query = $db->query("SELECT * FROM {$tblprefix}btagnames WHERE bclass='$bclass' ORDER BY sclass,vieworder,bnid");
		while($btagname = $db->fetch_array($query)){
			$sclassstr = '';
			if(in_array($bclass,array('commu','other'))){
				if($isApp){
					$sclassstr .= empty($btagname['sclass']) ? '通用标识':"<select style=\"vertical-align: middle;\" name=\"btagnamesnew[$btagname[bnid]][sclass]\">".makeoption($sclasses,$btagname['sclass'])."</select>";
				} else {
					$sclassstr .= "<select style=\"vertical-align: middle;\" name=\"btagnamesnew[$btagname[bnid]][sclass]\">".makeoption($sclasses,$btagname['sclass'])."</select>";
				}				
			} else {
				$sclassstr .= "-";
			}			
			$datatypestr = "<select style=\"vertical-align: middle;\" name=\"btagnamesnew[$btagname[bnid]][datatype]\">".makeoption($datatypearr,$btagname['datatype'])."</select>";
			echo "<tr align=\"center\">".
				"<td class=\"item1\" width=\"50\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$btagname[bnid]]\" value=\"$btagname[bnid]\" onclick=\"deltip()\"></td>\n".
				"<td class=\"item2\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"btagnamesnew[$btagname[bnid]][cname]\" value=\"$btagname[cname]\"></td>\n".
				"<td class=\"item1\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"btagnamesnew[$btagname[bnid]][ename]\" value=\"$btagname[ename]\"></td>\n".
				"<td class=\"item2\" width=\"100\">$sclassstr</td>\n".
				"<td class=\"item1\" width=\"100\">$datatypestr</td>\n".
				"<td class=\"item2\" width=\"100\"><input type=\"text\" size=\"5\" maxlength=\"5\" name=\"btagnamesnew[$btagname[bnid]][vieworder]\" value=\"$btagname[vieworder]\"></td>\n".
				"</tr>\n";
		}
		tabfooter('bbtagnamesedit','修改');
	}
	elseif(submitcheck('bbtagnamesadd')){
		if(!$btagnameadd['cname'] || !$btagnameadd['ename']) {
			cls_message::show('数据丢失',"?entry=btagnames&bclass=$bclass");
		}
		if(preg_match("/[^a-z_A-Z0-9]+/",$btagnameadd['ename'])){
			cls_message::show('请输入合法的标识id!',"?entry=btagnames&bclass=$bclass");
		}
		$btagnameadd['sclass'] = empty($btagnameadd['sclass']) ? '' : $btagnameadd['sclass'];
		$db->query("INSERT INTO {$tblprefix}btagnames SET 
					cname='$btagnameadd[cname]',
					ename='$btagnameadd[ename]',
					datatype='$btagnameadd[datatype]',
					bclass='$bclass',
					sclass='$btagnameadd[sclass]'
					");
		updatethiscache();
		cls_message::show('标识添加成功!',"?entry=btagnames&bclass=$bclass");
	
	}
	elseif(submitcheck('bbtagnamesedit')){
		if(isset($delete)){
			foreach($delete as $bnid){
				$db->query("DELETE FROM {$tblprefix}btagnames WHERE bnid=$bnid");
				unset($btagnamesnew[$bnid]);
			}
		}
		foreach($btagnamesnew as $bnid => $btagnamenew){
			$btagnamenew['sclass'] = empty($btagnamenew['sclass']) ? '' : $btagnamenew['sclass'];
			$db->query("UPDATE {$tblprefix}btagnames SET 
						cname='$btagnamenew[cname]',
						ename='$btagnamenew[ename]',
						datatype='$btagnamenew[datatype]',
						vieworder='$btagnamenew[vieworder]',
						sclass='$btagnamenew[sclass]'
						WHERE bnid='$bnid'");
		}
		updatethiscache();
		cls_message::show('标识修改成功!',"?entry=btagnames&bclass=$bclass");
	}

}

function updatethiscache(){
	global $db,$tblprefix;
	$items = array();
	$query = $db->query("SELECT * FROM {$tblprefix}btagnames ORDER BY bclass,sclass,vieworder,bnid");
	while($item = $db->fetch_array($query)){
		$items[$item['bnid']] = array('ename' => $item['ename'],'cname' => $item['cname'],'bclass' => $item['bclass'],'sclass' => $item['sclass'],'datatype' => $item['datatype'],);
	}
	$cacstr = var_export($items,TRUE);
	if($fp = fopen(_08_SYSCACHE_PATH.'btagnames.cac.php','wb')){
		fwrite($fp,"<?php\n\$btagnames = $cacstr ;\n?>");
		fclose($fp);
	}
}
?>
