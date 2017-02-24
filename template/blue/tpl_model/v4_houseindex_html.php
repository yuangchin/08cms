<?PHP
defined('M_COM') || exit('No Permission');
class tpl_v4_houseindex_html extends cls_Parse{
	
	# 初始化扩展的样例，如果不需做这个扩展，可删除
	protected function __construct($ParseInitConfig = array()){
		parent::__construct($ParseInitConfig);
		// 清除掉栏目首字母影响
		$letter = cls_env::GetG('letter');
		$this->_Set("_da.letter",$letter); 
		//$this->_Set("_da.addno",1); //搜索列表页addno, 改为:cls_uso::init中设置?
	}
}
