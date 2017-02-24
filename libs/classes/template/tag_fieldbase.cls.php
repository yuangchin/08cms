<?PHP
/**
* [字段标题值] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_FieldBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		$val = @$this->tag['tname'];
		$typearr = array(
			'archive' => array('','chid'),
			'member' => array('m','mchid'),
			'farchive' => array('f','chid'),
			'catalog' => array('cn',0),
			'coclass' => array('cn','coid'),
			'commu' => array('cu','cuid'),
			'push' => array('pa','paid'), 
		);
		if(!($type = @$typearr[$this->tag['type']]) || (!$fields = cls_cache::Read($type[0].'fields',$type[1] ? cls_Parse::Get('_a.'.$type[1]) : '')) || !($field = @$fields[$this->tag['fname']])){
			return $val;
		}
		return view_field_title($val,$field,@$this->tag['limits']);
		
	}
	
}
