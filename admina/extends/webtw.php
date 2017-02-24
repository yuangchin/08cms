<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('other') || cls_message::show('您没有当前项目的管理权限。');

$fields = cls_cache::Read('cufields',40);
$question_type_arr = preg_split('/\d+=/',$fields['twlx']['innertext']);
$question_type_arr[0] = '问题类型';


$cuid = 40; 
if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
$uclasses = cls_Mspace::LoadUclasses($memberid);
$ucidsarr = array();foreach($uclasses as $k => $v) if($v['cuid'] == $cuid) $ucidsarr[$k] = $v['title'];

$twlx = empty($twlx)?'0':max(0,intval($twlx));
$page = !empty($page) ? max(1, intval($page)) : 1; 
submitcheck('bfilter') && $page = 1;
$cid = empty($cid) ? 0 : max(0,intval($cid));
$checked = empty($checked) ? 0 : $checked; //是否回复
$keyword = empty($keyword) ? '' : $keyword; 
$filterstr = $checked?"&checked=$checked":'';
foreach(array('keyword') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));



if(!$cid){

  $selectsql = "SELECT cu.*";
  $wheresql = " WHERE 1=1 "; //cu.mid='$memberid' commu_offer. archives1.
  $fromsql = "FROM {$tblprefix}$commu[tbl] cu ";
  
  $wheresql .= empty($twlx)?'':" AND cu.twlx LIKE '%".str_replace(array(' ','*'),'%',addcslashes($twlx,'%_'))."%' ";
    
  $keyword && $wheresql .= " AND cu.twtitle LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' ";
  if($checked){
	  if($checked==-1) $wheresql .= " AND checked=0 ";
	  else $wheresql .= " AND checked=1 ";
  }
  
  if(!submitcheck('bsubmit')){
	  
	  echo form_str('newform',"?entry=$entry$extend_str$filterstr&page=$page");
	  tabheader_e();
	  echo "<tr><td class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('0'=>'审核状态','-1'=>'未核','1'=>'审核'),$checked)."</select>&nbsp; ";
	  
	  echo "<select style=\"vertical-align: middle;\" name=\"twlx\">".makeoption($question_type_arr,$twlx)."</select>&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader("网站提问管理",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  $cy_arr[] = '提问主题|L';
	  $cy_arr[] = '问题类型';
	  $cy_arr[] = '审核';
	  $cy_arr[] = '提问时间';
	  $cy_arr[] = '查看/回复';
	  $cy_arr[] = '回复时间';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page;
	 
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY cu.cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; 
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
		  $url = "?entry=$entry$extend_str&page=$page$filterstr&cid=$r[cid]\" onclick=\"return floatwin('open_inarchive',this,600,400)";
		  $addTime = date('Y-m-d H:i',$r['createdate']);
		  $checked = $r['checked']==0?'<span class="cRed">N</span>':'Y';
		  if($r['replydate']>0){
			$repFlag = "<a href=\"$url\" target='_blank'>详情</a>";
			$repTime = date('Y-m-d H:i',$r['replydate']);
		  }else{
			$repFlag = "<a href=\"$url\" target='_blank'><span class='red'>详情</span></a>";
			$repTime = '<span class="gray">-</span>';
		  }
		  $question_type = empty($r['twlx'])?'-':$question_type_arr[$r['twlx']];
		  
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtL\">$r[twtitle]</td>\n"; 
		  $itemstr .= "<td class=\"txtC w120\">$question_type</td>\n"; 
		  $itemstr .= "<td class=\"txtC w40\">$checked</td>\n";
		  $itemstr .= "<td class=\"txtC w120\">$addTime</td>\n";
		  $itemstr .= "<td class=\"txtC w80\">$repFlag</td>\n";
		  $itemstr .= "<td class=\"txtC w120\" width=\"100\">$repTime</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry$extend_str&page=$page$filterstr");
  
	  tabheader('批量操作');
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除提问",'','将选中提问从列表中清除','');
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[checkf]\" value=\"1\"> 审核留言",'checkv',makeoption(array('1'=>'审核留言','0'=>'屏蔽留言')),'select');
	  tabfooter('bsubmit');
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($selectid)) cls_message::show('请选择提问。',"?entry=$entry&page=$page$filterstr");
	foreach($selectid as $k){
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');
			continue;
		}
		if(!empty($arcdeal['checkf'])){
 			$db->query("UPDATE {$tblprefix}$commu[tbl] SET checked='$checkv' WHERE cid='$k'");
		}
	}
	cls_message::show('提问批量操作成功。',"?entry=$entry$extend_str&page=$page$filterstr");
 }
}else{

	if(!($row = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] WHERE cid='$cid'"))) cls_message::show('指定的提问记录不存在。');
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		tabheader("留言记录回复/编辑",'newform',"?entry=$entry$extend_str&page=$page$filterstr",2,1,1);
		trhidden('cid',$cid);
		$a_field = new cls_field; //echo "<pre>";print_r($fields);echo"</pre>";
		trbasic('留言者','',$row['mname']."($row[mid])",'');
		foreach($fields as $k => $v){
			if(!in_array($k,array('twlx'))){
		    	$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
		    	$a_field->trfield('fmdata');
			}
		}
		trbasic('问题类型','',"<select style=\"vertical-align: middle;\" name=\"fmdata[twlx]\">".makeoption($question_type_arr,$row['twlx'])."</select>&nbsp; ",'');
		trbasic("回复内容",'fmdata[reply]',$row['reply'],'textarea',array('w'=>360,'h'=>80));
		trbasic('留言时间','',date('Y-m-d H:i',$row['createdate']),'');
		trbasic('回复时间','',$row['replydate']?date('Y-m-d H:i',$row['replydate']):'-','');
		trbasic('是否审核','',"<select style=\"vertical-align: middle;\" name=\"fmdata[checked]\">".makeoption(array('0'=>'未核','1'=>'审核'),$row['checked'])."</select>&nbsp; ",'');
		unset($a_field);

		tabfooter('bsubmit'); 
	}else{//数据处理
		$a_field = new cls_field; 
		$sqlstr = '';
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
		$sqlstr .= ",reply='$fmdata[reply]',replydate='$timestamp',checked=$fmdata[checked]"; //die($sqlstr);
		$sqlstr && $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr WHERE cid='$cid'");
		cls_message::show('留言记录回复/编辑完成',axaction(6,M_REFERER));
	}	
}
?>