<?php
/**
 * 
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/floor/callback/_08cms.templet/querydata/tableName:field:value/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 * @since     nv50
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Floor_Base extends _08_Models_Base
{
    public function __toString()
    {
    	$v = explode(':', @$this->_get['querydata']);
        (preg_match('/^m?(?:comment|reply)s$/', $v[0]) && preg_match('/^\w+(,\w+)*$/', $v[1]) && preg_match('/^\d+(,\d+)*$/', $v[2])) || exit();
    
    	preg_match('/\bcid\b/', $v[1]) || $v[1] .= ',cid';
    
    	$querydata = array($v[0] => array());
    	$point = &$querydata[$v[0]];
        $this->_db->select($v[1])->from("#__{$v[0]}")->where('cid')->_in($v[2])->exec();
    	while( $row = $this->_db->fetch() )
        {
    		$point[$row['cid']] = $row;
    		unset($point[$row['cid']]['cid']);
    	}
    	$msg = empty($this->_get['callback']) ? jsonEncode($querydata, 1) : $this->_get['callback'] . '(' . jsonEncode($querydata, 1) . ')';
    	
        return $msg;
    }
}