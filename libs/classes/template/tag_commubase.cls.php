<?PHP
/**
* [单条交互] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_CommuBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		$NowID = $this->tag['id'] ? $this->tag['id'] : cls_Parse::Get('_a.aid');
		$NowID = (int)$NowID;
		if(!$NowID || !($commu = cls_commu::Config(@$this->tag['cuid'])) || !$commu['tbl']) $this->TagThrowException("需要指定正确的交互项目");
		$ReturnArray = self::$db->fetch_one("SELECT * FROM ".self::$tblprefix."$commu[tbl] WHERE cid='$NowID'",intval(@$this->tag['ttl']));
		return $ReturnArray;
	}
}
