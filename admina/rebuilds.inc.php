<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if(empty($action)){
	backnav('rebuilds','system');
	if($re = $curuser->NoBackFunc('affix')) cls_message::show($re);
	if(!submitcheck('brebuilds')){
		tabheader('刷新系统缓存',$actionid.'rebuilds',"?entry=rebuilds",2);
		trbasic('优化缓存更新','',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[excache]\" value=\"1\" checked>优化缓存更新",'',array('guide' => '当手动修改过缓存文件，需要将优化缓存更新才能使缓存生效。'));
		trbasic('系统内置缓存','',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[based]\" value=\"1\" checked>基本缓存&nbsp; <input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[cnode]\" value=\"1\">类目节点",'',array('guide' => '以上缓存为安装时系统内置，除非出现意外，一般不需要手动更新。'));
		trbasic('模板页面缓存','',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[common]\" value=\"1\" checked>前台模板&nbsp; <input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[mcenter]\" value=\"1\" checked>会员中心模板",'',array('guide' => '在调试状态关闭时，需要手动更新该缓存，才能使模板或标识的修改生效。<br>模板缓存是模板通过系统解释后的PHP文件(实际体现模板效果的文件)，位于dynamic/tplcache。'));
		trbasic('广告缓存','',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[adv]\" value=\"1\" checked>广告缓存",'',array('guide' => '选择刷新广告缓存后，系统会马上刷新所有的广告缓存，如果不选则按广告位缓存设置时间自动更新'));
		tabfooter('brebuilds');
		a_guide('rebuildcache');
	}else{
		if(!empty($arcdeal['excache'])){
			$m_excache->clear();
		}
		if(!empty($arcdeal['based'])){
			$mconfigs = cls_cache::Read('mconfigs');
			cls_CacheFile::ReBuild("cnodes,o_cnodes,mcnodes");
		}
		if(!empty($arcdeal['cnode'])){
			cls_CacheFile::Update("cnodes");
			cls_CacheFile::Update("o_cnodes");
			cls_CacheFile::Update("mcnodes");
		}
		if(!empty($arcdeal['common'])){
			clear_dir(cls_Parse::TplCacheDirFile(''));
		}
		
		if(!empty($arcdeal['mcenter'])){
			clear_dir(cls_Parse::TplCacheDirFile('',1));
		}
	
		if(!empty($arcdeal['adv'])) {
		    _08_Advertising::cheanAllCache();
        }
		cls_message::show('系统缓存更新完成！', "?entry=$entry");#做为常用链接会出现死循环
	}
}elseif($action == 'pagecache'){
	backnav('rebuilds','pagecache');
	if($re = $curuser->NoBackFunc('affix')) cls_message::show($re);
	$pctypes = array(
		1 => '类目节点|index.php',
		2 => '文档页|archive.php',
		3 => '独立页|info.php',
		4 => '搜索页|search.php',
		5 => '会员节点|member/index.php',
		6 => '会员搜索|member/search.php',
		7 => '空间栏目|mspace/index.php',
		8 => '空间文档|mspace/archive.php',
		9 => 'js缓存|tools/js.php',
		);
	$pc_records = cls_cache::Read('pc_records');
	if(!submitcheck('bsubmit')){
		tabheader("清理页面缓存 &nbsp;<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'typeids','chkall')\">全选",$actionid.'rebuilds',"?entry=$entry&action=$action",2);
		foreach($pctypes as $k => $v){
			!empty($pc_records[$k]) && $v .= ' &nbsp;[上次清理:'.date('Y-m-d H:i',$pc_records[$k]).']';
			trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"typeids[]\" value=\"$k\">",'',$v,'',array('guide' => "缓存文件保存目录:dynamic/htmlcac/$k/",));
		}
		tabfooter('bsubmit');
		a_guide('clearpagecache');
	}else{
		if(empty($typeids) && !empty($typeidstr)) $typeids = array_filter(explode(',',$typeidstr));
		if(empty($typeids)) cls_message::show('请选择需要清理的缓存类型');
		$typeid = array_shift($typeids);
		if(isset($pctypes[$typeid])){
			clear_dir(cls_cache::HtmlcacDir($typeid),true);
			$pc_records[$typeid] = $timestamp;
			cls_CacheFile::Save($pc_records,'pc_records');
		}

		if(empty($typeids)){
			cls_message::show('页面缓存清理完成！',"?entry=$entry&action=$action");
		}else{
			$typeidstr = implode(',',$typeids);
			cls_message::show("还有".count($typeids)."步，请耐心等待...","?entry=$entry&action=$action&typeidstr=$typeidstr&bsubmit=1");
		}
	}
}elseif($action == 'backup'){
	backnav('rebuilds','backup');
	if($re = $curuser->NoBackFunc('affix')) cls_message::show($re);
	@mmkdir(M_ROOT."dynamic/cathe_backup/",0);
	$templatedir = cls_env::GetG('templatedir');
	$extend_dir = cls_env::getBaseIncConfigs('_08_extend_dir'); //cls_env::GetG('templatedir');
	$dirkey = empty($dirkey) ? 'tpl_cache' : $dirkey;
	$tabback = array(
		'mconfig'       => array('dynamic/cache/mconfigs.cac.php','基本缓存'),
		'syscache'      => array("$extend_dir/dynamic/syscache",'系统缓存'),
		'tpl_config'    => array("template/$templatedir/config",'模版缓存'),
		'tpl_tag'       => array("template/$templatedir/tag",'模版标签'),
		'tpl_tpl'       => array("template/$templatedir/tpl",'模版文件'),
		'tpl_function'  => array("template/$templatedir/function",'模版函数'),
	);
	if(!isset($tabback[$dirkey])) $dirkey = 'tpl_config';
	$sarr = array();
	foreach($tabback as $k=>$v){
		$title = " title='对应文件/目录: {$v[0]}'";
		$arr[] = $dirkey == $k ? "<b $title>{$v[1]}({$k})</b>" : "<a href=\"?entry={$entry}&action={$action}&dirkey={$k}\" $title>{$v[1]}({$k})</a>";
	}
	echo tab_list($arr,7,0);
	if(!submitcheck('bsubmit')){
		$selector = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'backups','chkall')\">全选";
		tabheader("缓存备份列表 &nbsp;$selector",$actionid.'cachebackup',"?entry=$entry&action=$action&dirkey=$dirkey",2);
		$path = str_replace('/',DS,'dynamic/cathe_backup/');
		$dir = new DirectoryIterator(M_ROOT.$path);
		$num = 0;
		foreach($dir as $it){
			if($it->isDir() && !$it->isDot()){
				$dirname = $it->getFileName();
				$addtime = date('Y-m-d H:i:s',$it->getCTime()); //  title='备份时间:{$addtime}'
				if(preg_match("/^{$dirkey}[0-9\_]{15}$/",$dirname)){ //syscache_2014_0709_1658
					trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"backups[]\" value=\"$dirname\">",'',$tabback[$dirkey][1].'备份: '.$dirname,'',array('guide' => "备份目录:dynamic/cathe_backup/$dirname/",));
					$num++;
				}
			}
		}
		$cathedir = $tabback[$dirkey][1]."($dirkey): 对应文件/目录: {$tabback[$dirkey][0]}";
		if($num){
			trbasic("",'',"共{$num}个备份，<a href='?entry=$entry&action=$action&dirkey=$dirkey&do=back&bsubmit=backup' class='cBlue'>&gt;&gt;再添加一个备份</a>。",'',array('guide' => $cathedir,));	
			tabfooter('bsubmit','删除所选');
		}else{
			trbasic("",'',$tabback[$dirkey][1]." 暂无备份，<a href='?entry=$entry&action=$action&dirkey=$dirkey&do=back&bsubmit=backup' class='cBlue'>&gt;&gt;现在添加一个备份</a>。",'',array('guide' => $cathedir,));	
			tabfooter('','删除所选');
		}
		a_guide('backupcache');
	}else{
		if($bsubmit=='backup'){
			$actname = '备份缓存';
			$newdir = "{$dirkey}".date('_Y_md_Hi'); 
			$fulldir = "dynamic/cathe_backup/$newdir/";
			if(!is_dir(M_ROOT.$fulldir)){ 
				@mmkdir(M_ROOT.$fulldir,0);
			}else{
				cls_message::show('操作频繁，一分钟后在操作！',"?entry=$entry&action=$action&dirkey=$dirkey");
			}
			$path = str_replace('/',DS,$tabback[$dirkey][0]);
			if(is_file($path)){ 
				copy($path,M_ROOT.$fulldir.basename($path));
			}else{
				$iterator = new DirectoryIterator(M_ROOT.$path);
				foreach($iterator as $it){
					 if($it->isFile()) {
						 $filename = $it->getFileName();
						 $fullname = M_ROOT.$fulldir.$filename;
						 copy($it->getPathname(),$fullname);
					}
				}
			}
			$dores = ' 操作成功！';
		}else{
			$actname = '删除备份'; $n=0;
			$backups = empty($backups) ? array() : $backups;
			$fso = _08_FilesystemFile::getInstance();
			foreach($backups as $v){
				$fso->cleanPathFile("dynamic/cathe_backup/$v/");
				@rmdir(M_ROOT."dynamic/cathe_backup/$v/");
				$n++;
			}
			//cleanPathFile($path, $exts = '', $traversal = false)
			$dores = $n ? ' 操作成功！' : ' 操作失败！';
		}
		cls_message::show($actname.$dores,"?entry=$entry&action=$action&dirkey=$dirkey");
	}
}



?>