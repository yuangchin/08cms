<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('freeinfo')) cls_message::show($re);
foreach(array('currencys','grouptypes','mtpls','permissions','cotypes',) as $k) $$k = cls_cache::Read($k);
$fchidsarr = cls_fchannel::fchidsarr();
$fcatalogs = cls_fcatalog::InitialInfoArray();
$pfcaid = cls_fcatalog::InitID(@$pfcaid);//指定顶级分类内管理
$pfcaid_suffix = $pfcaid ? "&pfcaid=$pfcaid" : '';
if($action == 'fcatalogsedit'){
	backnav('fchannel','coclass');
	empty($fchidsarr) && cls_message::show('请定义附属信息模型');
	if(!submitcheck('bfcatalogsedit')){
		#积分清除计划
		cls_Currency::clearCurrency();
		# 建立大分类导航
	    $pfcatalogs = cls_fcatalog::InitialInfoArray('');
		if(empty($pfcatalogs[$pfcaid])) $pfcaid = '';
        $pfacatalogsarr = array();
        $pfcatalogsarr[] = !$pfcaid ? "<b>-顶级分类-</b>" : "<a href=\"?entry={$entry}&action={$action}\">-顶级分类-</a>";
        foreach($pfcatalogs as $v){
            $pfcatalogsarr[] = $pfcaid == $v['fcaid'] ? "<b>{$v['title']}</b>" : "<a href=\"?entry={$entry}&action={$action}&pfcaid={$v['fcaid']}\">{$v['title']}</a>";
        }
        echo tab_list($pfcatalogsarr,9,0);
		
		$TitleStr = "副件分类管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=fcatalogadd$pfcaid_suffix\" onclick=\"return floatwin('open_fcatalogdetail',this)\">添加分类</a>";
		tabheader($TitleStr,'fcatalogsedit',"?entry=$entry&action=$action$pfcaid_suffix",'7');
		$CategoryArray = array('序号',"<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",'唯一标识|L','分类名称|L','副件说明|L','排序','广告','模型','删除',);
		if(!$pfcaid) $CategoryArray[] = '子类';
		$CategoryArray[] = '详情';
		$CategoryArray[] = '复制';
		trcategory($CategoryArray);
		
        $nfcatalogs = cls_fcatalog::InitialInfoArray($pfcaid);
		$No = 0;
		foreach($nfcatalogs as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w30\">".++$No."</td>\n".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$k]\" value=\"$k\"></td>\n".
				"<td class=\"txtL\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][title]\" value=\"".mhtmlspecialchars($v['title'])."\" size=\"20\" maxlength=\"30\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][content]\" value=\"".mhtmlspecialchars($v['content'])."\" size=\"40\"></td>\n".
				"<td class=\"txtC w50\"><input type=\"text\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\" size=\"2\"></td>\n".
				"<td class=\"txtC w40\">".(empty($v['ftype']) ? '-' : 'Y')."</td>\n".
				"<td class=\"txtC w100\">".mhtmlspecialchars(cls_fchannel::Config($v['chid'],'cname'))."</td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=fcatalogdel&fcaid=$k$pfcaid_suffix\">删除</a></td>\n";
				if(!$pfcaid) echo "<td class=\"txtC w30\"><a href=\"?entry=$entry&action=fcatalogsedit&pfcaid=$k\">管理</a></td>\n";
			echo "<td class=\"txtC w30\"><a href=\"?entry=$entry&action=fcatalogdetail&fcaid=$k$pfcaid_suffix\" onclick=\"return floatwin('open_fcatalogdetail',this)\">设置</a></td>\n";
			echo "<td class=\"txtC w30\"><a href=\"?entry=$entry&action=fcatalogcopy&fcaid=$k$pfcaid_suffix\" onclick=\"return floatwin('open_fcatalogdetail',this)\">复制</a></td>\n";
			echo "</tr>";
		}
		tabfooter();

		tabheader('批量操作');
		$s_arr = array();
		$s_arr['deleteforce'] = '强制删除(含子类及所关联的副件)';
		if($s_arr){
			$soperatestr = '';$i = 1;
			foreach($s_arr as $k => $v){
				$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"".($k=='deleteforce'?' onclick="deltip()"':'').">$v &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$soperatestr,'',array('guide' => '请慎重操作，会删除所选分类、所含子分类、以上所有分类所包含的副件信息'));
		}
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[ftype]\" value=\"1\">&nbsp;设置类型",'',makeradio('arcftype',array('默认类型', '广告类型'),0),'',array('guide' => '设置分类为广告类型'));
		tabfooter('bfcatalogsedit');
		a_guide('fcatalogsedit');
	}else{
		if(!empty($selectid)){
			if(!empty($arcdeal['deleteforce'])){
				foreach($selectid as $k){
					cls_fcatalog::DeleteOne($k,1);
					unset($fmdata[$k]);
				}
			}elseif(!empty($arcdeal['ftype'])){
				cls_fcatalog::SetFtype(empty($arcftype) ? 0 : 1,$selectid);
			}
		}

		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				$v['title'] = $v['title'] ? $v['title'] : $fcatalogs[$k]['title'];
				$v['content'] = trim($v['content']);
				$v['vieworder'] = max(0,intval($v['vieworder']));
				
				cls_fcatalog::ModifyOneConfig($k,$v);
			}
		}

		adminlog('编辑副件分类管理列表');
		cls_message::show('分类编辑完成', "?entry=$entry&action=$action$pfcaid_suffix");
	}
}elseif($action =='fcatalogadd'){
	echo _08_HTML::Title('添加副件分类');
	if(!submitcheck('bsubmit')){
		tabheader('添加副件分类','fcatalogadd',"?entry=$entry&action=$action$pfcaid_suffix",2,0,1);
		trbasic('分类名称','fmdata[title]','','text',array('validate'=>makesubmitstr('fmdata[title]',1,0,4,30)));
		
		$na = array(
			'validate'=>' offset="1"' . makesubmitstr('fmdata[fcaid]',1,'tagtype',3,30),
			'guide' => '规定格式：头字符为字母，其它字符只能为"字母、数字、_"。',
		);
		trbasic('英文唯一标识','fmdata[fcaid]','','text',$na);
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC('ajax=check_fcaid&fcaid=%1');
		echo _08_HTML::AjaxCheckInput('fmdata[fcaid]', $ajaxURL);
		
		trbasic('副件模型','fmdata[chid]',makeoption($fchidsarr),'select');
		
        $arr = array(0 => '顶级分类');
        foreach($fcatalogs as $k => $v) $v['pid'] || $arr[$k] = $v['title'];
		trbasic('所属分类','fmdata[pid]',makeoption($arr,$pfcaid),'select');
		
		cls_fcatalog::fAreaCoType(); //关联地区 
		
		tabfooter('bsubmit');
	}else{
		$fcaid = cls_fcatalog::ModifyOneConfig($fmdata['fcaid'],$fmdata,true);
		if($fcaid){
			adminlog('添加副件分类');
			cls_message::show('副件分类添加成功，请对此分类进行详细设置。', "?entry=$entry&action=fcatalogdetail&fcaid=$fcaid$pfcaid_suffix");
		}else{
			cls_message::show('副件分类添加不成功。');
		}
	}
}elseif($action =='fcatalogcopy' && $fcaid){
	if(!($fcatalog = cls_fcatalog::InitialOneInfo($fcaid))) cls_message::show('请指定正确的副件分类。');
	echo _08_HTML::Title("复制副件分类-{$fcatalog['title']}");
	if(!submitcheck('bsubmit')){
		tabheader("复制副件分类 - {$fcatalog['title']}",'fcatalogadd',"?entry=$entry&action=$action&fcaid=$fcaid$pfcaid_suffix",2,0,1);
		trbasic('分类名称','fmdata[title]',$fcatalog['title'].'(副本)','text',array('validate'=>makesubmitstr('fmdata[title]',1,0,4,30)));
		
		$na = array(
			'validate'=>' offset="1"' . makesubmitstr('fmdata[fcaid]',1,'tagtype',0,30),
			'guide' => '规定格式：头字符为字母，其它字符只能为"字母、数字、_"。',
		);
		trbasic('英文唯一标识','fmdata[fcaid]',$fcatalog['fcaid'].'_cp','text',$na);
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC('ajax=check_fcaid&fcaid=%1');
		echo _08_HTML::AjaxCheckInput('fmdata[fcaid]', $ajaxURL);
		
        $arr = array(0 => '顶级分类');
        foreach($fcatalogs as $k => $v) $v['pid'] || $arr[$k] = $v['title'];
		trbasic('所属分类','fmdata[pid]',makeoption($arr,$fcatalog['pid']),'select');
		
        cls_fcatalog::fAreaCoType(@$fcatalog['farea']); //关联地区 
		
		tabfooter('bsubmit');
	}else{
		foreach(array('title','fcaid','pid') as $k){
			$fcatalog[$k] = @$fmdata[$k];
		}
		$nowID = cls_fcatalog::ModifyOneConfig($fmdata['fcaid'],$fcatalog,true);
		if($nowID){
			$fcfg = cls_fcatalog::InitialOneInfo($fcaid); 
			if(!empty($fcfg['ftype'])){
				try{
					_08_Advertising::AdvTagCopy($fcaid,$nowID); # 复制广告位模板标签
				}catch(Exception $e){
					cls_message::show($e->getMessage());
				}
			}
			adminlog('复制副件分类');
			cls_message::show('副件分类复制成功。',axaction(6,"?entry=$entry&action=fcatalogsedit$pfcaid_suffix"));
		}else{
			cls_message::show('副件分类复制不成功。');
		}
	}
}elseif($action =='fcatalogdetail' && $fcaid){
	if(!($fcatalog = cls_fcatalog::InitialOneInfo($fcaid))) cls_message::show('请指定正确的副件分类。');
	echo _08_HTML::Title("副件分类详情-{$fcatalog['title']}");
	if(!submitcheck('bfcatalogdetail')){
		tabheader("副件分类设置&nbsp;&nbsp;[$fcatalog[title]]",'fcatalogdetail',"?entry=$entry&action=$action&fcaid=$fcaid$pfcaid_suffix",2,0,1);
		trbasic('英文唯一标识','',$fcatalog['fcaid'],'');
		trbasic('副件模型','',cls_fchannel::Config($fcatalog['chid'],'cname'),'');
		if(!cls_fcatalog::InitialInfoArray($fcaid)){
			$arr = array(0 => '顶级分类');
			foreach($fcatalogs as $k => $v){
				if(empty($v['pid']) && ($k != $fcaid)){
					$arr[$k] = $v['title'];
				}
			}
			trbasic('所属分类','fmdata[pid]',makeoption($arr,$fcatalog['pid']),'select');
		}
		
        cls_fcatalog::fAreaCoType(@$fcatalog['farea']); //关联地区
		
		setPermBar('发布权限设置', 'fmdata[apmid]', @$fcatalog['apmid'], 'fadd', 'open', '');
        trbasic('副件类型','fmdata[ftype]',makeoption(array('默认类型', '广告类型'),$fcatalog['ftype']),'select');
		trbasic('信息自动审核','fmdata[autocheck]',$fcatalog['autocheck'],'radio');
		trbasic('不设置时间限制','fmdata[nodurat]',$fcatalog['nodurat'],'radio');
		trbasic('静态保存格式','fmdata[customurl]',$fcatalog['customurl'],'text',array('guide'=>'留空为系统默认{$infodir}/a-{$aid}-{$page}.html，{$infodir}副件总目录，{$y}年 {$m}月 {$d}日 {$aid}副件id {$page}分页页码 数字之间建议用分隔符_或-连接。','w'=>50));
		trbasic('副件说明','fmdata[content]',$fcatalog['content'],'text',array('guide'=>'如注释所在的模板,标签,演示地址等信息','w'=>50));
		tabfooter('bfcatalogdetail');
		a_guide('fcatalogdetail');
	}else{
		cls_fcatalog::ModifyOneConfig($fcaid,$fmdata,false);
		adminlog('详细修改副件信息');
		cls_message::show('分类设置完成', axaction(6,"?entry=$entry&action=fcatalogsedit$pfcaid_suffix"));
	}

}elseif($action == 'fcatalogdel' && $fcaid) {	
	backnav('fchannel','coclass');
	deep_allow($no_deepmode);
	$reurl = "?entry=$entry&action=fcatalogsedit$pfcaid_suffix";
	if(empty($confirm)){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href='?entry=$entry&action=$action&fcaid=$fcaid$pfcaid_suffix&confirm=ok'>删除</a><br>";
		$message .= "放弃请点击>><a href='$reurl'>返回</a>";
		cls_message::show($message);
	}
	if($re = cls_fcatalog::DeleteOne($fcaid)) cls_message::show($re, $reurl);
	adminlog('删除副件分类');
	cls_message::show('分类删除完成', $reurl);
}else cls_message::show('错误的文件参数');

