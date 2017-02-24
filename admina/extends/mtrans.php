<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('member') || cls_message::show('您没有当前项目的管理权限。');

$mchannels = cls_cache::Read('mchannels');
$channels = cls_cache::Read('channels');
$currencys = cls_cache::Read('currencys');
$mctypes = cls_cache::Read('mctypes');

empty($action) && $action = 'mtransedit'; //print_r($mchannels);
if($action == 'mtransedit'){
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$checked = isset($checked) ? $checked : '-1';
	$keyword = empty($keyword) ? '' : $keyword;

	$wheresql = '';
	$checked != '-1' && $wheresql .= ($wheresql ? " AND " : "")."checked='$checked'";
	$keyword && $wheresql .= ($wheresql ? " AND " : "")."mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";

	$filterstr = '';
	foreach(array('keyword',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('checked',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	$wheresql = $wheresql ? "WHERE ".$wheresql : "";
	if(!submitcheck('bsubmit')){
		echo form_str($actionid.'utransedit',"?entry=$entry$extend_str&action=$action&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索用户名\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('-1' => '审核状态','0' => '未审','1' => '已审'),$checked)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();

		tabheader('基本会员 升级 商家','','',8);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'selectid','chkall')\">",'会员帐号|L',);
		$cy_arr[] = "姓名/公司名";
		$cy_arr[] = "申请类型";
		$cy_arr[] = "审核";
		$cy_arr[] = '升级申请时间';
		$cy_arr[] = '详情';
		trcategory($cy_arr);
		
		$pagetmp = $page;	
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}mtrans $wheresql ORDER BY trid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$itemstr = '';
		while($row = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$row[trid]]\" value=\"$row[trid]\">";
			$createdatestr = date("Y-m-d H:i", $row['createdate']);
			$arr = unserialize($row['contentarr']);
			$arrname = isset($arr['xingming'])?$arr['xingming']:$arr['companynm']; //xingming,companynm			
			$arrtype = "($row[toid])".$mchannels[$row['toid']]['cname'];
			$checkstr = $row['checked'] ? 'Y' : '-';
			$detailstr = $row['checked'] ? '-' : "<a href=\"?entry=$entry$extend_str&action=mtrandetail&trid=$row[trid]\" onclick=\"return floatwin('open_transdetail',this)\">详情</a>";
			$itemstr .= "<tr class=\"txt\">\n".
			"<td class=\"txtC w40\">$selectstr</td>\n".
			"<td class=\"txtL\">$row[mname]</td>\n".
			"<td class=\"txt\">$arrname</td>\n".			
			"<td class=\"txt\">$arrtype</td>\n".
			"<td class=\"txtC w40\">$checkstr</td>\n".
			"<td class=\"txtC w150\">$createdatestr</td>\n".
			"<td class=\"txtC w40\">$detailstr</td>\n".
			"</tr>\n";
		}
		$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}mtrans $wheresql");
		$multi = multi($counts,$atpp,$page,"?entry=$entry$extend_str&action=$action$filterstr");

		echo $itemstr;
		tabfooter();
		echo $multi;
		
		tabheader('操作项目');
		$s_arr = array();
		$s_arr['delete'] = '删除';
		$s_arr['check'] = '审核';
		if($s_arr){
			$soperatestr = '';
			$i = 1;
			foreach($s_arr as $k => $v){
				$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" id=\"arcdeal[$k]\" name=\"arcdeal[$k]\" value=\"1\"" . ($k == 'delete' ? ' onclick="deltip()"' : '') . "><label for=\"arcdeal[$k]\">$v</label> &nbsp;";
				if(!($i % 5)) $soperatestr .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$soperatestr,'');
		}
		tabfooter('bsubmit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=$entry$extend_str&action=$action&page=$page$filterstr");
		if(empty($selectid)) cls_message::show('请选择会员',"?entry=$entry$extend_str&action=$action&page=$page$filterstr");
		
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}mtrans WHERE trid ".multi_str($selectid));
		}elseif(!empty($arcdeal['check'])){
			$actuser = new cls_userinfo;
			$a_field = new cls_field;
			$c_upload = new cls_upload;
			foreach($selectid as $trid){
				if($row = $db->fetch_one("SELECT * FROM {$tblprefix}mtrans WHERE trid='$trid' AND checked='0'")){
					$contentarr = empty($row['contentarr']) ? array() : unserialize($row['contentarr']);
					unset($row['contentarr']);
					
					//循环处理身份证和执照的验证审核开始
					$au = new cls_userinfo;
					$au->activeuser($row['mid']);
					$mchid = $au->info['mchid'];
					$mfields = cls_cache::Read('mfields',$mchid);
					$mcfield = &$mfields[$mctype['field']];
				
					foreach($contentarr as $tplx=>$tplj){
						if($tplx == 'sfz' || $tplx == 'jjrzz'){
							$mctid = $tplx == 'sfz'?'2':'3';
							$a_field->init($mcfield,$tplj);
							$content = $a_field->deal('fmdata','amessage',M_REFERER);
							$au->updatefield($tplx,$tplj,$a_field->field['tbl']);
							$au->updatefield("mctid$mctid",$mctid);
							$au->updatedb();
							$db->query("INSERT INTO {$tblprefix}mcerts SET mid='$row[mid]',mname='$row[mname]',mctid='$mctid',createdate='$timestamp',checkdate='$timestamp',content='$tplj'");
						}
					}//循环处理身份证和执照的验证审核结束
					
					$actuser->activeuser($row['mid'],1);
					$ochid = $row['fromid'];
					$mchid = $row['toid'];
					$mchannel = $mchannels[$mchid];
					$mfields = cls_cache::Read('mfields',$mchid);
					
					$db->query("DELETE FROM {$tblprefix}members_$ochid WHERE mid='$row[mid]'");
					$db->query("INSERT INTO {$tblprefix}members_$mchid SET mid='$row[mid]'",'SILENT');
					$actuser->updatefield('mchid',$mchid);
					
					if($mchid == 2){//如果是普通会员升级为经纪人，则二手房、出租房源中对应的mid的mchid字段也要修改成2
						$db->query("UPDATE {$tblprefix}".atbl(2)." SET mchid = '2' WHERE mid = '$row[mid]'");
						$db->query("UPDATE {$tblprefix}".atbl(3)." SET mchid = '2' WHERE mid = '$row[mid]'");
					}
					
					foreach($mfields as $k => $v){
						if(!$v['issystem'] && !empty($contentarr[$k])){
							$a_field->init($v);
							$contentarr[$k] = $a_field->deal('contentarr','');
							if(!$a_field->error){
								$actuser->updatefield($k,$contentarr[$k],$v['tbl']);
								if($arr = multi_val_arr($contentarr[$k],$v)) foreach($arr as $x => $y) $actuser->updatefield($k.'_'.$x,$y,$v['tbl']);
							}
						}
					}
					$crids = array();foreach($currencys as $k => $v) $v['available'] && $v['initial'] && $crids[$k] = $v['initial'];
					$crids && $actuser->updatecrids($crids,0,'会员注册初始积分。');
					$actuser->updatefield('checked',1);
					$actuser->nogroupbymchid();//模型变更以后清理不需要的组定义
					$actuser->groupinit();
					$actuser->updatefield('mtcid',($mtcid = array_shift(array_keys($actuser->mtcidsarr()))) ? $mtcid : 0);
					$actuser->autoletter();
					$actuser->updatedb();
					$db->query("UPDATE {$tblprefix}mtrans SET contentarr='',remark='',reply='',checked='1' WHERE trid='$trid'");
				}
			}
			
			$c_upload->closure(1,$memberid,'members');
			$c_upload->saveuptotal(1);
			unset($c_upload);
			unset($a_field);
			unset($actuser);
		}
		adminlog('会员升级商家管理','列表管理');
		cls_message::show('批量操作完成',"?entry=$entry$extend_str&action=$action&page=$page$filterstr");
	
	}
}elseif($action == 'mtrandetail' && $trid){
	if(!$row = $db->fetch_one("SELECT * FROM {$tblprefix}mtrans WHERE trid='$trid' AND checked='0'")) cls_message::show('请选择申请记录');
	$contentarr = empty($row['contentarr']) ? array() : unserialize($row['contentarr']);
	unset($row['contentarr']);
	
	$ochid = $row['fromid'];
	$mchid = $row['toid'];  //$mchannels[$row['toid']]['cname']
	$mchannel = $mchannels[$mchid]['cname'];  //echo $mchannel;
	$mfields = cls_cache::Read('mfields',$mchid);
	
	// 问答专家-处理字段
	$mfexp = array('dantu','ming','danwei','quaere');
	foreach($mfexp as $k){//后台架构字段
		unset($mfields[$k]);
	}
	// 排除会员认证字段
	foreach($mctypes as $k => $v){
		if(strstr(",$v[mchids],",",$mchid,")){ //允许的会员模型
			unset($mfields[$v['field']]);
		}
	}
	
	if(!submitcheck('bsubmit')){
		tabheader("$row[mname] 升级为 $mchannel",$actionid,"?entry=$entry$extend_str&action=mtrandetail&trid=$trid",2,1,1);
		trbasic('申请时间','',date("Y-m-d H:m",$row['createdate'] ? $row['createdate'] : $timestamp),'');
		trbasic('附加说明','',empty($row['remark']) ? '' : $row['remark'],'textarea',array('guide' => '不可更改'));
		trbasic('管理员回复','fmdata[reply]',empty($row['reply']) ? '' : $row['reply'],'textarea');
		tabfooter();
		
		tabheader('详细设置');
		$a_field = new cls_field;
		function mainpro(){
		}
		foreach($mfields as $k => $field){
			if(!$field['issystem']){
				empty($contentarr[$k]) || $contentarr[$k] = stripslashes($contentarr[$k]);
				$a_field->init($field,empty($contentarr[$k]) ? '' : $contentarr[$k]);
				$a_field->trfield('fddata');
			}
		}
		mainpro();
		tabfooter('bsubmit');
	}else{
		$c_upload = new cls_upload;	
		$a_field = new cls_field;
		foreach($mfields as $k => $v){
			if(!$v['issystem'] && isset($fddata[$k])){
				empty($contentarr[$k]) || $contentarr[$k] = stripslashes($contentarr[$k]);
				$a_field->init($v,empty($contentarr[$k]) ? '' : $contentarr[$k]);
				$fddata[$k] = $a_field->deal('fddata','amessage',M_REFERER);
			}
		}
		unset($a_field);
		
		$fmdata['reply'] = trim($fmdata['reply']);
		$fddata = empty($fddata) ? '' : addslashes(serialize($fddata));
		$db->query("UPDATE {$tblprefix}mtrans SET contentarr='$fddata',reply='$fmdata[reply]' WHERE trid='$trid'");
		$c_upload->closure(1,$memberid,'members');
		$c_upload->saveuptotal(1);
		adminlog('编辑会员升级申请');
		cls_message::show('申请记录编辑完成',axaction(6,M_REFERER));
	}
}
?>
