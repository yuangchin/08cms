<?php
/**
 * 检测楼盘是否重复
 *
 * @example   请求范例URL：index.php?/ajax/lpexist/lpname/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_LpExist extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-Type:text/html;CharSet=$mcharset");		
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$lpname  = empty($this->_get['lpname']) ? '' : trim($this->_get['lpname']);		
		$lpname = cls_string::iconv('utf-8',$mcharset,$lpname);
		$leixing = empty($this->_get['leixing']) ? '' : trim($this->_get['leixing']);
		// 0: 楼盘表 (预留:1:仅楼盘;2:仅小区...)
		// 5: 楼盘表+临时小区表
		// 115:写字楼楼盘, 116:商铺楼盘, 
		$rec0 = $db->result_one("SELECT aid FROM {$tblprefix}".atbl(4)." WHERE subject='$lpname'"); //楼盘表
		if($leixing==5){
			$rec5 = $db->result_one("SELECT aid FROM {$tblprefix}arctemp15 WHERE subject='$lpname'"); //临时小区表
			return ($rec0 || $rec5) ? "[$lpname] 已经存在！" : 'succeed';
		}elseif(in_array($leixing,array(115,116))){	
			$recN = $db->result_one("SELECT aid FROM {$tblprefix}".atbl($leixing)." WHERE subject='$lpname'");
			return empty($recN) ? 'succeed' : "[$lpname] 已经存在！";
		}else{
			return empty($rec0) ? 'succeed' : "[$lpname] 已经存在！";	
		}
		/*
		if((empty($leixing) && $rec0) || ($leixing=='5' && ($rec0 || $rec5))){
			return "[$lpname] 已经存在！";
		}else{
			return "succeed";	
		}*/
	}
}