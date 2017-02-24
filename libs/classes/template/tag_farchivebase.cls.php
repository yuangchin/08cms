<?PHP
/**
* [单个副件] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_FarchiveBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		
		$ReturnArray = array();
		$Nowid = intval(empty($tag['id']) ? cls_Parse::Get('_a.aid') : $tag['id']);
		if(!$Nowid) return $ReturnArray;
		
		//需要限制为有效信息
		$arc = new cls_farcedit;
		if(!$arc->set_aid($Nowid,0,intval(@$tag['ttl']))) return $ReturnArray;
		if(($arc->archive['startdate'] > self::$timestamp) || ($arc->archive['enddate'] && $arc->archive['enddate'] < self::$timestamp)) return $ReturnArray;
		$arc->archive['arcurl'] = cls_url::view_farcurl($arc->aid,$arc->archive['arcurl']);
		$ReturnArray = $arc->archive;
		unset($arc);
		
		return $ReturnArray;
	}
	
}
