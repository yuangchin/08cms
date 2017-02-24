<?PHP
/**
* [文档搜索列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_SearchsBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		return $this->TagResultBySql();
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		$OneRecord['nodemode'] = defined('IN_MOBILE');//设置手机版标志?????????????????
		cls_ArcMain::Parse($OneRecord,TRUE);
		return $OneRecord;
	}
		
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return " ORDER BY a.aid DESC";
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	# 搜索标签与其它标签的查询语句处理不同
	protected function CreateTagSqlBaseStr(){
		$sqlstr = $this->TagHandWherestr(); # 手动输入sqlstr
		if(!$sqlstr){
			$sqlstr = cls_Parse::Get('_da.selectstr').' '.cls_Parse::Get('_da.fromstr').' '.cls_Parse::Get('_da.wherestr');
			if(!empty($tag['validperiod'])) $sqlstr .= " AND (a.enddate=0 OR a.enddate>'$timestamp')";
			if(!empty($tag['letter']) && cls_Parse::Get('_da.letter')) $sqlstr .= " AND a.letter='".cls_Parse::Get('_da.letter')."'";
		}
		return $sqlstr;
	}
	
	# 搜索标签的排序字串的差别处理
	protected function TagCustomOrderStr($OrderStr){
		if($Return = cls_Parse::Get('_da.orderstr')){
			$OrderStr .= ' '.$Return;
		}
		return $OrderStr;
	}
	
	
}
