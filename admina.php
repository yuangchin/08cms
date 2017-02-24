<?php
define('M_ADMIN', TRUE);
define('NOROBOT', TRUE);
define('M_UPSEN', TRUE);
@set_time_limit(0);
include_once dirname(__FILE__).'/include/general.inc.php';
include_once M_ROOT.'include/admina.fun.php';
include_once M_ROOT."include/field.fun.php";
$ipAnd = empty($mconfigs['no_cip']) ? "AND ip = '$onlineip'" : '';
empty($entry) && $entry = '';
$lan_title = '主站管理后台 - 08CMS - '.(empty($no_deepmode) ? '完全架构模式' : '架构保护模式');
empty($cms_idkeep) || $lan_title .= $cms_idkeep == 1 ? ' - 官方升级模式' : ' - 二次开发模式';
$errmsg = '';
if(!empty($admin_user) && regcode_pass('admin', empty($regcode) ? '' : trim($regcode))){
	$curuser->init();
	$curuser->activeuserbyname($admin_user);
	$curuser->info += array('onlineip' => $onlineip,'mslastactive' => $timestamp,'lastolupdate' => $timestamp,'errtimes' => 0,'errdate' => 0,'msid' => cls_string::Random(6));
	$curuser->loginPreTesting();
	$memberid = $curuser->info['mid'];
	$md5_password = _08_Encryption::password($admin_password);
	if(!$memberid || $md5_password != $curuser->info['password'] ){
		$errmsg .= '用户名或密码错误 ';
	}
    elseif($curuser->info['checked'] != 1)
    {
        $errmsg .= '用户未审核 ';
    }
    else
    {
		$curuser->OneLoginRecord();
	}
}
if(!$memberid || !$curuser->isadmin()){
	$aflag = 'off';
}elseif($adminipaccess && !ipaccess($onlineip, $adminipaccess)){
	$aflag = 'ipdenied';
}else{
	$amaxerrtimes = empty($amaxerrtimes) ? 3 : $amaxerrtimes;
	$dateline = $timestamp - (empty($aminerrtime) ? 60 : $aminerrtime) * 60;
	$query = $db->query("SELECT * FROM {$tblprefix}asession WHERE mid='$memberid' $ipAnd AND dateline>$dateline", 'SILENT');
	if($db->error()){
		$db->query("DROP TABLE IF EXISTS {$tblprefix}asession");
		$db->query("CREATE TABLE {$tblprefix}asession (mid mediumint(8) UNSIGNED NOT NULL default '0',
		ip char(15) NOT NULL default '',
		dateline int(10) unsigned NOT NULL default '0',
		errorcount tinyint(1) NOT NULL default '0',
		PRIMARY KEY (mid,ip))".(mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM DEFAULT CHARSET=$dbcharset" : " TYPE=MYISAM"));
		$aflag = 'recheck';
	}elseif($asession = $db->fetch_array($query)){
		if($asession['errorcount'] == -1){
			$db->query("UPDATE {$tblprefix}asession SET dateline='$timestamp' WHERE mid='$memberid' $ipAnd", 'UNBUFFERED');
			$aflag = 'on';
		}elseif($asession['errorcount'] < $amaxerrtimes){
			$errcnt = $asession['errorcount'];
            if (!empty($admin_user))
            {
                $errmsg .= (' - ' . "登陆失败(".($errcnt+1).")次！");
            }	
			$aflag = 'recheck';
		}else{
			login_msg("<string style='color:#F00;font-size:14px'>太多的错误次数！</string><br /><br />如果您是管理员：<br />可以清空表 [#__asession] 后再试；<br />[或等] 登陆锁定解除 后再试！");
		}
	}else{//超时
		$db->query("DELETE FROM {$tblprefix}asession WHERE (mid='$memberid' $ipAnd) OR dateline<$dateline",'UNBUFFERED');
		$db->query("INSERT INTO {$tblprefix}asession(mid,ip,dateline,errorcount) VALUES('$memberid','$onlineip','$timestamp',0)", 'SILENT');
		$aflag = 'recheck';
	}
}
if($aflag == 'off'){
    if (empty($errmsg))
    {
        $errmsg = '没有管理后台权限';
    }
	login_msg($errmsg,'','login');
}elseif($aflag == 'ipdenied'){
	login_msg('管理后台IP禁止','','login');
}elseif($aflag == 'recheck'){
	$qstr = cls_envBase::repGlobalURL($_SERVER['QUERY_STRING']); 
	if(empty($md5_password) || $md5_password != $curuser->info['password'] || !regcode_pass('admin',empty($regcode) ? '' : trim($regcode))){
		if(!regcode_pass('admin',empty($regcode) ? '' : trim($regcode))){
			$errmsg = empty($admin_user) ? '' : '认证码错误！';
		}elseif(!empty($admin_user)){ //!empty($md5_password) && !empty($admin_user) &&
			$db->query("UPDATE {$tblprefix}asession SET errorcount=errorcount+1 WHERE mid='$memberid' $ipAnd");
		}
		login_msg($errmsg,'','login');
	}else{
		$db->query("UPDATE {$tblprefix}asession SET errorcount='-1' WHERE mid='$memberid' $ipAnd");
		if(empty($url_forward) && (empty($entry) || $entry == 'home')){
			login_msg('管理登陆成功',"?$qstr");
		}else{
			header('Location: ' . (empty($entry) ? $url_forward : "$_SERVER[PHP_SELF]?$qstr"));
			exit;
		}
	}
}
@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'cache.fun.php';

$curuser->aPermissions();
if($curuser->apms){
	# 为了兼容，暂时保留，以后做统一处理
	foreach(array('menus','funcs','caids','mchids','fcaids','cuids','checks','extends',) as $var) ${'a_'.$var} = empty($curuser->apms[$var]) ? array() : $curuser->apms[$var];
}elseif(!$entry || $entry != 'logout'){
	$msgstr = '您没有管理后台权限!<br><br><a href="?entry=logout">退出</a>';
	login_msg($msgstr);
}

if(!$entry || isset($isframe)){
	$_mnmenus = cls_cache::Read('mnmenus');
	if(!$curuser->info['isfounder']){
		foreach($_mnmenus as $k0 => $v0){
			foreach($v0['childs'] as $k1 => $v1) if(!in_array($k1,$a_menus)) unset($_mnmenus[$k0]['childs'][$k1]);
			if(empty($_mnmenus[$k0]['childs'])) unset($_mnmenus[$k0]);
		}
	}
	
	$extra = $entry ? withOutUrl() : '';#这样可以保证参数
	$extra || $extra = "?entry=home";

	$usualurlstr = '';
	$headstr = $submenu = '';
	$usualurls = cls_cache::Read('usualurls');
	foreach($usualurls as $v){
		if($curuser->pmbypmid($v['pmid'])){
			if(!$v['ismc'] && $v['available']) $usualurlstr .= "<li><em><a href=\"$v[url]&isframe=1\">$v[title]</a></em></li>";
		}
	}
	//附加类系菜单
	if($cocsmenus = cls_cache::exRead('cocsmenus')){
		foreach($cocsmenus as $coid => $acoids){
			$cocsstr = '';
			if(array_intersect(array(-1,$coid),$a_extends)){
				$a_vcoids = array();
				$a_ucoids = array_keys($acoids['items']);
				foreach($a_ucoids as $v) $a_vcoids = array_merge($a_vcoids,!$v ? array($v) : cls_catalog::Pccids($v, $coid, 1));//所有显示类目的上级类目需要显示出来
				if($a_vcoids = array_unique($a_vcoids)){
					$ncoclasses = array(0 => array('title' => '全部类目','level' => 0)) + cls_cache::Read('coclasses', $coid);
					$i=0;$space='					';
					foreach($ncoclasses as $k => $v){
						if(!in_array($k,$a_vcoids)) continue;
						// for 字母头
						if(isset($cocsmenus[$coid]['first_letter']) && !empty($v['letter']) && empty($v['pid'])){
							$ccprefix = "<span>$v[letter]</span> ";
						}else{
							$ccprefix = '';
						}
						$editstr = in_array($k,$a_ucoids) ? "<em><a href=\"javascript://mcontent\" onclick=\"get_operate($k,'c$coid')\">$ccprefix$v[title]</a></em>" : "<em>$ccprefix$v[title]</em>";
						if($i<$v['level']){
							$i++;
							$cocsstr.="<ul>\n$space	<li>$editstr";
							$space.='	';
						}else{
							if($i>$v['level']){
								while($i-->$v['level']){
									$space=substr($space,0,$i+3);
									$cocsstr.="</li></ul>\n$space";
								}
								$i++;
							}
							$cocsstr.="</li>\n$space<li>$editstr";
						}
					}
					if($i>0){
						while($i-->0){
							$space=substr($space,0,$i+3);
							$cocsstr.="</li></ul>\n$space";
						}
					}
					$headstr .= "<li><a id=\"mainmenu_c$coid\" href=\"javascript:\" onclick=\"setMenu('c$coid');return false\">$acoids[label]</a></li>";
					$cocsstr = substr($cocsstr,5)."</li>\n".substr($space,0,-1);
					if(isset($cocsmenus[$coid]['letter_search'])) $cocsstr = str_replace("全部类目</a></em></li>","全部类目</a></em></li>".$cocsmenus[$coid]['search_item']."",$cocsstr);
					$submenu .= "<ul id=\"submenus_c$coid\" class=\"treeMenu\" style=\"display:none\">$cocsstr</ul>";
				}
				unset($a_vcoids,$a_ucoids,$ncoclasses);
			}
		}
	}

	//常规管理
	$a_catastr = cls_cotype::BackMenuCode(0);
	
	//推送管理
	$p_catastr = cls_PushArea::BackMenuCode();
	
	//副件分类：不再使用管理节点配置，强制父分类仅仅是展示节点
	$f_catastr = cls_fcatalog::BackMenuCode();
	
	//会员模型分类
	$m_catastr = cls_mchannel::BackMenuCode();
	
	//头部菜单
	foreach($_mnmenus as $k => $v){
		if(empty($v['childs']) || (!$a_catastr && $k == 1) || (!$p_catastr && $k == 2) || (!$f_catastr && $k == 3) || (!$m_catastr && $k == 4)) continue;
		$headstr .= "<li><a id=\"mainmenu_$k\" href=\"javascript:\" onclick=\"setMenu($k);return false\">$v[title]</a></li>";
		$submenu .= "\n		<ul id=\"submenus_$k\" style=\"display:none\">";
		foreach($v['childs'] as $x => $c){
			$submenu .= "\n			<li><em><a href=\"" . ($c['url'] == '#' ? 'javascript:' : $c['url']) . "\">$c[title]</a></em></li>";
		}
		$submenu .= "\n		</ul>";
	}
	$submenu .= "\n";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$mcharset?>" />
<title><?=$lan_title?></title>
<link href="<?=$cmsurl?>images/admina/index.css" rel="stylesheet" type="text/css" />
<link href="<?=$cmsurl?>images/common/window.css" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" href="<?=$cmsurl?>images/common/validator.css" />
<script type="text/javascript"> var _08_ROUTE_ENTRANCE = '<?php echo _08_ROUTE_ENTRANCE;?>'; var originDomain = originDomain || document.domain; document.domain = '<?php echo $cms_top;?>' || document.domain;</script>
<?php cls_phpToJavascript::loadJQuery(); ?>
<script type="text/javascript" src="<?=$cmsurl?>include/js/common.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/aframe.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/admina.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/floatwin.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>images/common/layer/layer.min.js"></script>
</head>

<body scroll="no" style="overflow-y:hidden;">
<div id="append_parent"></div>
<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#f7f7f7">
	<tr>
		<td colspan="2" align="left">
			<div id="header">
				<div class="left">
					<div class="logo">
						<img src="images/admina/logo.png" width="165" height="75" title="" alt="" />
					</div>
					<div class="version">V<?=$cms_version?></div>
				</div>
				<div class="right">
					<div class="headTxt">
						<h1><font><?='您好：'.$curuser->info['mname'].'，欢迎使用08CMS！'?></font>[ <a href="?entry=home&isframe=1">管理首页</a> | <a href="<?=$cms_abs?>" target="_blank">网站首页</a> |
<?php
	//系统调试状态
	$debugtagstr = $style = '';
	if(_08_DEBUGTAG){
		$debugtagstr = '调试状态-开';
		$style = ' style="color: yellow;"';
	} else {
		$debugtagstr = '调试状态-关';
	}
	echo "<a$style href=\"?entry=tplconfig&action=tplbase&isframe=1\" title=\"调试模式下模板修改即时生效\">$debugtagstr</a> | ";

	//缓存状态
	$enablecache = true;
	$cachestr = $style = '';
    if ( ($m_excache instanceof cls_excache) && !empty($m_excache->obj->enable) )
    {
        if ( isset($m_excache->__cache_type) )
        {
            $cachestr = $m_excache->__cache_type;
        }
        $enablecache = false;
    }
    else
    {
		$style = ' style="color: yellow;"';
		$cachestr = '缓存优化-关';
	}
	echo "<span$style>$cachestr</span>"
?> | <a href="?entry=extend&extend=memberpw" title="修改个人管理密码" target="main">修改密码</a> | <a href="?entry=logout">退出管理</a> ]</h1>
						<div class="commonActions">
							<h2><a id="cpmap" href="javascript:" onclick="showMap()"><img src="images/admina/sitmap0.png" width="91" height="22" alt="后台地图" /></a></h2>
							<div id="checkWidth">
								<ul><?=$usualurlstr?></ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="globalNav">
				<h1><a href="tools/taghelp.html" target="08cmstaghelp">系统<b>帮助</b>中心</a></h1>
				<ul id="topmenu"><?=$headstr?></ul>
				<div onclick="click_setscreen(this)" style="float:right; width:35px; margin: 7px 12px; _margin-right:6px; height:25px; background:url(images/admina/arrow.jpg) -35px 0;" title="F11/Ctrl+F11 更便捷"></div>
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" width="169" style=" padding-top:9px;">
			<div id="leftmenu" class="col2" style="height:600px">
				<ul id="urlmenus"><?=$usualurlstr?></ul>
				<?=$submenu?>
				<ul id="catamenu" class="treeMenu" style="display:none"><?=$a_catastr?></ul>
				<ul id="pushmenu" class="treeMenu" style="display:none"><?=$p_catastr?></ul>
				<ul id="plugmenu" class="treeMenu" style="display:none"><?=$f_catastr?></ul>
				<ul id="clubmenu" class="treeMenu" style="display:none"><?=$m_catastr?></ul>
				<div id="operateitem" class="operateMenu" style="display:none"></div>
			</div>
		</td>
		<td valign="top" width="100%" style="min-width:753px;">
			<iframe onload="main_onload(this)" frameborder="0" id="main" name="main" src="<?=$extra?>" width="100%" height="600" style="overflow:visible;"></iframe>
		</td>
	</tr>
</table>
<script type="text/javascript">
var aframe_topheader_height = $id('header').parentNode.offsetHeight;
initaMenu($id('urlmenus'));initaMenu($id('catamenu'), 'bktm_cookie');initCpMap('cmain','topmenu');
!function(){
	var m = $id('main'), a = $id('checkWidth'), l = $id('header').childNodes, p = $id('leftmenu'), mids = [$id('catamenu'),$id('plugmenu'),$id('clubmenu')], i = 0, d;
	while(d = l[i++])if(d.className == 'left'){l = d.offsetWidth + 120;break}//120为DIV右边距和地图按钮宽度
	window.aframe_autosize = function(){
		setTimeout(function(){//谷歌不知道在干嘛
			var stat = CWindow.client();
			a.style.width = 'auto';
			if(a.offsetWidth + l > stat.W)a.style.width = stat.W - l + 'px';
			var h = stat.H - aframe_topheader_height - 9;
			m.style.height = h + 9 + 'px';
			p.style.height = h + 'px';
		}, 50);
	};
	listen(window, 'resize', aframe_autosize);
	if(_ua.ie && _ua.ie < 7){
		//IE6不能显示出Ajax菜单
		for(i = 0; i < mids.length; i++){
			d = document.createElement('DIV');
			p.appendChild(d);
			d.appendChild(mids[i]);
		}
		d = $id('operateitem');
		p.appendChild(d);
	}
	aframe_autosize();
	toggleMenu(location.search);
}();
</script>
</body>
</html>
<?
}else{
	if($entry == 'logout'){
		$db->query("DELETE FROM {$tblprefix}asession WHERE mid='$memberid' $ipAnd");
		if($curuser->info['isfounder']){ //isfounder同时登出会员中心(因不能登录会员中心),参考login.php:logout段
			cls_ucenter::logout();
			cls_WindID_Send::getInstance()->synLogout();
			cls_userinfo::LogoutFlag();
		}
		login_msg('管理后台退出完成',$cms_abs);
	}elseif($entry){
	    _08_FilesystemFile::filterFileParam($entry);
		$actionid = $entry . (empty($action) ? '' : "_$action");
		if($ex = extend_script(_08_ADMIN)){
			if(is_file(M_ROOT.$ex)) include_once M_ROOT.$ex;
			echo "\n<!--当前定制入口脚本：$ex-->\n";
		}else if(is_file(M_ROOT. _08_ADMIN . "/$entry.inc.php")) {
		    include_once M_ROOT. _08_ADMIN . "/$entry.inc.php";
		} else {
            // 以MVC前端控制器指定路由
            $front = cls_frontController::getInstance();
            $front->route();
		}
		entryfooter();
	}
}
mexit();

function login_msg($message,$url_forward = '',$msgtype = 'message'){
	global $memberid,$curuser,$entry,$isframe,$lan_title,$cms_regcode,$timestamp,$cms_abs,$mcharset,$inajax,$infloat,$handlekey,$ajaxtarget,$cms_top,$cmsurl;
	$entry = mhtmlspecialchars($entry);
	$target = $infloat ? ' onclick="floatwin(\'close_'.$handlekey.'\');return floatwin(\'open_login\',this)"' : '';
	if($msgtype == 'message'){
		$message = '<tr><td align="center" colspan="2"><br><br>'.$message;
		if($infloat)
			$message .= '<script reload="1">setTimeout("floatwin(\'close_'.$handlekey.'\')", 1250);floatwin(\'closeparent_'.$handlekey.'\')</script><br><br><br></tr>';
		elseif($url_forward){
			if(preg_match('/[?&]entry=logout\b/i', $url_forward))$url_forward = '?entry=home&isframe=1';
			$message .= "<br><br><a href=\"$url_forward\">如果浏览器没有跳转请点这里</a>";
			$message .= "<script reload=\"1\">setTimeout(\"redirect('$url_forward');\", 1250);</script><br><br></td></tr>";
		}else{
			$message .= '<br><br><br></tr>';
		}
	}else{
		if(substr($handlekey,0,8)=='new_new_'){
			$message = '<script reload="1">setTimeout("floatwin(\'close_'.$handlekey.'\')", 1250)</script>'.
			'<td class="txt txtC">密码错误</td></tr>';
		}else{
			$qstr = cls_envBase::repGlobalURL($_SERVER['QUERY_STRING']); 
			$extra = !$qstr ? '' : (empty($isframe) && $entry != 'logout' && !$infloat ? "?isframe=1&$qstr" : (in_array($entry, array('header', 'menu', 'logout')) ? '' : "?$qstr"));
			$_msg = $message;
			$message = '<tr><td align="center" colspan="2"><form method="post" name="login" id="login" action="'.$extra.'">'.
			'<input type="hidden" name="url_forward" value="'.$url_forward.'">'.
			'<table border="0" cellpadding="0" cellspacing="0">';
			$_msg && $message .= '<tr class="txt"><td class="txtL w80">信息提示</td><td class="txt txtL">'.$_msg.'</td></tr>';
			$message .= '<tr class="txt"><td class="txtL w80">管理帐号</td>';
			$message .= '<td class="txt txtL" style="width:240px;"><input type="text" name="admin_user"' . ($curuser->info['mid'] ? " value=\"{$curuser->info['mname']}\"" : ''). ' size="15"/></td></tr>';
			$message .= '<tr class="txt"><td class="txtL w80">登陆密码</td>'.
			'<td class="txt txtL"><input type="password" name="admin_password" size="15"/></td></tr>';
			if($cms_regcode && in_array('admin',explode(',',$cms_regcode))){
				$message .= '<tr class="txt"><td class="txtC w80">验证码&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'.
				'<td class="txt txtL">'. _08_HTML::getCode( '08cms_regcode', 'login') . '</td></tr><script type="text/javascript">window._08cms_validator && _08cms_validator.submit(function(){if(this.client_t)this.client_t.value=(new Date).getTime()})</script><input type="hidden" id="client_t" name="client_t" value="">';
			}
			$message .= '<tr class="txtcenter"><td colspan="2"><input type="submit" class="btn" value="提交" /></td></tr></table></form></td></tr>';
		}
	}
	if($infloat){
		aheader();
	}else{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$lan_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$mcharset?>">
<link rel="stylesheet" rev="stylesheet" href="./images/admina/contentsAdmin.css" type="text/css" media="all">
<script type="text/javascript" src="include/js/validator.js?t=<?php echo $timestamp;?>"></script>
<link type="text/css" rel="stylesheet" href="images/common/validator.css?t=<?php echo $timestamp;?>" />
<script type="text/javascript">
var CMS_ABS = '<?php echo $cms_abs;?>';
function redirect(url){
	var l = top.location;
// 	l.assign ? l.assign(url) : l.replace(url);
	l.replace(url);
}
if(top != self)redirect(location.href);
</script>
<style type="text/css">
    .validator_message {+position: absolute;}
</style>
</head>
<body>
<?php }?>
<div style="margin:0 auto;margin-top:<?=($inajax?0:200)?>px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tb"><tr><td align="center">
<table width="400" border="0" cellpadding="8" cellspacing="0"<?=($inajax?'':' class="tabmain"')?>>
<tr style="text-align:center; text-indent:0;"><td colspan="2"><div class="conlist1 bdbot fB"><?=$lan_title?></div></td></tr>
<?=$message?>
</table>
</td></tr></table>
</div>
<?
if($infloat){
	afooter();
}else{?>
</body>
</html>
<?
}
mexit();
}
?>