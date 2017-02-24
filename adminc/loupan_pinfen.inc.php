<?php
!defined('M_COM') && exit('No Permission');
$cuid = 2;
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');

$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$chid = empty($chid) ? 4 : max(0,intval($chid)); //4;

//获取所管理的楼盘aid
$mchid = $curuser->info['mchid'];
$sql_ids = "SELECT loupan FROM {$tblprefix}members_$mchid WHERE mid='$memberid'"; 
$loupanids = $db->result_one($sql_ids); if($loupanids) $loupanids = substr($loupanids,1); //echo($loupanids);
if(empty($loupanids)) $loupanids = 0;

if($cid){
	if(!($row = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] WHERE cid='$cid'"))) cls_message::show('指定的点评记录不存在。');
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		$arc = new cls_arcedit;
		$arc->set_aid($row['aid'],array('chid'=>$chid,'au'=>0));
		tabheader("点评记录编辑 &nbsp;<a href=\"".cls_ArcMain::Url($arc->archive)."\" target=\"_blank\">>>{$arc->archive['subject']}</a>",'newform',"?action=$action&cid=$cid",2,1,1);
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(empty($row['tocid']) && in_array($k,array('pjzj'))){
				$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
				$a_field->trfield('fmdata');
			}
			if(!empty($row['tocid']) && in_array($k,array('reply'))){
				$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
				$a_field->trfield('fmdata');
			}
		}
		unset($a_field);
		tabfooter('bsubmit');
	}else{//数据处理
		$sqlstr = '';
		$c_upload = new cls_upload;	
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
				$fmdata[$k] = $a_field->deal('fmdata','mcmessage',axaction(2,M_REFERER));
				$sqlstr .= ",$k='$fmdata[$k]'";
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $sqlstr .= ",{$k}_x='$y'";
			}
		}
		unset($a_field);
		$total = 0;foreach(array('jgdw','hxjg','fwzl','dlwz','zbhj','ptss','wygl','xqhj','fzs','ztpj',) as $k) !empty($fmdata[$k]) && $total += $fmdata[$k];
		$sqlstr .= ",total='$total'";
		$sqlstr = substr($sqlstr,1);
		$sqlstr && $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr  WHERE cid='$cid'");
		$c_upload->closure(1,$cid,"commu$cuid");
		$c_upload->saveuptotal(1);
		cls_message::show('评认记录编辑完成',axaction(6,M_REFERER));
	}
}elseif($aid){
	if(!($row = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] WHERE aid='$aid' AND tocid = '' AND mid = ''"))) cls_message::show('暂无点评记录。');
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		$arc = new cls_arcedit;
		$arc->set_aid($row['aid'],array('chid'=>4,'au'=>0));
		tabheader("点评人数/平均分编辑 &nbsp;<a href=\"".cls_ArcMain::Url($arc->archive)."\" target=\"_blank\">>>{$arc->archive['subject']}</a>",'newform',"?action=loupandp&aid=$aid",6,1,1);
		$a_field = new cls_field;
		//控制字段的排序,每一项都是先点评人数，再到平均分，防止table里面的样式出错。
		$_new_fileds_arr = array();
		$_fields_key_arr = array_keys($fields);		
		foreach($fields as $k => $v){
			if(strstr($k,'rs')){				
				$_name = substr($k,0,strpos($k,'rs'));				
				if(in_array($_name,$_fields_key_arr)){
					$_new_fileds_arr[$k] = $v;
					$_new_fileds_arr[$_name] = $fields[$_name];
				}	
			}
		}
		foreach($_new_fileds_arr as $k => $v){
			if(strstr($k,'rs')){
				echo "<tr><td width='20%'>&nbsp;</td><td class=\"txt txtright fB\">".$v['cname']."点评</td>
	<td class=\"txt txtleft\"><input type=\"text\" value=\"".$row[$k]."\" name=\"fmdata[".$k."]\" id=\"fmdata[".$k."]\" size=\"10\">&nbsp;人</td>";
			}else{
				echo "<td class=\"txt txtright fB\">平均分</td>
	<td class=\"txt txtleft\"><input type=\"text\" value=\"".$row[$k]."\" name=\"fmdata[".$k."]\" id=\"fmdata[".$k."]\" size=\"10\">&nbsp;分</td><td width='20%'>&nbsp;</td></tr>";
			}
		}
		unset($a_field);
		tabfooter('bsubmit');
	}else{//数据处理
		$sqlstr = '';
		$c_upload = new cls_upload;	
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
				$fmdata[$k] = $a_field->deal('fmdata','amessage',axaction(2,M_REFERER));
				$sqlstr .= ",$k='$fmdata[$k]'";
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $sqlstr .= ",{$k}_x='$y'";
			}
		}
		unset($a_field);
		$total = 0;
		$valid_num = 0;
		foreach($fmdata as $k => $v){
			!strstr($k,'rs') && $total += $v;
			!strstr($k,'rs') && $valid_num ++;
		}
		$total = round($total/$valid_num,2);
		$sqlstr .= ",total='$total'";
		$sqlstr = substr($sqlstr,1);
		$sqlstr && $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr  WHERE aid='$aid'");	
		cls_message::show('评分编辑完成',axaction(6,M_REFERER));
	}
}else{
	$ccid1 = empty($ccid1) ? 0 : max(0,intval($ccid1));
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$checked = isset($checked) ? $checked : '-1';
	$keyword = empty($keyword) ? '' : $keyword;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
	
	$selectsql = "SELECT cu.*,cu.createdate AS ucreatedate,a.createdate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject,a.ccid1";	
	$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}".atbl(4)." a ON a.aid=cu.aid WHERE cu.aid IN($loupanids) ";
	$wheresql = "";
	
	if($ccid1 && $cnsql = cnsql(1,sonbycoid($ccid1,1),'a.')) $wheresql .= " AND $cnsql";
	if($checked != -1) $wheresql .= " AND cu.checked='$checked'";
	$keyword && $wheresql .= " AND (cu.mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' OR a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%')";
	$indays && $wheresql .= " AND cu.createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND cu.createdate<'".($timestamp - 86400 * $outdays)."'";
	$wheresql .=  "AND cu.mname != '' and  cu.tocid = '0' ";
	$filterstr = '';
	foreach(array('ccid1','keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('checked',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	if(!submitcheck('bsubmit')){
		echo form_str(@$actionid.'arcsedit',"?action=$action&page=$page&chid=$chid");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索楼盘或点评作者\">&nbsp; ";
		echo '<span>'.cn_select("ccid1",array('value' => $ccid1,'coid' => 1,'notip' => 1,'addstr' => '不限地域','vmode' => 0,'framein' => 1,)).'</span>&nbsp; ';
		echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('-1' => '审核状态','0' => '未审','1' => '已审'),$checked)."</select>&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader('楼盘点评列表','','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('被评楼盘','txtL'),);		
		$cy_arr[] = '地域';
		$cy_arr[] = '会员';
		$cy_arr[] = '审核';
		$cy_arr[] = '添加时间';
		$cy_arr[] = '评分';
		$cy_arr[] = '点评';
		trcategory($cy_arr);
		
		$pagetmp = $page;
		
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$subjectstr = "<a href=\"".cls_ArcMain::Url($r)."\" target=\"_blank\">$r[subject]</a>";
			$coclasses = cls_cache::Read('coclasses',1);
			$ccid1str = @$coclasses[$r['ccid1']]['title'];
			$mnamestr = $r['mname'];
			$checkstr = $r['checked'] ? 'Y' : '-';
			$adddatestr = date('Y-m-d',$r['ucreatedate']);
			$ck_point = "<a href=\"?action=$action&aid=$r[aid]\" onclick=\"return floatwin('open_commentsedit',this)\">查看</a>";
			$editstr = "<a href=\"?action=$action&cid=$r[cid]".(empty($r['tocid'])?'':'&tocid='.$r['tocid'])."\" onclick=\"return floatwin('open_commentsedit',this)\">详情</a>";
	
			$itemstr .= "<tr class=\"txt\"><td class=\"item\" >$selectstr</td><td class=\"item2\">$subjectstr</td>\n";			
			$itemstr .= "<td class=\"item\">$ccid1str</td>\n";
			$itemstr .= "<td class=\"item\">$mnamestr</td>\n";
			$itemstr .= "<td class=\"item\">$checkstr</td>\n";
			$itemstr .= "<td class=\"item\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"item\">$ck_point</td>\n";
			$itemstr .= "<td class=\"item\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$atpp,$page, "?action=$action$filterstr&chid=$chid");
		
		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除';
		$s_arr['check'] = '审核';
		$s_arr['uncheck'] = '解审';
		if($s_arr){
			$str = '';
			$i = 1;
			foreach($s_arr as $k => $v){
				$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"".($k=='delete'?' onclick="deltip()"':'').">$v &nbsp;";
				if(!($i % 5)) $str .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$str,'');
		}
		tabfooter('bsubmit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
		if(empty($selectid)) cls_message::show('请选择点评记录。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			$k = empty($k) ? 0 : max(0, intval($k));
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
				continue;
			}
			if(!empty($arcdeal['check'])){
				$db->query("UPDATE {$tblprefix}$commu[tbl] SET checked='1' WHERE cid='$k'");
			}elseif(!empty($arcdeal['uncheck'])){
				$db->query("UPDATE {$tblprefix}$commu[tbl] SET checked='0' WHERE cid='$k'");
			}
		}

		cls_message::show('点评批量操作成功。',"?action=$action&page=$page$filterstr&chid=$chid");
		
		
	}
}
?>