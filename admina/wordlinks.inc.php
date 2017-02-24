<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('other')) cls_message::show($re);
$page = empty($page) ? 1 : max(1, intval($page));
if(!submitcheck('bwordlinksadd') && !submitcheck('bwordlinksedit') && !submitcheck('bhotkeywords')){
	tabheader('从系统导入热门关键词','hotkeywords',"?entry=wordlinks");
	trbasic('导入关键词数量','hotimport[amount]',100);
	trbasic('被引用次数需要大于','hotimport[vpcs]',10);
	tabfooter('bhotkeywords','导入');

	tabheader('手动添加被关联词','wordlinksadd',"?entry=wordlinks&page=$page");
	trbasic('被关联词','wordlinkadd[sword]');
	trbasic('关联链接','wordlinkadd[url]');
	tabfooter('bwordlinksadd','添加');

	$pagetmp = $page;
	do{
		$query = $db->query("SELECT * FROM {$tblprefix}wordlinks ORDER BY wlid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);
	$itemstr = '';
	while($item = $db->fetch_array($query)){
		$itemid = $item['wlid'];
		$item['rword'] = '<a href="'.cls_url::view_url($item['url']).'" target="_blank">'.'查看'.'</a>';
		$itemstr .= "<tr class=\"txt\">".
			"<td class=\"txtC\">".mhtmlspecialchars($item['sword'])."</td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"60\" name=\"wordlinksnew[$itemid][url]\" value=\"$item[url]\"></td>\n".
			"<td class=\"txtC w70\">$item[pcs]</td>\n".
			"<td class=\"txtC w45\"><input class=\"checkbox\" type=\"checkbox\" name=\"wordlinksnew[$itemid][available]\" value=\"1\"".($item['available'] ? " checked" : "")."></td>\n".
			"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$itemid]\" value=\"$itemid\" onclick=\"deltip()\"></td>\n".
			"<td class=\"txtC w40\">$item[rword]</td></tr>\n";
	}
	$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}wordlinks");
	$multi = multi($counts, $atpp, $page, "?entry=wordlinks");
	$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}wordlinks WHERE available=1");
	tabheader('被关联词管理&nbsp;:&nbsp;(启用&nbsp;: '.$counts.')','wordlinksedit',"?entry=wordlinks&page=$page",6);
	trcategory(array('被关联词','关联链接','引用次数','启用'."<input class=\"checkbox\" type=\"checkbox\" name=\"chkall1\" onclick=\"checkall(this.form, 'wordlinksnew','chkall1')\">","<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"deltip(this,0,checkall,this.form,'delete', 'chkall')\">删?",'查看'));
	echo $itemstr;
	tabfooter();
	echo $multi;
	echo "<input class=\"button\" type=\"submit\" name=\"bwordlinksedit\" value=\"".'修改'."\">";
	a_guide('wordlinks');
}elseif(submitcheck('bwordlinksadd')){
	$wordlinkadd['sword'] = empty($wordlinkadd['sword']) ? '' : trim(strip_tags($wordlinkadd['sword']));
	$wordlinkadd['sword'] = str_replace(array(' ',chr(0xa1).chr(0xa1), chr(0xa1).chr(0x40), chr(0xe3).chr(0x80).chr(0x80)),'',$wordlinkadd['sword']);
	if(empty($wordlinkadd['sword']) || !preg_match('/^([\x7f-\xff_-]|\w){3,20}$/',$wordlinkadd['sword'])){
		cls_message::show('被关联词不合规范',"?entry=wordlinks&page=$page");
	}
	$wordlinkadd['url'] = empty($wordlinkadd['url']) ? '' : trim(strip_tags($wordlinkadd['url']));
	if(empty($wordlinkadd['url'])){
		cls_message::show('请输入关联链接',"?entry=wordlinks&page=$page");
	}
	$db->query("INSERT INTO {$tblprefix}wordlinks SET 
				sword='$wordlinkadd[sword]',
				url='$wordlinkadd[url]'
				");
	cls_CacheFile::Update('wordlinks');
	adminlog('添加被关联词');
	cls_message::show('被关联词添加完成',"?entry=wordlinks&page=$page");

}elseif(submitcheck('bhotkeywords')){
	!$hotkeywords && cls_message::show('热门关键词统计功能已关闭');
	$query = $db->query("SELECT keyword,SUM(pcs) AS vpcs FROM {$tblprefix}keywords GROUP BY keyword");
	while($item = $db->fetch_array($query)){
		if($item['vpcs'] != 1){
			$db->query("DELETE FROM {$tblprefix}keywords WHERE keyword='$item[keyword]'");
			$db->query("INSERT INTO {$tblprefix}keywords SET keyword='$item[keyword]',pcs='$item[vpcs]'");
		}
	}
	$hotimport['amount'] = min(200,max(0,intval($hotimport['amount'])));
	$hotimport['amount'] = empty($hotimport['amount']) ? 200 : $hotimport['amount'];
	$hotimport['vpcs'] = max(0,intval($hotimport['vpcs']));
	$wheresql = $hotimport['vpcs'] ? "WHERE pcs>$hotimport[vpcs]" : '';
	$query = $db->query("SELECT * FROM {$tblprefix}keywords $wheresql ORDER BY pcs DESC LIMIT 0,$hotimport[amount]");
	while($item = $db->fetch_array($query)){
		$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}wordlinks WHERE sword='$item[keyword]'");
		if($counts){
			$db->query("UPDATE {$tblprefix}wordlinks SET pcs='$item[pcs]' WHERE sword='$item[keyword]'");
		}else{
			$item['keyword'] = addslashes($item['keyword']);
			$url = addslashes( '#');
			$db->query("INSERT INTO {$tblprefix}wordlinks SET sword='$item[keyword]',pcs='$item[pcs]',url='$url'");
		}
	}
	adminlog('从系统导入热门关键词');
	cls_CacheFile::Update('wordlinks');
	cls_message::show('关键词输入完成',"?entry=wordlinks");
}elseif(submitcheck('bwordlinksedit')){
	if(!empty($delete)){
		foreach($delete as $k){
			$db->query("DELETE FROM {$tblprefix}wordlinks WHERE wlid=$k");
			unset($wordlinksnew[$k]);
		}
	}
	if(!empty($wordlinksnew)){
		foreach($wordlinksnew as $wlid => $wordlinknew){
			$wordlinknew['url'] = empty($wordlinknew['url']) ? '' : trim(strip_tags($wordlinknew['url']));
			if(!empty($wordlinknew['url'])){
				$wordlinknew['available'] = empty($wordlinknew['available']) ? 0 : 1;
				$db->query("UPDATE {$tblprefix}wordlinks SET
							url='$wordlinknew[url]',
							available='$wordlinknew[available]'
							WHERE wlid=$wlid");
			}
		}
	}
	adminlog('编辑被关联词管理列表');
	cls_CacheFile::Update('wordlinks');
	cls_message::show('被关联词修改完成',"?entry=wordlinks&page=$page");
}
?>
