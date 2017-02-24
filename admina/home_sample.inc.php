<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
$cmsinfos = cls_cache::Read('cmsinfos');
$updatetime = @filemtime(M_ROOT.'dynamic/cache/cmsinfos.cac.php');
$cmsinfos = cls_cache::Read('cmsinfos');
$now_svr = strtolower($_SERVER["SERVER_NAME"]);
if($timestamp - $updatetime > 3600 * 4){
	$cmsinfos['dbversion'] = $db->result_one("SELECT VERSION()");
	$cmsinfos['dbsize'] = 0;
	$query = $db->query("SHOW TABLE STATUS LIKE '$tblprefix%'", 'SILENT');
	while($table = $db->fetch_array($query)) {
		$cmsinfos['dbsize'] += $table['Data_length'] + $table['Index_length'];
	}
	$cmsinfos['dbsize'] = $cmsinfos['dbsize'] ? sizecount($cmsinfos['dbsize']) : '未知';
	$cmsinfos['attachsize'] = $db->result_one("SELECT SUM(size) FROM {$tblprefix}userfiles");
	$cmsinfos['attachsize'] = is_numeric($cmsinfos['attachsize']) ? sizecount($cmsinfos['attachsize']) : '未知';
	$cmsinfos['sys_mail'] = @ini_get('sendmail_path') ? 'Unix Sendmail ( Path: '.@ini_get('sendmail_path').')' : (@ini_get('SMTP') ? 'SMTP ( Server: '.ini_get('SMTP').')' : 'Disabled');
	$cmsinfos['serverip'] = @$_SERVER["SERVER_ADDR"];
	$cmsinfos['servername'] = @$_SERVER["SERVER_NAME"];
	$cmsinfos['lic_str'] = cls_env::GetLicense();
	cls_CacheFile::Save($cmsinfos,'cmsinfos');
}
$LicenseMessage = empty($cmsinfos['lic_str']) ? '未授权版本 &nbsp;<a href="http://www.08cms.com" target="_blank" class="cRed">>>点击购买</a>' : '授权号：'. $cmsinfos['lic_str'].' &nbsp;<a href="http://www.08cms.com" target="_blank" class="cRed">>>核实授权</a>';
$cmsinfos['server'] = PHP_OS.'/PHP '.PHP_VERSION;
$cmsinfos['safe_mode'] = @ini_get('safe_mode') ? 'ON' : 'OFF';
$cmsinfos['max_upload'] = @ini_get('upload_max_filesize') ? @ini_get('upload_max_filesize') : 'Disabled';
$cmsinfos['allow_url_fopen'] = (@ini_get('allow_url_fopen') && function_exists('fsockopen') && function_exists('gzinflate')) ? "YES" : "NO";
$cmsinfos['gdpic'] = (function_exists("imagealphablending") && function_exists("imagecreatefromjpeg") && function_exists("ImageJpeg")) ? 'YES' : 'NO';
$cmsinfos['servertime'] = date("Y-m-d  H:i");

$gid = $curuser->info['grouptype2'];
$group = cls_cache::Read('usergroups', 2);
$group = $gid && isset($group[$gid]) ? $group[$gid]['cname'] : '未知';


function show_tip($key){
	if(@include(M_ROOT.'dynamic/aguides/'.$key.'.php'))echo $aguide;
}
$registeropenstr = $registerclosed ? '已关闭': '启用';
$mspaceopenstr = $mspacedisabled ? '已关闭': '启用';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$mcharset?>">
<style type="text/css">
/* resett.css
>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/
body {text-align:center;margin:0;padding:0;background:#FFF;font-size:12px;color:#000;}
div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td{margin:0;padding:0;border:0;}
ul,li{list-style-type:none;}
img{vertical-align:top; border:0;}
strong{font-weight:normal;}
em{font-style:normal;}
h1,h2,h3,h4,h5,h6{margin:0;padding:0;font-size:12px;font-weight:normal;}
input, textarea, select{margin:2px 0px;border:1px solid #CCCCCC;font:12px Arial, Helvetica, sans-serif;line-height: 1.2em;color: #006699;background:#FFFFFF;}
textarea {overflow:auto;}
cite {float:right; font-style:normal;}

.area{margin:0 auto; width:98%; padding:4px; background:#fafafa;  clear:both;}

/* Link */
a:link {color: #333; text-decoration:none;}
a:hover {color: #134d9d; text-decoration:underline;}
a:active {color: #103d7c;}
a:visited {color: #333;text-decoration:none;}
/* Color */
.cRed,a.cRed:link,a.cRed:visited{ color:#f00; }
.cBlue,a.cBlue:link,a.cBlue:visited,a.cBlue:active{color:#1f3a87;}
.cDRed,a.cDRed:link,a.cDRed:visited{ color:#bc2931;}
.cGray,a.cGray:link,a.cGray:visited{ color: #4F544D;}
.csGray,a.csGray:link,a.csGray:visited{ color: #999;}
.cDGray,a.cDGray:link,a.cDGray:visited{ color: #666;}
.cWhite,a.cWhite:link,a.cWhite:visited{ color:#fff;}
.cBlack,a.cBlack:link,a.cBlack:visited{color:#000;}a.cBlack:hover{color:#bc2931;}
.cYellow,a.cYellow:link,a.cYellow:visited{color:#ff0;}
.cGreen,a.cGreen:link,a.cGreen:visited{color:#008000;}
/* Font  */
.fn{font-weight:normal;}
.fB{font-weight:bold;}
.f12px{font-size:12px;}
.f14px{font-size:14px;}
.f16px{font-size:16px;}
.f18px{font-size:18px;}
.f24px{font-size:24px;}
/* Other */
.left{ float: left;}
.right{ float: right;}
.clear{ clear: both; font-size:1px; width:1px; height:0; visibility: hidden; }
.clearfix:after{content:"."; display:block; height: 0; clear: both; visibility: hidden;} /* only FF */
.hidden {display: none;}
.unLine ,.unLine a{text-decoration: none;}
.noBorder{border:none;}
.txtleft{text-align:left;}
.txtright{text-align:right;}
.nobg { background:none;}
.txtindent12 {text-indent:12px;}
.txtindent24 {text-indent:24px;}
.lineheight24{line-height:24px;}
.lineheight20{line-height:20px;}
.lineheight16{line-height:16px;}
.lineheight200{line-height:200%;}
.blank1{ height:1px; clear:both;display:block; font-size:1px;overflow:hidden;}
.blank3{ height:3px; clear:both;display:block; font-size:1px;overflow:hidden;}
.blank9{ height:9px; font-size:1px;display:block; clear:both;overflow:hidden;}
.blank6{height:6px; font-size:1px; display:block;clear:both;overflow:hidden;}
.blankW6{ height:6px; display:block;background:#fff; clear:both;overflow:hidden;}
.blankW9{ height:9px; display:block;background:#fff; clear:both;overflow:hidden;}
.blank12{ height:12px; font-size:1px;clear:both;overflow:hidden;}
.blank18{ height:18px; font-size:1px;clear:both;overflow:hidden;}
.blank36{ height:36px; font-size:1px;clear:both;overflow:hidden;}
.bgc_E7F5FE { background:#E7F5FE;}
.bgc_FFFFFF { background:#FFFFFF;}
.bgc_71ACD2 { background:#71ACD2;}
.bgc_c6e9ff { background:#c6e9ff;}

/*border*/
.borderall {border:1px #134d9d solid;}
.borderall2 {border:1px #CCCCCC solid; border-right:1px #666 solid; border-bottom:1px #666 solid; }
.borderleft {border-left:1px #CCC solid;}
.borderright {border-right:1px #CCC solid;}
.bordertop {border-top:1px #CCC solid;}
.borderbottom {border-bottom:1px #005584 solid;}
.borderno {border:none;}
.borderbottom_no {border-bottom:none;}

.nav1 { height:50px; line-height:50px;}
.nav2 { padding:9px; background:#FFF;}
.table_frame { clear:both; padding:0 9px;}
.w48 { width:48%;}
.m18{margin-top:18px;}
</style>
<script type="text/javascript"> var originDomain=originDomain || document.domain; document.domain = '<?php echo $cms_top;?>'||document.domain;</script>
</head>
<body>

<div class="area">
	<div class="blank9"></div>
    <div class="nav1">
        <font class=" left f24px">欢迎使用08CMS房产管理系统</font><font class="right"><?="08CMS $cms_version $LicenseMessage"?></font>	
    </div>
    <div class="nav2 borderall" style="background:#FFF;">
        <div class="blank12"></div>
        <h1 class=" lineheight200 txtindent12 txtleft fB f14px">官方最新动态 官方新版本的发布与重要补丁的升级等动态，都会在这里显示</h1>
        <ul class="txtleft txtindent12 lineheight200" id="_08cms_dynamic_info">
            <li></li>
        </ul>
        <div class="blank12"></div>
        <h1 class=" lineheight200 txtindent12 txtleft fB f14px">技术支持服务 如果你在使用中遇到问题，可以访问以下链接寻求帮助</h1>
        <div class="blank6"></div>
        <ul class="txtindent12 lineheight200">
            <li><font class="left f14px w48"><a href="http://bbs.08cms.com" class="cBlue" target="_blank">>>官方交流论坛</a></font> <font class="right f14px w48"><a href="http://www.08cms.com" class="cBlue" target="_blank">>>08CMS 商业支持服务</a></font> </li>
        </ul>
        <div class="blank6"></div>
    </div>
	<div class="blank18"></div>
    <!--统计区块-->
	<div class="blank18"></div>
    <div class="nav2 borderall">
        <div class="blank6"></div>
        <div class="table_frame txtleft">
            <ul class="left w48 lineheight200">
                <li>你的管理级别：<?=$group?></li>
                <li>会员开放注册：<?=$registeropenstr?></li>
                <li>会员空间开放：<?=$mspaceopenstr?></li>
                <li>当前域名：<?=$cmsinfos['servername']?></li>
                <li>当前访问IP：<?=$onlineip?></li>
                <li>软件版本信息：08CMS V<?=$cms_version?></li>			
                <li>服务器IP：<?=$cmsinfos['serverip']?></li>
                <li>服务器当前时间：<?=$cmsinfos['servertime']?></li>			
                 <li>系统最近更新：<?=$last_patch?></li>			
           </ul>
            <ul class="right w48 lineheight200">
                <li>服务器信息：<?=$cmsinfos['server']?></li>
                <li>PHP安全模式：<?=$cmsinfos['safe_mode']?></li>
                <li>MySQL版本：<?=$cmsinfos['dbversion']?></li>
                <li>允许打开远程文件：<?=$cmsinfos['allow_url_fopen']?> (需开启allow_url_fopen扩展;启用fsockopen,gzinflate函数)</li>
                <li>图像GD库支持：<?=$cmsinfos['gdpic']?></li>
                <li>最大上传限制：<?=$cmsinfos['max_upload']?></li>
                <li>当前数据库大小：<?=$cmsinfos['dbsize']?></li>
                <li>当前附件总量：<?=$cmsinfos['attachsize']?></li>
                <li>邮件支持模式：<?=$cmsinfos['sys_mail']?></li>
            </ul>
        <div class="blank3"></div>
        </div>
    </div>
	<div class="blank18"></div>
    <div class="nav2 borderall">
        <?=show_tip('08cms_group')?>
    </div>
	<br><br>
    <div class="footer"><hr size="0" noshade color="#86B9D6" width="100%">