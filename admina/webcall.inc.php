<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('webparam')) cls_message::show($re);

empty($webcall_enable) && cls_message::show('请先启用400电话，网站参数->手机和邮箱->400电话设置');
$keyword = empty($keyword) ? '' : $keyword;
$outdays = empty($outdays) ? 0 : $outdays;
$indays = empty($indays) ? 0 : $indays;
$kw_type = empty($kw_type) ? 0 : $kw_type;

$chid = 1; $mchid = 1;
$id = empty($id) ? 0 : max(0,intval($id));
$page = !empty($page) ? max(1, intval($page)) : 1;
$checked = isset($checked) ? $checked : '-9';
$wheresql = "WHERE 1=1";
//if($valid != '-1') $wheresql .= $valid ? " AND state = 1" : " AND state = 0";
$fromsql = "FROM {$tblprefix}webcall a INNER JOIN {$tblprefix}members m ON m.mid=a.mid ";

if($checked != -9) $wheresql .= " AND a.state='$checked'";
//搜索关键词处理
if(!empty($keyword)){
	if($kw_type == 1) $wheresql .= " AND a.extcode = '$keyword' ";
	else if($kw_type == 2) $wheresql .= " AND a.mid = '$keyword' ";
	else $wheresql .= " AND a.mname LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%'";
}

!empty($indays) && $wheresql .= " AND a.createdate>'".($timestamp - 86400 * $indays)."'";
!empty($outdays) && $wheresql .= " AND a.createdate<'".($timestamp - 86400 * $outdays)."'";
//echo $mconfigs['webcall_big'];

$filterstr = '';
foreach(array('mchid','indays','outdays','keyword','kw_type') as $k) !empty($$k) && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
foreach(array('checked',) as $k) $$k != -9 && $filterstr .= "&$k=".$$k;

if(!isset($option)){
	echo form_str('webcall',"?entry=$entry&page=$page");
	tabheader_e();
	//trhidden('mchid',$mchid);
	echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	echo "<select name=\"kw_type\"><option value=0 ".($kw_type==0?'selected':'').">用户名</option><option value=2 ".($kw_type==2?'selected':'').">用户编号</option><option value=1 ".($kw_type==1?'selected':'').">分机号</option></select>";
	echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索用户名或分机号\">&nbsp; ";
	
	echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\" title=\"注册\">天前&nbsp; ";
	echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\" title=\"注册\">天内&nbsp; ";

	echo strbutton('bfilter','筛选');
	echo "</td></tr>";
	tabfooter();
	$alink = array('-9'=>'全部','-1'=>'未通过','1'=>'已审核','0'=>'待审核');
	$slink = '';
	foreach($alink as $k=>$v){
		if($k==$checked){
			$slink .= "$v&nbsp; &nbsp;";
		}else{
			$slink .= "<a href=\"?entry=$entry&checked=$k\">$v</a>&nbsp; &nbsp;";
		}
	}
	tabheader("<span>400电话管理 >> </span>$slink</div> ", 'webcall', '?entry=$entry');
	trcategory(array('分机号','会员(点击查看空间)','状态','创建日期','审核日期','管理'));
	$pagetmp = $page; //echo "SELECT a.* $fromsql $wheresql";
	do{
		$query = $db->query("SELECT a.* $fromsql $wheresql ORDER BY a.id DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		$pagetmp--;
	}while(!$db->num_rows($query) && $pagetmp);
	$itemstr = '';
	while($r = $db->fetch_array($query)){
		$extcodestr = empty($r['extcode']) ? '未分配' : $r['extcode'];
		$memberstr = $r['mname'];
		switch($r['state']){
			case -1:
				$statestr = '<span style="color: red;">未通过</span>';
				break;
			case 0:
				$statestr = '审核中';
				break;
			case 1:
				$statestr = '<span style="color: green;">已审核</span>';
				break;
		}
		$createdatestr = $r['createdate'] ? date('Y-m-d',$r['createdate']) : '-';
		$checkdatestr = $r['checkdate'] ? date('Y-m-d',$r['checkdate']) : '-';
		$editstr = "<a href=\"?entry=$entry&option=check&id={$r['id']}\" onclick=\"return floatwin('open_check',this)\">审核</a> &nbsp; <a href=\"?entry=$entry&option=delete&id={$r['id']}\" onclick=\"return deltip()\">删除</a>";

		$itemstr .= "<tr><td class=\"txt\">$extcodestr</td>\n";
		$itemstr .= "<td class=\"txt\"><a href=".cls_Mspace::IndexUrl($r)." target=\"_blank\">$memberstr</a></td>\n";
		$itemstr .= "<td class=\"txt\">$statestr</td>\n";
		$itemstr .= "<td class=\"txt\">$createdatestr</td>\n";
		$itemstr .= "<td class=\"txt\">$checkdatestr</td>\n";
		$itemstr .= "<td class=\"txt\">$editstr</td>\n";
		$itemstr .= "</tr>\n";
	}
	$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$multi = multi($counts,$mrowpp,$page,"?entry=$entry");
	echo $itemstr;
	tabfooter();
	echo $multi;
	a_guide('webcall');
} elseif($option=='check') {
	$id && $wheresql .= " AND a.id=$id";
	$r = $db->fetch_one("SELECT a.* $fromsql $wheresql");

	if(!submitcheck('bsubmit')){
		$createdatestr = $r['createdate'] ? date('Y-m-d',$r['createdate']) : '-';
		$checkdatestr = $r['checkdate'] ? date('Y-m-d',$r['checkdate']) : '-';
		tabheader('网站总机&nbsp; -&nbsp; 修改分机','webcalladd',"?entry=$entry&option=$option&id=$id",2,1,1);

		trhidden('fmdata[mid]',$r['mid']);
		trbasic('企业名称','fmdata[suppliername]',$r['suppliername'],'');
		trbasic('企业地址','fmdata[address]',$r['address'],'');
		trbasic('邮编','fmdata[postcode]',$r['postcode'],'');
		$r['state']!=1 && trbasic('管理帐号','fmdata[username]',$r['username'],'text');
		$r['state']!=1 && trbasic('管理密码','fmdata[pwd]',$r['pwd'],'');
		trbasic('联系人','fmdata[contactman]',$r['contactman'],'');
		trbasic('性别','fmdata[contactman]',($r['sex']==1?'男':'女'),'');
		trbasic('身份证','fmdata[contactidcard]',$r['contactidcard'],'');
		trbasic('联系电话','fmdata[phone]',$r['phone'],'');
		trbasic('联系手机','fmdata[mobilephone]',$r['mobilephone'],'');
		trbasic('电子邮箱','fmdata[contactmail]',$r['contactmail'],'');
		trbasic('法人','fmdata[artiperson]',$r['artiperson'],'');
		trbasic('营业执照号码','fmdata[licence]',$r['licence'],'');
		trbasic('税务登记号','fmdata[taxnumber]',$r['taxnumber'],'');

		trbasic('分机号码','fmdata[extcode]',$r['extcode']);
		echo '<tr><td width="25%" class="txt txtright fB borderright">状态</td><td class="txt txtleft"><input type="radio"'.($r['state']==-1?' checked=""':'').' value="-1" name="fmdata[state]" id="fmdata[state]_-1" class="radio"><label for="fmdata[state]_-1"> 未通过</label> &nbsp; &nbsp; <input type="radio"'.($r['state']==0?' checked=""':'').' value="0" name="fmdata[state]" id="fmdata[state]_0" class="radio"><label for="fmdata[state]_0"> 审核中</label> &nbsp; &nbsp; <input type="radio"'.($r['state']==1?' checked=""':'').' value="1" name="fmdata[state]" id="fmdata[state]_1" class="radio"><label for="fmdata[state]_1"> 已审核</label> </td></tr>';
		trbasic('创建日期','',$createdatestr,'');
		trbasic('审核日期','',$checkdatestr,'');
		/*/trbasic('400电话链接','fmdata[webcallurl]',(empty($r['webcallurl']) ? "<script type=\"text/javascript\">
var jytx_m='5440080408073019450';var jytx_img=typeof(img400) == 'undefined'?'http://user.port400.com/CallPic/Big/200904081119176275.gif':img400;var jytx_t='y';var jytx_page='PPCall/PPCall.aspx';var jytx_s='y';</script>
<script src=\"http://customer.port400.com/Message/codenew.js\"></script>" : $r['webcallurl']) ,'textarea');*/
		trhidden('fmdata[webcallurl]',''); //暂不删除,先清空这个字段
		trbasic('备注','fmdata[remark]',$r['remark'],'textarea');

		tabfooter('bsubmit','提交');

	} else {
		$db->query("UPDATE {$tblprefix}webcall SET extcode='{$fmdata['extcode']}', state='{$fmdata['state']}', webcallurl='{$fmdata['webcallurl']}', remark='{$fmdata['remark']}', checkdate='$timestamp'".($fmdata['state']==1?", pwd=''":"")." WHERE id='$id'");
		if(!empty($fmdata['extcode']) && $fmdata['state']==1){
			$webcall = $mconfigs['webcall_big'].'-'.$fmdata['extcode'];
		}else{
			$webcall = '';	
		}

		$db->query("UPDATE {$tblprefix}members SET webcall='$webcall',webcallurl='{$fmdata['webcallurl']}' WHERE mid='{$fmdata['mid']}'");

		cls_message::show('审核内容成功', axaction(6,M_REFERER));
	}
} elseif($option=='delete') {
	$r = $db->fetch_one("SELECT mid,state FROM {$tblprefix}webcall WHERE id='$id'");
	if($r['state']==1){
		$db->query("UPDATE {$tblprefix}members SET webcall='',webcallurl='' WHERE mid='{$r['mid']}'");
	}
	$db->query("DELETE FROM {$tblprefix}webcall WHERE id='$id'");

	cls_message::show('删除成功', axaction(6,M_REFERER));
}
?>
