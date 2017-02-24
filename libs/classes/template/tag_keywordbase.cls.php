<?PHP
/**
* [关键词列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_KeywordBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		$ReturnArray = array();
		$uwordlinks = cls_cache::Read('uwordlinks');
		if(empty($uwordlinks)) $this->TagThrowException("请设置热门关键词");
		$TempArray = @array_slice($uwordlinks['swords'],$this->TagInitStart(),$this->TagInitLimits(),TRUE);
		foreach($TempArray as $k =>$v){
			$ReturnArray[] = array('word' => $v,'wordlink' => $uwordlinks['rwords'][$k]);
		}
		return $ReturnArray;
	
	}
	
	
}
