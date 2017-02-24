<?php
defined('M_COM') || exit('No Permisson');
class cls_PageException extends Exception {
	
    public function __construct($errorMessage,$errorCode=0,$errorFile='',$errorLine=0) 
	{
		parent::__construct($errorMessage,$errorCode);
		//排除:后台/会员中心:生成静态, 前台才这样处理
		if(!(defined('M_ADMIN') || defined('M_MCENTER'))) header("HTTP/1.1 403 Forbidden"); 
		//echo "(".__CLASS__."::".__FUNCTION__.")<br>";
    }
	
	//print_r($this);
	
}