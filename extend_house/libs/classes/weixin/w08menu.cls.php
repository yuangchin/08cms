<?php
// 08cms菜单操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08Menu extends cls_w08MenuBase{

	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}
	
	/*/{$mobileurl},{cms_abs},oauth=base/uinfo, 此方法，扩展需求比较多…
	function fmtUrl($url){ 
		$url = str_replace(array('{mobileurl}','{cms_abs}',),array(cls_env::mconfig('mobileurl'),cls_env::mconfig('cms_abs')),$url);
		...
		return $url;
	}*/

}
