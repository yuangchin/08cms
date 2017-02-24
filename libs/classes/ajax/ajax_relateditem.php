<?PHP
/**
* 指定aid的相关id的ajax处理脚本
* 后续删除这个文件：/tools/relateditem.php
*/
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Relateditem extends _08_Models_Base{
	public function __toString()
    {
		$db= _08_factory::getDBO();
        $keywords=@$this->_get['keywords'];
		$subject=@$this->_get['subject'];
		$chid=@$this->_get['chid'];
		$mcharset=$this->_mcharset;
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$wheresql = '';
		$action = empty($action) ? 'archive' : $action;
		if($action == 'archive'){
			if($mcharset != "utf-8"){
				$keywords = !empty($keywords) ? iconv("utf-8",$mcharset,trim($keywords)) : '';
				$subject = !empty($subject) ? iconv("utf-8",$mcharset,trim($subject)) : '';
			}
			$chid = empty($chid) ? 0 : max(0,intval($chid));
			if(!$chid || !($ntbl = atbl($chid))) cls_message::show('模型参数错误。');
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
		     return $result;
		}elseif($action == 'farchive'){
			if($mcharset != "utf-8"){
				$subject = !empty($subject) ? iconv("utf-8",$mcharset,$subject) : '';
			}
			$chid = empty($chid) ? 0 : max(0,intval($chid));
			if(!$chid) cls_message::show('模型参数错误。');
			$wheresql .= "chid='$chid' ";
			if(!empty($subject)) $wheresql .= "and a.subject like '%$subject%'";
			$query=$db->query("select a.aid,a.subject from {$tblprefix}farchives a where $wheresql limit 100");
			$result = array();
			if(!empty($query)){
				while($row=$db->fetch_array($query)){
					$result[] = array('aid' => $row['aid'], 'subject'=>$row['subject']);
				}
			}
			return $result;
      }
   }
}
?>