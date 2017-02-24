<?PHP
/**
* [时间日期] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_DateBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		if(!($datetime = @$this->tag['tname']) || !($datetime = intval($datetime))) return '';
		$formatstr = '';
		!empty($this->tag['date']) && $formatstr .= $this->tag['date'];
		!empty($this->tag['time']) && $formatstr .= ($formatstr ? ' ' : '').$this->tag['time'];
		if($formatstr) $datetime = @date($formatstr,$datetime);
		return $datetime;
	}
}
