<?PHP
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('tpl') || cls_message::show('您没有当前项目的管理权限。');
foreach(array('mtpls','csstpls','jstpls',) as $k) $$k = cls_cache::Read($k);
$action = empty($action) ? 'csstplsedit' : $action;
$jsmode = empty($jsmode) ? 0 : 1;
$FileTypeTitle = $jsmode ? 'JS' : 'CSS';
$FileTypeExt = $jsmode ? 'js' : 'css';
$true_tpldir = cls_tpl::TemplateTypeDir(empty($jsmode) ? 'css' : 'js');
mmkdir($true_tpldir);
if($action == 'csstplsedit'){
	backnav('tpl','cssjs');
	if(!submitcheck('bcsstplsedit')){
		$cssdocs = glob(cls_tpl::TemplateTypeDir('css').'*.css');
		tabheader('CSS文件管理'."&nbsp;&nbsp;&nbsp;&nbsp;[<a href=\"?entry=$entry&action=fileadd\" onclick=\"return floatwin('open_csstplsedit',this)\">添加</a>]",'csstplsedit',"?entry=$entry&action=$action",'9');
		trcategory(array(array('css文件','txtL'),array('名称','txtL'),'删除','复制','内容'));
		foreach($cssdocs as $k => $v){
			$v = basename($v);
			echo "<tr class=\"txt\">".
				"<td class=\"txtL w150\">$v</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"csstplsnew[$v][cname]\" value=\"".mhtmlspecialchars(@$csstpls[$v]['cname'])."\"></td>\n".
				"<td class=\"txtC w40\"><a onclick=\"return deltip()\" href=\"?entry=$entry&action=filedel&filename=$v\">删除</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=filecopy&filename=$v\" onclick=\"return floatwin('open_csstplsedit',this)\">复制</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=filedetail&filename=$v\" onclick=\"return floatwin('open_csstplsedit',this)\">编辑</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bcsstplsedit','修改');

		$jsdocs = glob(cls_tpl::TemplateTypeDir('js').'*.js');
		tabheader('js文件管理'."&nbsp;&nbsp;&nbsp;&nbsp;[<a href=\"?entry=$entry&action=fileadd&jsmode=1\" onclick=\"return floatwin('open_csstplsedit',this)\">添加</a>]",'jstplsedit',"?entry=$entry&action=csstplsedit&jsmode=1",'9');
		trcategory(array('JS文件|L','名称|L','删除','复制','内容'));
		foreach($jsdocs as $k => $v){
			$v = basename($v);
			echo "<tr class=\"txt\">".
				"<td class=\"txtL w150\">$v</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"jstplsnew[$v][cname]\" value=\"".mhtmlspecialchars(@$jstpls[$v]['cname'])."\"></td>\n".
				"<td class=\"txtC w40\"><a onclick=\"return deltip()\" href=\"?entry=$entry&action=filedel&filename=$v&jsmode=1\">删除</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=filecopy&filename=$v&jsmode=1\" onclick=\"return floatwin('open_csstplsedit',this)\">复制</a></td>\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&action=filedetail&filename=$v&jsmode=1\" onclick=\"return floatwin('open_csstplsedit',this)\">编辑</a></td>\n".
				"</tr>\n";
		}
		tabfooter('bcsstplsedit','修改');
	}elseif(!$jsmode){
		if(!empty($csstplsnew)){
			foreach($csstplsnew as $k => $v){
				$csstpls[$k]['cname'] = stripslashes($v['cname']);
			}
		}
		cls_CacheFile::Save($csstpls,'csstpls','csstpls');
		adminlog('编辑CSS文件管理列表');
		cls_message::show('CSS文件修改完成',M_REFERER);
	}else{
		if(!empty($jstplsnew)){
			foreach($jstplsnew as $k => $v){
				$jstpls[$k]['cname'] = stripslashes($v['cname']);
			}
		}
		cls_CacheFile::Save($jstpls,'jstpls','jstpls');
		adminlog('编辑JS文件管理列表');
		cls_message::show('JS文件修改完成',M_REFERER);
	}
}elseif($action == 'fileadd'){
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.rawurlencode($forward);
	if(!submitcheck('bfileadd')){
		tabheader("添加{$FileTypeTitle}文件",'filecopy',"?entry=$entry&action=$action&jsmode=$jsmode$forwardstr");
		trbasic($FileTypeTitle.'文件另存为','filenamenew','','text',array('guide' => "文件名称只允许包含字母、数字、下划线(_)、点(.)等字符,并以{$FileTypeExt}为扩展名"));
		echo "<tr class=\"txt\"><td class=\"txtL\">文件内容</td>".
		"<td class=\"txtL\"><textarea class=\"textarea\" style=\"width:650px;height:400px\" name=\"contentnew\" id=\"contentnew\"></textarea></td></tr>";
		tabfooter('bfileadd');
		a_guide('csstpladd');
	}else{
		if($re = _08_FilesystemFile::CheckFileName($filenamenew,$FileTypeExt)) cls_message::show($re,M_REFERER);
		$filesnew = findfiles($true_tpldir);
		in_array($filenamenew,$filesnew) && cls_message::show('指定的文件名称重复',M_REFERER);
		if(!str2file(stripslashes($contentnew),$true_tpldir.$filenamenew)) cls_message::show('文件添加失败',M_REFERER);
		adminlog("添加{$FileTypeTitle}文件");
		cls_message::show("{$FileTypeTitle}文件添加完成",axaction(6,$forward));
	}

}elseif($action == 'filecopy'){
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.rawurlencode($forward);
	if($re = _08_FilesystemFile::CheckFileName($filename,$FileTypeExt)) cls_message::show($re);
	if(!is_file($true_tpldir.$filename)) cls_message::show('指定的源文件不存在');
	if(!submitcheck('bfilecopy')){
		tabheader("复制{$FileTypeTitle}文件",'filecopy',"?entry=$entry&action=$action&filename=$filename&jsmode=$jsmode$forwardstr");
		trbasic('源文件','',$filename,'');
		trbasic($FileTypeTitle.'文件另存为','filenamenew','','text',array('guide' => "文件名称只允许包含字母、数字、下划线(_)、点(.)等字符,并以{$FileTypeExt}为扩展名"));
		tabfooter('bfilecopy');
		a_guide('csstplcopy');
	}else{
		if($re = _08_FilesystemFile::CheckFileName($filenamenew,$FileTypeExt)) cls_message::show($re,M_REFERER);
		$filesnew = findfiles($true_tpldir);
		in_array($filenamenew,$filesnew) && cls_message::show('指定的文件名称重复',M_REFERER);

		if(!copy($true_tpldir.$filename,$true_tpldir.$filenamenew)) cls_message::show('文件复制失败',M_REFERER);
		adminlog("复制{$FileTypeTitle}文件");
		cls_message::show('文件复制完成',axaction(6,$forward));
	}
}elseif($action == 'filedetail'){
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.rawurlencode($forward);
	if($re = _08_FilesystemFile::CheckFileName($filename,$FileTypeExt)) cls_message::show($re);
	if(!submitcheck('bfiledetail')){
		$content = @file2str($true_tpldir.$filename);
		tabheader($FileTypeTitle.'文件编辑'.'&nbsp;-&nbsp;'.$filename,'filedetail',"?entry=$entry&action=$action&filename=$filename&jsmode=$jsmode$forwardstr");
		echo "<tr class=\"txt\"><td colspan=\"2\"><textarea class=\"textarea\" style=\"width:700px;height:400px\" name=\"contentnew\" id=\"contentnew\">".htmlspecialchars(str_replace("\t","    ",$content))."</textarea></td><tr>";
		tabfooter('bfiledetail');
	}else{
		@str2file(stripslashes($contentnew),$true_tpldir.$filename);
		adminlog('详细修改'.$FileTypeTitle.'文件');
		cls_message::show($FileTypeTitle.'文件修改完成',axaction(6,$forward));
	}
}elseif($action == 'filedel'){
	$forward = empty($forward) ? M_REFERER : $forward;
	$forwardstr = '&forward='.rawurlencode($forward);
	if($re = _08_FilesystemFile::CheckFileName($filename,$FileTypeExt)) cls_message::show($re,M_REFERER);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击：[<a href='?entry=$entry&action=$action&filename=$filename&jsmode=$jsmode&confirm=ok$forwardstr'>删除</a>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$message .= "放弃请点击：[<a href='?entry=$entry'>返回</a>]";
		cls_message::show($message);
	}
    $file = _08_FilesystemFile::getInstance();
	$file->delFile($true_tpldir.$filename);
	adminlog('删除'.$FileTypeTitle.'文件');
	cls_message::show($FileTypeTitle.'文件删除完成',$forward);
}