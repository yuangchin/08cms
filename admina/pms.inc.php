<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('other')) cls_message::show($re);
$grouptypes = cls_cache::Read('grouptypes');
//$enable_uc = 0;
$page = isset($page) ? max(0,intval($page)) : 1;
$keyword = empty($keyword) ? '' : $keyword;
$keywordfrom = empty($keywordfrom) ? '' : $keywordfrom;
$keytypefrom = empty($keytypefrom) ? '' : $keytypefrom;
$keywordto = empty($keywordto) ? '' : $keywordto;
$keytypeto = empty($keytypeto) ? '' : $keytypeto;
$pmid = empty($pmid) ? 0 : max(0,intval($pmid));
$indays = empty($indays) ? 0 : max(0,intval($indays));
$outdays = empty($outdays) ? 0 : max(0,intval($outdays));

if($action == 'batchpms'){
	if(!submitcheck('bbatchpms')){
		backnav('pms','batch');

		tabheader('接收会员筛选','batchpms','?entry=pms&action=batchpms',2,0,1);
		trbasic('发送至(逗号分隔多个会员ID)','pmnew[toids]'); //$enable_uc || 
		trbasic('发送至(逗号分隔多个会员名称)','pmnew[tonames]');
		//if(!$enable_uc){
			$limitarr = array('0' => '不限会员组','1' => '手动选择');
			foreach($grouptypes as $gtid => $grouptype){
				sourcemodule($grouptype['cname'].'限制',
							"pmnew[limit$gtid]",
							$limitarr,
							'0',
							'1',
							"pmnew[ugids$gtid][]",
							ugidsarr($gtid),
							array()
							);
			}
		//}
		tabfooter();
		tabheader('短信内容设置');
		trbasic('短信标题','pmnew[title]','','text',array('guide'=>'','validate'=>' rule="text" must="1" regx="" min="" max="" '));
		trbasic('短信内容','pmnew[content]','','textarea',array('guide'=>'','validate'=>' rule="text" must="1" regx="" min="" max="" '));
		tabfooter('bbatchpms');
		a_guide('pmsbatch');
	}else{
		if(empty($pmnew['title']) || empty($pmnew['content'])){
			cls_message::show('短信资料不完全','?entry=pms&action=batchpms');
		}
		$wheresql = '';
		if(!empty($pmnew['toids'])){
			$toids = array_filter(explode(',',$pmnew['toids']));
			$toids = mimplode($toids);
			$wheresql = empty($toids) ? "" : "mid IN ($toids)";
		}
		if(!empty($pmnew['tonames'])){
			$tonames = array_filter(explode(',',$pmnew['tonames']));
			$tonames = mimplode($tonames);
			$wheresql .= empty($tonames) ? "" : ((empty($wheresql) ? "" : " OR ")."mname IN ($tonames)");
		}
		!empty($wheresql) && ($wheresql = "(".$wheresql.")");
		foreach($grouptypes as $gtid => $grouptype){
			if(!empty($pmnew['limit'.$gtid]) && !empty($pmnew['ugids'.$gtid])){
				$ugids = mimplode($pmnew['ugids'.$gtid]);
				$fieldname = 'grouptype'.$gtid;
				$wheresql .= empty($ugids) ? "" : ((empty($wheresql) ? "" : " AND ")."$fieldname IN ($ugids)");
			}
		}
		$wheresql = empty($wheresql) ? "" : "WHERE $wheresql";
		$query = $db->query("SELECT mid FROM {$tblprefix}members $wheresql ORDER BY mid");
		while($user = $db->fetch_array($query)){
			//收信数量限制分析
			$db->query("INSERT INTO {$tblprefix}pms SET
						title = '$pmnew[title]',
						content = '$pmnew[content]',
						toid = '$user[mid]',
						fromid = '$memberid',
						fromuser = '".$curuser->info['mname']."',
						pmdate = '$timestamp'
						");
		}
		cls_message::show('短信发送完成','?entry=pms&action=batchpms');
	}
}elseif($action == 'clearpms'){
	//$enable_uc && cls_message::show("请进入UCenter管理 [<a href=\"$uc_api\" target=\"_blank\">进入</a>]",'');
	if(!submitcheck('bclearpms')){
		backnav('pms','clear');

		tabheader('短信清除筛选','clearpms','?entry=pms&action=clearpms');
		trbasic('发送人ID(逗号分隔多个ID)','pmnew[fromids]');
		trbasic('发件人名称(逗号分隔多个)','pmnew[fromnames]');
		trbasic('仅清除已读短信','pmnew[viewed]','0','radio');
		trbasic('多少天以内添加','pmnew[days]');
		tabfooter('bclearpms');
		a_guide('pmsclear');
	}else{
		$wheresql = '';
		if(!empty($pmnew['fromids'])){
			$fromids = array_filter(explode(',',$pmnew['fromids']));
			$fromids = mimplode($fromids);
			$wheresql = empty($fromids) ? "" : "fromid IN ($fromids)";
		}
		if(!empty($pmnew['fromnames'])){
			$fromnames = array_filter(explode(',',$pmnew['fromnames']));
			$fromnames = mimplode($fromnames);
			$wheresql .= empty($fromnames) ? "" : ((empty($wheresql) ? "" : " OR ")."fromuser IN ($fromnames)");
		}
		!empty($wheresql) && ($wheresql = "(".$wheresql.")");
		if(!empty($pmnew['viewed'])){
			$wheresql .= (empty($wheresql) ? "" : " AND ")."viewed='1'";
		}
		if(!empty($pmnew['days'])){
			$wheresql .= (empty($wheresql) ? "" : " AND ")."pmdate<".($timestamp-86400*$pmnew['days']);
		}
		$wheresql = empty($wheresql) ? "" : "WHERE $wheresql";
		$db->query("DELETE FROM {$tblprefix}pms $wheresql",'UNBUFFERED');
		cls_message::show('短信清除完成','?entry=pms&action=clearpms');
	}
}elseif($action == 'pmsmanage'){
	$filterstr = '';
	if(!submitcheck('bsubmit')){
		backnav('pms','manage');
		
		$selectsql = "SELECT *,m.mname";
  		$wheresql = " WHERE 1=1 "; 
  		$fromsql = "FROM {$tblprefix}pms INNER JOIN {$tblprefix}members m ON m.mid = toid";
		
		if($keyword){
			$wheresql .= " AND title like '%".$keyword."%'";
		}
		if($keywordfrom){
			$wheresql .= ($keytypefrom !='fromid') ?  " AND fromuser like '%".$keywordfrom."%'" 
						: " AND fromid ='$keywordfrom'";
		}
		if($keywordto){
			$wheresql .= ($keytypeto !='fromid') ?  " AND m.mname like '%".$keywordto."%'" 
						: " AND toid ='$keywordto'";
		}
		
		$indays && $wheresql .= " AND pmdate>'".($timestamp - 86400 * $indays)."'";
		$outdays && $wheresql .= " AND pmdate<'".($timestamp - 86400 * $outdays)."'";
			
		foreach(array('keyword','keywordfrom','keytypefrom','keywordto','keytypeto','indays','outdays') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
		echo form_str('sendlogs',"?entry=$entry&action=$action$filterstr&page=$page");
	 	tabheader_e();
	 	echo "<tr><td class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键字\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"keytypefrom\">".makeoption(array('0'=>'--发信人--','fromuser'=>'会员名','fromid'=>'会员编号'),$keytypefrom)."</select>&nbsp; ";
	 	echo "<input class=\"text\" name=\"keywordfrom\" type=\"text\" value=\"$keywordfrom\" size=\"8\" style=\"vertical-align: middle;\" title=\"发信人 关键字\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"keytypeto\">".makeoption(array('0'=>'--收信人--','fromuser'=>'会员名','fromid'=>'会员编号'),$keytypeto)."</select>&nbsp; ";
	 	echo "<input class=\"text\" name=\"keywordto\" type=\"text\" value=\"$keywordto\" size=\"8\" style=\"vertical-align: middle;\" title=\"收信人 关键字\">&nbsp; "; 
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"3\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"3\" style=\"vertical-align: middle;\">天内&nbsp; ";
	    echo strbutton('bfilter','筛选');
	    tabfooter();
		tabheader("短信发送记录",'','',10);
		
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  //$cy_arr[] = '发送时间';
	  $cy_arr[] = '标题|L';
	  $cy_arr[] = '发信人';
	  $cy_arr[] = '收信人';
	  $cy_arr[] = '内容摘要|L';
	  $cy_arr[] = '发信时间';
	  $cy_arr[] = '已读';
	  $cy_arr[] = '详情';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY pmid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; $stype = array('sadm'=>'管理员发','scom'=>'会员发送','ctel'=>'其它认证',);
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[pmid]]\" value=\"$r[pmid]\">";
		  
		  $fromuser = $r['fromuser'];
		  $toid = $r['toid'];
		  $toname = $db->result_one("SELECT mname FROM {$tblprefix}members WHERE mid='$toid'");
		  $time = date('Y-m-d H:i',$r['pmdate']);
		  $viewed = $r['viewed'] ? 'Y' : 'N';
		  $content = cls_string::CutStr(mhtmlspecialchars($r['content']),40);
		 
		  $edit = "<a href=\"?entry=$entry&action=pmsedit&pmid=$r[pmid]\" onclick=\"return floatwin('open_arcexit',this)\">详情</a>";
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w30\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtL\">$r[title]</td>\n";
		  $itemstr .= "<td class=\"txtC w120\">$fromuser</td>\n";
		  $itemstr .= "<td class=\"txtC w120\">$toname</td>\n";
		  $itemstr .= "<td class=\"txtL\">$content</td>\n";
		  $itemstr .= "<td class=\"txtC w110\">$time</td>\n";
		  $itemstr .= "<td class=\"txtC w30\">$viewed</td>\n";
		  $itemstr .= "<td class=\"txtC w50\">$edit</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry&action=$action&page=$page$filterstr");
  
	  tabheader('批量操作');
	  trbasic("选择操作项目",'',"<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除记录",'');
	 
	  tabfooter('bsubmit');
	}else{
	  if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&action=$action&page=$page$filterstr");
	  if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&action=$action&page=$page$filterstr");
	  
	  if(!empty($selectid)){
	    foreach($selectid as $pmid){
			if(!empty($arcdeal['delete']))
				$db->query("DELETE FROM {$tblprefix}pms WHERE pmid='$pmid'");
		}
	  }
	  cls_message::show('记录批量操作成功'.'。',"?entry=$entry&action=$action&page=$page$filterstr");
	}
}elseif($action == "pmsedit" && $pmid){
	$pms_arr = array();
	$pmid = empty($pmid)?0:max(1,intval($pmid));
	$query = $db->query("SELECT *,m.mname FROM {$tblprefix}pms INNER JOIN {$tblprefix}members m ON m.mid = toid WHERE pmid='$pmid'");
	$r = $db->fetch_array($query);
	tabheader("站内短信明细");
	if($r){
		trbasic('发信人','',nl2br($r['fromuser']),'');
		trbasic('收信人','',nl2br($r['mname']),'');
		trbasic('标题','',nl2br($r['title']),'');
		trbasic('内容','',nl2br($r['content']),'');
		trbasic('发信时间','',nl2br(date('Y-m-d H:i',$r['pmdate'])),'');
		trbasic('已读','',$r['viewed'] ? 'Y' : 'N','');
	}
	$db->query("UPDATE {$tblprefix}pms SET viewed = '1' WHERE pmid='$pmid'");
}
?>