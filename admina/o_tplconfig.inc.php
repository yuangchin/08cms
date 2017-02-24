<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
foreach(array('mtpls','tpl_mconfigs',) as $k) $$k = cls_cache::Read($k);
if(empty($action)) $action = 'system';
if($action == 'system'){
	//手机版首页储存在通用设置中
	backnav('mobile','system');
	$o_sptpls = cls_cache::Read('o_sptpls');
	if(!submitcheck('bsubmit')){
		tabheader('系统设置','tplbase',"?entry=$entry&action=$action");
		trbasic('开启手机版','mconfigsnew[enable_mobile]',empty($mconfigs['enable_mobile']) ? 0 : 1,'radio');
		trbasic('手机版路径','mconfigsnew[mobiledir]',empty($mconfigs['mobiledir']) ? '' : $mconfigs['mobiledir'],'text',array('guide'=>'手机版路径，不要带/。{$mobiledir}调用路径，{$mobileurl}调用url。'));
		tabfooter();

		tabheader('系统模板');
		$index_items = array (
		  'index' => 
		  array (
			'cname' => '首页模板',
			'tpclass' => 'index',
			'tctitle' => '首页',
		  ),
		);
		foreach($index_items as $k => $v){
			trbasic($v['cname'],"fmdata[$k]",makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr($v['tpclass']),empty($o_sptpls[$k]) ? '' : $o_sptpls[$k]),'select',array('guide' => cls_mtpl::mtplGuide('index',0,1)));
		}
		//$sp_regs = array(); //各模型分开绑模板?
		$sp_items = array (
		  'register' => 
		  array (
			'cname' => '注册模板',
			'link' => '{$cms_abs}/{$mobiledir}/register.php',
		  ),
		  'login' => 
		  array (
			'cname' => '登录模板',
			'link' => '{$cms_abs}/{$mobiledir}/login.php',
		  ),
          '3g_wxlogin'=>
          array(
            'cname'=>'微信客户端登陆模板',
            'link'=>'{$cms_abs}/{$mobiledir}/login.php',
          ),
		  'message' => 
		  array (
			'cname' => '提示信息模板',
			'link' => '提示信息(系统调用)',
		  ),
		);
		foreach($sp_items as $k => $v){
			trbasic($v['cname'],"fmdata[$k]",makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr('special'),empty($o_sptpls[$k]) ? '' : $o_sptpls[$k]),'select',array('guide' => cls_mtpl::mtplGuide('special',0,1)."。调用链接：$v[link]。"));
		}
		tabfooter('bsubmit');
		a_guide('mobile_base');
	}else{
		foreach(array('mobiledir',) as $var){
			$mconfigsnew[$var] = strtolower($mconfigsnew[$var]);
			if($mconfigsnew[$var] == $mconfigs[$var]) continue;
			if(!$mconfigsnew[$var] || preg_match("/[^a-z_0-9]+/",$mconfigsnew[$var])){
				$mconfigsnew[$var] = $mconfigs[$var];
				continue;
			}
			if($mconfigs[$var] && is_dir(M_ROOT.$mconfigs[$var])){
				if(!rename(M_ROOT.$mconfigs[$var],M_ROOT.$mconfigsnew[$var])) $mconfigsnew[$var] = $mconfigs[$var];
			}else mmkdir(M_ROOT.$mconfigsnew[$var],0);
		}
		saveconfig('visit');
		
		cls_CacheFile::Save($fmdata,'o_sptpls','o_sptpls');
		adminlog('手机版模板绑定','系统模板绑定');
		cls_message::show('系统模板绑定完成',M_REFERER);
	}
}elseif($action == 'tplchannel'){
	foreach(array('channels','o_arc_tpl_cfgs','o_arc_tpls',) as $k) $$k = cls_cache::Read($k);
	if(empty($chid)){
		backnav('mobile','archive');
		tabheader("按文档模型
		 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=tplcatalog\">按文档栏目</a>
		 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=arc_tplsedit\" onclick=\"return floatwin('open_channeledit',this)\">文档模板方案</a>
		 &nbsp; &nbsp;".cls_mtpl::mtplGuide('archive',true));
		trcategory(array('ID',array('模型名称','txtL'),'方案','附加页','搜索页',array('文档模板方案/详情','txtL')));
		foreach($channels as $k => $v){
			$tid = empty($o_arc_tpl_cfgs[$k]) ? 0 : $o_arc_tpl_cfgs[$k];
			if(empty($o_arc_tpls[$tid])) $tid = 0;
			$namestr = $tid ? $tid.'-'.$o_arc_tpls[$tid]['cname']." &nbsp; &nbsp;>><a href=\"?entry=$entry&action=arc_tpldetail&tid=$tid\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a>" : '-';
			$addnum = !$tid || empty($o_arc_tpls[$tid]['addnum']) ? 0 : $o_arc_tpls[$tid]['addnum'];
			$searchs = !$tid || empty($o_arc_tpls[$tid]['search']) ? 0 : count($o_arc_tpls[$tid]['search']);
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=$entry&action=$action&chid=$k\" onclick=\"return floatwin('open_channeledit',this)\">设置</a></td>\n".
				"<td class=\"txtC w40\">$addnum</td>\n".
				"<td class=\"txtC w40\">$searchs</td>\n".
				"<td class=\"txtL\">$namestr</td>\n".
				"</tr>\n";
		}
		tabfooter();
	}else{
		echo "<title>按模型绑定文档模板</title>";
		if(!($channel = $channels[$chid])) cls_message::show('请指定正确的文档模型！');
		$tid = empty($o_arc_tpl_cfgs[$chid]) ? 0 : $o_arc_tpl_cfgs[$chid];
		if(!submitcheck('bsubmit')){
			tabheader("[$channel[cname]]模板设置",'channel',"?entry=$entry&action=$action&chid=$chid");
			$na = array(0 => '不设置',);foreach($o_arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
			trbasic('文档模板方案','fmdata[tid]',makeoption($na,$tid),'select',array('guide' => '文档优先使用所属栏目绑定的模板方案，栏目未绑定模板的话，则默认使用所属模型绑定的模板方案<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'));
			tabfooter('bsubmit');
			a_guide('tplchannel');
		}else{
			$o_arc_tpl_cfgs[$chid] = empty($fmdata['tid']) ? 0 : intval($fmdata['tid']);
			foreach($o_arc_tpl_cfgs as $k => $v) if(empty($channels[$k])) unset($o_arc_tpl_cfgs[$k]);
			cls_CacheFile::Save($o_arc_tpl_cfgs,'o_arc_tpl_cfgs','o_arc_tpl_cfgs');
			adminlog('详细修改文档模型');
			cls_message::show('模型修改完成',axaction(6,"?entry=$entry&action=$action"));
		}
	}
}elseif($action == 'tplcatalog'){
	foreach(array('catalogs','ca_tpl_cfgs','o_arc_tpls',) as $k) $$k = cls_cache::Read($k);
	if(empty($caid)){
		backnav('mobile','archive');
		tabheader(">><a href=\"?entry=$entry&action=tplchannel\">按文档模型</a>
		 &nbsp; &nbsp;按文档栏目
		 &nbsp; &nbsp;>> <a href=\"?entry=$entry&action=arc_tplsedit\" onclick=\"return floatwin('open_channeledit',this)\">文档模板方案</a>
		 &nbsp; &nbsp;".cls_mtpl::mtplGuide('archive',true));
		trcategory(array('ID',array('栏目名称','txtL'),'模板/静态格式','附加页','搜索页',array('文档模板方案/详情','txtL')));
		foreach($catalogs as $k => $v){
			$tid = empty($ca_tpl_cfgs[$k]) ? 0 : $ca_tpl_cfgs[$k];
			if(empty($o_arc_tpls[$tid])) $tid = 0;
			$namestr = $tid ? $tid.'-'.$o_arc_tpls[$tid]['cname']." &nbsp; &nbsp;>><a href=\"?entry=$entry&action=arc_tpldetail&tid=$tid\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a>" : '-';
			$addnum = !$tid || empty($o_arc_tpls[$tid]['addnum']) ? 0 : $o_arc_tpls[$tid]['addnum'];
			$searchs = !$tid || empty($o_arc_tpls[$tid]['search']) ? 0 : count($o_arc_tpls[$tid]['search']);
			$titlestr = empty($v['level']) ? "<b>$v[title]</b>" : str_repeat('&nbsp; &nbsp; &nbsp; ',$v['level']).$v['title'];
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\">$titlestr</td>\n".
				"<td class=\"txtC\"><a href=\"?entry=$entry&action=$action&caid=$k\" onclick=\"return floatwin('open_channeledit',this)\">配置</a></td>\n".
				"<td class=\"txtC w40\">$addnum</td>\n".
				"<td class=\"txtC w40\">$searchs</td>\n".
				"<td class=\"txtL\">$namestr</td>\n".
				"</tr>\n";
		}
		tabfooter();
	}else{
		echo "<title>按栏目绑定文档模板</title>";
		if(!($catalog = $catalogs[$caid])) cls_message::show('请指定正确的栏目！');
		$tid = empty($ca_tpl_cfgs[$caid]) ? 0 : $ca_tpl_cfgs[$caid];
		if(!submitcheck('bsubmit')){
			tabheader("[$catalog[title]]模板设置",'channel',"?entry=$entry&action=$action&caid=$caid");
			$na = array(0 => '不设置',);foreach($o_arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
			trbasic('文档模板方案','fmdata[tid]',makeoption($na,$tid),'select',array('guide' => '文档优先使用所属栏目绑定的模板方案，栏目未绑定模板的话，则默认使用所属模型绑定的模板方案<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'));
			trbasic('文档页静态保存格式','fmdata[customurl]',$catalog['customurl'],'text',array('guide'=>'留空为默认格式，{$topdir}顶级栏目目录，{$cadir}所属栏目目录，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。','w'=>50));
			tabfooter('bsubmit');
			a_guide('tplcatalog');
		}else{
			$ca_tpl_cfgs[$caid] = empty($fmdata['tid']) ? 0 : intval($fmdata['tid']);
			foreach($ca_tpl_cfgs as $k => $v) if(empty($catalogs[$k])) unset($ca_tpl_cfgs[$k]);
			cls_CacheFile::Save($ca_tpl_cfgs,'ca_tpl_cfgs','ca_tpl_cfgs');
			
			$fmdata['customurl'] = preg_replace("/^\/+/",'',trim($fmdata['customurl']));
			$db->query("UPDATE {$tblprefix}catalogs SET
				customurl='$fmdata[customurl]'
				WHERE caid='$caid'");
			cls_CacheFile::Update('catalogs');
			
			adminlog('按栏目绑定文档内容页模板');
			cls_message::show('文档内容页模板绑定完成',axaction(6,"?entry=$entry&action=$action"));
		}
	}
}elseif($action == 'tplfcatalog'){
	backnav('mobile','farchive');
	foreach(array('fcatalogs','o_tplcfgs',) as $k) $$k = cls_cache::Read($k);
	if(!submitcheck('bfcatalog')){
		tabheader("副件内容模板绑定&nbsp; &nbsp; ".cls_mtpl::mtplGuide('freeinfo',true),'fcatalog',"?entry=$entry&action=tplfcatalog");
		foreach($fcatalogs as $k => $v){
			if(!empty($v['ftype'])) continue;
			$tplcfg = empty($o_tplcfgs['farchive'][$k]) ? array() : $o_tplcfgs['farchive'][$k];
			trbasic($k.'.'.$v['title'],"tplcfgnew[$k][arctpl]",makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr('freeinfo'),@$tplcfg['arctpl']),'select');
		}
		tabfooter('bfcatalog');
		a_guide('tplfcatalog');
	}else{
		$tplcfg = array();
		$vars = array('arctpl',);
		foreach($fcatalogs as $k => $v){
			if(!empty($v['ftype'])) continue;
			foreach($vars as $var){
				empty($tplcfgnew[$k][$var]) ||$tplcfg[$k][$var] = $tplcfgnew[$k][$var];
			}
		}
		$o_tplcfgs['farchive'] = $tplcfg;
		cls_CacheFile::Save($o_tplcfgs,'o_tplcfgs','o_tplcfgs');
		adminlog('绑定副件内容页模板');
		cls_message::show('副件模板绑定完成',M_REFERER);
	}
}elseif($action == 'arc_tpladd'){
	$o_arc_tpls = cls_cache::Read('o_arc_tpls');
	echo "<title>添加文档模板方案</title>";
	if(!submitcheck('bsubmit')){
		$submitstr = '';
		tabheader('添加文档模板方案','arc_tpladd',"?entry=$entry&action=$action",2,0,1);
		trbasic('方案名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,1,4,30)));
		tabfooter('bsubmit','添加');
		a_guide('o_arc_tpladd');//?????????????
	} else {
		if(!($fmdata['cname'] = trim(strip_tags($fmdata['cname'])))) cls_message::show('请输入标题！',M_REFERER);
		$tid = auto_insert_id('o_arc_tpls');
		$o_arc_tpls[$tid] = array('cname' => $fmdata['cname'],'addnum' =>0,'chid' => 0,'vieworder' => 0,'cfg' => array(),);
		cls_CacheFile::Save($o_arc_tpls,'o_arc_tpls','o_arc_tpls');
		adminlog('添加文档模板方案');
		cls_message::show('文档模板方案添加完成，请继续下一步设置',"?entry=$entry&action=arc_tpldetail&tid=$tid");
	}
}elseif($action == 'arc_tpldetail' && $tid){
	foreach(array('o_arc_tpls','channels',) as $k) $$k = cls_cache::Read($k);
	if(!($arc_tpl = @$o_arc_tpls[$tid])) cls_message::show('请选择文档模板方案');
	echo "<title>文档模板方案 - $arc_tpl[cname]</title>";
	if(!submitcheck('bsubmit')){
		tabheader("文档模板设置&nbsp;&nbsp;[$arc_tpl[cname]]",'cntpldetail',"?entry=$entry&action=$action&tid=$tid");
		$arr = array();for($i = 0;$i <= $max_addno;$i ++) $arr[$i] = $i;
		$addnum = empty($arc_tpl['addnum']) ? 0 : $arc_tpl['addnum'];
		trbasic('附加页数量','',makeradio('fmdata[addnum]',$arr,$addnum),'');
		trbasic('文档搜索列表模板','fmdata[search][0]',makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr('cindex'),@$arc_tpl['search'][0]),'select',array('guide' => cls_mtpl::mtplGuide('cindex')));
		trbasic('搜索附加页1模板','fmdata[search][1]',makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr('cindex'),@$arc_tpl['search'][1]),'select',array('guide' => cls_mtpl::mtplGuide('cindex')));
		tabfooter();
		for($i = 0;$i <= $max_addno;$i ++){
			tabheader(($i ? '附加页'.$i : '基本页').'设置'.viewcheck(array('name' =>'viewdetail','title' => '详细','value' => $i > $addnum ? 0 : 1,'body' =>$actionid.'tbodyfilter'.$i)));
			echo "<tbody id=\"{$actionid}tbodyfilter$i\" style=\"display:".($i > $addnum ? 'none' : '')."\">";
			trbasic('页面模板',"fmdata[cfg][$i][tpl]",makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr('archive'),empty($arc_tpl['cfg'][$i]['tpl']) ? '' : $arc_tpl['cfg'][$i]['tpl']),'select',array('guide' => cls_mtpl::mtplGuide('archive')));
			trbasic('虚拟静态URL','',makeradio("fmdata[cfg][$i][novu]",array(0 => '按系统总设置',1 => '关闭虚拟静态'),empty($arc_tpl['cfg'][$i]['novu']) ? 0 : $arc_tpl['cfg'][$i]['novu']),'');
			echo "</tbody>";
			if($i != $max_addno) tabfooter();
		}
		tabfooter('bsubmit');
		a_guide('arc_tpldetail');
	}else{
		$arc_tpl['addnum'] = max(0,intval($fmdata['addnum']));
		foreach(array(0,1) as $i){
			if(empty($fmdata['search'][$i])){
				unset($arc_tpl['search'][$i]);
			}else{
				$arc_tpl['search'][$i] = $fmdata['search'][$i];
			}
		}
		for($i = 0;$i <= $max_addno;$i ++){
			foreach(array('tpl','novu',) as $var){
				if(in_array($var,array('tpl',))){
					$fmdata['cfg'][$i][$var] = trim(strip_tags($fmdata['cfg'][$i][$var]));
				}elseif(in_array($var,array('novu',))){
					$fmdata['cfg'][$i][$var] = empty($fmdata['cfg'][$i][$var]) ? 0 : 1;
				}
				
				if(empty($fmdata['cfg'][$i][$var])){
					unset($arc_tpl['cfg'][$i][$var]);
				}else{
					$arc_tpl['cfg'][$i][$var] = $fmdata['cfg'][$i][$var];
				}
			}
			if(empty($arc_tpl['cfg'][$i])) unset($arc_tpl['cfg'][$i]);	
		}
		$o_arc_tpls[$tid] = $arc_tpl;
		cls_CacheFile::Save($o_arc_tpls,'o_arc_tpls','o_arc_tpls');
		adminlog('文档模板方案编辑');
		cls_message::show('文档模板方案修改完成',axaction(6,"?entry=$entry&action=arc_tplsedit"));
	}

}elseif($action == 'arc_tplsedit'){
	foreach(array('o_arc_tpls','channels',) as $k) $$k = cls_cache::Read($k);
	echo "<title>文档模板方案管理</title>";
	if(!submitcheck('bsubmit')){
		tabheader("文档模板方案管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=arc_tpladd\" onclick=\"return floatwin('open_cntplsedit',this)\">添加</a>",'cntplsedit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID',array('方案名称','txtL'),'排序','附加页','搜索页','删除','详情'));
		foreach($o_arc_tpls as $k => $v){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"fmdata[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w40\">".@$v['addnum']."</td>\n".
				"<td class=\"txtC w40\">".(empty($v['search']) ? 0 : count($v['search']))."</td>\n".
				"<td class=\"txtC w40\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=arc_tpldel&tid=$k\">删除</a></td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=$entry&action=arc_tpldetail&tid=$k\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bsubmit','修改');
		a_guide('arc_tplsedit');
	}else{
		if(isset($fmdata)){
			foreach($fmdata as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $o_arc_tpls[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				foreach(array('cname','vieworder',) as $var) $o_arc_tpls[$k][$var] = $v[$var];
			}
			adminlog('编辑文档模板方案');
			cls_Array::_array_multisort($o_arc_tpls,'vieworder',1);
			cls_CacheFile::Save($o_arc_tpls,'o_arc_tpls','o_arc_tpls');
		}
		cls_message::show('文档模板方案修改完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'arc_tpldel' && $tid){
	deep_allow($no_deepmode,"?entry=$entry&action=arc_tplsedit");
	$o_arc_tpls = cls_cache::Read('o_arc_tpls');
	if(!($arc_tpl = @$o_arc_tpls[$tid])) cls_message::show('请选择文档模板方案');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&tid=$tid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=arc_tplsedit>返回</a>";
		cls_message::show($message);
	}
	unset($o_arc_tpls[$tid]);
	cls_CacheFile::Save($o_arc_tpls,'o_arc_tpls','o_arc_tpls');
	cls_message::show('文档模板方案删除成功', "?entry=$entry&action=arc_tplsedit");
}