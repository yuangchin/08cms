<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
foreach(array('cotypes','bnames','mtpls','o_mtpls','channels',) as $k) $$k = cls_cache::Read($k);

empty($action) && $action = 'mtplsedit';
$tpclasses = cls_mtpl::ClassArray(1);
$true_tpldir = cls_tpl::TemplateTypeDir('tpl');
mmkdir($true_tpldir);
if($action == 'mtpladd'){
	echo "<title>添加手机版模板</title>";
	if(!submitcheck('bmtpladd') && !submitcheck('bmtplsave')){
		$tpclass = empty($tpclass) ? 'index' : $tpclass;
		if(submitcheck('bmtplsearch')){
			$mtplstmp = findfiles($true_tpldir);
			$enamearr = array_merge(array_keys($mtpls),array_keys($o_mtpls));
			foreach($mtplstmp as $k => $tplname){
				if(in_array($tplname,$enamearr)) unset($mtplstmp[$k]);
			}
			empty($mtplstmp) && cls_message::show('没有搜索到需要入库的模板文件', "?entry=$entry&action=mtpladd");
			$in_search = 1;
		}
		tabheader("添加手机版模板&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"button\" type=\"submit\" name=\"bmtplsearch\" value=\"自动搜索\">",'mtpladd',"?entry=$entry&action=$action&tpclass=$tpclass");
		trbasic('模板名称','mtpladd[cname]');
		trbasic('模板类型','mtpladd[tpclass]',makeoption($tpclasses,$tpclass),'select');
		trbasic('模板文件','mtpladd[tplname]','','text',array('guide' => "文件名称只允许包含字母、数字、下划线(-_)、点(.)等字符,并以htm或html为扩展名"));
		tabfooter('bmtpladd','添加');
		if(!empty($in_search)){
			tabheader('手机版模板添加入库','mtplsave',"?entry=$entry&action=$action&tpclass=$tpclass",'4');
			trcategory(array('<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)">全选',array('模板文件','txtL'),'设置模板名称','设置模板类型'));
			foreach($mtplstmp as $tplname){
				if(_08_FilesystemFile::CheckFileName($tplname)) continue;
				echo "<tr class=\"txt\">".
					"<td class=\"txtC w45\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$tplname]\" value=\"$tplname\">\n".
					"<td class=\"txtL\">$tplname</td>\n".
					"<td class=\"txtC\"><input type=\"text\" size=\"30\" name=\"mtplsnew[$tplname][cname]\" value=\"\"></td>\n".
					"<td class=\"txtC w150\"><select style=\"vertical-align: middle;\" name=\"mtplsnew[$tplname][tpclass]\">".makeoption($tpclasses,$tpclass)."</select></td></tr>";
			}
			tabfooter('bmtplsave','入库');
		}
		a_guide('mtpladd');
	}elseif(submitcheck('bmtpladd')){
		if(empty($mtpladd['cname'])) cls_message::show('请输入模板名称',M_REFERER);
		if($re = _08_FilesystemFile::CheckFileName($mtpladd['tplname'])) cls_message::show($re,M_REFERER);
		$enamearr = array_merge(array_keys($mtpls),array_keys($o_mtpls));
		if(in_array($mtpladd['tplname'], $enamearr)) cls_message::show('页面模板重复定义',M_REFERER);
		if(!is_file($true_tpldir.'/'.$mtpladd['tplname'])){
			if(@!touch($true_tpldir.'/'.$mtpladd['tplname'])) cls_message::show('模板文件添加失败!',M_REFERER);
		}
		$o_mtpls[$mtpladd['tplname']] = array('cname' => stripslashes($mtpladd['cname']),'tpclass' => $mtpladd['tpclass']);
		cls_CacheFile::Save($o_mtpls,'o_mtpls','o_mtpls');
		adminlog('添加手机版模板');
		cls_message::show('模板添加完成',axaction(6,"?entry=$entry&action=mtplsedit&tpclass=$mtpladd[tpclass]"));
	}elseif(submitcheck('bmtplsave')){
		if(!empty($selectid)){
			foreach($selectid as $tplname){
				if(_08_FilesystemFile::CheckFileName($tplname)) continue;
				if(!empty($mtplsnew[$tplname]['cname']) && !empty($mtplsnew[$tplname]['tpclass'])){
					$cname = $mtplsnew[$tplname]['cname'];
					$tpclass = $mtplsnew[$tplname]['tpclass'];
					$o_mtpls[$tplname] = array('cname' => stripslashes($mtplsnew[$tplname]['cname']),'tpclass' => $mtplsnew[$tplname]['tpclass']);
				}
			}
		}
		cls_CacheFile::Save($o_mtpls,'o_mtpls','o_mtpls');
		adminlog('添加手机版模板');
		cls_message::show('模板入库完成',axaction(6,"?entry=$entry&action=mtplsedit&tpclass=$tpclass"));
	}
}elseif($action == 'mtplsedit'){
	echo "<title>手机版模板</title>";
	empty($tpclass) && $tpclass = 'index';
	backnav('mobile','mtpls');
	$tpclassarr = array();
	foreach($tpclasses as $k => $v){
		$tpclassarr[] = $tpclass == $k ? "<b>-$v-</b>" : "<a href=\"?entry=$entry&action=$action&tpclass=$k\">$v</a>";
	}
	echo tab_list($tpclassarr,10,0);
	if(!submitcheck('bmtplsedit')){
		if($tplbase = cls_env::GetG('templatebase')){ $tips = "<li>※提示：当前处于扩展模版,继承自基础模版[$tplbase]；基础模版不能删除,可从基础模版[扩展]到当前模板。</li>"; echo "<div style='color:red'>$tips</div>"; }
		$_add = empty($templatebase) ? "&nbsp; &nbsp; <a href=\"?entry=$entry&action=mtpladd&tpclass=$tpclass\" onclick=\"return floatwin('open_mtplsedit',this)\">>>添加</a>" : '';
		tabheader('手机版模板 - '.$tpclasses[$tpclass].$_add,'mtplsedit',"?entry=$entry&action=mtplsedit&tpclass=$tpclass",'9');
		$_copy = empty($templatebase) ? '复制' : '扩展';
		trcategory(array('序号',array('模板名称','txtL'),array('模板文件','txtL'),'<input class="checkbox" type="checkbox" name="chkall" onclick="deltip(this,0,checkall,this.form)">删?',$_copy,'内容'));
		$ii = 0;
		foreach($o_mtpls as $k => $v){
			if($tpclass == $v['tpclass']){
				echo "<tr class=\"txt\">".
					"<td class=\"txtC w40\">".++$ii."</td>\n".
					"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"mtplsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n";
				if(empty($templatebase)){
					echo "<td class=\"txtL\">$k</td>\n".
						"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\">\n".
						"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=mtplcopy&tplname=$k\" onclick=\"return floatwin('open_mtplsedit',this)\">复制</a></td>\n".
						"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=mtpldetail&tplname=$k\" onclick=\"return floatwin('open_mtplsedit',this)\">编辑</a></td></tr>\n";
				}elseif(!empty($templatebase)&&!file_tplexists($k)){
					//无扩展模板
					echo "<td class=\"txtL\">$k</td>\n".
						"<td class=\"txtC w40 tips1\">--</td>\n".
						"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=basic2extend&tplname=$k\" onclick=\"return floatwin('open_mtplsedit',this)\">扩展</a></td>\n".
						"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=mtpldetail&tplname=$k&isbase=1\" onclick=\"return floatwin('open_mtplsedit',this)\">编辑</a></td></tr>\n";
				}elseif(!empty($templatebase)&&file_tplexists($k)){
					//有扩展模板
					echo "<td class=\"txtL\">$k</td>\n".
						"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\">\n".
						"<td class=\"txtC w30 tips1\">扩展</td>\n".
						"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=mtpldetail&tplname=$k\" onclick=\"return floatwin('open_mtplsedit',this)\">编辑</a></td></tr>\n";
				}
			}
		}
		tabfooter('bmtplsedit','修改');
		a_guide("mtplsedit$tpclass");
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				if(!empty($templatebase)){
					$tname = cls_tpl::rel_path($k,'dir');
					file_exists($tname) && unlink($tname);
				}else {
					unset($mtplsnew[$k], $o_mtpls[$k]);
				}
			}
		}
		if(!empty($mtplsnew)){
			foreach($mtplsnew as $k => $v){
				$v['cname'] = empty($v['cname']) ? $mtpls[$k]['cname'] : $v['cname'];
				if($v['cname'] != $o_mtpls[$k]['cname']) $o_mtpls[$k]['cname'] = stripslashes($v['cname']);
			}
		}
		cls_CacheFile::Save($o_mtpls,'o_mtpls','o_mtpls');
		adminlog('编辑手机版模板管理列表');
		cls_message::show('模板修改完成',"?entry=$entry&action=mtplsedit&tpclass=$tpclass");
	}
}
elseif($action == 'mtpldetail' && $tplname){
	echo "<title>页面模板编辑</title>";
	if($re = _08_FilesystemFile::CheckFileName($tplname)) cls_message::show($re);
	if(!($mtpl = $o_mtpls[$tplname])) cls_message::show('指定的模板不存在');
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.rawurlencode($forward);
	$isbasestr = empty($isbase) ? '' : '&isbase=1';
	if(!submitcheck('bmtpldetail')){
		$template = cls_tpl::load($tplname,0);
		tabheader("模板设置 - {$mtpl['cname']} - {$tplname}",'mtpldetail',"?entry=$entry&action=mtpldetail&tplname=$tplname$isbasestr$forwardstr");
		trbasic('模板类别','mtplnew[tpclass]',makeoption($tpclasses,$mtpl['tpclass']),'select');
		templatebox('页面模板','templatenew',$template,30,110);
		tabfooter('bmtpldetail');
		a_guide('mtpldetail');
	}
	else{
		@str2file(stripslashes($templatenew),cls_tpl::rel_path($tplname,'get'));
		$o_mtpls[$tplname]['tpclass'] = $mtplnew['tpclass'];
		cls_CacheFile::Save($o_mtpls,'o_mtpls','o_mtpls');
		adminlog('详细修改手机版模板');
		cls_message::show('模板修改完成',axaction(6,$forward));
	}
}
elseif($action == 'mtplcopy' && $tplname){
	echo "<title>复制页面模板</title>";
	if($re = _08_FilesystemFile::CheckFileName($tplname)) cls_message::show($re);
	if(!($mtpl = $o_mtpls[$tplname])) cls_message::show('指定的模板不存在');
	if(!submitcheck('bmtplcopy')){
		!is_file($true_tpldir.'/'.$tplname) && cls_message::show('指定的源模板文件不存在');
		tabheader('复制手机版模板','mtplcopy',"?entry=$entry&action=mtplcopy&tplname=$tplname");
		trbasic('模板名称','mtpladd[cname]');
		trbasic('模板类别','mtpladd[tpclass]',makeoption($tpclasses,$mtpl['tpclass']),'select');
		trbasic('源模板文件','',$tplname,'');
		trbasic('模板文件另存为','mtpladd[tplname]','','text',array('guide' => "文件名称只允许包含字母、数字、下划线(-_)、点(.)等字符,并以htm或html为扩展名"));
		tabfooter('bmtplcopy');
		a_guide('mtplcopy');
	}else{
		if(empty($mtpladd['cname'])) cls_message::show('请输入模板名称',M_REFERER);
		if($re = _08_FilesystemFile::CheckFileName($mtpladd['tplname'])) cls_message::show($re);
		$mtplsnew = findfiles($true_tpldir);
		in_array($mtpladd['tplname'],$mtplsnew) && cls_message::show('指定的模板文件名称重复',M_REFERER);
		!copy($true_tpldir.'/'.$tplname,$true_tpldir.'/'.$mtpladd['tplname']) && cls_message::show('模板复制失败',M_REFERER);
		$o_mtpls[$mtpladd['tplname']] = array('cname' => stripslashes($mtpladd['cname']),'tpclass' => $mtpladd['tpclass']);
		cls_CacheFile::Save($o_mtpls,'o_mtpls','o_mtpls');
		adminlog('复制手机版模板');
		cls_message::show('模板复制完成',axaction(6,"?entry=$entry&action=mtplsedit"));
	}
}elseif($action == 'basic2extend' && $tplname){
    $msg = rtag_basic2extend($tplname) ? '模板扩展完成' : '基础模板原文件不存在';
    cls_message::show($msg,axaction(6,"?entry=$entry&action=mtplsedit"));
}