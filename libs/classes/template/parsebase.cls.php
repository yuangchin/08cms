<?php
/**
* 对模板代码区块进行解析，在模板解析或设计体系中，是对外的接口(cls_Parse::xxx等)，支持嵌套解析模板区块
* 为了兼容目前在模板或模板函数中频繁使用global更改参数的状况，目前还需要将$_da及其内部变量作为global参数来使用，有待模板进行正规化处理
* 变量尽量采用静态，方便子类(cls_TagParse_xxx、cls_TagParse)继承使用
* 方法尽量不采用静态，可以通过创建实例，方便子类(cls_TagParse_xxx、cls_TagParse)进行扩展
* 继承cls_FronPage是为了共用其部分静态变量，如$_mp等。
*/

defined('M_COM') || exit('No Permission');
abstract class cls_ParseBase extends cls_BasePage{
	
	protected static $_Instances = array();				# 一个完整页面的多个模板区块的解析实例的堆栈，特别是处理内嵌模板区块的解析，需要暂存上层模板解析实例
	protected static $_ActiveVarArray = array();		# 需要激活的所有变量的名称
	
	protected $SourceType = 'tplname';					# 来源类型：(1)tplname(页面模板名称) (2)js(js标签) (3)adv(广告) (4)fragment(碎片) (4)adminm(会员中心脚本)
	protected $ParseSource = '';						# 来源页面模板名称/模板标签(如标签js调用)名称/会员中心脚本名称
	protected $_da = array();							# 页面主体资料数组(目前使用global,暂时不定义)
	protected $_a = array();							# 当前激活变量数组
	protected $_ActiveParamStack = array();				# 激活变量数组的暂存堆栈
	
	
	# 根据配置，得到一个来源(如指定页面)的输出结果代码
	public static function OneSourceCode($ParseInitConfig = array()){
		try{
			$re = self::_ParseInstance($ParseInitConfig)->_iOneSourceCode();
			self::_DestroyNowInstance();
		}catch(cls_ParseException $e){
			self::_DestroyNowInstance();
			throw new cls_ParseException($e->getMessage());
		}
		return $re;
	}
	
	# 读取固定变量($G、$_da、$_mp)，对外接口
	public static function Get($Key = 'G'){
		return self::_ParseInstance()->_Get($Key);
	}
	
	# 更新固定变量($G、$_da、$_mp)，对外接口
	public static function Set($Key = 'G',$Value){
		return self::_ParseInstance()->_Set($Key,$Value);
	}
	
	# 模板中使用的意外信息提示方法，模板中不能直接用类似die/exit/message等方法退出(会导致批量静态时中断)，请用此方法
	public static function Message($message = ''){
		throw new cls_ParseException($message ? $message : '未知原因页面中止');
	}

	# 处理当前激活参数数组$_a，并压入激活参数堆栈中暂存。为了兼容，暂时保留对外接口
	public static function Active($SourceArray = array(),$isInit = 0){
		return self::_ParseInstance()->_Active($SourceArray,$isInit);
	}
	
	# 在激活参数的堆栈中回退一层，并更新当前的激活参数数组$_a。为了兼容，暂时保留对外接口
	public static function ActiveBack(){
		return self::_ParseInstance()->_ActiveBack();
	}
	
	# 根据复合标签设置提取复合标签的数据，唯一入口。为了兼容，暂时保留对外接口
	public static function Tag($tag = array()){			
		return self::_ParseInstance()->_Tag($tag);
	}
	
	# 模板所设置的权限判断
	public static function Pm($pmid=0){
		return self::$curuser->pmbypmid($pmid);
	}
	
	# 格式化模板缓存的完全文件路径
	# 当文件名为空时，返回缓存目录名称，用于清除缓存时取得目录
	# $SmallPathType：0为常规模板缓存目录(common)，1为会员中心缓存(adminm)，2为js标签缓存(jstag)
	public static function TplCacheDirFile($file = '',$SmallPathType = 0){
		_08_FilesystemFile::filterFileParam($file);
		$SmallPathTypeArray = array(0 => 'common',1 => 'adminm',2 => 'jstag',);
		return _08_TPL_CACHE.(empty($SmallPathTypeArray[$SmallPathType]) ? 'common' : $SmallPathTypeArray[$SmallPathType]).DIRECTORY_SEPARATOR.($file ? $file : '');
	}

	# 根据来源取得PHP文件缓存的文件完全路径
	public static function PHPCacheFileName($ParseSource,$SourceType = 'tplname'){
		if(!$ParseSource) return false;
		
		# 预检查
		$CacheID = $ParseSource;
		if(in_array($SourceType,array('fragment','adv',))){ # 模板标签数组 碎片自定义模版
			$CacheID = @$ParseSource['tclass'] ? (string)@$ParseSource['ename'] : (string)@$ParseSource['template'];
		}elseif($SourceType == 'js'){
			$CacheID = 'js_'.$ParseSource['ename'].'_'.substr(md5(var_export($ParseSource,TRUE)),0,10);
		}
		_08_FilesystemFile::filterFileParam($CacheID);
		if(!$CacheID) return false;
		
		# 缓存文件完整路径
		$CacheFileName = $CacheID.($SourceType == 'adminm' ? '' : '.php');
		$CacheFileName = cls_Parse::TplCacheDirFile($CacheFileName,$SourceType == 'adminm');
		return $CacheFileName;
	}
	
	# 初始化一个来源的解析
	protected function __construct($ParseInitConfig = array()){
		
		# 页面原始资料$_da，暂时维持global，以兼容目前的模板
		$this->_Set('_da',isset($ParseInitConfig['_da']) ? $ParseInitConfig['_da'] : array());
		$this->_Active($this->_Get('_da'),true); # 初始化激活变量
		
		# 当前页面的来源类型
		$this->SourceType = isset($ParseInitConfig['SourceType']) ? $ParseInitConfig['SourceType'] : 'tplname';
		
		# 当前页面的来源模板名称或模板标签
		$this->ParseSource = isset($ParseInitConfig['ParseSource']) ? $ParseInitConfig['ParseSource'] : '';
		if(!$this->ParseSource){
			throw new cls_ParseException('页面模板未定义');
		}
		
	}
	
	# 将模板解释为模板PHP缓存，对PHP缓存执行解析数据，返回结果
	protected function _iOneSourceCode(){
		$PHPCacheFileName = $this->_PHPCacheFileName();
				
		# ---------------------------------------------------------------------------		
		# 为兼容目前模板，暂时维持global散变量模式
		# 注意保持 "$_da->$mconfig->$btags" 的顺序
		# 注意确保$_da之前处理过外传变量覆盖的问题($_da包含了'外传参数+内部读取资料'两部分内容)
		$mconfigs = cls_env::mconfig();
		$btags = cls_cache::Read('btags');
		$_da = $this->_Get('_da');
		
		foreach(array('_da','mconfigs','btags',) as $var){
			extract($$var,EXTR_OVERWRITE);
			//foreach($$var as $k => $v) cls_env::SetG($k,$v);//应该不需要，而且可能有安全问题???
		}
		
		# 兼容base.inc.php所定义的散变量
		$BaseIncConfigs = cls_env::getBaseIncConfigs();
		extract($BaseIncConfigs,EXTR_OVERWRITE);
		
#		unset($mconfigs,$btags,$BaseIncConfigs);#  unset($_da);
		
		# 兼容general.inc.php所定义的散变量。可能会有遗漏，注意及时补充
		foreach(array('m_excache','m_cookie','onlineip','timestamp','authorization','debugtag','dbcharset','db','curuser','memberid',) as $k){
			$$k = cls_env::GetG($k);
		}
		# ---------------------------------------------------------------------------		
		
		# 提取输出内容
		ob_start();
		try{ # 比如捕捉tpl_exit()的页面中断信息
			if(_08_DEBUGTAG){
				include $PHPCacheFileName;
			}else{
				@include $PHPCacheFileName;
			}
		}catch(cls_ParseException $e){ # 意外中止信息，直接抛错
			throw new cls_ParseException($e->getMessage());
		}
		$_content = ob_get_contents();
		ob_end_clean();
		
		$re = array(
			'content' => $_content,
			'pcount' => isset(self::$_mp['pcount']) ? self::$_mp['pcount'] : 1,
		);
		
		return $re;
	}
	
	# 更新固定变量($G、$_da、$_mp、$_a)，使用统一方法，方便以后取消global模式
	# 支持'G.x.y'格式的多维键名
	protected function _Get($Key = 'G'){
		if(!($Key = preg_replace('/[^\w\.]/', '', (string)$Key))) return;
		$_DotPos = strpos($Key,'.');
		$_Var = false === $_DotPos ? $Key : substr($Key,0,$_DotPos);
		
		if($_Var == 'G'){ # 暂时维持global，以兼容目前的模板
			return cls_env::GetG($Key);
		}elseif($_Var == '_mp'){
			return false === $_DotPos ? self::$_mp : cls_Array::Get(self::$_mp,substr($Key,$_DotPos + 1));
		}elseif(in_array($_Var,array('_da','_a',))){
			return false === $_DotPos ? $this->$_Var : cls_Array::Get($this->$_Var,substr($Key,$_DotPos + 1));
		}
	}
	
	# 更新固定变量($G、$_da、$_mp、$_a)，使用统一方法，方便以后取消global模式
	# 支持'G.x.y'格式的多维键名
	protected function _Set($Key = 'G',$Value){
		if(!($Key = preg_replace('/[^\w\.]/', '', (string)$Key))) return;
		$_DotPos = strpos($Key,'.');
		$_Var = false === $_DotPos ? $Key : substr($Key,0,$_DotPos);
		
		if($_Var == 'G'){ # 暂时维持global，以兼容目前的模板
			cls_env::SetG($Key,$Value);
		}elseif($_Var == '_mp'){
			if(false === $_DotPos){
				self::$_mp = $Value;
			}else{
				cls_Array::Set(self::$_mp,substr($Key,$_DotPos + 1),$Value);
			}
		}elseif(in_array($_Var,array('_da','_a',))){
			if(false === $_DotPos){
				$this->$_Var = $Value;
			}else{
				cls_Array::Set($this->$_Var,substr($Key,$_DotPos + 1),$Value);
			}
		}
	}
	
	# 处理当前激活参数数组$_a，并压入激活参数堆栈中暂存
	protected function _Active($SourceArray = array(),$isInit = false){
		$_ActiveArray = $this->_Get('_a'); # 原有的激活变量数组
		

		if($isInit){ # 页面开始时，初始化激活堆栈及当前激活数组
			$this->_ActiveParamStack = array();			# 清空激活堆栈
			$_ActiveArray = array();
		}
		
		# 取得当前资料数组中的激活变量，在旧激活数组的基础进行更新
		$_ActiveVarArray = $this->_ActiveVarArray();
		foreach($_ActiveVarArray as $k => $v){
			if(isset($SourceArray[$k])){
				$_ActiveArray[$k] = $v == 'cn' ? cnoneid($SourceArray[$k]) : $SourceArray[$k];
			}
		}
		
		array_unshift($this->_ActiveParamStack,$_ActiveArray);		# 将当前激活数组压入堆栈暂存
		$this->_Set('_a',$_ActiveArray);							# 当前激活变量数组赋值
	}
	
	
	# 在激活参数的堆栈中回退一层，并更新当前的激活参数数组$_a
	protected function _ActiveBack(){
		array_shift($this->_ActiveParamStack);
		$this->_Set('_a',@$this->_ActiveParamStack[0]);	# 为当前激活变量数组赋值
	}
	
	# 根据复合标签设置提取复合标签的数据，唯一入口。为了兼容，暂时保留对外接口
	protected function _Tag($tag = array()){
		return cls_TagParse::OneTag($tag);
	}
	
	# 取得PHP文件缓存的文件完全路径
	protected function _PHPCacheFileName(){
		if(!($PHPCacheFileName = cls_Parse::PHPCacheFileName($this->ParseSource,$this->SourceType))){
			throw new cls_ParseException('PHP模板缓存未知');
		}
		if(_08_DEBUGTAG || !is_file($PHPCacheFileName)){
			if(!($PHPCacheFileName = cls_Refresh::OneSource($this->ParseSource,$this->SourceType))){
				throw new cls_ParseException('PHP模板缓存未知');
			}
		}
		return $PHPCacheFileName;
	}
	
	# 需要激活的变量名的数组
	# 如果需要扩展，在子类中定义本方法
	protected function _ActiveVarArray(){
		if(empty(self::$_ActiveVarArray)){
			self::$_ActiveVarArray = array();
			
			# 常规ID激活变量
			foreach(array('aid','mid','ucid','chid','coid','mchid','mcaid','fcaid','vid','addid','fid','cuid','arid','cid','paid') as $k){
				self::$_ActiveVarArray[$k] = '';
			}
			$grouptypes = cls_cache::Read('grouptypes');
			foreach($grouptypes as $x => $y) self::$_ActiveVarArray['grouptype'.$x] = '';
			
			# 类目ID激活变量
			self::$_ActiveVarArray['caid'] = 'cn';
			$cotypes = cls_cache::Read('cotypes');
			foreach($cotypes as $x => $y) self::$_ActiveVarArray['ccid'.$x] = 'cn';
		}
		return self::$_ActiveVarArray;
	}	
		
	# 取得当前来源的处理实例(单例)
	private static function _ParseInstance($ParseInitConfig = NULL){		
		if(!is_null($ParseInitConfig)){
			$ParseClassName = self::_ParseClassName($ParseInitConfig);
			$_NewInstance = new $ParseClassName($ParseInitConfig); # 注意：是扩展类的实例
			array_unshift(self::$_Instances,$_NewInstance);
		}
		if(empty(self::$_Instances[0])){
			throw new cls_ParseException('模板解析类初始化错误');
		}		
		return self::$_Instances[0];
	}
	
	# 解析类cls_Parse的扩展类名与文件名规则，不使用自动加载，扩展类继承cls_Parse，在其基础上做扩展
	private static function _ParseClassName($ParseInitConfig){
		$ClassName = 'cls_Parse';
		$_ExClassName = '';
		switch(@$ParseInitConfig['SourceType']){
			case 'tplname':	# 普通模板
				$_ExClassName = (string)$ParseInitConfig['ParseSource'];
			break;
			case 'js':	# 动态JS标签
				$_ExClassName = (string)@$ParseInitConfig['ParseSource']['ename'].'_'.@$ParseInitConfig['ParseSource']['tclass'].'_js';
			break;
		}
		
		_08_FilesystemFile::filterFileParam($_ExClassName);
		if($_ExClassName){
			$_ExClassName = str_replace('.','_',$_ExClassName);
			$_ExClassFile = cls_tpl::TemplateTypeDir('tpl_model').$_ExClassName.'.php';
			if(is_file($_ExClassFile)){ #不使用类自动加载
				include_once $_ExClassFile;
				$_ExClassName = 'tpl_'.$_ExClassName;
				if(class_exists($_ExClassName)){
					$ClassName = $_ExClassName;
				}
			}
		}
		return $ClassName;
	}
	
	# 将当前实例销除
	private static function _DestroyNowInstance(){
		if(isset(self::$_Instances[0])){
			self::$_Instances[0] = NULL;
			array_shift(self::$_Instances);
		}
	}
	
}
