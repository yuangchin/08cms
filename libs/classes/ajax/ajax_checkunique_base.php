<?php
/**
 * 根据会员认证类型id,mchid,认证字段 是否重复；后续考虑可兼容文档
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/checkunique/var/test/mctid/1/mchid/1/oldval/test/datatype/xml/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_CheckUnique_Base extends _08_Models_Base
{
    public function __toString()
    {
    	$val = empty($this->_get['val']) ? '' : $this->_get['val'];
    	$oldval = empty($this->_get['oldval']) ? '' : $this->_get['oldval'];
    	$mctid = empty($this->_get['mctid']) ? 0 : max(0,intval($this->_get['mctid']));
    	$mchid = empty($this->_get['mchid']) ? 0 : max(0,intval($this->_get['mchid']));
    	$mctypes = cls_cache::Read('mctypes');
    	$mfields = cls_cache::Read('mfields',$mchid); 
    	$field = @$mctypes[$mctid]['field']; 
    	if(!isset($mctypes[$mctid]) || !isset($mfields[$field])){
    		$msg = '参数错误！';
    	}else{
            $row = $this->_db->select('mid')->from("#__{$mfields[$field]['tbl']}")->where(array($field => $val))->limit(1)->exec()->fetch();
     		$mid = $row['mid'];
    		$msg = $mid ? 'Exists' : 'OK';
    	}
    	//echo $msg;
    	if(empty($this->_get['method'])){ //纯js认证
    		ajax_info(array('msg'=>$msg));
    	}else{ //使用validator.js认证
    		if($oldval && $msg=='Exists' && $oldval==$val) $msg = "";	
    		elseif($msg=='Exists') $msg = "号码已经存在！";	
    		elseif($msg=='OK') $msg = "";
    		return $msg;	
    	}		
    }
}