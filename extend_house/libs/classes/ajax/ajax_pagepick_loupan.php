<?php
/**
 * 选取楼盘,尽量都共用这个
 *
 * @example   请求范例URL：index.php?/ajax/pagepick_loupan/aj_model/a,4,1/aj_thumb/thumb,120,90/aj_pagesize/50/aj_pagenum/1/leixing/1/isfenxiao/1/searchword/do/rescript/data/domain/192.168.1.11&_=1414993322115
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pagepick_loupan extends _08_M_Ajax_pageload_Base{
    
	// Demo : jQuery.getScript(cms_abs + uri2MVC('ajax=pagepick_loupan&aj_model=a,4,1&aj_unsets=abstract,content,zhoshu,tujis&aj_thumb=thumb,120,90&aj_pagesize=50&aj_pagenum=1&leixing=1&isfenxiao=1&searchword='+encodeURIComponent(lpmc.val())+'&rescript=data'),function(){ 
	public function __toString(){ 
		//初始化及模拟da处理
		$this->_initDa(array('searchword','ccid1'));
		if(empty($this->_ajda['aj_unsets'])){
			$this->_ajda['aj_unsets'] = 'abstract,content,qqqun,xqjs,loupanlogo,lphf,jgsm,ltbk,stpic,lppmtu,nowurl,arcurl,arcurl1,arcurl2,arcurl3';	
		}
		if($this->mcfgs[1]!=4) die('Error::chid=4'); 
		//常规sql条件
		$this->_getSql();
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} 
		//扩展where条件
		$where = ''; 
		//标题首字母查询
		$searchword = isset($this->_get['searchword']) ? $this->_get['searchword'] : ''; 
		$searchword = trim(cls_string::iconv('utf-8',cls_env::getBaseIncConfigs('mcharset'),$searchword));
		if(!empty($searchword)){
			$where .= (empty($where) ? '' : ' AND ')."(subject ".sqlkw($searchword)." OR subjectstr ".sqlkw($searchword).")";
		}
		//区分小区
		$leixing = empty($this->_get['leixing']) ? '0' : intval($this->_get['leixing']); 
		$where .= (empty($where) ? '' : ' AND ')."(leixing='0' OR leixing='$leixing')";
		//处理分销参数 
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$isfenxiao = empty($this->_get['isfenxiao']) ? '0' : intval($this->_get['isfenxiao']); 
		$where .= (empty($where) ? '' : ' AND ')."a.aid NOT IN(SELECT pid33 FROM {$tblprefix}".atbl(113).")"; 
		//地区类系参数
		include_once(cls_tpl::TemplateTypeDir('function').'utags.fun.php');
		$where = cls_usql::where_str(array(
			array('ccid1'), //,0,'in',1
		),$where,$this->_ajda);  //echo "\n<br>2.$where;\n<br>"; 
		//order(默认排序,可不要这行)
		#$order = $this->_getOrder(array('aid','clicks','refreshdate','updatedate','oldprices','updata','licheng'),'a.ccid62 DESC,a.refreshdate DESC');
		//全部sql及结果
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit";
		$result = $this->_getData($sql); //echo "\n<br>$sql;\n<br><br>";
		//扩展result结果处理
		foreach($result as $k=>$r){
			$r['kpsjdate'] = date('Y-m-d',$r['kpsj']); 
			$result[$k] = $r; 	
		}
		$rescript = @$this->_get['rescript'];
		if(!empty($rescript)){
			echo "var $rescript = " . jsonEncode($result) . ';';
			die();
		}else{
        	return $result; 
		}
    }
}