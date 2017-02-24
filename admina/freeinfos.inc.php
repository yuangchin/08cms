<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($action == 'freeinfosedit'){
	if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
	backnav('bindtpl','freeinfos');
	$freeinfos = cls_FreeInfo::InitialInfoArray();
	if(!submitcheck('bsubmit')){
		$mtplsarr = cls_mtpl::mtplsarr('other');
		
		$TitleStr = "独立页面管理";
		$TitleStr .= " &nbsp; &nbsp;>><a href=\"?entry=$entry&action=freeinfoadd\" onclick=\"return floatwin('open_freeinfo',this)\">添加</a>";
		$TitleStr .= " &nbsp; &nbsp;".cls_mtpl::mtplGuide('other',true);
		tabheader($TitleStr,'freeinfosedit',"?entry=$entry&action=$action",'5');
		
		$CategoryArray = array();
		$CategoryArray[] = 'ID';
		$CategoryArray[] = '独立页面名称|L';
		$CategoryArray[] = '页面模板|L';
		$CategoryArray[] = "允许静态<input class=\"checkbox\" type=\"checkbox\" name=\"chkallx\" onclick=\"checkall(this.form,'fmdata','chkallx')\">";
		$CategoryArray[] = '静态保存格式|L';
		$CategoryArray[] = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"deltip(this,0,checkall,this.form, 'delete', 'chkall')\">删?";
		$CategoryArray[] = '预览';
		$CategoryArray[] = '静态';
		trcategory($CategoryArray);
		
		foreach($freeinfos as $k => $v){
			$_views = array();
			$_views['fid'] = $k;
			$_views['cname'] = $v['cname'];
			$_views['tplname'] = $v['tplname'];
			$_views['customurl'] = $v['customurl'];
			$_views['canstatic'] = empty($v['canstatic']) ? 0 : 1;
			if(empty($v['arcurl'])){
				$_views['arcurl'] = cls_url::view_url("info.php?fid=$k");
				$_views['static'] = empty($v['canstatic']) ? '-' : "<a href=\"?entry=$entry&action=fstatic&fid=$k\">生成</a>";
			}else{
				$_views['arcurl'] = cls_url::view_url($v['arcurl']);
				$_views['static'] = "<a href=\"?entry=$entry&action=unfstatic&fid=$k\"><b>取消</b></a>";
				if(!empty($v['canstatic'])){
					$_views['static'] .= " | <a href=\"?entry=$entry&action=fstatic&fid=$k\">更新</a>";
				}
			}
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">{$_views['fid']}</td>\n".
				"<td class=\"txtL w120\">".OneInputText("fmdata[$k][cname]",$_views['cname'])."</td>\n".
				"<td class=\"txtL w120\">".makeselect("fmdata[$k][tplname]",makeoption(isset($mtplsarr[$v['tplname']]) ? $mtplsarr : ($mtplsarr + array($v['tplname'] => '未知模板')),$_views['tplname'],'不设置'))."</td>\n".
				"<td class=\"txtC w80\">".OneCheckBox("fmdata[$k][canstatic]",'',$_views['canstatic'])."</td>\n".
				"<td class=\"txtL w150\">".OneInputText("fmdata[$k][customurl]",$_views['customurl'],50)."</td>\n".
				"<td class=\"txtC w50\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip()\"></td>\n".
				"<td class=\"txtC w35\"><a href=\"{$_views['arcurl']}\" target=\"_blank\">预览</a></td>\n".
				"<td class=\"txtC w70\">{$_views['static']}</td></tr>\n";
		}
		tabfooter('bsubmit');
		a_guide('freeinfosedit');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				if(!cls_FreeInfo::DeleteOne($k)){
					unset($fmdata[$k]);
				}
			}
		}
		
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				try{
					cls_FreeInfo::ModifyOneConfig($v,$k);
				}catch(Exception $e){
					continue;
				}
			}
		}
		
		cls_message::show('页面修改完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'freeinfoadd'){
	echo _08_HTML::Title('添加独立页面');
	if(!submitcheck('bsubmit')){
		$mtplsarr = cls_mtpl::mtplsarr('other');
		tabheader('添加独立页面','freeinfoadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('独立页面名称','fmdata[cname]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,4,30)));
		trbasic('独立页面模板','fmdata[tplname]',makeoption(array('' => '不设置') + $mtplsarr),'select',array('guide' => cls_mtpl::mtplGuide('other')));
		tabfooter('bsubmit','添加');
	}else{
		try {
			cls_FreeInfo::ModifyOneConfig($fmdata,0);
		} catch (Exception $e){
			cls_message::show($e->getMessage());
		}
		cls_message::show('独立页添加成功。',axaction(6,"?entry=$entry&action=freeinfosedit"));
	}

}elseif($action == 'static'){
	backnav('static','freeinfos');
	if($re = $curuser->NoBackFunc('static')) cls_message::show($re);
	$freeinfos = cls_FreeInfo::InitialInfoArray();
	$mtplsarr = cls_mtpl::mtplsarr('other');
	if(!submitcheck('bsubmit')){
		
		$TitleStr = "独立页面静态";
		$TitleStr .= " &nbsp; &nbsp;>><a href=\"?entry=$entry&action=freeinfosedit&isframe=1\" target=\"_08cms_mtpl\">页面配置</a>";
		tabheader($TitleStr,'freeinfosedit',"?entry=$entry&action=$action",'5');
		$CategoryArray = array();
		$CategoryArray[] = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">";
		$CategoryArray[] = 'ID';
		$CategoryArray[] = '独立页面名称|L';
		$CategoryArray[] = '页面模板|L';
		$CategoryArray[] = '静态保存格式|L';
		$CategoryArray[] = '预览';
		$CategoryArray[] = '静态';
		trcategory($CategoryArray);
		
		foreach($freeinfos as $k => $v){
			$_views = array();
			$_views['fid'] = $k;
			$_views['cname'] = "<b>{$v['cname']}</b>";
			$_views['tplname'] = $v['tplname'];
			$_views['customurl'] = empty($v['customurl']) ? cls_FreeInfo::DefaultFormat() : $v['customurl'];
			$_views['selectfix'] = "name='selectid[$k]'"; 
			if(empty($v['arcurl'])){
				$_views['arcurl'] = cls_url::view_url("info.php?fid=$k");
				if(empty($v['canstatic'])){
					$_views['static'] = '-';
					$_views['selectfix'] = "disabled=\"disabled\"";
				}else{
					$_views['static'] = "<a href=\"?entry=$entry&action=fstatic&fid=$k\">生成</a>";
				}
			}else{
				$_views['arcurl'] = cls_url::view_url($v['arcurl']);
				$_views['static'] = "<a href=\"?entry=$entry&action=unfstatic&fid=$k\"><b>取消</b></a>";
				if(!empty($v['canstatic'])){
					$_views['static'] .= " | <a href=\"?entry=$entry&action=fstatic&fid=$k\">更新</a>";
				}
			}
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\"><input class=\"checkbox\" type=\"checkbox\" {$_views['selectfix']} value=\"$k\">".
				"<td class=\"txtC w30\">{$_views['fid']}</td>\n".
				"<td class=\"txtL\">{$_views['cname']}</td>\n".
				"<td class=\"txtL\">{$_views['tplname']}</td>\n".
				"<td class=\"txtL\">{$_views['customurl']}</td>\n".
				"<td class=\"txtC w35\"><a href=\"{$_views['arcurl']}\" target=\"_blank\">预览</a></td>\n".
				"<td class=\"txtC w70\">{$_views['static']}</td></tr>\n";
		}
		tabfooter();
		tabheader('批量操作');
		$s_arr = array();
		$s_arr['create'] = '生成/更新静态';  
		$s_arr['cancel'] = '取消静态';
		if($s_arr){
			$soperatestr = '';
			foreach($s_arr as $k => $v){
				$soperatestr .= "<label><input class=\"radio\" type=\"radio\" name=\"fdeal\" value=\"$k\">$v</label> &nbsp;";
			}
			trbasic('选择操作项目','',$soperatestr,'');
		}
		tabfooter('bsubmit');
	}else{
		if(empty($fdeal)) cls_message::show('请选择操作项目',"?entry=$entry&action=$action");
		if(empty($selectid)) cls_message::show('请选择独立页',"?entry=$entry&action=$action");
		foreach($selectid as $k){
			if($fdeal == 'create'){
				cls_FreeInfo::ToStatic($k);
			}else{
				cls_FreeInfo::UnStatic($k);
			}
		}
		cls_message::show('操作完成',"?entry=$entry&action=$action");
	}
}elseif($action == 'fstatic' && $fid){
	if($Message = cls_FreeInfo::ToStatic($fid)){
		cls_message::show($Message,M_REFERER);
	}else{
		cls_message::show('页面静态完成',M_REFERER);
	}
}elseif($action == 'unfstatic' && $fid){
	if($Message = cls_FreeInfo::UnStatic($fid)){
		cls_message::show($Message,M_REFERER);
	}else{
		cls_message::show('页面静态完成',M_REFERER);
	}
}