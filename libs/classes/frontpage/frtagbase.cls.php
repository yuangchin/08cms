<?php
/**
 * 碎片标签的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_FrTagBase extends cls_FrontPage{
	
   	protected static $_ExtendAplicationClass = 'cls_FrTag'; 	# 当前基类的扩展应用类(即子类)的类名
 	protected $_Fragment = array(); 							# 指定碎片配置
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_SourceType = 'fragment'; # 来源类型
		$this->_Cfg['frview'] = empty($this->_QueryParams['frview']) ? false : true; 					# 预览碎片效果
		$this->_Cfg['frdata'] = empty($this->_QueryParams['frdata']) ? false : true; 					# 直接打印原始数据
		$this->_Cfg['charset'] = empty($this->_QueryParams['charset']) ? '' : $this->_Cfg['charset']; 	# 是否指定输出编码
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	# 是否返回结果
	# 页面特征参数(如aid,cnstr,tname等页面特征)
	protected function _Init($Params = array()){
		
		$this->_Cfg['DynamicReturn'] = empty($Params['DynamicReturn']) ? false : true; # 动态结果：返回(true)/输出(false)
		
		# 页面特征参数
		$this->_SystemParams['frname'] = '';
		if(isset($Params['frname'])){
			$this->_SystemParams['frname'] = $Params['frname'];
		}elseif(isset($this->_QueryParams['frname'])){
			$this->_SystemParams['frname'] = $this->_QueryParams['frname'];
		}
		
	}
	
	# 检查站点关闭
	protected function _CheckSiteClosed(){
		cls_env::CheckSiteClosed(1);
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadFragment(); 			# 读取碎片配置
		$this->_ParseSource();				# 读取碎片标签
	}
	
	# 数据模型的通用部分
	protected function _ModelCommon(){
		$this->_MainData(); # 读取页面主体资料
		$this->_ReadPageCache(); # 读取页面缓存(可能需要借用页面资料，请注意放置顺序)
	}
	
	# 读取碎片配置
	protected function _ReadFragment(){
		$this->_SystemParams['frname'] = cls_string::ParamFormat($this->_SystemParams['frname']);
		if($this->_SystemParams['frname']){
			$fragments = cls_cache::Read('fragments');
			$this->_Fragment = @$fragments[$this->_SystemParams['frname']];
		}
		if(!$this->_Fragment || empty($this->_Fragment['checked'])){
			throw new cls_PageException('碎片'.$this->_SystemParams['frname'].'未定义或未启用');
		}
		if(($this->_Fragment['startdate'] > self::$timestamp) || ($this->_Fragment['enddate'] && $this->_Fragment['enddate'] < self::$timestamp)){
			throw new cls_PageException('碎片'.$this->_SystemParams['frname'].'不在有效期');
		}
	}
	
	# 读取碎片标签
	protected function _ParseSource(){
		$tname = 'fr_'.$this->_Fragment['ename'];
		$ttype = empty($this->_Fragment['tclass']) ? 'rtag' : 'ctag';
    	$this->_ParseSource = cls_cache::ReadTag($ttype,$tname);
		if(!$this->_ParseSource){
			throw new cls_PageException('未找到指定的模板标签：'.$tname);
		}
	}
		
	# 读取页面主体资料
	protected function _MainData(){
		$_params = empty($this->_Fragment['params']) ? array() : array_filter(@explode(',', $this->_Adv['params']));
		$this->_Cfg['CacheKey'] = "frname={$this->_SystemParams['frname']}"; # 内容缓存的特征字串
		foreach($_params as $key){
			if(isset($this->_InitParams[$key])){ # 通过传参导入的参数
				$this->_MainData[$key] = intval($this->_InitParams[$key]);
			}elseif(isset($this->_QueryParams[$key])){ # GP参数
				$this->_MainData[$key] = intval($this->_QueryParams[$key]);
			}
			if(!empty($this->_MainData[$key])){
				$this->_Cfg['CacheKey'] .= "$key={$this->_MainData[$key]}";
			}
		}
	}
	
	# 读取页面缓存
	protected function _ReadPageCache(){
		if(!_08_DEBUGTAG && $this->_Fragment['period']){
			$CacheFile = $this->_PageCacheFile();
			
			# 为了兼容"结果返回"与"直接打印"这两种方式，将页面缓存以意外抛出，并中止后续流程
			if(is_file($CacheFile) && (@filemtime($CacheFile) > (self::$timestamp - $this->_Fragment['period'] * 60))){
				$Content = read_htmlcac($CacheFile);
				throw new cls_PageCacheException($Content);
			}
		}	
	}
	
	# 缓存动态页面结果
	protected function _SavePageCache($Content){
		if($this->_Fragment['period']){
			$CacheFile = $this->_PageCacheFile();
			save_htmlcac($Content,$CacheFile);
		}
	}

	# 动态页面缓存文件名
	protected function _PageCacheFile(){
		if(empty($this->_Cfg['CacheFile'])){
			_08_FileSystemPath::checkPath(_08_CACHE_PATH.'fragment/'.$this->_SystemParams['frname'], true);
			$this->_Cfg['CacheFile'] = _08_CACHE_PATH.'fragment/'.$this->_SystemParams['frname'] . '/' . md5($this->_Cfg['CacheKey']).".php";
		}
		return $this->_Cfg['CacheFile'];
	}
	
	# 输出动态结果
	protected function _DynamicResultOut($Content){
		# 处理指定编码
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		if($this->_Cfg['charset'] && $Content && $this->_Cfg['charset'] != $mcharset){
			$Content = cls_string::iconv($mcharset,$this->_Cfg['charset'],$Content);
		}
		
		if(!empty($this->_Cfg['frview']) || !empty($this->_Cfg['frdata'])){ # 效果预览，直接输出结果
			echo $Content;
			exit();
		}elseif(!empty($this->_Cfg['DynamicReturn'])){ # 返回原始结果(如ajax)
			return $Content;
		}else{ # 直接js输出
			js_write($Content);
			exit();
		}
	}
	
}
