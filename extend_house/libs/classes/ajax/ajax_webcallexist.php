<?php
/**
 * 检测楼盘是否重复
 *
 * @example   请求范例URL：index.php?/ajax/lpexist/lpname/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_WebCallExist extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-Type:text/html;CharSet=$mcharset");	
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$extcode  = empty($this->_get['extcode']) ? '' : trim($this->_get['extcode']);
		//echo "SELECT extcode FROM {$tblprefix}webcall WHERE extcode='$extcode'";
		$rec0 = $db->result_one("SELECT extcode FROM {$tblprefix}webcall WHERE extcode='$extcode'"); //
		return ($rec0 ) ? "[$extcode] 已经存在！" : 'succeed';
		//return "[$extcode] 已经存在！";
	}
}