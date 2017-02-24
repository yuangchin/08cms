<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('commu') || cls_message::show('no_apermission');
$cuid = 2;
array_intersect($a_cuids,array(-1,$cuid)) || cls_message::show('没有指定交互内容的管理权限');
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
if($aid){	
	if(!($row = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] WHERE aid='$aid' AND tocid = '' AND mid = ''"))) cls_message::show('暂无点评记录。');
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		$arc = new cls_arcedit;
		$arc->set_aid($row['aid'],array('au'=>0));
		tabheader("点评人数/平均分编辑 &nbsp;<a href=\"".cls_ArcMain::Url($arc->archive)."\" target=\"_blank\">>>{$arc->archive['subject']}</a>",'newform',"?entry=extend$extend_str&aid=$aid",2,1,1);
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
		adminlog('修改点评记录。');
		cls_message::show('评认记录编辑完成',axaction(6,M_REFERER));
	}
}