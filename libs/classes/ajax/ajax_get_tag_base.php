<?php
/**
 * 用AJAX获取标签
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/get_tag/data_format/js/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Get_Tag_Base extends _08_Models_Base
{    
    public function __toString()
    {
    	# 按游客初始化当前会员的设定未生效????????????
        
    	$_DataFormat = '';
    	if(!empty($this->_get['data_format'])){
    		switch(strtolower($this->_get['data_format'])){
    			case 'js':
    				$_DataFormat = 'get_tag_js';
    			break;
    		}
    	}
        
        return cls_JsTag::Create(array('DataFormat' => $_DataFormat, 'DynamicReturn' => true));
    }
}