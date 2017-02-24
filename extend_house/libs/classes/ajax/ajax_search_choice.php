<?php
/**
 * 楼盘/小区检索--楼盘/小区名称、楼盘/小区地址。
 *
 * @example   请求范例URL：index.php?/ajax/search_choice/search_mode/subject...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Search_Choice extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-type:text/html;Charset=$mcharset");
		
		$chcfgs = array(
			'4' => 2, //出租
			'3' => 3, //二手房
			'2' => 4, //楼盘
			'612' => 115,
			'616' => 116,
			'613' => 117,
			'617' => 118,
			'614' => 119,
			'618' => 120,
		);
		$caid  = isset($this->_get['caid']) ? max(2,intval($this->_get['caid'])) : 0;
		$chid  = isset($chcfgs[$caid]) ? $chcfgs[$caid] : 0;
		
		if(!in_array($chid,array(2,3,4,115,116,117,118,119,120))){
			return '';
		}
		
        //小区的标识
        $_isxq  = empty($this->_get['_isxq']) ? 0 : 1;
        
		$tblprefix = $this->_tblprefix;
		$db = $this->_db;

		//默认searchmode为subject
		$search_mode = empty($this->_get['searchmode']) ? array('subject') : array_filter(explode(",",$this->_get['searchmode']));
        $fieldKeyArr = array('aid','subject','address');
		$val = trim(cls_string::iconv('utf-8',$mcharset,$this->_get['searchword']));
        
		$_sql_str = '';
		if(!empty($val)){
			foreach($search_mode as $k){	
                //若字段不存在于文档模型中，忽略
			    if(!in_array($k,$fieldKeyArr))continue; 
				$_sql_str .= " OR ".($k=="aid" ? "a." : "")."$k LIKE '%".$val."%' ";
			}
			$_sql_str .= " OR a.subjectstr LIKE '%".$val."%' ";
			$_sql_str = " AND (".substr($_sql_str,3).")";
		}
	
		if($chid == 4){
		  if(!empty($_isxq)){//小区
		    $_sql_str .= " AND c.leixing != '1' ";  
		  }else{//楼盘
            $_sql_str .= " AND c.leixing != '2' ";
          }
        }
		$_sql = "SELECT a.*,c.* FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON a.aid = c.aid WHERE 1=1 $_sql_str LIMIT 10";
		
		$_query=$db->query("$_sql"); //echo $_sql;
		$data = array();
		while($r=$db->fetch_array($_query)){ 
		    $arr = array();
			$_url = cls_url::view_arcurl($r);
            $arr['url'] = $_url;
			foreach(array('subject','address','aid','lpesfsl','lpczsl') as $f){
				@$arr[$f] = $r[$f];
			}  
            foreach($fieldKeyArr as $k => $v){
                $arr[$v] = $r[$v]; 
            }
			$data[] = $arr;
		}	
		return $data;

	}
}