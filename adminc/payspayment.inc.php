<?PHP
!defined('M_COM') && exit('No Permission');
if($curuser->getTrusteeshipInfo()) cls_message::show('您是代管用户，当前操作仅原用户本人有权限！');
$currencys = cls_cache::Read('currencys');
$pay_fields = array('alipay' => 'alipay_v33', 'tenpay' => 'tenpay');
$pmodearr = array('0' => '上门支付','1' => '在线支付','2' => '银行转账','3' => '邮局汇款');
$poids = _08_factory::getInstance(_08_Loader::MODEL_PREFIX . 'PayGate_Pays')->getPays();
$dateformat = "Y-m-d H:m:i";
if(empty($pid)){
	backnav('payonline','payrecord');
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$pmode = isset($pmode) ? $pmode : '-1';
	$receive = isset($receive) ? $receive : '-1';
	$trans = isset($trans) ? $trans : '-1';
	$poid = empty($poid) ? '' : $poid;

	$wheresql = "WHERE mid=$memberid";
	if($pmode != '-1') $wheresql .= ($wheresql ? " AND " : "")."pmode='$pmode'";
	if($receive != '-1') $wheresql .= ($wheresql ? " AND " : "")."receivedate".($receive ? '>' : '=')."0";
	if($trans != '-1') $wheresql .= ($wheresql ? " AND " : "")."transdate".($trans ? '>' : '=')."0";
	if(!empty($poid)) $wheresql .= ($wheresql ? " AND " : "")."poid='$poid'";

	$filterstr = '';
	foreach(array('pmode','trans','receive','poid') as $k) $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	if(!submitcheck('barcsedit')){
		$pmodearr = array('-1' => '支付方式') + $pmodearr;
		$receivearr = array('-1' => '到账状态','0' => '未到账','1' => '已到账');
		#$transarr = array('-1' => '充值状态','0' => '未充值','1' => '已充值');
		$poidsarr = array('' => '支付接口') + $poids;
		echo form_str($action.'arcsedit',"?action=$action&page=&page");
		tabheader_e();
		echo "<tr><td class=\"item2\">";
		echo "<select style=\"vertical-align: middle;\" name=\"receive\">".makeoption($receivearr,$receive)."</select>&nbsp; ";
		#echo "<select style=\"vertical-align: middle;\" name=\"trans\">".makeoption($transarr,$trans)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"pmode\">".makeoption($pmodearr,$pmode)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"poid\">".makeoption($poidsarr,$poid)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选').'</td></tr>';
		tabfooter();
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}pays_payment $wheresql ORDER BY pid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$stritem = '';
		while($item = $db->fetch_array($query)){
			$pid = $item['pid'];
            if ( array_key_exists($item['poid'], $poids) )
            {
                $item['pmode'] = 1;
            }
			$pmodestr = $pmodearr[$item['pmode']];
			$poidstr = empty($poids[$item['poid']]) ? '-' : $poids[$item['poid']];
			$sendstr = date("$dateformat",$item['senddate']);
			$receivestr = empty($item['receivedate']) ? '-' : date("$dateformat",$item['receivedate']);
			$transstr = empty($item['transdate']) ? '-' : date("$dateformat",$item['transdate']);
			$stritem .= "<tr><td class=\"item\" width=\"30\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$pid]\" value=\"$pid\"></td>\n".
				"<td class=\"item2\">$pmodestr</td>\n".
				"<td class=\"item\" width=\"80\">$item[amount]</td>\n".
				"<td class=\"item\" width=\"60\">$poidstr</td>\n".
				"<td class=\"item\" width=\"70\">$sendstr</td>\n".
				"<td class=\"item\" width=\"70\">$receivestr</td>\n".
				#"<td class=\"item\" width=\"70\">$transstr</td>\n".
				"<td class=\"item\" width=\"30\"><a href=\"?action=payspayment&pid=$pid\" onclick=\"return floatwin('open_pays',this)\">查看</a></td></tr>\n";
		}
		$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}pays_payment $wheresql");
		$multi = multi($counts, $mrowpp, $page, "?action=pays$filterstr");

		tabheader('支付记录列表','','',9);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('支付方式','item2'),'支付数量','支付接口','记录日期','到帐日期','详情'));
		echo $stritem;
		tabfooter();
		echo $multi;
		tabfooter('barcsedit','删除');
		m_guide("pay_notes",'fix');
	}else{
		empty($selectid) && cls_message::show('请选择支付记录',"?action=pays&page=$page$filterstr");
		$db->query("DELETE FROM {$tblprefix}pays_payment WHERE mid=$memberid AND pid ".multi_str($selectid)." AND receivedate=0",'SILENT');
		cls_message::show('现金信息删除成功',"?action=pays&page=$page$filterstr");
	}
}else{
	$forward = empty($forward) ? M_REFERER : $forward;
	$pid = (int)$pid;
	empty($pid) && cls_message::show('请指定正确的支付',$forward);
	if(!$item = $db->fetch_one("SELECT * FROM {$tblprefix}pays_payment WHERE pid='$pid'")) cls_message::show('请指定正确的支付记录');
	if(!submitcheck('bpaydetail')){
		if(!$item['transdate']){
			tabheader('修改支付信息','paydetail','?action=payspayment&pid='.$pid.'&forward='.rawurlencode($forward),2,1);
		}else{
			tabheader('查看支付信息');
		}
		trbasic('会员名称','',$item['mname'],'');
		trbasic('支付方式','',$pmodearr[$item['pmode']],'');
		trbasic('支付数量(人民币)','',$item['amount'],'');
		trbasic('手续费(人民币)','',$item['handfee'],'');
		trbasic('支付接口','',$item['poid'] ? $poids[$item['poid']] : '-','');
		trbasic('支付订单号','',$item['ordersn'] ? $item['ordersn'] : '-','');
		trbasic('信息发送时间','',date("$dateformat",$item['senddate']),'');
		trbasic('现金到帐时间','',$item['receivedate'] ? date("$dateformat",$item['receivedate']) : '-','');
		#trbasic('积分充值时间','',$item['transdate'] ? date("$dateformat $timeformat",$item['transdate']) : '-','');
		trbasic('联系人名字','itemnew[truename]',$item['truename']);
		trbasic('联系电话','itemnew[telephone]',$item['telephone']);
		trbasic('联系Email','itemnew[email]',$item['email']);
		trbasic('备注','itemnew[remark]',$item['remark'],'textarea');
		trspecial('支付凭证'."&nbsp; &nbsp; ["."<a href=\"".$item['warrant']."\" target=\"_blank\">".'大图'."</a>"."]",specialarr(array('type' => 'image','varname' => 'itemnew[warrant]','value' => $item['warrant'],)));
		if($item['transdate']){
			tabfooter();
		}else{
			tabfooter('bpaydetail','修改');
		}
		m_guide("pay_notes",'fix');
	}else{
		if($item['transdate']) cls_message::show('已支付信息不能修改');
		$itemnew['truename'] = trim(strip_tags($itemnew['truename']));
		$itemnew['telephone'] = trim(strip_tags($itemnew['telephone']));
		$itemnew['email'] = trim(strip_tags($itemnew['email']));
		$itemnew['remark'] = trim($itemnew['remark']);
		$c_upload = cls_upload::OneInstance();
		$itemnew['warrant'] = upload_s($itemnew['warrant'],$item['warrant'],'image');
		$c_upload->saveuptotal(1);
		$db->query("UPDATE {$tblprefix}pays_payment SET
					 truename='$itemnew[truename]',
					 telephone='$itemnew[telephone]',
					 email='$itemnew[email]',
					 remark='$itemnew[remark]',
					 warrant='$itemnew[warrant]'
					 WHERE pid='$pid'
					 ");
		$c_upload->closure(1, $pid, 'pays');
		cls_message::show('支付信息修改完成',axaction(6,$forward));
	}
}
?>
