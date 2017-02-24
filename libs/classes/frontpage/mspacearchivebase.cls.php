<?php
/**
 * 生成会员空间文档内容页的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_MspaceArchiveBase extends cls_FrontPage{
	
	protected static $_ExtendAplicationClass = 'cls_MspaceArchive'; 		# 当前基类的扩展应用类(即子类)的类名
	protected $_Arc = NULL; 												# 当前文档的基本实例
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
 		$this->_PageCacheParams['typeid'] = 8; 								# 页面缓存类型
		$this->_Cfg['mspacedisabled'] = cls_env::mconfig('mspacedisabled');	# 会员空间关闭
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	protected function _Init($Params = array()){
		
		# 附加页参数		
		$this->_SystemParams['addno'] = isset($Params['addno']) ? $Params['addno'] : @$this->_QueryParams['addno']; # 附加页参数
		
		# 页面特征参数
		foreach(array('mid','aid',) as $k){
			$this->_SystemParams[$k] = isset($Params[$k]) ? $Params[$k] : @$this->_QueryParams[$k];
			$this->_SystemParams[$k] = max(0,intval($this->_SystemParams[$k]));
		}
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadArchive();# 读取当前文档的资料
		$this->_MspaceClosed(); # 会员空间是否关闭
	}
	
	# 读取当前文档的资料
	protected function _ReadArchive(){
		
		# 初始化主体资料
		if(!$this->_SystemParams['aid']){
			throw new cls_PageException('请指定文档ID');
		}
		$this->_Arc = new cls_arcedit();
		if(!$this->_Arc->set_aid($this->_SystemParams['aid'],array('au'=>0,'ch'=>1))){
			throw new cls_PageException('请指定正确的文档');
		}
		if(!$this->_Arc->archive['checked'] && !self::$curuser->isadmin()){
			throw new cls_PageException('指定的文档尚未审核');
		}
	}
	
	# 会员空间是否关闭
	protected function _MspaceClosed(){
		if(!empty($this->_Cfg['mspacedisabled'])){
			throw new cls_PageException('会员空间暂停访问');
		}	
	}
	
	# 附加页编号处理
	protected function _Addno(){
		$this->_SystemParams['addno'] = max(0,intval(@$this->_SystemParams['addno'])); # 已通过传参初始化
		if($this->_SystemParams['addno'] > 2){
			throw new cls_PageException($this->_PageName()." - 不允许的附加页");
		}
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		$this->_NormalVars = array('addno','page','mid','aid',);
	}
	
	# 动态页缓存的配置参数
	protected function _PageCacheConfig(){
		$this->_PageCacheParams['chid'] = $this->_Arc->archive['chid'];
		$this->_PageCacheParams['initdate'] = $this->_Arc->archive['initdate'];
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		
		# 是否需要生成静态，注意：即使动态页面，也需要分析
		$this->_CheckAllowStatic();
		
		# 初始化主体资料
		if(!$this->_SystemParams['mid']){
			throw new cls_PageException('请指定会员ID');
		}
		$this->_MainData = cls_Mspace::LoadMember($this->_SystemParams['mid']);
		if(!$this->_MainData){
			throw new cls_PageException('未找到指定的会员');
		}
		
		# 未审认证的字段清空不显示
		cls_UserMain::HiddenUncheckCertField($this->_MainData);
		
		# 兼容之前的用法，暂时保留，在以后的模板中，读取文档资料，使用文档标签
		$this->_MainData['_arc'] = $this->_Arc->archive;
		cls_ArcMain::Parse($this->_Arc->archive);
		$this->_MainData  += $this->_Arc->archive;
		
	}
	
	# 是否需要生成静态
	# 需要静态时，即使在动态页面，分页url也要按静态来处理(配合被动静态)
	protected function _CheckAllowStatic(){
		$this->_Cfg['AllowStatic'] = false;
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		$this->_ParseSource = cls_Mspace::ArchiveTplname($this->_MainData['mtcid'],$this->_Arc->archive['chid'],$this->_SystemParams['addno']);
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
	}
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		if($isStatic){ # 静态Url套用格式
			return '';
		}else{ # 动态Url套用格式
			$ParamStr = "&mid={$this->_SystemParams['mid']}";
			$ParamStr .= "&aid={$this->_SystemParams['aid']}";
			$ParamStr .= $this->_SystemParams['filterstr'];
			$ParamStr .= "&page={\$page}";
			$ParamStr = substr($ParamStr ,1);
			
			$re = cls_env::mconfig('mspaceurl')."archive.php?".$ParamStr;
			$re = cls_url::en_virtual($re);
		}
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		$re = cls_url::view_url($re);
		return $re;
	}
	
	# 生成ToolJs的参数数组，只有page=1时传送
	protected function _ToolParams(){
		if($this->_SystemParams['page'] == 1){
			$_ToolParams = array('mid' => $this->_SystemParams['mid'],'aid' => $this->_SystemParams['aid'],);
		}
		return @$_ToolParams;
	}
	
	# 页面名称
	protected function _PageName(){
		return "会员空间[{$this->_SystemParams['mid']}]";
	}
	
}
