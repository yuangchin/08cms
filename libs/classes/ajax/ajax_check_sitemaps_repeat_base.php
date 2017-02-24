<?php
/**
 * 验证网站地图表里该名称是否存在，-1 为未输入目录，0 为不存在，1 为存在
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_sitemaps_repeat/datatype/xml/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Sitemaps_Repeat_Base extends _08_Models_Base
{
    public function __toString()
    {
        $returnValue = 0;
    	if( empty($this->_get['value']) )
        {
            $returnValue = -1;
    	}
        else
        {
    	    $value = addslashes($this->_get['value']);
            $sitemaps = parent::getModels('Sitemaps_Table');
    	    $sitemaps->where(array('ename' => $value))->read('1') && ($returnValue = 1);        	
        }
        
        return $returnValue;
    }
}