<?php
/**
 * 售楼公司-管理的楼盘
 *
 * @example   请求范例URL：index.php?/ajax/relArchives/chid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_RelArchives extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$chid  = empty($this->_get['chid']) ? 0 : max(1,intval($this->_get['chid']));		
		$keywords  = empty($this->_get['keywords']) ? '' : trim($this->_get['keywords']);	
        $fugkey  = empty($this->_get['fugkey']) ? '' : '_'.trim($this->_get['fugkey']);
	
		if($mcharset != "utf-8"){
			$keywords = cls_string::iconv('utf-8',$mcharset,$keywords);			
		}
 
		if(!$chid) cls_message::show('文档模型参数错误。');
		$sql = "SELECT a.aid,a.subject FROM {$tblprefix}".atbl($chid)." a ";
		$sql .= " INNER JOIN  {$tblprefix}archives_$chid c ON c.aid=a.aid";
		$sql .= " WHERE c.leixing IN(0,1) ";
		$keywords && $sql .= " AND (a.subject LIKE '%$keywords%') ";
		$sql .= " ORDER BY aid DESC ";
		
		$query=$db->query("$sql limit 100"); $s = "";
		if(!empty($query)){
			while($r=$db->fetch_array($query)){ 
				$s .= "<div onclick=\"relAddItem$fugkey(this,$r[aid])\"><span id='arcid_$r[aid]'>$r[subject]</span></div>";	
			}
		}
		return js_callback($s);
	}
}