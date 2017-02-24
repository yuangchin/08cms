<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
cls_cache::Load('bnames');
cls_cache::Load('sptpls');
$dbtpls = fetch_arr();
$true_tpldir = M_ROOT."./template/$templatedir";
mmkdir($true_tpldir);
if($action == 'sptplsedit'){
	backnav('tpls','futpl');
	if(!submitcheck('bsptplsedit')) {
		tabheader('特定功能页面管理','sptplsedit',"?entry=sptpls&action=sptplsedit",'5');
		trcategory(array('序号','页面名称','调用链接','模板文件','内容'));
		$no = 0;
		foreach($dbtpls as $k => $v){
			$no ++;
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$no</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtL\">$v[link]</td>\n".
				"<td class=\"txtC\"><input type=\"text\" size=\"20\" name=\"sptplsnew[$k][tplname]\" value=\"".(empty($sptpls[$k]) ? '' : $sptpls[$k])."\"></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=sptpls&action=sptpldetail&spid=$k\" onclick=\"return floatwin('open_sptplsedit',this)\">编辑</a></td></tr>\n";
		}
		tabfooter('bsptplsedit','修改');
		a_guide('sptplsedit');
	}else{
		$sptpls = array();
		foreach($dbtpls as $k => $v){
			$sptplsnew[$k]['tplname'] = trim($sptplsnew[$k]['tplname']);
			if(preg_match("/[^a-z_A-Z0-9\.]+/",$sptplsnew[$k]['tplname'])) $sptplsnew[$k]['tplname'] = '';
			$sptpls[$k] = $sptplsnew[$k]['tplname'];
		}
		cls_CacheFile::Save($sptpls,'sptpls','sptpls');
		adminlog('编辑特定功能模板管理列表');
		cls_message::show('页面修改完成', "?entry=sptpls&action=sptplsedit");
	}
}
elseif($action == 'sptpldetail' && $spid){
	$dbtpl = $dbtpls[$spid];
	$tplname = empty($sptpls[$spid]) ? '' : $sptpls[$spid];
	if(!submitcheck('bsptpldetail')){
		if(empty($tplname) || !is_file($true_tpldir.'/'.$tplname)){
			if(@!touch($true_tpldir.'/'.$tplname)) cls_message::show('没有定义模板或模板不存在!',axaction(2,M_REFERER));
		}
		$template = cls_tpl::load($tplname,0);
		tabheader('特定功能模板设置'.'-'.$dbtpl['cname'],'sptpldetail',"?entry=sptpls&action=sptpldetail&spid=$spid");
		trbasic('模板内容','',$tplname,'');
		templatebox('模板内容','templatenew',$template,30,110);
		tabfooter('bsptpldetail','修改');
		a_guide('sptpldetail');
	}else{
		empty($templatenew) && cls_message::show('模板内容不能为空',"?entry=sptpls&action=sptplsedit");
		!str2file(stripslashes($templatenew),$true_tpldir.'/'.$tplname) && cls_message::show('模板保存时发生错误',"?entry=sptpls&action=sptplsedit");
		adminlog('详细修改特定功能模板');
		cls_message::show('模板修改完成',axaction(6,"?entry=sptpls&action=sptplsedit"));
	}
}
function fetch_arr(){
	global $db,$tblprefix;
	$items = array();
	$query = $db->query("SELECT * FROM {$tblprefix}sptpls ORDER BY vieworder");
	while($item = $db->fetch_array($query)){
		$items[$item['ename']] = $item;
	}
	return $items;
}

?>
