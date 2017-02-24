<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('catalog')) cls_message::show($re);
$cotypes = cls_cache::Read('cotypes');
if(!$coid || empty($cotypes[$coid])) cls_message::show('请指定正确的类别体系');
include_once M_ROOT."include/fields.fun.php";
foreach(array('channels','grouptypes','permissions','vcps','rprojects','catalogs',) as $k) $$k = cls_cache::Read($k);
$cotype = $cotypes[$coid];
$coclasses = cls_catalog::InitialInfoArray($coid);
$cotypename = $cotype['cname'];
$ccfields = cls_cache::Read('cnfields',$coid);
empty($action) && $action = 'coclassedit';
if(is_file($ex = dirname(__FILE__)."/exconfig/coclass_{$coid}_$action.php")){
	include($ex);
	entryfooter();
}
$c_upload = cls_upload::OneInstance();	
if($action == 'coclassadd'){
	echo "<title>添加分类 - $cotypename</title>";
	if(!submitcheck('bcoclassadd')) {
		$pid = empty($pid) ? 0 : max(0,intval($pid));
		if($pid) $pmsg = @$coclasses[$pid];
		tabheader("添加 [$cotypename] 分类",'coclassadd',"?entry=$entry&action=$action&coid=$coid",2,1,1);
		trbasic('分类名称','coclassnew[title]','','text',array('validate' => ' onfocus="initPinyin(\'coclassnew[dirname]\')"' . makesubmitstr('coclassnew[title]',1,0,0,30)));
		trbasic('分类标识','', '<input type="text" value="" name="coclassnew[dirname]" id="coclassnew[dirname]" size="25"' . makesubmitstr('coclassnew[dirname]',1,'numberletter',0,30) . ' offset="2">&nbsp;&nbsp;<input type="button" value="检查重名" onclick="check_repeat(\'coclassnew[dirname]\',\'dirname\');">&nbsp;&nbsp;<input type="button" value="自动拼音" onclick="autoPinyin(\'coclassnew[title]\',\'coclassnew[dirname]\')" />',
		'',array('guide' => '生成静态时，该标识将成为静态目录名，只允许字母数字和下划线'));
		trbasic('父分类','coclassnew[pid]',makeoption(array('0' => '顶级分类') + pidsarr($coid), $pid),'select');
		trbasic('结构分类(仅含子分类)','coclassnew[isframe]','','radio');
		if(empty($cotype['self_reg']) && empty($cotype['chidsforce'])){
			$dchids = empty($pmsg['chids']) ? (empty($cotype['chids']) ? '' : $cotype['chids']) : $pmsg['chids'];
			trbasic('在以下模型生效<br /><input class="checkbox" type="checkbox" name="chchkall" onclick="checkall(this.form,\'coclassnew[chids]\',\'chchkall\')">全选','',makecheckbox('coclassnew[chids][]',cls_channel::chidsarr(1),empty($dchids) ? array() : explode(',',$dchids),5),'');
		}
		if($cotype['groups']){ 
			$garr = select_arr($cotype['groups']); $vdef = explode(',',$cotype['groups']);
			trbasic('所属分组','',makecheckbox('coclassnew[groups][]',$garr,$vdef,5),'');
		}
		tabfooter();
		if(!empty($cotype['self_reg'])){
			tabheader("添加&nbsp;[$cotypename]&nbsp;分类-文档自动归类条件设置");
			trrange('添加日期',array('coclassnew[conditions][indays]','','','&nbsp; 天前&nbsp; &nbsp; -&nbsp; &nbsp; '),array('coclassnew[conditions][outdays]','','','&nbsp; '.'天内'));
			trrange('添加日期',array('coclassnew[conditions][createdatefrom]','','','&nbsp; 开始&nbsp; &nbsp; -&nbsp; &nbsp; '),array('coclassnew[conditions][createdateto]','','','&nbsp; '.'结束'),'calendar');
			trrange('点击数',array('coclassnew[conditions][clicksfrom]','','','&nbsp; 最小&nbsp; &nbsp; -&nbsp; &nbsp; '),array('coclassnew[conditions][clicksto]','','','&nbsp; '.'最大'));
			$createurl = "<br>>><a href=\"?entry=liststr&action=selfclass\" target=\"_blank\">生成字串</a>";
			trbasic('自定义条件查询字串'.$createurl,'coclassnew[conditions][sqlstr]','','textarea');
			tabfooter();
		}
		tabheader("其它信息");
		$a_field = new cls_field;
		foreach($ccfields as $k => $v){
			$a_field->init($v);
			$a_field->isadd = 1;
			$a_field->trfield('coclassnew');
		}
		trbasic('添加完的后续操作','',makeradio('needtip',array('提示我下一步做什么','继续添加','关闭窗口'),empty($m_cookie["np_add_$coid"]) ? 0 : $m_cookie["np_add_$coid"]),'');
		tabfooter('bcoclassadd','添加');
		a_guide('coclassadd');
	}else{
		if(!$coclassnew['title'] || !$coclassnew['dirname']) cls_message::show('分类资料不完全',M_REFERER);
		if(preg_match("/[^a-zA-Z_0-9]+/",$coclassnew['dirname'])) cls_message::show('分类标识不合规范',M_REFERER);
		$coclassnew['dirname'] = strtolower($coclassnew['dirname']);
		if(in_array($coclassnew['dirname'],cls_cache::Read('cn_dirnames'))) cls_message::show('分类标识重复',M_REFERER);
		$coclassnew['level'] = $coclassnew['pid'] ? ($coclasses[$coclassnew['pid']]['level'] + 1) : 0;
		$sqlstr0 = "title='$coclassnew[title]',
					dirname='$coclassnew[dirname]',
					isframe='$coclassnew[isframe]',
					level='$coclassnew[level]',
					pid='$coclassnew[pid]'";
		if(!empty($coclassnew['groups'])){
			$coclassnew['groups'] = empty($coclassnew['groups']) ? '' : implode(',',$coclassnew['groups']);
			$sqlstr0 .= ",groups='$coclassnew[groups]'";
		}
		if(empty($cotype['self_reg'])){
			$coclassnew['chids'] = empty($cotype['chidsforce']) ? (empty($coclassnew['chids']) ? '' : implode(',',$coclassnew['chids'])) : $cotype['chids'];
			$sqlstr0 .= ",chids='$coclassnew[chids]'";
		}else{
			foreach(array('clicksfrom','indays','clicksto','outdays',) as $v){
				if($coclassnew['conditions'][$v] == ''){
					unset($coclassnew['conditions'][$v]);
				}else $coclassnew['conditions'][$v] = max(0,intval($coclassnew['conditions'][$v]));
			}
			foreach(array('createdatefrom','createdateto',) as $v){
				if($coclassnew['conditions'][$v] == '' || !cls_string::isDate($coclassnew['conditions'][$v])){
					unset($coclassnew['conditions'][$v]);
				}else $coclassnew['conditions'][$v] = strtotime($coclassnew['conditions'][$v]);
			}
			$coclassnew['conditions']['sqlstr'] = trim($coclassnew['conditions']['sqlstr']);
			if($coclassnew['conditions']['sqlstr'] == '') unset($coclassnew['conditions']['sqlstr']);
			if(empty($coclassnew['conditions'])) cls_message::show('请设置自动归类条件',M_REFERER);
			$coclassnew['conditions'] = addslashes(serialize($coclassnew['conditions']));
			$sqlstr0 .= ",conditions='$coclassnew[conditions]'";
		}
		$a_field = new cls_field;
		$sqlstr = "";
		foreach($ccfields as $k => $v){
			$a_field->init($v);
			$a_field->deal('coclassnew','cls_message::show',M_REFERER);
			$sqlstr .= ','.$k."='".$a_field->newvalue."'";
			if($arr = multi_val_arr($a_field->newvalue,$v)) foreach($arr as $x => $y) $sqlstr .= ','.$k.'_'.$x."='$y'";
		}
		$c_upload->saveuptotal(1);
		!empty($cotype['autoletter']) && $sqlstr .= ",letter='".autoletter(@$coclassnew[$cotype['autoletter']])."'";
		$db->query("INSERT INTO {$tblprefix}coclass$coid SET ccid=".auto_insert_id('coclass').",$sqlstr0,coid='$coid' $sqlstr");
		if($ccid = $db->insert_id()){
			$c_upload->closure(1, $ccid, 'coclass');
		}
		unset($a_field);
		
		adminlog('添加类系分类');
		cls_catalog::DbTrueOrder($coid);
		cls_CacheFile::Update('coclasses',$coid);
		
		$needtip = min(2,max(0,intval($needtip)));
		$needtip ? msetcookie("np_add_$coid",$needtip,31536000) : mclearcookie("np_add_$coid");
		$na = array(array('查看后续事务',36,"follow"),array('继续添加下一个',36,$action),array('即将关闭窗口',6,'coclassedit'),);
		cls_message::show('分类添加成功，'.$na[$needtip][0], axaction($na[$needtip][1],"?entry=$entry&coid=$coid&action=".$na[$needtip][2]));
	}
}elseif($action == 'follow'){
	echo "<title>后续操作</title>";
	$cnrels = cls_cache::Read('cnrels');
	$viewsarr = array();
	foreach($cnrels as $k => $v){
		if(in_array($coid,array($v['coid'],$v['coid1']))){
			$viewsarr[] = array("$k.类目关联","<a href=\"?entry=cnrels&action=cnreldetail&rid=$k&isframe=1\" target=\"_blank\">>>点我</a>：设置 [$v[cname]] 中的关联");
		}
	}
	if($cotype['sortable']){
		$str = "<a href=\"?entry=cnodes&action=cnconfigs&ncoid=$coid&arcdeal=newupdate&isframe=1\" target=\"_blank\">>>方式1</a>：选择相关的节点组成方案，补全方案中的节点。此方式可手动选择";
		$str .= "<br><a href=\"?entry=cnodes&action=patchupdate&coid=$coid\" onclick=\"return floatwin('open_fnodes',this)\">>>方式2</a>：自动补全所有与 [$cotypename] 相关的节点组成方案，此方式一键完成。";
		$viewsarr[] = array('添加类目节点',$str);
		$viewsarr[] = array('添加会员节点',"<a href=\"?entry=mcnodes&action=mcnodeadd&isframe=1\" target=\"_blank\">>>点我</a>：添加需要的会员节点");
		$str = "<a href=\"?entry=o_cnodes&action=cnconfigs&ncoid=$coid&arcdeal=newupdate&isframe=1\" target=\"_blank\">>>方式1</a>：选择相关的节点组成方案，补全方案中的节点。此方式可手动选择";
		$str .= "<br><a href=\"?entry=o_cnodes&action=patchupdate&coid=$coid\" onclick=\"return floatwin('open_fnodes',this)\">>>方式2</a>：自动补全所有与 [$cotypename] 相关的节点组成方案，此方式一键完成。";
		$viewsarr[] = array('添加手机版节点',$str);
	}
	tabheader('添加分类之后的后续操作');
	if(empty($viewsarr)){
		trbasic('提示说明','','1、非节点类系不需要相关节点操作<br>2、没有相关的类目关联项目需要设置','');
	}else{
		foreach($viewsarr as $k => $v) trbasic($v[0],'',$v[1],'');
	}
	tabfooter();
}elseif($action == 'coclassadds' && empty($cotype['self_reg'])){
	echo "<title>批量添加 - $cotypename</title>";
	$pid = 0;
	$chids = cls_channel::chidsarr(1);
	$groups = select_arr($cotype['groups']); //$vdef = explode(',',$coclass['groups']);
	$_settings = array(
		'pid' => array(
			'type' => 'select',
			'title' => '父分类',
			'value' => makeoption(array('0' => '顶级分类') + pidsarr($coid))
		),
		'isframe' => array(
			'type' => 'radio',
			'title' => '结构分类(仅含子分类)',
			'value' => ''
		),
	);
	if(empty($cotype['chidsforce'])){
		$_settings['chids'] = array(
			'type' => '',
			'title' => '在以下模型生效',
			'value' => makecheckbox('coclasssitems[chids][]',$chids,!empty($cotype['chids']) ? explode(',',$cotype['chids']) : array(),5)
		);
	}
	//在chids后面
	if($cotype['groups']){ 
		$garr = select_arr($cotype['groups']); $vdef = explode(',',$cotype['groups']);
		$_settings['groups'] = array(
			'type' => '',
			'title' => '所属分组',
			'value' => makecheckbox('coclasssitems[groups][]',$garr,!empty($cotype['groups']) ? explode(',',$cotype['groups']) : array(),5)
		);	
	}	
	foreach($ccfields as $k => $v){
		$_settings[$k] = array(
			'type' => 'field',
			'title' => $v['cname']
		);
	}
	if(!submitcheck('bcoclassset') && !submitcheck('bcoclassadd')) {
		tabheader("批量添加分类 - $cotypename",'coclasseadd',"?entry=$entry&action=$action&coid=$coid",2,0,1);
		trbasic('添加分类数量','batch_count','','text',array('validate'=>' rule="int" must="1" min="1" max="200"'));
		trbasic('需要分别设置的项','','','');

		trbasic('<input class="checkbox" type="checkbox" checked="checked" disabled="disabled" />', '', '分类标识'.
			' <input class="checkbox" type="checkbox" name="auto_pinyin" id="auto_pinyin" value="1" /><label for="auto_pinyin">自动拼音</label>',
			'',array('guide' => '生成静态时，该标识将成为静态目录名，只允许字母数字和下划线'));
		foreach($_settings as $k => $v)trbasic('<input class="checkbox" type="checkbox" name="diffitems[]" value="'.$k.'" />', '', $v['title'], '');
		tabfooter('bcoclassset');
	}elseif(!submitcheck('bcoclassadd')){
		$batch_count = max(0, intval($batch_count));
		empty($batch_count) && cls_message::show('请填写批量添加的栏目数量', M_REFERER);
		$_diffitems = array(
			'title' => array(
				'type' => 'text',
				'title' => '分类名称',
				'value' => ''
			),
		);
		empty($auto_pinyin) && $_diffitems['dirname'] = array(
			'type' => 'text',
			'title' => '分类标识',
			'value' => ''
		);

		$a_field = new cls_field;
		empty($diffitems) && $diffitems = array();
		tabheader('批量添加分类 - 相同设置','coclassadd',"?entry=$entry&action=$action&coid=$coid",2,1,1);
		foreach($_settings as $k => $v){
			if(in_array($k, $diffitems)){
				$_diffitems[$k] = $v;
			}elseif($v['type'] == 'field'){
				$a_field->init($ccfields[$k]);
				$a_field->isadd = 1;
				$a_field->trfield('coclasssome');
			}else{
				trbasic($v['title'], "coclasssome[$k]", $v['value'], $v['type'],array('guide'=>array_key_exists('tip', $v) ? $v['tip'] : ''));
			}
		}
		trbasic('添加完的后续操作','',makeradio('needtip',array('提示我下一步做什么','继续添加','关闭窗口'),empty($m_cookie["np_adds_$coid"]) ? 0 : $m_cookie["np_adds_$coid"]),'');
		tabfooter();
		
		echo '<br /><br />'.(empty($auto_pinyin) ? '' : '<input type="hidden" name="auto_pinyin" value="1" />');
		for($i = 0; $i < $batch_count; $i++){
			tabheader('批量添加 - 分类'.($i+1));
			foreach($_diffitems as $k => $v){
				if($v['type'] == 'field'){
					$a_field->init($ccfields[$k]);
					$a_field->isadd = 1;
					$a_field->trfield("coclassitems[$i]");
				}else{
					if($k=='chids'){
						$str = makecheckbox("coclassitems[$i][chids][]",$chids,!empty($pmsg['chids']) ? explode(',',$pmsg['chids']) : array(),5);
					}elseif($k=='groups'){
						$str = makecheckbox("coclassitems[$i][groups][]",$groups, array() ,5); //echo "@@@@@@@@@@@";
					}else{
						$str = $v['value'];
					}
					trbasic($v['title'], "coclassitems[$i][$k]", "$str", $v['type'],array('guide'=>array_key_exists('tip', $v) ? $v['tip'] : ''));
				}
			}
			tabfooter();
		}
		echo '<br /><input class="btn" type="submit" name="bcoclassadd" value="提交">';
		a_guide('coclassadd');
	}else{
		$enamearr = cls_cache::Read('cn_dirnames');

		$ok = 0;
		$a_field = new cls_field;
		foreach($coclassitems as $item){
			$coclassnew = $coclasssome;
			foreach($item as $k => $v){
				if(is_array($v)){
					foreach($v as $a => $b)$coclassnew[$k][$a] = $b;
				}else{
					$coclassnew[$k] = $v;
				}
			}

			empty($auto_pinyin) || $coclassnew['dirname'] = cls_string::Pinyin($coclassnew['title']);
			if(!$coclassnew['title'] || !$coclassnew['dirname'])continue;
			if(preg_match("/[^a-zA-Z_0-9]+/",$coclassnew['dirname']))continue;
			$coclassnew['dirname'] = strtolower($coclassnew['dirname']);
			if(empty($auto_pinyin)){
				if(in_array($coclassnew['dirname'], $enamearr))continue;
			}else{
				$i = 1;
				$dirname = $coclassnew['dirname'];
				while(in_array($coclassnew['dirname'], $enamearr))$coclassnew['dirname'] = $dirname.($i++);
			}

			$coclassnew['level'] = $coclassnew['pid'] ? ($coclasses[$coclassnew['pid']]['level'] + 1) : 0;
			$sqlstr0 = "title='$coclassnew[title]',
						dirname='$coclassnew[dirname]',
						isframe='$coclassnew[isframe]',
						level='$coclassnew[level]',
						pid='$coclassnew[pid]'";
					
			if(!empty($coclassnew['groups'])){
				$coclassnew['groups'] = empty($coclassnew['groups']) ? '' : implode(',',$coclassnew['groups']);
				$sqlstr0 .= ",groups='$coclassnew[groups]'";
			}
						
			$coclassnew['chids'] = empty($cotype['chidsforce']) ? (empty($coclassnew['chids']) ? '' : implode(',',$coclassnew['chids'])) : $cotype['chids'];
			$sqlstr0 .= ",chids='$coclassnew[chids]'";
						
			$a_field = new cls_field;
			$sqlstr = "";
			foreach($ccfields as $k => $v){
				$a_field->init($v);
				$a_field->deal('coclassnew','cls_message::show',axaction(2,M_REFERER));
				$sqlstr .= ','.$k."='".$a_field->newvalue."'";
				if($arr = multi_val_arr($a_field->newvalue,$v)) foreach($arr as $x => $y) $sqlstr .= ','.$k.'_'.$x."='$y'";
			}
			$c_upload->saveuptotal(1);
			!empty($cotype['autoletter']) && $sqlstr .= ",letter='".autoletter(@$coclassnew[$cotype['autoletter']])."'";
			$db->query("INSERT INTO {$tblprefix}coclass$coid SET 
				ccid=".auto_insert_id('coclass').",
				$sqlstr0,
				coid='$coid' 
				$sqlstr
				");
			if($ccid = $db->insert_id()){
				$c_upload->closure(1,$ccid,'coclass');
				$enamearr[] = $coclassnew['dirname'];
				$ok++;
			}
		}
		unset($a_field);
		adminlog('添加文档分类');
		cls_catalog::DbTrueOrder($coid);
		cls_CacheFile::Update('coclasses',$coid);
		
		$needtip = min(2,max(0,intval($needtip)));
		$needtip ? msetcookie("np_adds_$coid",$needtip,31536000) : mclearcookie("np_adds_$coid");
		$na = array(array('查看后续事务',36,"follow"),array('继续添加下一批',36,$action),array('即将关闭窗口',6,'coclassedit'),);
		cls_message::show(($ok ? "成功添加 $ok 个栏目," : '批量添加失败').$na[$needtip][0], axaction($na[$needtip][1],"?entry=$entry&coid=$coid&action=".$na[$needtip][2]));
	}
}elseif($action == 'coclassedit') {
	echo "<title>分类管理 - $cotypename</title>";
	if(!submitcheck('bcoclassedit')) {
		echo form_str('coclassedit',"?entry=$entry&action=$action&coid=$coid");
		$addfieldstr = "&nbsp; &nbsp; >><a href=\"?entry=$entry&action=coclassadd&coid=$coid\" title=\"单个添加分类\" onclick=\"return floatwin('open_coclassedit',this)\">添加</a>";
		empty($cotype['self_reg']) && $addfieldstr .= " [<a href=\"?entry=$entry&action=coclassadds&coid=$coid\" title=\"批量添加分类\" onclick=\"return floatwin('open_coclassedit',this)\">批量</a>]";
		$addfieldstr .= " [<a href=\"?entry=$entry&action=follow&coid=$coid\" title=\"添加分类完之后的后续设置\" onclick=\"return floatwin('open_coclassedit',this)\">后续</a>]";
		echo "<div class=\"conlist1\">[$cotypename]&nbsp;分类管理$addfieldstr</div>";
		echo '<script type="text/javascript">var cocs = [';
		$pidsarr = pidsarr($coid);
		foreach($coclasses as $k => $v){
			$s = isset($pidsarr[$k]) ? "<a href=\"?entry=$entry&action=coclassadd&coid=$coid&pid=$k\" onclick=\"return floatwin('open_coclassedit',this)\">添加</a>" : '-';
			echo "[$v[level],$k,'" . str_replace("'","\\'",mhtmlspecialchars($v['title'])) . "',$v[vieworder],'".str_replace("'","\\'",$s)."'],";
		}
		empty($cotype['treestep']) && $cotype['treestep'] = '';
		echo <<<DOT
];
document.write(tableTree({data:cocs,ckey:'ckey_{$coid}_',step:'$cotype[treestep]',html:{
		head: '<td class="txtC" width="30"><input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form,\'selectid\',\'chkall\')">全</td>'
			+ '<td class="txtC" width="40">ID</td>'
			+ '<td class="txtL" width="350"%code%>分类名称 %input%</td>'
			+ '<td class="txtC" width="40">排序</td>'
			+ '<td class="txtC" width="40">添加</td>'
			+ '<td class="txtC" width="40">详情</td>'
			+ '<td class="txtC" width="40">删除</td>',
		cell:[2,4],
		rows: '<td class="txtC" width="30"><input class="checkbox" name="selectid[%1%]" value="'
					+ '%1%" type="checkbox" onclick="tableTree.setChildBox()" /></td>'
			+ '<td class="txtC" width="40">%1%</td>'
			+ '<td class="txtL" width="350">%ico%<input name="coclassesnew['
					+ '%1%][title]" value="%2%" size="25" maxlength="30" type="text" /></td>'
			+  '<td class="txtC" width="40"><input name="coclassesnew['
					+ '%1%][vieworder]" value="%3%" type="text" style="width:36px" /></td>'
			+ '<td class="txtC" width="40">%4%</td>'
			+ '<td class="txtC" width="40"><a href="?entry=$entry&action=coclassdetail&coid=$coid&ccid='
					+ '%1%" onclick="return floatwin(\'open_coclassedit\',this)">详情</a></td>'
			+ '<td class="txtC" width="40"><a onclick="return deltip()" href="?entry=$entry&action=coclassdelete&coid=$coid&ccid=%1%">删除</a></td>'
		},
	callback : true
}));
DOT;
		echo '</script>';

		tabheader('操作项目'.viewcheck(array('name' => 'viewdetail', 'title' => '显示详细', 'value' => 0, 'body' => $actionid.'tbodyfilter')));
		echo "<tbody id=\"{$actionid}tbodyfilter\" style=\"display:none\">";
		$s_arr = array();
		$cotype['autoletter'] && $s_arr['letter'] = '更新首字母';
		$s_arr['noletter'] = '清空首字母';
		$s_arr['delete'] = '批量删除';
		if($s_arr){
			$soperatestr = '';
			$i = 1;
			foreach($s_arr as $k => $v){
				$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" ".($k=='delete' ? "onclick=\"deltip()\"" : '')."  value=\"1\">$v &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			$soperatestr && trbasic('选择操作项目','',$soperatestr,'');
		}
		if($paidsarr = cls_pusher::paidsarr('catalogs',$coid)){ # 推送位
			$soperatestr = '';
			$i = 1;
			foreach($paidsarr as $k => $v){
				$soperatestr .= OneCheckBox("arcdeal[$k]",cls_pusher::AllTitle($k,1,1),0,1)." &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			$soperatestr && trbasic('选择推送位','',$soperatestr,'');
		
		}
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[pid]\" value=\"1\">&nbsp;重设父分类",'arcpid',makeoption(array('0' => '顶级分类') + pidsarr($coid)),'select');


		if(!$cotype['self_reg'] && empty($cotype['chidsforce'])){
			$cnmodearr = array(0 => '修改配置中设置',1 => '在原配置中添加',2 => '从原配置中移除',);
			trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[chids]\" value=\"1\">&nbsp;在以下模型生效<br><input class=\"checkbox\" type=\"checkbox\" name=\"chkallc\" onclick=\"checkall(this.form,'arcchids','chkallc')\">全选",'',"<select id=\"cnmode\" name=\"cnmode\" style=\"vertical-align: middle;\">".makeoption($cnmodearr)."</select><br>".makecheckbox('arcchids[]',cls_channel::chidsarr(0),array(),5),'');
		
		}
		
		if($cotype['groups']){ //echo "$cotype[groups]";
			$gtitle = "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[groups]\" value=\"1\">";
			$gtitle .= "&nbsp;所属分组";
			//$gtitle .= "<br><input class=\"checkbox\" type=\"checkbox\" name=\"chkallc\" onclick=\"checkall(this.form,'arcgroups','chkallc')\">全选";
			$gmodearr = array(0 => '修改配置中设置',1 => '在原配置中添加',2 => '从原配置中移除',);
			$gmodestr = "<select id=\"gmode\" name=\"gmode\" style=\"vertical-align: middle;\">".makeoption($gmodearr)."</select><br>";
			$garr = select_arr($cotype['groups']); $vdef = explode(',',$cotype['groups']);
			trbasic($gtitle,'',$gmodestr.makecheckbox('arcgroups[]',$garr,array(),5),'');
		}
		
		echo "</tbody>";
		tabfooter('bcoclassedit');
		a_guide('coclassedit');
	}else{
		if(isset($coclassesnew)){
			foreach($coclassesnew as $ccid => $coclassnew){
				$coclassnew['title'] = trim(strip_tags($coclassnew['title']));
				$coclassnew['title'] = $coclassnew['title'] ? $coclassnew['title'] : $coclasses[$ccid]['title'];
				$sqlstr = $coclassnew['vieworder'] != $coclasses[$ccid]['vieworder'] ? ",vieworder='" . max(0,intval($coclassnew['vieworder'])) . "'" : '';
				if(($coclassnew['title'] != $coclasses[$ccid]['title']) || $sqlstr){
					$db->query("UPDATE {$tblprefix}coclass$coid SET 
								title='$coclassnew[title]'
								$sqlstr
								WHERE ccid='$ccid'
								");
				}
			}
		}
		if(!empty($selectid) && !empty($arcdeal)){
			if(!empty($arcdeal['groups']) && $cotype['groups']){
				foreach($selectid as $ccid){
					$groupsnew = empty($arcgroups) ? array() : $arcgroups;
					if(!empty($gmode)){
						$coclass = cls_cache::Read('coclass',$coid,$ccid);
						$groups = empty($coclass['groups']) ? array() : explode(',',$coclass['groups']);
						$groupsnew = $gmode == 1 ? array_unique(array_merge($groups,$groupsnew)) : array_diff($groups,$groupsnew);
					}
					$groupsnew = !empty($groupsnew) ? implode(',',$groupsnew) : '';
					$db->query("UPDATE {$tblprefix}coclass$coid SET groups='$groupsnew' WHERE ccid='$ccid'");
				}
			}
			
			# 推送位
			if($paidsarr = cls_pusher::paidsarr('catalogs',$coid)){
				foreach($paidsarr as $k => $v){
					if(!empty($arcdeal[$k])){
						foreach($selectid as $ccid){
							cls_catalog::push($coid,$ccid,$k);
						}
					}
				}
			}
			
			if(!empty($arcdeal['chids'])){
				foreach($selectid as $ccid){
					$chidsnew = empty($arcchids) ? array() : $arcchids;
					if(!empty($cnmode)){
						$coclass = cls_cache::Read('coclass',$coid,$ccid);
						$chids = empty($coclass['chids']) ? array() : explode(',',$coclass['chids']);
						$chidsnew = $cnmode == 1 ? array_unique(array_merge($chids,$chidsnew)) : array_diff($chids,$chidsnew);
					}
					$chidsnew = !empty($chidsnew) ? implode(',',$chidsnew) : '';
					$db->query("UPDATE {$tblprefix}coclass$coid SET chids='$chidsnew' WHERE ccid='$ccid'");
				}
			}
			
			if(!empty($arcdeal['letter'])){				
					foreach($selectid as $ccid){						
							$letter = !empty($cotype['autoletter'])?autoletter(@$coclasses[$ccid][$cotype['autoletter']]):'';							
							$db->query("UPDATE {$tblprefix}coclass$coid SET letter='$letter' WHERE ccid='$ccid'");						
					}
			}
			//清空首字母
			if(!empty($arcdeal['noletter'])){				
					foreach($selectid as $ccid){
							$db->query("UPDATE {$tblprefix}coclass$coid SET letter='' WHERE ccid='$ccid'");						
					}
			}
			if(!empty($arcdeal['pid'])){
				foreach($selectid as $ccid){
					$sonids = cls_catalog::cnsonids($ccid,$coclasses);
					if(in_array($arcpid,$sonids)) continue;//不能父分类设为当前id及其下级分类
					$newlevel = !$arcpid ? 0 : $coclasses[$arcpid]['level'] + 1;
					$db->query("UPDATE {$tblprefix}coclass$coid SET pid='$arcpid',level='$newlevel' WHERE ccid='$ccid'");
					$leveldiff = $newlevel - $coclasses[$ccid]['level'];
					foreach($sonids as $sonid) if($sonid != $ccid) $db->query("UPDATE {$tblprefix}coclass$coid SET level=level+".$leveldiff." WHERE ccid='$sonid'");
				}
			}
			if(!empty($arcdeal['delete'])){
				//架构保护
				deep_allow($no_deepmode && in_array($coid,@explode(',',$deep_coids)),"?entry=$entry&action=coclassedit&coid=$coid");
				foreach($selectid as $ccid){
					if(!($coclass = cls_catalog::InitialOneInfo($coid,$ccid))) cls_message::show('请指定正确的分类。');
					if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}coclass$coid WHERE pid='$ccid'")) {
						cls_message::show('分类没有相关联的子分类才能删除', "?entry=$entry&action=coclassedit&coid=$coid");
					}
					$na = stidsarr(1);
					foreach($na as $k => $v){
						//将该分类的信息从主文档中删除
						$db->query("UPDATE {$tblprefix}".atbl($k,1)." SET ccid$coid=0 WHERE ccid$coid='$ccid'",'SILENT');
					}
					
					//删除相关的节点
					$db->query("DELETE FROM {$tblprefix}cnodes WHERE ename REGEXP 'ccid$coid=$ccid(&|$)'");
					cls_CacheFile::Update('cnodes');
					$db->query("DELETE FROM {$tblprefix}o_cnodes WHERE ename REGEXP 'ccid$coid=$ccid(&|$)'");
					cls_CacheFile::Update('o_cnodes');
					$db->query("DELETE FROM {$tblprefix}mcnodes WHERE mcnvar='ccid$coid' AND mcnid='$ccid'");
					cls_CacheFile::Update('mcnodes');
					
					$db->query("DELETE FROM {$tblprefix}coclass$coid WHERE ccid='$ccid'");
					adminlog('删除文档分类');
					cls_CacheFile::Update('coclasses',$coid);
					//更正类目关联
					$cnrels = cls_cache::Read('cnrels');
					foreach($cnrels as $k => $v){
						$alter = false;
						if(($v['coid'] == $coid) && isset($v['cfgs'][$ccid])){
							unset($v['cfgs'][$ccid]);
							$alter = true;
						}
						if($v['coid1'] == $coid){
							foreach($v['cfgs'] as $x => $y){
								$a = empty($y) ? array() : array_filter(explode(',',$y));
								if(in_array($ccid,$a)){
									$a = array_filter($a,"clearsameid");
									$v['cfgs'][$x] = implode(',',$a);
									$alter = true;
								}
							}
						}
						$alter && $db->query("UPDATE {$tblprefix}cnrels SET cfgs='".(empty($v['cfgs']) ? '' : addslashes(var_export($v['cfgs'],TRUE)))."' WHERE rid='$k'",'SILENT');
					}
					cls_CacheFile::Update('cnrels');					
				}				
			}
		}
		adminlog('编辑文档分类管理列表');
		cls_catalog::DbTrueOrder($coid);
		cls_CacheFile::Update('coclasses',$coid);
		cls_message::show('分类编辑完成', "?entry=$entry&action=coclassedit&coid=$coid");
	}
}elseif($action =='coclassdetail' && $ccid) {
	echo "<title>分类详情 - $cotypename</title>";
	if(!($coclass = cls_catalog::InitialOneInfo($coid,$ccid))) cls_message::show('请指定正确的分类。');
	if(!submitcheck('bcoclassdetail')) {
		tabheader("[$coclass[title]] 基本设置",'coclassdetail',"?entry=$entry&action=coclassdetail&coid=$coid&ccid=$ccid",2,1,1);
		trbasic('分类标识','', '<input type="text" value="'.$coclass['dirname'].'" name="coclassnew[dirname]" id="coclassnew[dirname]" size="25"' . makesubmitstr('coclassnew[dirname]',1,'numberletter',0,30) . ' offset="2">&nbsp;&nbsp;<input type="button" value="检查重名" onclick="check_repeat(\'coclassnew[dirname]\',\'dirname\');">',
		'',array('guide' => '生成静态时，该标识将成为静态目录名，只允许字母数字和下划线'));
		trbasic('父分类','coclassnew[pid]',makeoption(array('0' => '顶级分类') + pidsarr($coid),$coclass['pid']),'select');
		trbasic('结构分类(仅含子分类)','coclassnew[isframe]',$coclass['isframe'],'radio');
		$coclass['conditions'] = @unserialize($coclass['conditions']);
		if(empty($cotype['self_reg']) && empty($cotype['chidsforce'])){
			trbasic('在以下模型生效<br /><input class="checkbox" type="checkbox" name="chchkall" onclick="checkall(this.form,\'coclassnew[chids]\',\'chchkall\')">全选','',makecheckbox('coclassnew[chids][]',cls_channel::chidsarr(1),!empty($coclass['chids']) ? explode(',',$coclass['chids']) : array(),5),'');
		}
		if($cotype['groups']){ 
			$garr = select_arr($cotype['groups']); $vdef = explode(',',$coclass['groups']);
			trbasic('所属分组','',makecheckbox('coclassnew[groups][]',$garr,$vdef,5),'');
		}
		tabfooter();
		if(!empty($cotype['self_reg'])){
			tabheader("分类&nbsp;[$coclass[title]]&nbsp;文档自动归类条件设置");
			trrange('添加日期',array('coclassnew[conditions][indays]',isset($coclass['conditions']['indays']) ? $coclass['conditions']['indays'] : '','','&nbsp; '.'天前'.'&nbsp; &nbsp; -&nbsp; &nbsp; '),array('coclassnew[conditions][outdays]',isset($coclass['conditions']['outdays']) ? $coclass['conditions']['outdays'] : '','','&nbsp; '.'天内'));
			trrange('添加日期',array('coclassnew[conditions][createdatefrom]',isset($coclass['conditions']['createdatefrom']) ? date('Y-m-d',$coclass['conditions']['createdatefrom']) : '','','&nbsp; '.'开始'.'&nbsp; &nbsp; -&nbsp; &nbsp; '),array('coclassnew[conditions][createdateto]',isset($coclass['conditions']['createdateto']) ? date('Y-m-d',$coclass['conditions']['createdateto']) : '','','&nbsp; '.'结束'),'calendar');
			trrange('点击数',array('coclassnew[conditions][clicksfrom]',isset($coclass['conditions']['clicksfrom']) ? $coclass['conditions']['clicksfrom'] : '','','&nbsp; '.'最小'.'&nbsp; &nbsp; -&nbsp; &nbsp; '),array('coclassnew[conditions][clicksto]',isset($coclass['conditions']['clicksto']) ? $coclass['conditions']['clicksto'] : '','','&nbsp; '.'最大'));
			$createurl = "<br>>><a href=\"?entry=liststr&action=selfclass\" target=\"_blank\">生成字串</a>";
			trbasic('自定义条件查询字串'.$createurl,'coclassnew[conditions][sqlstr]',isset($coclass['conditions']['sqlstr']) ? stripslashes($coclass['conditions']['sqlstr']) : '','textarea');
			tabfooter();
		}
		$a_field = new cls_field;
		tabheader("[$coclass[title]] 其它设置");
		foreach($ccfields as $field){
			$a_field->init($field,!isset($coclass[$field['ename']]) ? '' : $coclass[$field['ename']]);
			$a_field->trfield('coclassnew');
		}
		tabfooter('bcoclassdetail');
		a_guide('coclassdetail');
	}else{
		$coclassnew['dirname'] = strtolower($coclassnew['dirname']);
		if($coclassnew['dirname'] != $coclass['dirname']){
			preg_match("/[^a-zA-Z_0-9]+/",$coclassnew['dirname']) && cls_message::show('分类标识不合规范',M_REFERER);
			in_array($coclassnew['dirname'], cls_cache::Read('cn_dirnames')) && cls_message::show('分类标识重复',M_REFERER);
		}
		$sonids = cls_catalog::cnsonids($ccid,$coclasses);
		(in_array($coclassnew['pid'],$sonids)) && cls_message::show('类目不能转到原类目及其子类目下',M_REFERER);
		$coclassnew['level'] = !$coclassnew['pid'] ? 0 : $coclasses[$coclassnew['pid']]['level'] + 1;
		$coclassnew['groups'] = empty($coclassnew['groups']) ? '' : implode(',',$coclassnew['groups']);
		$sqlstr0 = "isframe='$coclassnew[isframe]',
					dirname='$coclassnew[dirname]',
					level='$coclassnew[level]',
					groups='$coclassnew[groups]',
					pid='$coclassnew[pid]'";
		if(empty($cotype['self_reg'])){
			$coclassnew['chids'] = empty($cotype['chidsforce']) ? (empty($coclassnew['chids']) ? '' : implode(',',$coclassnew['chids'])) : $cotype['chids'];
			$sqlstr0 .= ",chids='$coclassnew[chids]'";
		}else{
			foreach(array('clicksfrom','indays','clicksto','outdays',) as $v){
				if($coclassnew['conditions'][$v] == ''){
					unset($coclassnew['conditions'][$v]);
				}else $coclassnew['conditions'][$v] = max(0,intval($coclassnew['conditions'][$v]));
			}
			foreach(array('createdatefrom','createdateto',) as $v){
				if($coclassnew['conditions'][$v] == '' || !cls_string::isDate($coclassnew['conditions'][$v])){
					unset($coclassnew['conditions'][$v]);
				}else $coclassnew['conditions'][$v] = strtotime($coclassnew['conditions'][$v]);
			}
			$coclassnew['conditions']['sqlstr'] = trim($coclassnew['conditions']['sqlstr']);
			if($coclassnew['conditions']['sqlstr'] == '') unset($coclassnew['conditions']['sqlstr']);
			if(empty($coclassnew['conditions'])) cls_message::show('请设置自动归类条件',M_REFERER);
			$coclassnew['conditions'] = addslashes(serialize($coclassnew['conditions']));
			$sqlstr0 .= ",conditions='$coclassnew[conditions]'";
		}
		
		$a_field = new cls_field;
		$sqlstr = "";
		foreach($ccfields as $k => $v){
			$a_field->init($v,!isset($coclass[$k]) ? '' : $coclass[$k]);
			$a_field->deal('coclassnew','cls_message::show',"?entry=$entry&action=coclassdetail&coid=$coid&ccid=$ccid");
			$sqlstr .= ','.$k."='".$a_field->newvalue."'";
			if($arr = multi_val_arr($a_field->newvalue,$v)) foreach($arr as $x => $y) $sqlstr .= ','.$k.'_'.$x."='$y'";
		}
		$c_upload->closure(1, $ccid, 'coclass');
		$c_upload->saveuptotal(1);
		unset($a_field);

		$leveldiff = $coclassnew['level'] - $coclass['level'];
		foreach($sonids as $sonid){
			 if($sonid != $ccid) $db->query("UPDATE {$tblprefix}coclass$coid SET level=level+".$leveldiff." WHERE ccid='$sonid'");
		}
		!empty($cotype['autoletter']) && $sqlstr .= ",letter='".autoletter(empty($coclassnew[$cotype['autoletter']]) ? @$coclass[$cotype['autoletter']] : $coclassnew[$cotype['autoletter']])."'";
		$db->query("UPDATE {$tblprefix}coclass$coid SET $sqlstr0 $sqlstr WHERE ccid='$ccid'");
		adminlog('详细修改文档分类');
		cls_catalog::DbTrueOrder($coid);
		cls_CacheFile::Update('coclasses',$coid);
		cls_message::show('分类设置完成',axaction(6,M_REFERER));
	}
}elseif($action == 'coclassdelete' && $ccid) {
	deep_allow($no_deepmode && in_array($coid,@explode(',',$deep_coids)),"?entry=$entry&action=coclassedit&coid=$coid");
	if(!($coclass = cls_catalog::InitialOneInfo($coid,$ccid))) cls_message::show('请指定正确的分类。');
	if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}coclass$coid WHERE pid='$ccid'")) {
		cls_message::show('分类没有相关联的子分类才能删除', "?entry=$entry&action=coclassedit&coid=$coid");
	}
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=\"?entry=$entry&action=coclassdelete&coid=$coid&ccid=$ccid&confirm=ok\">删除</a><br>";
		$message .= "放弃请点击>><a href=\"?entry=$entry&action=coclassedit&coid=$coid\">返回</a>";
		cls_message::show($message);
	}
	$na = stidsarr(1);
	foreach($na as $k => $v){
		$db->query("UPDATE {$tblprefix}".atbl($k,1)." SET ccid$coid=0 WHERE ccid$coid='$ccid'",'SILENT');//将该分类的信息从主文档中删除
	}
	
	//删除相关的节点
	$db->query("DELETE FROM {$tblprefix}cnodes WHERE ename REGEXP 'ccid$coid=$ccid(&|$)'");
	cls_CacheFile::Update('cnodes');
	$db->query("DELETE FROM {$tblprefix}o_cnodes WHERE ename REGEXP 'ccid$coid=$ccid(&|$)'");
	cls_CacheFile::Update('o_cnodes');
	$db->query("DELETE FROM {$tblprefix}mcnodes WHERE mcnvar='ccid$coid' AND mcnid='$ccid'");
	cls_CacheFile::Update('mcnodes');
	
	$db->query("DELETE FROM {$tblprefix}coclass$coid WHERE ccid='$ccid'");
	adminlog('删除文档分类');
	cls_CacheFile::Update('coclasses',$coid);
	//更正类目关联
	$cnrels = cls_cache::Read('cnrels');
	foreach($cnrels as $k => $v){
		$alter = false;
		if(($v['coid'] == $coid) && isset($v['cfgs'][$ccid])){
			unset($v['cfgs'][$ccid]);
			$alter = true;
		}
		if($v['coid1'] == $coid){
			foreach($v['cfgs'] as $x => $y){
				$a = empty($y) ? array() : array_filter(explode(',',$y));
				if(in_array($ccid,$a)){
					$a = array_filter($a,"clearsameid");
					$v['cfgs'][$x] = implode(',',$a);
					$alter = true;
				}
			}
		}
		$alter && $db->query("UPDATE {$tblprefix}cnrels SET cfgs='".(empty($v['cfgs']) ? '' : addslashes(var_export($v['cfgs'],TRUE)))."' WHERE rid='$k'",'SILENT');
	}
	cls_CacheFile::Update('cnrels');
	cls_message::show('分类删除完成', "?entry=$entry&action=coclassedit&coid=$coid");
}

# 为了兼容应用系统的扩展部分，暂时保留
function fetch_arr(){
	if(!($coid = cls_env::GetG('coid'))) return array();
	return cls_catalog::InitialInfoArray($coid);
}
# 为了兼容应用系统的扩展部分，暂时保留
function fetch_one($ccid){
	$ccid = intval($ccid);
	if(!($coid = cls_env::GetG('coid'))) return array();
	return cls_catalog::InitialOneInfo($coid,$ccid);
}
function clearsameid($var){
	global $ccid;
	return $var == $ccid ? false : true;
}
function pidsarr($coid,$maxlv = 0,$nospace = 0){//maxlv为0时按类系设置，否则手动传入
	global $cotypes;
	$narr = array();
	if(empty($cotypes[$coid])) return $narr;
	$maxlv || $maxlv = $cotypes[$coid]['maxlv'];
	$sarr = cls_cache::Read('coclasses',$coid);
	foreach($sarr as $k => $v){
		if(!$maxlv || $v['level'] < $maxlv - 1){
			$narr[$k] = ($nospace ? '' : str_repeat('&nbsp; &nbsp; ',$v['level'])).$v['title'];
		}
	}
	return $narr;
}