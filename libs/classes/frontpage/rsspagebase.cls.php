<?php
/**
 * 生成类目节点Rss页面的处理基类
 * 不生成静态、分页、附加页
 */
defined('M_COM') || exit('No Permission');
abstract class cls_RssPageBase extends cls_FrontPage{
	
 	protected $_Node = array(); 									# 当前节点的配置数组
 	protected static $_ExtendAplicationClass = 'cls_RssPage'; 		# 当前基类的扩展应用类(即子类)的类名
 	protected $_ErrorFlag = 0; 		                                # 错误标记

	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_Cfg['rss_enabled'] = cls_env::mconfig('rss_enabled');				# Rss关闭
		$this->_Cfg['rss_ttl'] = max(0,intval(cls_env::mconfig('rss_ttl')));		# Rss缓存周期
		//echo $this->_Cfg['rss_enabled'];
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	protected function _Init($Params = array()){
		# 页面特征参数
		$this->_SystemParams['cnstr'] = isset($Params['cnstr']) ? $Params['cnstr'] : cls_cnode::cnstr($this->_QueryParams); # 得到节点字串
		if(!$this->_SystemParams['cnstr']) $this->_SystemParams['cnstr'] = '';
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_RssClosed(); # Rss是否关闭
		$this->_Cnstr(); # 分析节点字串
		$this->_Node(); # 读取节点
	}
	
	# 分析节点字串(预留扩展接口)
	protected function _Cnstr(){}
	
	# RSS是否启用
	protected function _RssClosed(){
		if(empty($this->_Cfg['rss_enabled'])){ 
			$this->_ErrorFlag = 1; 	
			throw new cls_PageException('Rss暂停访问');
		}	
	}
	# 读取节点
	protected function _Node(){
		if($this->_SystemParams['cnstr']){
			if(!($this->_Node = cls_node::cnodearr($this->_SystemParams['cnstr']))){
				$this->_ErrorFlag = 1; 	
				throw new cls_PageException($this->_PageName()." - 不存在或未配置");
			}
		}
	}
	
	# 数据模型的通用部分，不使用通用基类中的处理方法
	protected function _ModelCommon(){
		$this->_ReadPageCache(); # 读取页面缓存(可能需要借用页面资料，请注意放置顺序)
		$this->_MainData(); # 读取页面主体资料
		$this->_ParseSource(); # 获得页面模板
		$this->_Mdebug(); # 当前页面调试信息
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		if($this->_SystemParams['cnstr']){
			$this->_MainData = cls_node::cn_parse($this->_SystemParams['cnstr']);
			cls_node::re_cnode($this->_MainData,$this->_SystemParams['cnstr'],$this->_Node);
		}else{
			$this->_MainData['rss'] = cls_url::view_url('rss.php',false);
		}
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		if($this->_SystemParams['cnstr']){
			$this->_ParseSource = cls_tpl::cn_tplname($this->_SystemParams['cnstr'],$this->_Node,0,'rsstpl');
		}else{
			$this->_ParseSource = cls_tpl::SpecialTplname('rss_index');
		}
		if(!$this->_ParseSource){
			$this->_ErrorFlag = 1; 	
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
	}
	
	
	# 读取页面缓存
	protected function _ReadPageCache(){
		if(!_08_DEBUGTAG && $this->_Cfg['rss_ttl']){
			$CacheFile = $this->_PageCacheFile();
			
			# 为了兼容"结果返回"与"直接打印"这两种方式，将页面缓存以意外抛出，并中止后续流程
			if(is_file($CacheFile) && (@filemtime($CacheFile) > (self::$timestamp - $this->_Cfg['rss_ttl'] * 60))){
				$Content = read_htmlcac($CacheFile);
				//$this->_ErrorFlag = 1; 	
				throw new cls_PageCacheException($Content);
			}
		}	
	}
	
	# 缓存动态页面结果
	protected function _SavePageCache($Content){
		if($this->_Cfg['rss_ttl']){
			$CacheFile = $this->_PageCacheFile();
			save_htmlcac($Content,$CacheFile);
		}
	}

	# 动态页面缓存文件名
	protected function _PageCacheFile(){
		if(empty($this->_Cfg['CacheFile'])){
			$this->_Cfg['CacheFile'] = cls_cache::HtmlcacDir('rss').md5('rss'.$this->_SystemParams['cnstr']).'.php';
		}
		return $this->_Cfg['CacheFile'];
	}
	
	# 输出动态结果
	protected function _DynamicResultOut($Content){
		
		# 输出XML(出错了就不用xml格式输出了)
		if(empty($this->_ErrorFlag)) header("Content-type: application/xml");
		echo $Content;
		exit();
	}
	
	# 页面名称
	protected function _PageName(){
		return $this->_SystemParams['cnstr'] ? "节点[{$this->_SystemParams['cnstr']}]Rss" : "系统首页Rss";
	}
	
}
