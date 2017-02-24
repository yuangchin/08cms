<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('smsapi')) cls_message::show($re);
$mchannels = cls_cache::Read('mchannels');
$grouptypes = cls_cache::Read('grouptypes');
$sms = new cls_sms(); //echo $sms->check_ipmax();
$sms_cfg_aset = $sms->cfga;

$defact = 'sendlog';
$action = empty($action) ? $defact : $action;
$page = !empty($page) ? max(1, intval($page)) : 1; 
$checked = empty($checked) ? 0 : $checked; 
$fclose = false;
$ermsg = ''; //错误信息
if($sms->isClosed()){
	$balance = array(-1,0);
	if($action!='setapi'){
		$fclose = true;
	}
	$sms_cfg_api = '(close)';
}else{
	$balance = $sms->getBalance();
	if(($balance[1]<=0) || ($sms->balanceWarn(5))){
		$defact = 'apiwarn';	
	}
	if(!empty($balance['msg'])) $ermsg = '('.$balance['msg'].')';
}
 
backnav('sms_admin',$action);
if($fclose) cls_message::show('请先开启[手机短信接口]：其他管理->手机短信->接口设置');

if($action=='sendlog'){

  $keyword = empty($keyword) ? '' : $keyword; 
  $keytype = empty($keytype) ? 'mname' : $keytype;
  $filterstr = $checked?"&checked=$checked":'';
  foreach(array('keyword','keytype') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE 1=1 "; //cu.mid='$memberid' commu_offer. archives1.
  $fromsql = "FROM {$tblprefix}sms_sendlogs ";
    
  if($keyword){
	  if($keytype){
	  	$wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }else{
	  	$wheresql .= " AND (tel ".sqlkw($keyword)." OR msg ".sqlkw($keyword).") ";
	  }
  }
  
  if(!submitcheck('bsubmit')){
	  
	  echo form_str('sendlogs',"?entry=$entry$filterstr&page=$page");
	  tabheader_e();
	  echo "<tr><td class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键词\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('0'=>'--筛选范围--','mname'=>'会员名','tel'=>'电话','msg'=>'内容','ip'=>'IP'),$keytype)."</select>&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader("短信发送记录",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  //$cy_arr[] = '发送时间';
	  $cy_arr[] = '发送电话|L';
	  $cy_arr[] = '信息内容|L';
	  $cy_arr[] = '会员';
	  $cy_arr[] = '发送ip/时间';
	  $cy_arr[] = '结果';
	  $cy_arr[] = '接口信息';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; $stype = array('sadm'=>'管理员发','scom'=>'会员发送','ctel'=>'其它认证',);
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
		  $time = date('Y-m-d H:i',$r['stamp']);
		  
		  $tel = str_replace(',',', ',$r['tel']);
		  if(strlen($tel)>72) $tel = substr($tel,0,64).'...'.substr($tel,strlen($tel)-15);
		  $msg = mhtmlspecialchars($r['msg']);
		  $res = (substr($r['res'],0,1)=='1' ? 'OK' : '失败')."<br>$r[cnt]条";
		  $a = explode('/',$r['api']); $key = substr($a[0],4);
		  $api_u = empty($stype[$a[1]]) ? '系统代发' : $stype[$a[1]];
		  $api = @$sms_cfg_aset[$a[0]]['name'].'<br>'.$api_u;
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtL w190\">$tel</td>\n";
		  $itemstr .= "<td class=\"txtL w240\">$msg</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[mname]</td>\n";
		  $itemstr .= "<td class=\"txtC w110\">$r[ip]<br>$time</td>\n";
		  $itemstr .= "<td class=\"txtC w50\">$res</td>\n";
		  $itemstr .= "<td class=\"txtC w60\">$api</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry&page=$page$filterstr");
  
	  tabheader('批量操作');
	  $str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"now\">删除记录 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m3\">删除3月前 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m1\">删除1月前 &nbsp;";
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除记录",'',$str,'');
	  //trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[checkf]\" value=\"1\"> 审核留言",'checkv',makeoption(array('1'=>'审核留言','0'=>'屏蔽留言')),'select');
	  tabfooter('bsubmit');
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($arcdeal_del)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	//echo "$arcdeal[delete],$arcdeal_del";
	if($arcdeal_del=='now'){
		if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&page=$page$filterstr");
		foreach($selectid as $k){
			$db->query("DELETE FROM {$tblprefix}sms_sendlogs WHERE cid='$k'",'UNBUFFERED');
			continue;
		}
	}elseif($arcdeal_del=='m3'){
		$sql = "DELETE FROM {$tblprefix}sms_sendlogs WHERE stamp<='".($timestamp-90*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}elseif($arcdeal_del='m1'){
		$sql = "DELETE FROM {$tblprefix}sms_sendlogs WHERE stamp<='".($timestamp-30*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}
	cls_message::show('记录批量操作成功'.'。',"?entry=$entry&page=$page$filterstr");
 }

}elseif($action=='chargelog'){

  $keyword = empty($keyword) ? '' : $keyword; 
  $keytype = empty($keytype) ? 'mname' : $keytype;
  $filterstr = $checked?"&checked=$checked":'';
  /*if(!empty($mid)){
	  $keyword = $db->result_one("SELECT mname FROM {$tblprefix}members WHERE mid='$mid'");
	  $keytype = 'mid'; //,'mid'
  }*/
  foreach(array('keyword','keytype','action') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE 1=1 "; //cu.mid='$memberid' commu_offer. archives1.
  $fromsql = "FROM {$tblprefix}sms_recharge ";
    
  /*if(!empty($mid)){ 
	  $kmname = $db->result_one("SELECT mname FROM {$tblprefix}members WHERE mid='$mid'");
	  $wheresql .= " AND (mname='$kmname') ";
	  $keyword = $kmname;
  }else*/
  if($keyword){
	  if($keytype=='mid'){
	 	 $kmname = $db->result_one("SELECT mname FROM {$tblprefix}members WHERE mid='$keyword'");
	 	 $wheresql .= " AND (mname='$kmname') ";
	  }elseif($keytype){
		 $wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }else{
	  	 $wheresql .= " AND (tel ".sqlkw($keyword)." OR msg ".sqlkw($keyword).") ";
	  }
  }
  
  if(!submitcheck('bsubmit')){
	  
	  echo form_str('chargelog',"?entry=$entry$filterstr&page=$page");
	  tabheader_e();
	  echo "<tr><td class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键词\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('0'=>'--筛选范围--','mname'=>'会员名','mid'=>'会员ID','ip'=>'IP','msg'=>'操作提示','opname'=>'操作者','note'=>'备注'),$keytype)."</select>&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader("短信充值记录",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  //$cy_arr[] = '发送时间';
	  $cy_arr[] = '会员ID';
	  $cy_arr[] = '会员名称';
	  $cy_arr[] = '充值条数';
	  $cy_arr[] = '时间';
	  $cy_arr[] = 'ip';
	  $cy_arr[] = '操作提示';
	  $cy_arr[] = '操作者';
	  $cy_arr[] = '备注';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; $stype = array('sadm'=>'管理员发','scom'=>'会员发送','ctel'=>'其它认证',);
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
		  $time = date('Y-m-d H:i',$r['stamp']); //print_r($r); //die();
		  
		  $msg = mhtmlspecialchars($r['msg']);
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtC\">$r[mid]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[mname]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[cnt]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$time</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[ip]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[msg]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[opname]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[note]</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry&page=$page$filterstr");
  
	  tabheader('批量操作');
	  $str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"now\">删除记录 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m3\">删除3月前 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m1\">删除1月前 &nbsp;";
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除记录",'',$str,'');
	  //trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[checkf]\" value=\"1\"> 审核留言",'checkv',makeoption(array('1'=>'审核留言','0'=>'屏蔽留言')),'select');
	  tabfooter('bsubmit');
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($arcdeal_del)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	//echo "$arcdeal[delete],$arcdeal_del";
	if($arcdeal_del=='now'){
		if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&page=$page$filterstr");
		foreach($selectid as $k){
			$db->query("DELETE FROM {$tblprefix}sms_recharge WHERE cid='$k'",'UNBUFFERED');
			continue;
		}
	}elseif($arcdeal_del=='m3'){
		$sql = "DELETE FROM {$tblprefix}sms_recharge WHERE stamp<='".($timestamp-90*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}elseif($arcdeal_del='m1'){
		$sql = "DELETE FROM {$tblprefix}sms_recharge WHERE stamp<='".($timestamp-30*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}
	cls_message::show('记录批量操作成功'.'。',"?entry=$entry&page=$page$filterstr");
 }
 
}elseif($action=='balance'){

  $keyword = empty($keyword) ? '' : $keyword; 
  $mchid = empty($mchid) ? '0' : $mchid;
  $chg1 = empty($chg1) ? '' : $chg1;
  $chg2 = empty($chg2) ? '' : $chg2;
  $filterstr = $checked?"&checked=$checked":'';
  foreach(array('keyword','mchid','chg1','chg2') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  //$entryv = "&action=$action";
  $wheresql = ' WHERE 1=1 ';
  $fromsql = "FROM {$tblprefix}members m";
  
  $keyword && $wheresql .= " AND (m.mid='$keyword' OR m.mname ".sqlkw($keyword).")";
  $mchid && $wheresql .= " AND m.mchid='$mchid'";
  $chg1 && $wheresql .= " AND m.sms_charge>='$chg1'";
  if($chg2 && $chg2>$chg1) $wheresql .= " AND m.sms_charge<='$chg2'";

  if(!submitcheck('bsubmit')){
	  
	  echo form_str($actionid.'members',"?entry=$entry$filterstr&page=$page");
	  tabheader_e();
	  //trhidden('mchid',$mchid);
	  $sarr['0'] = '--会员类型--';
	  foreach($mchannels as $k=>$v) $sarr[$k] = $v['cname'];
	  echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索会员名或ID\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"mchid\">".makeoption($sarr,$mchid)."</select>&nbsp; ";
	  /*
	  foreach($grouptypes as $gtid => $grouptype){
		  echo "<select style=\"vertical-align: middle;\" name=\"ugid$gtid\">".makeoption(array('0' => $grouptype['cname']) + ugidsarr($gtid),${"ugid$gtid"})."</select>&nbsp; ";
	  }
	  //*/
	  echo "短信余额<input class=\"text\" name=\"chg1\" type=\"text\" value=\"$chg1\" size=\"4\" style=\"vertical-align: middle;\" title=\"最少:条余额\">";
	  echo "~<input class=\"text\" name=\"chg2\" type=\"text\" value=\"$chg2\" size=\"4\" style=\"vertical-align: middle;\" title=\"最多:条余额\">条 ";
  
	  trhidden('action',$action);
	  echo strbutton('bfilter','筛选');
	  echo "</td></tr>";
	  tabfooter();
	  //列表区	
	  tabheader("会员列表",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",);
	  $cy_arr[] = '会员ID|L';
	  $cy_arr[] = '会员名称|L';
	  $cy_arr[] = '会员类型';
	  $cy_arr[] = '审核';
	  #foreach($grouptypes as $k => $v) $cy_arr["ugid$k"] = $v['cname'];
	  $cy_arr[] = '短信(条)';
	  $cy_arr[] = '现金(元)';
	  $cy_arr[] = '充值记录';
	  $cy_arr[] = '注册日期';
	  $cy_arr[] = '最近访问';
	  //$cy_arr[] = '更多';
	  //$cy_arr[] = '会员组';
	  //$cy_arr[] = '详情';
	  trcategory($cy_arr);
  
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("SELECT * $fromsql $wheresql ORDER BY mid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = '';
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[mid]]\" value=\"$r[mid]\">";
		  $mnamestr ="<a href=".cls_Mspace::IndexUrl($r)." target=\"_blank\">". $r['mname'].($r['isfounder'] ? '-创始人': '').'</a>';
		  $mchannelstr = @$mchannels[$r['mchid']]['cname'];
		  $checkstr = $r['checked'] == 1 ? 'Y' : '-';
		  foreach($grouptypes as $k => $v){
			  ${'ugid'.$k.'str'} = '-';
			  if($r['grouptype'.$k]){
				  $usergroups = cls_cache::Read('usergroups',$k);
				  ${'ugid'.$k.'str'} = @$usergroups[$r['grouptype'.$k]]['cname'];
			  }
		  }
		  $sms_charge = $r['sms_charge'];
		  $regdatestr = $r['regdate'] ? date('Y-m-d',$r['regdate']) : '-';
		  $lastvisitstr = $r['lastvisit'] ? date('Y-m-d',$r['lastvisit']) : '-';
		  $viewstr = "<a id=\"{$actionid}_info_$r[mid]\" href=\"?entry=extend&extend=memberinfo&mid=$r[mid]\" onclick=\"return showInfo(this.id,this.href)\">信息</a>";
		  $editstr = "<a href=\"?entry=extend&extend=member&mid=$r[mid]\" onclick=\"return floatwin('open_memberedit',this)\">详情</a>";
		  $groupstr = "<a href=\"?entry=extend&extend=membergroup&mid=$r[mid]\" onclick=\"return floatwin('open_memberedit',this)\">会员组</a>";
  
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td>";
		  $itemstr .= "<td class=\"txtL\">$r[mid]</td>\n";
		  $itemstr .= "<td class=\"txtL\">$mnamestr</td>\n";
		  $itemstr .= "<td class=\"txtC\">$mchannelstr</td>\n";
		  $itemstr .= "<td class=\"txtC w35\">$checkstr</td>\n";
		  #foreach($grouptypes as $k => $v) $itemstr .= "<td class=\"txtC\">".${'ugid'.$k.'str'}."</td>\n";
		  $itemstr .= "<td class=\"txtC\">$sms_charge</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[currency0]</td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href=\"?entry=sms_admin&action=chargelog&keytype=mid&keyword=$r[mid]\">查看详情</a></td>\n";
		  $itemstr .= "<td class=\"txtC\">$regdatestr</td>\n";
		  $itemstr .= "<td class=\"txtC\">$lastvisitstr</td>\n";
		  //$itemstr .= "<td class=\"txtC\">$viewstr</td>\n";
		  //$itemstr .= "<td class=\"txtC\">$groupstr</td>\n";
		  //$itemstr .= "<td class=\"txtC\">$editstr</td>\n";
		  $itemstr .= "</tr>\n";
	  }
	  $counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
	  $multi = multi($counts, $atpp, $page, "?entry=$entry$filterstr&action=$action");
	  echo $itemstr;
	  tabfooter();
	  echo $multi;
	  
	  //操作区
	  tabheader('操作项目');
	  $sql = "SELECT SUM(sms_charge) as sms_charge FROM {$tblprefix}members ";
	  $sum = $db->result_one($sql);
	  trbasic('短信充值','','<input name="arc_charge" >(条) 充值扣值理由备注<input name="arc_note" >(可不填)','');
	  $msg = '正整数为充值,可为负,则为扣除余额；会员当前短信余额总和('.$sum.')条；
	  接口当前短信余额('.$balance[1].')'.$sms_cfg_aset[$sms_cfg_api]['unit'].'；'.($sms_cfg_aset[$sms_cfg_api]['unit']=='元' ? '接口价格('.$sms_cfg_papi.')元/条；' : '').'';
	  trbasic('说明','',$msg,'');
	  tabfooter('bsubmit');
  
  }else{
	  //if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=$entry$extend_str&page=$page$filterstr&action=$action");
	  if(empty($selectid)) cls_message::show('请选择会员',"?entry=$entry&page=$page$filterstr&action=$action");
	  if(!is_numeric($arc_charge)) cls_message::show('充值条数 必须为数字! ',"?entry=$entry&page=$page$filterstr&action=$action");
	  /*
	  if($sms_cfg_aset[$sms_cfg_api]['unit']=='元') $b2 = $balance[1]/$sms_cfg_papi;
	  else $b2 = $balance[1];
	  if($b2<count($selectid)*$arc_charge) cls_message::show('接口余额不足! ');
	  */
	  foreach($selectid as $id){
		$sql = "UPDATE {$tblprefix}members SET sms_charge=sms_charge+('$arc_charge') WHERE mid='$id'";
		$db->query($sql,'UNBUFFERED');
		//echo "$sql";
		$mname = $db->result_one("SELECT mname FROM {$tblprefix}members WHERE mid='$id'");
		$sql = "INSERT INTO {$tblprefix}sms_recharge SET 
		  mid='$id',mname='$mname',stamp='$timestamp',ip='$onlineip',
		  cnt='$arc_charge',msg='管理员',opname='{$curuser->info['mname']}',note='$arc_note'";
		$db->query($sql);
	  }

	  adminlog('会员短信充值','短信充值操作');
	  cls_message::show('短信充值操作完成',"?entry=$entry&page=$page$filterstr&action=$action");
  }

}elseif($action=='sendsms'){

  $apiarr = $sms_cfg_aset[$sms_cfg_api];
  $apimsg = "余额:($balance[1]".$apiarr['unit']."){$ermsg}；接口名称:(".$apiarr['name'].")";
  $apimsg .= $apiarr['home'] ? "；<a href=\"".$apiarr['home']."\" target=\"_blank\">接口官网</a>" : '';
  
  if(!submitcheck('bsubmit')){
	  
		tabheader("短信发送",'sendsms',"?entry=$entry&page=$page",2,1,1);
		trbasic('接口信息','',"$apimsg",'');
		trbasic("手机号码",'fmdata[tel]','','textarea',array('w'=>360,'h'=>80,'validate'=>' rule="text" must="1" min="11" max="24000" rev="手机号码" ','guide'=>'一行一个或用,号分开；自动过滤如下符号【(-)】<br>一次最多2000发送个手机号码'));
		$hostname = cls_env::mconfig('hostname'); //很多接口要求签名,用这个默认签名
		$cmsg = "<br />多于[".$sms->cfg_mchar."]个字，按[".($sms->cfg_mchar-5)."]个字每条扣费。[当前已输入<span id='mcnt'>0</span>个字]";
		$cmsg .= "<br />有些接口要求短信内容要<a href='#' onClick='setMsgSign()'>加上类似“【某公司】”或”“【姓名】”等签名,或按其提供的[短信模版]</a>，否则可能发不出去，具体请与短信提供商联络！";
		trbasic("短信内容",'fmdata[msg]','','textarea',array('w'=>360,'h'=>80,'validate'=>' rule="text" must="1" min="3" max="255" rev="短信内容" ','guide'=>'一次发送,最多255个字符以内，<a href="#" onclick="setTestMsg()">[测试短信]</a>'.$cmsg));
		$apiarr['note'] && trbasic('接口说明','',$apiarr['note'],'');
		trhidden('action',$action);

		if($balance[1]<=0){ 
			//短信内容不能为空</div>
			trbasic('接口提示','',"<div style='' class='validator_message warn'>余额不足！请联系［<a href=\"".$apiarr['home']."\" target=\"_blank\">短信接口提供商</a>］充值！</div>",'');
			tabfooter('');
		}
		tabfooter('bsubmit'); 
		echo "\r\n<script type='text/javascript'>
		var m=\$id('fmdata[msg]');m.onblur=function(){\$id('mcnt').innerHTML=m.value.length;}
		function setTestMsg(){m.value='{$apiarr['name']}余额:$balance[1]{$apiarr['unit']}(".date('H:i:s').")';\$id('mcnt').innerHTML=m.value.length;}
		function setMsgSign(){m.value='【{$hostname}】'+m.value;m.onblur();}
		</script>";
  }else{
		
		$msg = $sms->sendSMS($fmdata['tel'],$fmdata['msg'],'sadm');
		if($msg[0]==1){
			$msg0 = "发送成功!";
		}else{
			$msg0 = "发送失败!";
		}
		$msg = $msg0.$msg[1];
		cls_message::show($msg,axaction(6,M_REFERER));
 }
		
}elseif($action=='setapi'){

	$f1 = function_exists('fsockopen'); $f2 = function_exists('curl_init');
	if(!$f1 || !$f2){
		$msg = "<p style='font-size:14px;color:#F00;padding:10px 20px;'><span style='color:#FF00FF'>提示：</span>[1.]请设置php.ini,allow_url_fopen=On,开启php_curl.dll扩展；[2.]保证fsockopen,curl_init等函数可用；[3.]设置好后本提示自动关闭。</p>";
		a_guide($msg,1);	
	}
	
	if(!submitcheck('bmconfigs')){
		$sms_cfg_api = $sms->isClosed() ? '(close)' : $sms_cfg_api;
		if(!modpro()) unset($sms_cfg_aset['0test']);
		tabheader('手机短信接口','cfmobile','?entry=sms_admin&action=setapi',2,0,1);
		echo "<tr class=\"txt\"><td class=\"txt txtright fB borderright\">接口提供商</td>\n".
		"<td class=\"txtL\">\n";
		$jstab = ''; $jsflg = '(close)';
		foreach($sms_cfg_aset as $k=>$v){
			$jsitm = '';
			$name = empty($v['gray']) ? "$v[name]" : "<span style='color:#BBB' title='只提供兼容性维护,新用户优先使用其它接口'>$v[name]</span>";
			echo "<label><input class=\"radio\" type=\"radio\" id=\"sms_cfg_api$k\" name=\"mconfigsnew[sms_cfg_api]\" value=\"$k\" onclick=\"setApi('$k')\"".($sms_cfg_api == $k ? ' checked="checked"' : '').">$name</label>&nbsp; ";
			$jsitm .= $v['home'] ? "<a href=\"$v[home]\" target=\"_blank\">接口官网首页</a>" : '';
			$jsitm .= $v['admin'] ? ($jsitm ? ' | ' : '')."<a href=\"$v[admin]\" target=\"_blank\">接口管理登陆</a>" : '';
			$jsitm .= $v['note'] ? ($jsitm ? ' <br /> ' : '').str_replace(array("\r","\n",'"',"'"),array("","","\\\"","\\'"),$v['note']) : '';
			$jstab .= "\nvar sms_js_$k = '".($jsitm ? $jsitm : '')."';";
			$jstab .= "\nvar sms_js_{$k}_uid = '".(@$mconfigs['sms_'.$k.'_uid'] ? $mconfigs['sms_'.$k.'_uid'] : '')."';";
			$jstab .= "\nvar sms_js_{$k}_upw = '".(@$mconfigs['sms_'.$k.'_upw'] ? $mconfigs['sms_'.$k.'_upw'] : '')."';";
			echo "<input type=\"hidden\" name=\"mconfigsnew[sms_{$k}_uid]\" value=\"".@$mconfigs['sms_'.$k.'_uid']."\">";
			echo "<input type=\"hidden\" name=\"mconfigsnew[sms_{$k}_upw]\" value=\"".@$mconfigs['sms_'.$k.'_upw']."\">";
			if($k==$sms_cfg_api) $jsflg = $k;
			//echo "\n\n<hr>$jsitm;\n";
		}
		echo "<label><input class=\"radio\" type=\"radio\" id=\"sms_cfg_api0\" name=\"mconfigsnew[sms_cfg_api]\" value=\"(close)\" onclick=\"setApi('(close)')\"".($sms_cfg_api == '(close)' ? ' checked="checked"' : '').">[关闭接口]</label>&nbsp; ";
		echo "</td></tr>\n";
		trbasic('接口说明','',"",'',array('rowid'=>'sms_id_note')); 
		trbasic('帐号/序列号','mconfigsnew[sms_cfg_uid]',@$mconfigs['sms_cfg_uid'],'text',array('rowid'=>'sms_uid'));  
		trbasic('密码/密钥','mconfigsnew[sms_cfg_upw]',@$mconfigs['sms_cfg_upw'],'password',array('validate' => ' autocomplete="off"','rowid'=>'sms_upw')); 
		trbasic('接口价格(元/条)','mconfigsnew[sms_cfg_papi]',isset($mconfigs['sms_cfg_papi']) ? $mconfigs['sms_cfg_papi'] : '0.1','text',array('guide'=>'(元/条)，如果当前接口不能查寻价格,请填写此项；接口可以查询价格的,不使用此项。','validate'=>' rule="float" must="1" regx="" min="0.001" max="9999" '));
		trbasic('会员价格(元/条)','mconfigsnew[sms_cfg_price]',isset($mconfigs['sms_cfg_price']) ? $mconfigs['sms_cfg_price'] : '0.15','text',array('guide'=>'(元/条)，用于给会员充值提示。','validate'=>' rule="float" must="1" regx="" min="0.001" max="9999" '));
		trbasic('管理员手机','mconfigsnew[hosttel]',@$mconfigs['hosttel'],'text',array('guide'=>'用于管理员接收手机短信，若没开通短信接口，可不填'));
		trbasic('单号码发送限额','mconfigsnew[sms_cfg_smax]',isset($mconfigs['sms_cfg_smax']) ? $mconfigs['sms_cfg_smax'] : '10','text',array('guide'=>'单个号码,一天内(24小时)最多能发送短信的次数; 群发取前24个号码字符，默认为10，请根据短信运营商设置。','validate'=>' rule="float" must="1" regx="" min="0.001" max="9999" '));
		trbasic('单IP发送时间间隔','mconfigsnew[sms_cfg_ipmax]',isset($mconfigs['sms_cfg_ipmax']) ? $mconfigs['sms_cfg_ipmax'] : '120','text',array('guide'=>'单位(秒)，单个IP两次发送信息的最短时间间隔，0为不限制，请根据短信运营商设置。','validate'=>' rule="int" must="1" regx="" min="0" max="9999" '));
		echo "\r\n<script type='text/javascript'>$jstab\nfunction setApi(api){var tr = \$id('sms_id_note');var td = tr.getElementsByTagName('td')[1];var td_uid=\$id('mconfigsnew[sms_cfg_uid]');var td_upw = \$id('mconfigsnew[sms_cfg_upw]');var tr_uid = \$id('sms_uid');var tr_upw = \$id('sms_upw');if(api=='(close)'){tr.style.display = 'none';tr_uid.style.display='none';tr_upw.style.display='none';}else{tr.style.display = '';tr_uid.style.display='';tr_upw.style.display='';eval('var note = sms_js_'+api+';var uid=sms_js_'+api+'_uid;var upw=sms_js_'+api+'_upw;');td.innerHTML = note;td_uid.value= uid;td_upw.value= upw;}}setApi('$jsflg');</script>";
		tabfooter('bmconfigs');
		a_guide('sms_apiset');
	}else{
		!empty($mconfigsnew) or cls_message::show('请重新设置',axaction(6,M_REFERER));
		isset($mconfigsnew['sms_'.$mconfigsnew['sms_cfg_api'].'_uid']) or $mconfigsnew['sms_'.$mconfigsnew['sms_cfg_api'].'_uid']='';
		isset($mconfigsnew['sms_'.$mconfigsnew['sms_cfg_api'].'_upw']) or $mconfigsnew['sms_'.$mconfigsnew['sms_cfg_api'].'_upw']='';
		//用新的配置更新原来的配置
		$mconfigsnew['sms_'.$mconfigsnew['sms_cfg_api'].'_uid'] = $mconfigsnew['sms_cfg_uid'];
		$mconfigsnew['sms_'.$mconfigsnew['sms_cfg_api'].'_upw'] = $mconfigsnew['sms_cfg_upw'];
		
		saveconfig('sms');
		adminlog('短信接口设置');
		cls_message::show('短信接口设置完成',axaction(6,M_REFERER));
	}
	
}elseif($action=='apiwarn'){
	
	$file = M_ROOT."dynamic/sms/balance_apiwarn.wlog"; 
	if(!empty($unlink)){
	    $fp = _08_FilesystemFile::getInstance();
		$fp->delFile(M_ROOT.$file);
	}
	$iapi = $sms_cfg_aset[$sms_cfg_api];
	$info = '';
	$info .= $iapi['home'] ? " ------ <a href=\"$iapi[home]\" target=\"_blank\">接口官网首页</a>" : '';
	$info .= $iapi['admin'] ? ($info ? ' | ' : '')."<a href=\"$iapi[admin]\" target=\"_blank\">接口管理登陆</a>" : '';
	$sum1 = $db->result_one("SELECT SUM(sms_charge) as sms_charge FROM {$tblprefix}members ");
	$sum2 = $db->result_one("SELECT SUM(cnt)        as sms_cnt FROM {$tblprefix}sms_sendlogs WHERE stamp>='".($timestamp-30*24*3600)."'");
	$agv1 = round($sum2/30);
	tabheader('统计与报警','info_warn','');
	trbasic('接口信息','',"接口名称:(".$iapi['name'].") $info",'');
	trbasic('接口总余额','',"($balance[1])".$iapi['unit']."",''); 
	trbasic('会员总余额','',"($sum1)条",'');
	trbasic('短信发布总数','',"($sum2)条 [按近30天计算]",'');
	trbasic('平均每天发布','',"($agv1)条 [按近30天计算]",'');
	if($iapi['unit']=='元') $b2 = $balance[1]/$sms_cfg_papi;
	else $b2 = $balance[1];
	$wno = 0;
	if($balance[1]<=0){
		$wno++;
		trbasic("<span class='cDRed'>警告{$wno}：</span>",'',"当前余额为[0]，请联系接口供应商充值",'');
	}
	$sum5 = $agv1*5;
	if($b2<$sum5){
		$wno++;
		trbasic("<span class='cDRed'>警告{$wno}：</span>",'',"按[最近30天]，[平均每天发布数]计算，当前余额已经不够使用5天，请联系接口供应商充值",'');
	}
	if($wflag = $sms->balanceWarn(5)){
		$wno++;
		$unlink = "<a href='?entry=sms_admin&action=apiwarn&unlink=1'>[这里]</a>";
		trbasic("<span class='cDRed'>警告{$wno}：</span>",'',"检测到[最近5天内]，因接口余额不足而发送短信失败记录如下；删除文件请点$unlink",'');
		$data = mhtmlspecialchars(cls_string::CutStr(file_get_contents($file),2048)); //2K
		echo '<tr><td colspan="2" class="txt txtleft"><textarea name="textarea" id="textarea" style="width:100%" rows="12">'.$data.'</textarea></td></tr>';
	}
	if($iapi['unit']=='条'&&$wno===0){
		if($b2<$sum1){
			trbasic("<span class='cBlue'>重要提示：</span>",'',"会员总余额 &gt; 接口总余额，但会员的余额余额不一定最近就使用完，请自行确定是否尽快充值！",'');
		}
	}
	if($wno===0){
		trbasic("<span class='cGreen'>安心提示：</span>",'',"没有检测到任何警告信息！请放心使用接口！",'');
	}

	tabfooter('');

}elseif($action=='enable'){
    if(!submitcheck('bsubmit')){
        $smscfgsets = cls_cache::exRead('smsregcodes');
        $smscfgsave = cls_cache::cacRead('smsconfigs',_08_USERCACHE_PATH);
		$groups = array(
			'sys' => '内置模块',
			'ex' => '扩展模块',
			'cu' => '交互模块',
		);

		// 是否存在跳转到交互设置页,
		if(empty($smscfgsets['cumodels']['cuids'])){
			$endgk = 'ex';
			unset($groups['cu']);
		}else{
			$endgk = 'cu';	
		}

		//是否标准版在缓存中配置

		foreach($groups as $gk=>$gname){
			tabheader("{$gname}设置",'exconfigs', $gk=='sys' ? "?entry=sms_admin&action=$action" : '',2,0,1);	
				echo "<tr class=\"txt\">\n";
				if($gk=='cu'){
					echo "<td width='20%' class='txt txtright fB'>交互模型</td>\n";
					echo "<td width='20%' class='txt txtcenter fB'>短信设置</td>\n";
					echo "<td class='txt txtleft fB'>说明</td>\n";
				}else{
					echo "<td width='20%' class='txt txtright fB'>模块/状态</td>\n";
					echo "<td class='txt txtleft'>短信模版/配置</td>\n";		
				}
				echo "</td></tr>\n";
				foreach($smscfgsets as $key=>$v){
					if($gk=='cu' && $v['group']=='cu'){
						$commus = cls_cache::Read('commus');
						foreach($v['cuids'] as $cuid){
							if(!isset($commus[$cuid])) continue;
							$cucfg = $commus[$cuid]; 
							echo "<tr class=\"txt\">\n<td width='20%' class='txt txtright fB'>交互($cuid) - {$cucfg['cname']}</td>\n";
							echo "<td width='20%' class='txt txtcenter'><a href='?entry=commus&action=commudetail&cuid=$cuid' onclick=\"return floatwin('open_commussms',this)\">短信设置</a></td>\n";
							echo "<td class='txt txtleft'>{$cucfg['remark']}</td>\n</td></tr>\n";
						}
					}elseif($v['group']==$gk){
						$cfg1 = $v; //一项配置值
						$val1 = @$smscfgsave[$key]; //一项保存缓存 
						$tpl = empty($val1['tpl']) ? @$v['tpl'] : @$val1['tpl'];
						$ischeck = !empty($val1['open']) ? 'checked="checked"' : '';
						$cbox = in_array($key,array('commtpl','membexp')) ? '' : '<input name="smsarrnew['.$key.'][open]" type="checkbox" value="1"'. $ischeck .'/>'; //.$val1['open']
						$cbox = empty($cfg1['nocbox']) ? $cbox : '';
						$sarea = (@$v['tpl']=='notpl') ? '' : '<textarea class="js-resize" name="smsarrnew['.$key.'][tpl]" id="smsarrnew['.$key.'][tpl]" style="width:400px;height:60px">'.$tpl.'</textarea>';
						if(!empty($cfg1['ugcfgs'])){
							$ugtitle = $key=='membexp' ? '仅提醒以下会员组: ' : @$cfg1['ugtitle'];
							$sarea .= $ugtitle.@makecheckbox('smsarrnew['.$key.'][cfgs][ugids][]',$cfg1['ugcfgs'],$val1['cfgs']['ugids']); //array('4_2')
						}
						if($key=='membexp'){
							$daysarr = array(15=>'15天',7=>'7天',3=>'3天',1=>'1天');
							$sarea .= '<br>提前提醒天数: '.@makeradio('smsarrnew['.$key.'][cfgs][days]',$daysarr,$val1['cfgs']['days']); //'7'
						}
						trbasic("$key - ".$v['title']." $cbox",'',$sarea,'',array('guide' =>$v['guide'] ));
					}
				}
			tabfooter($endgk==$gk ? 'bsubmit' : '');
		}
        //tabfooter('bsubmit');
        a_guide('sms_open');
    }else{
        //$smsarr = cls_cache::exRead('smsregcodes');
        //用新的配置更新原来的配置
        foreach($smsarrnew as $smskey=>$smsvalue){
           $smsarrnew[$smskey]['open'] = empty($smsvalue['open']) ? '0' : 1;
        }
        /*foreach($smsarr as $key=>$value){
            $smsarr[$key]['open'] = $smsarrnew[$key]['open'];
            $smsarr[$key]['tpl'] = $smsarrnew[$key]['tpl'];
            //array_merge($value,$smsvalue);
        }*/
        cls_CacheFile::cacSave($smsarrnew,'smsconfigs',_08_USERCACHE_PATH);
        cls_message::show('手机短信功能设置成功！',M_REFERER);
    }

	
}

/*
<script type='text/javascript'>
function warnLink(){
  var ul = document.getElementsByTagName('ul'); 
  ul[0].innerHTML += '<li><a href="?entry=sms_admin&action=current"><span class="cDRed">余额报警</span></a></li>'; 
}
if($defact=='apiwarn') echo 'warnLink()';
</script>
*/

?>
