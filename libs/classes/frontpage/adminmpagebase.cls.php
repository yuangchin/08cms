<?php
/**
 * 会员中心脚本解析模板标签的处理基类
 * 不处理静态、分页、附加页
 */
 defined('M_COM') || exit('No Permission');
abstract class cls_AdminmPageBase extends cls_FrontPage{
	
 	protected static $_ExtendAplicationClass = 'cls_AdminmPage'; 	# 当前基类的扩展应用类(即子类)的类名
 	protected static $_UserChecked = false; 						# 是否已执行了会员检查(只需要在entry.php中检查会员即可)

	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
		$this->_SourceType = 'adminm'; # 来源类型
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	# 是否为入口模板
	# 是否内部区块($action.inc.php)
	# 是否返回结果
	# 页面特征参数(如aid,cnstr,tname等页面特征)
	protected function _Init($Params = array()){
		
		$this->_Cfg['isEntry'] = empty($Params['isEntry']) ? false : true; # 是否为入口模板
		$this->_Cfg['DynamicReturn'] = empty($Params['DynamicReturn']) ? false : true; # 动态结果：返回(true)/输出(false)
		$this->_Cfg['SonBlockOfPage'] = empty($this->_Cfg['isEntry']);
		# 页面特征参数
		$this->_SystemParams['action'] = isset($this->_QueryParams['action']) ? $this->_QueryParams['action'] : ''; # 得到action
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_Action(); # 分析action
		$this->_UserCheck(); # 初始化会员中心
	}
	
	# 数据模型的通用部分
	protected function _ModelCommon(){
		$this->_ParseSource(); # 获得页面模板
		$this->_Mdebug(); # 当前页面调试信息
	}
	
	# 分析action
	protected function _Action(){
		_08_FilesystemFile::filterFileParam($this->_SystemParams['action']);
		$this->_SystemParams['action'] = empty($this->_SystemParams['action']) ? 'wjindex' : $this->_SystemParams['action'];
	}
	
	# 初始化会员中心
	protected function _UserCheck(){
		
		if(empty($this->_Cfg['isEntry'])){
			if(empty(self::$_UserChecked)){
				throw new cls_PageException('未检查会员资料');
			}
		}else{
			self::$curuser->detail_data();	# 统一读取完整会员资料
			self::$curuser->mcTrustee();	# 会员中心代管
			mc_allow();						# 验证进入会员中心的权限
			self::$_UserChecked = true;
			
		}
		
	}
	# 获得页面模板
	protected function _ParseSource(){
		if(empty($this->_Cfg['isEntry'])){
			$this->_ParseSource = $this->_SystemParams['action'].'.inc.php';
		}else{
			$this->_ParseSource = 'entry.php';
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
	}
	
	
	# 页面名称
	protected function _PageName(){
		return $this->_SystemParams['cnstr'] ? "节点[{$this->_SystemParams['cnstr']}]" : "系统首页";
	}
	
}
