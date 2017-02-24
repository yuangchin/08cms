<?PHP
/**
* [投票列表与单个投票(选项列表)] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_VotesBase extends cls_TagParse{
	# 返回数据结果
	protected function TagReSult(){
		$func = 'TagReSult_'.$this->tag['tclass'];
		$Return =  $this->$func();
		return $Return;
	}
	
	protected function TagReSult_votes(){
		if(empty($this->tag['type'])){
			$ReturnArray = $this->TagResultBySql();
		}else{
			$ReturnArray = array();
			if(!empty($this->tag['fname']) || !empty($this->tag['id'])){
				$ReturnArray = cls_field::field_votes($this->tag['fname'],$this->tag['type'],$this->tag['id']);
				$ReturnArray = @array_slice($ReturnArray,$this->TagInitStart(),$this->TagInitLimits(),TRUE);
				foreach($ReturnArray as $k => $v){
					$ReturnArray[$k]['vid'] = $k;
					$ReturnArray[$k]['sn_row'] = $i = empty($i) ? 1 : ++ $i;
				}
			}
		}
		
		return $ReturnArray;
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return ' ORDER BY vieworder,vid DESC';
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		$sqlselect = "SELECT *";
		$sqlfrom = " FROM ".self::$tblprefix."votes";
		$sqlwhere = " WHERE checked=1 AND (enddate=0 OR enddate>'".self::$timestamp."')";
		$vcatalogs = cls_cache::Read('vcatalogs');
		if(!empty($this->tag['vsource'])){
			if(empty($vcatalogs[$this->tag['vsource']])) $this->TagThrowException("需要设置投票分类vsource");
			$sqlwhere .= " AND caid='".$this->tag['vsource']."'";
		}
		if(!empty($this->tag['vids'])){
			$vids = explode(',',$this->tag['vids']);
			foreach($vids as $k => $v) $vids[$k] = max(0,intval($v));
			$sqlwhere .= " AND vid ".multi_str($vids);
		}
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		return $sqlstr;
	}
	
	protected function TagReSult_vote(){
		$ReturnArray = array();
		$limits = $this->TagInitLimits();
	
		if(empty($this->tag['type'])){
			$NowVid = empty($this->tag['vid']) ? (int)cls_Parse::Get('_a.vid') : (int)$this->tag['vid'];
			if(!$NowVid) $this->TagThrowException("未指定投票ID");
			$vote = self::$db->fetch_one("SELECT * FROM ".self::$tblprefix."votes WHERE vid='$NowVid'",intval(@$this->tag['ttl']));
			$ReturnArray = self::$db->ex_fetch_array("SELECT * FROM ".self::$tblprefix."voptions WHERE vid='$NowVid' ORDER BY vieworder,vopid LIMIT 0,$limits",intval(@$this->tag['ttl']));
			foreach($ReturnArray as $_k => $row){
				$row['input'] = empty($vote['ismulti']) ? "<input type=\"radio\" value=\"".$row['vopid']."\" name=\"vopids[]\">" : "<input type=\"checkbox\" value=\"".$row['vopid']."\" name=\"vopids[]\">";
				$row['percent'] = $vote['totalnum'] ? @round($row['votenum'] / $vote['totalnum'],3) : 0;
				$row['percent'] = ($row['percent'] * 100).'%';
				$row['sn_row'] = $_k + 1;
				$ReturnArray[$_k] = $row;
			}
		}else{
			if(empty($this->tag['fname']) || empty($this->tag['id']))  return $this->TagThrowException("未指定投票字段");
			$votes = cls_field::field_votes($this->tag['fname'],$this->tag['type'],$this->tag['id']);
			$NowVid = (int)cls_Parse::Get('_a.vid');
			if(!$votes || !($vote = $votes[$NowVid]) || !is_array($vote)) return $ReturnArray;
			$vote['options'] = @array_slice($vote['options'],0,$limits,TRUE);
			foreach($vote['options'] as $k => $row){
				$row['input'] = empty($vote['ismulti']) ? "<input type=\"radio\" value=\"".$k."\" name=\"vopids[$NowVid][]\">" : "<input type=\"checkbox\" value=\"".$k."\" name=\"vopids[$NowVid][]\">";
				$row['percent'] = $vote['totalnum'] ? @round($row['votenum'] / $vote['totalnum'],3) : 0;
				$row['percent'] = ($row['percent'] * 100).'%';
				$ReturnArray[] = $row;
			}
		}
		return $ReturnArray;
	}
	
}
