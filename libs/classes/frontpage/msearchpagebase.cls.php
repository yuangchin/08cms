<?php
/**
 * 文档搜索页面的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_MsearchPageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_MsearchPage'; 	# 当前基类的扩展应用类(即子类)的类名
	protected $_WhereArray = array();								# WHERE条件语句
	protected $_SQLArray = array();									# 所有查询字串
	protected $_Channel = array();									# 当前文档模型
	protected $_Fields = array();									# 当前字段缓存
	protected $_FieldSearched = array();							# 暂存已处理过搜索的字段名，避免重复
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
 		$this->_PageCacheParams['typeid'] = 6; 			# 页面缓存类型
		$this->_Cfg['search_repeat'] = cls_env::mconfig('search_repeat');
		$this->_Cfg['search_pmid'] = cls_env::mconfig('search_pmid');
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化
	protected function _Init($Params = array()){
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_CheckSearchPermission();
		$this->_ReadChannel();
		$this->_InitSQL();
		$this->_GroupType();
		$this->_Mname();
		$this->_InOutDays();
		$this->_FieldSearch();
		$this->_OrderStr();
		$this->_WhereStr();
	}
		
	# 初始化模型
	protected function _ReadChannel(){
		$this->_SystemParams['mchid'] = max(0, intval(@$this->_QueryParams['mchid']));
		if($this->_SystemParams['mchid'] && !($this->_Channel = cls_mchannel::Config($this->_SystemParams['mchid']))){
			$this->_SystemParams['mchid'] = 0;
		}
		
		if($this->_SystemParams['mchid']){
			$this->_SystemParams['mchannel'] = $this->_Channel['cname'];
			$this->_Fields = cls_cache::Read('mfields',$this->_SystemParams['mchid']);
		}else{
			$this->_SystemParams['mchannel'] = '';
			$this->_Fields = cls_cache::Read('mfields',0);
		}
	}
	
	# 初始化所有的SQL语句
	protected function _InitSQL(){
		$this->_SQLArray['selectstr'] = "SELECT m.*,s.*";
		$this->_SQLArray['fromstr'] = "FROM ".self::$tblprefix."members AS m INNER JOIN ".self::$tblprefix."members_sub AS s ON (s.mid=m.mid)";
		$this->_SQLArray['wherestr'] = '';
		$this->_SQLArray['orderstr'] = '';
		$this->_WhereArray['checked'] = 'm.checked=1';
		
		if($this->_SystemParams['mchid']){ # 模型表处理
			$this->_SQLArray['selectstr'] .= ",c.*";
			$this->_SQLArray['fromstr'] .= " INNER JOIN ".self::$tblprefix."members_{$this->_SystemParams['mchid']} AS c ON (c.mid=m.mid)";
			$this->_WhereArray['mchid'] = "m.mchid='{$this->_SystemParams['mchid']}'";
		}else{
			if($this->_SystemParams['nochids'] = empty($this->_QueryParams['nochids']) ? '' : trim($this->_QueryParams['nochids'])){ # 排除的模型
				$this->_WhereArray['nochids'] = "m.mchid ".multi_str(explode(',',$this->_SystemParams['nochids']),1);
			}
		}
	}
	
	# 组系处理
	protected function _GroupType(){
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if(!$v['issystem']){
				if($this->_SystemParams["grouptype$k"] = max(0,intval(@$this->_QueryParams["grouptype$k"]))){
					$_WhereArray["grouptype$k"] = "m.grouptype$k = '".$this->_SystemParams["grouptype$k"]."'";
				}
			}
		}
	}
	
	# 帐号处理
	protected function _Mname(){
		if($this->_SystemParams['mname'] = empty($this->_QueryParams['mname']) ? '' : trim($this->_QueryParams['mname'])){
			$_WhereArray['mname'] = "m.mname ".sqlkw($this->_SystemParams['mname']);
			$this->_FieldSearched[] = 'mname';
		}
	}
	
	# 时间处理
	protected function _InOutDays(){
		if($this->_SystemParams['indays'] = max(0,intval(@$this->_QueryParams['indays']))){
			$this->_WhereArray['indays'] = "m.regdate>'".(self::$timestamp - 86400 * $this->_SystemParams['indays'])."'";
		}
		if($this->_SystemParams['outdays'] = max(0,intval(@$this->_QueryParams['outdays']))){
			$this->_WhereArray['indays'] = "m.regdate<'".(self::$timestamp - 86400 * $this->_SystemParams['indays'])."'";
		}
	}
	
	# 字段的筛选处理
	protected function _FieldSearch(){
		$a_field = new cls_field;
		foreach($this->_Fields as $k => $v){
			if(!$v['issystem'] && $v['issearch'] && !in_array($k,$this->_FieldSearched)){
				$a_field->init($v);
				$a_field->deal_search($a_field->field['tbl'] == 'members_sub' ? 's.' : 'c.');
				if($a_field->searchstr){
					$this->_WhereArray[$k] = $a_field->searchstr;
				}
			}
		}
		unset($a_field);
	}
	
	# 排序处理
	protected function _OrderStr(){
		foreach(array('','1','2') as $k){
			$_var_orderby = "orderby$k";
			$_var_mode = "ordermode$k";
			
			$this->_SystemParams[$_var_orderby] = empty($this->_QueryParams[$_var_orderby]) ? '' : cls_string::ParamFormat($this->_QueryParams[$_var_orderby]);
			if(!$this->_SystemParams[$_var_orderby] && !$k) $this->_SystemParams[$_var_orderby] = 'mid';
			if($this->_SystemParams[$_var_orderby]){
				$this->_SystemParams[$_var_mode] = empty($this->_QueryParams[$_var_mode]) ? 0 : 1;
				$this->_SQLArray['orderstr'] .= ($this->_SQLArray['orderstr'] ? ',' : '').'m.'.$this->_SystemParams[$_var_orderby].($this->_SystemParams[$_var_mode] ? ' ASC' : ' DESC');
			}
		}
	}

	# 查询的wherestr处理
	protected function _WhereStr(){
		
		$this->_SQLArray['wherestr'] = '';
		foreach($this->_WhereArray as $k => $v){
			$this->_SQLArray['wherestr'] .= ' AND '.$v;
		}
		if($this->_SQLArray['wherestr']){
			$this->_SQLArray['wherestr'] = 'WHERE '.substr($this->_SQLArray['wherestr'],5);
		}
		$this->_SQLArray['wherearr'] = $this->_WhereArray;# 为了兼容之前的模板，暂时保留之前的命名wherearr
	}


	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		if($this->_SystemParams['mchid']){
			$this->_ParseSource = cls_tpl::CommonTplname('member',$this->_SystemParams['mchid'],'srhtpl'.($this->_SystemParams['addno'] ? $this->_SystemParams['addno'] : ''));
		}else{
			$this->_ParseSource = cls_tpl::SpecialTplname('msearch');
		}
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 附加页编号处理
	protected function _Addno(){
		$this->_SystemParams['addno'] = max(0,intval(@$this->_QueryParams['addno']));
		if($this->_SystemParams['addno'] > 1){
			throw new cls_PageException($this->_PageName()." - 不允许的附加页");
		}
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		if($this->_inStatic) return;
		$this->_NormalVars = array('page',);
	}
	
	# 拼接Url附加参数字串(页面常规变量排除在外)
	# 为了兼容现在系统模板，search页的filterstr头字符是不能带&的
	protected function _Filterstr(){
		parent::_Filterstr();
		$this->_SystemParams['filterstr'] = substr($this->_SystemParams['filterstr'],1);
	}
	
	# 将其它数据合并到主体数据，并做相关处理
	# 为了兼容当前单一主体资料入口($_da)
	protected function _MainDataCombo(){
		parent::_MainDataCombo();
		$this->_MainData += $this->_SQLArray; # SQL数组要排除XSS处理之外
	}
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		if($isStatic){
			return '';
		}else{
			$ParamStr = $this->_SystemParams['filterstr'] ? "&{$this->_SystemParams['filterstr']}" : '';# 为了兼容现在系统模板，filterstr头字符是不能带&的
			$ParamStr .= "&page={\$page}";
			$ParamStr = substr($ParamStr ,1);
			
			$re = "search.php?".$ParamStr;
			$re = cls_env::mconfig('memberurl').$re;
			$re = cls_url::view_url($re);
			
		}
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		return $re;
	}
	
	# 生成ToolJs的参数数组，只有page=1时传送
	protected function _ToolParams(){
		if($this->_SystemParams['page'] == 1){
			$_ToolParams = array();
		}
		return @$_ToolParams;
	}
	
	# 页面名称
	protected function _PageName(){
		return "会员频道[{$this->_SystemParams['mchannel']}]搜索页";
	}
	
}
