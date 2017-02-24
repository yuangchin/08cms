<?php
/**
 * 管理后台的左侧单个分类的管理节点展示
 * 
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/block/datatype/xml/coid/4/ccid/1/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Block_Base extends _08_Models_Base
{
    public function __toString()
    {
        $coid = isset($this->_get['coid']) ? (int)$this->_get['coid'] : 0;
        $ccid = isset($this->_get['ccid']) ? (int)$this->_get['ccid'] : 0;
        return cls_cotype::BackMenuBlock( $coid, $ccid );
    }
    
    public function OneBackMenuBlock($UrlsArray = array())
    {
    	$output = '';
    	if($UrlsArray && $this->_curuser->isadmin()){
    		foreach($UrlsArray as $k => $v){
    			$output .= "['".addslashes($k)."','".addslashes($v)."'],";
    		}
    		$output = "[$output]";
    	}
    	return $output;
    }
    
    public static function getInstance()
    {
        return _08_factory::getInstance('_08_M_Ajax_Block');
    }
}