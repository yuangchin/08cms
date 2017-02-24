<?PHP
/**
* [验证码区] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_RegcodeBase extends cls_TagParse{
	
	
	# 返回数据结果
	protected function TagReSult(){
		if(empty($this->tag['type'])) return 0;
		return @in_array($this->tag['type'],explode(',',cls_env::mconfig('cms_regcode'))) ? 1 : 0;
	}
}
