<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
foreach(array('catalogs','cotypes','grouptypes','mcnodes','mcntpls','mtpls',) as $k) $$k = cls_cache::Read($k);
$mcnvars = array('caid' => '栏目');
$mcnvars['mcnid'] = '自定义节点';
foreach($cotypes as $k => $v) !$v['self_reg'] && $mcnvars['ccid'.$k] = "[分类] {$v['cname']}";
foreach($grouptypes as $k => $v) !$v['issystem'] && $mcnvars['ugid'.$k] = "[会员组] {$v['cname']}";
empty($action) && $action = 'mcnodesedit';
if($action == 'cntpladd'){
	echo "<title>添加节点配置</title>";
	if(!submitcheck('bsubmit')){
		tabheader('添加节点配置','cntpladd',"?entry=$entry&action=$action",2,0,1);
		trbasic('节点配置名称','cntplnew[cname]','','text',array('validate'=>makesubmitstr('cntplnew[cname]',1,1,4,30)));
		tabfooter('bsubmit','添加');
		a_guide('mcntpladd');
	} else {
		if(!($cntplnew['cname'] = trim(strip_tags($cntplnew['cname'])))) cls_message::show('请输入标题！',M_REFERER);
		$tid = auto_insert_id('mcntpls');
		$mcntpls[$tid] = array('tid' => $tid,'cname' => $cntplnew['cname'],'addnum' =>0,'vieworder' => 0);
		cls_CacheFile::Save($mcntpls,'mcntpls','mcntpls');
		adminlog('添加节点配置');
		cls_message::show('节点配置添加完成',axaction(36,"?entry=$entry&action=cntpldetail&tid=$tid"));
	}
}elseif($action == 'cntpldetail' && $tid){
	if(!($cntpl = @$mcntpls[$tid])) cls_message::show('请选择节点配置');
	echo "<title>节点配置 - $cntpl[cname]</title>";
	if(!submitcheck('bsubmit')){
		tabheader("节点配置&nbsp;&nbsp;[$cntpl[cname]]",'cntpldetail',"?entry=$entry&action=$action&tid=$tid");
		$arr = array();for($i = 0;$i <= $mcn_max_addno;$i ++) $arr[$i] = $i;
		$addnum = empty($cntpl['addnum']) ? 0 : $cntpl['addnum'];
		trbasic('附加页数量','',makeradio('fmdata[addnum]',$arr,$addnum),'');
		tabfooter();
		
		$cfgs = @$cntpl['cfgs'];
		for($i = 0;$i <= $mcn_max_addno;$i ++){
			tabheader(($i ? '附加页'.$i : '节点首页').'设置'.viewcheck(array('name' =>'viewdetail','title' => '详细','value' => $i > $addnum ? 0 : 1,'body' =>$actionid.'tbodyfilter'.$i)));
			echo "<tbody id=\"{$actionid}tbodyfilter$i\" style=\"display:".($i > $addnum ? 'none' : '')."\">";
			trbasic('页面模板',"cfgsnew[$i][tpl]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('marchive'),empty($cfgs[$i]['tpl']) ? '' : $cfgs[$i]['tpl']),'select',array('guide' => !$i ? cls_mtpl::mtplGuide('marchive') : ''));
			trbasic('静态保存格式',"cfgsnew[$i][url]",empty($cfgs[$i]['url']) ? '' : $cfgs[$i]['url'],'text',array('guide'=>!$i ? '留空则按系统总设置，{$cndir}系统默认保存路径，{$page}分页页码，数字之间建议加上分隔符_或-连接。': '','w'=>50));
			trbasic('是否生成静态','',makeradio("cfgsnew[$i][static]",array(0 => '按系统总设置',1 => '保持动态'),empty($cfgs[$i]['static']) ? 0 : $cfgs[$i]['static']),'');
			trbasic('静态更新周期(分钟)',"cfgsnew[$i][period]",empty($cfgs[$i]['period']) ? '' : $cfgs[$i]['period'],'text',array('guide'=>'留空则按系统总设置','w'=>4));
			trbasic('虚拟静态URL','',makeradio("cfgsnew[$i][novu]",array(0 => '按系统总设置',1 => '关闭虚拟静态'),empty($cfgs[$i]['novu']) ? 0 : $cfgs[$i]['novu']),'');
			echo "</tbody>";
			if($i != $mcn_max_addno) tabfooter();
		}
		tabfooter('bsubmit');
		a_guide('mcntpldetail');
	}else{
		$cntpl['addnum'] = max(0,intval($fmdata['addnum']));
		
		foreach($cfgsnew as $k => $v){
			if($k > $cntpl['addnum']){
				unset($cfgsnew[$k]);
				continue;
			}else{
				foreach(array('tpl','url','static','period','novu') as $var){
					if(empty($v[$var])){
						unset($cfgsnew[$k][$var]);
					}
				}
			}
		}
		$cntpl['cfgs'] = empty($cfgsnew) ? array() : $cfgsnew;
		$mcntpls[$tid] = $cntpl;
		cls_CacheFile::Save($mcntpls,'mcntpls','mcntpls');
		adminlog('会员频道节点配置');
		cls_message::show('会员频道节点配置修改完成',axaction(6,"?entry=$entry&action=cntplsedit"));
	}

}elseif($action == 'cntplsedit'){
	backnav('mcnode','cntpls');
	if(!submitcheck('bsubmit')){
		tabheader("节点配置管理&nbsp; &nbsp; >><a href=\"?entry=$entry&action=cntpladd\" onclick=\"return floatwin('open_cntplsedit',this)\">添加</a>",'cntplsedit',"?entry=$entry&action=$action",'10');
		trcategory(array('ID',array('标题','txtL'),'附加页','排序','删除','详情'));
		foreach($mcntpls as $k => $v){
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"cntplsnew[$k][cname]\" value=\"$v[cname]\"></td>\n".
				"<td class=\"txtC w40\">$v[addnum]</td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"cntplsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=cntpldel&tid=$k\">删除</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=cntpldetail&tid=$k\" onclick=\"return floatwin('open_cntplsedit',this)\">详情</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bsubmit','修改');
		a_guide('mcntplsedit');
	}else{
		if(isset($cntplsnew)){
			foreach($cntplsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $cntpls[$k]['cname'];
				$v['vieworder'] = max(0,intval($v['vieworder']));
				foreach(array('cname','vieworder',) as $var) $mcntpls[$k][$var] = $v[$var];
			}
			cls_Array::_array_multisort($mcntpls,'vieworder',1);
			cls_CacheFile::Save($mcntpls,'mcntpls','mcntpls');
			adminlog('编辑节点配置');
		}
		cls_message::show('节点配置修改完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'cntpldel' && $tid){
	backnav('cnode','cntpls');
	if(!($cntpl = @$mcntpls[$tid])) cls_message::show('请选择节点配置');
	deep_allow($no_deepmode);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=cntpldel&tid=$tid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=cntplsedit>返回</a>";
		cls_message::show($message);
	}
	
	$db->query("UPDATE {$tblprefix}mcnodes SET tid='0' WHERE tid='$tid'");
	unset($mcntpls[$tid]);
	
	cls_CacheFile::Save($mcntpls,'mcntpls','mcntpls');
	cls_CacheFile::Update('mcnodes');
	
	adminlog('删除节点配置');
	cls_message::show('节点配置删除成功', "?entry=$entry&action=cntplsedit");
}elseif($action == 'mcnodesedit'){
	backnav('mcnode','mcnodesedit');
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$keyword = empty($keyword) ? '' : $keyword;
	$mcnvar = empty($mcnvar)? '' : $mcnvar;
	$tid = !isset($tid)? '-1' : max(-1, intval($tid));
	
	$wheresql = '';
	$fromsql = "FROM {$tblprefix}mcnodes";
	
	$mcnvar && $wheresql .= " AND mcnvar='$mcnvar'";
	$keyword && $wheresql .= " AND (ename ".sqlkw($keyword)." OR alias ".sqlkw($keyword).")";
	$tid != '-1' && $wheresql .= " AND tid='$tid'";
	$wheresql && $wheresql = 'WHERE '.substr($wheresql,5);
	
	$filterstr = '';
	foreach(array('mcnvar','keyword',) as $k) $filterstr .= "&$k=".urlencode($$k);
	foreach(array('tid',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	if(!submitcheck('bmcnodesedit')){
		echo form_str($actionid.'mcnodesedit',"?entry=$entry&action=$action&page=$page");
		tabheader_e();
		echo "<tr><td class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"mcnvar\">".makeoption(array('' => '全部类型') + $mcnvars,$mcnvar)."</select>&nbsp; ";
		$arr = array('-1' => '节点配置','0' => '尚未配置');foreach($mcntpls as $k => $v) $arr[$k] = $v['cname'];
		echo "<select name=\"tid\">".makeoption($arr,$tid)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();
		
		tabheader("会员节点列表&nbsp; &nbsp; <input class=\"checkbox\" type=\"checkbox\" name=\"select_all\" value=\"1\">全选所有页内容&nbsp;",'','',12);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('节点名称','txtL'),array('节点标识','txtL'),array('节点类型(关联ID)','txtL'),array('节点配置','txtL'),'附加页',array('查看','txtL'),);
		$cy_arr[] = '详情';
		trcategory($cy_arr);
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * $fromsql $wheresql ORDER BY cnid ASC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		while($cnode = $db->fetch_array($query)){
			$cnode = LoadMcnodeConfig($cnode);
			cls_url::view_mcnurl($cnode['ename'],$cnode);
			$aliasstr = $cnode['alias'];
			$ename0str = $cnode['ename'];
			$enamestr = $mcnvars[$cnode['mcnvar']]." ({$cnode['mcnid']})";
			$cnstplstr = empty($mcntpls[$cnode['tid']]['cname']) ? '-' : $mcntpls[$cnode['tid']]['cname'];
			$addnum = empty($cnode['addnum']) ? 0 : $cnode['addnum'];
			$lookstr = '';for($i = 0;$i <= @$cnode['addnum'];$i ++) $lookstr .= "<a href=\"".$cnode['mcnurl'.($i ? $i : '')]."\" target=\"_blank\">".($i ? '附'.$i : '首页')."</a>&nbsp; ";
			echo "<tr class=\"txt\"><td class=\"txtC\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$cnode[cnid]]\" value=\"$cnode[cnid]\"></td>\n";
			echo "<td class=\"txtL\">$aliasstr</td>\n";
			echo "<td class=\"txtL\">$ename0str</td>\n";
			echo "<td class=\"txtL\">$enamestr</td>\n";
			echo "<td class=\"txtL\">$cnstplstr</td>\n";
			echo "<td class=\"txtC\">$addnum</td>\n";
			echo "<td class=\"txtL\">$lookstr</td>\n";
			echo "<td class=\"txtC\"><a href=\"?entry=$entry&action=mcnodedetail&cnid=$cnode[cnid]\" onclick=\"return floatwin('open_cnodedetail',this)\">编辑</a></td></tr>\n";
		}
		tabfooter();
		$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
		echo multi($counts,$atpp,$page,"?entry=$entry&action=$action$filterstr");

		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除节点';
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
		$arr = array('0' => '不设置');
		foreach($mcntpls as $k => $v) $arr[$k] = $v['cname'];
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"cndeal[cntpl]\" value=\"1\">&nbsp;设置节点配置",'cncntpl',makeoption($arr,0),'select',array('guide'=>'节点配置包含节点数量，模板，静态保存格式等设置。'));
		$ptypearr = array();
		for($i = 0;$i <= $mcn_max_addno;$i ++) $ptypearr[$i] = $i ? '附'.$i : '首页';
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"cndeal[static]\" value=\"1\">&nbsp;主动生成静态",'',"<input class=\"checkbox\" type=\"checkbox\" name=\"mchkall\" onclick=\"checkall(this.form,'ptypes','mchkall')\">全选 &nbsp;".makecheckbox('ptypes[]',$ptypearr),'',array('guide'=>'仅静态开启时有效'));
		tabfooter('bmcnodesedit');
		a_guide('mcnodesedit');
	}else{
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
			
			$selectid = array();
			$npage = empty($npage) ? 1 : $npage;
			if(empty($pages)) $pages = @ceil($db->result_one("SELECT count(*) $fromsql $wheresql") / $atpp);
			if($npage <= $pages){
				$fromstr = empty($fromid) ? "" : "cnid>$fromid";
				$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
				$query = $db->query("SELECT cnid,ename $fromsql $nwheresql ORDER BY cnid ASC LIMIT 0,$atpp");
				while($item = $db->fetch_array($query)) $selectid[] = $item['cnid'];
			}
			if(empty($selectid)) cls_message::show('请选择节点',"?entry=$entry&action=$action&page=$page$filterstr");
		}

		if(!empty($cndeal['delete'])){
			$query = $db->query("SELECT * $fromsql WHERE cnid ".multi_str($selectid));
			while($r = $db->fetch_array($query)){
				$r = LoadMcnodeConfig($r);
				for($i = 0;$i <= @$r['addnum'];$i ++) m_unlink(cls_url::m_parseurl(cls_node::mcn_format($r['ename'],$i),array('addno' => $i)));
			}
			$db->query("DELETE $fromsql WHERE cnid ".multi_str($selectid), 'UNBUFFERED');
		}else{
			if(!empty($cndeal['cntpl'])){
				$cncntpl = empty($cncntpl) ? 0 : (empty($mcntpls[$cncntpl]) ? 0 : $cncntpl);
				$db->query("UPDATE {$tblprefix}mcnodes SET tid='$cncntpl' WHERE cnid ".multi_str($selectid));
			}
			if(!empty($cndeal['static']) && $ptypes){
				$query = $db->query("SELECT * FROM {$tblprefix}mcnodes WHERE cnid ".multi_str($selectid));
				while($r = $db->fetch_array($query)){
					foreach($ptypes as $k){
						cls_McnodePage::Create(array('cnstr' => $r['ename'],'addno' => $k,'inStatic' => true));
					}
				}
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
				$transtr .= "&bmcnodesedit=1";
				$transtr .= "&fromid=$fromid";
				cls_message::show('文件操作正在进行中...<br>共 '.$pages.' 页，正在处理第 '.$npage.' 页<br><br><a href=\"?entry=$entry&action=$action&page=$page$filterstr\">>>中止当前操作</a>',"?entry=$entry&action=$action&page=$page$filterstr$transtr$parastr&dealstr=$dealstr");
			}
		}
		cls_CacheFile::Update('mcnodes');
		adminlog('节点管理操作','节点列表管理操作');
		cls_message::show('节点操作完成',"?entry=$entry&action=$action&page=$page$filterstr");
	}
}elseif($action == 'mcnodeadd'){
	backnav('mcnode','mcnodeadd');
	$mcnvar = empty($mcnodenew['mcnvar']) ? '' : $mcnodenew['mcnvar'];
	if(!submitcheck('bmcnodeadd')){
		tabheader('添加会员节点','mcnodeadd',"?entry=$entry&action=$action&mcnvar=$mcnvar",2,0,1);
		if(empty($mcnvar)){
			trbasic('节点类型','mcnodenew[mcnvar]',makeoption($mcnvars),'select',array('guide' => '会员频道节点为会员频道页或列表页的载体，可绑定模板及设定其它页面规则。<br>节点类型示例：选择类型为\'地域\'分类时，可以生产以不同地域为特征的会员频道节点。'));
			tabfooter('baddpre','继续');
		}else{
			trbasic('节点类型','',$mcnvars[$mcnvar],'');
			trhidden('mcnodenew[mcnvar]',$mcnvar);
			$arr = array(0 => '暂不配置');
			foreach($mcntpls as $k => $v) $arr[$k] = $v['cname'];
			trbasic('选择节点配置','cntplnew',makeoption($arr,0),'select',array('guide'=>'节点配置决定了节点的模板及其它页面规则。'));
			if($mcnvar == 'mcnid'){
				trbasic('节点名称','mcnodenew[alias]','','text',array('validate'=>makesubmitstr('cntplnew[cname]',1,1,4,30)));
			}else{
				if($mcnvar == 'caid'){
					$arr = $catalogs;
					$tvar = 'title';
				}elseif(in_str('ccid',$mcnvar)){
					$arr = cls_cache::Read('coclasses',str_replace('ccid','',$mcnvar));
					$tvar = 'title';
				}elseif(in_str('ugid',$mcnvar)){
					$arr = cls_cache::Read('usergroups',str_replace('ugid','',$mcnvar));
					$tvar = 'cname';
				}
				$narr = array();
				foreach($arr as $k => $v) if(empty($mcnodes[$mcnvar.'='.$k])) $narr[$k] = $v[$tvar].(isset($v['level']) ? '('.$v['level'].')' : '');
				trbasic("选择成为节点<br><input class=\"checkbox\" type=\"checkbox\" name=\"chkallmcnids\" onclick=\"checkall(this.form,'mcnidsnew','chkallmcnids')\">全选",'',$narr ? makecheckbox('mcnidsnew[]',$narr,array(),5) : '本类型节点已全部生成，增加了分类项后再执行本操作','');
			}
			tabfooter('bmcnodeadd','添加');
		}
	}else{
		empty($mcnodenew) && $mcnodenew = array();
		$mcnodenew['ids'] = empty($mcnidsnew) ? array() : $mcnidsnew;
		$tid = $cntplnew;
		mcnodesfromcnc($mcnodenew,$tid);
		cls_CacheFile::Update('mcnodes');
		cls_message::show('会员节点添加成功！',axaction(6,"?entry=$entry&action=mcnodesedit"));
	}
}elseif($action == 'mcnodedetail' && $cnid){
	if(!$cnode = $db->fetch_one("SELECT * FROM {$tblprefix}mcnodes WHERE cnid='$cnid'")) cls_message::show('请指定正确的节点！');
	$cnode = LoadMcnodeConfig($cnode);
	foreach(array('tpls','urls','statics','periods',) as $var) ${$var.'arr'} = ${$var.'arr'} = empty($cnode[$var]) ? array() : explode(',',$cnode[$var]);
	if(!submitcheck('bmcnodedetail')){
		tabheader('节点管理','mcnodedetail',"?entry=$entry&action=$action&cnid=$cnid",2);
		trbasic('节点类型','',$mcnvars[$cnode['mcnvar']],'');
		trbasic('节点名称','mcnodenew[alias]',$cnode['alias']);
		trbasic('指定节点链接','mcnodenew[appurl]',$cnode['appurl'],'text',array('guide'=>'站外Url或站内Url均可','w'=>50));
		$arr = array('0' => '不设置');foreach($mcntpls as $k => $v) $arr[$k] = $v['cname'];
		trbasic('设置节点配置','mcnodenew[tid]',makeoption($arr,$cnode['tid']),'select',array('guide'=>'节点配置包含节点数量，模板，静态保存格式等设置。'));
		tabfooter();
		
		tabheader('节点相关配置 &nbsp;- &nbsp;'.(empty($mcntpls[$cnode['tid']]['cname']) ? '尚未设置' : "<a href=\"?entry=$entry&action=cntpldetail&tid=$cnode[tid]\" onclick=\"return floatwin('open_cntplsedit',this)\">>>".$mcntpls[$cnode['tid']]['cname']."</a>"));
		trbasic('附加页数量','',empty($cnode['addnum']) ? '0' : $cnode['addnum'],'');
		for($i = 0;$i <= @$cnode['addnum'];$i ++){
			$pvar = $i ? '附加页'.$i : '首页';
			$arr = cls_mtpl::mtplsarr('marchive');
			trbasic($pvar.'模板','',empty($cnode['cfgs'][$i]['tpl']) ? '未设置' : $cnode['cfgs'][$i]['tpl'].' &nbsp;- &nbsp;'.@$arr[$cnode['cfgs'][$i]['tpl']],'');
			trbasic($pvar.'静态保存格式','',empty($cnode['cfgs'][$i]['url']) ? '按系统配置' : $cnode['cfgs'][$i]['url'],'',array('guide'=>!$i ? '{$cndir}系统默认保存路径，{$page}分页页码。': ''));
			trbasic($pvar.'是否生成静态','',empty($cnode['cfgs'][$i]['static']) ? '按系统配置' : '保持动态','');
			trbasic($pvar.'静态更新周期','',empty($cnode['cfgs'][$i]['period']) ? '按系统配置' : $cnode['cfgs'][$i]['period'].'(分钟)','');
			trbasic($pvar.'虚拟静态URL','',empty($cnode['cfgs'][$i]['novu']) ? '按系统配置' : '关闭虚拟静态','');
		}
		tabfooter('bmcnodedetail');
	}else{
		if(!($mcnodenew['alias'] = trim(strip_tags($mcnodenew['alias'])))) $mcnodenew['alias'] = $mcnode['alias'];
		$mcnodenew['appurl'] = trim($mcnodenew['appurl']);
		$db->query("UPDATE {$tblprefix}mcnodes SET alias='$mcnodenew[alias]',appurl='$mcnodenew[appurl]',tid='$mcnodenew[tid]' WHERE cnid=$cnid");
		cls_CacheFile::Update('mcnodes');
		cls_message::show('会员节点编辑完成！',axaction(6,"?entry=$entry&action=mcnodesedit"));
	}
}
