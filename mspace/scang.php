<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/adminm.fun.php";
$forward = empty($forward) ? M_REFERER : $forward;
$cuid = 11;
$inajax = empty($inajax) ? 0 : 1;
$mid = empty($mid) ? 0 : max(0,intval($mid));
if(!$mid) cls_message::show('请指定需要收藏的对象。',$forward);
$memberid || cls_message::show('请先登录会员。',$forward);
if($mid == $memberid) cls_message::show('自已的店铺，不需要收藏。',$forward);
if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('当前功能关闭。',$forward);
if(!$curuser->pmbypmid($commu['pmid'])) cls_message::show('您没有收藏店铺的权限。',$forward);

$au = new cls_userinfo;
$au->activeuser($mid);
if(!$au->info['mid'] || !$au->info['checked'] || !in_array($au->info['mchid'],$commu['chids'])) cls_message::show('请指定收藏对象',$forward);

$db->result_one("SELECT COUNT(*) FROM {$tblprefix}$commu[tbl] WHERE mid='$memberid' AND tomid='$mid'") && cls_message::show('指定的店铺已经在您的收藏中了。',$forward);
$sqlstr = "tomid='$mid',tomname='{$au->info['mname']}',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',checked=1";
$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET $sqlstr");
cls_message::show($inajax ? 'succeed' : '店铺收藏成功。',$forward);
?>

