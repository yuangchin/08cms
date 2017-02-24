<?php
/**
 * 功能页面(或指定模板名称)的页面处理基类
 * 不支持附加页
 */
defined('M_COM') || exit('No Permission');
abstract class cls_SpecialPageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_SpecialPage'; 	# 当前基类的扩展应用类(即子类)的类名
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化
	protected function _Init($Params = array()){
		
		# 是否手机版
		$this->_inMobile = empty($Params['NodeMode']) ? false : true;
		
		# 是否加入广告js调用代码
		$this->_Cfg['LoadAdv'] = empty($Params['LoadAdv']) ? false : true;
		
		# 页面特征参数(功能页面名称或模板名称)
		if(isset($Params['spname'])){ # 传入功能页面名称
			$this->_SystemParams['spname'] = $Params['spname'];
			$this->_ParseSource = cls_tpl::SpecialTplname($this->_SystemParams['spname'],$this->_inMobile);
		}elseif(isset($Params['tplname'])){ # 直接传入模板名称
			$this->_ParseSource = $Params['tplname'];
		}
		
		# 主体数据
		if(isset($Params['_da'])){
			$this->_MainData = $Params['_da'];
		}
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 输出/返回动态结果
	protected function _DynamicResultOut($Content){
		return $Content;
	}
	
}
