<?php
/**
 * 检查标题是否存在
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/subject/datatype/xml/subject/标题/table/archives11/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Subject_Base extends _08_Models_Base
{
    public function __toString()
    {
        isset($this->_get['table']) && ($table = trim($this->_get['table']));
        isset($this->_get['subject']) && ($subject = trim(cls_string::iconv('utf-8',cls_env::getBaseIncConfigs('mcharset'),$this->_get['subject']))); 
		$subject = cls_string::iconv('utf-8',$this->_mcharset,$subject);	 
        if(empty($table) || empty($subject) || preg_match('/\W/', $table)){
    		$output = '-1';
    	}else{
            $output = $this->_db->where(array('subject' => $subject))->getTableRowNum("#__$table");
    	}        
        
        return $output;
    }
}