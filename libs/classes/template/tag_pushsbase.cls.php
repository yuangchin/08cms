<?PHP
/**
* [推送列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_PushsBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		return $this->TagResultBySql();
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		$OneRecord = cls_pusher::ViewOneInfo($OneRecord);
		return $OneRecord;
	}
		
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return " ORDER BY trueorder,pushid DESC";
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		
		if(empty($this->tag['paid'])) $this->TagThrowException("需要指定推送位");
		
		# 兼容之前paid的数字ID的标签，暂时保留
		if(is_numeric($this->tag['paid'])){
			$this->tag['paid'] = 'push_'.$this->tag['paid'];
		}
		
		if(!($pusharea = cls_PushArea::Config($this->tag['paid']))) $this->TagThrowException("指定推送位不存在");

		$sqlselect = "SELECT *";
		$sqlfrom = " FROM ".self::$tblprefix.cls_PushArea::ContentTable($this->tag['paid'])." FORCE INDEX (trueorder)"; # 强制使用trueorder索引
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';
		$sqlwhere .= " AND checked=1 AND (startdate<'".self::$timestamp."' AND (enddate=0 OR enddate>'".self::$timestamp."'))";
		//处理两个分类
		for($i = 1;$i < 3;$i ++){
			if($classid = max(0,intval(@$this->tag["classid$i"]))){
				$sqlwhere .= " AND classid$i='$classid'";
			}
		}
		$sqlwhere = $sqlwhere ? ' WHERE '.substr($sqlwhere,5) : '';
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		return $sqlstr;
		
	}	
}
