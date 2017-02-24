<?php
/**
 * 文档内容页的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_ArchivePageBase extends cls_FrontPage{
	
  	protected static $_ExtendAplicationClass = 'cls_ArchivePage'; 	# 当前基类的扩展应用类(即子类)的类名
	protected $_Arc = NULL; 										# 当前文档的内容实例
 	protected $_KeepStaticFormat = 0; 								# 保持原静态格式
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
 		$this->_PageCacheParams['typeid'] = 2; 							# 页面缓存类型
		$this->_Cfg['AllowStatic'] = cls_env::mconfig('enablestatic');	# 静态总开关
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化
	protected function _Init($Params = array()){
		
		$this->_inStatic = empty($Params['inStatic']) ? false : true; # 是否静态	
			
		if(!empty($Params['arc'])){
			$this->_Arc = $Params['arc']; # 注意：实例的引用
			$this->_SystemParams['aid'] = $this->_Arc->aid;
			$this->_inMobile = (bool)@$this->_Arc->archive['nodemode'];
		}else{
			$this->_SystemParams['aid'] = isset($Params['aid']) ? $Params['aid'] : @$this->_QueryParams['aid'];
			$this->_inMobile = defined('IN_MOBILE'); # 手机版暂不支持静态
		}
		if($this->_inStatic && $this->_inMobile){
			throw new cls_PageException($this->_PageName()." - 手机版暂不支持静态");
		}
		
		$this->_SystemParams['addno'] = isset($Params['addno']) ? $Params['addno'] : @$this->_QueryParams['addno']; # 附加页参数
		$this->_KeepStaticFormat = empty($Params['kp']) ? 0 : 1;
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_ReadArchive(); # 读取文档资料
		$this->_PageCacheConfig(); # 动态页缓存的配置参数
	}
	
	# 读取文档资料
	protected function _ReadArchive(){
		$this->_SystemParams['aid'] = max(0,intval($this->_SystemParams['aid']));
		
		if(!$this->_SystemParams['aid']){
			throw new cls_PageException($this->_PageName()." - 未指定文档ID");
		}
		
		if(empty($this->_Arc)){
			$this->_Arc = new cls_arcedit();
			if(!$this->_Arc->set_aid($this->_SystemParams['aid'],array('au' => 0,'ch' => 1,'nodemode' => $this->_inMobile))){
				throw new cls_PageException($this->_PageName()." - 文档不存在");
			}
		}elseif(!$this->_Arc->detailed){
			$this->_Arc->detail_data(0);
		}
		
		# 设置了文档跳转
		if(!empty($this->_Arc->archive['jumpurl'])){
			throw new cls_PageException($this->_PageName()." - 文档跳转");
		}
		
		# 检查是否审核
		$this->_ArcChecked();
	}
	
	# 检查是否审核
	protected function _ArcChecked(){
		if(!$this->_Arc->archive['checked']){
			if($this->_inStatic || !self::$curuser->isadmin()){
				throw new cls_PageException($this->_PageName()." - 文档未通过审核");
			}
		}
	}
	
	# 动态页缓存的配置参数
	protected function _PageCacheConfig(){
		if($this->_inStatic) return;
		$this->_PageCacheParams['chid'] = $this->_Arc->archive['chid'];
		$this->_PageCacheParams['initdate'] = $this->_Arc->archive['initdate'];
	}
	
	# 附加页编号处理
	protected function _Addno(){
		$this->_SystemParams['addno'] = max(0,intval($this->_SystemParams['addno']));
		$Addnum = @$this->_Arc->arc_tpl['addnum'];
		if($this->_SystemParams['addno'] > $Addnum){
			throw new cls_PageException($this->_PageName()." - 不允许的附加页");
		}
	}
	
	# 是否需要静态，与$_inStatic(是否处理静态页生成过程中)是不同的含义
	# 需要静态时，即使在动态页面，分页url也要按静态来处理(配合被动静态)
	protected function _CheckAllowStatic(){
		if($this->_Cfg['AllowStatic']){
			if($this->_inMobile){
				$this->_Cfg['AllowStatic'] = 0;
			}elseif(!empty($this->_Arc->arc_tpl['cfg'][$this->_SystemParams['addno']]['static'])){ # 方案中设置关闭静态
				$this->_Cfg['AllowStatic'] = 0;
			}
		}
		$this->_Cfg['MpUrlStatic']	= $this->_Cfg['AllowStatic'];	# 分页Url静态原则：动中有静，动态页面中根据是否允许静态来体现其它分页Url
		$this->_CheckStatic();
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		if($this->_inStatic) return;
		$this->_NormalVars = array('aid','addno','page',);
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		$this->_CheckAllowStatic(); # 是否需要生成静态，注意：即使动态页面，也需要分析
		$this->_MainData = $this->_Arc->archive;
		cls_ArcMain::Parse($this->_MainData);
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		$this->_ParseSource = $this->_Arc->tplname($this->_SystemParams['addno']);	
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
			$ParamStr = $this->_SystemParams['addno'] ? "&addno={$this->_SystemParams['addno']}" : '';
			$ParamStr = substr($ParamStr ,1);
			$_mdebug->setvar('uri',"archive.php?$ParamStr");
			cls_env::SetG('_no_dbhalt',true); # 静态时关闭SQL中断错误 ????
		}
	}
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		$re = $this->_Arc->urlpre($this->_SystemParams['addno'],$this->_SystemParams['filterstr'],$isStatic,$this->_KeepStaticFormat);
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		return $re;
	}
	
	# 生成ToolJs的参数数组，只有page=1时传送
	protected function _ToolParams(){
		if($this->_SystemParams['page'] == 1){
			$_ToolParams = array();
			$_ToolParams = array('mode' => 'arc','static' => 1,'aid' => $this->_SystemParams['aid'],'chid' => $this->_Arc->archive['chid'],'mid' => $this->_Arc->archive['mid'],);
			if(!$this->_inStatic) $_ToolParams['upsen'] = 1;
			if($this->_SystemParams['addno']) $_ToolParams['addno'] = $this->_SystemParams['addno'];
		}
		return @$_ToolParams;
	}
	
	
	# 生成静态后更新相关信息(时间，Url等)到主体记录
	protected function _UpdateStaticRecord(){
		if(!$this->_inStatic) return;
		$ns = explode(',',$this->_Arc->archive['needstatics']);
		$nns = '';
		for($i = 0;$i <= $this->_Arc->arc_tpl['addnum'];$i++){
			$nns .= ($i == $this->_SystemParams['addno'] ? self::$timestamp : @$ns[$i]).',';
		}
		self::$db->query("UPDATE ".self::$tblprefix.$this->_Arc->tbl." SET needstatics='$nns' WHERE aid='{$this->_SystemParams['aid']}'");
	}
	
	# 取得分页_静态文件保存格式
	protected function _StaticFilePre(){
		if(!isset($this->_Cfg['_StaticFilePre'])){ # 需要重复使用
			$this->_Cfg['_StaticFilePre'] = $this->_Arc->filepre($this->_SystemParams['addno'],$this->_KeepStaticFormat);
		}
		return $this->_Cfg['_StaticFilePre'];
	}
	
	# 页面名称
	protected function _PageName(){
		return "文档内容页[{$this->_SystemParams['aid']}]";
	}
	
}
