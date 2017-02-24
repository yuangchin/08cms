<?php
/**
 * 副件内容页的处理基类
 * 不支持附加页
 */
defined('M_COM') || exit('No Permission');
abstract class cls_FarchivePageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_FarchivePage'; 	# 当前基类的扩展应用类(即子类)的类名
	protected $_Arc = NULL; 										# 当前副件的基本实例
	
	# 页面生成的外部执行入口
	# 执行静态：inStatic => true
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
			
		if(!empty($Params['arc'])){
			$this->_Arc = $Params['arc']; # 注意：实例的引用
			$this->_SystemParams['aid'] = $this->_Arc->aid;
		}else{
			$this->_SystemParams['aid'] = isset($Params['aid']) ? $Params['aid'] : @$this->_QueryParams['aid'];
		}
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadArchive(); # 读取副件资料
	}
	
	# 读取副件资料
	protected function _ReadArchive(){
		$this->_SystemParams['aid'] = max(0,intval($this->_SystemParams['aid']));
		
		if(!$this->_SystemParams['aid']){
			throw new cls_PageException($this->_PageName()." - 未指定副件ID");
		}
		
		if(empty($this->_Arc)){
			$this->_Arc = new cls_farcedit();
			if(!$this->_Arc->set_aid($this->_SystemParams['aid'],0)){
				throw new cls_PageException($this->_PageName()." - 副件不存在");
			}
		}
		
		# 检查是否审核
		$this->_ArcChecked();
		
		# 检查有效期
		$this->_ArcValid();
	}
	
	# 检查是否审核
	protected function _ArcChecked(){
		if(!$this->_Arc->archive['checked']){
			if($this->_inStatic || !self::$curuser->isadmin()){
				throw new cls_PageException($this->_PageName()." - 副件未通过审核");
			}
		}
	}
	
	# 检查有效期
	protected function _ArcValid(){
		if(($this->_Arc->archive['startdate'] > self::$timestamp) || ($this->_Arc->archive['enddate'] && $this->_Arc->archive['enddate'] < self::$timestamp)){
			throw new cls_PageException("指定副件不在有效期");
		}
	}
	
	# 附加页编号处理
	protected function _Addno(){
		$this->_SystemParams['addno'] = 0;
	}
	
	# 是否需要静态
	# 分页url与当前页面动静态是一致的(不需要被动静态)
	protected function _CheckAllowStatic(){
		$this->_Cfg['AllowStatic'] = true;								# 目前所有副件内容都可以生成静态，不受总开关的限制
		$this->_Cfg['MpUrlStatic']	= $this->_inStatic;					# 分页Url静态原则：动归动，静归静
		$this->_CheckStatic();
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		if($this->_inStatic) return;
		$this->_NormalVars = array('aid','page',);
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		$this->_CheckAllowStatic(); # 是否需要生成静态，注意：即使动态页面，也需要分析
		cls_url::arr_tag2atm($this->_Arc->archive,'f');
		$this->_MainData = $this->_Arc->archive;
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		$this->_ParseSource = cls_tpl::CommonTplname('farchive',$this->_Arc->archive['fcaid'],'arctpl');
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
		if($this->_inStatic){ # 静态时输出动态Url
			$ParamStr = "&aid={$this->_SystemParams['aid']}";
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
			$ParamStr = "&aid={$this->_SystemParams['aid']}";
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
	
	# 生成静态后更新相关信息(时间，Url等)到主体记录
	protected function _UpdateStaticRecord(){
		if(!$this->_inStatic) return;
		$StaticFilePre = $this->_StaticFilePre();
		$StaticFile = cls_url::m_parseurl($StaticFilePre,array('page' => 1));
		self::$db->query("UPDATE ".self::$tblprefix."farchives SET arcurl='$StaticFile' WHERE aid='".$this->_SystemParams['aid']."'");
	}
	
	# 取得分页_静态文件保存格式
	protected function _StaticFilePre(){
		if(!isset($this->_Cfg['_StaticFilePre'])){ # 需要重复使用
			$this->_Cfg['_StaticFilePre'] = $this->_Arc->arcformat();
		}
		return $this->_Cfg['_StaticFilePre'];
	}
	
	# 页面名称
	protected function _PageName(){
		return "副件内容页[{$this->_SystemParams['aid']}]";
	}
	
}
