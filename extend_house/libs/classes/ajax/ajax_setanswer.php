<?php
/**
 * 设置问答状态
 *
 * @example   请求范例URL：index.php?/ajax/setanswer/aid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_SetAnswer extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-Type:text/html;CharSet=$mcharset");		
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));		
		$type  = empty($this->_get['type']) ? 1 : 0;

		if(empty($aid)) return '参数出错！';
		$db->query("UPDATE {$tblprefix}".atbl(106)." SET close='$type' WHERE aid='$aid'");
		return 'succeed';
	}
}