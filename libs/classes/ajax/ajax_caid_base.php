<?php
/**
 * _08cms.fields.linkage 联动菜单使用AJAX
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/caid/ids/1/varname/archives11/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Caid_Base extends _08_Models_Base
{
    public function __toString()
    {
        if ( empty($this->_get['varname']) )
        {
            exit();
        }
        else
        {
        	$this->_get['varname'] = preg_replace('/[^\w]/', '', $this->_get['varname']);
        }
        
    	$framein = empty($this->_get['framein']) ? 0 : 1;
    	$chid = empty($this->_get['chid']) ? 0 : max(0,intval($this->_get['chid']));
    	$arr_mode = array();
    	@header("Content-Type: text/javascript");
    	$output = "var {$this->_get['varname']}=[";
    	if(!empty($ids)){
    		$ids = explode(',',$ids);
    		foreach($ids as $k) $arr_mode[] = cls_catalog::uccidsarr(empty($this->_get['coid'])? 0 : $this->_get['coid'],$chid,$framein,1,1,$k);
    		$_tmp = array();
    		foreach($arr_mode as $p){
    			foreach($p as $k2=>$p2){
    				$_tmp[$k2] = $p2;
    			}
    		}
    		cls_catalog::uccidstop($_tmp);
    		$cnt = 0;
    		foreach($_tmp as $k=>$v){
    			$output .= ($cnt ? ',' : '' )."[$k,$v[pid],'".addslashes($v['title'])."',".(empty($v['unsel']) ? 0 : 1) . ']';
    			$cnt++;
    		}	
    	}else{
    		$ccidsarr = cls_catalog::uccidsarr(0,$chid,$framein,1,1);
    		cls_catalog::uccidstop($ccidsarr);
			$cnt = 0;
    		foreach($ccidsarr as $k => $v){ 
				$output .= ($cnt ? ',' : '' )."[$k,$v[pid],'".addslashes($v['title'])."'".(empty($v['unsel']) ? '' : ',1') . ']';
				$cnt++;
			}
    	}		
    	$output .= ']';
        
        return $output;
    }
}