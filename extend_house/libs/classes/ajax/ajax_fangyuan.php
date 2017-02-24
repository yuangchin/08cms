<?php
/**
 * 最近浏览过的房源
 *
 * @example   请求范例URL：index.php?/ajax/fangyuan/caid/4/domain/192.168.1.153/aids/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_FangYuan extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;
		header("Content-Type:text/html;CharSet=$mcharset");
		$tblprefix = $this->_tblprefix;
		$db = $this->_db;
		
		$aids  = empty($this->_get['aids']) ? '' : trim($this->_get['aids']) ;
		$caid  = empty($this->_get['caid']) ? 0 : max(1,intval($this->_get['caid']));
		
		if(!empty($aids)){		
			$aids = explode(',',$aids);		
			$chid = $caid == 3?'3':'2';
			$i = 0;
			$_data = array();
			foreach($aids as $k => $v){
				$v = empty($v) ? '0' : max(0,intval($v));
				$_sql = $db->query("SELECT * FROM {$tblprefix}".atbl($chid)." WHERE aid = '$v'");
				$_result = $db->fetch_array($_sql);
				if($_result){
					$_data[$i]['arcurl'] = cls_ArcMain::Url($_result);
					$_data[$i]['aid'] = $_result['aid'];
					$_data[$i]['subject'] = $_result['subject']; 
					$_data[$i]['zj'] = @$_result['zj'];
					$_data[$i]['mj'] = @$_result['mj'];
					$_data[$i]['arcurl'] = $_result['arcurl'];
					$i++;
				}
			}
			// echo 
			$_data = cls_string::iconv($mcharset, "UTF-8", $_data);
			echo 'var fangyuan = ' . json_encode($_data) . ';';
		}
	}
}