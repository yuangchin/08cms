<?PHP
/**
* [单个会员节点] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_McnodeBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		if(empty($this->tag['cnsource']) || empty($this->tag['cnid'])) return array();
		$cnstr = $this->tag['cnsource'].'='.$this->tag['cnid'];
		$ReturnArray = cls_node::m_cnparse($cnstr);
		$ReturnArray += cls_node::mcnodearr($cnstr);
		return $ReturnArray;
	}
	
}
