<?PHP
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('catalog')) cls_message::show($re);
foreach(array('channels','mtpls','rprojects','splitbls',) as $k) $$k = cls_cache::Read($k);
include_once M_ROOT."include/fields.fun.php";
if($action=='cotypesedit'){
	$cotypes = cls_cotype::InitialInfoArray();
	backnav('cata','cotype');
	echo "<title>类系管理</title>";
	if(!submitcheck('bcotypesedit')){
		tabheader("类系管理 &nbsp;".modpro(">><a href=\"?entry=$entry&action=cotypeadd\" onclick=\"return floatwin('open_cotypesedit',this)\">添加类系</a>&nbsp;"),'cotypesedit',"?entry=$entry&action=$action",'10');
		$ii = 0;
		foreach($cotypes as $k => $cotype){
			if(!($ii % 15)) trcategory(array('ID',array('类系名称','txtL'),array('简称','txtL'),array('备注','txtL'),'排序',array('数据表','txtL'),'自动','节点','多选','期限',modpro('删除'),modpro('模型'),'参数','字段','分类'));
			$ii ++;
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w35\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"15\" maxlength=\"15\" name=\"cotypesnew[$k][cname]\" value=\"$cotype[cname]\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"5\" maxlength=\"10\" name=\"cotypesnew[$k][sname]\" value=\"$cotype[sname]\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"40\" maxlength=\"40\" name=\"cotypesnew[$k][remark]\" value=\"$cotype[remark]\"></td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"cotypesnew[$k][vieworder]\" value=\"$cotype[vieworder]\"></td>\n".
				"<td class=\"txtL\">coclass$k</td>\n".
				"<td class=\"txtC w35\">".($cotype['self_reg'] ? 'Y' : '-')."</td>\n".
				"<td class=\"txtC w35\">".($cotype['sortable'] ? 'Y' : '-')."</td>\n".
				"<td class=\"txtC w35\">".($cotype['asmode'] ? $cotype['asmode'] : '-')."</td>\n".
				"<td class=\"txtC w35\">".($cotype['emode'] ? 'Y' : '-')."</td>\n".
				modpro("<td class=\"txtC w35\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=cotypesdelete&coid=$k\">删除</a></td>\n").
				modpro("<td class=\"txtC w35\"><a href=\"?entry=$entry&action=archivetbl&coid=$k\" onclick=\"return floatwin('open_cotypesedit',this)\">模型</a></td>\n").
				"<td class=\"txtC w35\"><a href=\"?entry=$entry&action=cotypedetail&coid=$k\" onclick=\"return floatwin('open_cotypesedit',this)\">设置</a></td>\n".
				"<td class=\"txtC w35\"><a href=\"?entry=$entry&action=ccfieldsedit&coid=$k\" onclick=\"return floatwin('open_cotypesedit',this)\">字段</a></td>\n".
				"<td class=\"txtC w35\"><a href=\"?entry=coclass&action=coclassedit&coid=$k\" onclick=\"return floatwin('open_cotypesedit',this)\">管理</a></td>\n".
				"</tr>";
		}
		tabfooter('bcotypesedit','修改');
		a_guide('cotypesedit');
	}else{
		if(!empty($cotypesnew)){
			foreach($cotypesnew as $k => $cotype) {
				$cotype['vieworder'] = max(0,intval($cotype['vieworder']));
				$cotype['cname'] = trim(strip_tags($cotype['cname']));
				$cotype['cname'] = $cotype['cname'] ? $cotype['cname'] : $cotypes[$k]['cname'];
				$cotype['sname'] = trim(strip_tags($cotype['sname']));
				$cotype['remark'] = trim(strip_tags($cotype['remark']));
				$db->query("UPDATE {$tblprefix}cotypes SET 
							cname='$cotype[cname]', 
							sname='$cotype[sname]', 
							remark='$cotype[remark]', 
							vieworder='$cotype[vieworder]'
							WHERE coid='$k'
							");
			}
			adminlog('编辑类系管理列表');
			cls_CacheFile::Update('cotypes');
			cls_message::show('类系编辑完成',"?entry=$entry&action=$action");
		}
	}
}elseif($action == 'cotypeadd'){
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	echo "<title>添加类系</title>";
	deep_allow($no_deepmode);
	if(!submitcheck('bcotypesadd')){
		tabheader('添加类系','cotypesadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('类系名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,0,30)));
		trbasic('是否自动归类','fmdata[self_reg]',0,'radio',array('guide' => '提交后不可变更。自动归类类系中的分类非手工设定，而是根据某些条件系统自定设定文档所属的分类。'));
		$stidsarr = array();foreach($splitbls as $k => $v) $stidsarr[$k] = "($k)$v[cname]";
		trbasic('应用到以下主表<br /><input class="checkbox" type="checkbox" name="chchkall" onclick="checkall(this.form,\'fmdata[stids]\',\'chchkall\')">全选','',makecheckbox('fmdata[stids][]',$stidsarr,array(),5),'');
		tabfooter('bcotypesadd','添加');
	}else{
		($fmdata['cname'] = trim(strip_tags($fmdata['cname']))) || cls_message::show('类系名称不完全',M_REFERER);
		$fmdata['stids'] = empty($fmdata['stids']) ? array() : array_filter($fmdata['stids']);
		$db->query("INSERT INTO {$tblprefix}cotypes SET coid = ".auto_insert_id('cotypes').",cname='$fmdata[cname]',self_reg='$fmdata[self_reg]'");
		if($coid = $db->insert_id()){
			if($fmdata['stids'] && !$fmdata['self_reg']){
				foreach($fmdata['stids'] as $stid){
					empty($splitbls[$stid]) || $db->query("ALTER TABLE {$tblprefix}archives$stid ADD ccid$coid smallint(6) unsigned NOT NULL default 0",'SILENT');
				}
			}
			$db->query("CREATE TABLE {$tblprefix}coclass$coid LIKE {$tblprefix}init_coclass");
			$db->query("ALTER TABLE {$tblprefix}coclass$coid COMMENT='$fmdata[cname](类系)表'");
			adminlog('添加类系');
			cls_CacheFile::Update('cotypes');
			cls_message::show('类系添加完成',axaction(36,"?entry=$entry&action=cotypedetail&coid=$coid"));
		}else cls_message::show('类系添加失败',axaction(2,"?entry=$entry&action=cotypesedit"));
	}
}elseif($action == 'archivetbl' && $coid){//只分析数据表上是否有该字段
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	!($cotype = cls_cotype::InitialOneInfo($coid)) && cls_message::show('请指定正确的类系');
//	if($cotype['self_reg']) cls_message::show('指定类系为自动类系。');
	echo "<title>类系应用到文档表</title>";
	if(!submitcheck('bsubmit')){
		tabheader($cotype['cname']."($coid) - 类系应用到主表",'cotypedetail',"?entry=$entry&action=$action&coid=$coid");
		trcategory(array('启用','ID',array('文档主表','txtL'),array('数据表','txtL'),array('文档模型','txtL')));
		foreach($splitbls as $k => $v){
			$channelstr = '';foreach($v['chids'] as $x) @$channels[$x]['cname'] && $channelstr .= $channels[$x]['cname']."($x),";
			$available = in_array($coid,$v['coids']) ? TRUE : FALSE;
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fmdata[$k][enabled]\" value=\"1\"".($available ? ' checked' : '')."></td>\n".
				"<td class=\"txtC w35\">$k</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtL\">archives$k</td>\n".
				"<td class=\"txtL\">".($channelstr ? $channelstr : '空')."</td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
	}else{
		foreach($splitbls as $k => $v){
			$available = in_array($coid,$v['coids']) ? TRUE : FALSE;
			$enabled = empty($fmdata[$k]['enabled']) ? FALSE : TRUE;
			if($enabled != $available){
				if($enabled){
					if(!$cotype['self_reg']){
						if($cotype['asmode']){
							// 如果保存的是[2,3,4]会保留第一个值, 如果保存的是[,2,3,4,]则变为0, 所以先执行如下UPDATE
							// UPDATE xtest_msg_ys SET t3=substring(t3,2) WHERE t3 LIKE ',%' (select *,substring(t3,2) as t3a from xtest_msg_ys WHERE t3 LIKE ',%')
							$db->query("UPDATE {$tblprefix}archives$k SET ccid$coid=SUBSTRING(ccid$coid,2) WHERE ccid$coid LIKE ',%'",'SILENT');
							$db->query("ALTER TABLE {$tblprefix}archives$k ADD ccid$coid varchar(255) NOT NULL default ''",'SILENT');
						}else{
							$db->query("ALTER TABLE {$tblprefix}archives$k ADD ccid$coid smallint(6) unsigned NOT NULL default 0",'SILENT');
							$cotype['emode'] && $db->query("ALTER TABLE {$tblprefix}archives$k ADD ccid{$coid}date int(10) unsigned NOT NULL default 0",'SILENT');
						}
					}
					$v['coids'][] = $coid;
				}else{
					if(!$cotype['self_reg']){
						$db-> query("ALTER TABLE {$tblprefix}archives$k DROP ccid$coid",'SILENT'); 
						$db-> query("ALTER TABLE {$tblprefix}archives$k DROP ccid{$coid}date",'SILENT'); 
					}
					$key = array_search($coid,$v['coids']);
					if($key !== FALSE) unset($v['coids'][$key]);
				}
			}
			@sort($v['coids']);
			$db->query("UPDATE {$tblprefix}splitbls SET coids='".(empty($v['coids']) ? '' : implode(',',$v['coids']))."' WHERE stid='$k'");
		}
		cls_CacheFile::Update('splitbls');
		adminlog($cotype['cname']."类系应用到主表");
		cls_message::show('类系设置完成。',"?entry=$entry&action=$action&coid=$coid");
	}
}elseif($action == 'cotypedetail' && $coid){
	!($cotype = cls_cotype::InitialOneInfo($coid)) && cls_message::show('请指定正确的类系');
	echo "<title>类系详情 - $cotype[cname]</title>";
	if(!submitcheck('bcotypedetail')){
		tabheader("设置类系 - $cotype[cname]",'cotypedetail',"?entry=$entry&action=$action&coid=$coid");
		$fields = cls_cache::Read('cnfields',$coid);
		$arr = array('' => '不设置','title' => '分类名称','dirname' => '分类标识',);
		foreach($fields as $k => $v) $v['datatype'] == 'text' && $arr[$k] = $v['cname'];
		trbasic('自动首字母来源字段','cotypenew[autoletter]',makeoption($arr,@$cotype['autoletter']),'select');
		$vmodearr = array('0' => '普通选择列表','1' => '单选按钮','2' => '多级联动','3' => '多级联动(ajax)',);
		trbasic('分类选择列表模式','',makeradio('cotypenew[vmode]',$vmodearr,empty($cotype['vmode']) ? 0 : $cotype['vmode']),'');
		if(modpro()){
			tabfooter();
			tabheader('高级选项');
			trbasic('节点成员类系','cotypenew[sortable]',$cotype['sortable'],'radio',array('guide'=>'请谨慎操作！取消节点类系将删除所有与本类系有关的类目节点。'));
			trbasic('管理界面树形分页显示', 'cotypenew[treestep]', empty($cotype['treestep']) ? '' : $cotype['treestep'], 'text', array('guide'=>'请输入每页行数，留空为不分页。当类目数量过多时，建议设为10-30间的整数。'));
			trbasic('最多添加几层分类','cotypenew[maxlv]',empty($cotype['maxlv']) ? '' : $cotype['maxlv'], 'text', array('guide'=>'留空或0表示不限层数，请输入整数。'));
			if(empty($cotype['self_reg'])){
				trbasic('是否必选类目','cotypenew[notblank]',$cotype['notblank'],'radio');
				$relatearr = array(0 => '单选',);
				for($i = 2;$i < 20;$i ++) $relatearr[$i] = "<={$i}个";	
				trbasic('分类的选择模式','',makeradio('cotypenew[asmode]',$relatearr,empty($cotype['asmode']) ? 0 : $cotype['asmode']),'',array('guide'=>'请谨慎操作！！多选会影响某些查询效率，单选与多选间切换将更新数据库的大量数据。<br>多选转为单选时，将只保留第一个原有选择，且不可恢复。'));
				$emodearr = array(0 => '不设置期限',1 => '设定期限(选添)',2 => '设定期限(必添)');
				trbasic('分类的期限设置模式','',makeradio('cotypenew[emode]',$emodearr,empty($cotype['emode']) ? 0 : $cotype['emode']),'',array('guide'=>'请谨慎操作！从支持期限到不支持会丢失原有分类的期限数据。'));
				trbasic('是否强制分类模型','cotypenew[chidsforce]',$cotype['chidsforce'],'radio',array('guide' => '强制模式下，分类的有效模型在本界面设置，否则在分类设置界面手动设置'));
				trbasic('分类的有效模型<br /><input class="checkbox" type="checkbox" name="chchkall" onclick="checkall(this.form,\'cotypenew[chids]\',\'chchkall\')">全选','',makecheckbox('cotypenew[chids][]',cls_channel::chidsarr(1),empty($cotype['chids']) ? array() : explode(',',$cotype['chids']),5),'',
				array('guide' => '强制模式下，后续新增分类自动设为当前设置，并更新到已有分类，分类设置时不出现该项。<br>非强制模式下，此设置只是作为分类手动设置界面的默认项'));
			}
		}	
		$fmcstr1 = '';//'<br><input class="checkbox" type="checkbox" name="fieldnew[fromcode]" value="1"'.(empty($field['fromcode']) ? '' : ' checked').'>来自代码返回数组';
		$fmcstr2 = '';//'<br> 如选 来自代码返回数组，请填写PHP代码，使用return array(数组内容);得到选择内容。<br>如使用扩展函数，请定义到'._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php';
		trbasic('分组内容设置'.$fmcstr1,'cotypenew[groups]',empty($cotype['groups']) ? '' : $cotype['groups'],'textarea',array('guide'=>"设置类系关联时使用，不设置则不分组；每行填写一个选项，格式：分组标识=分组显示标题。$fmcstr2"));
		tabfooter('bcotypedetail');
		a_guide('cotypedetail');
	}else{
		$cotypenew['autoletter'] = trim($cotypenew['autoletter']);
		$cotypenew['notblank'] = empty($cotypenew['notblank']) ? 0 : 1;
		$sqlstr = "notblank='$cotypenew[notblank]',autoletter='$cotypenew[autoletter]',vmode='$cotypenew[vmode]',groups='$cotypenew[groups]'";
		
		if(modpro()){
			if($cotypenew['sortable'] && !$cotype['sortable']){
				$db->query("ALTER TABLE {$tblprefix}cnodes ADD ccid$coid smallint(6) unsigned NOT NULL default '0'",'SILENT');
				$db->query("ALTER TABLE {$tblprefix}o_cnodes ADD ccid$coid smallint(6) unsigned NOT NULL default '0'",'SILENT');
			}elseif(!$cotypenew['sortable'] && $cotype['sortable']){
				$db->query("DELETE FROM {$tblprefix}cnodes WHERE ccid$coid<>0",'SILENT');
				$db->query("ALTER TABLE {$tblprefix}cnodes DROP ccid$coid",'SILENT');
				cls_CacheFile::Update('cnodes');
				$db->query("DELETE FROM {$tblprefix}o_cnodes WHERE ccid$coid<>0",'SILENT');
				$db->query("ALTER TABLE {$tblprefix}o_cnodes DROP ccid$coid",'SILENT');
				cls_CacheFile::Update('o_cnodes');
			}
			$sqlstr .= ",sortable='$cotypenew[sortable]'";	
				
			$cotypenew['treestep'] = empty($cotypenew['treestep']) ? 0 : max(10,intval($cotypenew['treestep']));
			$cotypenew['maxlv'] = empty($cotypenew['maxlv']) ? 0 : max(0,intval($cotypenew['maxlv']));
			$sqlstr .= ",treestep='$cotypenew[treestep]',maxlv='$cotypenew[maxlv]'";		

			
			$cotypenew['chidsforce'] = empty($cotypenew['chidsforce']) ? 0 : 1;
			$cotypenew['chids'] = empty($cotypenew['chids']) ? '' : implode(',',$cotypenew['chids']);
			if($cotypenew['chidsforce']){
				$db->query("UPDATE {$tblprefix}coclass$coid SET chids='$cotypenew[chids]'");
				cls_CacheFile::Update('coclasses',$coid);
			}
			$sqlstr .= ",chidsforce='$cotypenew[chidsforce]',chids='$cotypenew[chids]'";		
			
			$cotypenew['asmode'] = empty($cotypenew['asmode']) ? 0 : max(2,intval($cotypenew['asmode']));
			$cotypenew['emode'] = empty($cotypenew['emode']) ? 0 : max(0,intval($cotypenew['emode']));
			if(empty($cotype['self_reg'])){
				if(!cls_DbOther::AlterFieldSelectMode($cotypenew['asmode'],@$cotype['asmode'],'ccid'.$coid,'archives')) $cotypenew['asmode'] = @$cotype['asmode'];
				if($cotypenew['emode'] != @$cotype['emode']){
					if($cotypenew['emode']){
						cls_dbother::BatchAlterTable("ALTER TABLE {TABLE} ADD ccid{$coid}date int(10) unsigned NOT NULL default 0 AFTER ccid{$coid}");
					}else{
						cls_dbother::BatchAlterTable("ALTER TABLE {TABLE} DROP ccid{$coid}date");
					}
				}
			}
			$sqlstr .= ",asmode='$cotypenew[asmode]',emode='$cotypenew[emode]'";
		}
		$db->query("UPDATE {$tblprefix}cotypes SET $sqlstr WHERE coid='$coid'");
		adminlog('详细修改类系');
		cls_CacheFile::Update('cotypes');
		cls_message::show('类系设置完成',axaction(6,"?entry=$entry&action=cotypesedit"));
	}
}elseif($action == 'cotypesdelete' && $coid) {//删除类系，与节点的关系
	backnav('cata','cotype');
	modpro() || cls_message::show('请联系创始人开放二次开发模式');
	!($cotype = cls_cotype::InitialOneInfo($coid)) && cls_message::show('请指定正确的类系');
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=cotypesdelete&coid=$coid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry&action=cotypesedit>返回</a>";
		cls_message::show($message);
	}
	//删除与当前类系有关的类目字段及资料
	$cids = array();$rels = array('a' => 'fields','m' => 'mfields','f' => 'ffields','cu' => 'cufields','cn' => 'cnfields',);
	$query = $db->query("SELECT * FROM {$tblprefix}afields WHERE datatype='cacc' AND coid='$coid'");
	while($r = $db->fetch_array($query)){
		if($var = @$rels[$r['type']]){
			if(empty($cids[$var]) || !in_array($r['tpid'],$cids[$var])) $cids[$var][] = $r['tpid'];
		}
		cls_dbother::DropField($r['tbl'],$r['ename'],$r['datatype']);
	}
	$db->query("DELETE FROM {$tblprefix}afields WHERE datatype='cacc' AND coid='$coid'"); 
	if($cids){//更新不同资料的字段缓存
		foreach($cids as $k => $v){
			foreach($v as $id) cls_CacheFile::Update($k,$id);
		}
	}
	unset($cids,$rels);
	
	//删除本类系的分类字段记录
	cls_fieldconfig::DeleteOneSourceFields('cotype',$coid);
	
	//删除相关的节点
	$db->query("DELETE FROM {$tblprefix}cnodes WHERE ccid$coid<>0",'SILENT');
	$db->affected_rows && cls_CacheFile::Update('cnodes');
	$db->query("DELETE FROM {$tblprefix}o_cnodes WHERE ccid$coid<>0",'SILENT');
	$db->affected_rows && cls_CacheFile::Update('o_cnodes');
	$db->query("DELETE FROM {$tblprefix}mcnodes WHERE mcnvar='ccid$coid'",'SILENT');
	$db->affected_rows && cls_CacheFile::Update('mcnodes');
	
	//删除所有文档表上的类系字段
	$na = stidsarr(1);
	foreach($na as $k => $v){
		$db-> query("ALTER TABLE {$tblprefix}".atbl($k,1)." DROP ccid$coid",'SILENT'); 
		$db-> query("ALTER TABLE {$tblprefix}".atbl($k,1)." DROP ccid{$coid}date",'SILENT'); 
	}
	
	//更正类目关联
	$cnrels = cls_cache::Read('cnrels');
	foreach($cnrels as $k => $v){
		if($v['coid'] == $coid || $v['coid1'] == $coid){
			$db->query("DELETE FROM {$tblprefix}cnrels WHERE rid='$k'");
		}
	}
	cls_CacheFile::Update('cnrels');
	
	//修正跟主表的关联
	
	$db->query("DELETE FROM {$tblprefix}cotypes WHERE coid='$coid'",'SILENT');
	$db->query("DROP TABLE IF EXISTS {$tblprefix}coclass$coid",'SILENT');
	cls_CacheFile::Update('cotypes');
	cls_CacheFile::Del('coclasses',$coid);
	adminlog('删除类系');
	cls_message::show('类系删除完成',"?entry=$entry&action=cotypesedit");
}elseif($action == 'ccfieldsedit' && $coid){
	echo "<title>分类字段管理</title>";
	!($cotype = cls_cotype::InitialOneInfo($coid)) && cls_message::show('请指定正确的类系');
	$fields = cls_fieldconfig::InitialFieldArray('cotype',$coid);
	if(!submitcheck('bccfieldsedit')){
		tabheader("分类字段管理 - $cotype[cname]&nbsp;&nbsp;&nbsp;>><a href=\"?entry=$entry&action=fieldone&coid=$coid\" onclick=\"return floatwin('open_fielddetail',this)\">添加字段</a>",'ccfieldsedit',"?entry=$entry&action=$action&coid=$coid",'5');
		trcategory(array('启用',array('字段名称','txtL'),'排序',array('字段标识','txtL'),array('数据表','txtL'),'字段类型','删除','编辑'));
		foreach($fields as $k => $v) {
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '')."></td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
				"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars($k)."</td>\n".
				"<td class=\"txtL\">$v[tbl]</td>\n".
				"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
				"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=fieldone&coid=$coid&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a></td>\n".
				"</tr>";
		}
		tabfooter('bccfieldsedit');
		a_guide('ccfieldsedit');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			$deleteds = cls_fieldconfig::DeleteField('cotype',$coid,$delete);
			foreach($deleteds as $k){
				unset($fieldsnew[$k]);
			}
		}
		if(!empty($fieldsnew)){
			foreach($fieldsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = $v['cname'] ? $v['cname'] : $fields[$k]['cname'];
				$v['available'] = !empty($v['available']) ? 1 : 0;
				$v['vieworder'] = max(0,intval($v['vieworder']));
				cls_fieldconfig::ModifyOneConfig('cotype',$coid,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('cotype',$coid);
		
		adminlog('编辑类系信息字段');
		cls_message::show('字段修改完成',"?entry=$entry&action=ccfieldsedit&coid=$coid");
	}
}elseif($action == 'fieldone' && $coid){
	cls_FieldConfig::EditOne('cotype',@$coid,@$fieldname);

}