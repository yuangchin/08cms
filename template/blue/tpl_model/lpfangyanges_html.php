<?PHP
defined('M_COM') || exit('No Permission');
class tpl_lpfangyanges_html extends cls_Parse{
	
	# 初始化扩展的样例，如果不需做这个扩展，可删除
	protected function __construct($ParseInitConfig = array()){
		parent::__construct($ParseInitConfig);
		// 清除掉从文档aid中带来的参数
		$arr = array(4,5,6,12,'shi');
		foreach($arr as $k){
			$key = is_numeric($k) ? "ccid$k" : $k;
			$this->_Set("_da.$key",cls_env::GetG($key)); 
		}
	}
}
