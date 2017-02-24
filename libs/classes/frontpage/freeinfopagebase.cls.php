<?php
/**
 * 独立页的处理基类
 * 不支持附加页
 */
defined('M_COM') || exit('No Permission');
abstract class cls_FreeinfoPageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_FreeinfoPage'; 	# 当前基类的扩展应用类(即子类)的类名
 	protected $Freeinfo = array(); 								# 指定独立页配置
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
 		$this->_PageCacheParams['typeid'] = 3; 						# 页面缓存类型
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化
	protected function _Init($Params = array()){
		$this->_inStatic = empty($Params['inStatic']) ? false : true; 	# 是否静态	
		
		# 页面特征参数
		$this->_SystemParams['fid'] = 0;
		if(isset($Params['fid'])){
			$this->_SystemParams['fid'] = $Params['fid'];
		}elseif(isset($this->_QueryParams['fid'])){
			$this->_SystemParams['fid'] = $this->_QueryParams['fid'];
		}
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadFreeinfo(); # 读取独立页配置
	}
	
	# 读取独立页配置
	protected function _ReadFreeinfo(){
		$this->_SystemParams['fid'] = cls_FreeInfo::InitID($this->_SystemParams['fid']);
		if(!($this->Freeinfo = cls_FreeInfo::Config($this->_SystemParams['fid']))){
			throw new cls_PageException('独立页'.$this->_SystemParams['fid'].'未定义');
		}
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		if($this->_inStatic) return;
		$this->_NormalVars = array('fid','page',);
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		$this->_CheckAllowStatic(); 			# 是否需要生成静态，注意：即使动态页面，也需要分析
	}
	
	# 是否需要静态
	# 分页url与当前页面动静态是一致的(不需要被动静态)
	protected function _CheckAllowStatic(){
		$this->_Cfg['AllowStatic'] = empty($this->Freeinfo['canstatic']) ? false : true;			# 不受总开关控制，每个独立页分别控制
		$this->_Cfg['MpUrlStatic']	= $this->_inStatic;												# 分页Url静态原则：动归动，静归静
		$this->_CheckStatic();
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		
		$this->_ParseSource = @$this->Freeinfo['tplname'];
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
		if($this->_inStatic){ # 静态时输出动态Url
			$ParamStr = "&fid={$this->_SystemParams['fid']}";
			$ParamStr = substr($ParamStr ,1);
			$_mdebug->setvar('uri',"info.php?$ParamStr");
			cls_env::SetG('_no_dbhalt',true); # 静态时关闭SQL中断错误 ????
		}
	}
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		if($isStatic){ # 静态Url套用格式
			if(!$this->_inStatic) return '';
			$re = $this->_StaticFilePre();
		}else{ # 动态Url套用格式
			if($this->_inStatic) return '';
			$ParamStr = "&fid={$this->_SystemParams['fid']}";
			$ParamStr .= $this->_SystemParams['filterstr'];
			$ParamStr .= "&page={\$page}";
			$ParamStr = substr($ParamStr ,1);
			
			$re = "info.php?".$ParamStr;
			$re = cls_url::en_virtual($re);
		}
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		$re = cls_url::view_url($re);
		return $re;
	}
	
	# 生成ToolJs的参数数组，只有page=1时传送
	protected function _ToolParams(){
		if($this->_SystemParams['page'] == 1){
			$_ToolParams = array();
			if(!$this->_inStatic) $_ToolParams['upsen'] = 1;
		}
		return @$_ToolParams;
	}
	
	# 生成静态后更新相关信息(时间，Url等)到主体记录
	protected function _UpdateStaticRecord(){
		if(!$this->_inStatic) return;
		$StaticFilePre = $this->_StaticFilePre();
		$StaticFile = cls_url::m_parseurl($StaticFilePre,array('page' => 1));
		cls_FreeInfo::ModifyOneConfig(array('arcurl' => addslashes($StaticFile)),$this->_SystemParams['fid']);
	}
	
	# 取得分页_静态文件保存格式
	protected function _StaticFilePre(){
		if(!isset($this->_Cfg['_StaticFilePre'])){ # 需要重复使用
			$this->_Cfg['_StaticFilePre'] = cls_FreeInfo::_StaticFormat($this->_SystemParams['fid']);
		}
		return $this->_Cfg['_StaticFilePre'];
	}
	
	# 页面名称
	protected function _PageName(){
		return "独立页[{$this->_SystemParams['fid']}]";
	}
	
}
