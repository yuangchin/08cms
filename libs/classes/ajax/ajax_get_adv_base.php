<?php
/**
 * 用AJAX获取广告位数据
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Get_Adv_Base extends _08_Models_Base
{
    protected $fcaid = '';
    
    protected $params = array();
    
    public function __toString()
    {
        $fcaid = $this->fcaid;
        if ( isset($this->_get['params']) )
        {
            $params = json_decode(str_replace("'", "\"", stripslashes($this->_get['params'])), true);
        }
        else
        {
        	$params = array();
        }        
        
		$_nParams = empty($params[$fcaid]) ? array() : $params[$fcaid];
		$_nParams['fcaid'] = $fcaid;
		$_nParams['DynamicReturn'] = true;        
		$contents = cls_AdvTag::Create($_nParams);
        
        return $contents;
    }
}