<?php
/**
 * 前台选取分销
 *
 * @example   请求范例URL：index.php?/ajax/pageload_ucar/aj_model/a,3,1/caid/33/bigpic/1/ccid20/1298/orderby/oldprices/aj_thumb/thumb,120,90/aj_pagesize/2/aj_pagenum/2/domain/192.168.1.11/
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pagepick_fenxiao extends _08_M_Ajax_pageload_Base{
    
	public function __toString(){
		//初始化及模拟da处理
		$this->_initDa(array('searchword','ccid1'));
		if($this->mcfgs[1]!=113) die('Error::chid=113');
		//常规sql条件
		$this->_getSql();
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} //echo "\n<br>1.$where;\n<br>"; //print_r($this->_ajda);
		//扩展where条件
		include_once(cls_tpl::TemplateTypeDir('function').'utags.fun.php');
		$where = cls_usql::where_str(array(
			array('ccid1'), //,0,'in',1
		),$where,$this->_ajda);  //echo "\n<br>2.$where;\n<br>"; 
		//order(默认排序,可不要这行)
		$order = $this->_getOrder(array('aid','clicks','refreshdate','updatedate'),'a.aid DESC');
		//全部sql及结果
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit";
		$result = $this->_getData($sql); //echo "\n<br>$sql;\n<br><br>";
		//扩展result结果处理
		/*
		foreach($result as $k=>$r){
			$r['licheng'] = cls_uview::number_view($r['licheng'],0.0001, 2, '-', '万公里');
			$result[$k] = $r; 	
		}*/
		//echo "<pre>";
		//print_r($result);
		//echo "</pre>";
        return $result; 
    }
}