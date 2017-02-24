<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('catalog')) cls_message::show($re);
foreach(array('cotypes','channels','grouptypes','permissions','vcps','rprojects','cnrels','ca_tpl_cfgs','arc_tpls',) as $k) $$k = cls_cache::Read($k);
include_once M_ROOT."include/fields.fun.php";
$c_upload = cls_upload::OneInstance();	
if($action == 'catalogadd'){
	echo "<title>添加栏目</title>";
	$catalogs = cls_catalog::InitialInfoArray(0);
	$cafields = cls_cache::Read('cnfields',0);
	if(!submitcheck('bcatalogadd')){
		!cls_channel::chidsarr() && cls_message::show('请先添加有效的模型');
		$pid = empty($pid) ? 0 : $pid;
		if($pid){
			$pmsg = @$catalogs[$pid];
			$pmsg['tid'] = empty($ca_tpl_cfgs[$pid]) ? 0 : $ca_tpl_cfgs[$pid];
		}
		tabheader('添加栏目 - 基本设置','catalogadd',"?entry=$entry&action=$action",2,1,1);
		trbasic('栏目名称','catalognew[title]','','text',array('validate' => ' onfocus="initPinyin(\'catalognew[dirname]\')"' . makesubmitstr('catalognew[title]',1,0,0,30)));
		trbasic('静态文件保存目录','','<input type="text" value="" name="catalognew[dirname]" id="catalognew[dirname]" size="25" ' . makesubmitstr('catalognew[dirname]',1,'tagtype',0,30) . ' offset="2">&nbsp;&nbsp;<input type="button" value="检查重名" onclick="check_repeat(\'catalognew[dirname]\',\'dirname\');">&nbsp;&nbsp;<input type="button" value="自动拼音" onclick="autoPinyin(\'catalognew[title]\',\'catalognew[dirname]\')" />','');
		trbasic('父栏目','catalognew[pid]',makeoption(array('0' => '顶级栏目') + cls_catalog::ccidsarr(0), $pid),'select');
		trbasic('结构栏目(仅含子栏目)','catalognew[isframe]','','radio');
		trbasic('允许添加以下模型的文档','',makecheckbox('catalognew[chids][]',cls_channel::chidsarr(0),!empty($pmsg['chids']) ? explode(',',$pmsg['chids']) : array(),5),'');
		tabfooter();
		tabheader("模板设置");
		$na = array(0 => '按模型默认',);foreach($arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
		trbasic('文档模板方案','new_tid',makeoption($na,@$pmsg['tid']),'select',array('guide' => '留空则按文档所属模型的相关配置。<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'));
		trbasic('文档页静态保存格式','catalognew[customurl]',@$pmsg['customurl'],'text',array('guide'=>'留空为默认格式，{$topdir}顶级栏目目录，{$cadir}所属栏目目录，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。','w'=>50));
		tabfooter();
		tabheader("权限设置");
		setPermBar('附件下载权限设置', 'catalognew[dpmid]',@$pmsg['dpmid'], $source='down', $soext='open', '');
        trbasic('下载或播放附件扣除积分','catalognew[ftaxcp]',makeoption(array('' => '免费') + $vcps['ftax'],@$pmsg['ftaxcp']),'select');
		tabfooter();
		tabheader("其它信息");
		$a_field = new cls_field;
		foreach($cafields as $field){
			$a_field->init($field);
			$a_field->isadd = 1;
			$a_field->trfield('catalognew');
		}
		trbasic('添加完的后续操作','',makeradio('needtip',array('提示我下一步做什么','继续添加','关闭窗口'),empty($m_cookie["np_add_0"]) ? 0 : $m_cookie["np_add_0"]),'');
		tabfooter('bcatalogadd','添加');
		a_guide('catalogadd');
	} else {
		$catalognew['title'] = trim(strip_tags($catalognew['title']));
		if(!$catalognew['title'] || !$catalognew['dirname']) cls_message::show('栏目资料不完全',M_REFERER);
		if(preg_match("/[^a-zA-Z_0-9]+/",$catalognew['dirname'])) cls_message::show('栏目标识不合规范',M_REFERER);
		$catalognew['dirname'] = strtolower($catalognew['dirname']);
		if(in_array($catalognew['dirname'],cls_cache::Read('cn_dirnames'))) cls_message::show('栏目标识重复',M_REFERER);
		$catalognew['chids'] = !empty($catalognew['chids']) ? implode(',',$catalognew['chids']) : '';
		$catalognew['level'] = !$catalognew['pid'] ? 0 : $catalogs[$catalognew['pid']]['level'] + 1;
		$catalognew['customurl'] = preg_replace("/^\/+/",'',trim($catalognew['customurl']));

		$a_field = new cls_field;
		$sqlstr = "";
		foreach($cafields as $k => $v){
			$a_field->init($v);
			$a_field->deal('catalognew','cls_message::show',"?entry=$entry&action=catalogadd");
			$sqlstr .= ','.$k."='".$a_field->newvalue."'";
			if($arr = multi_val_arr($a_field->newvalue,$v)) foreach($arr as $x => $y) $sqlstr .= ','.$k.'_'.$x."='$y'";
		}
		$c_upload->saveuptotal(1);
		$catalognew['letter'] = empty($ca_autoletter) || empty($catalognew[$ca_autoletter]) ? '' : autoletter($catalognew[$ca_autoletter]);
		$db->query("INSERT INTO {$tblprefix}catalogs SET 
				   	caid = ".auto_insert_id('catalogs').",
					title='$catalognew[title]', 
					dirname='$catalognew[dirname]', 
					letter='$catalognew[letter]', 
					level='$catalognew[level]', 
					chids='$catalognew[chids]', 
					isframe='$catalognew[isframe]',
					dpmid='$catalognew[dpmid]',
					ftaxcp='$catalognew[ftaxcp]',
					customurl='$catalognew[customurl]',
					pid='$catalognew[pid]'
					$sqlstr
					");
		if($caid = $db->insert_id()){
			$c_upload->closure(1, $caid, 'catalogs');
			if($new_tid = empty($arc_tpls[$new_tid]) ? 0 : $new_tid) $ca_tpl_cfgs[$caid] = $new_tid;
			cls_CacheFile::Save($ca_tpl_cfgs,'ca_tpl_cfgs','ca_tpl_cfgs');
		}
		unset($a_field);
		
		adminlog('添加栏目');
		cls_catalog::DbTrueOrder(0);
		cls_CacheFile::Update('catalogs');
		
		
		$needtip = min(2,max(0,intval($needtip)));
		$needtip ? msetcookie("np_add_0",$needtip,31536000) : mclearcookie("np_add_0");
		$na = array(array('栏目添加成功',36,"follow"),array('继续添加下一个',36,$action),array('即将关闭窗口',6,'catalogedit'),);
		cls_message::show('栏目添加成功，'.$na[$needtip][0], axaction($na[$needtip][1],"?entry=$entry&action=".$na[$needtip][2]));
	}
}elseif($action == 'follow'){
	echo "<title>后续操作</title>";
	$cnrels = cls_cache::Read('cnrels');
	tabheader('添加栏目之后的后续操作');
	trbasic('后台管理节点','',"<a href=\"?entry=amconfigs&action=amconfigablock\"  onclick=\"return floatwin('open_fnodes',this)\">>>点我</a>：设置该栏目在管理后台常规管理中的节点",'');
	foreach($cnrels as $k => $v){
		if(in_array(0,array($v['coid'],$v['coid1']))){
			trbasic("$k.类目关联",'',"<a href=\"?entry=cnrels&action=cnreldetail&rid=$k&isframe=1\" target=\"_blank\">>>点我</a>：设置 [$v[cname]] 中的关联",'');
		}
	}
	$str = "<a href=\"?entry=cnodes&action=cnconfigs&ncoid=0&arcdeal=newupdate&isframe=1\" target=\"_blank\">>>方式1</a>：选择相关的节点组成方案，补全方案中的节点。此方式可手动选择";
	$str .= "<br><a href=\"?entry=cnodes&action=patchupdate&coid=0\" onclick=\"return floatwin('open_fnodes',this)\">>>方式2</a>：自动补全所有与栏目相关的节点组成方案，此方式一键完成。";
	trbasic('添加类目节点','',$str,'');
	trbasic('添加会员节点','',"<a href=\"?entry=mcnodes&action=mcnodeadd&isframe=1\" target=\"_blank\">>>点我</a>：手动添加需要的会员节点",'');
	$str = "<a href=\"?entry=o_cnodes&action=cnconfigs&ncoid=0&arcdeal=newupdate&isframe=1\" target=\"_blank\">>>方式1</a>：选择相关的节点组成方案，补全方案中的节点。此方式可手动选择";
	$str .= "<br><a href=\"?entry=o_cnodes&action=patchupdate&coid=0\" onclick=\"return floatwin('open_fnodes',this)\">>>方式2</a>：自动补全所有与栏目相关的节点组成方案，此方式一键完成。";
	trbasic('添加手机版节点','',$str,'');
	tabfooter();
}elseif($action == 'catalogadds'){
	backnav('catalog','adds');
	echo "<title>批量添加栏目</title>";
	cls_channel::chidsarr() || cls_message::show('请先添加有效的模型');
	$catalogs = cls_catalog::InitialInfoArray(0);
	$cafields = cls_cache::Read('cnfields',0);
	if($pid = empty($pid) ? 0 : $pid){
		$pmsg = @$catalogs[$pid];
		$pmsg['tid'] = empty($ca_tpl_cfgs[$pid]) ? 0 : $ca_tpl_cfgs[$pid];
	}
	$chids = cls_channel::chidsarr(0);
	$_settings = array(
		'pid' => array(
			'type' => 'select',
			'title' => '父栏目',
			'value' => makeoption(array('0' => '顶级栏目') + cls_catalog::ccidsarr(0), $pid)
		),
		'isframe' => array(
			'type' => 'radio',
			'title' => '结构栏目(仅含子栏目)',
			'value' => ''
		),
		'chids' => array(
			'type' => '',
			'title' => '允许添加以下模型的文档',
			'value' => makecheckbox('catalogsame[chids][]',$chids,!empty($pmsg['chids']) ? explode(',',$pmsg['chids']) : array(),5)
		),
		'dpmid' => array(
			'type' => 'select',
			'title' => '附件下载权限设置',
			'value' => makeoption(pmidsarr('down'),@$pmsg['dpmid'])
		),
		'ftaxcp' => array(
			'type' => 'select',
			'title' => '下载或播放附件扣除积分',
			'value' => makeoption(array('' => '免费') + $vcps['ftax'],@$pmsg['ftaxcp'])
		),
	);
	$_settings['customurl'] = array(
			'type' => 'text',
			'title' => '文档页静态保存格式',
			'value' => @$pmsg['customurl'],
			'tip' => '留空为默认格式，{$topdir}顶级栏目目录，{$cadir}所属栏目目录，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。'
			);

	$na = array(0 => '按模型默认',);foreach($arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
	$_settings['tid'] = array(
			'type' => 'select',
			'title' => '文档模板方案',
			'value' => makeoption($na,@$pmsg['tid']),
			'tip' => '留空则按文档所属模型的相关配置。<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'
			);
	foreach($cafields as $k => $v){
		$_settings[$k] = array(
			'type' => 'field',
			'title' => $v['cname']
		);
	}
	if(!submitcheck('bcatalogset') && !submitcheck('bcatalogadd')){
		tabheader('批量添加栏目 - 基本设置','catalogadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('批量添加栏目数量','batch_count','','text',array('validate'=>' rule="int" must="1" min="1" max="200"'));
		trbasic('需要分别设置的项','','','');

		trbasic('<input class="checkbox" type="checkbox" checked="checked" disabled="disabled" />', '', '静态文件保存目录'.
			' <input class="checkbox" type="checkbox" name="auto_pinyin" id="auto_pinyin" value="1" /><label for="auto_pinyin">自动拼音</label>', '');
		foreach($_settings as $k => $v)trbasic('<input class="checkbox" type="checkbox" name="diffitems[]" value="'.$k.'" />', '', $v['title'], '');
		tabfooter('bcatalogset');
	}elseif(!submitcheck('bcatalogadd')){
		$batch_count = max(0, intval($batch_count));
		empty($batch_count) && cls_message::show('请填写批量添加的栏目数量', M_REFERER);
		$_diffitems = array(
			'title' => array(
				'type' => 'text',
				'title' => '栏目名称',
				'value' => ''
			),
		);
		empty($auto_pinyin) && $_diffitems['dirname'] = array(
			'type' => 'text',
			'title' => '静态文件保存目录',
			'value' => ''
		);

		$a_field = new cls_field;
		empty($diffitems) && $diffitems = array();
		tabheader('批量添加栏目 - 相同设置','catalogadd',"?entry=$entry&action=$action",2,1,1);
		foreach($_settings as $k => $v){
			if(in_array($k, $diffitems)){
				$_diffitems[$k] = $v;
			}elseif($v['type'] == 'field'){
				$a_field->init($cafields[$k]);
				$a_field->isadd = 1;
				$a_field->trfield('catalogsame');
			}else{
				trbasic($v['title'], "catalogsame[$k]", $v['value'], $v['type'], array('guide' => array_key_exists('tip', $v) ? $v['tip'] : ''));
			}
		}
		trbasic('添加完的后续操作','',makeradio('needtip',array('提示我下一步做什么','继续添加','返回管理界面'),empty($m_cookie["np_adds_0"]) ? 0 : $m_cookie["np_adds_0"]),'');
		tabfooter();
		
		echo '<br /><br />'.(empty($auto_pinyin) ? '' : '<input type="hidden" name="auto_pinyin" value="1" />');
		for($i = 0; $i < $batch_count; $i++){
			tabheader('批量添加 - 栏目'.($i+1));
			foreach($_diffitems as $k => $v){
				if($v['type'] == 'field'){
					$a_field->init($cafields[$k]);
					$a_field->isadd = 1;
					$a_field->trfield("catalogitems[$i]");
				}else{
					trbasic($v['title'], "catalogitems[$i][$k]", $k != 'chids' ? $v['value'] : makecheckbox("catalogitems[$i][chids][]",$chids,!empty($pmsg['chids']) ? explode(',',$pmsg['chids']) : array(),5), $v['type'], array('guide' => array_key_exists('tip', $v) ? $v['tip'] : ''));
				}
			}
			tabfooter();
		}
		echo '<br /><input class="btn" type="submit" name="bcatalogadd" value="提交">';
		a_guide('catalogadd');
	}else{
		$enamearr = cls_cache::Read('cn_dirnames');
		$ok = 0;
		$a_field = new cls_field;
		foreach($catalogitems as $item){
			$catalognew = $catalogsame;
			foreach($item as $k => $v){
				if(is_array($v)){
					foreach($v as $a => $b)$catalognew[$k][$a] = $b;
				}else{
					$catalognew[$k] = $v;
				}
			}

			$catalognew['title'] = trim(strip_tags($catalognew['title']));
			empty($auto_pinyin) || $catalognew['dirname'] = cls_string::Pinyin($catalognew['title']);
			if(!$catalognew['title'] || !$catalognew['dirname'])continue;
			if(preg_match("/\W/",$catalognew['dirname']))continue;
			$catalognew['dirname'] = strtolower($catalognew['dirname']);
			if(empty($auto_pinyin)){
				if(in_array($catalognew['dirname'], $enamearr))continue;
			}else{
				$i = 1;
				$dirname = $catalognew['dirname'];
				while(in_array($catalognew['dirname'], $enamearr))$catalognew['dirname'] = $dirname.($i++);
			}

			$catalognew['chids'] = !empty($catalognew['chids']) ? implode(',',$catalognew['chids']) : '';
			$catalognew['level'] = !$catalognew['pid'] ? 0 : $catalogs[$catalognew['pid']]['level'] + 1;
			$catalognew['customurl'] = preg_replace("/^\/+/",'',trim($catalognew['customurl']));
	
			$sqlstr = "";
			foreach($cafields as $k => $v){
				$a_field->init($v);
				$a_field->deal('catalognew');
				if(!empty($a_field->error)) break;
				$sqlstr .= ','.$k."='".$a_field->newvalue."'";
				if($arr = multi_val_arr($a_field->newvalue,$v)) foreach($arr as $x => $y) $sqlstr .= ','.$k.'_'.$x."='$y'";

			}
			if(!empty($a_field->error))continue;
			$c_upload->saveuptotal(1);
			$catalognew['letter'] = empty($ca_autoletter) || empty($catalognew[$ca_autoletter]) ? '' : autoletter($catalognew[$ca_autoletter]);
			$db->query("INSERT INTO {$tblprefix}catalogs SET 
					    caid = ".auto_insert_id('catalogs').",
						title='$catalognew[title]', 
						dirname='$catalognew[dirname]', 
						letter='$catalognew[letter]', 
						level='$catalognew[level]', 
						chids='$catalognew[chids]', 
						isframe='$catalognew[isframe]',
						dpmid='$catalognew[dpmid]',
						ftaxcp='$catalognew[ftaxcp]',
						customurl='$catalognew[customurl]',
						pid='$catalognew[pid]'
						$sqlstr
						");
			
			if($caid = $db->insert_id()){
				$enamearr[] = $catalognew['dirname'];
				if($new_tid = empty($arc_tpls[$catalognew['tid']]) ? 0 : $catalognew['tid']) $ca_tpl_cfgs[$caid] = $new_tid;
				$ok++;
			}
			$c_upload->closure(1,$caid,'catalogs');
		}
		unset($a_field);
		
		adminlog('批量添加栏目');
		cls_catalog::DbTrueOrder(0);
		cls_CacheFile::Update('catalogs');
		cls_CacheFile::Save($ca_tpl_cfgs,'ca_tpl_cfgs','ca_tpl_cfgs');
		
		$needtip = min(2,max(0,intval($needtip)));
		$needtip ? msetcookie("np_adds_0",$needtip,31536000) : mclearcookie("np_adds_0");
		$na = array(array('查看后续事务',36,"follow"),array('继续添加下一批',36,$action),array('即将返回管理界面',6,'catalogedit'),);
		cls_message::show(($ok ? "成功添加 $ok 个栏目," : '批量添加失败').$na[$needtip][0], axaction($na[$needtip][1],"?entry=$entry&action=".$na[$needtip][2]));
	}
}elseif($action == 'catalogedit'){
	backnav('catalog','admin');
	$catalogs = cls_catalog::InitialInfoArray(0);
	if(!submitcheck('bcatalogedit')){
		$addfieldstr = "&nbsp; &nbsp;>><a href=\"?entry=$entry&action=catalogadd\" title=\"添加单个栏目\" onclick=\"return floatwin('open_catalogedit',this)\">添加栏目</a>";
		$addfieldstr .= "&nbsp; &nbsp;>><a href=\"?entry=$entry&action=follow\" title=\"添加栏目之后的相关设置\" onclick=\"return floatwin('open_catalogedit',this)\">添加栏目后的后续设置</a>";
		echo form_str('catalogedit', "?entry=$entry&action=catalogedit");
		echo "<div class=\"conlist1\">栏目管理$addfieldstr</div>";
		echo '<script type="text/javascript">var cata = [';
		foreach($catalogs as $caid => $catalog)echo "[$catalog[level],$caid,'" . str_replace("'","\\'",mhtmlspecialchars($catalog['title'])) . "',$catalog[vieworder]],";
		empty($treesteps) && $treesteps = '';
		echo <<<DOT
];
document.write(tableTree({data:cata,ckey:'ckey_0_',step:'$treesteps'.split(',')[0],html:{
		head: '<td class="txtC" width="30"><input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form,\'selectid\',\'chkall\')"></td>'
			+ '<td class="txtC" width="40">ID</td>'
			+ '<td class="txtL" width="350"%code%>栏目名称 %input%</td>'
			+ '<td class="txtC" width="40">排序</td>'
			+ '<td class="txtC" width="40">添加</td>'
			+ '<td class="txtC" width="40">详情</td>'
			+ '<td class="txtC" width="40">删除</td>',
		cell:[2,4],
		rows:'<td class="txtC" width="30"><input class="checkbox" name="selectid[%1%]" value="'
					+ '%1%" type="checkbox" onclick="tableTree.setChildBox()" /></td>'
			+ '<td class="txtC" width="40">%1%</td>'
			+ '<td class="txtL" width="400">%ico%<input name="catalogsnew['
					+ '%1%][title]" value="%2%" size="25" maxlength="30" type="text" /></td>'
			+ '<td class="txtC" width="40"><input name="catalogsnew['
					+ '%1%][vieworder]" value="%3%" type="text" style="width:36px" /></td>'
			+ '<td class="txtC" width="40"><a href="?entry=$entry&action=catalogadd&pid='
					+ '%1%" onclick="return floatwin(\'open_catalogedit\',this)">添加</a></td>'
			+ '<td class="txtC" width="40"><a href="?entry=$entry&action=catalogdetail&caid='
					+ '%1%" onclick="return floatwin(\'open_catalogedit\',this)">详情</a></td>'
			+ '<td class="txtC" width="40"><a href="?entry=$entry&action=catalogdelete&caid=%1%" onclick="return deltip()">删除</a></td>'
		},
	callback : true
}));
DOT;
		echo '</script>';

		tabheader('操作项目'.viewcheck(array('name' => 'viewdetail','value' =>0,'body' =>$actionid.'tbodyfilter',)).' &nbsp;显示详细');
		echo "<tbody id=\"{$actionid}tbodyfilter\" style=\"display:none\">";
		$s_arr = array();
		$s_arr['letter'] = '更新首字母';
		$s_arr['noletter'] = '清空首字母';
		if($s_arr){
			$soperatestr = '';$i = 1;
			foreach($s_arr as $k => $v){
				$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\">$v &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$soperatestr,'');
		}
		if($paidsarr = cls_pusher::paidsarr('catalogs',0)){ # 推送位
			$soperatestr = '';
			$i = 1;
			foreach($paidsarr as $k => $v){
				$soperatestr .= OneCheckBox("arcdeal[$k]",cls_pusher::AllTitle($k,1,1),0,1)." &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			$soperatestr && trbasic('选择推送位','',$soperatestr,'');
		}
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[pid]\" value=\"1\">&nbsp;重设父栏目",'arcpid',makeoption(array('0' => '顶级栏目') + cls_catalog::ccidsarr(0)),'select');
		$cnmodearr = array(0 => '修改配置中设置',1 => '在原配置中添加',2 => '从原配置中移除',);
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[chids]\" value=\"1\">&nbsp;允许添加以下模型的文档<br><input class=\"checkbox\" type=\"checkbox\" name=\"chkallc\" onclick=\"checkall(this.form,'arcchids','chkallc')\">全选",'',"<select id=\"cnmode\" name=\"cnmode\" style=\"vertical-align: middle;\">".makeoption($cnmodearr)."</select><br>".makecheckbox('arcchids[]',cls_channel::chidsarr(0),array(),5),'');
		$na = array(0 => '按模型默认',);foreach($arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[tid]\" value=\"1\">&nbsp;文档模板方案",'arctid',makeoption($na),'select',array('guide' => '留空则按文档所属模型的相关配置。<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'));
		setPermBar("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[dpmid]\" value=\"1\">&nbsp;附件下载权限设置", 'arcdpmid','', $source='down', 'open', '');
        trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[ftaxcp]\" value=\"1\">&nbsp;下载或播放附件扣除积分",'arcftaxcp',makeoption(array('' => '免费') + $vcps['ftax']),'select');
		echo "</tbody>";
		tabfooter('bcatalogedit');
		a_guide('catalogedit');
	}
	else{
		if(isset($catalogsnew)){
			foreach($catalogsnew as $caid => $catalognew){
				$catalognew['title'] = trim(strip_tags($catalognew['title']));
				$catalognew['title'] = $catalognew['title'] ? $catalognew['title'] : $catalogs[$caid]['title'];
				$catalognew['vieworder'] = max(0,intval($catalognew['vieworder']));
				if(($catalognew['title'] != $catalogs[$caid]['title']) || ($catalognew['vieworder'] != $catalogs[$caid]['vieworder'])){
					$db->query("UPDATE {$tblprefix}catalogs SET 
								title='$catalognew[title]', 
								vieworder='$catalognew[vieworder]' 
								WHERE caid='$caid'
								");
				}
			}
		}
		if(!empty($selectid) && !empty($arcdeal)){
			$sqlstr = '';
			if(!empty($arcdeal['dpmid'])) $sqlstr .= ",dpmid='$arcdpmid'";
			if(!empty($arcdeal['ftaxcp'])) $sqlstr .= ",ftaxcp='$arcftaxcp'";
			$sqlstr = substr($sqlstr,1);
			$sqlstr && $db->query("UPDATE {$tblprefix}catalogs SET $sqlstr WHERE caid ".multi_str($selectid));
			if(!empty($arcdeal['chids'])){
				foreach($selectid as $caid){
					$chidsnew = empty($arcchids) ? array() : $arcchids;
					if(!empty($cnmode)){
						$chids = empty($catalogs[$caid]['chids']) ? array() : explode(',',$catalogs[$caid]['chids']);
						$chidsnew = $cnmode == 1 ? array_unique(array_merge($chids,$chidsnew)) : array_diff($chids,$chidsnew);
					}
					$chidsnew = !empty($chidsnew) ? implode(',',$chidsnew) : '';
					$db->query("UPDATE {$tblprefix}catalogs SET chids='$chidsnew' WHERE caid='$caid'");
				}
			}
			# 推送位
			if($paidsarr = cls_pusher::paidsarr('catalogs',0)){
				foreach($paidsarr as $k => $v){
					if(!empty($arcdeal[$k])){
						foreach($selectid as $caid){
							cls_catalog::push(0,$caid,$k);
						}
					}
				}
			}
			if(!empty($arcdeal['letter']) && $ca_autoletter){
				foreach($selectid as $caid){
					$letter = autoletter(@$catalogs[$caid][$ca_autoletter]);
					$db->query("UPDATE {$tblprefix}catalogs SET letter='$letter' WHERE caid='$caid'");
				}
			}
			//清空首字母
			if(!empty($arcdeal['noletter'])){
				foreach($selectid as $caid){						
					$db->query("UPDATE {$tblprefix}catalogs SET letter='' WHERE caid='$caid'");
				}
			}
			if(!empty($arcdeal['pid'])){
				foreach($selectid as $caid){
					$sonids = cls_catalog::cnsonids($caid,$catalogs);
					if(in_array($arcpid,$sonids)) continue;
					$newlevel = !$arcpid ? 0 : $catalogs[$arcpid]['level'] + 1;
					$db->query("UPDATE {$tblprefix}catalogs SET pid='$arcpid',level='$newlevel' WHERE caid='$caid'");
					$leveldiff = $newlevel - $catalogs[$caid]['level'];
					foreach($sonids as $sonid) if($sonid != $caid) $db->query("UPDATE {$tblprefix}catalogs SET level=level+".$leveldiff." WHERE caid='$sonid'");
				}
			}
			if(!empty($arcdeal['tid'])){
				$arctid = empty($arc_tpls[$arctid]) ? 0 : $arctid;
				foreach($selectid as $caid){
					if($arctid){
						$ca_tpl_cfgs[$caid] = $arctid;
					}else unset($ca_tpl_cfgs[$caid]);
				}
			}
		}
		cls_catalog::DbTrueOrder(0);
		cls_CacheFile::Update('catalogs');
		cls_CacheFile::Save($ca_tpl_cfgs,'ca_tpl_cfgs','ca_tpl_cfgs');
		adminlog('编辑栏目管理列表');
		cls_message::show('栏目编辑完成', "?entry=$entry&action=catalogedit");
	}
}elseif($action =='catalogdetail' && $caid){
	if(!($catalog = cls_catalog::InitialOneInfo(0,$caid))) cls_message::show('请指定正确的栏目。');
	echo "<title>栏目详情[$catalog[title]]</title>";
	$catalogs = cls_catalog::InitialInfoArray(0);
	$cafields = cls_cache::Read('cnfields',0);
	if(!submitcheck('bcatalogdetail')){
		tabheader('栏目基本设置'."&nbsp;&nbsp;[$catalog[title]]",'catalogdetail',"?entry=$entry&action=catalogdetail&caid=$caid",2,1,1);
		trbasic('静态文件保存目录','catalognew[dirname]',$catalog['dirname'],'text',array('guide'=>'请谨慎操作，修改静态目录后，需要针对相关页面修复静态链接或重新生成静态。'));
		trbasic('父栏目','catalognew[pid]',makeoption(array('0' => '顶级栏目') + cls_catalog::ccidsarr(0),$catalog['pid']),'select');
		trbasic('结构栏目(仅含子栏目)','catalognew[isframe]',$catalog['isframe'],'radio');
		trbasic('允许添加以下模型的文档','',makecheckbox('catalognew[chids][]',cls_channel::chidsarr(0),!empty($catalog['chids']) ? explode(',',$catalog['chids']) : array(),5),'');
		tabfooter();
		tabheader("模板设置");
		$tid = empty($ca_tpl_cfgs[$caid]) ? 0 : $ca_tpl_cfgs[$caid];
		$na = array(0 => '按模型默认',);foreach($arc_tpls as $k => $v) $na[$k] = $v['cname']."($k)";
		trbasic('文档模板方案','new_tid',makeoption($na,$tid),'select',array('guide' => '留空则按文档所属模型的相关配置。<br>模板方案指定了内容页及搜索列表所用的模板,方案管理：模板设置->文档模板->文档模板方案'));
		trbasic('文档页静态保存格式','catalognew[customurl]',$catalog['customurl'],'text',array('guide'=>'留空为默认格式，{$topdir}顶级栏目目录，{$cadir}所属栏目目录，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。','w'=>50));
		tabfooter();
		tabheader("权限设置");
		setPermBar('附件下载权限设置', 'catalognew[dpmid]', @$catalog['dpmid'] , $source='down', $soext='open', '');
        trbasic('下载或播放附件扣除积分','catalognew[ftaxcp]',makeoption(array('' => '免费') + $vcps['ftax'],$catalog['ftaxcp']),'select');
		tabfooter();
		$a_field = new cls_field;
		$addfieldstr = "&nbsp; &nbsp; >><a href=\"?entry=$entry&action=cafieldsedit\">栏目字段</a>";
		tabheader("其它信息");
		foreach($cafields as $field){
			$a_field->init($field,isset($catalog[$field['ename']]) ? $catalog[$field['ename']] : '');
			$a_field->trfield('catalognew');
		}
		tabfooter('bcatalogdetail');
		a_guide('catalogdetail');
	}else{
		$catalognew['dirname'] = strtolower($catalognew['dirname']);
		if($catalognew['dirname'] != $catalog['dirname']){
			if(preg_match("/[^a-zA-Z_0-9]+/",$catalognew['dirname'])) cls_message::show('栏目标识不合规范',M_REFERER);
			if(in_array($catalognew['dirname'],cls_cache::Read('cn_dirnames'))) cls_message::show('栏目标识重复',M_REFERER);
		}
		$sonids = cls_catalog::cnsonids($caid,$catalogs);
		in_array($catalognew['pid'],$sonids) && cls_message::show('类目不能转到原类目及其子类目下',"?entry=$entry&action=catalogdetail&caid=$caid");
		$catalognew['chids'] = !empty($catalognew['chids']) ? implode(',',$catalognew['chids']) : '';
		$catalognew['level'] = !$catalognew['pid'] ? 0 : $catalogs[$catalognew['pid']]['level'] + 1;
		$catalognew['customurl'] = preg_replace("/^\/+/",'',trim($catalognew['customurl']));

		$a_field = new cls_field;
		$sqlstr = "";
		foreach($cafields as $k => $v){
			$a_field->init($v,isset($catalog[$k]) ? $catalog[$k] : '');
			$a_field->deal('catalognew','cls_message::show',"?entry=$entry&action=catalogdetail&caid=$caid");
			$sqlstr .= ','.$k."='".$a_field->newvalue."'";
			if($arr = multi_val_arr($a_field->newvalue,$v)) foreach($arr as $x => $y) $sqlstr .= ','.$k.'_'.$x."='$y'";
		}
		$c_upload->closure(1, $caid, 'catalogs');
		$c_upload->saveuptotal(1);
		unset($a_field);

		$leveldiff = $catalognew['level'] - $catalog['level'];
		foreach($sonids as $sonid) $db->query("UPDATE {$tblprefix}catalogs SET level=level+".$leveldiff." WHERE caid='$sonid'");
		if(!empty($ca_autoletter) && ($LetterSource = empty($catalognew[$ca_autoletter]) ? @$catalog[$ca_autoletter] : $catalognew[$ca_autoletter])){
			$catalognew['letter'] = autoletter($LetterSource);
		}else $catalognew['letter'] = '';
		$db->query("UPDATE {$tblprefix}catalogs SET
			dirname='$catalognew[dirname]',
			letter='$catalognew[letter]',
			pid='$catalognew[pid]',
			chids='$catalognew[chids]', 
			level='$catalognew[level]',
			isframe='$catalognew[isframe]',
			dpmid='$catalognew[dpmid]',
			ftaxcp='$catalognew[ftaxcp]',
			customurl='$catalognew[customurl]'
			$sqlstr
			WHERE caid='$caid'");
		adminlog('详细修改栏目');
		cls_catalog::DbTrueOrder(0);
		cls_CacheFile::Update('catalogs');
		
		$new_tid = empty($arc_tpls[$new_tid]) ? 0 : $new_tid;
		if($new_tid){
			$ca_tpl_cfgs[$caid] = $new_tid;
		}else unset($ca_tpl_cfgs[$caid]);
		cls_CacheFile::Save($ca_tpl_cfgs,'ca_tpl_cfgs','ca_tpl_cfgs');
		
		cls_message::show('栏目设置完成', axaction(6,"?entry=$entry&action=catalogedit"));
	}

}elseif($action == 'catalogdelete' && $caid){
	backnav('catalog','admin');
	deep_allow($no_deepmode && in_array($caid,@explode(',',$deep_caids)),"?entry=$entry&action=catalogedit");
	if(!($catalog = cls_catalog::InitialOneInfo(0,$caid))) cls_message::show('请指定正确的栏目。',"?entry=$entry&action=catalogedit");
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=catalogdelete&caid=$caid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=catalogedit>返回</a>";
		cls_message::show($message);
	}
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}catalogs WHERE pid='$caid'")) {
		cls_message::show('栏目没有相关联的子栏目才能删除', "?entry=$entry&action=catalogedit");
	}
	$na = stidsarr(1);
	foreach($na as $k => $v){
		$db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl($k,1)." WHERE caid='$caid'") && cls_message::show('删除栏目请先清空栏目内的文档', "?entry=$entry&action=catalogedit");
	}

	//删除相关的节点
	$db->query("DELETE FROM {$tblprefix}cnodes WHERE caid='$caid'");
	cls_CacheFile::Update('cnodes');
	$db->query("DELETE FROM {$tblprefix}o_cnodes WHERE caid='$caid'");
	cls_CacheFile::Update('o_cnodes');
	$db->query("DELETE FROM {$tblprefix}mcnodes WHERE mcnvar='caid' AND mcnid='$caid'");
	cls_CacheFile::Update('mcnodes');

	$db->query("DELETE FROM {$tblprefix}catalogs WHERE caid='$caid'");
	adminlog('删除栏目');
	cls_CacheFile::Update('catalogs');
	//更正类目关联
	$cnrels = cls_cache::Read('cnrels');
	foreach($cnrels as $k => $v){
		$alter = false;
		if(!$v['coid'] && isset($v['cfgs'][$caid])){
			unset($v['cfgs'][$caid]);
			$alter = true;
		}
		if(!$v['coid1']){
			foreach($v['cfgs'] as $x => $y){
				$a = empty($y) ? array() : array_filter(explode(',',$y));
				if(in_array($caid,$a)){
					$a = array_filter($a,"clearsameid");
					$v['cfgs'][$x] = implode(',',$a);
					$alter = true;
				}
			}
		}
		$alter && $db->query("UPDATE {$tblprefix}cnrels SET cfgs='".(empty($v['cfgs']) ? '' : addslashes(var_export($v['cfgs'],TRUE)))."' WHERE rid='$k'",'SILENT');
	}
	cls_CacheFile::Update('cnrels');
	cls_message::show('栏目删除完成', "?entry=$entry&action=catalogedit");
}elseif($action == 'cafieldsedit'){
	backnav('catalog','fields');
	echo "<title>栏目字段</title>";
	$fields = cls_fieldconfig::InitialFieldArray('catalog',0);
	if(!submitcheck('bcafieldsedit')){
		$addfieldstr = "&nbsp; &nbsp; >><a href=\"?entry=$entry&action=fieldone\" onclick=\"return floatwin('open_{$actionid}_cafieldadd',this);\">添加字段</a>";
		tabheader('栏目信息字段管理'.$addfieldstr,'cafieldsedit',"?entry=$entry&action=cafieldsedit",'5');
		trcategory(array('启用',array('字段名称','txtL'),'排序',array('字段标识','txtL'),array('数据表','txtL'),'字段类型','删除','编辑'));
		foreach($fields as $k => $v) {
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
				"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars($k)."</td>\n".
				"<td class=\"txtL\">$v[tbl]</td>\n".
				"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
				"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=fieldone&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a></td>\n".
				"</tr>";
		}
		tabfooter('bcafieldsedit');
		a_guide('cafieldsedit');
	}else{
		if(!empty($delete)){
			$deleteds = cls_fieldconfig::DeleteField('catalog',0,$delete);
			foreach($deleteds as $k){
				unset($fieldsnew[$k]);
			}
		}
		if(!empty($fieldsnew)){
			foreach($fieldsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $fields[$k]['cname'];
				$v['available'] = $fields[$k]['issystem'] || !empty($v['available']) ? 1 : 0;
				$v['vieworder'] = max(0,intval($v['vieworder']));
				cls_fieldconfig::ModifyOneConfig('catalog',0,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('catalog',0);
		adminlog('编辑栏目信息字段管理列表');
		cls_message::show('字段修改完成',"?entry=$entry&action=cafieldsedit");
	}
}elseif($action == 'fieldone'){
	cls_FieldConfig::EditOne('catalog',0,@$fieldname);

}elseif($action == 'mconfigs'){
	backnav('catalog','mconfigs');
	echo "<title>栏目参数</title>";
	$treestep = empty($mconfigs['treesteps']) ? '' : explode(',', $mconfigs['treesteps']);
	if(!submitcheck('bmconfigs')){
		tabheader('栏目参数设置','cfview',"?entry=$entry&action=$action");
		is_array($treestep) && $treestep = $treestep[0];#第0个为栏目的
		$ca_vmodearr = array('0' => '普通选择列表','1' => '单选按钮','2' => '多级联动','3' => '多级联动(ajax)',);
		trbasic('栏目选择时列表方式','',makeradio('mconfigsnew[ca_vmode]',$ca_vmodearr,empty($mconfigs['ca_vmode']) ? 0 : $mconfigs['ca_vmode']),'');
		trbasic('选择列表隐藏不可选项','mconfigsnew[catahidden]',$mconfigs['catahidden'],'radio');
		trbasic('管理界面树形分页显示', 'mconfigsnew[treesteps]', $treestep, 'text', array('guide'=>'请输入每页行数，留空为不分页。当类目数量过多时，建议设为10-30间的整数。'));
		$fields = cls_fieldconfig::InitialFieldArray('catalog',0);
		$arr = array('' => '不设置','title' => '栏目名称','dirname' => '静态目录',);
		foreach($fields as $k => $v) $v['datatype'] == 'text' && $arr[$k] = $v['cname'];
		trbasic('自动首字母来源字段','mconfigsnew[ca_autoletter]',makeoption($arr,@$mconfigs['ca_autoletter']),'select');
		tabfooter('bmconfigs');
	}else{
		$treestep || $treestep = array();
		$treestep[0] = empty($mconfigsnew['treesteps']) ? '' : max(10, $mconfigsnew['treesteps']);#第0个为栏目的
		$mconfigsnew['treesteps'] = implode(',', $treestep);
		saveconfig('view');
		adminlog('栏目参数设置');
		cls_message::show('栏目参数设置完成',axaction(6,"?entry=$entry&action=catalogedit"));
	}
}else cls_message::show('错误的文件参数');


function clearsameid($var){
	global $caid;
	return $var == $caid ? false : true;
}

