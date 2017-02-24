<?PHP
/**
* [自由调用列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_OutinfosBase extends cls_TagParse{
	
	protected $TagSourceDB = NULL;				# 外部数据源的数据连接
	
	# 返回数据结果
	protected function TagReSult(){
		
		$ReturnArray = array();
		if(empty($this->tag['wherestr'])) $this->TagThrowException("需要手动输入查询语句wherestr");
		
		$sqlstr = $this->tag['wherestr'];
		$sqlstr .= $this->iTagLimitStr();
		
		$ReturnArray = $this->TagSourceDB->ex_fetch_array($sqlstr,intval(@$this->tag['ttl']));
		foreach($ReturnArray as $k => $v){
			$v['sn_row'] = $k + 1;
			$ReturnArray[$k] = $v;
		}
		return $ReturnArray;
		
	}
	
	# 初始化当前标签
	# 先确定数据库连接
	protected function _TagInit(){
		if(empty($this->tag['dsid'])){
			$this->TagSourceDB = self::$db;
		}else{
			$dbsources = cls_cache::Read('dbsources');
			if(empty($dbsources[$this->tag['dsid']])) $this->TagThrowException("指定的外部数据源dsid不存在");
			
			$dbsource = $dbsources[$this->tag['dsid']];
			if($dbsource['dbpw']) $dbsource['dbpw'] = authcode($dbsource['dbpw'],'DECODE',md5(cls_env::mconfig('authkey')));
			if(empty($dbsource['dbhost']) || empty($dbsource['dbuser']) || empty($dbsource['dbname'])) $this->TagThrowException("外部数据源dsid的资料不完全");
			
			$this->TagSourceDB = & _08_factory::getDBO( 
				array('dbhost' => $dbsource['dbhost'], 'dbuser' => $dbsource['dbuser'], 'dbpw' => $dbsource['dbpw'], 
					  'dbname' => $dbsource['dbname'], 'pconnect' => 0, 'dbcharset' => $dbsource['dbcharset'])
			);
			
			if(!$this->TagSourceDB->link) $this->TagThrowException("外部数据源无法连接");
		}
	}
		
		
	function outinfos_nums(){
		
	}
		
	# 分页处理self::$_mp['acount']等不同类型标签的差异化部分
	protected function TagCustomMpInfo(){
		
		if(empty($this->tag['wherestr'])) $this->TagThrowException("需要手动输入查询语句wherestr");
		$Return = $this->TagSourceDB->result_one($this->SqlStrTransToCount($this->tag['wherestr']),intval(@$this->tag['ttl']));
		self::$_mp['acount'] = (int)$Return;
	}
}
