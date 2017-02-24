<?php
/**
 * 广告标签的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_AdvTagBase extends cls_FrontPage{
	
   	protected static $_ExtendAplicationClass = 'cls_AdvTag'; 	# 当前基类的扩展应用类(即子类)的类名
 	protected $_Adv = array(); 									# 指定广告位配置
 	protected $_InitParams = array(); 							# 暂存外部传参
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_SourceType = 'adv'; # 来源类型
		$this->_Cfg['adv_period'] = max(0,intval(cls_env::mconfig('adv_period'))); 			# 广告内容缓存周期
		$this->_Cfg['adv_viewscache'] = max(0,intval(cls_env::mconfig('adv_viewscache'))); 	# 广告点击数统计周期
		$this->_Cfg['adv_view'] = empty($this->_QueryParams['adv_view']) ? false : true; 	# 预览广告效果
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	# 是否返回结果
	# 页面特征参数(如aid,cnstr,tname等页面特征)
	protected function _Init($Params = array()){
		
		$this->_Cfg['DynamicReturn'] = empty($Params['DynamicReturn']) ? false : true; # 动态结果：返回(true)/输出(false)
		
		# 页面特征参数
		$this->_SystemParams['fcaid'] = '';
		if(isset($Params['fcaid'])){
			$this->_SystemParams['fcaid'] = $Params['fcaid'];
		}elseif(isset($this->_QueryParams['fcaid'])){
			$this->_SystemParams['fcaid'] = $this->_QueryParams['fcaid'];
		}
		
		# 将传参暂存
		$this->_InitParams = $Params;
	}
	
	# 检查站点关闭
	protected function _CheckSiteClosed(){
		cls_env::CheckSiteClosed(1);
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadAdv(); 			# 读取广告位配置
		$this->_ParseSource();		# 读取广告标签
		
	}
	
	# 数据模型的通用部分
	protected function _ModelCommon(){
		$this->_MainData(); # 读取页面主体资料
		$this->_ReadPageCache(); # 读取页面缓存(可能需要借用页面资料，请注意放置顺序)
	}
	
	# 读取广告位配置
	protected function _ReadAdv(){
		$this->_SystemParams['fcaid'] = cls_fcatalog::InitID($this->_SystemParams['fcaid']);
		
		if($this->_SystemParams['fcaid']){
			$this->_Adv = cls_cache::Read('fcatalog',$this->_SystemParams['fcaid']);
		}
		if(!$this->_Adv || empty($this->_Adv['checked'])){
			throw new cls_PageException('广告位未定义或未启用');
		}
	}
	
	# 读取广告标签
	protected function _ParseSource(){
    	$this->_ParseSource = cls_cache::ReadTag('advtag','adv_'.$this->_SystemParams['fcaid']);
		if(!$this->_ParseSource){
			throw new cls_PageException('未找到指定的模板标签');
		}
	}
		
	# 读取页面主体资料
	protected function _MainData(){
		$_params = empty($this->_Adv['params']) ? array() : array_filter(@explode(',', $this->_Adv['params']));
		$this->_Cfg['CacheKey'] = "fcaid={$this->_SystemParams['fcaid']}"; # 内容缓存的特征字串
		foreach($_params as $k => $v){
			if (!empty($v)){
				list($key, ) = explode(':', $v); // 格式:"参数名":"参数值",如：farea:{ccid20}
				if(isset($this->_InitParams[$key])){
					$this->_MainData[$key] = intval($this->_InitParams[$key]);
				}elseif(isset($this->_QueryParams[$key])){
					$this->_MainData[$key] = intval($this->_QueryParams[$key]);
				}
				if(!empty($this->_MainData[$key])){
					$this->_Cfg['CacheKey'] .= "$key={$this->_MainData[$key]}";
				}
			}    
		}
	}
	
	# 读取页面缓存
	protected function _ReadPageCache(){
		if(!_08_DEBUGTAG && $this->_Cfg['adv_period']){
			$CacheFile = $this->_PageCacheFile();
			
			# 为了兼容"结果返回"与"直接打印"这两种方式，将页面缓存以意外抛出，并中止后续流程
			if(is_file($CacheFile) && (@filemtime($CacheFile) > (self::$timestamp - $this->_Cfg['adv_period'] * 60))){
				$Content = read_htmlcac($CacheFile);
				throw new cls_PageCacheException($Content);
			}
		}	
	}
	
	# 缓存动态页面结果
	protected function _SavePageCache($Content){
		if($this->_Cfg['adv_period']){
			$CacheFile = $this->_PageCacheFile();
			save_htmlcac($Content,$CacheFile);
		}
	}

	# 动态页面缓存文件名
	protected function _PageCacheFile(){
		if(empty($this->_Cfg['CacheFile'])){
			_08_FileSystemPath::checkPath(_08_CACHE_PATH.'adv_cache/adv_' . $this->_SystemParams['fcaid'], true);
			$this->_Cfg['CacheFile'] = _08_CACHE_PATH.'adv_cache/adv_'. $this->_SystemParams['fcaid'] . '/' . md5($this->_Cfg['CacheKey']).".php";
		}
		return $this->_Cfg['CacheFile'];
	}
	
	# 输出/返回动态结果
	protected function _DynamicResultOut($Content){
		$this->_AdvViews($Content);
		if(!empty($this->_Cfg['adv_view'])){ # 效果预览，直接输出结果
			echo '<style type="text/css"> li{ list-style:none; } img { border:0px; } </style>';
			echo "\n<p style='font-size:12px'> &nbsp; 以下为内容预览效果,前台受css,html等影响而展示不同效果; 空白表示无资料.</p><hr>\n";
			echo $Content;
			exit();
		}elseif(!empty($this->_Cfg['DynamicReturn'])){ # 返回原始结果(如ajax)
			return $Content;
		}else{ # 直接js输出
			js_write($Content);
			exit();
		}
	}
	
	# 广告浏览数统计
	protected function _AdvViews($Content){
		if ( empty($this->_Cfg['CacheKey']) )
        {
            return NULL;          
        }
		_08_FileSystemPath::checkPath(_08_CACHE_PATH.'stats/adv_'. $this->_SystemParams['fcaid'], true);
		$viewscachefile = _08_CACHE_PATH.'stats/adv_'. $this->_SystemParams['fcaid'] . '/' . md5($this->_Cfg['CacheKey']).".views";
		
		$file = _08_FilesystemFile::getInstance();
		$views = _08_Advertising::getViews($Content);
		if(is_file($viewscachefile) && !empty($this->_Cfg['adv_viewscache'])) {
			$file->_fopen($viewscachefile, 'rb');
			if( $file->_flock(LOCK_SH) )
			{
				$filestr = (string) $file->_fread(128);
				$file->_flock(LOCK_UN);        
			}
            else
            {
            	$filestr = '';
            }
			$time = (int)substr($filestr, 0, strpos($filestr, ','));
		} else {
			$time = self::$timestamp;
		}
		
		if(is_file($viewscachefile) && ($time > (self::$timestamp - $this->_Cfg['adv_viewscache'] * 60))) {
			$file->_fopen($viewscachefile, 'ab+');
			if( $file->_flock() )
			{
				$file->_fwrite(',' . implode(',', $views));
				$file->_flock(LOCK_UN);        
			}
		} else {
			is_file($viewscachefile) && _08_Advertising::setViews($viewscachefile);
			$file->_fopen($viewscachefile, 'wb');
			
			if( $file->_flock() )  # 如果不缓存，则不需要写入文件，直接将views存入数据表????????????????????????????
			{
				$file->_fwrite(time() . ',' . implode(',', $views));
				$file->_flock(LOCK_UN);        
			}
		}
		
		$file->_fclose();
	}
	
}
