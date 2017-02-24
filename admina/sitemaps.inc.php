<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('sitemap')) cls_message::show($re);
foreach(array('catalogs','cotypes','channels',) as $k) $$k = cls_cache::Read($k);
$objcron=new cls_cron();
if($action == 'sitemapsedit'){
	$sitemaps = fetch_arr();
	if(!submitcheck('bsitemapsedit')){
		tabheader("Sitemap页面管理&nbsp; &nbsp; >><a href=\"?entry=sitemaps&action=sitemapsadd\" onclick=\"return floatwin('open_sitemapsadd',this)\">添加</a>",'sitemapsedit',"?entry=sitemaps&action=sitemapsedit",'8');
		trcategory(array('启用',array('Sitemap名称','txtL'),array('动态调用链接','txtL'),array('XML调用链接','txtL'),'排序','删?','详细','生成'));
		foreach($sitemaps as $ename => $sitemap){
			$d_url = "sitemap.php?map=$ename";
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"sitemapsnew[$ename][available]\" value=\"1\"".(empty($sitemap['available']) ? '' : ' checked')."></td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars($sitemap['cname'])."</td>\n".
				"<td class=\"txtL\"><a target=\"_blank\" href=\"".cls_url::view_url($d_url)."\">{$d_url}</a></td>\n".
				"<td class=\"txtL\"><a target=\"_blank\" href=\"".cls_url::view_url($sitemap['xml_url'])."\">".cls_url::view_url($sitemap['xml_url'])."</a></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"sitemapsnew[$ename][vieworder]\" value=\"$sitemap[vieworder]\"></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(empty($sitemap['issystem']) ? " name=\"delete[$ename]\" value=\"1\" onclick=\"deltip(this,$no_deepmode)\"" : ' disabled').">\n".
				"<td class=\"txtC w30\"><a href=\"?entry=sitemaps&action=sitemapdetail&ename=$ename\" onclick=\"return floatwin('open_sitemapdetail',this)\">设置</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=sitemaps&action=sitemapcreate&ename=$ename\">生成</a></td></tr>\n";
		}
		tabfooter('bsitemapsedit');
		
		$note = "- 此参数仅用于“百度主动推送”；请先 <a href='http://zhanzhang.baidu.com/linksubmit/index' style='color:blue;' target='_blank'>申请 百度主动推送 接口</a>；";
		$note .= "每天由计划任务推送指定的xml到如上地址； <br>- 示例：http://data.zz.baidu.com/urls?site=www.example.com&token=edk7yc4rEZP9pDQD。";
		tabheader('参数设置','sitemapsedit',"?entry=sitemaps&action=sitemapsedit&api=1");
		trbasic('主动推送接口地址','',"<label for='mconfigsnew[baidu_push_api]'></label><input type='text' id='mconfigsnew[baidu_push_api]' name='mconfigsnew[baidu_push_api]' value='".@$mconfigs['baidu_push_api']."' style='width:320px'>",'',array('guide'=>$note));
		trbasic('主动推送脚本名称','',"<label for='mconfigsnew[baidu_push_name]'></label><input type='text' id='mconfigsnew[baidu_push_name]' name='mconfigsnew[baidu_push_name]' value='".@$mconfigs['baidu_push_name']."' style='width:200px'>",'',array('guide'=>"此参数与<a href='?entry=misc&action=cronedit&isframe=1' style='color:blue;' target='_blank'>计划任务</a>里面的主动推送的脚本名称保持一致（例如：baidu_mob_push.php）"));
		trbasic('立即主动推送','','','',array('guide'=>" <a onclick=\"return floatwin('open_inarchive',this)\" href='?entry=sitemaps&action=sitemapsedit&push_now=1&bsitemapsedit=提交' style='color:blue;'>执行</a><p>说明：请先设置好'主动推送接口地址'和'主动推送脚本名称'</p>"));
		tabfooter('bsitemapsedit');
		a_guide('sitemapsedit');
	}else{
		
		$baidu_push_name = $mconfigs['baidu_push_name'];
		
		if($action == 'sitemapsedit' && @$api){ //设置主动推送接口参数
			
			$new_push_name = $mconfigsnew['baidu_push_name'];
			if(empty($new_push_name) || !$objcron->isFile($new_push_name)) cls_message::show('任务执行文件不存在或者重名！请检查"主动推送脚本名称"。','?entry=sitemaps&action=sitemapsedit');
			saveconfig('site');
			if($new_push_name != $baidu_push_name){ //如果主动推送脚本名称改变，对应修改计划任务脚本名称
				$db->query("update {$tblprefix}cron set filename='$new_push_name' where filename='$baidu_push_name'");
			}
 
			cls_message::show('接口设置完成',M_REFERER);	
						    			    		    
		}elseif($action == 'sitemapsedit' && @$push_now){//立即主动推送
			
		    if(empty($mconfigs['baidu_push_api'])){
		    	cls_message::show('请设置主动推送接口地址', axaction(6,'?entry=sitemaps&action=sitemapsedit'));
		    }
		    
		    if(empty($baidu_push_name) || !$objcron->isFile($baidu_push_name)) cls_message::show('任务执行文件不存在或者重名！请检查"主动推送脚本名称"。',axaction(6,'?entry=sitemaps&action=sitemapsedit'));
		    
		    $cronid = $db->result_one("select cronid from {$tblprefix}cron where filename='$baidu_push_name'");
		    if(empty($cronid)){
		    	cls_message::show('主动推送计划任务不存在', axaction(6,'?entry=sitemaps&action=sitemapsedit'));
		    }else{
		    		
		    	cls_SitemapPage::Create(array('map' => 'baidu_mob_push','inStatic' => true));

		    	$cronid = max(0,intval($cronid));
		    	$ret = $objcron->run($cronid);
		    	$msg = $ret ? '计划任务执行成功' : '<span style="color:red;">计划任务没执行</span>';
		    	cls_message::show($msg,axaction(6,'?entry=sitemaps&action=sitemapsedit'));
		    }
		
		}else{ // Sitemap页面管理 
			if(!empty($delete) && deep_allow($no_deepmode)){
				foreach($delete as $k=>$v){
					$db->query("DELETE FROM {$tblprefix}sitemaps WHERE ename='$k'");
				}
			}

			foreach($sitemapsnew as $ename => $v){
				$v['available'] = empty($v['available']) ? 0 : $v['available'];
				$db->query("UPDATE {$tblprefix}sitemaps SET available='$v[available]',vieworder='$v[vieworder]' WHERE ename='$ename'");
			}
			cls_CacheFile::Update('sitemaps');
			unset($delete);
			unset($sitemapsnew);
			cls_message::show('Sitemap修改完成', "?entry=sitemaps&action=sitemapsedit");
		}
	}
}elseif($action == 'sitemapdetail' && $ename){
	$sitemap = fetch_one($ename);
	empty($sitemap) && cls_message::show('请指定正确的Sitemap', '?entry=sitemaps&action=sitemapsedit');
	if(!submitcheck('bsitemapdetail')){
		tabheader('Sitemap设置','sitemapdetail','?entry=sitemaps&action=sitemapdetail&ename='.$ename);

		trhidden('ename',$sitemap['ename']);
		trbasic('* Sitemap名称','sitemapnew[cname]',$sitemap['cname']);
		trbasic('* 是否启用','sitemapnew[available]',isset($sitemap['available']) ? $sitemap['available'] : 0,'radio');
		trbasic('* XML文件名','sitemapnew[xml_url]',$sitemap['xml_url'],'text',array('guide' => 'XML文件名,如：example.xml'));
		trbasic('模板文件','sitemapnew[tpl]',makeoption(cls_mtpl::mtplsarr('xml'),$sitemap['tpl']),'select',array('guide' => cls_mtpl::mtplGuide('xml')));
		trbasic('周期','sitemapnew[ttl]',$sitemap['ttl'],'text',array('guide' => '更新周期，单位：小时'));
		trbasic('排序','sitemapnew[vieworder]',$sitemap['vieworder'],'text',array('guide' => '列表排序'));

		tabfooter('bsitemapdetail','修改');
		a_guide('sitemapdetail');
	}else{
		$ename || cls_message::show('缺少Sitemap标识',M_REFERER);
		$sitemapnew['cname'] = trim(strip_tags($sitemapnew['cname']));
		$sitemapnew['cname'] || cls_message::show('缺少Sitemap名称',M_REFERER);
		if($re = _08_FilesystemFile::CheckFileName($sitemapnew['xml_url'],'xml')) cls_message::show($re,M_REFERER);
		$sitemapnew['available'] = empty($sitemapnew['available']) ? 0 : 1;
		$sitemapnew['vieworder'] = empty($sitemapnew['vieworder']) ? 0 : max(0,intval($sitemapnew['vieworder']));
		$sitemapnew['tpl'] = empty($sitemapnew['tpl']) ? '' : $sitemapnew['tpl'];
		$sitemapnew['ttl'] = empty($sitemapnew['ttl']) ? 0 : max(0,intval($sitemapnew['ttl']));

		$db->query("UPDATE {$tblprefix}sitemaps SET cname='$sitemapnew[cname]',xml_url='$sitemapnew[xml_url]',available='$sitemapnew[available]',vieworder='$sitemapnew[vieworder]',tpl='$sitemapnew[tpl]',ttl='$sitemapnew[ttl]' WHERE ename='$ename'");
		cls_CacheFile::Update('sitemaps');
		adminlog('设置Sitemap');
		cls_message::show('Sitemap设置完成',axaction(6,"?entry=sitemaps&action=sitemapsedit"));
	}

}elseif($action == 'sitemapcreate' && $ename){
	$re = cls_SitemapPage::Create(array('map' => $ename,'inStatic' => true));
	cls_message::show($re, '?entry=sitemaps&action=sitemapsedit');
} elseif($action == 'sitemapsinfo' && $ename) {
	$sitemap = fetch_one($ename);
	empty($sitemap) && cls_message::show('请指定正确的Sitemap');
	tabheader("$sitemap[cname] 更多信息");
	trbasic('动态调用链接','',cls_url::view_url($sitemap['d_url']),'');
	trbasic('XML调用链接','',cls_url::view_url($sitemap['xml_url']),'');
	tabfooter();
	a_guide('sitemapsinfo');
} elseif($action == 'sitemapsadd') {
	if(!submitcheck('bgsitemapsadd')){
		tabheader('Sitemap添加','sitemapsadd',"?entry=sitemaps&action=sitemapsadd");
		trbasic('* Sitemap名称','sitemapsadd[cname]');
		trbasic('* Sitemap标识','','<input type="text" value="" name="sitemapsadd[ename]" id="sitemapsadd[ename]" size="25">&nbsp;&nbsp;<input type="button" value="检查重名" onclick="check_repeat(\'sitemapsadd[ename]\',\'check_sitemaps_repeat\');">','');
		trbasic('* XML文件名','sitemapsadd[xml_url]','','text',array('guide' => 'XML文件名,如：example.xml'));
		trbasic('模板文件','sitemapsadd[tpl]',makeoption(cls_mtpl::mtplsarr('xml')),'select',array('guide' => cls_mtpl::mtplGuide('xml')));
		trbasic('周期','sitemapsadd[ttl]','0','text',array('guide' => '更新周期，单位：小时'));
		trbasic('排序','sitemapsadd[vieworder]','0','text',array('guide' => '列表排序'));
		tabfooter('bgsitemapsadd','添加');
		a_guide('sitemapsadd');
	}else{
		$sitemapsadd['cname'] = trim(strip_tags($sitemapsadd['cname']));
		$sitemapsadd['ename'] || cls_message::show('缺少Sitemap标识',M_REFERER);
		$sitemapsadd['cname'] || cls_message::show('缺少Sitemap名称',M_REFERER);
		if($re = _08_FilesystemFile::CheckFileName($sitemapsadd['xml_url'],'xml')) cls_message::show($re,M_REFERER);
		//$sitemapsadd['tpl'] || cls_message::show('缺少模板文件',M_REFERER);
		$sitemapsadd['vieworder'] = empty($sitemapsadd['vieworder']) ? 0 : max(0,intval($sitemapsadd['vieworder']));
		$sitemapsadd['ttl'] = empty($sitemapsadd['ttl']) ? 0 : max(0,intval($sitemapsadd['ttl']));
		$db->query("INSERT INTO {$tblprefix}sitemaps SET ename='$sitemapsadd[ename]',cname='$sitemapsadd[cname]',xml_url='$sitemapsadd[xml_url]',available='1',vieworder='$sitemapsadd[vieworder]',tpl='$sitemapsadd[tpl]',ttl='$sitemapsadd[ttl]'");
		unset($sitemapsadd);
		adminlog('添加Sitemap');
		cls_message::show('Sitemap添加完成',axaction(6,"?entry=sitemaps&action=sitemapsedit"));
	}
}
function fetch_arr(){
	global $db,$tblprefix;
	$sitemaps = array();
	$query = $db->query("SELECT * FROM {$tblprefix}sitemaps ORDER BY vieworder");
	while($sitemap = $db->fetch_array($query)){
		$sitemaps[$sitemap['ename']] = $sitemap;
	}
	return $sitemaps;
}
function fetch_one($ename){
	global $db,$tblprefix;
	$sitemap = $db->fetch_one("SELECT * FROM {$tblprefix}sitemaps WHERE ename='$ename'");
	return $sitemap;
}
?>
