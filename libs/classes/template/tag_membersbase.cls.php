<?PHP
/**
* [会员列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_MembersBase extends cls_TagParse{
		
	# 返回数据结果
	protected function TagReSult(){
		if($this->tag['tclass'] == 'members'){
			return $this->TagResultBySql();
		}else{
			$func = 'TagReSult_'.$this->tag['tclass'];
			return $this->$func();
		}
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		cls_UserMain::Parse($OneRecord,true);
		return $OneRecord;
	}
		
	# 文档数量统计(acount)标签的数据返回
	protected function TagReSult_mcount(){
		$ReturnArray = array('counts' => 0);
		if($sqlstr = $this->TagSqlStr(true)){
			$ReturnArray['counts'] = self::$db->result_one($sqlstr,intval(@$this->tag['ttl']));
		}
		return $ReturnArray;
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return ' ORDER BY m.mid DESC';
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		
		$sqlselect = "SELECT m.*,s.*";
		$sqlfrom = " FROM ".self::$tblprefix."members m".$this->ForceIndexSql('m').
				   " INNER JOIN ".self::$tblprefix."members_sub s".$this->ForceIndexSql('s')." ON s.mid=m.mid";
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';
		if(!empty($this->tag['mode'])){
			$NowID = empty($this->tag['id']) ? cls_Parse::Get('_a.mid') : $this->tag['id'];
			if(!($NowID = (int)$NowID)) $this->TagThrowException("未指定有效的关联ID");
			if($this->tag['mode'] == 'in'){
				if(!$abrel = cls_cache::Read('abrel',@$this->tag['arid'])) $this->TagThrowException("未指定有效的合辑项目");
				if($abrel['tbl']){
					$sqlfrom = " FROM ".self::$tblprefix."$abrel[tbl] b".$this->ForceIndexSql('b').
					" INNER JOIN ".self::$tblprefix."members m".$this->ForceIndexSql('m')." ON m.mid=b.inid".
					" INNER JOIN ".self::$tblprefix."members_sub s".$this->ForceIndexSql('s')." ON s.mid=m.mid";
					$sqlselect .= ",b.*";
					$sqlwhere .= " AND b.pid='".$NowID."' AND b.arid='".$this->tag['arid']."'";
				}else $sqlwhere .= " AND m.pid".$this->tag['arid']."='".$NowID."'";
			}elseif($this->tag['mode'] == 'belong'){
				if(!$abrel = cls_cache::Read('abrel',@$this->tag['arid'])) $this->TagThrowException("未指定有效的合辑项目");
				if($abrel['tbl']){
					$sqlfrom = " FROM ".self::$tblprefix."$abrel[tbl] b".$this->ForceIndexSql('b').
					" INNER JOIN ".self::$tblprefix."members m".$this->ForceIndexSql('m')." ON m.mid=b.pid".
					" INNER JOIN ".self::$tblprefix."members_sub s".$this->ForceIndexSql('s')." ON s.mid=m.mid";
					$sqlselect .= ",b.*";
					$sqlwhere .= " AND b.inid='".$NowID."' AND b.arid='".$this->tag['arid']."'";
				}else{
					if(!($pid = self::$db->result_one("SELECT mid FROM ".self::$tblprefix."members WHERE pid".$this->tag['arid']."='".$NowID."'"))){
						$this->TagThrowException("未找到所属合辑id");
					}
					$sqlwhere .= " AND m.mid='$pid'";
				}
			}
		}
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if(!empty($this->tag['ugid'.$k])){
				$sqlwhere .= " AND m.grouptype$k='".(int)$this->tag['ugid'.$k]."'";
			}
		}
		if(!empty($this->tag['chsource'])){
			$NowMchid = 0;
			if($this->tag['chsource'] == 1){
				$NowMchid = (int)cls_Parse::Get('_a.chid');
				if(empty($NowMchid)) $this->TagThrowException("无法激活会员模型id");
				$sqlwhere .= " AND m.mchid='".$NowMchid."'";
			}elseif($this->tag['chsource'] == 2){
				if(empty($this->tag['chids'])) $this->TagThrowException("需要设置会员模型id");
				$tchids = explode(',',$this->tag['chids']);
				$sqlwhere .= " AND m.mchid ".multi_str($tchids);
				if(count($tchids) == 1) $NowMchid = $tchids[0];
			}
			if(!empty($this->tag['detail']) && $NowMchid && cls_cache::Read('mchannel',$NowMchid)){
				$sqlfrom .= " INNER JOIN ".self::$tblprefix."members_$NowMchid c".$this->ForceIndexSql('c')." ON c.mid=m.mid";
				$sqlselect .= ",c.*";
			}
		}
		$sqlwhere .= " AND m.checked=1";
		if(!empty($this->tag['ids'])) $sqlwhere .= cls_DbOther::str_fromids($this->tag['ids'],'m.mid');
		$sqlwhere = $sqlwhere ? ' WHERE '.substr($sqlwhere,5) : '';
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		return $sqlstr;
	}	
}
