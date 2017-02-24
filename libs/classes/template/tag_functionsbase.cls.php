<?PHP
/**
* [自定函数列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_FunctionsBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		$ReturnArray = array();
		if(@$this->tag['func']){
			$ReturnArray = EvalFuncInTag($this->tag['func']);
			if(is_array($ReturnArray)){
				foreach($ReturnArray as $k => $v){
					$ReturnArray[$k]['sn_row'] = $i = empty($i) ? 1 : ++ $i;
				}
			}
		}
		return $ReturnArray ? $ReturnArray : array();
	}
	
	# 分页处理self::$_mp['acount']等不同类型标签的差异化部分
	protected function TagCustomMpInfo(){
		$Return = EvalFuncInTag(@$this->tag['mpfunc']);
		self::$_mp['acount'] = (int)$Return;
	}
	
	
}
