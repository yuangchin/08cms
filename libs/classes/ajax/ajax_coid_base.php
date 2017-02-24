<?php
/**
 * _08cms.fields.linkage 联动菜单使用AJAX
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/coid/chid/1/varname/archives11/coid/2/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Coid_Base extends _08_Models_Base
{
    public function __toString()
    {
        $framein = empty($this->_get['framein']) ? 0 : 1;
    	$chid = empty($this->_get['chid']) ? 0 : max(0,intval($this->_get['chid']));
    	$coid = empty($this->_get['coid']) ? 0 : max(0,intval($this->_get['coid']));
    	empty($this->_get['varname']) || empty($coid) && exit();
    	$ccidsarr = cls_catalog::uccidsarr($coid,$chid,$framein,1,1);
    	cls_catalog::uccidstop($ccidsarr);
    	header("Content-Type: text/javascript");
    	$output = "var {$this->_get['varname']}=[";
		$cnt = 0;
    	foreach($ccidsarr as $k => $v){ 
			$output .= ($cnt ? ',' : '' )."[$k,$v[pid],'".addslashes($v['title'])."'".(empty($v['unsel']) ? '' : ',1') . ']';
			$cnt++;
		}
    	$output .= ']';
        
        return $output;
    }
}