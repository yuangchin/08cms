<?
!defined('M_COM') && exit('No Permission'); 
$sms = new cls_sms();
$sms_cfg_aset = $sms->cfga;
//$sms_cfg_api = $sms->api;
if($sms->isClosed()) cls_message::show('[手机短信接口]未开启!');
$balance = $sms->getBalance();
$balanceu = $curuser->info['sms_charge'];

$page = !empty($page) ? max(1, intval($page)) : 1; 
$checked = empty($checked) ? 0 : $checked; 
$section = empty($section) ? 'sendlog' : $section;
backnav('sms_member',$section);  

if($section=='sendlog'){ 

  $keyword = empty($keyword) ? '' : $keyword;
  $keytype = empty($keytype) ? 'tel' : $keytype;
  $filterstr = $checked?"&checked=$checked":'';
  foreach(array('keyword','keytype') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE mid='$memberid' "; //
  $fromsql = "FROM {$tblprefix}sms_sendlogs ";
    
  if($keyword){
	  if($keytype){
	  	$wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }else{
	  	$wheresql .= " AND (tel ".sqlkw($keyword)." OR msg ".sqlkw($keyword).") ";
	  }
  }
  
  if(!submitcheck('bsubmit')){ 
	  
	  echo form_str($action.'archivesedit',"?action=$action&page=$page&section=sendlog");
	  tabheader_e();
	  echo "<tr height=\"38\"><td class=\"txt txtleft\">";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('tel'=>'电话','msg'=>'内容','ip'=>'IP'),$keytype)."</select> ";
	  echo "&nbsp;<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索\">&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader('短信发送记录','','',10);
	  //$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  //$cy_arr[] = '发送时间';
	  $cy_arr[] = '&nbsp;发送电话|L';
	  $cy_arr[] = '&nbsp;信息内容|L';
	  //$cy_arr[] = '会员';
	  $cy_arr[] = '发送ip/时间';
	  $cy_arr[] = '发送结果';
	  //$cy_arr[] = '接口信息';
	  
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
		  
		  $a = explode('/',$r['api']); $key = substr($a[0],4);
		  $api_u = empty($stype[$a[1]]) ? '系统代发' : $stype[$a[1]];
		  $res = (substr($r['res'],0,1)=='1' ? 'OK' : (substr($r['res'],0,2)=='-2' ? '<span style="color:#F0F">余额不足</span>' : '失败')).'<br>'.$api_u; 
		  $itemstr .= "<td class=\"item2\" width='180'>$tel</td>\n";
		  $itemstr .= "<td class=\"item2\" width='360'>$msg</td>\n";
		  //$itemstr .= "<td class=\"txtC\">$r[mname]</td>\n";
		  $itemstr .= "<td class=\"item\">$r[ip]<br>$time</td>\n";
		  $itemstr .= "<td class=\"item\">$res</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action&page=$page&section=sendlog$filterstr");
	  m_guide("sms_mobile",'fix');
	  /*
	  tabheader('批量操作');
	  $str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"now\">删除记录 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m3\">删除3月前 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m1\">删除1月前 &nbsp;";
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除提问",'',$str,'');
	  //trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[checkf]\" value=\"1\"> 审核留言",'checkv',makeoption(array('1'=>'审核留言','0'=>'屏蔽留言')),'select');
	  tabfooter('bsubmit');
	  */
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($arcdeal_del)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	//echo "$arcdeal[delete],$arcdeal_del";
	if($arcdeal_del=='now'){
		if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&page=$page$filterstr");
		foreach($selectid as $k){
			//$db->query("DELETE FROM {$tblprefix}sms_sendlogs WHERE cid='$k'",'UNBUFFERED');
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
 
}elseif($section=='chargelog'){
 
  //echo "xx";
  $keyword = empty($keyword) ? '' : $keyword; 
  $keytype = empty($keytype) ? 'cnt' : $keytype;
  $indays  = empty($indays)  ? 0 : max(0,intval($indays));
  $outdays = empty($outdays) ? 0 : max(0,intval($outdays));
  $filterstr = $checked?"&checked=$checked":'';
  foreach(array('keyword','keytype','section','indays','outdays') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE mid='$memberid' "; //
  $fromsql = "FROM {$tblprefix}sms_recharge ";
    
  if($keyword){
	  if($keytype){
	  	$wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }else{
	  	$wheresql .= " AND (tel ".sqlkw($keyword)." OR msg ".sqlkw($keyword).") ";
	  }
  }
  
  $indays && $wheresql .= " AND stamp>'".($timestamp - 86400*$indays)."'"; 
  $outdays && $wheresql .= " AND stamp<'".($timestamp - 86400*$outdays)."'"; 
  
  if(!submitcheck('bsubmit')){ 
	  
	  echo form_str('sendlogs',"?action=$action&page=$page&section=chargelog");
	  tabheader_e();
	  //tabheader("短信发送",'sendsms',"?action=$action&page=$page&section=chargelog",2,1,1);
	  echo "<tr height=\"38\"><td class=\"txt txtleft\">";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('cnt'=>'条数','msg'=>'操作者',),$keytype)."</select> ";
	  echo "&nbsp;<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键字\">&nbsp; ";
	  echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"3\" style=\"vertical-align: middle;\">天前&nbsp; ";
		echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"3\" style=\"vertical-align: middle;\">天内&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader("短信充值记录",'','',10);
	  //$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  //$cy_arr[] = '发送时间';
	  $cy_arr[] = '会员ID';
	  $cy_arr[] = '会员名称';
	  $cy_arr[] = '充值条数';
	  $cy_arr[] = '时间';
	  $cy_arr[] = 'ip';
	  $cy_arr[] = '操作者';
	  $cy_arr[] = '备注';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY cid DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; 
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
		  $time = date('Y-m-d H:i',$r['stamp']);
		  
		  $msg = mhtmlspecialchars($r['msg']);
		  $note = empty($r['note']) ? '-' : $r['note'];
		  $itemstr .= "<td class=\"item\">$r[mid]</td>\n";
		  $itemstr .= "<td class=\"item\">$r[mname]</td>\n";
		  $itemstr .= "<td class=\"item\">$r[cnt]</td>\n";
		  $itemstr .= "<td class=\"item\">$time</td>\n";
		  $itemstr .= "<td class=\"item\">$r[ip]</td>\n";
		  $itemstr .= "<td class=\"item2\">$r[msg]</td>\n";
		  $itemstr .= "<td class=\"item2\">$note</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?action=$action&page=$page&section=chargelog$filterstr");
	  m_guide("sms_mobile",'fix');
	  /*
	  tabheader('批量操作');
	  $str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"now\">删除记录 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m3\">删除3月前 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m1\">删除1月前 &nbsp;";
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除提问",'',$str,'');
	  //trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[checkf]\" value=\"1\"> 审核留言",'checkv',makeoption(array('1'=>'审核留言','0'=>'屏蔽留言')),'select');
	  tabfooter('bsubmit');
	  */
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($arcdeal_del)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	//echo "$arcdeal[delete],$arcdeal_del";
	if($arcdeal_del=='now'){
		if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&page=$page$filterstr");
		foreach($selectid as $k){
			//$db->query("DELETE FROM {$tblprefix}sms_recharge WHERE cid='$k'",'UNBUFFERED');
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
  

}elseif($section=='balance'){
	
		//$curuser->info['currency0'] = 1; //测试
		if(!submitcheck('bsubmit')){ 
			tabheader('手机短信充值','gtexchagne',"?action=$action&section=$section",2,1,1);
			trbasic('现有短信余额','',$balanceu.' 条','');
			trbasic('现金帐户余额','',$curuser->info['currency0']."元 &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a>",'');
			trbasic('短信单价','',$sms_cfg_price.' 元/条','');
			
			$max1 = ceil($curuser->info['currency0']/$sms_cfg_price); 
			$max2 = min($max1,80000);
			if($max2<10){
				trbasic('余额提示','',"<div style='' class='validator_message warn'>现金余额不足充值10条短信！请联系[管理员]或>>在线支付！</div>",'');
				tabfooter('');
			}else{
				trbasic('<FONT color=red>*</FONT> 充值条数','',"<input class=\"input\" type=\"text\" id=\"sms_count\" name=\"sms_count\" value=\"0\" rule='int' must='1' regx='' min='10' max='$max2' rev='充值条数'>",'');
				tabfooter("bsubmit\" onclick=\"return sendReCheck('sms_count','充值条数','确认执行充值?');\"");
			}
			m_guide("sms_mobile",'fix');

		}else{
			$sms_count = max(0,intval($sms_count));
			$sms_money = $sms_count*$sms_cfg_price;
			$cur_money = $curuser->info['currency0']-$sms_money;
			$cur_count = $curuser->info['sms_charge']+$sms_count;
			if($curuser->info['currency0'] < $sms_money) cls_message::show('您的现金帐户余额不足，请充值。',M_REFERER);
			$curuser->updatefield("currency0",$cur_money);
			$curuser->updatefield("sms_charge",$cur_count);
			$curuser->updatedb();
			//echo "$cur_money,$cur_count";
			$sql = "INSERT INTO {$tblprefix}sms_recharge SET 
			  mid='$memberid',mname='{$curuser->info['mname']}',stamp='$timestamp',ip='$onlineip',
			  cnt='$sms_count',msg='会员自主充值',note='当前金额:($cur_money)元'";
			$db->query($sql);
			cls_message::show('短信充值成功。',M_REFERER);
		}
		


}elseif($section=='sendsms'){

  $apiarr = $sms->cfgs;
  $apimsg = "余额:(".$balanceu."条)；接口名称:(".$apiarr['name'].")";
  $apimsg .= $apiarr['home'] ? "；<a href=\"".$apiarr['home']."\" target=\"_blank\">接口官网</a>" : '';
  
  m_guide("接口信息 : $apimsg",'tip');
  if(!submitcheck('bsubmit')){
	  
		tabheader("短信发送",'sendsms',"?action=$action&section=sendsms",2,1,1);
		//trbasic('接口信息','',"$apimsg",'');
		trbasic("手机号码",'fmdata[tel]','','textarea',array('w'=>360,'h'=>80,'validate'=>' rule="text" must="1" min="11" max="24000" rev="手机号码" ','guide'=>'<br>一行一个或用,号分开；自动过滤如下符号【(-)】<br>一次最多2000发送个手机号码'));
		$curuser = cls_UserMain::CurUser(); 
		$hostname = isset($curuser->info['company']) ? $curuser->info['company'] : $curuser->info['mname'];//cls_env::mconfig('hostname'); //很多接口要求签名,用这个默认签名
		$cmsg = "<br />多于[".$sms->cfg_mchar."]个字，按[".($sms->cfg_mchar-3)."]个字每条扣费。[当前已输入<span id='mcnt'>0</span>个字]";
		$cmsg .= "<br />有些接口要求短信内容要<a href='#' onClick='setMsgSign()'>加上类似“[某公司]”或”“[姓名]”等签名</a>才可成功发送，具体请与管理员联络！";
		trbasic("短信内容",'fmdata[msg]','','textarea',array('w'=>360,'h'=>80,'validate'=>' rule="text" must="1" min="3" max="255" rev="短信内容" ','guide'=>'<br>一次发送,最多255个字符以内，<a href="#" onclick="setTestMsg()">[测试短信]</a>'.$cmsg));
		$msg = ($apiarr['nmem'] ? $apiarr['nmem'].'<br>' : '')."";
		$msg && trbasic('接口说明','',$msg,'');
		trhidden('section',$section);

		if($balanceu<=0){ 
			//短信内容不能为空</div>
			trbasic('余额提示','',"<div style='' class='validator_message warn'>余额不足！请充值！</div>",'');
			tabfooter('');
		}else{
			tabfooter('bsubmit'); 
		}
		echo "\r\n<script type='text/javascript'>
		var m=\$id('fmdata[msg]');m.onblur=function(){\$id('mcnt').innerHTML=m.value.length;}
		function setTestMsg(){m.value='sms会员测试(".$curuser->info['mname'].");时间(".date('H:i:s').");余额:$balanceu($apiarr[unit]);\\n接口名称[$apiarr[name]]';\$id('mcnt').innerHTML=m.value.length;}
		function setMsgSign(){m.value+=m.innerHTML+'[$hostname]';m.onblur();}
		</script>\n";
		m_guide("sms_mobile",'fix');
  }else{
		
		$msg = $sms->sendSMS($fmdata['tel'],$fmdata['msg']);
		if($msg[0]==1){
			$msg0 = "发送成功!";
		}else{
			$msg0 = "发送失败!";
		}
		$msg = $msg0.$msg[1];
		cls_message::show($msg,"?action=$action&section=sendsms");
 }
		
}

?>
