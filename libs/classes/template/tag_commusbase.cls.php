<?PHP
/**
* [交互内容列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_CommusBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		return $this->TagResultBySql();
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return "  ORDER BY c.cid DESC";
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		if(!($commu = cls_commu::Config(@$this->tag['cuid'])) || !$commu['tbl']) $this->TagThrowException("需要指定正确的交互项目");
		
		$sqlselect = "SELECT c.*";
		$sqlfrom = " FROM ".self::$tblprefix."$commu[tbl] c".$this->ForceIndexSql('c');
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';
		if(!empty($this->tag['checked'])) $sqlwhere .= " AND c.checked <>'0'";
		$sqlwhere = $sqlwhere ? ' WHERE '.substr($sqlwhere,5) : '';
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		return $sqlstr;
	}
	
}
