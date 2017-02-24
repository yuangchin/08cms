<?php
/**
 * JS调用模板标签的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_JsTagBase extends cls_FrontPage{
	
   	protected static $_ExtendAplicationClass = 'cls_JsTag'; 	# 当前基类的扩展应用类(即子类)的类名
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_SourceType = 'js'; # 来源类型
 		$this->_PageCacheParams['typeid'] = 9; 					# 页面缓存类型
		$this->_PageCacheParams['is_p'] = empty($this->_QueryParams['is_p']) ? 0 : 1;
		unset($this->_QueryParams['t']);
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	# 是否返回结果
	# 页面特征参数(如aid,cnstr,tname等页面特征)
	protected function _Init($Params = array()){
		
		# 动态结果输出方式：返回(true)/输出(false)
		$this->_Cfg['DynamicReturn'] = empty($Params['DynamicReturn']) ? false : true; 
		# 动态结果数据格式：js/json/xml等
		$this->_Cfg['DataFormat'] = isset($Params['DataFormat']) ? strtolower($Params['DataFormat']) : ''; 
		
		# 页面特征参数
		$this->_SystemParams['tname'] = '';
		if(isset($Params['tname'])){
			$this->_SystemParams['tname'] = $Params['tname'];
		}elseif(isset($this->_QueryParams['tname'])){
			$this->_SystemParams['tname'] = $this->_QueryParams['tname'];
		}
	}
	
	# 检查站点关闭
	protected function _CheckSiteClosed(){
		cls_env::CheckSiteClosed(1);
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ParseSource(); # 读取标签配置
	}
	
	# 数据模型的通用部分
	protected function _ModelCommon(){
		$this->_ReadPageCache(); # 读取页面缓存(可能需要借用页面资料，请注意放置顺序)
		$this->_MainData(); # 读取页面主体资料
	}
	# 获得页面模板
	protected function _ParseSource(){
		if($this->_SystemParams['tname']){
			$this->_ParseSource = cls_cache::cacRead('js_tag_'.$this->_SystemParams['tname'],cls_Parse::TplCacheDirFile('',2));
			if(!empty($this->_ParseSource['mp'])) unset($this->_ParseSource['mp']);
		}
		if(!$this->_ParseSource){
			throw new cls_PageException('未找到指定的模板标签');
		}
	}
		
	# 读取页面主体资料
	protected function _MainData(){
		if(!empty($this->_QueryParams['data'])){
			$this->_MainData = (array)$this->_QueryParams['data'];
		}
	}
	
	# 输出/返回动态结果
	protected function _DynamicResultOut($Content){
		if($this->_Cfg['DataFormat']){
			switch($this->_Cfg['DataFormat']){
				case 'js': # 将结果转为符合JS使用的格式
					$Content = cls_phpToJavascript::JsFormat($Content);
				break;
				case 'get_tag_js': # 转为定义JS变量的JS代码
					$Content = cls_phpToJavascript::JsFormat($Content);
					$Content = "var get_tag = '$Content';";
				break;
				case 'jswrite':	# document.write的JS代码
					$Content = cls_phpToJavascript::JsWriteCode($Content);
				break;
			}
		}
		if(empty($this->_Cfg['DynamicReturn'])){ # 直接打印输出
			if($this->_Cfg['DataFormat'] == 'write'){ # 保持现有方式，后续确认是否需要???????????????????
				header("content-type: text/javascript; charset=".cls_env::getBaseIncConfigs('mcharset'));
			}
			exit($Content);
		}else{ # 返回结果
			return $Content;
		}
	}
	
}
