<?php
/**
 * 文档搜索页面的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_SearchPageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_SearchPage'; 	# 当前基类的扩展应用类(即子类)的类名
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
 		$this->_PageCacheParams['typeid'] = 4; 			# 页面缓存类型
		$this->_Cfg['search_repeat'] = cls_env::mconfig('search_repeat');
		$this->_Cfg['search_pmid'] = cls_env::mconfig('search_pmid');
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化
	protected function _Init($Params = array()){
		$this->_inMobile = defined('IN_MOBILE'); #  搜索页不支持静态
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_CheckSearchPermission();
		$this->_ReadChannel();
		$this->_InitSQL();
		$this->_Caid();
		$this->_Ccid();
		$this->_InOutDays();
		$this->_SearchWord();
		$this->_FieldSearch();
		$this->_OrderStr();
		$this->_WhereStr();
	}

	# 初始化模型
	protected function _ReadChannel(){
		$this->_SystemParams['chid'] = max(0, intval(@$this->_QueryParams['chid']));
		if(!$this->_SystemParams['chid'] || !($this->_Channel = cls_channel::Config($this->_SystemParams['chid']))){
			throw new cls_PageException('请指定需要搜索的文档模型');
		}
		$this->_SystemParams['channel'] = $this->_Channel['cname'];
		$this->_Fields = cls_cache::Read('fields',$this->_SystemParams['chid']);
	}
	
	# 初始化所有的SQL语句
	protected function _InitSQL(){
		$this->_SQLArray['selectstr'] = "SELECT a.*,c.*";
		$this->_SQLArray['fromstr'] = "FROM ".self::$tblprefix.atbl($this->_SystemParams['chid'])." AS a INNER JOIN ".self::$tblprefix."archives_{$this->_SystemParams['chid']} AS c ON (a.aid=c.aid)";
		$this->_SQLArray['wherestr'] = '';
		$this->_SQLArray['orderstr'] = '';
		$this->_WhereArray['checked'] = 'a.checked=1';
	}

	# 栏目处理
	protected function _Caid(){
		if($this->_SystemParams['caid'] = max(0,intval(@$this->_QueryParams['caid']))){
			if($catalog = cls_cache::Read('catalog',$this->_SystemParams['caid'])){
				$this->_SystemParams['catalog'] = $catalog['title'];
				if($cnsql = cnsql(0,sonbycoid($this->_SystemParams['caid']),'a.')){
					$this->_WhereArray['caid'] = $cnsql;
				}
			}else $this->_SystemParams['caid'] = 0;
		}
		unset($catalog);
	}

	# 类目处理
	protected function _Ccid(){
		$cotypes = cls_cache::Read('cotypes');
		foreach($cotypes as $k => $v){
			if($this->_SystemParams["ccid$k"] = max(0,intval(@$this->_QueryParams["ccid$k"]))){
				if($coclass = cls_cache::Read('coclass',$k,$this->_SystemParams["ccid$k"])){
					$this->_SystemParams["ccid{$k}title"] = $coclass['title'];
					if($cnsql = cnsql($k,sonbycoid($this->_SystemParams["ccid$k"],$k),'a.')){ 
						$this->_WhereArray["ccid$k"] = $cnsql;
					}
				}else $this->_SystemParams["ccid$k"] = 0;
			}
		}
		unset($coclass);
	}
	
	# 时间处理
	protected function _InOutDays(){
		if($this->_SystemParams['indays'] = max(0,intval(@$this->_QueryParams['indays']))){
			$this->_WhereArray['indays'] = "a.createdate>'".(self::$timestamp - 86400 * $this->_SystemParams['indays'])."'";
		}
		if($this->_SystemParams['outdays'] = max(0,intval(@$this->_QueryParams['outdays']))){
			$this->_WhereArray['indays'] = "a.createdate<'".(self::$timestamp - 86400 * $this->_SystemParams['indays'])."'";
		}
	}
	# 关键词搜索处理
	protected function _SearchWord(){
		$this->_SystemParams['searchmode'] = empty($this->_QueryParams['searchmode']) ? array('subject') : explode(',',trim($this->_QueryParams['searchmode']));
		$this->_FieldSearched = array(); # 已进行关键词搜索的字段，以免后面重复处理这些字段。
		if($this->_SystemParams['searchword'] = cls_string::CutStr(trim(@$this->_QueryParams['searchword']),50,'')){
			
			# 处理全文搜索，如果执行了，则不进行其它字段的关键词搜索
			if(in_array('fulltxt',$this->_SystemParams['searchmode'])){		
				if($this->Channel['fulltxt'] && isset($this->_Fields[$this->Channel['fulltxt']])) $fulltxt = $this->Channel['fulltxt'];
				if(!empty($fulltxt)){
					$this->_SystemParams['searchmode'] = array('fulltxt');	
					# 下面的fulltxt最好改成search表示为 '关键词搜索' 产生的sqlstr，可能涉及到模板，后续统一修改
					$this->_WhereArray['fulltxt'] = ($this->_Fields[$fulltxt]['tbl'] == "archives_{$this->_SystemParams['chid']}" ? 'c.' : 'a.')."$fulltxt ".sqlkw($this->_SystemParams['searchword']);
					$this->_FieldSearched[] = $fulltxt;
				}else{ # 否则将全文搜索的选择取消
					$key = array_search('fulltxt',$this->_SystemParams['searchmode']);
					unset($this->_SystemParams['searchmode'][$key]);
				}
			}
			# 处理多个字段的关键词搜索(不含全文搜索)
			if(!in_array('fulltxt',$this->_SystemParams['searchmode'])){
				$_where_array = array();
				foreach($this->_SystemParams['searchmode'] as $k => $v){
					if(in_array($v,array('subject','keywords')) || !empty($this->_Fields[$v]['issearch'])){
						$_where_array[] = ($this->_Fields[$v]['tbl'] == "archives_{$this->_SystemParams['chid']}" ? 'c.' : 'a.')."$v ".sqlkw($this->_SystemParams['searchword']);
						$this->_FieldSearched[] = $v;
					}else unset($this->_SystemParams['searchmode'][$k]);
				}
				if($_where_array){
					# 下面的fulltxt最好改成search表示为 '关键词搜索' 产生的sqlstr，可能涉及到模板，后续统一修改
					$this->_WhereArray['fulltxt'] = "(".implode(' OR ',$_where_array).")";
				}
				unset($_where_array);
			}
		}
	}
	
	# 字段的筛选处理
	protected function _FieldSearch(){
		$a_field = new cls_field;
		foreach($this->_Fields as $k => $v){
			if($v['issearch'] && !in_array($k,$this->_FieldSearched)){
				$a_field->init($v);
				$a_field->deal_search($a_field->field['tbl'] == "archives_{$this->_SystemParams['chid']}" ? "c." : "a.");
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
			if(!$this->_SystemParams[$_var_orderby] && !$k) $this->_SystemParams[$_var_orderby] = 'aid';
			if($this->_SystemParams[$_var_orderby]){
				$this->_SystemParams[$_var_mode] = empty($this->_QueryParams[$_var_mode]) ? 0 : 1;
				$this->_SQLArray['orderstr'] .= ($this->_SQLArray['orderstr'] ? ',' : '').'a.'.$this->_SystemParams[$_var_orderby].($this->_SystemParams[$_var_mode] ? ' ASC' : ' DESC');
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
		$this->_ParseSource = cls_tpl::SearchTplname(
			array(
				'chid' => $this->_SystemParams['chid'],
				'caid' => $this->_SystemParams['caid'],
				'addno' => $this->_SystemParams['addno'],
				'nodemode' => defined('IN_MOBILE'),
			)
		);
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
	
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		if($isStatic){
			return '';
		}else{
			$ParamStr = $this->_SystemParams['filterstr'] ? "&{$this->_SystemParams['filterstr']}" : '';# 为了兼容现在系统模板，filterstr头字符是不能带&的
			$ParamStr .= "&page={\$page}";
			$ParamStr = substr($ParamStr ,1);
			
			$re = ($this->_inMobile ? cls_env::mconfig('mobiledir').'/' : '')."search.php?".$ParamStr;
			$re = cls_url::view_url($re);
		}
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		return $re;
	}
	
	# 将其它数据合并到主体数据，并做相关处理
	# 为了兼容当前单一主体资料入口($_da)
	protected function _MainDataCombo(){
		parent::_MainDataCombo();
		$this->_MainData += $this->_SQLArray; # SQL数组要排除XSS处理之外
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
		return "[{$this->_SystemParams['channel']}]搜索页";
	}
	
}
