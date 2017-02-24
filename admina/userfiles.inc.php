<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('other')) cls_message::show($re);
if($action == 'userfilesedit'){
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$type = empty($type) ? '' : $type;
	isset($table) || $table = -1;
	$aids = empty($aids) ? '' : $aids;
	$mids = empty($mids) ? '' : $mids;
    $sizefrom = empty($sizefrom) ? '' : $sizefrom;
    $sizeto = empty($sizeto) ? '' : $sizeto;
	
	$wheresql = '';
    if($sizefrom && $sizeto){
        $wheresql .= ($wheresql ? " AND " : "")." (size>='".($sizefrom*1024)."' AND size<='".($sizeto*1024)."')";
    } elseif($sizefrom=='' && $sizeto) {
        $wheresql .= ($wheresql ? " AND " : "")." size<='".($sizeto*1024)."'";
    }elseif($sizefrom && $sizeto=='') {
        $wheresql .= ($wheresql ? " AND " : "")." size>='".($sizefrom*1024)."'";
    }
	if(!empty($type)){
		$wheresql .= ($wheresql ? " AND " : "")."type='$type'";
	}
	if(!empty($aids)){
		$aidsarr = array_filter(explode(',',$aids));
		$wheresql .= ($wheresql ? " AND " : "")."aid ".multi_str($aidsarr);
	}
	if(!empty($mids)){
		$midsarr = array_filter(explode(',',$mids));
		$wheresql .= ($wheresql ? " AND " : "")."mid ".multi_str($midsarr);
	}
	$table != -1 && $wheresql .= ($wheresql ? " AND " : "")."tid='$table'";
	
	$filterstr = '';
	foreach(array('aids','type','table','sizefrom','sizeto') as $k)$filterstr .= "&$k=".urlencode($$k);

	$wheresql = $wheresql ? ("WHERE $wheresql") : "";
	if(!submitcheck('buserfilesedit')){
		//同include/upload.cls.php中closure函数的$tids变量对应
		$tabsarr = array('-1' => '全部类型',1 => '文档', 2 => '副件信息', 3 => '会员','0' => '其它');
		$linkarr = array(1 => 'archive&action=archivedetail&aid=', 2 => 'farchive&action=farchivedetail&aid=', 3 => 'member&action=memberdetail&mid=', 4 => 'marchives&action=marchivedetail&maid=', 16 => 'comments&action=commentdetail&cid=', 17 => 'replys&action=replydetail&cid=', 18 => 'offers&action=offerdetail&cid=', 32 => 'mcomments&action=mcommentdetail&cid=', 33 => 'mreplys&action=mreplydetail&cid=');
		$typearr = array('0' => '全部类型','image' => '图片','flash' => 'Flash','media' => '视频','file' => '其它',);
		echo form_str($action.'arcsedit',"?entry=$entry&action=$action");
		tabheader_e();
		echo "<tr><td class=\"txtL\">";
        echo '附件大小&nbsp;<input type="text" class="txt1" title="请输入最低大小" name="sizefrom" value="'.$sizefrom.'" size="6"> - <input class="txt1" type="text" title="请输入最高大小" name="sizeto" value="'.$sizeto.'" size="6">K&nbsp;';
		echo '相关文档ID(多个ID用逗号隔开)'."&nbsp; <input class=\"text\" name=\"aids\" type=\"text\" value=\"$aids\" style=\"vertical-align: middle;\">&nbsp; ";
		echo '会员id(逗号分隔)'."&nbsp; <input class=\"text\" name=\"mids\" type=\"text\" value=\"$mids\" style=\"vertical-align: middle;\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"type\">".makeoption($typearr,$type)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"table\">".makeoption($tabsarr,$table)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选').'</td></tr>';
		tabfooter();
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}userfiles $wheresql ORDER BY ufid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$itemstr = '';
		while($item = $db->fetch_array($query)) {
			$item['createdate'] = date("$dateformat", $item['createdate']);
			if(cls_url::is_remote_atm($item['url'])){
				$item['url'] = $ftp_url.$item['url']; //global $ftp_url; 参考：view_checkurl($dbstr)
			}else{
				$item['url'] = is_file(M_ROOT.$item['url']) ? cls_url::tag2atm($item['url']) : ''; //cls_url::view_url，cls_url::tag2atm
			}
			$item['preview'] = empty($item['url']) ? '-' : ("<a href='".$item['url']."' target='_blank'>".($item['type']=='image' ? '预览' : '打开')."</a>");
			$item['type'] = $typearr[$item['type']];
			$item['thumbedstr'] = $item['thumbed'] ? 'Y' : '-';
			$item['size'] = ceil($item['size'] / 1024);
			$item['source'] = $item['aid'] ? "<a href=\"archive.php?aid=$item[aid]\" target=\"_blank\">查看</a>" : "-";
			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid['$item[ufid]']\" value=\"$item[ufid]\" onclick=\"deltip()\">\n".
				"<td class=\"txtL\">$item[filename]</td>\n".
				"<td class=\"txtC w40\">$item[type]</td>\n".
				"<td class=\"txtC w50\">$item[size]</td>\n".
				"<td class=\"txtC w40\">$item[preview]</td>\n".
				"<td class=\"txtC w40\">$item[thumbedstr]</td>\n".
				"<td class=\"txtC w80\">$item[mname]</td>\n".
				"<td class=\"txtC w100\">$item[createdate]</td>\n".
				"<td class=\"txtC w40\">$item[source]</td></tr>\n";
		}
		$itemcount = $db->result_one("SELECT count(*) FROM {$tblprefix}userfiles $wheresql");
		$multi = multi($itemcount, $atpp, $page, "?entry=userfiles&action=userfilesedit$filterstr");

		tabheader('附件列表删除操作'."&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"checkbox\" type=\"checkbox\" name=\"select_all\" value=\"1\">&nbsp;".'全选所有页内容','','',9);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"deltip(this,0,checkall,this.form,'selectid','chkall')\">".'删?',array('名称','txtL'),'类型','大小(k)','预览','缩略图','会员','上传日期','来源'));
		echo $itemstr;
		tabfooter();
		echo $multi;
		echo "<br><input class=\"button\" type=\"submit\" name=\"buserfilesedit\" value=\"提交\"></form>";
	}else{
		if(empty($selectid) && empty($select_all)){
			cls_message::show('请选择文档',"?entry=userfiles&action=userfilesedit&page=$page$filterstr");
		}
		$items = array();
		if(!empty($select_all)){
			$selectid = array();
			$npage = empty($npage) ? 1 : $npage;
			if(empty($pages)){
				$itemcount = $db->result_one("SELECT count(*) FROM {$tblprefix}userfiles $wheresql");
				$pages = @ceil($itemcount / $atpp);
			}
			if($npage <= $pages){
				$fromstr = empty($fromid) ? "" : "ufid<$fromid";
				$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
				$query = $db->query("SELECT * FROM {$tblprefix}userfiles $nwheresql ORDER BY ufid DESC LIMIT 0,$atpp");
			}
		}else{
			$query = $db->query("SELECT * FROM {$tblprefix}userfiles WHERE ufid ".multi_str($selectid)." ORDER BY ufid");
		}
		while($r = $db->fetch_array($query)) $items[$r['ufid']] = $r;
		
		$actuser = new cls_userinfo;
		foreach($items as $r){
			atm_delete($r['url'],$r['type']);
			$actuser->activeuser($r['mid']);
			$actuser->updateuptotal(ceil($r['size'] / 1024),1,1);
			$actuser->init();
		}
		$db->query("DELETE FROM {$tblprefix}userfiles WHERE ufid ".multi_str(array_keys($items)),'UNBUFFERED');
		unset($actuser);

		if(!empty($select_all)){
			$npage ++;
			if($npage <= $pages){
				$fromid = min(array_keys($items));
				$transtr = '';
				$transtr .= "&select_all=1";
				$transtr .= "&pages=$pages";
				$transtr .= "&npage=$npage";
				$transtr .= "&buserfilesedit=1";
				$transtr .= "&fromid=$fromid";
				cls_message::show("文件操作正在进行中...<br>共 $pages 页，正在处理第 $npage 页<br><br><a href=\"?entry=userfiles&action=userfilesedit&page=$page$filterstr\">>>中止当前操作</a>","?entry=userfiles&action=userfilesedit&page=$page$filterstr$transtr",50);
			}
		}
		adminlog('上传附件管理','附件列表删除操作');
		cls_message::show('附件操作完成',"?entry=userfiles&action=userfilesedit&page=$page$filterstr",500);
	
	}
}
?>