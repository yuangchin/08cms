<?php
//涉及到样式的会员中心函数，及特定模板专用的函数在这里定义
!defined('M_COM') && exit('No Permission');
function noedit($var = '',$otherfbd = 0){
	global $useredits,$freeupdate;
	empty($useredits) && $useredits = array();
	return !$otherfbd && ($freeupdate || in_array($var,$useredits)) ? '' : '&nbsp; <img src="images/common/lock.gif" align="absmiddle">';
}

function url_nav($arr = array(),$current=''){//针对所选择的链接，高亮当前页
	echo "<div class=\"menutop\">\n";
	foreach($arr as $k => $v) echo "<a href=\"$v[1]\"".($k == $current ? ' class="act"' : '')."><span>$v[0]</span></a>\n";
	echo "<div class=\"blank0\"></div></div>";
}
function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $simple = 0, $onclick = '') {
	global $infloat,$handlekey;
	$multipage = '';
	$mpurl .= in_str('?',$mpurl) ? '&amp;' : '?';
	$onclick && $onclick .='(event);';
	$infloat && $onclick .= "return floatwin('update_$handlekey',this)";
	$onclick && $onclick = " onclick=\"$onclick\"";
	if($num > $perpage) {//只有超过1页时，才显示分页导航
		$offset = 2;//当前页码之前显示的页码数
		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;//需要统计的页数
		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) $to = $page;
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1" class="p_redirect"'.$onclick.'>1...</a>' : '').($curpage > 1 && !$simple ? '<a href="'.$mpurl.'page='.($curpage - 1).'" class="p_redirect"><<</a>' : '');
		for($i = $from; $i <= $to; $i++) $multipage .= $i == $curpage ? '<a class="p_curpage">'.$i.'</a>' : '<a href="'.$mpurl.'page='.$i.'" class="p_num"'.$onclick.'>'.$i.'</a>';
		$multipage .= ($curpage < $pages && !$simple ? '<a href="'.$mpurl.'page='.($curpage + 1).'" class="p_redirect"'.$onclick.'>>></a>' : '').($to < $pages ? '<a href="'.$mpurl.'page='.$pages.'" class="p_redirect"'.$onclick.'>...'.$pages.'</a>' : '').
			(!$simple && $pages > $page ? '<a class="p_pages" style="padding: 0px; border:0;"><input class="p_input" type="text" name="custompage" onKeyDown="if(event.keyCode==13) {window.location=\''.$mpurl.'page=\'+this.value; return false;}"></a><input type="button" name="s_asd" value="跳转" onclick="return upb_dir();"><script type="text/javascript">function upb_dir(){var url = "'.$mpurl.'page="+document.forms[0].custompage.value;window.location = url.replace("&amp;","&");}</script>' : '');
		$multipage = $multipage ? '<div class="p_bar">'.(!$simple ? '<a class="p_total">&nbsp;'.$num.'&nbsp;</a>' : '').$multipage.'</div>' : '';
	}
	return $multipage;
}

function tabheader($tname='',$fname='',$furl='',$col=2,$fupload=0,$checksubmit=0,$newwin=0){
	if($fname) echo form_str($fname,$furl,$fupload,$checksubmit,$newwin);
	tabheader_e();
	echo "<tr class=\"header\"><td colspan=\"$col\"><b>$tname</b></td></tr>\n";
}
function tabheader_e(){
	echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" class=\"black tabmain marb10\">\n";
}
function tabfooter($bname='',$bvalue='',$addstr='',$fmclose=1){//$fmclose是否关闭form
	global $aListSetReset;
	$bvalue = empty($bvalue) ? '提交' : $bvalue;
	echo "</table>\n";
	if($aListSetReset){
		echo $aListSetReset;
		$aListSetReset = '';
	}
	echo $bname ? "<div align=\"center\"><input class=\"button\" type=\"submit\" name=\"$bname\" value=\"$bvalue\"></div>\n" : '';
	echo $addstr ? $addstr : '';
	echo $bname && $fmclose ? "</form>\n" : '';
}
function trcategory($arr = array(), $tabID='1'){
	global $ckpre,$entry,$extend,$action,$aListSetReset;
	$arr = array_filter($arr);
	$baseID = empty($entry)?'entID':$entry;
	foreach(array('extend','action','tabID') as $k) empty($$k) || $baseID .= '_'.$$k;
	$aListSet_tCfg = ''; //$i = 0;
	$trStr = "<tr id=\"TR_$baseID\" class=\"category\" align=\"center\">\n";
	foreach ($arr as $v) {
	   $iCfg = '';
	   if(is_array($v)){
		  foreach ($v as $j => $vsub) $iCfg .= $v[$j].'|';
		  $iCfg .= '|';
	   }else{
		  $iCfg .= $v.'||';
	   }
	   $iArr = explode('|',$iCfg);
	   $aListSet_tCfg .= strtoupper($iArr[2]).'|'; // S/H
	   $iVal = $iArr[0];
	   if(strlen($iArr[1])>1){
		   $trStr .= "\n<td class=\"$iArr[1]\">$iVal</td>\n";
	   }else{
		   if(strlen($iArr[1])=='') $iArr[1] = 'C';
		   else $iArr[1] = strtoupper($iArr[1]);
		   if($iArr[1]=='C'){
			   $trStr .= "\n<td>$iVal</td>\n";
		   }else if($iArr[1]=='R'){
			   $trStr .= "\n<td class=\"right\">$iVal</td>\n";
		   }else if($iArr[1]=='L'){
			   $trStr .= "\n<td class=\"left\">$iVal</td>\n";
		   }else{
			   $trStr .= "\n<td>$iVal</td>\n";
		   }
	   }
	}
	$trStr .= "</tr>\n";
	if(str_replace('|','',$aListSet_tCfg)!=''){
		$trStr = str_replace("<tr id=","<tr ondblclick=\"aListSetting('$baseID','$aListSet_tCfg')\" id=",$trStr);
		$aListSetReset = "\n<script type='text/javascript'>\n";
		$aListSetReset .= "var aListSet_ckpre = '$ckpre';\n";
		$aListSetReset .= "aListSetReset('$baseID','$aListSet_tCfg');"; //
		$aListSetReset .= "\n</script>\n";
	}
	echo $trStr;
}
function strbutton($name,$value='提交',$class='button',$onclick = ''){
	return "<input class=\"$class\" type='".($onclick ? 'button' : 'submit')."' name=\"$name\" value=\"$value\"".($onclick ?  " onclick=\"$onclick\"" : '').">";
}
function viewcheck($param){
	$name = $value = $body = $title = '';$noblank = 0;
	extract($param, EXTR_OVERWRITE);
	return ($noblank ? '' : '&nbsp; &nbsp; ')."<input class=\"checkbox\" type=\"checkbox\" name=\"$name\" value=\"1\" onclick=\"alterview('$body')\"".(empty($value) ? '' : ' checked').">$title";
}
function trrange($trname,$arr1,$arr2,$type='text',$guide='',$width = '150px'){
	$trname = '<b>'.$trname.'</b>';
	echo "<tr><td width=\"$width\" class=\"item1\">$trname</td>\n";
	echo "<td class=\"item2\">\n";
	echo (empty($arr1[2]) ? '' : $arr1[2])."<input type=\"text\" size=\"".(empty($arr1[4]) ? 10 : $arr1[4])."\" id=\"$arr1[0]\" name=\"$arr1[0]\" value=\"".mhtmlspecialchars($arr1[1])."\"".($type == 'calendar' ? " class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\"" : '')."><span id=\"alert_$arr1[0]\" name=\"alert_$arr1[0]\" class=\"red\"></span>".(empty($arr1[3]) ? '' : $arr1[3]);
	echo (empty($arr2[2]) ? '' : $arr2[2])."<input type=\"text\" size=\"".(empty($arr2[4]) ? 10 : $arr2[4])."\" id=\"$arr2[0]\" name=\"$arr2[0]\" value=\"".mhtmlspecialchars($arr2[1])."\"".($type == 'calendar' ? " class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\"" : '')."><span id=\"alert_$arr2[0]\" name=\"alert_$arr2[0]\" class=\"red\"></span>".(empty($arr2[3]) ? '' : $arr2[3]);
	if($guide) echo "<br /><font class=\"gray\">$guide</font>";
	echo "</td></tr>";
}
function tr_regcode($rname, $params = array()){
	global $cms_regcode,$cms_abs,$timestamp;
        $fromName = empty($params['formName']) ? NULL : $params['formName'];
        $class = empty($params['class']) ? 'regcode' : $params['$class'];
        $inputName = empty($params['inputName']) ? '' : $params['inputName'];
        $inputString = empty($params['inputString']) ? '' : $params['inputString'];
        $code = _08_HTML::getCode($rname, $fromName, $class, $inputName, $inputString);
	if($cms_regcode && in_array($rname,explode(',',$cms_regcode))){
		echo <<<EOT
            <tr><td class="item1"><b><font color='red'> * </font>验证码</b></td>
            <td class="item2">$code&nbsp;&nbsp;</td></tr>
EOT;
	}
}
function trspecial($trname,$varr = array()){
	$trname = '<b>'.$trname.'</b>';
	$lcls = 'item1';$rcls = 'item2';
	$varr['width'] = empty($varr['width']) ? '150px' : $varr['width'];
	if(in_array($varr['type'],array('image','images','flash','flashs','media','medias')))
		$guidestr = $varr['guide'] ? "<div class=\"tips1\">$varr[guide]</div>" : '';
	else
		$guidestr = $varr['guide'] ? (!empty($varr['mode']) ? "<div class=\"tips1\">$varr[guide]</div>" : "<font class=\"gray\">$varr[guide]</font>") : '';
	if($varr['type'] == 'htmltext'){
		echo empty($varr['mode']) ? "<tr><td colspan=\"2\" class=\"item1 item4\">".$trname.$guidestr."</td></tr><tr><td colspan=\"2\" class=\"$rcls\">\n" : "<tr><td width=\"$varr[width]\" class=\"$lcls\">".$trname."</td><td class=\"$rcls\">\n";
		echo $varr['frmcell'].$guidestr;
		#echo "</td></tr>\n";
	}else{
		$varr['addcheck'] && $guidestr = '&nbsp; &nbsp; '.$varr['addcheck'].$guidestr;
		echo "<tr".(@$varr['view'] == 'H'?' style="display:none"':'')."><td width=\"$varr[width]\" class=\"$lcls\">".$trname."</td>\n";
		echo "<td class=\"$rcls\">".$varr['frmcell'].$guidestr;
	}

	echo @$varr['more'] ? '<script type="text/javascript">var __js="'.implode(",",$varr['more']).'";</script>'."<div><span style=\"float:right;cursor:pointer;font-weight:bold;color:#C4141F;\" id=\"more_tips\" onclick=\"hidspan(__js,'fmdata')\">更多设置</span></div></td></tr>\n" : "</td></tr>\n";
	
}
function trbasic($trname, $varname, $value = '', $type = 'text', $arr = array()) {//w,h为单行文本(size)或多行文本指定宽度及高度(px)
	$guide=''; $width = '150px'; $rshow = 1; $rowid = ''; $validate = '';$w = 0;$h = 0;$addstr = '';
	extract($arr, EXTR_OVERWRITE);
	echo "<tr" . ($rowid ? " id=\"$rowid\"" : '') . ($rshow ? '' : ' style="display:none"') . "><td width=\"$width\" class=\"item1\"><b>$trname</b></td>\n";
	echo "<td class=\"item2\">\n";
	if($type == 'radio') {
		$check[$value ? 'true' : 'false'] = "checked";$check[$value ? 'false' : 'true'] = '';
		echo "<input type=\"radio\" class=\"radio\" id=\"$varname\" name=\"$varname\" value=\"1\" $check[true] $validate> ".'是'." &nbsp; &nbsp; \n".
			"<input type=\"radio\" class=\"radio\" id=\"$varname\" name=\"$varname\" value=\"0\" $check[false] $validate> ".'否'." \n";
	}elseif($type == 'select') {
		echo "<select style=\"vertical-align: middle;\" id=\"$varname\" name=\"$varname\" $validate>$value</select>";
	}elseif($type == 'text' || $type == 'password'){
		$w = $w ? $w : 25;
		echo "<input type=\"".($type == 'password' ? $type : 'text')."\" size=\"$w\" id=\"$varname\" name=\"$varname\" value=\"".mhtmlspecialchars($value)."\" $validate />\n";
	}elseif($type == 'calendar'){
		$w = $w ? $w : 15;
		echo "<input type=\"text\" size=\"$w\" id=\"$varname\" name=\"$varname\" value=\"".mhtmlspecialchars($value)."\" class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\" $validate />\n";
	}elseif($type == 'textarea'){
		$w = $w ? $w : 300;$h = $h ? $h : 100;
		echo "<textarea name=\"$varname\" id=\"$varname\" style=\"width:{$w}px;height:{$h}px\" $validate>".mhtmlspecialchars($value)."</textarea>\n";
	}else echo $value;
	echo $addstr;
	if($guide) echo"<font class=\"gray\">$guide</font>";
	echo "</td></tr>\n";
}
function mc_allow(){
	global $message_class,$handlekey,$infloat;
	$curuser = cls_UserMain::CurUser();
	if(!$curuser->info['mid']){
		_header();
		$message_class = 'curbox';
		echo '<div class="area col"><div class="conBox"><div class="con_con"><div class="main_area">';
		empty($handlekey) && $handlekey = '';
		$tmp=empty($infloat)?'':" onclick=\"floatwin('close_$handlekey');return floatwin('open_login',this)\"";
		cls_message::show('请登录会员中心  [<a href="login.php"'.$tmp.'>会员登陆</a>] [<a href="register.php" target="_blank">注册</a>]','');
	}elseif($curuser->info['isfounder']){
		_header();
		cls_message::show('创始人请不要使用会员中心！[<a href="login.php?action=logout">退出</a>]','');
	}
}
function _footer(){
	global $infloat;
	if(!$infloat) echo '<div class="blank9"></div></div>';
	echo '</body></html>';
}
function _header($title = '', $class = 'main_area'){
	defined('M_MCENTER') || define('M_MCENTER', TRUE);
	global $hostname,$mcharset,$cmsname,$mallowfloatwin,$mfloatwinwidth,$mfloatwinheight,$cms_abs,$infloat,$message_class, $cms_top, $cmsurl, $ck_plugins_enable,$ck_plugins_disable,$ckpre;
	$curuser = cls_UserMain::CurUser();
	define('NO_MCFOOTER', TRUE);
	$message_class = 'msgbox';
	$css = MC_ROOTURL.'images/style.css';
	$fltcss = $infloat ? 'floatbox' : 'box';
	$_Title_Adminm = $title ? $title : "会员管理中心 - $cmsname";
	if(!empty($curuser->info['atrusteeship'])) $_Title_Adminm .= " [代管人：{$curuser->info['atrusteeship']['from_mname']}]";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$mcharset?>" />
<title><?=$_Title_Adminm?></title>
<link href="<?=MC_ROOTURL?>css/default.css" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" href="<?=$cmsurl?>images/common/validator.css" />
<link type="text/css" rel="stylesheet" href="<?=$cmsurl?>images/common/window.css" />
</head>
<body>
<script type="text/javascript">var CMS_ABS = "<?=$cms_abs?>" <?=empty($cmsurl) ? '' : ', CMS_URL = "'.$cmsurl.'"'?>,MC_ROOTURL = "<?=MC_ROOTURL?>",tipm_ckkey = '<?=$ckpre?>mTips_List';var originDomain = originDomain || document.domain; document.domain = '<?php echo $cms_top;?>' || document.domain; </script>
<?php cls_phpToJavascript::loadJQuery(); ?>
<script type="text/javascript" src="<?=$cmsurl?>images/common/layer/layer.min.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/common.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/adminm.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/floatwin.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/setlist.js"></script>
<!-- ueditor -->
<script type="text/javascript" src="<?=$cmsurl?>static/ueditor1_4_3/ueditor.config.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>static/ueditor1_4_3/ueditor.all.min.js"> </script>
<script type="text/javascript" src="<?=$cmsurl?>static/ueditor1_4_3/lang/zh-cn/zh-cn.js"></script>
<!-- ueditor end -->
<script type="text/javascript" src="<?=$cmsurl?>include/js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/tree.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/_08cms.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/validator.js"></script>
<div id="append_parent"></div>
<div class="<?=$class ?>">
<?php
}

function mcfooter(){
	global $cms_power,$cms_icpno,$cms_version,$infloat;
	$tpl_mconfigs = cls_cache::Read('tpl_mconfigs');
	$copyright = @$tpl_mconfigs['copyright']; 
	if($infloat){
		echo "</body></html>";
	}else{
?>
</div>
<div class="blank9"></div>
<div style="width:960px; margin:0 auto; text-align:center; height:100px; padding-top:10px;">
<? if(empty($copyright)){ ?>
Copyright &copy; 2008-2012 <a href="http://www.08cms.com" target="_blank">08cms.com</a>. &nbsp;Powered by 08CMS <?=$cms_version?>
<? }else{ echo $copyright; } ?>
</div>
</body>
</html>
<?php 
	}
	entryExit();
}

//我的委托:
function my_wt_header($action){
	global $cms_abs;
	$str=<<<EOT
	<table border="0" cellpadding="0" cellspacing="1" class="black tabmain tabnav marb10"><tbody><tr class="header"><td colspan="30">
			<div class="blocktitle"> 
				<div class="xlist" id="menage"> 管理委托:<a href="?action={$action}&chid=3" style="color:red;">我的委托出售</a>|<a href="?action={$action}&chid=2" style="color:red;">我的委托出租</a>|<a href="{$cms_abs}info.php?fid=101&chid=2" target="_blank">我要委托出租</a>|<a href="{$cms_abs}info.php?fid=101&chid=3" target="_blank">我要委托出售</a>
				</div>
				 <h2>管理我的房子</h2>
			</div>
			</td></tr>
			</tbody></table>
			
			<div class="tishi mT10">
				您可以委托出租，出售各<span class="red">5</span>套房子，每套房子最多可以委托5位经纪人，<span class="red">您的电话及房源信息不对外公开</span>
	</div>
EOT;
	echo $str;
}

function u_memberstat($mid,$minute = 60){//固定时间间隔统计会员的相关状况
	global $db,$tblprefix,$timestamp;//archives,checks,
	@set_time_limit(1000);
	@ignore_user_abort(TRUE);
	if(!($mid = max(0,intval($mid)))) return;
	$ctfile = M_ROOT.'./dynamic/memberstat/'.($mid % 100).'/'.$mid.'.cac';
	if(!$mid || $timestamp - @filemtime($ctfile) < 60 * $minute) return;
	$na = array();
	$na['archives'] = '';
	$na['checks'] = '';
	$tbls = stidsarr(1);
	$curuser = cls_UserMain::CurUser();
	$mchid = $curuser->info['mchid'];
	foreach($tbls as $k => $v){
		$na['archives'] += $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl($k,1)." WHERE mid='$mid'");
		$na['checks'] += $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl($k,1)." WHERE mid='$mid' AND checked=1");	
	}
	$na['aesfys'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(3)." WHERE mid='$mid' AND chid=3");
	if($mchid!=3) $na['vesfys'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(3)." WHERE mid='$mid' AND chid=3 AND checked=1 AND (enddate=0 OR enddate>$timestamp)");
	$na['aczfys'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(2)." WHERE mid='$mid' AND chid=2");
	if($mchid!=3) $na['vczfys'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(2)." WHERE mid='$mid' AND chid=2 AND checked=1 AND (enddate=0 OR enddate>$timestamp)");
	$na['aqzs'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(9)." WHERE mid='$mid' AND chid=9");
	$na['vqzs'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(9)." WHERE mid='$mid' AND chid=9 AND checked=1 AND (enddate=0 OR enddate>$timestamp)");
	$na['aqgs'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(10)." WHERE mid='$mid' AND chid=10");
	$na['vqgs'] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(10)." WHERE mid='$mid' AND chid=10 AND checked=1 AND (enddate=0 OR enddate>$timestamp)");
	foreach(array(5 => 'ablys',11 => 'abscs',) as $k => $v){
		if($commu = cls_cache::Read('commu',$k)){
			$na[$v] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}$commu[tbl] WHERE tomid='$mid'");
		}
	}
	$str = '';foreach($na as $x => $y) $str .= ",$x='$y'";
	$db->query("UPDATE {$tblprefix}members_sub SET ".substr($str,1)." WHERE mid=$mid");
	mmkdir($ctfile,0,1);
	if(@$fp = fopen($ctfile,'w')) fclose($fp);	
	return;
}
/**
 *判断是经纪公司还是经纪人，返回 $otherSql 以及 $whereStr
 * @param array  $agentNameArr 经纪公司下的经纪人
 * @param string $agentMidStr  经纪公司下的经纪人MID组成的字符串，用于sql中
 */
function isCompany($isCompany,$curuser){
    $db = _08_factory::getDBO();
    $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
	$mname = intval(cls_env::GetG('mname'));
    $otherSql = '';
    $whereStr = '';
    $agentNameArr = array();
	if($isCompany){ //找到该经纪公司的所有经纪人
    	if($curuser->info['mchid']!=3){ cls_message::show('您不是[经纪公司]，不能访问。'); } 
    	$agentMidStr = '';
    	$namesql = "select m.mid,m.mname FROM {$tblprefix}members m WHERE m.mchid=2 AND pid4='".$curuser->info['mid']."' AND incheck4=1";
    	$query = $db->query($namesql);
    	while($row = $db->fetch_array($query)){    
    		$agentNameArr[$row['mid']] = $row['mname'];
    		$agentMidStr .= ','.$row['mid'];
    	}
    	$agentNameArr = array('0'=>'-经纪人-') + $agentNameArr;
    	$agentMidStr = empty($agentMidStr) ? "-1" : substr($agentMidStr,1); 
    	if($mname){ //找到该经纪公司下某一个经纪人的房源
    		$whereStr .= "a.mid='$mname'";
    		$otherSql .= "a.mid='$mname'";
		}else{
			$whereStr .= "a.mid IN($agentMidStr)";
    		$otherSql .= "a.mid IN($agentMidStr)";
		}
    }else{
    	$whereStr .= "a.mid='".$curuser->info['mid']."'";
    }
    return array('otherSql'=>$otherSql,'whereStr'=>$whereStr,'agentNameArr'=>$agentNameArr);
}

/**
 * 会员中心二手房、出租、求租、求购搜索区域下方的提示信息
 * @param object $curuser    当前会员实例
 * @param int    $chid       文档id
 * return array
 */
 function userCenterDisplayMes($curuser,$chid,$isAdd=0){
    $style1 = " style='font-weight:bold;color:green'";
    $style2 = " style='font-weight:bold;color:red'";
    $exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
    $message = '';
    $otherData = array();
   
    if(in_array($chid,array(2,3,117,118,119,120))){//二手房/出租房源的显示信息
        $MessageArr = houseDisplayMes($exconfigs,$curuser,$chid,$isAdd,$style1,$style2);
    }else if(in_array($chid,array(9,10))){//求租/求购房源的显示信息
        $MessageArr = qzQgDisplayMes($exconfigs,$curuser,$chid,$isAdd,$style1,$style2);
    }
    $message .= $MessageArr['message'];
    unset($MessageArr['message']);
    return array('message'=>$message,'otherData'=>$MessageArr);
 }
 
/**
 * 会员中心二手房/出租搜索区域下方的提示信息
 * @param array  $exconfigs  后台设定的发布房源求租求购数据
 * @param object $curuser    当前会员实例
 * @param int    $chid       文档id
 * @param string $style1     css样式一
 * @param string $style2     css样式二 
 * return array  
 */
 function houseDisplayMes($exconfigs,$curuser,$chid,$isAdd,$style1,$style2){
    if(!in_array($chid,array(2,3,117,118,119,120))){
        cls_message::show("请指定正确文档模型。");
    }
    $message = '';

    if(in_array($chid,array(2,3))){
        if(!($rules = @$exconfigs['yysx'])) cls_message::show('系统没有预约刷新规则。');
        if(empty($curuser->info['grouptype14'])){
            $exconfigs = $exconfigs['fanyuan'][0];
        }else{
            $exconfigs = $exconfigs['fanyuan'][$curuser->info['grouptype14']];
        }
        //已发布二手房/出租房数量
        $houseNum = cls_DbOther::ArcLimitCount($chid, 'createdate');
        $message .= "每日允许发布房源:<span$style1>$exconfigs[daymax]</span>条；已发布:<span$style1>$houseNum</span>条；还可发布:<span$style2>".($exconfigs['daymax'] - $houseNum)."</span>条；<br/>";

        //已用刷新次数（不是预约刷新）
        $refreshUsedNum = empty($curuser->info['refreshes'])?'0':$curuser->info['refreshes'];

        //剩下刷新次数
        $refreshRemainNum = $exconfigs['refresh'] - $refreshUsedNum;
        $refreshRemainNum = $refreshRemainNum<0 ? 0 : $refreshRemainNum;
        $message .= "每日允许刷新次数为:<span$style1>$exconfigs[refresh]</span>次；已刷新<span$style1>$refreshUsedNum</span>次；还可以刷新<span$style2>$refreshRemainNum</span>次；<br/>";

        //预约刷新
        $chuzuYuyue 	= cls_DbOther::ArcLimitCount(2, 'yuyuedate', "='".strtotime(date('Y-m-d'))."' AND yuyue = '1'");
        $chushouYuyue   = cls_DbOther::ArcLimitCount(3, 'yuyuedate', "='".strtotime(date('Y-m-d'))."' AND yuyue = '1'");
        $yuyueTotalNum 	= $chuzuYuyue + $chushouYuyue;
        $yuyueTotalNum = empty($yuyueTotalNum)?'0':$yuyueTotalNum;
        $curuser->info['mchid'] != 1 && $message .= "每日允许预约房源条数:<span$style1>$rules[totalnum]</span>条；已预约:<span$style1>$yuyueTotalNum</span>条；还可预约:<span$style2>".($rules['totalnum'] - $yuyueTotalNum)."</span>条；<br/>";
    }else{
        //if(!($rules = @$exconfigs['yysx'])) cls_message::show('系统没有预约刷新规则。');
        if(empty($curuser->info['grouptype14'])){
            $exconfigs = $exconfigs['shangye'][0];
        }else{
            $exconfigs = $exconfigs['shangye'][$curuser->info['grouptype14']];
        }
        //已发布二手房/出租房数量
        $houseNum = cls_DbOther::ArcLimitCount($chid, 'createdate');
        $message .= "每日允许发布商业地产:<span$style1>$exconfigs[daymax]</span>条；已发布:<span$style1>$houseNum</span>条；还可发布:<span$style2>".($exconfigs['daymax'] - $houseNum)."</span>条；<br/>";

        //已用刷新次数（不是预约刷新）
        $refreshUsedNum = empty($curuser->info['refreshes'])?'0':$curuser->info['refreshes'];

        //剩下刷新次数
        $refreshRemainNum = $exconfigs['refresh'] - $refreshUsedNum;
        $refreshRemainNum = $refreshRemainNum<0 ? 0 : $refreshRemainNum;
        $message .= "每日允许刷新次数为:<span$style1>$exconfigs[refresh]</span>次；已刷新<span$style1>$refreshUsedNum</span>次；还可以刷新<span$style2>$refreshRemainNum</span>次；<br/>";

    }

    return array('message'=>$message,'refreshRemainNum'=>$refreshRemainNum);    
 }
 /**
 * 会员中心求租求购搜索区域下方的提示信息
 * @param array  $exconfigs  后台设定的发布房源求租求购数据
 * @param object $curuser    当前会员实例
 * @param int    $chid       文档id
 * @param string $style1     css样式一
 * @param string $style2     css样式二 
 * return array  
 */
 function qzQgDisplayMes($exconfigs,$curuser,$chid,$isAdd,$style1,$style2){
    if(!in_array($chid,array(9,10))){
        cls_message::show("请指定正确文档模型。");
    }
    $message = '';  
    if(empty($curuser->info['grouptype14'])){
    	$exconfigs = $exconfigs['fanyuan'][0];	
    }else{
    	$exconfigs = $exconfigs['fanyuan'][$curuser->info['grouptype14']];	
    }
    //已用刷新次数（不是预约刷新）
    $refreshUsedNum = empty($curuser->info['refreshes'])?'0':$curuser->info['refreshes'];
    
    //剩下刷新次数
    $refreshRemainNum = $exconfigs['refresh'] - $refreshUsedNum; 
    $refreshRemainNum = $refreshRemainNum<0 ? 0 : $refreshRemainNum;
    $message .= "每日允许刷新次数为:<span$style1>$exconfigs[refresh]</span>次；已刷新<span$style1>$refreshUsedNum</span>次；还可以刷新<span$style2>$refreshRemainNum</span>次；<br/>";
    
    //已发布需求数量
    $qiuzutotal = cls_DbOther::ArcLimitCount(9, '');
    $qiugoutotal = cls_DbOther::ArcLimitCount(10, '');
    $total = $qiuzutotal + $qiugoutotal;
    $total = empty($total) ? 0 : $total;
    $message .= "允许发布的需求总量:<span$style1>$exconfigs[xuqiu]</span>条；已发布:<span$style1>$total</span>条；还可发布:<span$style2>".($exconfigs['xuqiu'] - $total)."</span>条；<br/>";
    
    return array('message'=>$message,'refreshRemainNum'=>$refreshRemainNum);    
 }
 
 
 /**
  * 二手/出租管理页面头部滑动栏目
  * @param string $type chuzu/chushou
  * @param int    $valid 
  */
 function slidingColumn($type,$valid){
    if(empty($type)) return;
    if($type=='chushou'){
   	    switch($valid){
    		case 1:
    			$_menu = 'shangjia';
    			break;
    		case 0:
    			$_menu = 'cangku';
    			break;
    		case -1:
    			$_menu = 'manage';
    			break;
    		case 3:
    			$_menu = 'maifang';
    			break;
    		default:
    			$_menu = 'ershoufabu';
    			break;
    	}
    }elseif($type=='chuzu'){
      	switch($valid){
    		case 1:
    			$_menu = 'shangjia';
    			break;
    		case 0:
    			$_menu = 'cangku';
    			break;
    		case -1:
    			$_menu = 'manage';
    			break;
    		case 2:
    			$_menu = 'chuzu';
    			break;
    		default:
    			$_menu = 'czfabu';
    			break;
    	}    
    } elseif($type=='bussell_office' || $type=='bussell_shop'){
      	switch($valid){
    		case 1:
    			$_menu = 'shangjia';
    			break;
    		case 0:
    			$_menu = 'cangku';
    			break;
    		case -1:
    			$_menu = 'manage';
    			break;
    		case 2:
    			$_menu = 'chuzu';
    			break;
    		default:
    			$_menu = 'czfabu';
    			break;
    	}
    } elseif($type=='busrent_office' || $type=='busrent_shop'){
      	switch($valid){
    		case 1:
    			$_menu = 'shangjia';
    			break;
    		case 0:
    			$_menu = 'cangku';
    			break;
    		case -1:
    			$_menu = 'manage';
    			break;
    		case 2:
    			$_menu = 'chuzu';
    			break;
    		default:
    			$_menu = 'czfabu';
    			break;
    	}
    }
    backnav($type,$_menu);
 }
 
 /**
  * 非经纪人会员是否有权限查看修改出租/出售房源(目前会员中心只有经纪人以及经纪公司才能查看房源信息)
  * @param object $curuser 当前用户实例
  * @param object $oA      当前文档实例
  */
 function hasPermissionCheckHouse($curuser,$oA){
    $db = _08_factory::getDBO();
    $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
	if($curuser->info['mchid']!=3)cls_message::show('您不是[经纪公司]，不能访问。');  
    
	//找出经纪公司旗下的所有经纪人，并且组成字符串
    $midStr = ',';	
    $midSql = "select m.mid FROM {$tblprefix}members m WHERE m.mchid=2 AND pid4='".$curuser->info['mid']."' ";    
	$query = $db->query($midSql);
	while($row = $db->fetch_array($query)){
		$midStr .= $row['mid'].',';
	}
    
    //文档发布者的mid是否存在于经纪公司旗下的经纪人字符串中
	if(!strstr($midStr,$oA->predata['mid'])){
		$oA->message('对不起，您选择的文档不属于你公司下的经纪人。');
	}
 }
 /**
  * 会员要填写了必填的会员信息，若启用手机认证，手机必须通过认证才能发布房源
  * @param object $curuser 当前用户实例
  * @param int    $chid    文档模型ID
  */
 function publishAfterCheckUserInfo($curuser,$chid){    
    $mchid = $curuser->info['mchid'];
    $mfields = cls_cache::Read('mfields',$mchid);    
	$mctypes = cls_cache::Read('mctypes');
    //会员认证字段数组
    $fieldArr = array();
	foreach($mctypes as $k => $v){
		if(!empty($v['available']) && strstr(",$v[mchids],",",".$mchid.",")){ //允许的会员模型
            $fieldArr[]=$v['field'];
		}
	}
    
    //查看个人信息中必填项是否已填写
    foreach($mfields as $k => $v){
        //当前字段属于认证字段，则忽略
        if(in_array($k,$fieldArr)) continue; 
		if(in_array($k,array('dantu','ming','danwei','quaere'))) continue; //专家字段排除
        //字段启用 并且设置不能为空的情况下， 会员信息中该字段为空
        if(!empty($v['available']) && !empty($v['notnull']) && empty($curuser->info[$k])){
            m_guide("您个人信息还没填写完整，<a href='?action=memberinfo' style='color:red'>点击完善个人信息。</a>",'fix');                      
            die();
        }
    }    
    //如果启用了手机验证，该会员类型又是设定了要手机认证的类型，那就必须通过手机验证之后才能发布房源信息   
    if(!empty($mctypes['1']['available']) && empty($curuser->info['mctid1'])){
        $needCheckMchidArr = array_filter(explode(',',$mctypes['1']['mchids']));
        if(in_array($curuser->info['mchid'],$needCheckMchidArr)){
            m_guide("您还未认证电话号码，<a href='?action=mcerts' style='color:red'>点击认证电话号码。</a>",'fix');     
            die();
        }   
    }    
 }
 
 /**
  * 发布二手房源/出租房源/求租求购房源限额
  * @param object $curuser 当前用户实例
  * @param int    $chid    文档ID
  * @param object $oA      当前用户实例
  * return array
  */
 function publishLimit($curuser,$chid,$oA){  
    $style = " style='font-weight:bold;color:#F00'";
    //发布数量限制
    $exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
	if(empty($curuser->info['grouptype14'])){
		$exconfigs = $exconfigs['fanyuan'][0];
	}else{
		$exconfigs = $exconfigs['fanyuan'][$curuser->info['grouptype14']];
	}
    if(in_array($chid,array(2,3))){
        //发布二手房、出租房源限额
        return housePublishLimit($exconfigs,$chid,$oA,$style);
    }elseif(in_array($chid,array(9,10))){
        //发布求租求购房源限额
        return requirementPublishLimit($exconfigs,$chid,$style);
    }    
 }
 
 /**
  * 二手房/出租房发布条数限额
  * @param array  $exconfigs 后台设置的房源发布参数
  * @param int    $chid      文档ID
  * @param object $oA        当前用户实例
  * @param string $style     css样式
  * return array   
  */
 function housePublishLimit($exconfigs,$chid,$oA,$style){
    if(!in_array($chid,array(2,3))){
        cls_message::show("请指定正确文档模型。");
    }
    if(empty($exconfigs['total'])){ 
    	$exconfigs['total'] = 999999;
    }
    if(empty($exconfigs['daymax'])){ 
    	$exconfigs['daymax'] = 999999;
    }
    
    //统计出租出售总的发布数量
    $chuzuTotalNum = cls_DbOther::ArcLimitCount(2, ''); 
    $chushouTotalNum = cls_DbOther::ArcLimitCount(3, ''); 
    $totalPublishNum = $chuzuTotalNum + $chushouTotalNum;
    $totalPublishNum = empty($totalPublishNum)?0:max(1,$totalPublishNum);
    
    //统计当天已发布的房源的数量
    $dayPublishNum = cls_DbOther::ArcLimitCount($chid, 'createdate');    
    if(empty($dayPublishNum)) $dayPublishNum = '0';
    
    //限额信息
    $limitMessageStr = '';
   	if(!empty($exconfigs['total']) && $exconfigs['total'] <= $totalPublishNum){	
        $limitMessageStr .= "房源发布的<span$style>总限额已满</span>,不能再发布房源！<br>您的发布总限额为：<span$style>$exconfigs[total]</span> 条";
	}
	if(!empty($exconfigs['daymax']) && $exconfigs['daymax'] <= $dayPublishNum){
        $limitMessageStr .= "您今天发布<span$style>限额已满</span>,不能再发布房源！<br>您当天发布的限额为：<span$style>$exconfigs[daymax]</span> 条";
	}
    
    //发布页面提示信息
    $message = $oA->getmtips(array('check'=>1,'limit'=>array($exconfigs['total'],$totalPublishNum),'daymax'=>array($exconfigs['daymax'],$dayPublishNum),),'');
    
    return array('limitMessageStr'=>$limitMessageStr,'message'=>$message); 
 }
 
 /**
  * 求租求购发布条数限制
  * @param array  $exconfigs 后台设置的房源发布参数
  * @param int    $chid      文档ID 
  * @param string $style     css样式
  */
 function requirementPublishLimit($exconfigs,$chid,$style){
    if(!in_array($chid,array(9,10))){
        cls_message::show("请指定正确文档模型。");
    }
    
    //统计已发布的求租求购房源的总数
    $qiuzuTotalNum = cls_DbOther::ArcLimitCount(9, '');
	$qiugouTotalNum = cls_DbOther::ArcLimitCount(10, '');
	$totalPublishNum = $qiuzuTotalNum + $qiugouTotalNum;
    $totalPublishNum = empty($totalPublishNum)?0:max(1,$totalPublishNum);
    
    //限额信息
    $limitMessageStr = '';
	if(!empty($exconfigs['xuqiu']) && $exconfigs['xuqiu'] <= $totalPublishNum){
        $limitMessageStr .= '您当前身份只允许发布的需求总数量为<font color="red"><b> '.$exconfigs['xuqiu'].' </b></font>条信息，<br/>您目前已发布<font color="red"><b> '.(empty($qiugouTotalNum)?'0':$qiugouTotalNum).' </b></font>条求购和<font color="red"><b> '.(empty($qiuzuTotalNum)?'0':$qiuzuTotalNum).' </b></font>条求租信息，如需继续发布，请联系管理员升级管理权限！<br/><br/>';
	}
    return array('limitMessageStr'=>$limitMessageStr);
 }

 
 
 