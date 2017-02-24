<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('affix')) cls_message::show($re);
$domains = cls_cache::Read('domains');
if(empty($action)) $action = 'domainsedit';
if($action == 'domainsedit'){
	backnav('otherset','domain');
	if(!submitcheck('bdomainsedit')){
		tabheader('域名管理'."&nbsp; &nbsp; >><a href=\"?entry=$entry&action=domainadd\" onclick=\"return floatwin('open_domains',this)\">".'添加域名'.'</a>',$actionid.'arcsedit',"?entry=$entry&action=$action");
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"deltip(this,$no_deepmode,checkall,this.form, 'delete', 'chkall')\">删?",'系统路径|L','指向域名|L','是否正则','排序'));
		$query = $db->query("SELECT * FROM {$tblprefix}domains ORDER BY vieworder,id");
		while($item = $db->fetch_array($query)){
			$id = $item['id'];
			echo "<tr class=\"txt\">".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$id]\" value=\"$id\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"40\" name=\"domainsnew[$id][folder]\" value=\"$item[folder]\"></td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"40\" name=\"domainsnew[$id][domain]\" value=\"$item[domain]\"></td>\n".
			"<td class=\"txtC w60\"><input class=\"checkbox\" type=\"checkbox\" name=\"domainsnew[$id][isreg]\" value=\"1\" ".(empty($item['isreg']) ? '' : 'checked')."></td>\n".
			"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" name=\"domainsnew[$id][vieworder]\" value=\"$item[vieworder]\"></td>\n".
			"</tr>\n";
		}
		tabfooter('bdomainsedit');
		a_guide('domainsedit');
	}else{
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}domains WHERE id='$k'");
				unset($domainsnew[$k]);
			}
		}
		if(!empty($domainsnew)){
			foreach($domainsnew as $k => $v){
				$v['folder'] = trim(strip_tags($v['folder']));
				$v['domain'] = trim(strip_tags($v['domain']));
				$v['vieworder'] = max(0,intval($v['vieworder']));
				$v['isreg'] = empty($v['isreg']) ? 0 : 1;
				if(!$v['folder'] || !$v['domain']) continue;
				$db->query("UPDATE {$tblprefix}domains SET domain='$v[domain]',folder='$v[folder]',isreg='$v[isreg]',vieworder='$v[vieworder]' WHERE id='$k'");
			}
		}
		adminlog('编辑域名列表');
		cls_CacheFile::Update('domains'); 
		cls_message::show('域名编辑完成！', "?entry=$entry&action=$action");
	}

}elseif($action == 'domainadd'){
	if(!submitcheck('bdomainadd')){
		tabheader('添加域名','domainadd',"?entry=$entry&action=$action");
		trbasic('系统路径','domainnew[folder]','','text',array('w'=>50));
		trbasic('指向域名','domainnew[domain]','','text',array('w'=>50));
		trbasic('是否正则','domainnew[isreg]',0,'radio');
		tabfooter('bdomainadd');
		a_guide('domainsedit');
	}else{
		$domainnew['folder'] = trim(strip_tags($domainnew['folder']));
		$domainnew['domain'] = trim(strip_tags($domainnew['domain']));
		//if(!preg_match("/^(?:[A-Z0-9-]+\\.)?[A-Z0-9-]+\\.[A-Z]{2,4}$/i",$domainnew['domain'])) a|message('domainillegal',"?entry=$entry&action=domainsedit");
		//if(in_array($domainnew['domain'],array_keys($domains))) ame|ssage('domainrepeat',"?entry=$entry&action=domainsedit");
		if(!$domainnew['folder'] || !$domainnew['domain']) cls_message::show('资料不完全',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}domains SET 
					domain='$domainnew[domain]', 
					folder='$domainnew[folder]',
					isreg='$domainnew[isreg]'
					");
		adminlog('添加域名');
		cls_CacheFile::Update('domains');
		cls_message::show('域名添加成功！', axaction(6,"?entry=$entry&action=domainsedit"));
	}
}

?>