<?php
(defined('M_COM') && defined('M_ADMIN')) || exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('member')) cls_message::show($re);
foreach(array('mctypes','mchannels',) as $k) $$k = cls_cache::Read($k);
$mcmodearr = array(0 => '普通',1 => '手机',);
$mctidsarr = array();foreach($mctypes as $k => $v) $mctidsarr[$k] = $v['cname'];
if(empty($action)){
	$mchid = empty($mchid) ? 0 : max(0,intval($mchid));
	$mctid = !isset($mctid) ? -1 : max(-1,intval($mctid));
	$page = empty($page) ? 1 : max(1, intval($page));
	submitcheck('bfilter') && $page = 1;
	$checked = isset($checked) ? $checked : '-1';
	$keyword = empty($keyword) ? '' : $keyword;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));
	
	$selectsql = "SELECT mc.*,m.mchid";
	$wheresql = "";
	$fromsql = "FROM {$tblprefix}mcerts mc INNER JOIN {$tblprefix}members m ON m.mid=mc.mid";
	
	$mchid && $wheresql .= " AND m.mchid='$mchid'";
	if($mctid != -1) $wheresql .= " AND mc.mctid='$mctid'";
	$keyword && $wheresql .= " AND mc.mname ".sqlkw($keyword);
	if($checked != -1) $wheresql .= $checked ? " AND mc.checkdate<>0" : " AND mc.checkdate=0";
	$indays && $wheresql .= " AND mc.createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND mc.createdate<'".($timestamp - 86400 * $outdays)."'";
	
	$filterstr = '';
	foreach(array('mchid','keyword','indays','outdays',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('mctid','checked',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;
	
	$wheresql = $wheresql ? 'WHERE '.substr($wheresql,5) : '';
	if(!submitcheck('bsubmit')){
		echo form_str($actionid.'arcsedit',"?entry=$entry&page=$page");
		trhidden('mchid',$mchid);
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索会员\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"mctid\">".makeoption(array(-1 => '不限类型') + $mctidsarr,$mctid)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('-1' => '审核状态','0' => '未审','1' => '已审'),$checked)."</select>&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		tabheader('认证申请管理','','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('认证会员','txtL'),);
		$cy_arr[] = array('认证类型','txtL');
		$cy_arr[] = '会员类型';
		$cy_arr[] = '申请时间';
		$cy_arr[] = '审核时间';
		$cy_arr[] = '编辑';
		trcategory($cy_arr);
		
		$pagetmp = $page;
		do{
			$query = $db->query("$selectsql $fromsql $wheresql ORDER BY mc.mcid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[mcid]]\" value=\"$r[mcid]\">";
			$mnamestr = $r['mid'] ? "<a href=\"{$mspaceurl}index.php?mid=$r[mid]\" target=\"_blank\">$r[mname]</a>" : $r['mname'];
			$mctypestr = @$mctypes[$r['mctid']]['cname'];
			$mchannelstr = @$mchannels[$r['mchid']]['cname'];
			$adddatestr = date('Y-m-d',$r['createdate']);
			$checkdatestr = $r['checkdate'] ? date('Y-m-d',$r['checkdate']) : '-';
			$editstr = "<a href=\"?entry=$entry&action=detail&mcid=$r[mcid]\" onclick=\"return floatwin('open_commentsedit',this)\">详情</a>";
	
			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td><td class=\"txtL\">$mnamestr</td>\n";
			$itemstr .= "<td class=\"txtL\">$mctypestr</td>\n";
			$itemstr .= "<td class=\"txtC\">$mchannelstr</td>\n";
			$itemstr .= "<td class=\"txtC w100\">$adddatestr</td>\n";
			$itemstr .= "<td class=\"txtC w100\">$checkdatestr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$editstr</td>\n";
			$itemstr .= "</tr>\n";
		}
		echo $itemstr;
		tabfooter();
		echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$atpp,$page,"?entry=$entry$filterstr");
		
		tabheader('批量操作');
		$s_arr = array();
		$s_arr['delete'] = '删除';
		//$s_arr['check'] = '审核';
		//$s_arr['uncheck'] = '解审并删除';
		if($s_arr){
			$str = '';
			$i = 1;
			foreach($s_arr as $k => $v){
				$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"" . ($k == 'delete' ? ' onclick="deltip()"' : '') . ">$v &nbsp;";
				if(!($i % 5)) $str .= '<br>';
				$i ++;
			}
			trbasic('选择操作项目','',$str,'');
		}
		tabfooter('bsubmit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
		if(empty($selectid)) cls_message::show('请选择评论记录。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}mcerts WHERE mcid='$k'",'UNBUFFERED');
				continue;
			}
		}
		adminlog('会员认证列表管理');
		cls_message::show('会员认证批量操作成功。',"?entry=$entry&page=$page$filterstr");
		
		
	}
}elseif($action == 'detail'){
	if(empty($mcid) || !($row = $db->fetch_one("SELECT * FROM {$tblprefix}mcerts WHERE mcid='$mcid'"))) cls_message::show('无效的认证请求！');
	$au = new cls_userinfo;
	$au->activeuser($row['mid']);
	$mchid = $au->info['mchid'];
	$mfields = cls_cache::Read('mfields',$mchid);
	if(!($mctid = $row['mctid']) || !($mctype = @$mctypes[$mctid])) cls_message::show('无效的认证类型。');
	if(!$mctype['available'] || !in_array($mchid,explode(',',$mctype['mchids'])) || !isset($mfields[$mctype['field']])) cls_message::show('无效的认证类型。');
	$mcfield = &$mfields[$mctype['field']];
	$a_field = new cls_field;
	$a_field->init($mcfield,$row['content']);
	if(!submitcheck('bsubmit')){
		tabheader("认证内容 - $mctype[cname]", 'memcert_need', "?entry=$entry&action=$action&mcid=$mcid",2,1,1);
		$a_field->trfield('fmdata');
		if($mctype['mode'] == 1 && $row['msgcode']) trbasic('手机确认码','',$row['msgcode'],'');
		if($mcfield['datatype'] == 'image' ){
		?>
			<script type="text/javascript">
				function setImgSize(obj,w,h){
					img = new Image(); img.src = obj.src;
					zw = img.width; zh = img.height;
					zr = zw / zh;
					if(w){ fixw = w; }
					else { fixw = obj.getAttribute('width'); }
					if(h){ fixh = h; }
					else { fixh = obj.getAttribute('height'); }
					if(zw > fixw) {
						zw = fixw; zh = zw/zr;
					}
					if(zh > fixh) {
						zh = fixh; zw = zh*zr;
					}
					obj.width = zw; obj.height = zh;
				}
			</script>
		<?php		
			$image_url = view_checkurl($row['content']);
			echo "<div style=\"width:600px; margin-left:85px; margin-top:50px;\">";			
			echo "<a href=\"".$image_url."\" target=\"_blank\"><img alt=\"".$image_url."\" onload=setImgSize(this,500,500) src=\"".$image_url."\"></a><br/><br/>";
			echo "<span style=\"color:#999999;\">（倘若图片不清晰，可点击图片另行观看。）</span><br/><br/>";
			echo "</div>";
		}
		tabfooter('bsubmit',$row['checkdate'] ? '解除认证' : '审核认证');
	}elseif($row['checkdate']){
		#解审
		#$au->updatefield($mctype['field'],'',$a_field->field['tbl']);
		$au->updatefield("mctid$mctid",0);
		if($mctype['award'])$au->updatecrids(array($mctype['crid'] => -$mctype['award']),0,"$mctype[cname] 扣分");
		$au->updatedb();
		
		$db->query("DELETE FROM {$tblprefix}mcerts WHERE mcid='$mcid'");
		cls_message::show('解审认证完成', axaction(6,"?entry=$entry"));
	}else{
		$content = $a_field->deal('fmdata','cls_message::show',M_REFERER);
		
		$au->updatefield($mctype['field'],$content,$a_field->field['tbl']);
		$au->updatefield("mctid$mctid",$mctid);
		if($mctype['award']) $au->updatecrids(array($mctype['crid'] => $mctype['award']),0,"$mctype[cname] 加分");
		$au->updatedb();
		
		$db->query("UPDATE {$tblprefix}mcerts SET checkdate='$timestamp',content='$content' WHERE mcid='$mcid'");
		cls_message::show('认证审核完成', axaction(6,"?entry=$entry"));
	}
}
?>