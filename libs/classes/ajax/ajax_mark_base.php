<?php
/**
 * 浏览记录
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/mark/datatype/xml/aid/1/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
 
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Mark_Base extends _08_Models_Base
{
    public function __toString()
    {
        $timestamp = TIMESTAMP;
        $memberid = $this->_curuser->info['mid'];
        $m_cookie = cls_env::_COOKIE();
    	$aid = empty($this->_get['aid']) ? 0 : max(0,intval($this->_get['aid']));
    	if(!$aid || !($ntbl = atbl($aid,2))) exit();
    	if(!($this->_db->where(array('aid' => $aid))->_and(array('checked' => 1))->getTableRowNum("#__$ntbl"))) exit();
    	$cookie_key = "BR_R_$memberid";
    	$limit = 30;
    	$tmp = empty($m_cookie[$cookie_key]) ? array() : explode(';', $m_cookie[$cookie_key]);
    	in_array($aid, $tmp) || $tmp[] = "$aid,$timestamp";
    	$cookie_val = implode(';', count($tmp) > $limit ? array_splice($tmp, -$limit) : $tmp);
    	msetcookie($cookie_key, $cookie_val);
        exit;
    }
}