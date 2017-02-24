<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
$currencys = cls_cache::Read('currencys');
$pmodearr = array('0' => '上门支付','1' => '在线支付','2' => '银行转账','3' => '邮局汇款');
$poids = _08_factory::getInstance(_08_Loader::MODEL_PREFIX . 'PayGate_Pays')->getPays();
if($action == 'paysedit'){
	backnav('cysave','pays');
	if($re = $curuser->NoBackFunc('pay')) cls_message::show($re);
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$viewdetail = empty($viewdetail) ? '' : $viewdetail;
	$pmode = isset($pmode) ? $pmode : '-1';
	$receive = isset($receive) ? $receive : '-1';
	$trans = isset($trans) ? $trans : '-1';
	$poid = empty($poid) ? '' : $poid;
	$mname = empty($mname) ? '' : $mname;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));

	$filterstr = '';
	foreach(array('pmode','trans','receive','poid','mname','indays','outdays') as $k){
		$filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	}

	$wheresql = '';
	if($pmode != '-1') $wheresql .= ($wheresql ? " AND " : "")."pmode='$pmode'";
	if($receive != '-1') $wheresql .= ($wheresql ? " AND " : "")."receivedate".($receive ? '>' : '=')."0";
	if($trans != '-1') $wheresql .= ($wheresql ? " AND " : "")."transdate".($trans ? '>' : '=')."0";
	if(!empty($poid)) $wheresql .= ($wheresql ? " AND " : "")."poid='$poid'";
	if(!empty($mname)) $wheresql .= ($wheresql ? " AND " : "")."mname ".sqlkw($mname);
	if(!empty($indays)) $wheresql .= ($wheresql ? " AND " : "")."senddate>'".($timestamp - 86400 * $indays)."'";
	if(!empty($outdays)) $wheresql .= ($wheresql ? " AND " : "")."senddate<'".($timestamp - 86400 * $outdays)."'";
	$wheresql = $wheresql ? "WHERE $wheresql" : '';

	if(!submitcheck('barcsedit')){
		$pmodearr = array('-1' => '支付方式') + $pmodearr;
		$receivearr = array('-1' => '到账状态','0' => '未到账','1' => '已到账');
		$transarr = array('-1' => '充值状态','0' => '未充值','1' => '已充值');
		$poidsarr = array('' => '支付接口') + $poids;
		echo form_str($actionid.'arcsedit',"?entry=pays&action=paysedit&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"mname\" type=\"text\" value=\"$mname\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索支付会员\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"receive\">".makeoption($receivearr,$receive)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"trans\">".makeoption($transarr,$trans)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"pmode\">".makeoption($pmodearr,$pmode)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"poid\">".makeoption($poidsarr,$poid)."</select>&nbsp; ";
		echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();
		
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}pays $wheresql ORDER BY pid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$stritem = '';
		while($item = $db->fetch_array($query)){
			$pid = $item['pid'];
			$pmodestr = $pmodearr[$item['pmode']];
			$poidstr = empty($item['poid']) ? '-' : @$poids[$item['poid']];
			$sendstr = date("$dateformat",$item['senddate']);
			$receivestr = empty($item['receivedate']) ? '-' : date("$dateformat",$item['receivedate']);
			$transstr = empty($item['transdate']) ? '-' : date("$dateformat",$item['transdate']);
			$stritem .= "<tr class=\"txt\"><td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$pid]\" value=\"$pid\"></td>\n".
				"<td class=\"txtL\">$item[mname]</td>\n".
				"<td class=\"txtC w80\">$item[amount]</td>\n".
				"<td class=\"txtC w80\">$pmodestr</td>\n".
				"<td class=\"txtC w80\">$poidstr</td>\n".
				"<td class=\"txtC w80\">$sendstr</td>\n".
				"<td class=\"txtC w80\">$receivestr</td>\n".
				"<td class=\"txtC w80\">$transstr</td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=pays&action=paydetail&pid=$pid\" onclick=\"return floatwin('open_pays',this)\">查看</a></td></tr>\n";
		}
		$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}pays $wheresql");
		$multi = multi($counts, $atpp, $page, "?entry=pays&action=paysedit$filterstr");
		
		tabheader('会员支付管理 &nbsp;>><a href="?entry=mconfigs&action=cfpay&isframe=1" target="_blank">支付配置</a>','','',9);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('支付会员','txtL'),'支付数量','支付模式','支付接口','记录日期','到帐日期','充值日期','详情'));
		echo $stritem;
		tabfooter();
		echo $multi;
		
		$receivearr = array('0' => '未到账','1' => '已到账');
		tabheader('操作项目');
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\">&nbsp;删除支付记录",'','仅未到账或已充值的支付记录才能删除','');
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[receive]\" value=\"1\">&nbsp;设置到帐状态",'arcreceive',makeradio('arcreceive',$receivearr,1),'');
		trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[trans]\" value=\"1\">&nbsp;为会员现金帐户充值",'','支付到帐才能充值','');
		tabfooter('barcsedit');
	}else{
		if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=pays&action=paysedit&page=$page$filterstr");
		if(empty($selectid)) cls_message::show('请选择支付记录',"?entry=pays&action=paysedit&page=$page$filterstr");
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}pays WHERE pid ".multi_str($selectid)." AND (receivedate=0 OR transdate>0)",'SILENT');
		}else{
			if(!empty($arcdeal['receive'])){
				$db->query("UPDATE {$tblprefix}pays SET receivedate='".(empty($arcreceive) ? 0 : $timestamp)."' WHERE pid ".multi_str($selectid)." AND transdate=0",'SILENT');
			}
			if(!empty($arcdeal['trans'])){
				$auser = new cls_userinfo;
				$query = $db->query("SELECT * FROM {$tblprefix}pays WHERE pid ".multi_str($selectid));
				while($item = $db->fetch_array($query)){
					if(!$item['amount'] || !$item['receivedate'] || $item['transdate']) continue;
					$auser->activeuser($item['mid']);
					$auser->updatecrids(array(0 => $item['amount']),1,'现金充值');
					$db->query("UPDATE {$tblprefix}pays SET transdate='$timestamp' WHERE pid='$item[pid]'",'SILENT');
					$auser->init();
				}
				unset($actuser);
			}
		}
		adminlog('现金充值管理','支付充值列表管理操作');
		cls_message::show('现金充值管理操作完成',"?entry=pays&action=paysedit&page=$page$filterstr");
	}
}
elseif($action == 'paydetail' && $pid){
	if($re = $curuser->NoBackFunc('pay')) cls_message::show($re);
	$forward = empty($forward) ? M_REFERER : $forward;
	empty($pid) && cls_message::show('请指定正确的支付',$forward);
	if(!$item = $db->fetch_one("SELECT * FROM {$tblprefix}pays WHERE pid=$pid")) cls_message::show('请指定正确的支付记录',$forward);
	if(!submitcheck('bpaydetail')){
		if(!$item['transdate']){
			tabheader('支付信息修改','paydetail','?entry=pays&action=paydetail&pid='.$pid.'&forward='.rawurlencode($forward),2,1);
		}else{
			tabheader('支付信息查看');
		}
		trbasic('会员名称','',$item['mname'],'');
		trbasic('支付模式','',$pmodearr[$item['pmode']],'');
		trbasic('支付数量(人民币)','itemnew[amount]',$item['amount']);
		trbasic('手续费(人民币)','',$item['handfee'],'');
		trbasic('支付接口','',$item['poid'] ? @$poids[$item['poid']] : '-','');
		trbasic('支付订单号','',$item['ordersn'] ? $item['ordersn'] : '-','');
		trbasic('信息发送时间','',date("$dateformat $timeformat",$item['senddate']),'');
		trbasic('现金到帐时间','',$item['receivedate'] ? date("$dateformat $timeformat",$item['receivedate']) : '-','');
		trbasic('积分充值时间','',$item['transdate'] ? date("$dateformat $timeformat",$item['transdate']) : '-','');
		trbasic('联系人姓名','itemnew[truename]',$item['truename']);
		trbasic('联系电话','itemnew[telephone]',$item['telephone']);
		trbasic('联系Email','itemnew[email]',$item['email']);
		trbasic('备注','itemnew[remark]',$item['remark'],'textarea');
		trspecial('支付凭证'."&nbsp; &nbsp; ["."<a href=\"".$item['warrant']."\" target=\"_blank\">".'大图'."</a>"."]",specialarr(array('type' => 'image','varname' => 'itemnew[warrant]','value' => $item['warrant'],)));
		if($item['transdate']){
			tabfooter();
			echo "<input class=\"button\" type=\"submit\" name=\"\" value=\"返回\" onclick=\"history.go(-1);\">";
		}else{
			tabfooter('bpaydetail','修改');
		}
		a_guide('paydetail');
	}else{
		$itemnew['amount'] = max(0,round(floatval($itemnew['amount']),2));
		empty($itemnew['amount']) && cls_message::show('请输入支付数量',M_REFERER);
		$itemnew['truename'] = trim(strip_tags($itemnew['truename']));
		$itemnew['telephone'] = trim(strip_tags($itemnew['telephone']));
		$itemnew['email'] = trim(strip_tags($itemnew['email']));
		$itemnew['remark'] = trim($itemnew['remark']);
		$c_upload = cls_upload::OneInstance();	
		$itemnew['warrant'] = upload_s($itemnew['warrant'],$item['warrant'],'image');
		$c_upload->closure(1, $pid, 'pays');
		$c_upload->saveuptotal(1);
		$db->query("UPDATE {$tblprefix}pays SET
					 amount='$itemnew[amount]',
					 truename='$itemnew[truename]',
					 telephone='$itemnew[telephone]',
					 email='$itemnew[email]',
					 remark='$itemnew[remark]',
					 warrant='$itemnew[warrant]' 
					 WHERE pid='$pid'
					 ");
		cls_message::show('支付信息修改完成',axaction(6,$forward));
	}
}