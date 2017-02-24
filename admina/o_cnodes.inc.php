<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
foreach(array('catalogs','cotypes','o_mtpls','o_cntpls','o_cnconfigs',) as $k) $$k = cls_cache::Read($k);
if($action == 'cnconfigs'){
	backnav('mobile','cnconfigs');
	if(!submitcheck('bcnconfigs')){
		$ncoid = isset($ncoid) ? intval($ncoid) : -1;//对与某类系有关的方案进行预选
		tabheader("节点组成方案&nbsp; &nbsp; >><a href=\"?entry=$entry&action=cnconfigadd\" onclick=\"return floatwin('open_cnodes',this)\">添加</a>",'cnodesupdate',"?entry=$entry&action=$action",3);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",'ID',array('方案名称','txtL'),array('备注说明','txtL'),array('节点组成结构','txtL'),array('节点配置','txtL'),'关闭','排序','复制','编辑'));
		foreach($o_cnconfigs as $k => $v){
			$configstr = '';$checked = 0;
			if(empty($v['isfunc'])){
				$idsarr = cfgs2ids($v['configs']);
				foreach($v['configs'] as $k1 => $v1){
					$configstr .= ($configstr ? ' x ' : '').($k1 ? @$cotypes[$k1]['cname'] : '栏目').'('.count($idsarr[$k1]).')';
					$k1 == $ncoid && $checked = 1;
				}
			}else{
				foreach($v['configs'] as $k1 => $v1){
					$configstr .= ($configstr ? ' x ' : '').($k1 ? @$cotypes[$k1]['cname'] : '栏目');
					$k1 == $ncoid && $checked = 1;
				}
				$configstr .= '...函数';
			}
			$cntplstr = empty($o_cntpls[$v['tid']]['cname']) ? '-' : $v['tid'].'-'.$o_cntpls[$v['tid']]['cname'];
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[]\" value=\"$k\"".($checked ? ' checked' : '')."></td>\n".
				"<td class=\"txtC\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"20\" maxlength=\"30\" name=\"cnconfigsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"35\" maxlength=\"50\" name=\"cnconfigsnew[$k][remark]\" value=\"$v[remark]\"></td>\n".
				"<td class=\"txtL\">$configstr</td>\n".
				"<td class=\"txtL\">$cntplstr</td>\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"cnconfigsnew[$k][closed]\" value=\"1\"".($v['closed'] ? " checked" : "")."></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"cnconfigsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=cnconfigdetail&cncid=$k&iscopy=1\" onclick=\"return floatwin('open_cnodes',this)\">复制</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=cnconfigdetail&cncid=$k\" onclick=\"return floatwin('open_cnodes',this)\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter();
		tabheader('操作项目'.viewcheck(array('name' => 'viewdetail','value' =>0,'body' =>$actionid.'tbodyfilter',)).' &nbsp;显示详细');
		$str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"edit\" checked>修改方案列表 &nbsp;";
		$str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"newupdate\"".(empty($arcdeal) || $arcdeal != 'newupdate' ? '' : ' checked')."><b>补全方案中节点</b> &nbsp;";
		$str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"oldupdate\"".(empty($arcdeal) || $arcdeal != 'oldupdate' ? '' : ' checked')."><b>补全并更新节点配置</b> &nbsp;";
		$str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"delete\" onclick=\"return deltip(this,$no_deepmode)\">删除方案 &nbsp;";
		trbasic('选择操作项目','',$str,'');
		echo "<tbody id=\"{$actionid}tbodyfilter\" style=\"display:none\">";
		$cnmodearr = array(0 => '重设模式',1 => '添加模式',2 => '移除模式',);
		trbasic("<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"ccid0\">&nbsp;修改方案组成:栏目",'',multiselect('cnccids0[]',cls_catalog::ccidsarr(0),array(),'30%').
		"&nbsp; &nbsp; <select id=\"cnmode0\" name=\"cnmode0\" style=\"vertical-align: top;\">".makeoption($cnmodearr)."</select>",'',array('guide' => '仅当前栏目或类系包含在选中方案，且为手动选择时有效。修改方案不自动更新节点。',));
		foreach($cotypes as $k => $v){
			if($v['sortable']){
				trbasic("<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"ccid$k\">&nbsp;修改方案组成:".$v['cname'],'',multiselect('cnccids'.$k.'[]',cls_catalog::ccidsarr($k),array(),'30%').
				"&nbsp; &nbsp; <select id=\"cnmode$k\" name=\"cnmode$k\" style=\"vertical-align: top;\">".makeoption($cnmodearr)."</select>",'');
			}
		}
		echo "</tbody>";
		tabfooter('bcnconfigs');
		a_guide('cnconfigs');
	}else{
		if(!empty($arcdeal)){
			if($arcdeal == 'edit'){
				if(!empty($cnconfigsnew)){
					foreach($cnconfigsnew as $k => $v){
						$v['cname'] = trim(strip_tags($v['cname']));
						!$v['cname'] && $v['cname'] = $o_cnconfigs[$k]['cname'];
						$v['remark'] = trim(strip_tags($v['remark']));
						$v['closed'] = empty($v['closed']) ? 0 : 1;
						$v['vieworder'] = max(0,intval($v['vieworder']));
						foreach(array('cname','remark','closed','vieworder',) as $var) $o_cnconfigs[$k][$var] = $v[$var];
					}
					cls_Array::_array_multisort($o_cnconfigs,'vieworder',1);
					cls_CacheFile::Save($o_cnconfigs,'o_cnconfigs','o_cnconfigs');
				}
			}elseif($arcdeal == 'delete'){
				if(!empty($selectid) && deep_allow($no_deepmode)){
					foreach($selectid as $k){
						unset($o_cnconfigs[$k]);
						unset($cnconfigsnew[$k]);
					}
					cls_CacheFile::Save($o_cnconfigs,'o_cnconfigs','o_cnconfigs');
				}
			}elseif(in_array($arcdeal,array('newupdate','oldupdate'))){
				if(empty($selectid) && empty($selectstr)) cls_message::show('请选择节点组成方案',"?entry=$entry&action=$action");
				if(empty($selectid)) $selectid = array_filter(explode(',',$selectstr));
				$pages = max(empty($pages) ? 0 : max(0,intval($pages)),count($selectid));
				$cncid = $selectid[0];
				if($cnconfig = $o_cnconfigs[$cncid]){
					if(cnodesfromcnc($cnconfig,$arcdeal == 'newupdate' ? 0 : 1,1)) cls_CacheFile::Update('o_cnodes');
				}
				unset($selectid[0]);
				if($selectid){
					$selectstr = implode(',',$selectid);
					$npage = $pages - count($selectid) + 1;
					cls_message::show("节点更新正在进行中...<br>共 $pages 页，正在处理第 $npage 页<br><br><a href=\"?entry=$entry&action=$action\">>>中止当前操作</a>","?entry=$entry&action=$action&selectstr=$selectstr&arcdeal=$arcdeal&pages=$pages&bcnconfigs=1");
				}
			}elseif(in_str('ccid',$arcdeal)){//更新结构但不更新节点
				if(!empty($selectid)){
					$coid = intval(str_replace('ccid','',$arcdeal));
					${"cnccids$coid"} = empty(${"cnccids$coid"}) ? array() : ${"cnccids$coid"};
					${"cnmode$coid"} = empty(${"cnmode$coid"}) ? 0 : ${"cnmode$coid"};
					foreach($selectid as $k) modify_cnconfig(@$o_cnconfigs[$k],$coid,${"cnccids$coid"},${"cnmode$coid"});
					cls_CacheFile::Save($o_cnconfigs,'o_cnconfigs','o_cnconfigs');
				}
			}
		}
		adminlog('批量操作节点组成方案');
		cls_message::show('组合方案操作完成', "?entry=$entry&action=$action");
	}
}elseif($action == 'patchupdate'){
	echo "<title>类目节点批量补全</title>";
	$coid = empty($coid) ? 0 : intval($coid);
	if($coid && empty($cotypes[$coid])) cls_message::show('指定的类系不存在');
	if(empty($selectstr)){
		$selectid = array();
		foreach($o_cnconfigs as $k => $v) empty($v['configs'][$coid]) || $selectid[] = $k;
	}else $selectid = array_filter(explode(',',$selectstr));
	if($selectid){
		$pages = max(empty($pages) ? 0 : max(0,intval($pages)),count($selectid));
		$cncid = $selectid[0];
		if($cnconfig = $o_cnconfigs[$cncid]){
			if(cnodesfromcnc($cnconfig,0,1)) cls_CacheFile::Update('o_cnodes');
		}
		unset($selectid[0]);
	}
	if($selectid){
		$selectstr = implode(',',$selectid);
		$npage = $pages - count($selectid) + 1;
		cls_message::show("节点更新正在进行中...<br>共 $pages 页，正在处理第 $npage 页","?entry=$entry&action=$action&coid=$coid&selectstr=$selectstr&pages=$pages");
	}else cls_message::show('节点批量补全完成',axaction(2));
}elseif($action == 'clearnodes'){
	echo "<title>清空所有节点并重建</title>";
	if(!submitcheck('bsubmit')) cls_message::show('非法的操作。'); # 控制演示站的操作权限
	$db->query("TRUNCATE TABLE {$tblprefix}o_cnodes");
	if(!empty($o_cnconfigs)){
		$selectid = array();
		foreach($o_cnconfigs as $k => $v){
			$selectid[] = $k;
		}
		$selectstr = implode(',',$selectid);
		$pages = count($selectid);
		$npage = 1;
		cls_message::show("节点已清空，接下来将重建节点...<br>共 $pages 页，正在处理第 $npage 页","?entry=$entry&action=patchupdate&selectstr=$selectstr&pages=$pages");
		
	}else cls_message::show('所有节点已清空，未生成任何节点。',axaction(2));
}elseif($action == 'cnconfigdetail' && $cncid){
	$iscopy = empty($iscopy) ? 0 : 1;
	echo "<title>".($iscopy ? '复制节点组成方案' : '编辑节点组成方案')."</title>";
	if(!($cnconfig = @$o_cnconfigs[$cncid])) cls_message::show('请指定正确的节点组成方案');
	$configs = &$cnconfig['configs'];
	if(!submitcheck('bsubmit')){
		tabheader($iscopy ? '复制节点组成方案' : '编辑节点组成方案','cnconfigdetail',"?entry=$entry&action=$action".($iscopy ? '&iscopy=1' : '')."&cncid=$cncid",2,0,1);
		trbasic('方案名称','cnconfignew[cname]',$cnconfig['cname'].($iscopy ? '_复制' : ''),'text',array('w' => 50,'validate' => makesubmitstr('cnconfignew[cname]',1,0,4,50)));
		$arr = array(0 => '不设置');
		foreach($o_cntpls as $k => $v) $arr[$k] = $k.'-'.$v['cname'];
		trbasic('节点配置','cnconfignew[tid]',makeoption($arr,$cnconfig['tid']),'select');
		$modearr = array(0 => '全部分类',1 => '全部顶级类目',2 => '全部二级类目',3 => '全部三级类目',4 => '全部四级栏目',5 => '来自扩展函数',-1 => '手动指定');
		$nomodearr = array(0 => '不设置',1 => '手动指定');
		$i = 1;
		foreach($configs as $k => $v){
			$arr = $k ? cls_cache::Read('coclasses',$k) : $catalogs;
			foreach($arr as $x => $y) $arr[$x] = $y['title'].'('.$y['level'].')';
			$cname = $k ? $cotypes[$k]['cname'] : '栏目';
			sourcemodule("$i.包含:".$cname.
				"<br><input class=\"checkbox\" type=\"checkbox\" name=\"configsnew[$k][son]\" value=\"1\"".(empty($v['son']) ? "" : " checked").">含子分类",
				"configsnew[$k][mode]",
				$modearr,
				empty($v['mode']) ? 0 : $v['mode'],
				-1,
				"configsnew[$k][ids][]",
				$arr,
				empty($v['ids']) ? array() : explode(',',$v['ids']),
				'25%',1,'',1
			);
			sourcemodule("$i.排除:".$cname.
				"<br><input class=\"checkbox\" type=\"checkbox\" name=\"configsnew[$k][noson]\" value=\"1\"".(empty($v['noson']) ? "" : " checked").">含子分类",
				"configsnew[$k][nomode]",
				$nomodearr,
				empty($v['noids']) ? 0 : 1,
				1,
				"configsnew[$k][noids][]",
				$arr,
				empty($v['noids']) ? array() : explode(',',$v['noids']),
				'25%',1,'',1
			);
			$i ++;
		}
		if(!empty($cnconfig['isfunc'])){
			trbasic('扩展函数','cnconfignew[funcode]',$cnconfig['funcode'],'textarea',array('guide'=>'请使用return 扩展函数名(参数...);输入，错误返回FALSE，成功返回TRUE。<br>当前组合方案配置使用$cnconfig传入。<br>扩展函数为当前方案生成节点过程，请定义到'._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php'));
			trbasic('函数使用备注','cnconfignew[fremark]',$cnconfig['fremark'],'textarea');
		}
		tabfooter('bsubmit');
	}else{
		$cnconfignew['cname'] = trim(strip_tags($cnconfignew['cname']));
		$cnconfignew['cname'] || $cnconfignew['cname'] = $cnconfig['cname'];
		if(!empty($configsnew)){
			foreach($configsnew as $k => $v){
				foreach(array('ids','noids') as $var) $configsnew[$k][$var] = $configsnew[$k][$var][0];
				foreach(array('son','noson') as $var) $configsnew[$k][$var] = empty($configsnew[$k][$var]) ? 0 : 1;
				if(empty($configsnew[$k]['nomode'])) $configsnew[$k]['noids'] = '';
				unset($configsnew[$k]['nomode']);
			}
		}
		if(!$configsnew) cls_message::show('资料填写不完整',M_REFERER);
		
		if($iscopy){
			$cncid = auto_insert_id('o_cnconfigs');
			$cnconfig['cncid'] = $cncid;
		}
		
		$cnconfig['cname'] = $cnconfignew['cname'];
		$cnconfig['tid'] = $cnconfignew['tid'];
		$cnconfig['configs'] = $configsnew;
		if(!empty($cnconfig['isfunc'])){
			$cnconfignew['funcode'] = trim($cnconfignew['funcode']);
			if(!$cnconfignew['funcode']) cls_message::show('资料填写不完整',M_REFERER);
			$cnconfig['isfunc'] = 1;
			$cnconfig['funcode'] = $cnconfignew['funcode'];
			$cnconfig['fremark'] = $cnconfignew['fremark'];
		}
		$o_cnconfigs[$cncid] = $cnconfig;
		cls_CacheFile::Save($o_cnconfigs,'o_cnconfigs','o_cnconfigs');
		
		adminlog($iscopy ? '复制节点组成方案' : '编辑节点组成方案');
		cls_message::show($iscopy ? '复制节点组成方案完成' : '编辑节点组成方案完成',axaction(6,"?entry=$entry&action=cnconfigs"));
	}
}elseif($action == 'cnconfigadd'){
	echo "<title>添加节点组成方案</title>";
	if(!submitcheck('bsubmit')){
		tabheader('添加节点组成方案','cnconfigsadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('节点组成方案名称','cncfgcname',@$cncfgcname,'text',array('w' => 50,'validate' => makesubmitstr('cncfgcname',1,0,4,50)));
		$arr = array(0 => '不设置');
		foreach($o_cntpls as $k => $v) $arr[$k] = $k.'-'.$v['cname'];
		trbasic('选择节点配置','cntplnew',makeoption($arr,@$cntplnew),'select');
		if(empty($cncoids)){
			ksort($cotypes);
			$coidsarr = array('caid' => '栏目');
			foreach($cotypes as $k => $v) $v['sortable'] && $coidsarr['ccid'.$k] = $v['cname'];
			trbasic('*参与生成节点的类系','',makecheckbox('cncoids[]',$coidsarr,array(),5),'');
			trbasic('组合配置模式','',makeradio('isfunc',array('普通模式','扩展函数'),empty($isfunc) ? 0 : 1),'',array('guide' => '请注意，此项提交后不可变更'));
			tabfooter('baddpre','继续');
		}else{
				$modearr = array(0 => '全部分类',1 => '全部顶级类目',2 => '全部二级类目',3 => '全部三级类目',4 => '全部四级栏目',5 => '来自扩展函数',-1 => '手动指定');
				$nomodearr = array(0 => '不设置',1 => '手动指定');
				$i = 1;
				$cncoids = array_filter($cncoids); //去掉第一个空数组
				foreach($cncoids as $k){
					$k = $k == 'caid' ? 0 :  intval(str_replace('ccid','',$k));
					$arr = $k ? cls_cache::Read('coclasses',$k) : $catalogs;
					foreach($arr as $x => $y) $arr[$x] = $y['title'].'('.$y['level'].')';
					$cname = $k ? $cotypes[$k]['cname'] : '栏目';
					sourcemodule("$i.".'包含以下：'.$cname.
						"<br><input class=\"checkbox\" type=\"checkbox\" name=\"configsnew[$k][son]\" value=\"1\">含子分类",
						"configsnew[$k][mode]",
						$modearr,
						0,
						-1,
						"configsnew[$k][ids][]",
						$arr,
						array(),
						'25%',1,'',1
					);
					sourcemodule("$i.".'排除以下：'.$cname.
						"<br><input class=\"checkbox\" type=\"checkbox\" name=\"configsnew[$k][noson]\" value=\"1\">含子分类",
						"configsnew[$k][nomode]",
						$nomodearr,
						0,
						1,
						"configsnew[$k][noids][]",
						$arr,
						array(),
						'25%',1,'',1
					);
					$i ++;
				}
			if(!empty($isfunc)){
				trbasic('扩展函数','funcodenew','','textarea',array('guide'=>'请使用return 扩展函数名(参数...);输入，错误返回FALSE，成功返回TRUE。<br>当前组合方案配置使用$cnconfig传入。<br>扩展函数为当前方案生成节点过程，请定义到'._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php'));
				trbasic('函数使用备注','fremarknew','','textarea');
				trhidden('isfunc',1);
			}
			tabfooter('bsubmit','添加');
		}
		a_guide('cnconfigs');
	}else{
		if(!$cncfgcname = trim($cncfgcname)) cls_message::show('请输入方案名称',M_REFERER);
		$tid = empty($cntplnew) ? 0 : $cntplnew;
		foreach($configsnew as $k => $v){
			foreach(array('ids','noids') as $var) $configsnew[$k][$var] = $configsnew[$k][$var][0];
			foreach(array('son','noson') as $var) $configsnew[$k][$var] = empty($configsnew[$k][$var]) ? 0 : 1;
			if(empty($configsnew[$k]['nomode'])) $configsnew[$k]['noids'] = '';
			unset($configsnew[$k]['nomode']);
		}
		if(empty($configsnew)) cls_message::show('请输入类目组合',M_REFERER);
		$cncid = auto_insert_id('o_cnconfigs');
		$cnconfig = array('cncid' => $cncid,'cname' => $cncfgcname,'configs' => $configsnew,'tid' => $tid,'vieworder' => 0,'remark' => '','closed' => 0,);
		if(!empty($isfunc)){
			$funcodenew = trim($funcodenew);
			if(empty($funcodenew)) cls_message::show('请输入扩展函数内容',M_REFERER);
			$cnconfig['isfunc'] = 1;
			$cnconfig['funcode'] = $funcodenew;
			$cnconfig['fremark'] = $fremarknew;
		}
		$o_cnconfigs[$cncid] = $cnconfig;
		
		cls_CacheFile::Save($o_cnconfigs,'o_cnconfigs','o_cnconfigs');
		adminlog('添加节点组成方案');
		cls_message::show('节点组成方案添加成功',axaction(6,"?entry=$entry&action=cnconfigs"));
	}
}elseif($action == 'cntpladd'){
	echo "<title>添加节点配置</title>";
	if(!submitcheck('bcntpladd')){
		tabheader('添加节点配置','cntpladd',"?entry=$entry&action=$action",2,0,1);
		trbasic('节点配置名称','cntplnew[cname]','','text',array('validate'=>makesubmitstr('cntplnew[cname]',1,1,4,30)));
		tabfooter('bcntpladd','添加');
		a_guide('cntpladd');
	} else {
		if(!($cntplnew['cname'] = trim(strip_tags($cntplnew['cname'])))) cls_message::show('请输入标题！',M_REFERER);
		$tid = auto_insert_id('o_cntpls');
		$o_cntpls[$tid] = array('tid' => $tid,'cname' => $cntplnew['cname'],'addnum' =>0,'vieworder' => 0,'cfgs' => array(),);
		cls_CacheFile::Save($o_cntpls,'o_cntpls','o_cntpls');
		adminlog('添加节点配置');
		cls_message::show('节点配置添加完成',"?entry=$entry&action=cntpldetail&tid=$tid");
	}
}elseif($action == 'cntpldetail' && $tid){
	if(!($cntpl = @$o_cntpls[$tid])) cls_message::show('请选择节点配置');
	echo "<title>节点配置 - $cntpl[cname]</title>";
	if(!submitcheck('bcntpldetail')){
		tabheader("节点配置&nbsp;&nbsp;[$cntpl[cname]]",'cntpldetail',"?entry=$entry&action=$action&tid=$tid");
		$arr = array();for($i = 0;$i <= $cn_max_addno;$i ++) $arr[$i] = $i;
		$addnum = empty($cntpl['addnum']) ? 0 : $cntpl['addnum'];
		trbasic('附加页数量','',makeradio('fmdata[addnum]',$arr,$addnum),'');
		tabfooter();
		
		$cfgs = @$cntpl['cfgs'];
		for($i = 0;$i <= $cn_max_addno;$i ++){
			tabheader(($i ? '附加页'.$i : '节点首页').'设置'.viewcheck(array('name' =>'viewdetail','title' => '详细','value' => $i > $addnum ? 0 : 1,'body' =>$actionid.'tbodyfilter'.$i)));
			echo "<tbody id=\"{$actionid}tbodyfilter$i\" style=\"display:".($i > $addnum ? 'none' : '')."\">";
			trbasic('页面模板',"cfgsnew[$i][tpl]",makeoption(array('' => '不设置') + cls_mtpl::o_mtplsarr('cindex'),empty($cfgs[$i]['tpl']) ? '' : $cfgs[$i]['tpl']),'select',array('guide' => cls_mtpl::mtplGuide('cindex')));
			trbasic('虚拟静态URL','',makeradio("cfgsnew[$i][novu]",array(0 => '按系统总设置',1 => '关闭虚拟静态'),empty($cfgs[$i]['novu']) ? 0 : $cfgs[$i]['novu']),'');
			echo "</tbody>";
			if($i != $cn_max_addno) tabfooter();
		}
		tabfooter('bcntpldetail');
		a_guide('cntpldetail');
	}else{
		$cntpl['addnum'] = max(0,intval($fmdata['addnum']));		
		foreach($cfgsnew as $k => $v){
			if($k > $cntpl['addnum']){
				unset($cfgsnew[$k]);
				continue;
			}else{
				foreach(array('tpl','novu') as $var){
					if(empty($v[$var])){
						unset($cfgsnew[$k][$var]);
					}
				}
			}
		}
		$cntpl['cfgs'] = empty($cfgsnew) ? array() : $cfgsnew;
		$o_cntpls[$tid] = $cntpl;
		cls_CacheFile::Save($o_cntpls,'o_cntpls','o_cntpls');
		adminlog('类目节点配置');
		cls_message::show('节点配置修改完成',axaction(6,"?entry=$entry&action=cntplsedit"));
	}
}elseif($action == 'cntplsedit'){
	backnav('mobile','cntpls');
	if(!submitcheck('bcntplsedit')){
		tabheader("节点配置管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=cntpladd\" onclick=\"return floatwin('open_cntplsedit',this)\">添加</a>",'cntplsedit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID',array('标题','txtL'),'附加页','排序','删除','详情'));
		foreach($o_cntpls as $k => $v){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"cntplsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w40\">$v[addnum]</td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"cntplsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=cntpldel&tid=$k\">删除</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=cntpldetail&tid=$k\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bcntplsedit','修改');
		a_guide('cntplsedit');
	}else{
		if(isset($cntplsnew)){
			foreach($cntplsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $o_cntpls[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				foreach(array('cname','vieworder',) as $var) $o_cntpls[$k][$var] = $v[$var];
			}
			cls_Array::_array_multisort($o_cntpls,'vieworder',1);
			cls_CacheFile::Save($o_cntpls,'o_cntpls','o_cntpls');
			adminlog('编辑节点配置');
		}
		cls_message::show('节点配置修改完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'cntpldel' && $tid){
	backnav('mobile','cntpls');
	if(empty($o_cntpls[$tid])) cls_message::show('请选择节点配置');
	deep_allow($no_deepmode,"?entry=$entry&action=cntplsedit");
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=cntpldel&tid=$tid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=cntplsedit>返回</a>";
		cls_message::show($message);
	}
	$db->query("UPDATE {$tblprefix}o_cnodes SET tid='0' WHERE tid='$tid'");
	unset($o_cntpls[$tid]);
	
	cls_CacheFile::Save($o_cntpls,'o_cntpls','o_cntpls');
	cls_CacheFile::Update('o_cnodes');
	adminlog('删除节点配置');
	cls_message::show('节点配置删除成功', "?entry=$entry&action=cntplsedit");
}elseif($action == 'cnodescommon'){
	backnav('mobile','cnodes');
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$keyword = empty($keyword) ? '' : $keyword;
	$caid = !isset($caid)? '0' : max(-4,intval($caid));
	$tid = !isset($tid)? '-1' : max(-1, intval($tid));
	$keeptid = !isset($keeptid)? '-1' : max(-1, intval($keeptid));
	$cnlevel = !isset($cnlevel) ? '0' : $cnlevel;

	$fromsql = "FROM {$tblprefix}o_cnodes cn force index(ename)";
	$wheresql = "cn.closed=0";
	$cnlevel && $wheresql .= " AND cn.cnlevel='$cnlevel'";
	if(!empty($caid)){
		if($caid < -1){
			$fromsql .= " INNER JOIN {$tblprefix}catalogs ca ON (ca.caid=cn.caid)";
			$wheresql .= " AND ca.level='".(abs($caid) - 2)."'";
		}elseif($caid == -1){
			$wheresql .= " AND cn.caid<>0";
		}else $wheresql .= " AND cn.caid ".multi_str(sonbycoid($caid));
	}
	$tid != '-1' && $wheresql .= " AND cn.tid='$tid'";
	$keeptid != '-1' && $wheresql .= " AND cn.keeptid='$keeptid'";
	$keyword && $wheresql .= " AND cn.ename ".sqlkw($keyword);

	$filterstr = '';
	foreach(array('keyword','caid','cnlevel',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('tid','keeptid',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	foreach($cotypes as $k => $v){
		if($v['sortable']){
			${"ccid$k"} = isset(${"ccid$k"}) ? max(-4,intval(${"ccid$k"})) : 0;
			if(!empty(${"ccid$k"})){
				if(${"ccid$k"} < -1){
					$fromsql .= " INNER JOIN {$tblprefix}coclass$k cc$k ON (cc$k.ccid=cn.ccid$k)";
					$wheresql .= " AND cc$k.level='".(abs(${"ccid$k"}) - 2)."'";
				}elseif(${"ccid$k"} == -1){
					$wheresql .= " AND cn.ccid$k<>0";
				}else{
					$wheresql .= " AND cn.ccid$k ".multi_str(sonbycoid(${"ccid$k"},$k));
				}
				${"ccid$k"} && $filterstr .= "&ccid$k=".${"ccid$k"};
			}
		}
	}
	$wheresql = $wheresql ? "WHERE $wheresql" : '';
	if(!submitcheck('bcnodescommon')){
		echo form_str('cnodescommon',"?entry=$entry&action=$action&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\" width=\"740\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\">&nbsp; ";
		echo "<select name=\"cnlevel\">".makeoption(array('0'=>'交叉','1'=>'单重','2'=>'两重','3'=>'三重','4'=>'四重'),$cnlevel)."</select>&nbsp; ";
		$arr = array('-1' => '节点配置','0' => '尚未配置',);foreach($o_cntpls as $k => $v) $arr[$k] = $k.'-'.$v['cname'];
		echo "<select name=\"tid\">".makeoption($arr,$tid)."</select>&nbsp; ";
		$arr = array('-1' => '定制节点?','1' => '是','0' => '否',);
		echo "<select name=\"keeptid\">".makeoption($arr,$keeptid)."</select>&nbsp; ";
		echo "<select name=\"caid\">".makeoption(array('0' => '不限栏目','-1' => '全部','-2' => '顶级','-3' => '二级','-4' => '三级',) + cls_catalog::ccidsarr(0),$caid)."</select>&nbsp; ";
		foreach($cotypes as $k => $v){
			if($v['sortable']) echo "<select name=\"ccid$k\">".makeoption(array('0' => $v['cname'],'-1' => '全部','-2' => '顶级','-3' => '二级','-4' => '三级',) + cls_catalog::ccidsarr($k),${"ccid$k"})."</select>&nbsp; ";
		}
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();

		$TitleStr = "类目节点列表";
		$TitleStr .= "&nbsp; &nbsp; <input class=\"checkbox\" type=\"checkbox\" name=\"select_all\" value=\"1\">&nbsp;全选所有页内容";
		$TitleStr .= "&nbsp; &nbsp; <a href=\"?entry=$entry&action=clearnodes&bsubmit=1\" onclick=\"return floatwin('open_fnodes',this)\">>>清空所有节点并重建</a>";
		tabheader($TitleStr,'','',12);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('节点名称','txtL'),array('节点识别字串','txtL'),array('节点配置','txtL'),'附加页',array('查看','txtL'),);
		$cy_arr[] = '详情';
		trcategory($cy_arr);

		$pagetmp = $page;
		do{
			$query = $db->query("SELECT cn.* $fromsql $wheresql ORDER BY cnid ASC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		while($cnode = $db->fetch_array($query)){
			$cnode['nodemode'] = 1;//标为手机节点
			$cnode = LoadCnodeConfig($cnode);
			$catalogstr = $cnode['ename'];
			cls_url::view_cnurl($cnode['ename'],$cnode);
			$cnamestr = cls_node::cnode_cname($cnode['ename']);
			$addnum = empty($cnode['addnum']) ? 0 : $cnode['addnum'];
			$cnstplstr = (empty($o_cntpls[$cnode['tid']]['cname']) ? '-' : "$cnode[tid]-".$o_cntpls[$cnode['tid']]['cname']).($cnode['keeptid'] ? ' (定制)' : '');
			$lookstr = '';
			for($i = 0;$i <= $addnum;$i ++) $lookstr .= "<a href=\"".$cnode['indexurl'.($i ? $i : '')]."\" target=\"_blank\">".($i ? $i : '首页')."</a>&nbsp; ";
			echo "<tr class=\"txt\"><td class=\"txtC\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$cnode[cnid]]\" value=\"$cnode[cnid]\">\n";
			echo "<td class=\"txtL\">$cnamestr</td>\n";
			echo "<td class=\"txtL\">$catalogstr</td>\n";
			echo "<td class=\"txtL\">$cnstplstr</td>\n";
			echo "<td class=\"txtC\">$addnum</td>\n";
			echo "<td class=\"txtL\">$lookstr</td>\n";
			echo "<td class=\"txtC\"><a href=\"?entry=$entry&action=cnodedetail&cnid=$cnode[cnid]\" onclick=\"return floatwin('open_cnodedetail',this)\">编辑</a></td></tr>\n";
		}
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"), $atpp, $page, "?entry=$entry&action=$action$filterstr");

		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除节点';
		$s_arr['keeptid'] = '设为定制节点';
		$s_arr['un_keeptid'] = '取消定制节点';
		if($s_arr){
			$soperatestr = '';
			$i = 1;
			foreach($s_arr as $k => $v){
				$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" id=\"cndeal[$k]\" name=\"cndeal[$k]\" value=\"1\"" . ($k == 'delete' ? ' onclick="deltip()"' : '') . "><label for=\"cndeal[$k]\">$v</label> &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$soperatestr,'');
		}
		$arr = array('0' => '不设置');foreach($o_cntpls as $k => $v) $arr[$k] = $k.'-'.$v['cname'];
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"cndeal[cntpl]\" value=\"1\">&nbsp;设置节点配置",'cncntpl',makeoption($arr,0),'select',array('guide'=>'节点配置包含节点数量，模板，静态保存格式等设置。'));
		tabfooter('bcnodescommon');
		a_guide('cnodesedit');
	}
	else{
		if(empty($cndeal) && empty($dealstr)) cls_message::show('请选择操作项目',"?entry=$entry&action=$action&page=$page$filterstr");
		if(empty($selectid) && empty($select_all)) cls_message::show('请选择节点',"?entry=$entry&action=$action&page=$page$filterstr");
		if(!empty($select_all)){
			if(empty($dealstr)){
				$dealstr = implode(',',array_keys(array_filter($cndeal)));
			}else{
				$cndeal = array();
				foreach(array_filter(explode(',',$dealstr)) as $k) $cndeal[$k] = 1;
			}
			if(!isset($ptypestr)){
				$ptypes = empty($ptypes) ? array() : $ptypes;
				$ptypestr = implode(',',$ptypes);
			}else $ptypes = explode(',',$ptypestr);
			
			$parastr = "";
			foreach(array('cncntpl','ptypestr',) as $k) $parastr .= "&$k=".$$k;

			$selectid = $cnstrarr = array();
			$npage = empty($npage) ? 1 : $npage;
			if(empty($pages)) $pages = @ceil($db->result_one("SELECT count(*) $fromsql $wheresql") / $atpp);
			if($npage <= $pages){
				$fromstr = empty($fromid) ? "" : "cn.cnid>$fromid";
				$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
				$query = $db->query("SELECT cn.cnid,cn.ename $fromsql $nwheresql ORDER BY cn.cnid ASC LIMIT 0,$atpp");
				while($item = $db->fetch_array($query)) $selectid[] = $item['cnid'];
			}
			if(empty($selectid)) cls_message::show('请选择节点',"?entry=$entry&action=$action&page=$page$filterstr");
		}
		if(!empty($cndeal['delete'])){
			$query = $db->query("SELECT * FROM {$tblprefix}o_cnodes WHERE cnid ".multi_str($selectid));
			while($r = $db->fetch_array($query)){
				$r['nodemode'] = 1;//置为手机节点
				$r = LoadCnodeConfig($r);
				for($i = 0;$i <= @$r['addnum'];$i ++) m_unlink(cls_url::m_parseurl(cls_node::cn_format($r['ename'],$i,$r),array('addno' => $i)));
			}
			$db->query("DELETE FROM {$tblprefix}o_cnodes WHERE cnid ".multi_str($selectid), 'UNBUFFERED');
		}else{
			if(!empty($cndeal['keeptid'])){
				$db->query("UPDATE {$tblprefix}o_cnodes SET keeptid='1' WHERE cnid ".multi_str($selectid));
			}elseif(!empty($cndeal['un_keeptid'])){
				$db->query("UPDATE {$tblprefix}o_cnodes SET keeptid='0' WHERE cnid ".multi_str($selectid));
			}
			if(!empty($cndeal['cntpl'])){
				$cncntpl = empty($cncntpl) ? 0 : (empty($o_cntpls[$cncntpl]) ? 0 : $cncntpl);
				$db->query("UPDATE {$tblprefix}o_cnodes SET tid='$cncntpl' WHERE cnid ".multi_str($selectid));
			}
		}
		if(!empty($select_all)){
			$npage ++;
			if($npage <= $pages){
				$fromid = max($selectid);
				$transtr = '';
				$transtr .= "&select_all=1";
				$transtr .= "&pages=$pages";
				$transtr .= "&npage=$npage";
				$transtr .= "&bcnodescommon=1";
				$transtr .= "&fromid=$fromid";
				cls_message::show("文件操作正在进行中...<br>共 $pages 页，正在处理第 $npage 页<br><br><a href=\"?entry=$entry&action=$action&page=$page$filterstr\">>>中止当前操作</a>","?entry=$entry&action=$action&page=$page$filterstr$transtr$parastr&dealstr=$dealstr");
			}
		}
		cls_CacheFile::Update('o_cnodes');
		adminlog('节点管理操作','节点列表管理操作');
		cls_message::show('节点操作完成',"?entry=$entry&action=$action&page=$page$filterstr");
	}
}elseif($action == 'cnodedetail' && $cnid){
	echo "<title>节点详情</title>";
	$forward = empty($forward) ? M_REFERER : $forward;
	$cnode = $db->fetch_one("SELECT * FROM {$tblprefix}o_cnodes WHERE cnid=$cnid");
	$cnode['nodemode'] = 1;//将节点置为手机节点
	$cnode = LoadCnodeConfig($cnode);
	if(!submitcheck('bcnodedetail')){
		tabheader('节点详细设置','cnodedetail',"?entry=$entry&action=$action&cnid=$cnid&forward=".urlencode($forward));
		trbasic('节点名称','',cls_node::cnode_cname($cnode['ename']),'');
		trbasic('节点别名','cnodenew[alias]',$cnode['alias']);
		trbasic('指定节点链接','cnodenew[appurl]',$cnode['appurl'],'text',array('guide'=>'站外Url或站内Url均可','w'=>50));
		$arr = array('0' => '不设置');foreach($o_cntpls as $k => $v) $arr[$k] = $k.'-'.$v['cname'];
		trbasic('设置节点配置','cnodenew[tid]',makeoption($arr,$cnode['tid']),'select',array('guide'=>'节点配置包含节点数量，模板，静态保存格式等设置。'));
		trbasic('设为定制节点?','cnodenew[keeptid]',$cnode['keeptid'],'radio',array('guide'=>'定制节点在节点组成方案的 \'补全并更新节点配置\' 批量操作中，节点配置将保持现有设置，不被更新。'));
		tabfooter();

		tabheader('节点相关配置 &nbsp;- &nbsp;'.(empty($o_cntpls[$cnode['tid']]['cname']) ? '尚未设置' : "<a href=\"?entry=$entry&action=cntpldetail&tid=$cnode[tid]\" onclick=\"return floatwin('open_cntplsedit',this)\">>>".$o_cntpls[$cnode['tid']]['cname']."</a>"));
		trbasic('附加页数量','',empty($cnode['addnum']) ? '0' : $cnode['addnum'],'');
		$mtplsarr = cls_mtpl::o_mtplsarr('cindex');
		for($i = 0;$i <= @$cnode['addnum'];$i ++){
			$pvar = $i ? '附加页'.$i : '首页';
			trbasic($pvar.'模板','',empty($cnode['cfgs'][$i]['tpl']) ? '未设置' : $cnode['cfgs'][$i]['tpl'].' &nbsp;- &nbsp;'.@$mtplsarr[$cnode['cfgs'][$i]['tpl']],'');
			trbasic($pvar.'虚拟静态URL','',empty($cnode['cfgs'][$i]['novu']) ? '按系统总设置' : '关闭虚拟静态','');
		}
		tabfooter('bcnodedetail');
		a_guide('cnodedetail');
	}else{
		$cnodenew['alias'] = trim(strip_tags($cnodenew['alias']));
		$cnodenew['appurl'] = trim($cnodenew['appurl']);
		$cnodenew['keeptid'] = empty($cnodenew['keeptid']) ? 0 : 1;
		$db->query("UPDATE {$tblprefix}o_cnodes SET alias='$cnodenew[alias]',appurl='$cnodenew[appurl]',tid='$cnodenew[tid]',keeptid='$cnodenew[keeptid]' WHERE cnid=$cnid");
		adminlog('详细类目节点');
		cls_CacheFile::Update('o_cnodes');
		cls_message::show('节点设置完成',axaction(6,$forward));
	}
}

function modify_cnconfig(&$cncfg,$coid = 0,$ccids = array(),$mode = 0){
	global $db,$tblprefix,$o_cnconfigs;
	if(empty($cncfg)) return false;
	$configs = $cncfg['configs'];
	if(($cfg = @$configs[$coid]) && (@$cfg['mode'] == -1)){
		$ids = empty($cfg['ids']) ? array() : explode(',',$cfg['ids']);
		$ids = !$mode ? $ccids : ($mode == 1 ? array_filter(array_merge($ids,$ccids)) : array_diff($ids,$ccids));
		$configs[$coid]['ids'] = !$ids ? '' : implode(',',$ids);
		$o_cnconfigs[$cncfg['cncid']]['configs'] = $configs;
		return true;
	}
	return false;
}

?>
