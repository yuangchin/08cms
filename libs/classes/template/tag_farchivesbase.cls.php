<?PHP
/**
* [副件列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_FarchivesBase extends cls_TagParse{
		
	# 返回数据结果
	protected function TagReSult(){
		return $this->TagResultBySql();
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		cls_url::arr_tag2atm($OneRecord,'f');
		$OneRecord['arcurl'] = cls_url::view_farcurl($OneRecord['aid'],$OneRecord['arcurl']);
		return $OneRecord;
	}
		
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return " ORDER BY a.aid DESC";
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		if(empty($this->tag['casource'])) $this->TagThrowException("需要指定副件分类");
		# 暂时对分类的数字ID做一个兼容处理(兼容核心升级后模板标签未做相应ID替换的情况)
		if(is_numeric($this->tag['casource'])) $this->tag['casource'] = 'fcatalog'.$this->tag['casource'];
		$this->tag['casource'] = cls_fcatalog::InitID($this->tag['casource']);
		if(!($chid = cls_fcatalog::Config($this->tag['casource'],'chid'))) $this->TagThrowException("指定了错误的副件模型");
		
		$sqlselect = "SELECT a.*,c.*";
		$sqlfrom = " FROM ".self::$tblprefix."farchives a".$this->ForceIndexSql('a')." INNER JOIN ".self::$tblprefix."farchives_$chid c".$this->ForceIndexSql('c')." ON c.aid=a.aid";
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';
		$sqlwhere .= " AND a.fcaid='{$this->tag['casource']}'";
		$sqlwhere .= " AND a.checked=1";
		if(!empty($this->tag['ids'])) $sqlwhere .= cls_DbOther::str_fromids($this->tag['ids'],'a.aid');
		if(!empty($this->tag['validperiod'])) $sqlwhere .= " AND (a.startdate<'".self::$timestamp."' AND (a.enddate=0 OR a.enddate>'".self::$timestamp."'))";
		$sqlwhere = $sqlwhere ? ' WHERE '.substr($sqlwhere,5) : '';
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		return $sqlstr;
	}	
}
