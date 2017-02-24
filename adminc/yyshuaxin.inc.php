<?php
!defined('M_COM') && exit('No Permission');

$nmchid = 2;$nchids = array(2,3);
if($curuser->info['mchid'] != $nmchid) cls_message::show('请先注册为经纪人会员。');
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
if(!($rules = @$exconfigs['yysx'])) cls_message::show('系统没有预约刷新规则。');

$arc = new cls_arcedit;
$arc->set_aid($aid,array('au'=>0));
!$arc->aid && cls_message::show('选择文档');
$arc->archive['mid'] == $memberid || cls_message::show('您只能预约自已发布的房源。');

if($curuser->info['mchid'] != 2 || !in_array($curuser->info['grouptype14'],$rules['allowgroup'])){
	 cls_message::show('您没有预约刷新的权限。');
};
$_today_time = strtotime(date('Y-m-d'));
$chid = $arc->archive['chid'];
if(!in_array($chid,$nchids) || !$arc->archive['checked']) cls_message::show('只能预约已审的房源。');
$_yytime_arr = search_yytime($aid,$_today_time);//当前已经设置了的预约的次数

//因为刷新并不是实时的，因此进行预约的时候，会看到某些本应该被刷新的，还显示着选中的状态
$_no_reffesh_arr = not_be_refresh($aid,$_today_time);



$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.urlencode($forward);
if(!submitcheck('bsubmit')){
	tabheader("房源置顶-{$arc->archive['subject']}","{$action}newform","?action=$action$forwardstr&aid=$aid",2,0,1);
	trbasic('当前预约状态','',empty($arc->archive['yuyue']) ? '未预约' : '已预约','');
	trbasic('赠送预约刷新数量','',$curuser->info['freeyys'].' 次','');
	trbasic('现金帐户余额','',$curuser->info['currency0']."元 &nbsp; &nbsp;<a href=\"?action=payonline\" target=\"_blank\">>>在线支付</a><font color=\"#FF0000\">(刷新一次扣除金额&nbsp;".$rules['price']."&nbsp;元)</font>",'');
	trbasic('预约刷新说明','',$rules['directions'],'');
	$_yytime_str = implode(',',$_yytime_arr);
	trhidden('fmdata[oldyytime]',$_yytime_str);
	
	
	
	$_time = array_filter(explode(',',$rules['time']));
	$_select_str = '';
	$style1 = " style = \"text-align:center;width:100%;border-top:1px solid #9EC9EB;border-right:1px solid #9EC9EB;\" ";
	$style2 = " style = \"border-left:1px solid #9EC9EB;border-bottom:1px solid #9EC9EB;\" ";	
	$style3 = " style = \"width:100px; border-left:1px solid #9EC9EB;border-bottom:1px solid #9EC9EB;\" ";
	
	
	$_select_str .= "<table $style1 cellpadding=\"0\" cellspacing=\"0\"><tr><td $style2>&nbsp;</td>";
	foreach($_time as $k => $v){        
		if($v>=24){//防止后台设置的数大于等于24
		  cls_message::show('刷新时间不能大于等于24,请联系网站管理员，<br/>在"系统管理>>房源参数>>预约刷新"中重新设置。');
          exit;
        }   
        $id_head= strstr($v,":")?str_replace(':','',$v):$v; 
		$v = strstr($v,":")?$v:$v.":00";
		$_select_str .= "<td $style2>".$v."&nbsp;&nbsp;<input class=\"checkbox\" type=\"checkbox\" onclick=\"checkallid(this.form,'".$id_head."yytime','chkall$id_head')\" id=\"chkall$id_head\" name=\"chkall$id_head\"></td>"; 
	}
	$_select_str .= "</tr>";    
	for($i = 0;$i < $rules['yyday'];$i++){
		foreach($_time as $k => $v){
            $minitues_arr = array();
            $id_head= strstr($v,":")?str_replace(':','',$v):$v;
            if(strstr($v,':')){
                $minitues_arr = explode(':',$v);
                $v = $minitues_arr[0];
                if($minitues_arr[1]>=60){
                   $minitues_arr[1] = $minitues_arr[1]%60; 
                }
            }
            $minitues_arr[1] = empty($minitues_arr[1])?0:$minitues_arr[1];	            	
			$_time_str = mktime($v,$minitues_arr[1],0,date('m'),date('d')+$i,date('Y'));
			$_datetime = date('Y-m-d',$_time_str);
			$_datetime2 = $_time_str + 1;			
			$_idspecial = mktime(0,0,0,date('m'),date('d')+$i,date('Y'));
			$k == 0 && $_select_str .= "<tr><td $style3>".$_datetime."&nbsp;&nbsp;<input class=\"checkbox\" type=\"checkbox\" onclick=\"checkallid(this.form,'yytime$_idspecial','chkall$_datetime2','1')\" name=\"chkall$_datetime2\" id = 'chkall$_datetime2'></td>";
			$_id = $_name = $_value = $_datetime = $_time_str;
			$_select_str .= "<td $style2><input type=\"checkbox\" onclick=\"checkbox(this.form,".$curuser->info['freeyys'].",".$curuser->info['currency0'].",".$rules['price'].")\" id=\"".$id_head."yytime".$_idspecial."[$_id]\" ".($timestamp > $_id ? "disabled=\"disabled\"" :'')." name = \"yytime[$_name]\" ".(in_array($_value,$_yytime_arr)? "checked = 'checked'":'' )." value=\"$_value\"></td>";
            unset($minitues_arr);
		}
		$_select_str .= "</tr>";
	}$_select_str .= "</table>";	
	
	trbasic("预约刷新时间设置 <br/>全部选择<input class=\"checkbox\" type=\"checkbox\" name=\"yychkall\" onclick=\"checkall(this.form,'yytime','yychkall')\">",'',$_select_str,'');	
	echo "<tr><td>&nbsp;</td><td><div id=\"should_pay\"></div></td></tr>";
	tabfooter('bsubmit');	
}else{
	$_old_yytime = empty($fmdata['oldyytime'])?array():explode(',',$fmdata['oldyytime']);		
	$_new_yytime = empty($yytime)?array():$yytime;
	
	
	//防止出现已经过了刷新时间，但是数据还没刷新的情况
	if(!empty($_no_reffesh_arr)){
		foreach($_no_reffesh_arr as $k => $v){
			$_new_yytime[$k] = $v; 
		}
	}


	//控制每天进行预约刷新的房源条数
	$_count_yycz = cls_DbOther::ArcLimitCount(2, 'yuyuedate', "='".strtotime(date('Y-m-d'))."' AND yuyue = '1'"); 
	$_count_yycs = cls_DbOther::ArcLimitCount(3, 'yuyuedate', "='".strtotime(date('Y-m-d'))."' AND yuyue = '1'");
    if(($_count_yycz + $_count_yycs) >=  $rules['totalnum']) cls_message::show("您今天设置房源预约刷新的条数已用完。");
	if(empty($_old_yytime)){		
		if(!empty($_new_yytime)){
			//设置了刷新后，再次设置刷新，数据库members对刷新条数和金额的操作
			_kouchu(count($_new_yytime),$rules);
			$db->query("UPDATE {$tblprefix}".atbl($chid)." SET yuyue='1',yuyuedate = '$_today_time' WHERE aid = '$aid'");
			foreach($_new_yytime as $k => $v){
				$db->query("INSERT INTO {$tblprefix}commu_yuyue set aid = '$aid',refreshtime = '$k',chid = '$chid',mid='$memberid'");
			}
		}
	}else{
		$_num = count($_old_yytime)-count($_new_yytime);
		if($_num > 0){
			empty($_new_yytime) && $db->query("UPDATE {$tblprefix}".atbl($chid)." SET yuyue='0',yuyuedate='0' WHERE aid = '$aid'");
			$db->query("UPDATE {$tblprefix}members SET freeyys = freeyys + '$_num'  WHERE mid = '$memberid'");
			//设置了刷新后，再次设置刷新，数据库表commu_yuyue的插入和删除操作
			_control_sql(array('_old_yytime'=>$_old_yytime,'_new_yytime'=>$_new_yytime,'aid'=>$aid,'chid'=>$chid,'_no_reffesh_arr'=>$_no_reffesh_arr));
		}else{
			//设置了刷新后，再次设置刷新，数据库members对刷新条数和金额的操作
			_kouchu($_num,$rules);
			//设置了刷新后，再次设置刷新，数据库表commu_yuyue的插入和删除操作
			_control_sql(array('_old_yytime'=>$_old_yytime,'_new_yytime'=>$_new_yytime,'aid'=>$aid,'chid'=>$chid,'_no_reffesh_arr'=>$_no_reffesh_arr));
		}			
	}
	cls_message::show('房源预约设置成功。',axaction(6,$forward));
}
?>
<script type="text/javascript">
	//预约刷新余额
	var yu_e = <?php echo $curuser->info['freeyys'];?>;
	//金钱
	var cash = <?php echo $curuser->info['currency0'];?>;
	//每次刷新要支付金额
	var each_pay = <?php echo $rules['price'];?>;
	
	///////////////////////////算出原来已经预约了多少次，供再次预约的时候，对预约的次数以及金钱进行信息提示////////////////////////////////
	var num = 0;
	var find_input = document.getElementsByTagName('form')[0].getElementsByTagName('input')
	for(var i = 0; i < find_input.length; i++) {
		var e = find_input[i];
		if(e.type == 'checkbox' && e.id.indexOf('yytime') != -1 && e.checked == true) {
			num++;
		}
	}
	/////////////////////////////////////////////////////////////

	
	function checkall(form, prefix, checkall){
		checkall = checkall ? checkall : 'chkall';
		for(var i = 0; i < form.elements.length; i++){
			var e = form.elements[i];
			if(e.name != checkall && (!prefix || !e.name.indexOf(prefix)) && !e.disabled){
				e.checked = form.elements[checkall].checked;
			}
		}
		checkbox(form,yu_e,cash,each_pay);
	}

	function checkallid(form, sid, checkall,is_first){	
		checkall = checkall ? checkall : 'chkall';		
		for(var i = 0; i < form.elements.length; i++) {
			var e = form.elements[i];	
			if(!is_first){
				if(e.type == 'checkbox' && !e.id.indexOf(sid) && !e.disabled) {
					e.checked = form.elements[checkall].checked;
				}
			}else{
				if(e.type == 'checkbox' && e.id.indexOf(sid) != -1 &&  e.id.indexOf(sid) && !e.disabled) {				
					e.checked = form.elements[checkall].checked;
				}
			}			
		}		
		checkbox(form,yu_e,cash,each_pay);
	}
	

	
	function checkbox(form,yu_e,cash,each_pay){//找出全部被选的checkbox
		var j = 0;
		var _mes = '';
		for(var i = 0; i < form.elements.length; i++) {
			var e = form.elements[i];				
			if(e.type == 'checkbox' && e.id.indexOf('yytime') != -1 && e.checked == true) {
				j++;
			}
		}
		j = j - num;
		if( yu_e - j >= 0){
			_mes = '本次预约刷新设置应扣除预约刷新次数：<font color="red">' + j + '</font>次。剩余预约刷新次数：<font color="red">' + (yu_e - j) + '</font>次,剩余金额<font color="red">' + cash + '</font>元。';
		}else if( cash - (j - yu_e) * each_pay >= 0){
			
			_mes = '本次预约刷新设置应扣除预约刷新次数：<font color="red">' + yu_e + '</font>次,同时扣除金额<font color="red">' + parseFloat(((j - yu_e) * each_pay).toFixed(2).toString()) + '</font>元，您剩余的刷新余额为<font color="red">0</font>次，金钱为<font color="red">' + parseFloat((cash - (j - yu_e) * each_pay).toFixed(2).toString()) + '</font>';
		}else{		
			alert('本次预约刷新设置应扣除预约刷新次数：' + yu_e + '次,同时扣除金额' + parseFloat((each_pay*(j - yu_e)).toFixed(2).toString()) + '元。\n您总的刷新余额为' + yu_e + '次，金额为' + cash + '元，不足以支付本次刷新数目，请充值或者重新设置。');
			return false;
		}
		if(_mes){
			document.getElementById('should_pay').innerHTML = _mes;
		}
	}
</script>