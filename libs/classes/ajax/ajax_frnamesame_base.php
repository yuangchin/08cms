<?php
/**
 * 验证碎片表里该名称是否存在，-1 为未输入目录，0 为不存在，1 为存在
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/frnamesame/datatype/xml/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Frnamesame_Base extends _08_Models_Base
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
    	    $value = strtolower(trim($this->_get['value']));
            $fragments = parent::getModels('Fragments_Table');
    	    $fragments->where(array('ename' => $value))->count() && ($returnValue = 1);        	
        }
        
        return $returnValue;
    }
}