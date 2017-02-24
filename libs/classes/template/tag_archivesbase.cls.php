<?PHP
/**
* [文档列表/文档数量统计] 标签处理类，继承cls_TagParse
* 先作意外抛出，暂时无法展示意外，有待后续处理???
*/



defined('M_COM') || exit('No Permission');
abstract class cls_Tag_ArchivesBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		if($this->tag['tclass'] == 'archives'){
			return $this->TagResultBySql();
		}else{
			$func = 'TagReSult_'.$this->tag['tclass'];
			return $this->$func();
		}
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		$OneRecord['nodemode'] = defined('IN_MOBILE');//设置手机版标志?????????????????
		cls_ArcMain::Parse($OneRecord,TRUE);
		return $OneRecord;
	}
		
	# 文档数量统计(acount)标签的数据返回
	protected function TagReSult_acount(){
		$ReturnArray = array('counts' => 0);
		if($sqlstr = $this->TagSqlStr(true)){
			$ReturnArray['counts'] = self::$db->result_one($sqlstr,intval(@$this->tag['ttl']));
		}
		return $ReturnArray;
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return ' ORDER BY a.aid DESC';
	}
	
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		
		$sqlselect = "SELECT a.*";
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';
		
		# 处理文档模型ID的设置选项chsource
		$NowChid = 0; # 当前指定的文档模型id，可能是多个ID字串(,分隔)
		$SingleChid = 0; # 是否指定了单个chid，如果chid指定了多个ID，此变量设为0
		if(!empty($this->tag['chsource'])){ 
			if($this->tag['chsource'] == 1){ # 激活文档模型ID(只激活单个ID)
				if(!($NowChid = (int)cls_Parse::Get('_a.chid'))) $this->TagThrowException("无法取得激活的文档模型id");
				$sqlwhere .= " AND a.chid='$NowChid'";
				$SingleChid = $NowChid;
			}elseif($this->tag['chsource'] == 2){ # 手动指定文档模型ID
				if(empty($this->tag['chids'])){
					$this->TagThrowException("需要手动指定chids");	
				}else{
					$NowChidArray = explode(',',$this->tag['chids']);
					$sqlwhere .= " AND a.chid ".multi_str($NowChidArray);
					$NowChid = $NowChidArray[0];
					if(count($NowChidArray) == 1) $SingleChid = $NowChid;
				}
			}
		}
		if(empty($NowChid)) $this->TagThrowException("需要指定文档模型id");
		
		if(!($ntbl = atbl($NowChid))) $this->TagThrowException("请指定正确的文档模型id");
		$NowStid = cls_channel::Config($NowChid,'stid');
		$sqlfrom = " FROM ".self::$tblprefix."$ntbl a".$this->ForceIndexSql('a');

		if(!empty($this->tag['mode'])){
			$this->tag['id'] = empty($this->tag['id']) ? (int)cls_Parse::Get('_a.aid') : (int)$this->tag['id'];
			if(empty($this->tag['id'])) $this->TagThrowException("未指定相关id");
			if($this->tag['mode'] == 'in'){
				if(!$abrel = cls_cache::Read('abrel',@$this->tag['arid'])) $this->TagThrowException("未指定有效的合辑项目");
				if($abrel['tbl']){
					$sqlfrom = " FROM ".self::$tblprefix."$abrel[tbl] b".$this->ForceIndexSql('b')." INNER JOIN ".self::$tblprefix."$ntbl a".$this->ForceIndexSql('a')." ON a.aid=b.inid";
					$sqlselect .= ",b.*";
					$sqlwhere .= " AND b.pid='".$this->tag['id']."' AND b.arid='".$this->tag['arid']."'";
				}else $sqlwhere .= " AND a.pid".$this->tag['arid']."='".$this->tag['id']."'";
			}elseif($this->tag['mode'] == 'belong'){
				if(!$abrel = cls_cache::Read('abrel',@$this->tag['arid']))  $this->TagThrowException("未指定有效的合辑项目");
				if($abrel['tbl']){
					$sqlfrom = " FROM ".self::$tblprefix."$abrel[tbl] b".$this->ForceIndexSql('b')." INNER JOIN ".self::$tblprefix."$ntbl a".$this->ForceIndexSql('a')." ON a.aid=b.pid";
					$sqlselect .= ",b.*";
					$sqlwhere .= " AND b.inid='".$this->tag['id']."' AND b.arid='".$this->tag['arid']."'";
				}else{
					if(!($_ntbl = atbl($this->tag['id'],2)) || !($pid = self::$db->result_one("SELECT pid".$this->tag['arid']." FROM ".self::$tblprefix."$_ntbl WHERE aid='".$this->tag['id']."'"))){
						$this->TagThrowException("未找到合辑id");
					}
					$sqlwhere .= " AND a.aid='$pid'";
				}
			}elseif($this->tag['mode'] == 'relate'){
				if(!($_ntbl = atbl($this->tag['id'],2)) || !($r = self::$db->fetch_one("SELECT keywords,relatedaid FROM ".self::$tblprefix."$_ntbl WHERE aid='".$this->tag['id']."'"))){
					$this->TagThrowException("未找到合辑id");//????					
				}
				if(!empty($r['relatedaid'])){
					if(!($arr = array_unique(explode(',',$r['relatedaid'])))){
						$this->TagThrowException("未设置关联的aid");
					}
					$sqlwhere .= " AND a.aid ".multi_str($arr);
				}elseif(!empty($r['keywords'])){
					$arr = array_unique(explode(',',$r['keywords']));
					$i = 0;
					$keywordstr = '';
					foreach($arr as $str){
						$keywordstr .= ($keywordstr ? ' OR ' : '')."a.keywords LIKE '%".addcslashes($str,'%_')."%'";
						$i ++;
						if($i > 5) break;
					}
					if(!$keywordstr){
						$this->TagThrowException("未找到相关的关键词");
					}
					$sqlwhere .= " AND a.aid!='".(int)cls_Parse::Get('_a.aid')."' AND ($keywordstr)";
				}else  $this->TagThrowException("相关性设置错误");
			}
		}
		
		# 处理栏目筛选
		if(!empty($this->tag['casource'])){
			$caidArray = array();
			if($this->tag['casource'] == '1'){//手动指定caid，如果caids指定为空，属SQL错误
				$caidArray = array_filter(explode(',',@$this->tag['caids']));
				if(empty($caidArray)) $this->TagThrowException("需要设置caids");
			}elseif($this->tag['casource'] == '2'){ # 使用激活caid，如果未找开激活变量，则不影响sqlwhere
				if($NowCaid = (int)cls_Parse::Get('_a.caid')) $caidArray[] = $NowCaid;
			}
			if($caidArray && !empty($this->tag['caidson'])){
				$_sons = array();
				foreach($caidArray as $k) $_sons = array_merge($_sons,sonbycoid($k,0));
				$caidArray = array_unique($_sons);
				unset($_sons);
			}
			$caidArray && $sqlwhere .= " AND a.caid ".multi_str($caidArray);
		}
		
		# 处理有效类系的分类筛选
		$cotypes = cls_cache::Read('cotypes');
		$splitbls = cls_cache::Read('splitbls');
		foreach($cotypes as $k => $v){
			if(in_array($k,$splitbls[$NowStid]['coids'])){//排除使用无效类系组成sql
				$ccidArray = array();
				if(!empty($this->tag['cosource'.$k])){
					if($this->tag['cosource'.$k] == '1'){ # 手动指定，如果ccids$k指定为空，属SQL错误
						$ccidArray = array_filter(explode(',',@$this->tag['ccids'.$k]));
						if(empty($ccidArray)) $this->TagThrowException("需要设置ccids$k");
					}elseif($this->tag['cosource'.$k] == '2'){ # 使用激活ccid$k
						if($NowCcid = (int)cls_Parse::Get('_a.ccid'.$k)) $ccidArray[] = $NowCcid;
					}
					if($ccidArray && !empty($this->tag['ccidson'.$k])){
						$_sons = array();
						foreach($ccidArray as $y) $_sons = array_merge($_sons,sonbycoid($y,$k));
						$ccidArray = array_unique($_sons);
						unset($_sons);
					}
					if($ccidArray && $str = cnsql($k,$ccidArray,'a.')) $sqlwhere .= ' AND '.$str;
				}
			}
		}
		
		# 需要模型表的信息
		if(!empty($this->tag['detail']) && $SingleChid && cls_channel::Config($SingleChid)){
			$sqlfrom .= " INNER JOIN ".self::$tblprefix."archives_$SingleChid c ON c.aid=a.aid";
			$sqlselect .= ",c.*";
		}
		if(!empty($this->tag['ids'])){
			$sqlwhere .= cls_DbOther::str_fromids($this->tag['ids'],'a.aid');
		}
		
		# 处理排除的模型id
		if(!empty($this->tag['nochids'])){
			if($nochids = explode(',',$this->tag['nochids'])){
				$sqlwhere .= " AND a.chid ".multi_str($nochids,1);
			}
		}
		$sqlwhere .= " AND a.checked=1";
		
		# 显示激活会员发布的文档
		if(!empty($this->tag['space'])){
			if($NowMid = (int)cls_Parse::Get('_a.mid')){
				$sqlwhere .= " AND a.mid='".$NowMid."'";
			}else{
				$this->TagThrowException("无法激活mid");
			}
		}
		
		# 指定激活的会员空间的个人分类
		if(!empty($this->tag['ucsource'])){
			if($NowUcid = (int)cls_Parse::Get('_a.ucid')){
				$sqlwhere .= " AND a.ucid='".$NowUcid."'";
			}
		}
		
		# 只显示有效期内的文档
		if(!empty($this->tag['validperiod'])){
			$sqlwhere .= " AND (a.enddate=0 OR a.enddate>'".self::$timestamp."')";
		}
		$sqlwhere = $sqlwhere ? ' WHERE '.substr($sqlwhere,5) : '';
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		
		return $sqlstr;
	}
	
	
}
