<?PHP
/**
* [会员节点列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_MccatalogsBase extends cls_TagParse{
	
	protected $_ListCoid = 0;		# 本标签的列表类系ID，如果是栏目，则为0
	protected $_ListOldKey = '';	# 原表中读出的ID的变量名，栏目(caid)，类系(ccid)
	protected $_ListNewKey = '';	# 返回值中的ID的变量名，栏目(caid)，类系(ccid*)
	
	# 返回数据结果
	protected function TagReSult(){
		return $this->TagResultBySql();
	}
	
	# 初始化当前标签
	protected function _TagInit(){
		$this->_ListCoid = $this->tag['listby'] == 'ca' ? 0 :  intval(str_replace('co','',$this->tag['listby']));
		$this->_ListOldKey = $this->_ListCoid ? 'ccid' : 'caid';
		$this->_ListNewKey = $this->_ListCoid ? 'ccid'.$this->_ListCoid : 'caid';
#		$this->_cotypes= cls_cache::Read('cotypes');
	}
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		if($this->_ListCoid) $OneRecord[$this->_ListNewKey] = $OneRecord[$this->_ListOldKey];
		$OneRecord = array_merge($OneRecord,cls_node::mcnodearr($this->_ListNewKey.'='.$OneRecord[$this->_ListOldKey]));
		return $OneRecord;
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return ' ORDER BY trueorder ASC';
	}
	
	# 根据标签配置拼接sqlstr，得到SQL的主要部分(select、from、where)
	protected function CreateTagSqlBaseStr(){
		
		$sqlselect = "SELECT *";
		$sqlfrom = " FROM ".self::$tblprefix.($this->_ListCoid ? "coclass{$this->_ListCoid}" : 'catalogs');
		$sqlwhere = $this->TagHandWherestr();
		$sqlwhere = $sqlwhere ? " AND $sqlwhere" : '';

		$TagOption = @$this->tag[$this->_ListCoid ? "cosource{$this->_ListCoid}" : 'casource'];
		if(empty($TagOption)){
			$sqlwhere .= " AND level=0";
		}elseif($TagOption == 1){
			if($ids = array_filter(explode(',',@$this->tag[$this->_ListCoid ? 'ccids'.$this->_ListCoid : 'caids']))){
				$sqlwhere .= ' AND '.$this->_ListOldKey.multi_str($ids);
			}else $this->TagThrowException("请手动设定ID");
		}elseif($TagOption == 2){//激活栏目的子栏目
			if($ActiveID = (int)cls_Parse::Get('_a.'.$this->_ListOldKey)){
				$sqlwhere .= " AND pid=$ActiveID";
			}else $this->TagThrowException("无法得到激活ID");
		}elseif($TagOption == 4){
			$sqlwhere .= " AND level=1";
		}elseif($TagOption == 5){
			$sqlwhere .= " AND level=2";
		}
		$sqlwhere = ' WHERE '.substr($sqlwhere.' AND closed=0',5);
		$sqlstr = $sqlselect.$sqlfrom.$sqlwhere;
		return $sqlstr;
	}
}
