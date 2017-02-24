<?php
!defined('M_COM') && exit('No Permission');

$chid = 103; $cuid = 33;
$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$commu = cls_cache::Read('commu',$cuid);
$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$keyword = empty($keyword) ? '' : $keyword;

$selectsql = "SELECT cu.*";
$wheresql = " WHERE a.mid='$memberid'";
$aid && $wheresql .= " AND a.aid = '$aid'";
$fromsql = "FROM {$tblprefix}$commu[tbl] cu INNER JOIN {$tblprefix}".atbl($chid)." a ON cu.aid=a.aid";
$state = isset($state) ? $state : '-1';
$state==0 && $wheresql .= ' AND cu.state=0';
$state==1 && $wheresql .= ' AND cu.state=1';
$keyword && $wheresql .= " AND cu.xingming LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";

$filterstr = '';
foreach(array('keyword','state') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

if($cid){
	if(!($row = $db->fetch_one("SELECT c.* FROM {$tblprefix}$commu[tbl] c WHERE c.cid='$cid'"))) cls_message::show('指定的订单不存在。');
	$row['mspacehome'] = cls_Mspace::IndexUrl($row);
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		tabheader("报名记录",'newform',"?action=$action&cid=$cid",2,1,1);
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
			$a_field->trfield('fmdata');
		}
		unset($a_field);
		trbasic('处理状态','',makeradio('fmdata[state]',array('0'=>'未处理','1'=>'已处理'),$row['state']),'');
		$bsubmit = ($row['state']==1)?null:'bsubmit';
		tabfooter($bsubmit);
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
		$sqlstr = substr($sqlstr,1);	
		$sqlstr && $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr  WHERE cid='$cid'");
		$db->query("UPDATE {$tblprefix}$commu[tbl] SET state='$fmdata[state]' WHERE cid='$cid'");
		$c_upload->closure(1,$cid,"commu$cuid");
		$c_upload->saveuptotal(1);
		cls_message::show('订单编辑完成',axaction(6,M_REFERER));
	}
} else {
	if(!submitcheck('bsubmit')){
		echo form_str('newform',"?action=$action&aid=$aid&page=$page");
		tabheader_e();
		echo "<tr><td class=\"item2\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索用户名\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"state\">".makeoption(array('-1' => '处理状态','0' => '未处理','1' => '已处理'),$state)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader("订单列表",'','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('姓名','left'),);
		$cy_arr[] = '手机';
		$cy_arr[] = '地址';
		$cy_arr[] = '状态';
		$cy_arr[] = '审核';
		$cy_arr[] = '创建日期';
		$cy_arr[] = '明细';
		trcategory($cy_arr);

		$pagetmp = $page;
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$checkstr = $r['checked'] ? 'Y' : '-';
			$r['state']==0 && $statestr = '未处理';
			$r['state']==1 && $statestr = '已处理';
			$adddatestr = date('Y-m-d',$r['createdate']);
			$editstr = "<a href=\"?action=$action&cid=$r[cid]\" onclick=\"return floatwin('open_arcexit',this)\">编辑</a>";
			$itemstr .= "<tr><td class=\"item\" width=\"40\">$selectstr</td><td class=\"item2\">$r[xingming]</td>\n";
			$itemstr .= "<td class=\"item\">$r[tel]</td>\n";
			$itemstr .= "<td class=\"item\">$r[address]</td>\n";
			$itemstr .= "<td class=\"item\">$statestr</td>\n";
			$itemstr .= "<td class=\"item\">$checkstr</td>\n";
			$itemstr .= "<td class=\"item\" width=\"100\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"item\" width=\"100\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action&aid=$aid$filterstr");

		tabheader('批量操作');
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\"> 删除订单",'','将选中记录从列表中清除','');
		tabfooter('bsubmit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
		if(empty($selectid)) cls_message::show('请选择项目。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
				continue;
			}
		}
		cls_message::show('订单批量操作成功。',"?action=$action&aid=$aid&page=$page$filterstr");
	}
}

?>