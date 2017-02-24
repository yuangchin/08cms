<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
foreach(array('cotypes','bnames','mtpls','o_mtpls','channels',) as $k) $$k = cls_cache::Read($k);
empty($action) && $action = 'mtplsedit';
$tpclasses = cls_mtpl::ClassArray();
$true_tpldir = cls_tpl::TemplateTypeDir('tpl');
mmkdir($true_tpldir);
if($action == 'mtpladd'){
	echo "<title>添加常规模板</title>";
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
		tabheader("添加常规模板&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"button\" type=\"submit\" name=\"bmtplsearch\" value=\"自动搜索\">",'mtpladd',"?entry=$entry&action=$action&tpclass=$tpclass");
		trbasic('模板名称','mtpladd[cname]');
		trbasic('模板类型','mtpladd[tpclass]',makeoption($tpclasses,$tpclass),'select');
		if($tpclass == 'archive') trbasic('单文档设置时可选','mtpladd[chid]',makeoption(array(0 => '请选择') + cls_channel::chidsarr(1),0),'select',array('guide'=>'为单个文档指定内容页模板时，只有上面所选模型的文档才可以选择此模板',));
		trbasic('模板文件','mtpladd[tplname]','','text',array('guide' => "文件名称只允许包含字母、数字、下划线(-_)、点(.)等字符,并以htm或html为扩展名"));
		tabfooter('bmtpladd','添加');
		if(!empty($in_search)){
			tabheader('常规模板添加入库','mtplsave',"?entry=$entry&action=$action&tpclass=$tpclass",'4');
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
		echo "<script>\$('select').mousedown(function(event){event.stopPropagation();});</script>";
		a_guide('mtpladd');
	}elseif(submitcheck('bmtpladd')){
		if(empty($mtpladd['cname'])) cls_message::show('请输入模板名称',M_REFERER);
		if($re = _08_FilesystemFile::CheckFileName($mtpladd['tplname'])) cls_message::show($re,M_REFERER);
		$enamearr = array_merge(array_keys($mtpls),array_keys($o_mtpls));
		if(in_array($mtpladd['tplname'], $enamearr)) cls_message::show('页面模板重复定义',M_REFERER);
		if(!is_file($true_tpldir.$mtpladd['tplname'])){
			if(@!touch($true_tpldir.$mtpladd['tplname'])) cls_message::show('模板文件添加失败!',M_REFERER);
		}
		$mtpls[$mtpladd['tplname']] = array('cname' => stripslashes($mtpladd['cname']),'tpclass' => $mtpladd['tpclass']);
		if($mtpladd['tpclass'] == 'archive'){
			if($mtpladd['chid'] = max(0,intval($mtpladd['chid']))){
				$mtpls[$mtpladd['tplname']]['chid'] = $mtpladd['chid'];
			}else unset($mtpls[$mtpladd['tplname']]['chid']);
		}
		cls_CacheFile::Save($mtpls,'mtpls','mtpls');
		adminlog('添加常规模板');
		cls_message::show('模板添加完成',axaction(6,"?entry=$entry&action=mtplsedit&tpclass=$mtpladd[tpclass]"));
	}elseif(submitcheck('bmtplsave')){
		if(!empty($selectid)){
			foreach($selectid as $tplname){
				if(_08_FilesystemFile::CheckFileName($tplname)) continue;
				if(!empty($mtplsnew[$tplname]['cname']) && !empty($mtplsnew[$tplname]['tpclass'])){
					$cname = $mtplsnew[$tplname]['cname'];
					$tpclass = $mtplsnew[$tplname]['tpclass'];
					$mtpls[$tplname] = array('cname' => stripslashes($mtplsnew[$tplname]['cname']),'tpclass' => $mtplsnew[$tplname]['tpclass']);
				}
			}
		}
		cls_CacheFile::Save($mtpls,'mtpls','mtpls');
		adminlog('添加常规模板');
		cls_message::show('模板入库完成',axaction(6,"?entry=$entry&action=mtplsedit&tpclass=$tpclass"));
	}
}
elseif($action == 'mtplsedit'){
	echo "<title>常规页面模板</title>";
	empty($tpclass) && $tpclass = 'index';
	backnav('tpl','retpl');
	$tpclassarr = array();
	foreach($tpclasses as $k => $v){
		$tpclassarr[] = $tpclass == $k ? "<b>-$v-</b>" : "<a href=\"?entry=$entry&action=$action&tpclass=$k\">$v</a>";
	}
	echo tab_list($tpclassarr,10,0);
	if(!submitcheck('bmtplsedit')){
		if($tplbase = cls_env::GetG('templatebase')){ $tips = "<li>※提示：当前处于扩展模版,继承自基础模版[$tplbase]；基础模版不能删除,可从基础模版[扩展]到当前模板。</li>"; echo "<div style='color:red'>$tips</div>"; }
		$_add = empty($templatebase) ? "&nbsp; &nbsp; <a href=\"?entry=$entry&action=mtpladd&tpclass=$tpclass\" onclick=\"return floatwin('open_mtplsedit',this)\">>>添加</a>" : '';
		tabheader('常规页面 - '.$tpclasses[$tpclass].$_add,'mtplsedit',"?entry=$entry&action=mtplsedit&tpclass=$tpclass",'9');
		$_copy = empty($templatebase) ? '复制' : '扩展';
		trcategory(array('序号',array('模板名称','txtL'),$tpclass == 'archive' ? array('单文档设置时可选','txtL') : '',array('模板文件','txtL'),'<input class="checkbox" type="checkbox" name="chkall" onclick="deltip(this,0,checkall,this.form)">删?',$_copy,'内容'));
		$ii = 0;
		foreach($mtpls as $k => $v){
			if($tpclass == $v['tpclass']){
				echo "<tr class=\"txt\">".
					"<td class=\"txtC w40\">".++$ii."</td>\n".
					"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"mtplsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n";
				if($tpclass == 'archive'){
					echo "<td class=\"txtL\">".(empty($channels[@$v['chid']]) ? '-' : $v['chid'].'-'.$channels[$v['chid']]['cname'])."</td>\n";
				}
				if(empty($templatebase)){
					echo "<td class=\"txtL\">$k</td>\n".
						"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
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
						"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
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
					unset($mtplsnew[$k], $mtpls[$k]);
				}
			}
		}
		if(!empty($mtplsnew)){
			foreach($mtplsnew as $k => $v){
				$v['cname'] = empty($v['cname']) ? $mtpls[$k]['cname'] : $v['cname'];
				if($v['cname'] != $mtpls[$k]['cname']) $mtpls[$k]['cname'] = stripslashes($v['cname']);
			}
		}
		cls_CacheFile::Save($mtpls,'mtpls','mtpls');
		adminlog('编辑常规模板管理列表');
		cls_message::show('模板修改完成',"?entry=$entry&action=mtplsedit&tpclass=$tpclass");
	}
}
elseif($action == 'mtpldetail' && $tplname){
	echo "<title>页面模板编辑</title>";
	if($re = _08_FilesystemFile::CheckFileName($tplname)) cls_message::show($re);
	if(!($mtpl = $mtpls[$tplname])) cls_message::show('指定的模板不存在');
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.rawurlencode($forward);
	$isbasestr = empty($isbase) ? '' : '&isbase=1';
	if(!submitcheck('bmtpldetail')){
		$template = cls_tpl::load($tplname,0);
		tabheader("模板设置 - {$mtpl['cname']} - {$tplname}",'mtpldetail',"?entry=$entry&action=mtpldetail&tplname=$tplname$isbasestr$forwardstr");
		trbasic('模板类别','mtplnew[tpclass]',makeoption($tpclasses,$mtpl['tpclass']),'select');
		if($mtpl['tpclass'] == 'archive') trbasic('单文档设置时可选','mtplnew[chid]',makeoption(array(0 => '请选择') + cls_channel::chidsarr(1),empty($mtpl['chid']) ? 0 : $mtpl['chid']),'select',array('guide'=>'指定单个文档的内容页模板时，只有所选模型的文档才可以选择此模板。',));
		templatebox('页面模板','templatenew',$template,28,110);
		tabfooter('bmtpldetail');
		a_guide('mtpldetail');
	}
	else{
		// 不管是否有扩展模版,这里都用cls_tpl::rel_path默认定位到当前模版目录; 如果url中isbase=1则定位到基础模版
		@str2file(stripslashes($templatenew),cls_tpl::rel_path($tplname,'get'));
		$mtpls[$tplname]['tpclass'] = $mtplnew['tpclass'];
		if($mtplnew['tpclass'] == 'archive'){
			if($mtplnew['chid'] = max(0,intval($mtplnew['chid']))){
				$mtpls[$tplname]['chid'] = $mtplnew['chid'];
			}else unset($mtpls[$tplname]['chid']);
		}
		cls_CacheFile::Save($mtpls,'mtpls','mtpls');
		adminlog('详细修改常规模板');
		cls_message::show('模板修改完成',axaction(6,$forward));
	}
}
elseif($action == 'mtplcopy' && $tplname){
	echo "<title>复制页面模板</title>";
	if($re = _08_FilesystemFile::CheckFileName($tplname)) cls_message::show($re);
	if(!($mtpl = $mtpls[$tplname])) cls_message::show('指定的模板不存在');
	if(!submitcheck('bmtplcopy')){
		!is_file($true_tpldir.$tplname) && cls_message::show('指定的源模板文件不存在');
		tabheader('复制常规页面模板','mtplcopy',"?entry=$entry&action=mtplcopy&tplname=$tplname");
		trbasic('模板名称','mtpladd[cname]');
		trbasic('模板类别','mtpladd[tpclass]',makeoption($tpclasses,$mtpl['tpclass']),'select');
		if($mtpl['tpclass'] == 'archive') trbasic('单文档设置时可选','mtpladd[chid]',makeoption(array(0 => '请选择') + cls_channel::chidsarr(1),empty($mtpl['chid']) ? 0 : $mtpl['chid']),'select',array('guide'=>'为单个文档指定内容页模板时，只有上面所选模型的文档才可以选择此模板',));
		trbasic('源模板文件','',$tplname,'');
		trbasic('模板文件另存为','mtpladd[tplname]','','text',array('guide' => "文件名称只允许包含字母、数字、下划线(-_)、点(.)等字符,并以htm或html为扩展名"));
		tabfooter('bmtplcopy');
		a_guide('mtplcopy');
	}else{
		if(empty($mtpladd['cname'])) cls_message::show('请输入模板名称',M_REFERER);
		if($re = _08_FilesystemFile::CheckFileName($mtpladd['tplname'])) cls_message::show($re);
		$mtplsnew = findfiles($true_tpldir);
		in_array($mtpladd['tplname'],$mtplsnew) && cls_message::show('指定的模板文件名称重复',M_REFERER);
		!copy($true_tpldir.$tplname,$true_tpldir.$mtpladd['tplname']) && cls_message::show('模板复制失败',M_REFERER);
		$mtpls[$mtpladd['tplname']] = array('cname' => stripslashes($mtpladd['cname']),'tpclass' => $mtpladd['tpclass']);
		if($mtpladd['tpclass'] == 'archive'){
			if($mtpladd['chid'] = max(0,intval($mtpladd['chid']))){
				$mtpls[$mtpladd['tplname']]['chid'] = $mtpladd['chid'];
			}else unset($mtpls[$mtpladd['tplname']]['chid']);
		}
		cls_CacheFile::Save($mtpls,'mtpls','mtpls');
		adminlog('复制常规模板');
		cls_message::show('模板复制完成',axaction(6,"?entry=$entry&action=mtplsedit"));
	}
}elseif($action == 'mtagcode'){
    empty($fn) || $fn = preg_replace('/[^A-Z0-9_-]/i', '_', trim($fn));
    $types = trim($types);
    $textid = trim($textid);
    $floatwin_id = trim($floatwin_id);

    $url_params = array();
    foreach(array('fn', 'types', 'textid', 'floatwin_id', 'caretpos', 'ttype', 'bclass', 'sclass') as $key)
    {
        empty($$key) || $url_params[$key] = ("{$key}=" . $$key);
    }
    if(empty($url_params)) cls_message::show('参数错误!');

    $createranges = read_select_file($fn);
    $createrange = stripslashes($createranges['old_str']);
    
    // 当前执行插入标识时
    if(isset($types) && $types == 'insert') {
        $url_params = implode('&', $url_params);
        $url = "?entry=mtags&action=mtaginsert" . (empty($url_params) ? '' : "&{$url_params}");
        mheader("Location:$url");
    } else {
    	if(empty($createrange)) cls_message::show('请指定标识来源!');
    	if(preg_match("/\{(u|c|p|tpl)\\$(.+?)(\s|\})/is",$createrange,$matches))
        {
            if (empty($createranges['tclass']))
            {
                cls_message::show('该标识不存在。');
            }
            # 选中标识时让点击插入原始标识对应上
            $mtagses = _08_factory::getMtagsInstance($createranges['tclass']);
            $url_params['bclass'] = 'bclass=' . $createranges['tclass'];
            is_object($mtagses) && $url_params['sclass'] = 'sclass=' . $mtagses->getSclass((array) $createranges['setting']);
            $url_params = implode('&', $url_params);
    	    if(strtolower(trim($matches[1])) == 'tpl') {
    	        $ttype = 'rtag';
    	    } else {
    	        $ttype = $matches[1].'tag';
    	    }
    		$tname = $matches[2];
    		$url = "?entry=mtags&action=mtagsdetail&ttype=$ttype&tname=$tname" . (empty($url_params) ? '' : "&{$url_params}");
            #exit($url);
 
        	mheader("location:$url");
    	}
    }
	cls_message::show('请指定标识来源!');
}elseif($action == 'basic2extend' && $tplname){
    $msg = rtag_basic2extend($tplname) ? '模板扩展完成' : '基础模板原文件不存在';
    cls_message::show($msg,axaction(6,"?entry=$entry&action=mtplsedit"));
}
