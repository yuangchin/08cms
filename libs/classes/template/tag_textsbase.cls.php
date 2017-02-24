<?PHP
/**
* [文本集列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_TextsBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		$narr = @array_slice(unserialize($this->tag['tname']),$this->TagInitStart(),$this->TagInitLimits(),TRUE);
		$ReturnArray = array();
		if(!empty($narr)){
			foreach($narr as $k => $v){
				$item = array();
				if(is_array($v)) foreach($v as $x => $y) $item['title'.$x] = $y;
				if($item){
					$item['sn_row'] = $i = empty($i) ? 1 : ++ $i;
					$ReturnArray[] = $item;
				}
			}
		}
		return $ReturnArray;
	}
}
