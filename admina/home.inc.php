<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
foreach(array('cmsinfos') as $k) $$k = cls_cache::Read($k); 
$updatetime = @filemtime(M_ROOT.'./dynamic/cache/cmsinfos.cac.php');
$now_svr = strtolower($_SERVER["SERVER_NAME"]);
$tm_base = mktime(0,0,0);


$opsarr = array(
'ck' => "checked='1'",
'nock' => "checked='0'",
'm' => "createdate>'".($timestamp-30*24*3600)."'",
'w' => "createdate>'".($timestamp-7*24*3600)."'",
'd3' => "createdate>'".($timestamp-3*24*3600)."'",
'd1' => "createdate>'".($tm_base)."'",
);

$mem_gt = array(
'mem_gt14' => '8', 
/*'mem_gt31' => '102', 
'mem_gt32' => '104', */
);

$tblarr = array(
'archive3' => atbl(3),
'archive2' => atbl(2),
'archive4' => atbl(4),
'archive107' => atbl(107),
'archive9' => atbl(9),
'archive10' => atbl(10),
'archive1' => atbl(1),
'archive106' => atbl(106),
//'archive108' => atbl(108),

'member1' => 'members',
'member2' => 'members',
'member3' => 'members',
//'member11' => 'members',
//'member12' => 'members',
'member13' => 'members',

'mem_gt14' => 'members',
/*'mem_gt31' => 'members', 
'mem_gt32' => 'members', */

'commu_zixun' => 'commu_zixun',
'commu_dp' => 'commu_zixun',
'commu_yx' => 'commu_yx',
'commu_jubao' => 'commu_jubao',
'commu_kanfang' => 'commu_kanfang',

'commu_df' => 'commu_df',
'commu_fyyx' => 'commu_fyyx',
'commu_weituo' => 'commu_weituo',
'commu_answers' => 'commu_answers',
'commu_jbask' => 'commu_jbask',

);

$tbllang = array(
'archive3' => '二手房',
'archive2' => '出租',
'archive4' => '楼盘',
'archive107' => '特价房',
'archive5' => '新房团购',
'archive9' => '求租',
'archive10' => '求购',
'archive1' => '资讯',
'archive106' => '问答',
'archive108' => '招聘',

'member1' => '普通会员',
'member2' => '经纪人',
'member3' => '经纪公司',
'member11' => '装修公司',
'member12' => '品牌商家',
'member13' => '售楼公司',

'mem_gt14' => '高级经纪人', 
/*'mem_gt31' => 'VIP公司', 
'mem_gt32' => 'VIP商家', */

'commu_zixun' => '资讯评论',
'commu_dp' => '楼盘点评',
'commu_yx' => '楼盘意向',
'commu_jubao' => '房源举报',
'commu_kanfang' => '看房活动报名',

'commu_df' => '网上订房',
'commu_fyyx' => '房源意向',
'commu_weituo' => '委托房源',
'commu_answers' => '问答答案',
'commu_jbask' => '问答举报',

);

$tblurl = array(
'archive3' => '?entry=extend&extend=usedhousearchives&caid=3',
'archive2' => '?entry=extend&extend=chuzuarchives&caid=4',
'archive4' => '?entry=extend&extend=loupanarchives&caid=2',
'archive107' => '?entry=extend&extend=tejiaarchives&caid=559',
'archive5' => '?entry=extend&extend=dinggouarchives&caid=5',
'archive9' => '?entry=extend&extend=qiuzuarchives&caid=9',
'archive10' => '?entry=extend&extend=qiugouarchives&caid=10',
'archive1' => '?entry=extend&extend=zixunarchives&caid=1',
'archive106' => '?entry=extend&extend=qa_s&caid=516',
'archive108' => '?entry=extend&extend=zhaopinarchives&caid=558',

'member1' => '?entry=extend&extend=memberspt&mchid=1',
'member2' => '?entry=extend&extend=membersjr&mchid=2',
'member3' => '?entry=extend&extend=membersjs&mchid=3',
'member11' => '?entry=extend&extend=memberszx&mchid=11',
'member12' => '?entry=extend&extend=memberszx&mchid=12',
'member13' => '?entry=extend&extend=membersales&mchid=13',

'mem_gt14' => '?entry=extend&extend=membersjr&mchid=2',
'mem_gt31' => '?entry=extend&extend=membersjr&mchid=11',
'mem_gt32' => '?entry=extend&extend=membersjr&mchid=12',

'commu_zixun' => '?entry=extend&extend=comments&cuid=1&chid=1',
'commu_dp' => '?entry=extend&extend=comments&cuid=1&chid=4',
'commu_yx' => '?entry=extend&extend=yixiangs&caid=2',
'commu_jubao' => '?entry=extend&extend=jubaos',
'commu_kanfang' => '?entry=extend&extend=commu_kanfangbm',

'commu_df' => '?entry=extend&extend=dfangs&caid=5',
'commu_fyyx' => '?entry=extend&extend=commu_yixiang&caid=3',
'commu_weituo' => '?entry=extend&extend=weituo&caid=3',
'commu_answers' => '?entry=extend&extend=commu_answers&caid=516',
'commu_jbask' => '?entry=extend&extend=jubaos&caid=3',

);
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
	foreach($tblarr as $k => $v){
	    if ( empty($v) )
        {
             continue;
        }
		foreach($opsarr as $x => $y){
			if(substr($k,0,7) == 'archive'){
				$chid = str_replace('archive','',$k);
				$x == 'ck' && $y = "chid='$chid' AND checked='1'";
				$x == 'nock' && $y = "chid='$chid' AND checked='0'";
				$x == 'm' && $y = "chid='$chid' AND createdate>'".($timestamp-30*24*3600)."'";
				$x == 'w' && $y = "chid='$chid' AND createdate>'".($timestamp-7*24*3600)."'";
				$x == 'd3' && $y = "chid='$chid' AND createdate>'".($timestamp-3*24*3600)."'";
				$x == 'd1' && $y = "chid='$chid' AND createdate>'".($tm_base)."'";
			}elseif(substr($k,0,6) == 'member'){
				$mchid = str_replace('member','',$k);
				$x == 'ck' && $y = "mchid='$mchid' AND checked='1'";
				$x == 'nock' && $y = "mchid='$mchid' AND checked='0'";				
				$x == 'm' && $y = "mchid='$mchid' AND regdate>'".($timestamp-30*24*3600)."'";
				$x == 'w' && $y = "mchid='$mchid' AND regdate>'".($timestamp-7*24*3600)."'";
				$x == 'd3' && $y = "mchid='$mchid' AND regdate>'".($timestamp-3*24*3600)."'";
				$x == 'd1' && $y = "mchid='$mchid' AND regdate>'".($tm_base)."'";
			}elseif(substr($k,0,6) == 'mem_gt'){ 
				$gtid = str_replace('mem_gt','',$k); 
				$x == 'ck' && $y = "grouptype$gtid='$mem_gt[$k]' AND checked='1'";
				$x == 'nock' && $y = "grouptype$gtid='$mem_gt[$k]' AND checked='0'";
				$x == 'm' && $y = "grouptype$gtid='$mem_gt[$k]' AND grouptype{$gtid}date<'".($timestamp+30*24*3600)."' AND grouptype{$gtid}date>'".($timestamp+7*24*3600)."'";
				$x == 'w' && $y = "grouptype$gtid='$mem_gt[$k]' AND grouptype{$gtid}date<'".($timestamp+7*24*3600)."' AND grouptype{$gtid}date>'".($timestamp+3*24*3600)."'";
				$x == 'd3' && $y = "grouptype$gtid='$mem_gt[$k]' AND grouptype{$gtid}date<'".($timestamp+3*24*3600)."' AND grouptype{$gtid}date>'".($timestamp+24*3600)."'";
				$x == 'd1' && $y = "grouptype$gtid='$mem_gt[$k]' AND grouptype{$gtid}date<'".($tm_base+24*3600)."' AND grouptype{$gtid}date>'".($tm_base)."'";
			}
			//当为交互时，where后面只有时间条件，不必类似前面三项组装额外的sql，因此不必进行substr($k,0,6) == 'commu'去组装sql的操作           
           
			if($k === 'commu_dp' || $k === 'commu_zixun'){//楼盘点评和资讯评论
			    $commudz=$tblprefix.'commu_zixun';
                $x == 'ck' && $y = " {$commudz}.checked = '1' AND {$commudz}.tocid=0  ";
				$x == 'nock' && $y = " {$commudz}.checked = '0' AND {$commudz}.tocid=0  ";
			    $x == 'm' && $y = "{$commudz}.createdate>'".($timestamp-30*24*3600)."'";
				$x == 'w' && $y = "{$commudz}.createdate>'".($timestamp-7*24*3600)."'";
				$x == 'd3' && $y = "{$commudz}.createdate>'".($timestamp-3*24*3600)."'";
				$x == 'd1' && $y = "{$commudz}.createdate>'".($tm_base)."'";
				$k == 'commu_dp' ? $v = "commu_zixun INNER JOIN {$tblprefix}archives15 a ON a.aid={$tblprefix}commu_zixun.aid ":$v = "commu_zixun INNER JOIN {$tblprefix}archives21 a ON a.aid={$tblprefix}commu_zixun.aid ";
            }
			$cmsinfos[$k][$x] = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}$v WHERE $y");
		}
	}
#\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\	
	$cmsinfos['lic_str'] = cls_env::GetLicense();
	cls_CacheFile::Save($cmsinfos,'cmsinfos');
}
$archivestr = $memberstr = $commustr = $mem_gtstr = '';
foreach($tblarr as $k => $v){
	if(substr($k,0,7) == 'archive'){
		$var = 'archivestr';
	}elseif(substr($k,0,5) == 'commu'){
		$var = 'commustr';
	}else{
		$var = 'memberstr';
	}
	substr($k,0,6)!='mem_gt' && $$var .= '<tr><td class="bgc_E7F5FE fB">'.$tbllang[$k].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['ck'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['nock'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['m'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['w'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['d3'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['d1'].'</td><td class="bgc_FFFFFF"><a href="'.$tblurl[$k].'">>></a></td></tr>';
}

$mem_gt = array(
'mem_gt14' => '8', 
/*'mem_gt31' => '102', 
// 'mem_gt32' => '104', */
);
foreach($mem_gt as $k=>$v){
	$mem_gtstr .= '<tr><td class="bgc_E7F5FE fB">'.$tbllang[$k].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['m'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['w'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['d3'].'</td><td class="bgc_FFFFFF">'.@$cmsinfos[$k]['d1'].'</td><td class="bgc_FFFFFF"><a href="'.$tblurl[$k].'">>></a></td></tr>';
}


$LicenseMessage = empty($cmsinfos['lic_str']) ? '未授权版本 &nbsp;<a href="http://www.08cms.com" target="_blank" class="cRed">>>点击购买</a>' : '授权号：'. $cmsinfos['lic_str'].' &nbsp;<a href="http://www.08cms.com" target="_blank" class="cRed">>>核实授权</a>';
$cmsinfos['server'] = PHP_OS.'/PHP '.PHP_VERSION;
$cmsinfos['safe_mode'] = @ini_get('safe_mode') ? 'ON' : 'OFF';
$cmsinfos['max_upload'] = @ini_get('upload_max_filesize') ? @ini_get('upload_max_filesize') : 'Disabled';
$cmsinfos['allow_url_fopen'] = (@ini_get('allow_url_fopen') && function_exists('fsockopen') && function_exists('gzinflate')) ? "YES" : "NO";
$cmsinfos['gdpic'] = (function_exists("imagealphablending") && function_exists("imagecreatefromjpeg") && function_exists("ImageJpeg")) ? 'YES' : 'NO';
$cmsinfos['servertime'] = date("Y-m-d  H:i");

if($curuser->info['isfounder'] == 1){
	$group = '超级管理员';
}else{
    $gid = $curuser->info['grouptype2'];
    $group = cls_cache::Read('usergroups', 2);
    $group = $gid && isset($group[$gid]) ? $group[$gid]['cname'] : '未知';
}

function show_tip($key){
	if(@include(M_ROOT.'./dynamic/aguides/'.$key.'.php'))echo $aguide;
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
            <li><font class="left f14px w48"><a href="http://tech.08cms.com" class="cBlue" target="_blank">>>官方交流论坛</a></font> <font class="right f14px w48"><a href="http://www.08cms.com" class="cBlue" target="_blank">>>08CMS 商业支持服务</a></font> </li>
        </ul>
        <div class="blank6"></div>
    </div>
	<div class="blank18"></div>
    <div class="nav2 borderall">
        <div class="table_frame">
        <div class="left w48 lineheight200">
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="bgc_71ACD2">
				 <tr>
					<td colspan="8" class="bgc_c6e9ff fB txtleft txtindent12">文档信息统计</td>
				</tr>
				<tr>
					<td class="bgc_E7F5FE fB">统计</td>
					<td class="bgc_E7F5FE fB">已审</td>
					<td class="bgc_E7F5FE fB">未审</td>
					<td class="bgc_E7F5FE fB">一月</td>
					<td class="bgc_E7F5FE fB">一周</td>
					<td class="bgc_E7F5FE fB">三天</td>
					<td class="bgc_E7F5FE fB">今天</td>
                    <td class="bgc_E7F5FE fB">管理</td>
				</tr>
				<?=$archivestr?>
    		</table>
            <div class="blank18"></div>
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="bgc_71ACD2">
              <tr>
                <td colspan="8" class="bgc_c6e9ff fB txtleft txtindent12">会员信息统计</td>
              </tr>
              <tr>
                <td class="bgc_E7F5FE fB">统计</td>
                <td class="bgc_E7F5FE fB">已审</td>
                <td class="bgc_E7F5FE fB">未审</td>
                <td class="bgc_E7F5FE fB">一月</td>
                <td class="bgc_E7F5FE fB">一周</td>
                <td class="bgc_E7F5FE fB">三天</td>
                <td class="bgc_E7F5FE fB">今天</td>
                <td class="bgc_E7F5FE fB">管理</td>
              </tr>
              <?=$memberstr?>
            </table>
        </div>
        <div class=" right w48 lineheight200">
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="bgc_71ACD2">
              <tr>
                <td colspan="8" class="bgc_c6e9ff fB txtleft txtindent12">交互信息统计</td>
              </tr>
              <tr>
                <td class="bgc_E7F5FE fB">统计</td>
                <td class="bgc_E7F5FE fB">已审</td>
                <td class="bgc_E7F5FE fB">未审</td>
                <td class="bgc_E7F5FE fB">一月</td>
                <td class="bgc_E7F5FE fB">一周</td>
                <td class="bgc_E7F5FE fB">三天</td>
                <td class="bgc_E7F5FE fB">今天</td>
                <td class="bgc_E7F5FE fB">管理</td>
              </tr>
              <?=$commustr?>
            </table>
            <div class="blank18"></div>
            <table width="100%" border="0" cellspacing="1" cellpadding="0" class="bgc_71ACD2">
              <tr>
                <td colspan="8" class="bgc_c6e9ff fB txtleft txtindent12">会员到期统计</td>
              </tr>
              <tr>
                <td class="bgc_E7F5FE fB">到期统计</td>
                <td class="bgc_E7F5FE fB">一月</td>
                <td class="bgc_E7F5FE fB">一周</td>
                <td class="bgc_E7F5FE fB">三天</td>
                <td class="bgc_E7F5FE fB">今天</td>
                <td class="bgc_E7F5FE fB">管理</td>
              </tr>
              <?=$mem_gtstr?>
            </table>
        </div>
        <div class="blank9"></div>
        </div>
    </div>
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