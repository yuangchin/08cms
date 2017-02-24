<?php
/**
 * 生成会员空间静态页的处理基类
 *
 */
defined('M_COM') || exit('No Permission');
abstract class cls_MspaceIndexBase extends cls_FrontPage{
	
	protected static $_ExtendAplicationClass = 'cls_MspaceIndex'; 			# 当前基类的扩展应用类(即子类)的类名
	
	# 页面生成的外部执行入口
	# 传参详情请参看 function _Init
	public static function Create($Params = array()){
		return self::_iCreate(self::$_ExtendAplicationClass,$Params);
	}

	protected function __construct(){
		parent::__construct();
 		$this->_PageCacheParams['typeid'] = 7; 								# 页面缓存类型
		$this->_Cfg['maxStaicPage'] = 1; 									# 注意：会员空间只生成第一个页码的静态
		$this->_Cfg['mspacedisabled'] = cls_env::mconfig('mspacedisabled');	# 会员空间关闭
		$this->_Cfg['LoadAdv'] = true;										# 是否需要生成广告js调用代码
	}
	
	# 空间静态的生成结果返回格式与其它页面不同
	protected function _StaticAllPage(){
		$_start_time = microtime(TRUE);
		
		$PageByteSize = 0;
		$maxStaticPage = 1;
		for($this->_SystemParams['page'] = 1;$this->_SystemParams['page'] <= $maxStaticPage;$this->_SystemParams['page'] ++){
			try{
				$re = $this->_CreateOnePage();
			}catch(cls_PageException $e){
				return array('error' => $e->getMessage());
			}
			if($error = $this->_SaveStaticFile($re['content'])){
				return array('error' => $error);
			}
			$PageByteSize += strlen($re['content']);
			$maxStaticPage = $this->_MaxStaticPageNo(@$re['pcount']);
		}
		cls_env::SetG('_no_dbhalt',false); # 静态时关闭SQL错误中断 ????
		
		# 正确生成完成后的返回信息
		$Result = array(
			'num' => $this->_Cfg['maxStaicPage'],
			'time' => round(microtime(TRUE) - $_start_time,2),
			'size' => $PageByteSize,
		);
		return $Result;
	}
	
	# 应用实例的基本初始化，必须定义，每个应用定制自已的处理方法
	protected function _Init($Params = array()){
		
		# 是否静态
		$this->_inStatic = empty($Params['inStatic']) ? false : true; # 是否静态

		# 附加页参数		
		$this->_SystemParams['addno'] = $this->_inStatic || isset($Params['addno']) ? @$Params['addno'] : @$this->_QueryParams['addno']; # 附加页参数
		
		# 页面特征参数
		foreach(array('mid','mcaid','ucid',) as $k){
			$this->_SystemParams[$k] = $this->_inStatic || isset($Params[$k]) ? @$Params[$k] : @$this->_QueryParams[$k];
			$this->_SystemParams[$k] = max(0,intval($this->_SystemParams[$k]));
		}
	}
	
	# 不同类型页面的数据模型
	protected function _ModelCumstom(){
		$this->_MspaceClosed(); # 会员空间是否关闭
	}
	
	# 会员空间是否关闭
	protected function _MspaceClosed(){
		if($this->_inStatic) return;
		if(!empty($this->_Cfg['mspacedisabled'])){
				throw new cls_PageException('会员空间暂停访问');
		}	
	}
	
	# 附加页编号处理
	protected function _Addno(){
		$this->_SystemParams['addno'] = max(0,intval(@$this->_SystemParams['addno'])); # 已通过传参初始化
		if($this->_SystemParams['addno'] > 1){
			throw new cls_PageException($this->_PageName()." - 不允许的附加页");
		}
	}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	protected function _NormalVars(){
		if($this->_inStatic) return;
		$this->_NormalVars = array('addno','page','mid','mcaid','ucid',);
	}
	
	# 读取页面主体资料
	protected function _MainData(){
		
		# 初始化主体资料
		if(!$this->_SystemParams['mid']){
			throw new cls_PageException('请指定会员ID');
		}
		$this->_MainData = cls_Mspace::LoadMember($this->_SystemParams['mid'],60,0);
		if(!$this->_MainData){
			throw new cls_PageException('未找到指定的会员');
		}elseif(empty($this->_MainData['checked'])){
			throw new cls_PageException('会员未审核');
		
		}
		
		# 是否需要生成静态，注意：即使动态页面，也需要分析
		$this->_CheckAllowStatic();
		
		# 未审认证的字段清空不显示
		cls_UserMain::HiddenUncheckCertField($this->_MainData);
		
		# 追加模板原始标识资料
		$this->_MainData += cls_Mspace::IndexAddParseInfo($this->_MainData,$this->_AddParams());
	}
	
	# 是否需要生成静态
	# 需要静态时，即使在动态页面，分页url也要按静态来处理(配合被动静态)
	protected function _CheckAllowStatic(){
		
		if(empty($this->_MainData)){
			throw new cls_PageException('请先初始化会员资料');
		}
		
		$this->_Cfg['AllowStatic'] = cls_Mspace::AllowStatic($this->_MainData) ? false : true;
		
		# 分页Url静态条件：1)允许静态，2)正在或已经生成静态
		if($this->_Cfg['AllowStatic']){	
			if($this->_inStatic || !empty($this->_MainData['msrefreshdate'])){
				$this->_Cfg['MpUrlStatic'] = true;
			}
		}
		$this->_CheckStatic();
	}
	
	# 获得页面模板
	protected function _ParseSource(){
		$this->_ParseSource = cls_Mspace::IndexTplname($this->_MainData['mtcid'],$this->_SystemParams);
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	# 当前页面调试信息
	protected function _Mdebug(){
		$_mdebug = cls_env::GetG('_mdebug');
		$_mdebug->setvar('tpl',$this->_ParseSource);
		if($this->_inStatic){ # 静态时输出动态Url
			$ParamStr = '';
			foreach(array('mid','mcaid','ucid','addno',) as $k){
				if(!empty($this->_SystemParams[$k])){
					$ParamStr .= "&$k=".$this->_SystemParams[$k];
				}
			}
			$ParamStr = substr($ParamStr ,1);
			
			$mspacedir = cls_env::GetG('mspacedir');
			$_mdebug->setvar('uri',"{$mspacedir}/index.php?$ParamStr");
			cls_env::SetG('_no_dbhalt',true); # 静态时关闭SQL中断错误 ????
		}
	}
	
	# 取得分页Url套用格式
	protected function _UrlPre($isStatic = false){
		if($isStatic){ # 静态Url套用格式
			if(!empty($this->_SystemParams['filterstr'])) return '';
			 $re = $this->_StaticFilePre();# 需要允许其静态格式为''
			 return cls_url::view_url($re);
		}else{ # 动态Url套用格式
			$re = MspaceIndexFormat($this->_MainData,$this->_AddParams(),true);
		}
		if(!$re) throw new cls_PageException($this->_PageName().' - '.($isStatic ? '静态' : '动态').'URL格式错误');
		$re = cls_url::view_url($re);
		return $re;
	}
	
	# 生成ToolJs的参数数组，只有page=1时传送
	protected function _ToolParams(){
		if($this->_SystemParams['page'] == 1){
			$_ToolParams = array('mid' => $this->_SystemParams['mid'],);
			foreach(array('mcaid','ucid','addno',) as $k){
				if(!empty($this->_SystemParams[$k])) $_ToolParams[$k] = $this->_SystemParams[$k];
			}
			if(!$this->_inStatic) $_ToolParams['upsen'] = 1;
			# 只有空间首页请求静态更新
			if(!array_intersect(array_keys($_ToolParams),array('mcaid','ucid',))){
				$_ToolParams['msp_static'] = 1;
			}
		}
		return @$_ToolParams;
	}
	
	# 取得分页_静态文件保存格式
	protected function _StaticFilePre(){
		if(!isset($this->_Cfg['_StaticFilePre'])){ # 需要重复使用
  			$Params = array();
			foreach($this->_SystemParams as $k => $v){
				if(in_array($k,array('addno','mid','mcaid','ucid',))){
					if(!empty($v)) $Params[$k] = $v;
				}
			}
			$this->_Cfg['_StaticFilePre'] = MspaceIndexFormat($this->_MainData,$Params,false);
		}
		return $this->_Cfg['_StaticFilePre'];
	}
	
	# 格式化传参资料数组
	protected function _AddParams(){
		$re = $this->_SystemParams;
		if(!$this->_inStatic) $re += $this->_QueryParams;
		unset($re['page']); //去掉&page=999,后续加上&page={\$page}参数;否则分页错误。
		return $re;
	}
	
	# 页面名称
	protected function _PageName(){
		return "会员空间[{$this->_SystemParams['mid']}]";
	}
	
}
