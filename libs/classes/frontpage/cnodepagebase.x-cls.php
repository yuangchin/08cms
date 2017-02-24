<?php
/**
 * 生成类目节点页面的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_CnodePageBase extends cls_FrontPage{
	
 	protected $_Node = array(); 									# 当前节点的配置数组
 	protected static $_ExtendAplicationClass = 'cls_CnodePage'; 	# 当前基类的扩展应用类(即子类)的类名

	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
 		$this->_PageCacheParams['typeid'] = 1; 										# 页面缓存类型
		$this->_Cfg['AllowStatic'] = cls_env::mconfig('enablestatic');				# 静态总开关
		$this->_Cfg['maxStaicPage'] = max(0,intval(cls_env::mconfig('liststaticnum'))); # 注意：具体内容页全部生成静态，不使用此设置
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	# 是否静态
	# 是否手机版
	# 是否返回结果
	# 附加页参数		
	# 页面特征参数(如aid,cnstr,tname等页面特征)
	protected function _Init($Params = array()){
		
		$this->_inStatic = empty($Params['inStatic']) ? false : true; # 是否静态
		$this->_inMobile = $this->_inStatic ? false : defined('IN_MOBILE'); # 是否手机版
		$this->_SystemParams['addno'] = $this->_inStatic || isset($Params['addno']) ? @$Params['addno'] : @$this->_QueryParams['addno']; # 附加页参数
		
		# 页面特征参数
		$this->_SystemParams['cnstr'] = $this->_inStatic || isset($Params['cnstr']) ? @$Params['cnstr'] : cls_cnode::cnstr($this->_QueryParams); # 得到节点字串
		if(!$this->_SystemParams['cnstr']) $this->_SystemParams['cnstr'] = '';
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_Cnstr(); # 分析节点字串
		$this->_Node(); # 读取节点
	}
	
	# 分析节点字串(预留扩展接口)
	protected function _Cnstr(){}
	
	# 读取节点
	protected function _Node(){
		if($this->_SystemParams['cnstr']){
			if(!($this->_Node = cls_node::cnodearr($this->_SystemParams['cnstr'],$this->_inMobile))){
				throw new cls_PageException($this->_PageName()." - 不存在或未配置");
			}
		}
	}
	
	# 附加页编号处理
	protected function _Addno(){
		$this->_SystemParams['addno'] = max(0,intval(@$this->_SystemParams['addno'])); # 已通过传参初始化
		$Addnum = $this->_SystemParams['cnstr'] ? @$this->_Node['addnum'] : 0;
		if($this->_SystemParams['addno'] > $Addnum){
			throw new cls_PageException($this->_PageName()." - 不允许的附加页");
		}
	}
	
	# 是否需要生成静态
	# 需要静态时，即使在动态页面，分页url也要按静态来处理(配合被动静态)
	protected function _CheckAllowStatic(){
		if($this->_Cfg['AllowStatic']){
			if($this->_inMobile){
				$this->_Cfg['AllowStatic'] = 0;
			}elseif(! empty($this->_Node['cfgs'][$this->_SystemParams['addno']]['static'])){
				$this->_Cfg['AllowStatic'] = 0;
			}
		}
		$this->_Cfg['MpUrlStatic']	= $this->_Cfg['AllowStatic'];	# 分页Url静态原则：动中有静，动态页面中根据是否允许静态来体现其它分页Url
		$this->_CheckStatic();
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		if($this->_inStatic) return;
		$this->_NormalVars = array('addno','page',);
		parse_str($this->_SystemParams['cnstr'],$idsarr);
		$this->_NormalVars = array_merge($this->_NormalVars,array_keys($idsarr));
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		$this->_CheckAllowStatic(); 			# 是否需要生成静态，注意：即使动态页面，也需要分析
		if($this->_SystemParams['cnstr']){
			$this->_MainData = cls_node::cn_parse($this->_SystemParams['cnstr']);
			cls_node::re_cnode($this->_MainData,$this->_SystemParams['cnstr'],$this->_Node);
		}else{
			$this->_MainData['rss'] = cls_url::view_url('rss.php',false);
		}
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		if($this->_SystemParams['cnstr']){
			$this->_ParseSource = cls_tpl::cn_tplname($this->_SystemParams['cnstr'],$this->_Node,$this->_SystemParams['addno']);
		}else{
			$this->_ParseSource = cls_tpl::SpecialTplname('index',$this->_inMobile);
		}
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
		if($this->_inStatic){ # 静态时输出动态Url
			$ParamStr = $this->_SystemParams['cnstr'] ? "&{$this->_SystemParams['cnstr']}" : '';
			$ParamStr .= $this->_SystemParams['addno'] ? "&addno={$this->_SystemParams['addno']}" : '';
			$ParamStr = substr($ParamStr ,1);
			$_mdebug->setvar('uri',"index.php?$ParamStr");
			cls_env::SetG('_no_dbhalt',true); # 静态时关闭SQL中断错误 ????
		}
	}
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		if($isStatic && empty($this->_SystemParams['filterstr'])){ # 静态Url套用格式
			if($this->_inMobile || !empty($this->_SystemParams['filterstr'])) return '';
			$re = $this->_StaticFilePre();
		}else{ # 动态Url套用格式
			$ParamStr = $this->_SystemParams['cnstr'] ? "&{$this->_SystemParams['cnstr']}" : '';
			$ParamStr .= $this->_SystemParams['addno'] ? "&addno={$this->_SystemParams['addno']}" : '';
			$ParamStr .= $this->_SystemParams['filterstr'];
			$ParamStr .= "&page={\$page}";
			$ParamStr = substr($ParamStr ,1);
			$re = ($this->_inMobile ? cls_env::mconfig('mobiledir').'/' : '')."index.php?".$ParamStr;
			$re = cls_url::en_virtual($re,$this->_inMobile);
		}
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		$re = cls_url::view_url($re);
		return $re;
	}
	
	# 生成ToolJs的参数数组，只有page=1时传送
	protected function _ToolParams(){
		if(!$this->_inMobile && $this->_SystemParams['page'] == 1){
			$_ToolParams = array('mode' => 'cnindex','static' => 1,);
			if(!$this->_inStatic) $_ToolParams['upsen'] = 1;
			if($this->_SystemParams['cnstr']) $_ToolParams[0] = $this->_SystemParams['cnstr'];
			if($this->_SystemParams['addno']) $_ToolParams['addno'] = $this->_SystemParams['addno'];
		}
		return @$_ToolParams;
	}
	
	# 生成静态后更新相关信息(时间，Url等)到主体记录
	protected function _UpdateStaticRecord(){
		if(!$this->_inStatic) return;
		if($this->_SystemParams['cnstr']){
			$ns = self::$db->result_one("SELECT needstatics FROM ".self::$tblprefix."cnodes WHERE ename='".$this->_SystemParams['cnstr']."'");
			$ns = explode(',',$ns);
			$nns = '';
			for($i = 0;$i <= @$this->_Node['addnum'];$i++){
				$nns .= ($i == $this->_SystemParams['addno'] ? self::$timestamp : @$ns[$i]).',';
			}
			self::$db->query("UPDATE ".self::$tblprefix."cnodes SET needstatics='$nns' WHERE ename='".$this->_SystemParams['cnstr']."'");
		}else{
			self::$db->query("UPDATE ".self::$tblprefix."mconfigs SET value='".self::$timestamp."' WHERE varname='ineedstatic'");
		}
	}
	
	# 取得分页_静态文件保存格式
	protected function _StaticFilePre(){
		if(!isset($this->_Cfg['_StaticFilePre'])){ # 需要重复使用
			$this->_Cfg['_StaticFilePre'] = $this->_CnFormat();
		}
		return $this->_Cfg['_StaticFilePre'];
	}
	
	# 类目页的静态Url及文件格式
	protected function _CnFormat(){
		if($this->_SystemParams['cnstr']){
			$re = cls_node::cn_format($this->_SystemParams['cnstr'],$this->_SystemParams['addno'],$this->_Node);
		}else{
			$re = idx_format($this->_inMobile);
		}
		return $re;
	}
	
	# 页面名称
	protected function _PageName(){
		return $this->_SystemParams['cnstr'] ? "节点[{$this->_SystemParams['cnstr']}]" : "系统首页";
	}
	
}
