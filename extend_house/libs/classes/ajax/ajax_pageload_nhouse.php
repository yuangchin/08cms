<?php
/**
 * 新房列表
 *
 * @example   请求范例URL：
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pageload_nhouse extends _08_M_Ajax_pageload_Base{
    
	public function __toString(){
		//初始化及模拟da处理
		$this->_initDa(array('ccid1','ccid2','ccid12','ccid17','orderby','ordermode','searchword','letter'));
        if(empty($this->_ajda['aj_unsets'])) $this->_ajda['aj_unsets'] = 'abstract,content,cksm,jtxl,qtbz,nowurl';
		if($this->mcfgs[1]!=4) die('Error::chid=4');
		//常规sql条件
		$this->_getSql();
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} //echo "\n<br>1.$where;\n<br>"; //print_r($this->_ajda);
		//扩展where条件
		include_once(cls_tpl::TemplateTypeDir('function').'utags.fun.php');
		$where = cls_usql::where_str(array(
			array('ccid1',0),
            array('ccid2'),
            array('ccid12'),
			array('ccid17',0,'auto',17),
			array('subject,address','searchword'),
            array('letter'),  
		),$where,$this->_ajda);  //echo "\n<br>2.$where;\n<br>"; 
		$leixing = isset($this->_get['leixing']) ? intval($this->_get['leixing']) : '-1';
		$where = $where.($leixing==='-1' ? '' : " AND (leixing='0' OR leixing='$leixing')");
		//order(默认排序,可不要这行) 
		$order = $this->_getOrder(array('aid','dj','refreshdate','kpsj','clicks'),'a.ccid41 DESC,a.vieworder ASC');
		//全部sql及结果
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit";
		$result = $this->_getData($sql); //echo "\n<br>$sql;\n<br><br>";
		//扩展result结果处理
		foreach($result as $k=>$r){
			//$r['oldprices'] = $r['oldprices'] ? $r['oldprices'].'万' : '面议';
			$result[$k] = $r; 	
		}
        return $result; 
    }
}