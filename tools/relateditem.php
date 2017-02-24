<?PHP
/**
* 指定aid的相关id的ajax处理脚本
*/

include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
m_clear_ob();

$inajax = 1;
$wheresql = '';
$action = empty($action) ? 'archive' : $action;
if($action == 'archive'){
	if($mcharset != "utf-8"){
		$keywords = !empty($keywords) ? iconv("utf-8","gbk",$keywords) : '';
		$subject = !empty($subject) ? iconv("utf-8","gbk",$subject) : '';
	}
	$chid = empty($chid) ? 0 : max(0,intval($chid));
	if(!$chid || !($ntbl = atbl($chid))) cls_message::ajax_info('模型参数错误。');
	if(!empty($subject)) $wheresql .= " a.subject like '%$subject%'";
	if(!empty($keywords)) $wheresql .= (empty($wheresql) ? "" : " or")." a.keywords like '%$keywords%'";
	$wheresql = empty($wheresql) ? '1=1' : $wheresql;
	$query=$db->query("select a.aid,a.subject from {$tblprefix}$ntbl a where chid='$chid' and ($wheresql) limit 100");
	$result = array();
	if(!empty($query)){
		while($row=$db->fetch_array($query)){
			$result[] = array('aid' => $row['aid'], 'subject'=>$row['subject']);
		}
	}
	echo cls_message::ajax_info($result);
}elseif($action == 'farchive'){
	if($mcharset != "utf-8"){
		$subject = !empty($subject) ? iconv("utf-8","gbk",$subject) : '';
	}
	$chid = empty($chid) ? 0 : max(0,intval($chid));
	if(!$chid) cls_message::ajax_info('模型参数错误。');
	$wheresql .= "chid='$chid' ";
	if(!empty($subject)) $wheresql .= "and a.subject like '%$subject%'";
	$query=$db->query("select a.aid,a.subject from {$tblprefix}farchives a where $wheresql limit 100");
	$result = array();
	if(!empty($query)){
		while($row=$db->fetch_array($query)){
			$result[] = array('aid' => $row['aid'], 'subject'=>$row['subject']);
		}
	}
	echo cls_message::ajax_info($result);	
}
?>