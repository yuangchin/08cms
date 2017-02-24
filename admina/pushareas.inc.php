<?php
/*
* 推送位管理
*
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('pusharea')) cls_message::show($re);
include_once M_ROOT."include/fields.fun.php";

$pushtypes = cls_pushtype::InitialInfoArray();
if(empty($pushtypes)) cls_message::show('请设置推送位分类',"?entry=pushtypes");
$ptid = isset($ptid) ? (int)$ptid : 0;

if(empty($action)){
	backnav('pushareas','pusharea');
	
	if(empty($pushtypes[$ptid])) $ptid = 0;
	$pushareas = cls_pusharea::InitialInfoArray($ptid);
	
	if(!submitcheck('bsubmit')){
		
		$area_arr = array();
		$area_arr[] = empty($ptid) ? "<b>-全部分类-</b>" : "<a href=\"?entry={$entry}\">-全部分类-</a>";
		foreach($pushtypes as $v){
			$area_arr[] = $ptid == $v['ptid'] ? "<b>{$v['title']}</b>" : "<a href=\"?entry={$entry}&ptid={$v['ptid']}\">{$v['title']}</a>";
		}
		echo tab_list($area_arr,9,0);
		
		$TitleStr = "推送位管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=pushareaadd&ptid={$ptid}\" onclick=\"return floatwin('open_pushareaedit',this)\">添加</a>";
		tabheader($TitleStr,'pusharea',"?entry=$entry&ptid=$ptid",'7');

		$CategoryArray = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",);
		if(!$ptid) $CategoryArray[] = '分类名称|L';
		$CategoryArray = array_merge($CategoryArray,array('推送位|L','PAID|L','内容表|L','内容来源|L','排序','启用','删除','复制','设置','字段'));
		trcategory($CategoryArray);
		
		$oldTypeID = 0;
		foreach($pushareas as $k => $v){
			echo "<tr class=\"txt\">\n";
			echo "<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$k]\" value=\"$k\"></td>\n";
			if(!$ptid){
				echo "<td class=\"txtL\">".($oldTypeID == $v['ptid'] ? '' : mhtmlspecialchars(@$pushtypes[$v['ptid']]['title']))."</td>\n";
			}
			echo "<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][cname]\" value=\"$v[cname]\"></td>\n";
			echo "<td class=\"txtL\">$k</td>\n";
			echo "<td class=\"txtL\">$k</td>\n";
			echo "<td class=\"txtL\">".cls_pusharea::SourceIDTitle($v['sourcetype'],$v['sourceid'])."</td>\n";
			echo "<td class=\"txtC w50\"><input type=\"text\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\" size=\"4\" maxlength=\"4\"></td>\n";
			echo "<td class=\"txtC w30\">".($v['available'] ? 'Y' : '-')."</td>\n";
			echo "<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=pushareadel&paid=$k&ptid=$ptid\">删除</a></td>\n";
			echo "<td class=\"txtC w30\"><a href=\"?entry=$entry&action=pushareacopy&paid=$k\" onclick=\"return floatwin('open_pushareaedit',this)\">复制</a></td>\n";
			echo "<td class=\"txtC w30\"><a href=\"?entry=$entry&action=pushareadetail&paid=$k\" onclick=\"return floatwin('open_pushareaedit',this)\">设置</a></td>\n";
			echo "<td class=\"txtC w30\"><a href=\"?entry=$entry&action=pushareafields&paid=$k\" onclick=\"return floatwin('open_pushareaedit',this)\">字段</a></td>\n";
			echo "</tr>";
			$oldTypeID = $v['ptid'];
		}
		
		tabfooter();

		tabheader('批量操作');
		$s_arr = array();
		$s_arr['available'] = '启用';
		$s_arr['unavailable'] = '不启用';
		$s_arr['deleteforce'] = '强制删除(含推送位及其推送记录)';
		if($s_arr){
			$soperatestr = '';$i = 1;
			foreach($s_arr as $k => $v){
				$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"".($k=='deleteforce'?' onclick="deltip()"':'').">$v &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$soperatestr,'',array('guide' => '批量强制删除请慎重操作，会删除指定推送位及其推送信息'));
		}
		// 已发现两例客户,全部推送位都属于第一个分类；所以这里用$ptid判断一下并作默认选项,可减少一点误操作
		$ptid && trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[ptid]\" value=\"1\">&nbsp;设置推送位分类",'arcptid',makeoption(cls_pushtype::ptidsarr(),$ptid),'select');
		tabfooter('bsubmit');
		a_guide('pushareaedit');
	}else{
		if(!empty($selectid)){
			if(!empty($arcdeal['deleteforce'])){
				foreach($selectid as $k){
					cls_pusharea::DeleteOne($k,true);
					unset($fmdata[$k]);
				}
			}else{
				$_ModifyParams = array();
				if(!empty($arcdeal['ptid'])){
					$_ModifyParams['ptid'] = $arcptid;
				}
				if(!empty($arcdeal['available'])){
					$_ModifyParams['available'] = 1;
				}elseif(!empty($arcdeal['unavailable'])){
					$_ModifyParams['available'] = 0;
				}
				if($_ModifyParams){
					foreach($selectid as $k){
						cls_pusharea::ModifyOneConfig($k,$_ModifyParams);
					}
				}
			}
		}
		
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				cls_pusharea::ModifyOneConfig($k,$v);
			}
		}
		adminlog('编辑推送位列表');
		cls_message::show('推送位编辑完成',"?entry=$entry&ptid=$ptid");
	}

}elseif($action == 'pushareaadd'){
	echo _08_HTML::Title("添加推送位");
	deep_allow($no_deepmode);
	if(!submitcheck('bsubmit')){
		
		$ptid = isset($ptid) ? (int)$ptid : 0;
		if(empty($pushtypes[$ptid])) $ptid = 0;
		
		tabheader('添加推送位',$action,"?entry=$entry&action=$action",2,0,1);
		trbasic('推送位名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,4,32)));
		
		$na = array(
			'validate'=>' offset="1"' . makesubmitstr('fmdata[paid]',1,'tagtype',0,30),
			'guide' => '规定格式：push_字符，只能包含"字母数字_"，系统将根据唯一标识创建推送内容数据表。',
		);
		trbasic('英文唯一标识','fmdata[paid]','push_***','text',$na);
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC('ajax=check_paid&paid=%1');
		echo _08_HTML::AjaxCheckInput('fmdata[paid]', $ajaxURL);

		trbasic('推送位分类','fmdata[ptid]',makeoption(cls_pushtype::ptidsarr(),$ptid),'select',array('guide'=>'在后台管理对推送位进行分类管理'));
		trbasic('推送内容来源','fmdata[_pushsource]',makeoption(cls_pusharea::SourceIDArray()),'select',array('guide'=>'此项设置后不可修改'));
		tabfooter();
		
		tabheader('推送分类设置');
		$OptionArray = array(0 => '手动添加',-1 => '栏目(0)') + cls_cotype::coidsarr(1,1);
		$guide = '对推送信息的分类进行配置，指定分类的选项来源，可为栏目，类系或手动添加分类项，此项设置后不可更改。';
		trbasic('推送分类1选项来自','fmdata[classoption1]',makeoption($OptionArray),'select',array('guide'=>$guide));
		trbasic('推送分类2选项来自','fmdata[classoption2]',makeoption($OptionArray),'select',array('guide'=>$guide));
		tabfooter('bsubmit','添加');
		
		a_guide('pushareadetail');
	}else{
		
		# 对内容来源的选择进行前期处理
		if(!($fmdata['_pushsource'] = trim(strip_tags($fmdata['_pushsource'])))) cls_message::show('请输入推送来源');
		list($fmdata['sourcetype'],$fmdata['sourceid']) = explode('_',$fmdata['_pushsource']);
		unset($fmdata['_pushsource']);
		
		# 新增推送位，关联内容表及其内容记录
		if($paid = cls_pusharea::ModifyOneConfig($fmdata['paid'],$fmdata,true)){
			$db->query("ALTER TABLE {$tblprefix}{$fmdata['paid']} COMMENT='{$fmdata['cname']}(推荐位)表'");
			adminlog('添加推送位-'.$fmdata['cname']);
			cls_message::show('推送位添加成功，请对此推送位进行详细设置。',"?entry=$entry&action=pushareadetail&paid=$paid");
		}else cls_message::show('推送位添加不成功。');
	}

}elseif($action == 'pushareacopy'){
	echo _08_HTML::Title("复制推送位");
	deep_allow($no_deepmode);
	if(!($pusharea = cls_pusharea::InitialOneInfo($paid))) cls_message::show('请指定正确的推送位');
	if(!submitcheck('bsubmit')){
		
		tabheader("复制推送位 - {$pusharea['cname']}",$action,"?entry=$entry&action=$action&paid=$paid",2,0,1);
		trbasic('推送位名称','fmdata[cname]',$pusharea['cname'].'(副本)','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,4,32)));
		
		$na = array(
			'validate'=>' offset="1"' . makesubmitstr('fmdata[paid]',1,'tagtype',0,30),
			'guide' => '规定格式：push_字符，只能包含"字母数字_"，系统将根据唯一标识创建推送内容数据表。',
		);
		trbasic('英文唯一标识','fmdata[paid]',$pusharea['paid'].'_cp','text',$na);
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC('ajax=check_paid&paid=%1');
		echo _08_HTML::AjaxCheckInput('fmdata[paid]', $ajaxURL);
		#echo _08_HTML::AjaxCheckInput('fmdata[paid]',"{$cms_abs}tools/ajax.php?action=check_paid&paid=%1");

		trbasic('推送位分类','fmdata[ptid]',makeoption(cls_pushtype::ptidsarr(),$pusharea['ptid']),'select',array('guide'=>'在后台管理对推送位进行分类管理'));
		tabfooter('bsubmit');
		
		a_guide('pushareadetail');
	}else{
		$newConfig = array();
		foreach(array('cname','ptid') as $k){
			$newConfig[$k] = @$fmdata[$k];
		}
		
		try {
			# 复制推送位，关联内容表及其内容记录
			$nowID = cls_pusharea::CopyOneConfig($paid,@$fmdata['paid'],$newConfig);
		} catch (Exception $e){
			cls_message::show('推送位复制失败：'.$e->getMessage());
		}
		adminlog('复制推送位');
		cls_message::show('推送位复制成功。',axaction(6,"?entry=$entry"));
	}

}elseif($action == 'pushareadetail'){
	if(!($pusharea = cls_pusharea::InitialOneInfo($paid))) cls_message::show('请指定正确的推送位');
	echo _08_HTML::Title("推送位-{$pusharea['cname']}");
	if(!submitcheck('bsubmit')){
		tabheader('推送位详情',$action,"?entry=$entry&action=$action&paid=$paid",2,0,1);
		trbasic('推送位名称','fmdata[cname]',$pusharea['cname'],'text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,4,32)));
		trbasic('推送位分类','fmdata[ptid]',makeoption(cls_pushtype::ptidsarr(),$pusharea['ptid']),'select',array('guide'=>'在后台管理对推送位进行分类管理'));
		setPermBar('新推送自动置顶', 'fmdata[autocheck]', @$pusharea['autocheck'], 'chk', $soext=array(0=>'不自动置顶', 1=>'全部自动置顶','check'=>1), '选择权限方案，则方案中会员新推送的信息自动排到首位，否则列在已排序信息的后面。');
        trbasic('最大排序值','fmdata[maxorderno]',$pusharea['maxorderno'],'text',array('validate'=>makesubmitstr('fmdata[maxorderno]',1,'int',1,2),'w'=>3,'guide' => '建议设为本推送位前台展示列表的信息数量，或稍大，此值将成为固位排序设置范围'));
		tabfooter();
		
		tabheader('高级设置 - 内容来源');
		trbasic('推送内容来源','',cls_pusharea::SourceIDTitle($pusharea['sourcetype'],$pusharea['sourceid']),'');
		if($pusharea['sourcetype'] == 'archives'){
			tr_cns('来自以下栏目<br>'.OneCheckBox('fmdata[smallson]','含子栏目',$pusharea['smallson']),'fmdata[smallids]',array('value'=>$pusharea['smallids'],'chid'=>$pusharea['sourceid'],'framein'=>1,'notip'=>1,'max'=>10));
		}
		if(in_array($pusharea['sourcetype'],array('archives','members','commus'))){
			trbasic('添加资料时自动推送','fmdata[autopush]',@$pusharea['autopush'],'radio',array('guide'=>'默认为否，选择是则添加资料时自动推送符合条件的资料。'));
			trbasic('禁止手动添加','fmdata[forbid_useradd]',@$pusharea['forbid_useradd'],'radio',array('guide'=>'默认为否，可与[自动推送]配合使用，选择是则没有手动添加入口。'));
		}
		if(in_array($pusharea['sourcetype'],array('archives','members',))){
			trbasic('需要使用模型表信息','fmdata[sourceadv]',$pusharea['sourceadv'],'radio',array('guide'=>'默认为否，只需要从主表中获取信息。'));
		}
		if(in_array($pusharea['sourcetype'],array('archives','members',))){ 
			trbasic('到期日期来源字段','fmdata[enddate_from]',makeoption(cls_pusharea::DateFieldArray($pusharea['sourcetype'],$pusharea['sourceid']),@$pusharea['enddate_from']),'select',array('guide'=>'默认为空，设置后[推送信息]或[同步来源]时从设置的字段获取资料。'));
		}
		$guide = '控制来源范围，以 {pre}x=4 AND {pre}y>5 的格式输入追加SQL';
		if(in_array($pusharea['sourcetype'],array('archives','members',))) $guide .= '，条件字段只限于主表';
		$guide .= "<br>可使用系统的全局变量：如：{timestamp} 代表变量 \$timestamp";
		$guide .= "<br>或通过 return 函数名(); 返回符合手动格式的SQL，函数请自定义到"._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php';
		
		$createurl = "<br>>><a href=\"?entry=liststr&action={$pusharea['sourcetype']}&pushmode=1&typeid={$pusharea['sourceid']}\" target=\"_blank\">生成字串</a>";
		trbasic("附加过滤SQL{$createurl}",'fmdata[sourcesql]',$pusharea['sourcesql'],'text',array('guide'=>$guide,'w'=>40));
		tabfooter();
		
		tabheader('高级设置 - 排序共享'.viewcheck(array('name' => 'viewdetail_1','value' =>0,'body' =>$actionid.'tbodyfilter_1',)).' &nbsp;显示详细');
		echo "<tbody id=\"{$actionid}tbodyfilter_1\" style=\"display:none\">";
		$fields = cls_PushArea::Field($paid);
		$na = array();foreach(array(1,2) as $k) if(!empty($fields["classid$k"])) $na[$k] = "分类$k-{$fields["classid$k"]['cname']}(classid$k)";
		trbasic('以分类划分排序空间','fmdata[orderspace]',makeoption(array(0 => '不划分空间') + $na + array(3 => '以上两种分类组合'),$pusharea['orderspace']),'select',array('guide' => '推送信息以单个分类或交叉组合分类作为排序空间进行排序。'));
		trbasic('共享分类设置','fmdata[copyspace]',makeoption(array(0 => '不设置共享分类') + $na,$pusharea['copyspace']),'select',array('guide' => '允许设置某推送信息共享到多个分类。'));
		echo "</tbody>";
		tabfooter();
		
		tabheader('高级设置 - 程序扩展'.viewcheck(array('name' => 'viewdetail_2','value' =>0,'body' =>$actionid.'tbodyfilter_2',)).' &nbsp;显示详细');
		echo "<tbody id=\"{$actionid}tbodyfilter_2\" style=\"display:none\">";
		trbasic("推送管理扩展脚本",'fmdata[script_admin]',$pusharea['script_admin'],'text',array('guide'=>'留空则使用系统内置的通用脚本pushs_com.php，位于admina/extend/','w'=>20));
		trbasic("推送详情扩展脚本",'fmdata[script_detail]',$pusharea['script_detail'],'text',array('guide'=>'留空则使用系统内置的通用脚本push_com.php，位于admina/extend/','w'=>20));
		trbasic("推送加载扩展脚本",'fmdata[script_load]',$pusharea['script_load'],'text',array('guide'=>"留空则使用系统内置的通用脚本push_load_{$pusharea['sourcetype']}.php，位于admina/extend/",'w'=>20));
		echo "</tbody>";
		tabfooter('bsubmit');
		a_guide('pushareadetail');
	}else{
		cls_pusharea::ModifyOneConfig($paid,$fmdata);
		adminlog('编辑推送位-'.$pusharea['cname']);
		cls_message::show('推送位编辑完成!',axaction(6,"?entry=$entry"));
	}
}elseif($action == 'pushareadel'){
	backnav('pushareas','pusharea');
	deep_allow($no_deepmode);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href='?entry=$entry&action=$action&paid=$paid&confirm=ok&ptid=$ptid'>删除</a><br>";
		$message .= "放弃请点击>><a href='?entry=$entry'>返回</a>";
		cls_message::show($message);
	}
	if($re = cls_pusharea::DeleteOne($paid)) cls_message::show($re);
	adminlog('删除推送位');
	cls_message::show('推送位删除完成',"?entry=$entry&ptid=$ptid");
}elseif($action == 'pushareafields' && $paid){
	if(!($pusharea = cls_pusharea::InitialOneInfo($paid))) cls_message::show('请指定正确的推送位');
	$fields = cls_fieldconfig::InitialFieldArray('pusharea',$paid);
	if(!submitcheck('bsubmit') && !submitcheck('brules')){
		tabheader($pusharea['cname']."-字段管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=fieldone&paid=$paid\" onclick=\"return floatwin('open_fielddetail',this)\">添加字段</a>",'pushareadetail',"?entry=$entry&action=$action&paid=$paid");
		trcategory(array('有效','字段名称|L','排序','字段标识|L','数据表|L','字段类型','删除','编辑'));
		foreach($fields as $k => $v){
		echo "<tr class=\"txt\">\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"fieldsnew[$k][available]\" value=\"1\"".($v['available'] ? ' checked' : '').(!empty($v['issystem']) ? ' disabled' : '')."></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fieldsnew[$k][cname]\" value=\"".mhtmlspecialchars($v['cname'])."\"></td>\n".
			"<td class=\"txtC w60\"><input type=\"text\" size=\"4\" name=\"fieldsnew[$k][vieworder]\" value=\"$v[vieworder]\"></td>\n".
			"<td class=\"txtL\">".mhtmlspecialchars($k)."</td>\n".
			"<td class=\"txtL\">".$v['tbl']."</td>\n".
			"<td class=\"txtC w100\">".cls_fieldconfig::datatype($v['datatype'])."</td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(empty($v['iscustom']) ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"")."></td>\n".
			"<td class=\"txtC w50\"><a href=\"?entry=$entry&action=fieldone&paid=$paid&fieldname=$k\" onclick=\"return floatwin('open_fielddetail',this)\">详情</a></td>\n".
			"</tr>";
		}
		tabfooter('bsubmit');
		
		tabheader('推送字段规则','pusharearules',"?entry=$entry&action=$action&paid=$paid");
		$sfields = array('' => '插入内容来源字段') + cls_pusharea::SourceFieldArray($pusharea['sourcetype'],$pusharea['sourceid']);
		$sfs = $pusharea['sourcefields'];
		$guide = "{字段}返回内容，可多个字段组合，或return 函数名('{字段1}','{字段2}');通过函数返回结果<br>函数请自定义到"._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php';
		foreach($fields as $k => $v){
			if(empty($v['available'])) continue;
			$str = "<br><input class=\"checkbox\" type=\"checkbox\" name=\"fmfields[$k][refresh]\" value=\"1\"".(empty($sfs[$k]['refresh']) ? '' : ' checked')."> 需要后续更新";
			if(in_array($pusharea['sourcetype'],array('archives')) && ($k == 'url')){
				$str .= " &nbsp;<input class=\"checkbox\" type=\"checkbox\" name=\"fmfields[$k][nodemode]\" value=\"1\"".(empty($sfs[$k]['nodemode']) ? '' : ' checked')."> 来自手机版";
			}
			trbasic("[{$v['cname']}] 的来源值","fmfields[$k][from]",@$sfs[$k]['from'],'text',array('addstr'=>$str,'guide' => $guide,'ops' => $sfields,'w' => 40));
		}
		tabfooter('brules');
	}elseif(submitcheck('bsubmit')){
		if(!empty($delete) && deep_allow($no_deepmode)){
			$deleteds = cls_fieldconfig::DeleteField('pusharea',$paid,$delete);
			foreach($deleteds as $k){
				unset($fieldsnew[$k]);
			}
		}
		if(!empty($fieldsnew)){
			foreach($fieldsnew as $k => $v){
				$v['cname'] = trim(strip_tags($v['cname']));
				$v['cname'] = !$v['cname'] ? $fields[$k]['cname'] : $v['cname'];
				$v['available'] = empty($v['available']) && !$fields[$k]['issystem'] ? 0 : 1;
				$v['vieworder'] = max(0,intval($v['vieworder']));
				cls_fieldconfig::ModifyOneConfig('pusharea',$paid,$v,$k);
			}
		}
		cls_fieldconfig::UpdateCache('pusharea',$paid);
		
		adminlog('编辑推送位'.$pusharea['cname'].'字段列表');
		cls_message::show('推送位字段编辑完成。',"?entry=$entry&action=$action&paid=$paid");
	}elseif(submitcheck('brules')){
		cls_pusharea::ModifyOneConfig($paid,array('sourcefields' => $fmfields));
		adminlog('编辑推送位-'.$pusharea['cname']);
		cls_message::show('推送位编辑完成!',M_REFERER);
	}
}elseif($action == 'fieldone'){
	cls_FieldConfig::EditOne('pusharea',@$paid,@$fieldname);
}elseif($action == 'repair'){
	backnav('pushareas','repair');
	$pushareas = cls_pusharea::InitialInfoArray(0);
	if(!submitcheck('bsubmit')){
		$TitleStr = "需要修复的推送表";
		tabheader($TitleStr,'pusharea',"?entry=$entry&action=$action",'7');

		$CategoryArray = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",);
		$CategoryArray = array_merge($CategoryArray,array('推送位|L','内容表|L','状况说明|L'));
		trcategory($CategoryArray);
		
		foreach($pushareas as $k => $v){
			if($CheckError = cls_PushArea::CheckTable($k)){
				$View['select'] = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$k]\" value=\"$k\">";
				$View['cname'] = mhtmlspecialchars(@$pushtypes[$v['ptid']]['title']).'|<b>'.mhtmlspecialchars($v['cname']).'</b>';
				$View['contenttable'] = $k;
				$View['state'] = $CheckError ? $CheckError : 'ok';
				
				echo "<tr class=\"txt\">\n";
				echo "<td class=\"txtC w30\">{$View['select']}</td>\n";
				echo "<td class=\"txtL w200\">{$View['cname']}</td>\n";
				echo "<td class=\"txtL w60\">{$View['contenttable']}</td>\n";
				echo "<td class=\"txtL\">{$View['state']}</td>\n";
				echo "</tr>";
			
			
			}
		}
		
		tabfooter('bsubmit','修复');
	}else{
		if(!empty($selectid)){
			foreach($selectid as $k){
				cls_PushArea::RepairTable($k);
			}
		}
		adminlog('修复推送位内容表');
		cls_message::show('推送位内容表修复完成!',M_REFERER);
	
	}
}