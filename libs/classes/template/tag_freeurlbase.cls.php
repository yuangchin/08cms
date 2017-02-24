<?PHP
/**
* [独立页URL] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_FreeurlBase extends cls_TagParse{

	# 返回数据结果
	protected function TagReSult(){
		return cls_FreeInfo::Url(@$this->tag['fid']);
	}
}
