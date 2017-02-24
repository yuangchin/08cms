<?php
/**
 * Sitemap页面的处理基类
 * 
 */
defined('M_COM') || exit('No Permission');
abstract class cls_SitemapPageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_SitemapPage'; 	# 当前基类的扩展应用类(即子类)的类名
 	protected $_Sitemap = array(); 									# 指定Sitemap配置
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_Cfg['maxStaicPage'] = 1; 									# 注意：Sitemap页面没有分页
	}
	
	# 应用实例的基本初始化
	protected function _Init($Params = array()){
		$this->_inStatic = empty($Params['inStatic']) ? false : true; 	# 是否静态	
		
		# 页面特征参数
		$this->_SystemParams['map'] = '';
		if(isset($Params['map'])){
			$this->_SystemParams['map'] = trim($Params['map']);
		}elseif(isset($this->_QueryParams['map'])){
			$this->_SystemParams['map'] = trim($this->_QueryParams['map']);
		}
		if(!$this->_SystemParams['map']) $this->_SystemParams['map'] = 'google';
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadSitemap(); # 读取Sitemap配置
	}
	
	# 读取Sitemap配置
	protected function _ReadSitemap(){
		$sitemaps = cls_cache::Read('sitemaps');
		if(!($this->_Sitemap = $sitemaps[$this->_SystemParams['map']])){
			throw new cls_PageException($this->_PageName().'未定义');
		}
		if(empty($this->_Sitemap['available'])){
			throw new cls_PageException('指定的Sitemap已关闭');
		}

		_08_FilesystemFile::filterFileParam(@$this->_Sitemap['xml_url']);
		if(empty($this->_Sitemap['xml_url'])){
			throw new cls_PageException('请设置Sitemap的XML生成文件名');
		}
		$this->_Sitemap['ttl'] = min(24,max(0,intval(@$this->_Sitemap['ttl'])));
	}
	
	# 数据模型的通用部分，不使用通用基类中的处理方法
	protected function _ModelCommon(){
		if(!$this->_inStatic){
			$this->_ReadPageCache(); # 读取页面缓存(可能需要借用页面资料，请注意放置顺序)
		}
		$this->_MainData(); # 读取页面主体资料
		$this->_ParseSource(); # 获得页面模板
		$this->_Mdebug(); # 当前页面调试信息
	}
	
	# 读取页面缓存
	protected function _ReadPageCache(){
		if(!_08_DEBUGTAG && !empty($this->_Sitemap['ttl'])){
			$CacheFile = $this->_PageCacheFile();
			if(is_file($CacheFile) && (@filemtime($CacheFile) > (self::$timestamp - $this->_Sitemap['ttl'] * 3600))){
				$Content = file2str($CacheFile);
				throw new cls_PageCacheException($Content);
			}
		}
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		$this->_MainData = array(
			'ttl' => $this->_Sitemap['ttl'],
			'adminemail' => cls_env::getBaseIncConfigs('adminemail'),
		);
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		$this->_ParseSource = @$this->_Sitemap['tpl'];
		
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
		if($this->_inStatic){ # 静态时输出动态Url
			$_mdebug->setvar('uri',"sitemap.php?map={$this->_SystemParams['map']}");
			cls_env::SetG('_no_dbhalt',true); # 静态时关闭SQL中断错误 ????
		}
	}
	
	# 缓存动态页面结果
	protected function _SavePageCache($Content){
		if(!empty($this->_Sitemap['ttl'])){
			$CacheFile = $this->_PageCacheFile();
			if(!@str2file($Content,$CacheFile)){
				throw new cls_PageException($this->_Sitemap['xml_url']."无法写入");
			}
		}
	}

	# 动态页面缓存文件名
	protected function _PageCacheFile(){
		if(empty($this->_Cfg['CacheFile'])){
			$this->_Cfg['CacheFile'] = M_ROOT.$this->_Sitemap['xml_url'];
		}
		return $this->_Cfg['CacheFile'];
	}
	
	# 输出/返回动态结果
	protected function _DynamicResultOut($Content){
		# 输出XML
		header("Content-type: application/xml");
		echo $Content;
		exit();
	}
	
	# 取得分页_静态文件保存格式
	protected function _StaticFilePre(){
		return $this->_Sitemap['xml_url'];	
	}
	
	# 页面名称
	protected function _PageName(){
		return "Sitemap[{$this->_SystemParams['map']}]";
	}
	
}
