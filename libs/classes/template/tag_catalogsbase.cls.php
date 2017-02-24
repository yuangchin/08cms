<?PHP
/**
* [类目列表] 标签处理类，实际是类目节点列表，需要继续优化一下?????????
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_CatalogsBase extends cls_TagParse{
	
	protected $_ListCoid = 0;		# 本标签的列表类系ID，如果是栏目，则为0
	protected $_cotypes = array();	# 类系资料，需要重复使用
	
	
	# 返回数据结果
	protected function TagReSult(){
		return $this->TagResultBySql();
	}
	
	# 初始化当前标签
	protected function _TagInit(){
		$this->_ListCoid = $this->tag['listby'] == 'ca' ? 0 :  intval(str_replace('co','',$this->tag['listby']));
		$this->_cotypes= cls_cache::Read('cotypes');
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
			
		$midarr = $this->_ListCoid ? array("ccid{$this->_ListCoid}" => $OneRecord['ccid']) : array('caid' => $OneRecord['caid']);
		if($this->_ListCoid && !empty($this->tag['cainherit'])){
			if(is_numeric($this->tag['cainherit'])){
				$midarr['caid'] = $this->tag['cainherit'];
			}elseif(cls_Parse::Get('_a.caid')) $midarr['caid'] = (int)cls_Parse::Get('_a.caid');
		}
		
		$cotypes = cls_cache::Read('cotypes');
		foreach($cotypes as $k => $v){
			if($v['sortable'] && !isset($midarr["ccid$k"]) && !empty($this->tag['coinherit'.$k])){
				if(is_numeric($this->tag['coinherit'.$k])){
					$midarr['ccid'.$k] = $this->tag['coinherit'.$k];
				}elseif(cls_Parse::Get('_a.ccid'.$k)) $midarr['ccid'.$k] = (int)cls_Parse::Get('_a.ccid'.$k);
			}
		}

		$cnstr = cls_cnode::cnstr($midarr);
		foreach($midarr as $k => $v){
			$coid = $k == 'caid' ? 0 : intval(str_replace('ccid','',$k));
			if($item = $coid ? cls_cache::Read('coclass',$coid,$v) : cls_cache::Read('catalog',$v)){
				$OneRecord[$coid ? "ccid$coid" : 'caid'] = $v;
				$OneRecord[$coid ? 'ccid'.$coid.'title' : 'catalog'] = $item['title'];
			}
		}
		$cnode = cls_node::cnodearr($cnstr,defined('IN_MOBILE'));
		cls_node::re_cnode($OneRecord,$cnstr,$cnode);
		
		return $OneRecord;
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return ' ORDER BY trueorder ASC';
	}
	
	
	# 根据指定的类目关联项目，得到与激活id相关联的另一类系的所有分类id
	protected function idsbyrel($tid,$coid = 0){
		
		$ReturnArray = array();
		$cnrels = cls_cache::Read('cnrels');
		if(!($cnrel = &$cnrels[$tid])) return $ReturnArray;
		$reverse = 0;
		$nvar = $coid;
		if(in_array($coid,array($cnrel['coid'],$cnrel['coid1']))){
			if($coid == $cnrel['coid']){
				$reverse = 1;//反向关系
				$nvar = $cnrel['coid1'];
			}else $nvar = $cnrel['coid'];
		}else return $ReturnArray;
		if(!($nid = (int)cls_Parse::Get($nvar ? "_a.ccid$nvar" : '_a.caid'))) return $ReturnArray;
	
		if($reverse){
			foreach($cnrel['cfgs'] as $k => $v){
				$v = empty($v) ? array() : array_filter(explode(',',$v));
				in_array($nid,$v) && $ReturnArray[] = $k;
			}
		}else $ReturnArray = empty($cnrel['cfgs'][$nid]) ? array() : array_filter(explode(',',$cnrel['cfgs'][$nid]));
		return $ReturnArray;
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){

		$sourcestr = @$this->tag[$this->_ListCoid ? "cosource{$this->_ListCoid}" : 'casource'];

		$sqlselect = "SELECT *";
		$sqlfrom = " FROM ".self::$tblprefix.($this->_ListCoid ? "coclass{$this->_ListCoid}" : 'catalogs').$this->ForceIndexSql();
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';
		
		if(empty($sourcestr)){ # 所有顶级类目
			$sqlwhere .= " AND level=0";
		}elseif($sourcestr == 1){ # 手动选择类目id
			if($ids = array_filter(explode(',',@$this->tag[$this->_ListCoid ? 'ccids'.$this->_ListCoid : 'caids']))){
				$sqlwhere .= ' AND '.($this->_ListCoid ? 'ccid ' : 'caid ').multi_str($ids);
			}else $this->TagThrowException("需要手动选择类目id");
		}elseif($sourcestr == 2){//激活类目的所有子类目
			if($actid = (int)cls_Parse::Get($this->_ListCoid ? "_a.ccid{$this->_ListCoid}" : '_a.caid')){
				$sqlwhere .= " AND pid=$actid";
			}else $this->TagThrowException("无法取得激活类目id");
		}elseif($sourcestr == 4){ # 一级栏目
			$sqlwhere .= " AND level=1";
			$sqlwhere .= $this->_ListCoid ? " AND coid={$this->_ListCoid}" : '';
		}elseif($sourcestr == 5){ # 二级类目
			$sqlwhere .= " AND level=2";
			$sqlwhere .= $this->_ListCoid ? " AND coid={$this->_ListCoid}" : '';
		}elseif($sourcestr < 0){ # 关联联目
			if($ids = $this->idsbyrel(abs($sourcestr),$this->_ListCoid)){
				$sqlwhere .= ' AND '.($this->_ListCoid ? 'ccid ' : 'caid ').multi_str($ids);
			}else  $this->TagThrowException("未找到关联的类目id");
		}
		$sqlwhere = ' WHERE '.substr($sqlwhere.' AND closed=0',5);
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		
		return $sqlstr;
	}
	
	
}
