<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
if($action == 'mcatalogsedit'){
	backnav('mtconfigs','mcatalogs');
	$mcatalogs = cls_mcatalog::InitialInfoArray();
	if(!submitcheck('bsubmit')){
		$TitleStr = "空间栏目管理";
		$TitleStr .= " &nbsp; &nbsp;>><a href=\"?entry=$entry&action=mcatalogadd\" onclick=\"return floatwin('open_mtconfigsedit',this)\">添加空间栏目</a>";
		tabheader($TitleStr,'mcatalogsedit',"?entry=$entry&action=$action",6);
		trcategory(array('ID','空间栏目名称|L','静态目录(留空为动态)|L','分类限额','排序','删除','备注|L'));
		
		foreach($mcatalogs as $k => $v) {
			$_views = array();
			$_views['title'] = $v['title'];
			$_views['dirname'] = $v['dirname'];
			$_views['maxucid'] = (int)$v['maxucid'];
			$_views['vieworder'] = (int)$v['vieworder'];
			$_views['remark'] = $v['remark'];
			echo "<tr class=\"txt\">\n".
			"<td class=\"txtC w30\">$k</td>\n".
			"<td class=\"txtL w120\">".OneInputText("fmdata[$k][title]",$_views['title'],25)."</td>\n".
			"<td class=\"txtL w150\">".OneInputText("fmdata[$k][dirname]",$_views['dirname'],15)."</td>\n".
			"<td class=\"txtC w80\">".OneInputText("fmdata[$k][maxucid]",$_views['maxucid'],4)."</td>\n".
			"<td class=\"txtC w80\">".OneInputText("fmdata[$k][vieworder]",$_views['vieworder'],4)."</td>\n".
			"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
			"<td class=\"txtL\">".OneInputText("fmdata[$k][remark]",$_views['remark'],50)."</td>\n".
			"</tr>";
		}
		tabfooter('bsubmit');
		a_guide('mcatalogsedit');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k){
				if(!cls_mcatalog::DeleteOne($k)){
					unset($fmdata[$k]);
				}
			}
		}
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				try {
					cls_mcatalog::ModifyOneConfig($v,$k);
				} catch (Exception $e){
					continue;
				}
			}
		}
		adminlog('编辑空间栏目管理列表');
		cls_message::show('空间栏目修改完成', "?entry=$entry&action=$action");
	}

}elseif($action == 'mcatalogadd'){
	echo _08_HTML::Title('添加空间栏目');
	if(!submitcheck('bsubmit')){
		tabheader('添加空间栏目','mcatalogadd',"?entry=$entry&action=$action",2,1,1);
		trbasic('*空间栏目名称','fmdata[title]','','text',array('validate' => makesubmitstr('fmdata[title]',1,0,4,30)));
		trbasic('栏目静态目录','fmdata[dirname]','','text',array('validate' => makesubmitstr('fmdata[dirname]',0,0,2,30),'guide' => '留空则个人空间生成静态时此栏目保持动态'));
		trbasic('个人分类最大数量','fmdata[maxucid]',0,'text',array('w' => 2,'guide' => '会员在本栏目中允许添加个人分类的最大数量，0为不允许。'));
		trbasic('栏目备注','fmdata[remark]','','text',array('w'=>50));
		tabfooter('bsubmit');
		a_guide('mcatalogdetail');
	}else{
		try {
			cls_mcatalog::ModifyOneConfig($fmdata,0);
		} catch (Exception $e){
			cls_message::show('空间栏目添加失败：'.$e->getMessage());
		}
		adminlog('添加空间栏目');
		cls_message::show('空间栏目添加完成', axaction(6,"?entry=$entry&action=mcatalogsedit"));
	}

}