<?php
// 用户相关操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08User extends cls_w08UserBase{

	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}
	
	// 授权链接,登录认证
    static function chkLogin($openid,$state='mlogin'){ 
		self::chkLoginBase($openid,$state);
		//需要的化,这里扩展吧...
	}

}
