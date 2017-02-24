<?php
/**
 * 生成前台页面(动态/静态/js标签/广告标签等)的共用处理总基类
 * 所有类型的前台页面(需要模板或标签解析)的均继承此基类
 */
defined('M_COM') || exit('No Permission');
abstract class cls_FrontPageBase extends cls_BasePage implements ICreate{
	
	protected $_QueryParams = array(); 					# 外来GP变量
	protected $_SystemParams = array();					# 用于模板解析之前经过预处理的变量，合并时优先
	protected $_MainData = array();						# 页面主体资料数组
	protected $_Cfg = array();							# 当前流程中需要临时保存的其它变量数组
	protected $_NormalVars = array();					# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录
	
	protected $_SourceType = 'tplname';					# 来源类型：(1)tplname(页面模板) (2)js(js标签) (3)adv(广告) (4)fragment(碎片) (4)adminm(会员中心脚本)
	protected $_ParseSource = '';						# 页面模板名称/模板标签配置(如标签js调用)/会员中心脚本名称
	
	protected $_oPageCache = NULL;						# 页面缓存操作实例
 	protected $_PageCacheParams = array(); 				# 页面缓存附加参数
	
	protected $_inStatic = false;						# 动态模式(false)/静态模式(true)
	protected $_inMobile = false;						# 是否手机版
	
	
	abstract protected function _Init($Params = array());					# 应用实例的基本初始化
	
	# 内部初始化入口
	protected static function _iCreate($ExtendAplicationClass,$Params = array()){
		if(!$ExtendAplicationClass || !class_exists($ExtendAplicationClass)){
			exit("扩展应用类[$ExtendAplicationClass]未设置");
		}
		$_Instance = new $ExtendAplicationClass();
		$_Instance->_Init($Params);
		if($_Instance->_inStatic){ # 生成静态
			$re = $_Instance->_StaticAllPage(); # 多页码一次性生成，返回生成信息/错误提示
		}else{ # 动态页
			$re = $_Instance->_DynamicOnePage();
		}
		unset($_Instance);
		return $re;
	}
	
	protected function __construct(){
		
		self::$db = _08_factory::getDBO();
		self::$curuser = cls_env::GetG('curuser');
		self::$tblprefix = cls_env::GetG('tblprefix');
		self::$timestamp = cls_env::GetG('timestamp');
		self::$cms_abs = cls_env::mconfig('cms_abs');
		
		$this->_QueryParams = cls_env::_GET_POST(); # 外来GP变量
        //排除固定的多余的项
        if(empty($this->_QueryParams['domain'])) unset($this->_QueryParams['domain']);
		
		$this->_Cfg['AllowStatic'] = false;				# 当页是否允许生成静态
		$this->_Cfg['MpUrlStatic']	= false; 			# 分页Url是否需要静态
		$this->_Cfg['maxStaicPage']	= 0;				# 多页码静态时，限只生成前几页。0为不限，所有页码都生成
		$this->_Cfg['SonBlockOfPage']	= false;		# 用于支持主体页面模板中嵌套模板区块，页面主体(false)/子区块(true)，允许跨页面类型进行嵌套
		$this->_Cfg['LoadAdv'] = false;					# 是否需要生成广告js调用代码
	}
	
	
	# 生成动态页面
	protected function _DynamicOnePage($Params = array()){
		$this->_CheckSiteClosed(); # 检查站点关闭
		$Content = '';
		try{
			try{
				$re = $this->_CreateOnePage();
				$Content = $re['content'];
				$this->_SavePageCache($Content);
			}catch(cls_PageCacheException $PageCache){ # 捕捉页面缓存，后续流程中止
				$Content = $PageCache->getMessage();
			}
		}catch(cls_PageException $e){ # 意外中止信息
			$Content = $e->getMessage();
		}
		return $this->_DynamicResultOut($Content);
	}
	
	# 生成全部分页的静态页面
	# 目前的处理是一次性生成同一文档的所有分页，对于单文档内的辑内列表(可能是无穷的)，需要特别注意??????????????????
	protected function _StaticAllPage(){
		$_start_time = microtime(TRUE);
		
		$maxStaticPage = 1;
		$PageByteSize = 0;
		for($this->_SystemParams['page'] = 1;$this->_SystemParams['page'] <= $maxStaticPage;$this->_SystemParams['page'] ++){
			try{
				$re = $this->_CreateOnePage();
			}catch(cls_PageException $e){
				return $e->getMessage();
			}
			
			if($error = $this->_SaveStaticFile($re['content'])){
				return $error;
			}
			$PageByteSize += strlen($re['content']);
			$maxStaticPage = $this->_MaxStaticPageNo(@$re['pcount']);
		}
		cls_env::SetG('_no_dbhalt',false); # 静态时关闭SQL错误中断 ????
		
		# 生成静态后更新相关信息(时间，Url等)到主体记录
		$this->_UpdateStaticRecord();
		
		# 正确生成完成后的返回信息
		$_Msg = $maxStaticPage."个分页  ";
		$_Msg .= round(microtime(TRUE) - $_start_time,2)."s ";
		$_Msg .= $PageByteSize."byte >> ".cls_url::m_parseurl($this->_StaticFilePre(),array('page' => 1));
		return $_Msg;
	}
	
	
	# 生成的单页面（当前页码）
	protected function _CreateOnePage(){
		$this->_InitMainPage();
		$this->_Model();
		$re = $this->_View();
		return $re;
	}
	
	# 初始化主体页面，如果是页面内模板(SonBlockOfPage为true)则不需要处理。
	protected function _InitMainPage(){
		if(!$this->_Cfg['SonBlockOfPage']){
			$this->_LoadExtendFunc();
			self::$_mp = array();
			cls_env::SetG('G',array());# 页面共用变量容器$G，暂时维持global，以兼容目前的模板
		}
	}
	
	# 加载模板扩展函数，可根据不同类型的页面，在子类中加载不同的扩展函数
	protected function _LoadExtendFunc(){
		foreach(array(cls_tpl::TemplateTypeDir('function').'utags.fun.php',) as $k){
			if(is_file($k)) include_once $k;
		}
	}
	# 数据模型处理
	protected function _Model(){
		$this->_PageNo(); # 当前页码处理
		
		# 静态时只需要第一页建立全新数据
		if(!$this->_inStatic || $this->_SystemParams['page'] == 1){
			$this->_ModelCumstom(); # 不同类型页面的数据模型
			$this->_ModelCommon(); # 数据模型的通用部分
			$this->_ModelEnd(); # 数据模型收尾部分
		}else{
			# 静态中的第二页码开始，只需要更新分页资料，其它资料保持。
			$this->_MpConfig(); # 分页配置
		}
	}
	
	# 数据模型的通用部分
	# 公用基类中的本方法主要是针对页面模板(tplname)类型的处理，如果是其它类型，可在相应子类中定义
	protected function _ModelCommon(){
		$this->_Addno(); # 附加页编号处理
		if(!$this->_inStatic){
			$this->_NormalVars(); # 页面常规变量
			$this->_AllowRobot(); # 禁止搜索引擎抓取附加参数的页面
			$this->_ReadPageCache(); # 读取页面缓存(可能需要借用页面资料，请注意放置顺序)
		}
		$this->_MainData(); # 读取页面主体资料
		$this->_ParseSource(); # 获得页面模板
		$this->_Mdebug(); # 当前页面调试信息
		$this->_MpConfig(); # 分页配置
	}
	
	# 数据模型收尾部分
	protected function _ModelEnd(){
		$this->_ModelExtend(); # 预留数据模型的扩展接口
		$this->_MainDataCombo(); # 主体数据合并，最后执行!
	}
	
	# 模板解析
	protected function _View(){		
		$_ParseInitConfig = $this->_ParseInitConfig(); # 解析页面所需要的完整数据资料
		try{		  
            if ( isset($_ParseInitConfig['_da']['action']) && (strtolower($_ParseInitConfig['SourceType']) == 'js') )
            {
                $re = self::_MultiSourceCode($_ParseInitConfig);
            }
            else
            {
                $re = cls_Parse::OneSourceCode($_ParseInitConfig);
            }
		}catch(cls_ParseException $e){
			throw new cls_PageException($e->getMessage());
		}
		$this->_ViewToolParams($re);
		$this->_ViewAdv($re);
		return $re;
	}
    
    /**
     * 解析AJAX参数
     * 允许让AJAX一次请求多个JS模块
     * @example http://auto.08cms.com/tools/ajax.php?action=get_tag&tname=532194d643&iteration=cid&data[cid]=411,410,408&data_format=js&_=1393829251424
     * 
     * @param  array $_ParseInitConfig 参数配置
     * @return array $re               资源数组
     * @since  auto5.0
     */
    protected function _MultiSourceCode( $_ParseInitConfig )
    {
        $iterationString = $iteration = '';
        $params = array();
        if ( isset($_ParseInitConfig['_da']['iteration']) )
        {
            $iteration = $_ParseInitConfig['_da']['iteration'];
        }
        if ( !empty($_ParseInitConfig['_da']['data'][$iteration]) )
        {
            $re = array();
            $params = array_filter(explode(',', $_ParseInitConfig['_da']['data'][$iteration]));
            foreach ( $params as $param ) 
            {
                $_ParseInitConfig['_da'][$iteration] = $_ParseInitConfig['_da']['data'][$iteration] = $param;
                $re = cls_Parse::OneSourceCode($_ParseInitConfig);
                if ( $iterationString )
                {
                    $iterationString .= '<!--_08_TAG_SPILT-->';
                }
                $iterationString .= $re['content'];
            }
            
            $re['content'] = $iterationString;
        }
        else  # 只为兼容之前代码
        {
        	$re = cls_Parse::OneSourceCode($_ParseInitConfig);
        }        
        
        return $re;
    }
	
	# 禁止搜索引擎抓取附加参数的页面
	protected function _AllowRobot(){
		if($this->_inStatic) return;
		cls_env::AllowRobot($this->_QueryParams,$this->_NormalVars);
	}
	
	# 拼接Url附加参数字串(页面常规变量排除在外)
	protected function _Filterstr(){
		$this->_SystemParams['filterstr'] = '';
		if($this->_inStatic) return;
		foreach($this->_QueryParams as $k => $v){
			if(!in_array($k,$this->_NormalVars)){
				$this->_SystemParams['filterstr'] .= "&$k=".rawurlencode(@stripslashes($v));
			}
		}
		if($this->_SystemParams['filterstr']){
			$this->_Cfg['AllowStatic'] = 0;
		}
	}
	
	# 分页配置以及分页资料初始化
	protected function _MpConfig(){
		$this->_Filterstr(); # 拼接Url附加参数字串
		self::$_mp = array(
			'nowpage' => $this->_SystemParams['page'],
			'durlpre' => $this->_UrlPre(false),
			'surlpre' => $this->_UrlPre(true),
			'static' => $this->_Cfg['MpUrlStatic'],
			's_num' => $this->_Cfg['maxStaicPage'],
		);
		$this->_Mp_Init();
	}
	
	# 分页配置以及分页资料初始化
	protected function _Mp_Init(){
		if(empty(self::$_mp)) return; # 只在配置了分页的情况下才初始化
		self::$_mp['pcount'] = 1;
		self::$_mp['acount'] = 0;
		self::$_mp['limits'] = 10;
		self::$_mp['length'] = 10;
		self::$_mp['simple'] = 1;
		self::$_mp['static'] = empty(self::$_mp['static']) ? 0 : 1;
		self::$_mp['mptitle'] = '';
		self::$_mp['acount'] = 0;
		self::$_mp['mppage'] = self::$_mp['nowpage'];
		self::$_mp['mpcount'] = self::$_mp['pcount'];
		self::$_mp['mpacount'] = self::$_mp['acount'];
		foreach(array('mpstart','mpend','mppre','mpnext',) as $k) self::$_mp[$k] = '#';
		self::$_mp['mpnav'] = '';
	}
	
	# 将其它数据合并到主体数据，并做相关处理
	# 为了兼容当前单一主体资料入口($_da)
	protected function _MainDataCombo(){
		$this->_MainData = array_merge($this->_MainData,$this->_SystemParams);# _SystemParams优先
		if(!$this->_inStatic){
			$this->_MainData += $this->_QueryParams;
			cls_env::repGlobalValue($this->_MainData); # XSS
		}
	}
	
	# 读取动态页面缓存，需要将缓存返回后，根据本类决定是返回还是输出????????????
	protected function _ReadPageCache(){
		if($this->_inStatic || empty($this->_PageCacheParams['typeid'])) return;
		$this->_PageCacheParams['page'] = $this->_SystemParams['page'];
		$this->_oPageCache = new cls_pagecache();
		$Content = $this->_oPageCache->read($this->_QueryParams,$this->_PageCacheParams);
		if(!is_null($Content)){ # 为了兼容"结果返回"与"直接打印"这两种方式，将页面缓存以意外抛出，并中止后续流程
			throw new cls_PageCacheException($Content);
		}
	}
	
	# 缓存动态页面结果
	protected function _SavePageCache($Content){
		if(!empty($this->_oPageCache)){
			$this->_oPageCache->save($Content);
		}
	}
	
	# 当前页码处理
	protected function _PageNo(){
		$Page = empty($this->_SystemParams['page']) ? @$this->_QueryParams['page'] : $this->_SystemParams['page'];
		$this->_SystemParams['page'] = max(1,intval($Page));
	}
	
	# 组装解析页面所需要的完整数据资料
	protected function _ParseInitConfig(){
		$re = array(
			'SourceType' => $this->_SourceType, 		# 解析来源类型
			'ParseSource' => $this->_ParseSource, 		# 解析来源
			'_da' => $this->_MainData,					# 当前来源的主体数据
		);
		return $re;
	}
	
	# 保存静态文件
	protected function _SaveStaticFile($Content = ''){
		$StaticFilePre = $this->_StaticFilePre();
		if(!$StaticFilePre) return '静态生成格式未定义';
		$StaticFile = cls_url::m_parseurl($StaticFilePre,array('page' => $this->_SystemParams['page']));
		$re = str2file($Content,M_ROOT.$StaticFile);
		return $re ? '' : "$StaticFile 无法写入";
	}
	
	# 最大静态页数
	# $Pcount：页面总分页数
	protected function _MaxStaticPageNo($Pcount = 1){
		$Pcount = max(1,intval($Pcount));
		$re = empty($this->_Cfg['maxStaicPage']) ? $Pcount : min($Pcount,$this->_Cfg['maxStaicPage']);
		$re = max(1,$re);
		return $re;
	}
	
	# 处理ToolJs字串
	protected function _ViewToolParams(&$ContentArray = array()){
		if(isset($ContentArray['content'])){
			$ToolParams = $this->_ToolParams(); # 生成ToolJs的参数数组
			$ContentArray['content'] .= cls_phpToJavascript::PtoolJS($ToolParams);
		}
	}
	
	# 处理广告js调用代码
	protected function _ViewAdv(&$ContentArray = array()){
		if(!empty($ContentArray['content'])){
			if(!empty($this->_Cfg['LoadAdv'])){
				$ContentArray['content'] .= cls_phpToJavascript::LoadAdv();
			}
		}
	}
	
	# 检查站点关闭
	protected function _CheckSiteClosed(){
		cls_env::CheckSiteClosed();
	}
	
	# 输出/返回动态结果(基类的本方法适用于普通页面的输出，如js,ajax请在子类中自行定义)
	protected function _DynamicResultOut($Content){
		$Content .= $this->_ViewMdebug();	
		if(empty($this->_Cfg['DynamicReturn'])){ # 直接打印输出
			//echo ' PRE_content '; //$a = 2/0;
			$preData = ob_get_contents(); //把前面的输出(如错误信息)一同保存下来
			ob_end_clean();
			cls_env::mob_start(true);			
			echo $preData.$Content;
			exit();
		}else{ # 返回结果
			return $Content;
		}
	}
	
	# 当前页面调试信息
	protected function _ViewMdebug(){
		$Return = '';
		if($_mdebug = cls_env::GetG('_mdebug')){
			$Return = $_mdebug->view();
		}
		return $Return;
	}
	
	# 检查静态操作中，是否允许生成静态
	protected function _CheckStatic(){
		if($this->_inStatic && empty($this->_Cfg['AllowStatic'])){
			throw new cls_PageException($this->_PageName()." - 不允许生成静态");
		}
	}
	
	# 搜索权限与频度控制
	protected function _CheckSearchPermission(){
		if($error = self::$curuser->noPm($this->_Cfg['search_pmid'])){
			throw new cls_PageException($error);
		}
		if($this->_Cfg['search_repeat']){
			$diff = self::$timestamp - @self::$curuser->info['lastsearch'];
			//修改过系统时间搜索后,再还原系统时间,可能使$diff为负数,此情况算做正常搜索
			if($diff>0 && $diff < $this->_Cfg['search_repeat']){
				throw new cls_PageException('搜索操作过于频繁');
			}
			self::$db->query("UPDATE ".self::$tblprefix."msession SET lastsearch='".self::$timestamp."' WHERE msid='".@self::$curuser->info['msid']."'",'SILENT');
		}
	}
	
	
	

# ******************以下为防止不同的子类中不需要而未定义，预留的空接口 *********************************************
	# 预留一个空的数据模型扩展接口
	protected function _ModelExtend(){}
		
	# 生成静态后更新相关信息(时间，Url等)到主体记录，预留空接口
	protected function _UpdateStaticRecord(){}
	
	# 页面名称，预留空接口
	protected function _PageName(){
		return '指定页面';
	}
	
	# 读取页面主体资料，预留空接口
	protected function _MainData(){}
	
	# 页面常规变量名，区别于附加变量，辅助决定分页Url是否需要静态、是否需要搜索引擎收录，预留空接口
	protected function _NormalVars(){}
		
	# 附加页编号处理，预留空接口
	protected function _Addno(){}
	
	# 不同类型页面的数据模型，预留空接口
	protected function _ModelCumstom(){}
	
	# 获得页面模板，预留空接口
	protected function _ParseSource(){}

	# 当前页面调试信息，预留空接口
	protected function _Mdebug(){}
	
	# 取得分页Url套用格式，预留空接口
	protected function _UrlPre($isStatic = false){
		return '';
	}
	
	# 取得分页_静态文件保存格式，预留空接口
	protected function _StaticFilePre(){}
	
	# 生成ToolJs的参数数组，预留空接口
	protected function _ToolParams(){
	}
	
	
}

interface ICreate {
    public static function Create($Params = array());	# 页面生成的外部执行入口
}