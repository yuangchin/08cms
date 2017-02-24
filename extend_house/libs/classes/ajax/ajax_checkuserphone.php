<?php
/**
 * 认证[房产参数]会员电话号码是否唯一(注册,修改会员资料,升级等。)
 *
 * @example   请求范例URL：index.php?/ajax/checkUserPhone/val/...
 * @author    peace#08cms.com
 * @copyright Copyright (C) 2008 - 2015 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_checkUserPhone extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		//$timestamp = TIMESTAMP; 
		header("Content-Type:text/html;CharSet=$mcharset");
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$val  = empty($this->_get['val']) ? '-1' : $this->_get['val'];
		$old  = empty($this->_get['old']) ? '' : $this->_get['old'];
		
		if($old && $val==$old) return ''; //修改...
		/*
		if(!preg_match("/^1\d{2}[-]?\d{8}$/", $val)){
			return '号码错误！';
		}
		*/
		$telisunique = cls_env::mconfig('telisunique');
		if(empty($telisunique)) return '';
		
		//$chid = isset($this->_get['chid']) ? intval($this->_get['chid']) : 0;
        //if(!in_array($chid,array(2,3,9,10))) return '参数错误！';

		$sql = "SELECT mid FROM {$tblprefix}members_sub WHERE lxdh='$val'";
		$mid = $db->result_one($sql);
		// 是否普通会员或经纪人
		$msg = $mid ? '号码已经存在于系统会员中，不能使用！' : '';
		
		if($msg) return $msg;
		return '';
		
	}
}