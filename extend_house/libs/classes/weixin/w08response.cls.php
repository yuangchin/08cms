<?php
// 消息回复（被动回复）
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08Response extends cls_w08ResponseBase{

	function __construct($post,$cfg,$re=0){
		parent::__construct($post,$cfg,$re); 
	}	
	
	// 图片消息
    function reText($re=0){ 
		$re = $this->reTextBase(1);
		die($re);
    }
	
	// 图片消息
    function reImage($re=0){ 
		$this->reImageBase(1);
		die('');
    }
	
	// 文本消息(扩展)
	/*
    function reText(){  
		$detail = cls_w08Basic::iconv('utf-8',cls_env::getBaseIncConfigs('mcharset'),$this->post->Content);
		if(strstr($detail,'888ext')){
			die($detail);
		}else{
			return $this->reTextBase();	
		}
	}*/
	
	/*/ 地理位置消息
    function reLocation(){ 
		//print_r($this->post);
		die('');    
    }*/
	
}
