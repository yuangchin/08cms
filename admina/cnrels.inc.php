<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('catalog')) cls_message::show($re);
foreach(array('cotypes','catalogs',) as $k) $$k = cls_cache::Read($k);
$action = empty($action) ? 'cnrelsedit' : $action;
$sourcearr = array(0 => '栏目');
foreach($cotypes as $k => $v) $sourcearr[$k] = $v['cname'];
if($action == 'cnreladd'){
	backnav('cata','cnrel');
	deep_allow($no_deepmode,M_REFERER);
	if(!submitcheck('bcnreladd')){
		tabheader('添加类目关联','cnreladd',"?entry=$entry&action=$action",2,0,1);
		trbasic('标题','cnrelnew[cname]','','text',array('validate'=>makesubmitstr('cnrelnew[cname]',1,0,4,30)));
		trbasic('备注','cnrelnew[remark]','','text',array('w' => 50));
		trbasic('主类系','cnrelnew[coid]',makeoption($sourcearr),'select');
		trbasic('从类系','cnrelnew[coid1]',makeoption($sourcearr),'select');
		tabfooter('bcnreladd','添加');
		a_guide('cnreladd');

	} else {
		if(!($cnrelnew['cname'] = trim(strip_tags($cnrelnew['cname'])))) cls_message::show('请输入标题！',M_REFERER);
		$cnrelnew['remark'] = trim(strip_tags($cnrelnew['remark']));
		$db->query("INSERT INTO {$tblprefix}cnrels SET 
				    rid=".auto_insert_id('cnrels').",
					cname='$cnrelnew[cname]', 
					remark='$cnrelnew[remark]', 
					coid='$cnrelnew[coid]', 
					coid1='$cnrelnew[coid1]'
					");
		$rid = $db->insert_id();
		adminlog('添加类目关联');
		cls_CacheFile::Update('cnrels');
		cls_message::show('类目关联添加成功！',"?entry=$entry&action=cnreldetail&rid=$rid");
	}
}elseif($action == 'cnreldetail' && $rid){
	backnav('cata','cnrel');
	if(!($cnrel = fetch_one($rid)) || !isset($sourcearr[$cnrel['coid']]) || !isset($sourcearr[$cnrel['coid1']])) cls_message::show('请指定正确的类目关联项目！');
	$cfgs = &$cnrel['cfgs'];
	$narr = $cnrel['coid'] ? cls_cache::Read('coclasses',$cnrel['coid']) : $catalogs;
	$narr1 = $cnrel['coid1'] ? cls_cache::Read('coclasses',$cnrel['coid1']) : $catalogs;
	$cotypes = cls_cache::Read('cotypes');
	$groupsets = isset($cotypes[$cnrel['coid1']]['groups']) ? $cotypes[$cnrel['coid1']]['groups'] : ''; 
	foreach($cfgs as $k => $v) if(!isset($narr[$k])) unset($cfgs[$k]);
	if(!submitcheck('bcnreldetail')){
		$submitstr = '';
		tabheader("类目关联详情&nbsp;&nbsp;[$cnrel[cname]]",'cnreldetail',"?entry=$entry&action=$action&rid=$rid",2,1,1);
		echo '<script type="text/javascript">var cata = [';
		foreach($narr as $caid => $catalog){
			$descrip = '';
			if(empty($cfgs[$caid])){
				$descrip = '(0)';
			}else{
				$cfgs[$caid] = array_filter(explode(',',$cfgs[$caid]));
				$descrip .= '('.count($cfgs[$caid]).')';
				$i = 0;
				foreach($cfgs[$caid] as $id){
					if(++ $i > 5){$descrip .= '..';break;}
					$descrip .= @$narr1[$id]['title'].',';
				}
			}
			echo "[$catalog[level],$caid,'" . str_replace("'","\\'",mhtmlspecialchars($catalog['title'])) . "','".str_replace("'","\\'",mhtmlspecialchars($descrip))."'],";
		}
		if($cnrel['coid']){
			cls_cache::Read('cotypes');
			$cotype = $cotypes[$cnrel['coid']];
			$treestep = empty($cotype['treestep']) ? '' : $cotype['treestep'];
		}else{
			$treestep = empty($mconfigs['treesteps']) ? '' : explode(',', $mconfigs['treesteps']);
			is_array($treestep) && $treestep = $treestep[0];
		}
		echo <<<DOT
];
document.write(tableTree({data:cata,step:'$treestep',html:{
		head: '<td class="txtC" width="30"><input class="checkbox" name="chkall" onclick="checkall(this.form, \'selectid\', \'chkall\')" type="checkbox"></td>'
			+ '<td class="txtC" width="40">ID</td>'
			+ '<td class="txtL" width="248"%code%>{$sourcearr[$cnrel['coid']]} %input%</td>'
			+ '<td class="txtL">关联{$sourcearr[$cnrel['coid1']]}</td>'
			+ '<td class="txtC" width="40">详情</td>',
		cell:[2,2],
		rows: '<td class="txtC" width="30"><input class="checkbox" name="selectid[%1%]" value="'
				+ '%1%" type="checkbox" onclick="tableTree.setChildBox()" /></td>'
			+ '<td class="txtC" width="40">%1%</td>'
			+ '<td class="txtL" width="248">%ico%%2%</td>'
			+ '<td class="txtL">%3%</td>'
			+ '<td class="txtC" width="40"><a id=\"{$actionid}_info_%1%\" href="?entry=$entry&action=viewdetail&rid=$rid&caid='
				+ '%1%" onclick="return showInfo(this.id,this.href)">详情</a></td>'
		},
	callback : true
}));
DOT;
		echo '</script>';

		if(empty($groupsets)){
			tabheader('操作项目');
			$ccidsarr = array();foreach($narr1 as $k => $v) $ccidsarr[$k] = $v['title'].'('.$v['level'].')';
			$cnmodearr = array(0 => '修改配置中设置',1 => '在原配置中添加',2 => '从原配置中移除',);
			trbasic('请选择'.$sourcearr[$cnrel['coid1']]."<br><input class=\"checkbox\" type=\"checkbox\" name=\"chkallccids\" onclick=\"checkall(this.form,'ccidsnew','chkallccids')\">全选",'',
			"<select id=\"cnmode\" name=\"cnmode\" style=\"vertical-align: middle;\">".makeoption($cnmodearr)."</select><br>".makecheckbox('ccidsnew[]',$ccidsarr,array(),5),'');
		}else{
			tabheader('操作项目 - 设置 关联'.$sourcearr[$cnrel['coid1']]);
			$garr = select_arr($groupsets); //
			$garr = $garr+array('0'=>'[未分组]项目',);
			$group = ''; $gdata = ''; $gids = ""; 
			$n = 0; $first = 0;
			$gtmp = array(); //print_r($garr);
			$cnmodearr = array(0 => '修改配置中设置',1 => '在原配置中添加',2 => '从原配置中移除',);
			$gdata .= "<select id=\"cnmode\" name=\"cnmode\" style=\"vertical-align: middle;\">".makeoption($cnmodearr)."</select>";
			foreach($garr as $g=>$t){
					$n++; if($n==1) $first = "$g"; 
					$gids .= ",$g";
					$group .= "\n<div id='gtitle_$g' style='border:1px solid #BBB; margin:5px 0px;cursor:pointer;' onmouseover=\"show_cgroup('$g')\">$t</div>";
					$gtmp[$g] = array();
					foreach($narr1 as $k => $v){
						if(!isset($v['groups'])) $v['groups'] = '';
						if($g=='0'){ //  if($g===0){
							if($v['groups']==''){ 
								$gtmp[$g][$k] = $v['title'];
							}
						}else{
							if(strlen($v['groups'])>0&&strstr(",$v[groups],",",$g,")){ 
								$gtmp[$g][$k] = $v['title'];
							}
						}
					}
					$gdline = "\n　*** <b>$t</b> <input class=\"checkbox\" type=\"checkbox\" name=\"chkallccids\" onclick=\"check_cgroup(this,'$g')\">全选当前组";
					$gdata .= "\n<div id='gdata_$g' style='border:1px solid #BBB; padding:5px; margin:5px 0px;display:xnone;'>$gdline<br>".makecheckbox('ccidsnew[]',$gtmp[$g],array(),6)."</div>";
			}
			echo '<tr>
			<td style="vertical-align:top" class="txt txtright fB" width="20%">'.$group.'</td>
			<td style="vertical-align:top" class="txt txtleft">'.$gdata.'</td></tr>';
			echo "<script type='text/javascript'> 
function show_cgroup(g){
	var tab = '$gids'.split(',');
	for(var i=1;i<tab.length;i++){
		var flag = (tab[i]==''+g) ? '' : 'none';
		if(tab[i]==''+g){
			\$id('gdata_'+tab[i]).style.display = '';
			\$id('gtitle_'+tab[i]).style.background = '#CCCCCC';
		}else{
			\$id('gdata_'+tab[i]).style.display = 'none';
			\$id('gtitle_'+tab[i]).style.background = '#FFFFFF';
		}
	}
}
function check_cgroup(e,g){
	var items = \$id('gdata_'+g).getElementsByTagName('input');
	var flag = e.checked; // ? 
	for(var i=0;i<items.length;i++){
		items[i].checked = flag;
	}
}
function init_cgroup(){
	var tab = '$gids'.split(',');
	var def = '$first'; var nmax = 0, imax = 0;
	for(var j=1;j<tab.length;j++){
		var imax = 0;
		var items = \$id('gdata_'+tab[j]).getElementsByTagName('input');
		for(var i=0;i<items.length;i++){
			//if(items[i].checked) 
			imax++;
		}
		if(imax>nmax){
			def = tab[j];
			nmax = imax;
		}
	}
	show_cgroup(def);
}
//show_cgroup('$first'); //打开第一个;
init_cgroup(); //打开最多的一个tab
</script>";

		}
		tabfooter('bcnreldetail','',"&nbsp; <a href=\"?entry=$entry\">返回</a>");
		a_guide('cnreldetail');
	}else{
		if(empty($selectid)) cls_message::show('请选择节点',M_REFERER);
		$ccidsnew = empty($ccidsnew) ? array() : array_filter($ccidsnew);
		foreach($selectid as $k){
			$cfgs[$k] = empty($cfgs[$k]) ? array() : array_filter(explode(',',$cfgs[$k]));
			$cfgs[$k] = !$cnmode ? $ccidsnew : ($cnmode == 1 ? array_unique(array_merge($cfgs[$k],$ccidsnew)) : array_diff($cfgs[$k],$ccidsnew));
			$cfgs[$k] = empty($cfgs[$k]) ? '' : implode(',',$cfgs[$k]);
		}
		$db->query("UPDATE {$tblprefix}cnrels SET cfgs='".(empty($cfgs) ? '' : addslashes(var_export($cfgs,TRUE)))."' WHERE rid='$rid'",'SILENT');
		cls_CacheFile::Update('cnrels');
		cls_message::show('类目关联编辑完成！',M_REFERER);
	}

}elseif($action == 'viewdetail' && $rid && $caid){
	if(!($cnrel = fetch_one($rid)) || !isset($sourcearr[$cnrel['coid']]) || !isset($sourcearr[$cnrel['coid1']])) cls_message::show('请指定正确的类目关联项目！');
	$narr = $cnrel['coid'] ? cls_cache::Read('coclasses',$cnrel['coid']) : $catalogs;
	$narr1 = $cnrel['coid1'] ? cls_cache::Read('coclasses',$cnrel['coid1']) : $catalogs;
	if(empty($narr[$caid])) cls_message::show('请指定正确的类目关联项目！');
	$cfg = empty($cnrel['cfgs'][$caid]) ? array() : array_filter(explode(',',$cnrel['cfgs'][$caid]));
	tabheader($narr[$caid]['title'].'关联'.$sourcearr[$cnrel['coid1']]);
	$str = '';$i = 0;
	foreach($cfg as $id){
		$str .= $narr1[$id]['title'];
		$str .= ++ $i % 5 ? ',' : '<br>';
	}
	trbasic($sourcearr[$cnrel['coid1']].'<br>('.count($cfg).')','',$str,'');
	tabfooter();
}elseif($action == 'cnrelsedit'){
	backnav('cata','cnrel');
	$cnrels = fetch_arr();
	if(!submitcheck('bcnrelsedit')){
		tabheader('类目关联管理'."&nbsp; &nbsp; >><a href=\"?entry=$entry&action=cnreladd\">添加</a>",'cnrelsedit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID',array('标题','txtL'),array('备注','txtL'),'关联','排序','删除','详情'));
		foreach($cnrels as $k => $v){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"20\" maxlength=\"30\" name=\"cnrelsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"50\" maxlength=\"50\" name=\"cnrelsnew[$k][remark]\" value=\"$v[remark]\"></td>\n".
				"<td class=\"txtC\">".($sourcearr[$v['coid']].'=>'.$sourcearr[$v['coid1']])."</td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"cnrelsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=cnreldel&rid=$k\">删除</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=cnreldetail&rid=$k\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bcnrelsedit','修改');
		a_guide('cnrelsedit');
	}else{
		if(isset($cnrelsnew)){
			foreach($cnrelsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['remark'] = trim(strip_tags($v['remark']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $cnrels[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$db->query("UPDATE {$tblprefix}cnrels SET cname='$v[cname]',remark='$v[remark]',vieworder='$v[vieworder]' WHERE rid='$k'");
			}
			adminlog('edit_cnrel_list');
			cls_CacheFile::Update('cnrels');
		}
		cls_message::show('类目关联编辑完成！',"?entry=$entry&action=$action");
	}
}elseif($action == 'cnreldel' && $rid){
	backnav('cata','cnrel');
	deep_allow($no_deepmode,"?entry=$entry&action=cnrelsedit");
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=cnreldel&rid=$rid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=cnrelsedit>返回</a>";
		cls_message::show($message);
	}
	$db->query("DELETE FROM {$tblprefix}cnrels WHERE rid='$rid'");
	adminlog('del_cnrel');
	cls_CacheFile::Update('cnrels');
	cls_message::show('类目关联成功删除！', "?entry=$entry&action=cnrelsedit");
}else cls_message::show('错误的文件参数');

function fetch_one($rid){
	global $db,$tblprefix;
	$item = $db->fetch_one("SELECT * FROM {$tblprefix}cnrels WHERE rid='$rid'");
	foreach(array('cfgs',) as $var) $item[$var] = $item[$var] && is_array($arr = varexp2arr($item[$var])) ? $arr : array();
	return $item;
}
function fetch_arr(){
	global $db,$tblprefix;
	$items = array();
	$query = $db->query("SELECT * FROM {$tblprefix}cnrels ORDER BY vieworder,rid");
	while($item = $db->fetch_array($query)){
		foreach(array('cfgs',) as $var) $item[$var] = $item[$var] && is_array($arr = varexp2arr($item[$var])) ? $arr : array();
		$items[$item['rid']] = $item;
	}
	return $items;
}

?>
