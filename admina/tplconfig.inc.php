<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
foreach(array('mtpls','tpl_mconfigs','tpl_fields',) as $k) $$k = cls_cache::Read($k);
if($action == 'tplbase'){
	backnav('tpl','base');
	if(!submitcheck('bsubmit')){
		tabheader('模板基本设置','tplbase',"?entry=$entry&action=$action");
		trbasic('选择模板','mconfigsnew[templatedir]',makeoption(listTplpacks(),$mconfigs['templatedir']),'select',array('guide'=>'每套模版一个目录,位于template目录下'));
		trbasic('模板css目录','mconfigsnew[css_dir]',empty($mconfigs['css_dir']) ? 'css' : $mconfigs['css_dir'],'text',array('guide'=>'只需要添写目录名,位于template/模板目录/下'));
		trbasic('模板js目录','mconfigsnew[js_dir]',empty($mconfigs['js_dir']) ? 'js' : $mconfigs['js_dir'],'text',array('guide'=>'只需要添写目录名,位于template/模板目录/下'));
		trbasic('模板标识内SQL缓存周期倍率','',makeradio('mconfigsnew[tagttlplus]',array(0 => '保持原值',2 => '2倍',3 => '3倍',4 => '4倍',-1 => '关闭标识内SQL缓存',),@$mconfigs['tagttlplus']),'',array('guide' => '在数据库查询压力比较大或即时性要求较低的情况,可适当调高。仅扩展缓存开启时有效。'));
		tabfooter();
		
		tabheader('模板调试选项');
		trbasic('系统设为调试状态','debugtagnew',$debugtag,'radio',array('guide'=>'调试模式下模板的修改即时更新，静态页面即时更新，停用页面缓存，会员中心帮助不显示未设置项的调试信息。<br>此设置会影响相同访问IP的用户，点[<a href="?entry=tplconfig&action=cleardebug">这里</a>]关闭所有访问IP的调试模式。'));//两个作用：出错标识显示出样式，被动静态页面每次刷新更新
		trbasic('前台页面显示查询统计','mconfigsnew[viewdebug]',empty($mconfigs['viewdebug']) ? 0 : $mconfigs['viewdebug'],'radio',array('guide'=>'前台页面底部显示查询统计信息，类似如下字符串: querys:15,in:0.0292s'));
		trbasic('前台调试信息显示样式','',makeradio('mconfigsnew[viewdebugmode]',array(0 => '以HTML注释显示(推荐)','direct' => '直接显示(可能使一些css显示不正常)'),@$mconfigs['viewdebugmode']),'',array('guide' => '此信息同时控制[查询统计]和[调试模版]的显示样式；[以HTML注释显示]：前台看不到，要用浏览器的[查看源代码]才可看到。'));
		tabfooter('bsubmit');
		a_guide('tplbase');
	}else{
		//设置模板目录
		$mconfigsnew['templatedir'] = trim(strip_tags($mconfigsnew['templatedir']));//指定新的模板文件夹，所以可以有不同的模板样式
		if(empty($mconfigsnew['templatedir']) || preg_match("/[^a-zA-Z_0-9]+/",$mconfigsnew['templatedir'])){
			cls_message::show('模板目录不合规范',M_REFERER);
		}
		if($mconfigs['templatedir']!=$mconfigsnew['templatedir']){ //更改了模版目录才执行
			mmkdir(M_ROOT.'template/'.$mconfigsnew['templatedir']);
			clear_dir(cls_Parse::TplCacheDirFile('')); //模板缓存
		}
		//因class cls_CacheFile里面“//针对合作开发的特殊处理”,设立设置为全局变量才可更改
		$_tpldir = $mconfigsnew['templatedir'];
		cls_env::SetG('templatedir',$_tpldir);
		
		$tplPacks = listTplpacks(''); //则设置基础模版,为继承则为空
		$mconfigsnew['templatebase'] = empty($tplPacks[$_tpldir]) ? '' : $tplPacks[$_tpldir];
		cls_env::SetG('templatebase',$mconfigsnew['templatebase']);
		
		$mconfigsnew['css_dir'] = trim(strip_tags($mconfigsnew['css_dir']));
		if(empty($mconfigsnew['css_dir']) || preg_match("/[^a-zA-Z_0-9]+/",$mconfigsnew['css_dir'])){
			cls_message::show('模板css目录不合规范',M_REFERER);
		}
		
		$mconfigsnew['js_dir'] = trim(strip_tags($mconfigsnew['js_dir']));
		if(empty($mconfigsnew['js_dir']) || preg_match("/[^a-zA-Z_0-9]+/",$mconfigsnew['js_dir'])){
			cls_message::show('模板js目录不合规范',M_REFERER);
		}
		
		//设置调试模式
		if($onlineip && $debugtagnew != $debugtag){
			$ips = explode(',',$debugtag_ips);
			if($debugtagnew){
				if(count($ips) > 30) $ips = array_slice($ips, -30);
				in_array($onlineip, $ips) || $ips[] = $onlineip;
			}elseif(in_array($onlineip, $ips)){
				$key = array_search($onlineip, $ips); 
				unset($ips[$key]);
			}
			$mconfigsnew['debugtag_ips'] = empty($ips) ? '' : implode(',',$ips);
		}
		empty($debugtagnew) ? mclearcookie('debugtag') : msetcookie('debugtag',1);
		
		saveconfig('tpl');
		adminlog('模板设置','模板基本设置');
		cls_message::show('模板设置完成',M_REFERER);
	}

}elseif($action == 'tplfield'){
	backnav('tpl','tplfield');
	if(!submitcheck('bsubmit')){
		tabheader('前台模板变量','tplbase',"?entry=$entry&action=$action");
		trspecial('站点Logo',specialarr(array('type' => 'image','varname' => 'mconfigsnew[cmslogo]','value' => @$tpl_mconfigs['cmslogo'],'guide' => '前台调用样式：{$cmslogo}',)));
		trbasic('站点SEO标题','mconfigsnew[cmstitle]',@$tpl_mconfigs['cmstitle'],'text',array('w'=>50,'guide' => '前台调用样式：{$cmstitle}',));
		trbasic('站点SEO关键词','mconfigsnew[cmskeyword]',@$tpl_mconfigs['cmskeyword'],'text',array('w'=>50,'guide' => '前台调用样式：{$cmskeyword}',));
		trbasic('站点SEO描述','mconfigsnew[cmsdescription]',@$tpl_mconfigs['cmsdescription'],'textarea',array('guide' => '前台调用样式：{$cmsdescription}',));
		trbasic('网站ICP备案','mconfigsnew[cms_icpno]',@$tpl_mconfigs['cms_icpno'],'text',array('w'=>50,'guide' => '前台调用样式：{$cms_icpno}',));
		trbasic('备案证书bazs.cert文件','mconfigsnew[bazscert]',@$tpl_mconfigs['bazscert'],'text',array('w'=>50,'guide' => '前台调用样式：{$bazscert}',));
		trbasic('版权信息','mconfigsnew[copyright]',@$tpl_mconfigs['copyright'],'textarea',array('guide' => '前台调用样式：{$copyright}',));
		trbasic('第三方统计代码','mconfigsnew[cms_statcode]',@$tpl_mconfigs['cms_statcode'],'textarea',array('guide' => '前台调用样式：{$cms_statcode}',));
		tabfooter();

		tabheader("自定模板变量 &nbsp;>><a href=\"?entry=$entry&action=tpl_fieldsedit\" onclick=\"return floatwin('open_channeledit',this)\">自定变量管理</a>");
		foreach($tpl_fields as $k => $v){
		    /*# 暂时隐藏微信二维码字段
            if ( in_array($k, array('weixin', 'wxewmpic')) )
            {
                continue;
            }*/
			$var = "user_$k";
			switch($v['type']){
				case 'image':
					trspecial($v['cname'],specialarr(array('type' => 'image','varname' => "mconfigsnew[$var]",'value' => @$tpl_mconfigs[$var],'guide' => '前台调用样式：{$'.$var.'}',)));
				break;
				case 'text':
					trbasic($v['cname'],"mconfigsnew[$var]",@$tpl_mconfigs[$var],'text',array('w'=>50,'guide' => '前台调用样式：{$'.$var.'}',));
				break;
				case 'multitext':
					trbasic($v['cname'],"mconfigsnew[$var]",@$tpl_mconfigs[$var],'textarea',array('guide' => '前台调用样式：{$'.$var.'}',));
				break;
			
			}
		}
		tabfooter('bsubmit');
		a_guide('tplfield');
	}else{
		$c_upload = cls_upload::OneInstance();
		
		$mconfigsnew['cmslogo'] = upload_s($mconfigsnew['cmslogo'],@$tpl_mconfigs['cmslogo'],'image');
		if($k = strpos($mconfigsnew['cmslogo'],'#')) $mconfigsnew['cmslogo'] = substr($mconfigsnew['cmslogo'],0,$k);
		//自定义字段
		foreach($tpl_fields as $k => $v){
			$var = "user_$k";
			switch($v['type']){
				case 'image':
					$mconfigsnew[$var] = upload_s($mconfigsnew[$var],@$tpl_mconfigs[$var],'image');
					if($k = strpos($mconfigsnew[$var],'#')) $mconfigsnew[$var] = substr($mconfigsnew[$var],0,$k);
				break;
				case 'text':
				case 'multitext':
					$mconfigsnew[$var] = trim($mconfigsnew[$var]);
				break;
			
			}
		}
		$c_upload->closure(2, 0, 'mconfigs');
		saveconfig('tpl');
		adminlog('模板设置','前台模板变量');
		cls_message::show('模板变量设置完成',M_REFERER);
	}

}elseif($action == 'system'){
	backnav('bindtpl','system');
	$sptpls = cls_cache::Read('sptpls');
	if(!submitcheck('bsubmit')){
		
		tabheader("系统首页模板",'tplbase',"?entry=$entry&action=$action");
		$index_items = array (
		  'index' => 
		  array (
			'cname' => '系统首页模板',
			'tpclass' => 'index',
			'tctitle' => '首页',
		  ),
		  'm_index' => 
		  array (
			'cname' => '会员频道首页模板',
			'tpclass' => 'marchive',
			'tctitle' => '会员相关',
		  ),
		  'rss_index' => 
		  array (
			'cname' => '首页RSS模板',
			'tpclass' => 'xml',
			'tctitle' => 'RSS/SiteMap',
		  ),
		);
		foreach($index_items as $k => $v){
			trbasic($v['cname'],"fmdata[$k]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr($v['tpclass']),empty($sptpls[$k]) ? '' : $sptpls[$k]),'select',array('guide' => cls_mtpl::mtplGuide($v['tpclass'])));
		
		}
		tabfooter();
		
		tabheader('功能页面模板');
		$sp_items = array (
		  'msearch' => 
		  array (
			'cname' => '全模型会员搜索页',
			'link' => '{$cms_abs}msearch.php',
		  ),
		  'login' => 
		  array (
			'cname' => '会员登录页面',
			'link' => '{$cms_abs}login.php',
		  ),
		  'message' => 
		  array (
			'cname' => '系统提示信息模板',
			'link' => '提示信息(系统调用)',
		  ),
		  'jslogin' => 
		  array (
			'cname' => '会员(未)登录js调用模板',
			'link' => '{$cms_abs}login.php?mode=js',
		  ),
		  'jsloginok' => 
		  array (
			'cname' => '会员(已)登录js调用模板',
			'link' => '{$cms_abs}login.php?mode=js',
		  ),
		  'down' => 
		  array (
			'cname' => '附件下载附加页',
			'link' => '通过模板标识定义',
		  ),
		  'flash' => 
		  array (
			'cname' => 'FLASH播放附加页',
			'link' => '通过模板标识定义',
		  ),
		  'media' => 
		  array (
			'cname' => '视频播放附加页',
			'link' => '通过模板标识定义',
		  ),
		  'vote' => 
		  array (
			'cname' => '投票查看页面',
			'link' => '{$cms_abs}vote.php?action=view&vid={$vid}',
		  ),
		);
		foreach($sp_items as $k => $v){
			trbasic($v['cname'],"fmdata[$k]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('special'),empty($sptpls[$k]) ? '' : $sptpls[$k]),'select',array('guide' => cls_mtpl::mtplGuide('special')."。调用链接：$v[link]。"));
		}
		tabfooter('bsubmit');
		a_guide('bindindex');
	}else{
		cls_CacheFile::Save($fmdata,'sptpls','sptpls');
		adminlog('模板绑定','系统模板绑定');
		cls_message::show('系统模板绑定完成',M_REFERER);
	}
}elseif($action == 'cleardebug'){
	$mconfigsnew['debugtag_ips'] = '';
	saveconfig('tpl');
	cls_message::show('关闭所有来源的调试模式',M_REFERER);
}elseif($action == 'tpl_fieldadd'){
	$tpl_fields = cls_cache::Read('tpl_fields');
	echo "<title>添加模板自定变量</title>";
	if(!submitcheck('bsubmit')){
		$submitstr = '';
		$typesarr = array('text' => '单行文本','multitext' => '多行文本','image' => '图片上传',);
		tabheader('添加模板自定变量','tpl_fieldadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('变量名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,1,4,30)));
		trbasic('变量英文标识','fmdata[ename]','','text',array('validate'=>makesubmitstr('fmdata[ename]',1,'tagtype',0,30),'guide' => '模板中将使用{$user_英文标识}来调用本变量。'));
		trbasic('变量类型','fmdata[type]',makeoption($typesarr),'select');
		tabfooter('bsubmit','添加');
		a_guide('tpl_fieldadd');//?????????????
	} else {
		if(!($fmdata['cname'] = trim(strip_tags($fmdata['cname'])))) cls_message::show('请输入标题！',M_REFERER);
		if(!($fmdata['ename'] = trim($fmdata['ename']))) cls_message::show('变量标识！',M_REFERER);
		if(preg_match("/[^a-zA-Z_0-9]+|^[0-9_]+/",$fmdata['ename'])) cls_message::show('变量标识不合规范',M_REFERER);
		$enamearr = array();foreach($tpl_fields as $k => $v) $enamearr[] = $k;
		if(in_array($fmdata['ename'],$enamearr)) cls_message::show('变量标识被占用',M_REFERER);
		$tpl_fields[$fmdata['ename']] = array('cname' => $fmdata['cname'],'type' => $fmdata['type'],'vieworder' => 0);
		cls_CacheFile::Save($tpl_fields,'tpl_fields','tpl_fields');
		adminlog('添加模板自定变量');
		cls_message::show('模板自定变量添加完成',axaction(6,"?entry=$entry&action=tpl_fieldsedit"));
	}
}elseif($action == 'tpl_fieldsedit'){//强制加user_
	foreach(array('tpl_fields','tpl_mconfigs',) as $k) $$k = cls_cache::Read($k);
	echo "<title>前台模板自定变量</title>";
	if(!submitcheck('bsubmit')){
		$typesarr = array('text' => '单行文本','multitext' => '多行文本','image' => '图片上传',);
		tabheader("前台模板自定变量&nbsp; &nbsp; >><a href=\"?entry=$entry&action=tpl_fieldadd\" onclick=\"return floatwin('open_cntplsedit',this)\">添加</a>",'cntplsedit',"?entry=$entry&action=$action",'10');
		trcategory(array('删除',array('变量名称','txtL'),'排序','变量类型',array('前台调用样式','txtL')));
		$ii = 0;
		foreach($tpl_fields as $k => $v){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"fmdata[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w60\">".@$typesarr[$v['type']]."</td>\n".
				"<td class=\"txtL w120\">{\$user_$k}</td>\n".
				"</tr>\n";
		}
		tabfooter('bsubmit');
		a_guide('tpl_fieldsedit');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				unset($tpl_fields[$k]);
				unset($tpl_mconfigs["user_$k"]);
				unset($fmdata[$k]);
			}
			cls_CacheFile::Save($tpl_mconfigs,'tpl_mconfigs','tpl_mconfigs');
			cls_CacheFile::Update('mconfigs');//需要在此过程中更新btags
		}
		
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $tpl_fields[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				foreach(array('cname','vieworder',) as $var) $tpl_fields[$k][$var] = $v[$var];
			}
			adminlog('编辑前台模板自定变量');
			cls_Array::_array_multisort($tpl_fields);
		}
		cls_CacheFile::Save($tpl_fields,'tpl_fields','tpl_fields');
		cls_message::show('自定变量修改完成',axaction(6,"?entry=$entry&action=$action"));
	}
}elseif($action == 'tplchannel'){
	foreach(array('channels','arc_tpl_cfgs','arc_tpls',) as $k) $$k = cls_cache::Read($k);
	if(empty($chid)){
		backnav('bindtpl','channel');
		tabheader("按模型绑定
		 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=tplcatalog\">按栏目绑定</a>
		 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=arc_tplsedit\" onclick=\"return floatwin('open_channeledit',this)\">文档模板方案</a>
		 &nbsp; &nbsp;".cls_mtpl::mtplGuide('archive',true));
		trcategory(array('ID',array('模型名称','txtL'),'方案','附加页','搜索页',array('文档模板方案/详情','txtL')));
		foreach($channels as $k => $v){
			$tid = empty($arc_tpl_cfgs[$k]) ? 0 : $arc_tpl_cfgs[$k];
			if(empty($arc_tpls[$tid])) $tid = 0;
			$namestr = $tid ? $tid.'-'.$arc_tpls[$tid]['cname']." &nbsp; &nbsp;>><a href=\"?entry=$entry&action=arc_tpldetail&tid=$tid\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a>" : '-';
			$addnum = !$tid || empty($arc_tpls[$tid]['addnum']) ? 0 : $arc_tpls[$tid]['addnum'];
			$searchs = !$tid || empty($arc_tpls[$tid]['search']) ? 0 : count($arc_tpls[$tid]['search']);
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
		$tid = empty($arc_tpl_cfgs[$chid]) ? 0 : $arc_tpl_cfgs[$chid];
		if(!submitcheck('bsubmit')){
			tabheader("[$channel[cname]]模板设置",'channel',"?entry=$entry&action=$action&chid=$chid");
			$na = array(0 => '不设置',);foreach($arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
			trbasic('文档模板方案','fmdata[tid]',makeoption($na,$tid),'select',array('guide' => '文档优先使用所属栏目绑定的模板方案，栏目未绑定模板的话，则默认使用所属模型绑定的模板方案<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'));
			tabfooter('bsubmit');
			a_guide('tplchannel');
		}else{
			$arc_tpl_cfgs[$chid] = empty($fmdata['tid']) ? 0 : intval($fmdata['tid']);
			foreach($arc_tpl_cfgs as $k => $v) if(empty($channels[$k])) unset($arc_tpl_cfgs[$k]);
			cls_CacheFile::Save($arc_tpl_cfgs,'arc_tpl_cfgs','arc_tpl_cfgs');
			adminlog('详细修改文档模型');
			cls_message::show('模型修改完成',axaction(6,"?entry=$entry&action=$action"));
		}
	}
}elseif($action == 'tplcatalog'){
	foreach(array('catalogs','ca_tpl_cfgs','arc_tpls',) as $k) $$k = cls_cache::Read($k);
	if(empty($caid)){
		backnav('bindtpl','channel');
		tabheader(">><a href=\"?entry=$entry&action=tplchannel\">按模型绑定</a>
		 &nbsp; &nbsp;按栏目绑定
		 &nbsp; &nbsp;>> <a href=\"?entry=$entry&action=arc_tplsedit\" onclick=\"return floatwin('open_channeledit',this)\">文档模板方案</a>
		 &nbsp; &nbsp;".cls_mtpl::mtplGuide('archive',true));
		trcategory(array('ID',array('栏目名称','txtL'),'模板/静态格式','附加页','搜索页',array('文档模板方案/详情','txtL')));
		foreach($catalogs as $k => $v){
			$tid = empty($ca_tpl_cfgs[$k]) ? 0 : $ca_tpl_cfgs[$k];
			if(empty($arc_tpls[$tid])) $tid = 0;
			$namestr = $tid ? $tid.'-'.$arc_tpls[$tid]['cname']." &nbsp; &nbsp;>><a href=\"?entry=$entry&action=arc_tpldetail&tid=$tid\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a>" : '-';
			$addnum = !$tid || empty($arc_tpls[$tid]['addnum']) ? 0 : $arc_tpls[$tid]['addnum'];
			$searchs = !$tid || empty($arc_tpls[$tid]['search']) ? 0 : count($arc_tpls[$tid]['search']);
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
			$na = array(0 => '不设置',);foreach($arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
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
}elseif($action == 'tplmchannel'){
	backnav('bindtpl','mchannel');
	$mchannels = cls_cache::Read('mchannels');
	$tplcfgs = cls_cache::Read('tplcfgs');
	if(!submitcheck('bmchannel')){
		tabheader("会员模板绑定&nbsp; &nbsp; ".cls_mtpl::mtplGuide('marchive',true),'mchannel',"?entry=$entry&action=$action");
		trcategory(array('ID',array('会员模型','txtL'),array('注册模板','txtL'),array('搜索会员模板','txtL'),array('搜索附加页1模板','txtL'),array('备用模板','txtL'),));
		foreach($mchannels as $k => $v){
			$tplcfg = empty($tplcfgs['member'][$k]) ? array() : $tplcfgs['member'][$k];
			$sel_style = " style='width:150px; height:23px;' ";
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtL\">".makeselect("tplcfgnew[$k][addtpl]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('marchive'),@$tplcfg['addtpl']),$sel_style)."</td>\n".
				"<td class=\"txtL\">".makeselect("tplcfgnew[$k][srhtpl]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('marchive'),@$tplcfg['srhtpl']),$sel_style)."</td>\n".
				"<td class=\"txtL\">".makeselect("tplcfgnew[$k][srhtpl1]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('marchive'),@$tplcfg['srhtpl1']),$sel_style)."</td>\n".
				"<td class=\"txtL\">".makeselect("tplcfgnew[$k][bktpl]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('marchive'),@$tplcfg['bktpl']),$sel_style)."</td>\n".
				"</tr>\n";
		}
		tabfooter('bmchannel');
		a_guide('tplmchannel');
	}else{
		$vars = array('addtpl','srhtpl','srhtpl1','bktpl',);
		$tplcfgs['member'] = array();
		foreach($mchannels as $k => $v){
			foreach($vars as $var) @$tplcfgnew[$k][$var] && $tplcfgs['member'][$k][$var] = $tplcfgnew[$k][$var];
		}
		cls_CacheFile::Save($tplcfgs,'tplcfgs','tplcfgs');
		adminlog('详细修改会员模型');
		cls_message::show('模型修改完成',M_REFERER);
	}
}elseif($action == 'tplfcatalog'){
	backnav('bindtpl','fcatalog');
	$fcatalogs = cls_cache::Read('fcatalogs');
	$tplcfgs = cls_cache::Read('tplcfgs');
	if(!submitcheck('bfcatalog')){
		tabheader("副件内容模板绑定&nbsp; &nbsp; ".cls_mtpl::mtplGuide('freeinfo',true),'fcatalog',"?entry=$entry&action=tplfcatalog");
		foreach($fcatalogs as $k => $v){
			if(!empty($v['ftype'])) continue;
			$tplcfg = empty($tplcfgs['farchive'][$k]) ? array() : $tplcfgs['farchive'][$k];
			trbasic($k.'.'.$v['title'],"tplcfgnew[$k][arctpl]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('freeinfo'),@$tplcfg['arctpl']),'select');
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
		$tplcfgs['farchive'] = $tplcfg;
		cls_CacheFile::Save($tplcfgs,'tplcfgs','tplcfgs');
		adminlog('绑定副件内容页模板');
		cls_message::show('副件模板绑定完成',M_REFERER);
	}
}elseif($action == 'arc_tpladd'){
	$arc_tpls = cls_cache::Read('arc_tpls');
	echo "<title>添加文档模板方案</title>";
	if(!submitcheck('bsubmit')){
		$submitstr = '';
		tabheader('添加文档模板方案','arc_tpladd',"?entry=$entry&action=$action",2,0,1);
		trbasic('方案名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,1,4,30)));
		tabfooter('bsubmit','添加');
		a_guide('arc_tpladd');//?????????????
	} else {
		if(!($fmdata['cname'] = trim(strip_tags($fmdata['cname'])))) cls_message::show('请输入标题！',M_REFERER);
		$tid = auto_insert_id('arc_tpls');
		$arc_tpls[$tid] = array('cname' => $fmdata['cname'],'addnum' =>0,'chid' => 0,'vieworder' => 0,'cfg' => array(),);
		cls_CacheFile::Save($arc_tpls,'arc_tpls','arc_tpls');
		adminlog('添加文档模板方案');
		cls_message::show('文档模板方案添加完成，请继续下一步设置',"?entry=$entry&action=arc_tpldetail&tid=$tid");
	}
}elseif($action == 'arc_tpldetail' && $tid){
	foreach(array('arc_tpls','channels',) as $k) $$k = cls_cache::Read($k);
	if(!($arc_tpl = @$arc_tpls[$tid])) cls_message::show('请选择文档模板方案');
	echo "<title>文档模板方案 - $arc_tpl[cname]</title>";
	if(!submitcheck('bsubmit')){
		tabheader("文档模板设置&nbsp;&nbsp;[$arc_tpl[cname]]",'cntpldetail',"?entry=$entry&action=$action&tid=$tid");
		$arr = array();for($i = 0;$i <= $max_addno;$i ++) $arr[$i] = $i;
		$addnum = empty($arc_tpl['addnum']) ? 0 : $arc_tpl['addnum'];
		trbasic('附加页数量','',makeradio('fmdata[addnum]',$arr,$addnum),'');
		trbasic('文档搜索列表模板','fmdata[search][0]',makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('cindex'),@$arc_tpl['search'][0]),'select',array('guide' => cls_mtpl::mtplGuide('cindex')));
		trbasic('搜索附加页1模板','fmdata[search][1]',makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('cindex'),@$arc_tpl['search'][1]),'select',array('guide' => cls_mtpl::mtplGuide('cindex')));
		tabfooter();
		for($i = 0;$i <= $max_addno;$i ++){
			tabheader(($i ? '附加页'.$i : '基本页').'设置'.viewcheck(array('name' =>'viewdetail','title' => '详细','value' => $i > $addnum ? 0 : 1,'body' =>$actionid.'tbodyfilter'.$i)));
			echo "<tbody id=\"{$actionid}tbodyfilter$i\" style=\"display:".($i > $addnum ? 'none' : '')."\">";
			trbasic('页面模板',"fmdata[cfg][$i][tpl]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('archive'),empty($arc_tpl['cfg'][$i]['tpl']) ? '' : $arc_tpl['cfg'][$i]['tpl']),'select',array('guide' => cls_mtpl::mtplGuide('archive')));
			trbasic('是否生成静态','',makeradio("fmdata[cfg][$i][static]",array(0 => '按系统总设置',1 => '保持动态'),empty($arc_tpl['cfg'][$i]['static']) ? 0 : $arc_tpl['cfg'][$i]['static']),'');
			trbasic('静态格式中addno替代值',"fmdata[cfg][$i][addno]",empty($arc_tpl['cfg'][$i]['addno']) ? '' : $arc_tpl['cfg'][$i]['addno'],'text',array('guide'=>'文档内容页静态URL中{$addno}的套用值，留空时，此值规则：基本页=>空，附加页=>当前附加页数字序号'.($i ? $i : '').'。',));
			trbasic('静态更新周期(分钟)',"fmdata[cfg][$i][period]",empty($arc_tpl['cfg'][$i]['period']) ? '' : $arc_tpl['cfg'][$i]['period'],'text',array('guide'=>'留空则按系统总设置','w'=>4));
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
			foreach(array('tpl','static','addno','period','novu',) as $var){
				if(in_array($var,array('tpl','addno',))){
					$fmdata['cfg'][$i][$var] = trim(strip_tags($fmdata['cfg'][$i][$var]));
				}elseif(in_array($var,array('static','novu',))){
					$fmdata['cfg'][$i][$var] = empty($fmdata['cfg'][$i][$var]) ? 0 : 1;
				}elseif(in_array($var,array('period',))){
					$fmdata['cfg'][$i][$var] = max(0,intval($fmdata['cfg'][$i][$var]));
				}
				
				if(empty($fmdata['cfg'][$i][$var])){
					unset($arc_tpl['cfg'][$i][$var]);
				}else{
					$arc_tpl['cfg'][$i][$var] = $fmdata['cfg'][$i][$var];
				}
			}
			if(empty($arc_tpl['cfg'][$i])) unset($arc_tpl['cfg'][$i]);	
		}
		$arc_tpls[$tid] = $arc_tpl;
		cls_CacheFile::Save($arc_tpls,'arc_tpls','arc_tpls');
		adminlog('文档模板方案编辑');
		cls_message::show('文档模板方案修改完成',axaction(6,"?entry=$entry&action=arc_tplsedit"));
	}

}elseif($action == 'arc_tplsedit'){
	foreach(array('arc_tpls','channels',) as $k) $$k = cls_cache::Read($k);
	echo "<title>文档模板方案管理</title>";
	if(!submitcheck('bsubmit')){
		tabheader("文档模板方案管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=arc_tpladd\" onclick=\"return floatwin('open_cntplsedit',this)\">添加</a>",'cntplsedit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID',array('方案名称','txtL'),'排序','附加页','搜索页','删除','详情'));
		foreach($arc_tpls as $k => $v){
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
				$v['cname'] = $v['cname'] ? $v['cname'] : $arc_tpls[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				foreach(array('cname','vieworder',) as $var) $arc_tpls[$k][$var] = $v[$var];
			}
			adminlog('编辑文档模板方案');
			cls_Array::_array_multisort($arc_tpls,'vieworder',1);
			cls_CacheFile::Save($arc_tpls,'arc_tpls','arc_tpls');
		}
		cls_message::show('文档模板方案修改完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'arc_tpldel' && $tid){
	deep_allow($no_deepmode,"?entry=$entry&action=arc_tplsedit");
	$arc_tpls = cls_cache::Read('arc_tpls');
	if(!($arc_tpl = @$arc_tpls[$tid])) cls_message::show('请选择文档模板方案');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&tid=$tid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=arc_tplsedit>返回</a>";
		cls_message::show($message);
	}
	unset($arc_tpls[$tid]);
	cls_CacheFile::Save($arc_tpls,'arc_tpls','arc_tpls');
	cls_message::show('文档模板方案删除成功', "?entry=$entry&action=arc_tplsedit");
}
