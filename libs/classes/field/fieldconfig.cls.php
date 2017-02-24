<?php
/* 
** 字段的配置操作(添加、修改、删除等)，兼容将字段保存到'模板缓存/数据库'这两种方式
** 暂时只做单个字段的配置的修改，列表管理之后再整合进来或另行架构
** 注意：会员的系统字段的配置是不可修改的，文档或副件的系统字段可修改。
** 模板配置缓存有两种：应用缓存(如ffields*.cac.php)，对应初始完全数据源(如 _ffields*.cac.php)
** 注意数据表与模板数据源保存时的差别：前者cfgs为格式化后的字串，后者cfgs为数组
** 因为兼容模板数据源与数据库保存两种方式，更新数据源的同时不更新应用缓存，所以要注意需要另外更新应用缓存
*/

!defined('M_COM') && exit('No Permission');
class cls_FieldConfig{
	
	protected static $db = NULL;//数据库连接
	protected static $Table = 'afields';//字段配置表
    protected static $params = array();
	protected static $fmdata = array();//表单数组
	
	# 载体相关
	protected static $SourceType = '';//当前载体类型
	protected static $SourceID = '';//当前载体ID
	protected static $SourceConfig = '';//当前载体资料
	
	# 当前字段资料
	protected static $datatype = '';//当前字段类型
	protected static $isNew = false;//是否添加字段
	protected static $oldField = array();//原字段资料
	protected static $newField = array();//修改后经过处理的字段资料
	private static $DataTypeInstance = NULL;//具体类型字段类的实例
	
	
	# 更新应用缓存
	public static function UpdateCache($SourceType = 'channel',$SourceID = 0){
		self::_SaveCache($SourceType,$SourceID);
	}
	
	# 专用于更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($SourceType = 'channel',$SourceID = 0,$CacheArray = ''){
		self::_SaveCache($SourceType,$SourceID,$CacheArray,true);
	}
	
	# 动态的字段资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	# $KeepDB 保持数据库读出的格式，不对数据做处理，用于将数据在数据表中复制记录
	public static function InitialFieldArray($SourceType = 'channel',$SourceID = 0,$OnlyAvailable = false,$KeepDB = false){
		if(self::_CheckSource($SourceType,$SourceID)) return array();
		
		if(self::isTemplateConfig($SourceType)){
			$re = cls_cache::Read(self::FieldCacheName($SourceType,true),$SourceID,'',1);
			foreach($re as $k => $v){
				if($OnlyAvailable && empty($v['available'])){
					unset($re[$k]);
					continue;
				}
				unset($re[$k]['fid']);
			}
		}else{
			$re = array();
			self::$db->select('*')->from(self::_Table())->where(array('type' => self::FieldType($SourceType)))->_and(array('tpid' => $SourceID));
			if($OnlyAvailable){
				self::$db->_and(array('available' => 1));
			}
			self::$db->order('vieworder,fid')->exec();
			while($r = self::$db->fetch()){
				if(!$KeepDB){
					cls_CacheFile::ArrayAction($r,'cfgs','varexport');
				}
				unset($r['fid']);
				$re[$r['ename']] = $r;
			}
		}	
		return $re;
		
	}
	
	# 检查新定义的ename是否合法
	public static function CheckNewID($SourceType = 'channel',$SourceID = 0,$ename = ''){
		if(!($ename = self::InitID($ename))) return '唯一标识不能为空';
		if($re = self::_InitSource($SourceType,$SourceID)) return $re;
		if(preg_match(self::_EnameRegular(self::$SourceType),$ename))  return '字段标识不合规范。';
		if(in_array($ename,self::_UsedEnameArray())) return '字段标识重复';
		if(self::_isDBKeepWord($ename)) return '字段标识禁止使用';
		return '';
	}
	
	# 对ename进行初始格式化
    public static function InitID($ename = ''){
		$ename = empty($ename) ? '' : trim(strtolower($ename));
		return cls_string::ParamFormat($ename);
	}
	
	
	# 动态的单个字段资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneField($SourceType = 'channel',$SourceID = 0,$ename = ''){
		if(self::isTemplateConfig($SourceType)){
			$fields = self::InitialFieldArray($SourceType,$SourceID);
			$re = @$fields[$ename];
		}else{
			if(self::_CheckSource($SourceType,$SourceID)) return array();
			$re = self::$db->select('*')->from(self::_Table())->where(array('type' => self::FieldType($SourceType)))->_and(array('tpid' => $SourceID))->_and(array('ename' => $ename))->exec()->fetch();
			if($re){
				cls_CacheFile::ArrayAction($re,'cfgs','varexport');
				unset($re['fid']);
			}
		}
		$re = $re ? $re : array();
		return $re;
	}
	
	# 编辑单个字段的配置
	public static function EditOne($SourceType = 'channel',$SourceID = 0,$FieldName = ''){
		self::_InitEdit($SourceType,$SourceID);
		self::_LoadOneField($FieldName);
		self::$isNew = $FieldName ? false : true;
		if(!submitcheck('bsubmit')){
			self::_ViewOne();
		}else{
			self::_SaveOne();
		}
	}
	
	# 复制指定载体的字段，只涉及afields记录及字段缓存，不涉及表结构的变更，用于配合复制一个载体。
	public static function CopyOneSourceFields($SourceType = 'channel',$fromID = 0,$toID = 0){
		
		if(!($fromID = self::SourceInitID($SourceType,$fromID))){ # 栏目与会员通用字段不能复制
			throw new Exception('请指定来源载体ID。');
		}
		if(!($toID = self::SourceInitID($SourceType,$toID))){
			throw new Exception('请指定目的载体ID。');
		}
		if($re = self::_InitSource($SourceType,$toID)){ # 读取目的载体资料
			throw new Exception($re);
		}
		
		$CacheArray = self::InitialFieldArray($SourceType,$fromID,false,true);
		foreach($CacheArray as $k => &$v){
			$v['tpid'] = $toID;
			$v['tbl'] = self::_ContentTable($v);
		}
		if(self::isTemplateConfig($SourceType)){ # 删除完全数据源
			self::SaveInitialCache($SourceType,$toID,$CacheArray);
		}else{
			self::_InitDB();
			$CacheArray = maddslashes($CacheArray,true);
			foreach($CacheArray as $k => $v){
				self::$db->insert(self::_Table(),$v)->exec();
			}
		}
		# 更新字段缓存
		self::UpdateCache($SourceType,$toID);
		return true;
	}
	
	# 删除指定来源的所有字段配置，通常是为了配合来源的删除，在这里不涉及内容表结构的处理及前期的预检测
	# 同时删除字段缓存
	public static function DeleteOneSourceFields($SourceType = 'channel',$SourceID = 0){
		if(!($SourceID = self::SourceInitID($SourceType,$SourceID))) return; # 栏目与会员通用字段不能作为一个来源，删除其所有字段
		if(self::isTemplateConfig($SourceType)){ # 删除完全数据源
			cls_CacheFile::Del(self::FieldCacheName($SourceType,true),$SourceID); 
		}else{
			self::_InitDB();
			self::$db->delete(self::_Table())->where(array('type' => self::FieldType($SourceType)))->_and(array('tpid' => $SourceID))->exec();
		}
		cls_CacheFile::Del(self::FieldCacheName($SourceType),$SourceID);
	}
	
	# 通过传入数组的方法，添加或更新一条字段记录，不涉及关联的内容表结构
	# 通常用于配合插入新表字段新增的字段记录、升级时更新字段配置(内容表已另外处理)
	# 传入的字段资料不进行检测及处理，在传入之前需要掌控新增资料的完整与合法性。
	public static function ModifyOneConfig($SourceType = 'channel',$SourceID = 0,array $newField = array(),$FieldName = ''){
		if($re = self::_InitSource($SourceType,$SourceID)) cls_message::show($re);
		self::_LoadOneField($FieldName);
		self::$isNew = $FieldName ? false : true;
		if(self::$isNew){
			foreach(array('ename','cname','datatype') as $k){
				if(empty($newField[$k])) cls_message::show('缺少资料，新字段资料无法建立');
			}
			self::$newField = self::_OneBlankField();
		}else{
			self::$newField = maddslashes(self::$oldField,true);
		}
		foreach(self::$newField as $k => &$v){
			if(isset($newField[$k])){
				$v = $newField[$k];
			}
		}
		
		# 增加或修改当前字段的配置记录
		self::_SaveOneConfig();
		
		# 更新字段缓存
		self::UpdateCache($SourceType,$SourceID);
	}
	
	# 删除指定的字段，按字段名称，可同时删除多个字段
	# 同时处理内容表结构，通用字段的字段配置副本等
	public static function DeleteField($SourceType = 'channel',$SourceID = 0,$DelArray = array(),$UpdateCache = false){
		if(!$DelArray) return array();
		if(!is_array($DelArray)) $DelArray = array($DelArray);
		
		# 处理当前字段记录，不包括通用字段在各个模型中的副本
		$Deleteds = array();
		foreach($DelArray as $ename){
			if($_field = self::InitialOneField($SourceType,$SourceID,$ename)){ 
			
				# 排除不能删除的字段
				if(($SourceType == 'mchannel') && $SourceID){ # 在会员模型字段管理时，不能删除通用字段
					if(!empty($_field['iscommon'])) continue;
				}else{
					if(empty($_field['iscustom'])) continue; # 非自定字段不能删除
				}
				
				# 删除相关内容表的字段
				cls_dbother::DropField($_field['tbl'],$_field['ename'],$_field['datatype']);
				
				# 删除本字段的配置记录
				self::_DelOneConfig($SourceType,$SourceID,$ename);
  
				$Deleteds[] = $_field['ename'];
			}
		}
		
		if($Deleteds){
			# 更新缓存
			if($UpdateCache){
				self::UpdateCache($SourceType,$SourceID);
			}
			# 对会员通用字段在各个模型中的副本进行特别处理
			if(($SourceType == 'mchannel') && !$SourceID){
				foreach($Deleteds as $ename){
					self::$db->delete(self::_Table())->where(array('type' => self::FieldType($SourceType)))->_and(array('ename' => $ename))->exec();
				}
				$mchannels = cls_mchannel::InitialInfoArray();
				foreach($mchannels as $k => $v){
					self::UpdateCache(self::$SourceType,$k);
				}
			}
		}
		return $Deleteds;
	}
	# 取得字段类型资料，指定$type，返回该类型标题，否则返回所有字段类型数组
	public static function datatype($type = ''){
		$datatypes = array(//所有允许的字段类型
			'text' => '单行文本',
			'multitext' => '多行文本',
			'htmltext' => 'Html文本',
			'image' => '单图',
			'images' => '图集',
			'flash' => 'Flash',
			'flashs' => 'Flash集',
			'media' => '视频',
			'medias' => '视频集',
			'file' => '单点下载',
			'files' => '多点下载',
			'select' => '单项选择',
			'mselect' => '多项选择',
			'cacc' => '类目选择',
			'date' => '日期(时间戳)',
			'int' => '整数',
			'float' => '小数',
			'map' => '地图',
			'vote' => '投票',
			'texts' => '文本集',
		);
		return $type ? (isset($datatypes[$type]) ? $datatypes[$type] : '') : $datatypes;
	}
	# 是否保存为模板配置缓存
    public static function isTemplateConfig($SourceType = 'channel'){
		return in_array($SourceType,array('fchannel','pusharea',)) ? true : false;
	}
	
	# 字段缓存名称
    public static function FieldCacheName($SourceType,$isInit = false){
		return ($isInit ? '_' : '').($SourceType == 'channel' ? '' : self::FieldType($SourceType)).'fields';
    }
	
	public static function FieldType($SourceType = 'channel'){
		return self::_SourceVar($SourceType,'Type');
	}
	
	# 根据传入的字段配置参数，修改或新增关联内容表的结构
	public static function AlterContentTableByConfig($newCfg = array(),$isNew = false,$oldCfg = array()){
		
		if(empty($newCfg['tbl']) || empty($newCfg['ename']) || empty($newCfg['datatype'])){
			throw new Exception('请指定内容表，字段标识及类型。');
		}
		if(in_array($newCfg['datatype'],array('mselect','select','text',)) && !isset($newCfg['length'])){
			throw new Exception('未指定数据表字段长度。');
		}
		if(($newCfg['datatype'] == 'cacc') && !isset($newCfg['cnmode'])){
			throw new Exception('请指定分类的选择方式。');
		}
		
		if(in_array($newCfg['datatype'],array('files','flashs','htmltext','images','medias','multitext','texts','vote',))){
			$_sqlstr = "text NOT NULL";
		}elseif(in_array($newCfg['datatype'],array('file','flash','image','media',))){
			$_sqlstr = "varchar(255) NOT NULL default ''";
		}elseif(in_array($newCfg['datatype'],array('mselect','text',))){
			$_sqlstr = "varchar(".$newCfg['length'].") NOT NULL default ''";
		}elseif($newCfg['datatype'] == 'select'){
			$_sqlstr = $newCfg['length'] ? "varchar($newCfg[length]) NOT NULL default ''" : "int(10) NOT NULL default 0";
		}elseif($newCfg['datatype'] == 'int'){
			$_sqlstr = "int(11) NOT NULL default 0";
		}elseif($newCfg['datatype'] == 'map'){
			$_sqlstr = "varchar(40) NOT NULL default ''";
		}elseif($newCfg['datatype'] == 'date'){
			$_sqlstr = "int(10) NOT NULL default 0";
		}elseif($newCfg['datatype'] == 'float'){
			$_sqlstr = "float NOT NULL default 0";
		}elseif($newCfg['datatype'] == 'cacc'){
			$_sqlstr = "smallint(6) unsigned NOT NULL default 0";
		}
		
		if($isNew){
			cls_dbother::AddField($newCfg['tbl'],$newCfg['ename'],$newCfg['datatype'],$_sqlstr);
			if(($newCfg['datatype'] == 'cacc') && $newCfg['cnmode']){ # 如果是多选
				cls_dbother::AlterFieldSelectMode($newCfg['cnmode'],0,$newCfg['ename'],$newCfg['tbl']);
			}
		}else{
			if(in_array($newCfg['datatype'],array('mselect','select','text',)) && $newCfg['length'] != $oldCfg['length']){ # 变更字段长度
				self::$db->query("ALTER TABLE ".cls_env::GetG('tblprefix').$newCfg['tbl']." CHANGE ".$newCfg['ename']." ".$newCfg['ename']." $_sqlstr");
			}elseif(($newCfg['datatype'] == 'cacc') && $newCfg['cnmode'] != $oldCfg['cnmode']){ # 变更单/多选
				if(!cls_dbother::AlterFieldSelectMode($newCfg['cnmode'],$oldCfg['cnmode'],$newCfg['ename'],$newCfg['tbl'])){
					$newCfg['cnmode'] = $oldCfg['cnmode'];
				}
			}
		}
		return $newCfg;
	
	}	
	private static function _Table(){
		return '#__'.self::$Table;
	}
	
    private static function _InitEdit($SourceType = 'channel',$SourceID = 0){
		if($re = self::_InitSource($SourceType,$SourceID)) cls_message::show($re);
		self::$params = cls_env::_GET_POST();
		if(!empty(self::$params['fmdata'])) self::$fmdata = self::$params['fmdata'];
		if(self::$SourceType == 'commu' && empty(self::$SourceConfig['tbl'])) cls_message::show('未设置交互表。');
    }
	
	# 检查字段所属载体的类型及详细资料
	# 使用于需要切换载体，但不更改当前self::$SourceConfig及self::$SourceID的场合
    private static function _CheckSource($SourceType = 'channel',$SourceID = 0){
		if(!self::_SourceVar($SourceType)) return '请指定字段所属载体类型。';
		if(!self::_LoadSourceConfig($SourceType,$SourceID)) return '载体资料不能为空。';
		self::_InitDB();
	}
	
	# 初始化数据库
    protected static function _InitDB(){
		self::$db = _08_factory::getDBO();
	}
	
	# 取得字段所属载体的类型及详细资料
    private static function _InitSource($SourceType = 'channel',$SourceID = 0){
		$SourceID = self::SourceInitID($SourceType,$SourceID);
		if((self::$SourceType == $SourceType) && (self::$SourceID == $SourceID) && self::$SourceConfig) return; # 避免重复加载
		self::$SourceType = $SourceType;
		if(!self::_SourceVar(self::$SourceType)) return '请指定字段所属载体类型。';
		self::$SourceID = self::SourceInitID($SourceType,$SourceID);
		self::$SourceConfig = self::_LoadSourceConfig(self::$SourceType,self::$SourceID);
		if(empty(self::$SourceConfig)) return '载体资料不能为空。';
		self::_InitDB();
	}
	
	# 取得指定载体的资料
    private static function _LoadSourceConfig($SourceType = 'channel',$SourceID = 0){
		$re = array();
		if(empty($SourceID)){
			switch($SourceType){
				case 'mchannel': # 相当于mchid=0的一个会员模型
					$re	= array(
						'mchid' => 0,
						'cname' => '通用字段',
					);
				break;
				case 'catalog': # 模拟类系的设置，注意参数跟随了栏目，去配合afield中的tpid=0的属性
					$re	= array(
						'coid' => 0,
						'title' => '栏目',
					);
				break;
			}
		}else{
			$ClassName = self::_SourceVar($SourceType,'Class');
			$_tmpObj = new $ClassName(); //某些情况下,method_exists判断不出来(第一次),导致$re为空
			if($ClassName && method_exists($_tmpObj,'InitialOneInfo')){
				$re = call_user_func_array(array($ClassName,'InitialOneInfo'),array($SourceID));
			}
			unset($_tmpObj);
		}
		return $re;
		
	}
	# 取得指定字段的详细资料
    private static function _LoadOneField($FieldName = ''){
		if(!($FieldName = trim($FieldName))){//添加字段时不需要指定字段名
			self::$oldField = array();
			return;
		}
		self::$oldField = self::InitialOneField(self::$SourceType,self::$SourceID,$FieldName);
		if(empty(self::$oldField)) cls_message::show('指定的字段不存在。');
	}
	# 根据指定的操作，返回url，请注意需要在初始化载体资料后调用
	public static function _RouteUrl($Action = 'fieldone'){
		if(empty(self::$SourceConfig)) cls_message::show('载体资料不能为空。');
		$Params = array();
		$Params['entry'] = self::$SourceType.'s';
		if(self::$SourceID){
			$Params[self::_SourceVar(self::$SourceType,'ID')] = self::$SourceID;
		}
		switch($Action){
			case 'fieldone':
				$Params['action'] = $Action;
				if(!empty(self::$oldField['ename'])) $Params['fieldname'] = self::$oldField['ename'];
				break;
			case 'onefinish': # 暂时兼容现有旧规则，编辑(添加)完后返回的url
				switch(self::$SourceType){
					case 'mchannel':
						$Params['action'] = self::$SourceID ? 'mchannelfields' : 'initmfieldsedit';
						break;
					case 'fchannel':
						$Params['action'] = 'fchanneldetail';
						break;
					case 'catalog':
						$Params['action'] = 'cafieldsedit';
						break;
					case 'cotype':
						$Params['action'] = 'ccfieldsedit';
						break;
					case 'channel':
						$Params['action'] = 'channelfields';
						break;
					case 'commu':
						$Params['action'] = 'commufields';
						break;
					case 'pusharea':
						$Params['action'] = 'pushareafields';
						break;
					
						
				}
				break;
		}
		return self::_Url($Params);	
	}
	private static function _Url($Params = array()){
		$Url = '';
		foreach($Params as $k => $v){
			$Url .= '&'.$k.'='.rawurlencode($v);
		}
		$Url = $Url ? '?'.substr($Url,1) : '#';
		
		return $Url;
	}
	
    private static function _FieldFormHeader($HaveForm = true){
		$_Title = (self::$isNew ? '添加' : '编辑')."字段 - ".self::_SourceVar(self::$SourceType,'Name').' - '.self::$SourceConfig[self::_SourceVar(self::$SourceType,'Title')];
		echo "<title>$_Title</title>";
		if($HaveForm){
			tabheader($_Title,'field_detail',self::_RouteUrl(),2,0,1);
		}
	}
    private static function _FieldFormFooter(){
		a_guide('ffieldadd');
	
	}
	
	# 单个字段编辑时的字段类型
    private static function _FieldDataType(){
		if(!empty(self::$oldField)){
			self::$datatype = self::$oldField['datatype'];
		}elseif(!empty(self::$fmdata['datatype'])){
			self::$datatype = self::$fmdata['datatype'];
		}
		if(empty(self::$datatype) || !self::datatype(self::$datatype)) cls_message::show('请指定正确的字段类型。');
		
		# 建立具体类型字段的处理类对象，该对象继承当前类，当该对象的方法及属性未定义时，会继承本类中的方法及属性。
		$FieldClassName = 'cls_field_'.self::$datatype;
		self::$DataTypeInstance = new $FieldClassName;
	}
	
	# 单个字段配置的表单展示
    private static function _ViewOneForm(){
		self::_FieldFormHeader(); # 表单头部
		self::_FieldDataType(); # 处理字段类型
		
		self::_fm_cname(); # 字段中文名称
		self::_fm_ename(); # 英文唯一标识
		self::_fm_datatype(); # 显示字段类型
		self::_fm_iscommon(); # 处理iscommon设置项
		self::_fm_separator('字段配置');
		
		# 不同类型字段的差别区块
		self::$DataTypeInstance->_fm_custom_region();
		
		# 通用字段修改应用到已有模型
		self::_fm_common_to_other();
		
		tabfooter('bsubmit',self::$isNew ? '添加' : '提交');
		self::_FieldFormFooter();
	}
	
    private static function _SaveOne(){
		self::_FieldFormHeader(false); # 窗口标题
		self::_FieldDataType(); # 处理字段类型
		
		self::_sv_PreCommon(); # 通用部分的数据处理
		
		# 不同类型字段的差别处理，如果在具体的类中不需要，可以不定义此方法
		self::$DataTypeInstance->_sv_custom_region();
		
		# 根据字段的新增或修改，对内容数据表做相应的变更
		self::_sv_content_table();
		
		# 根据字段的新增或修改，对字段配置做相应的变更
		self::$DataTypeInstance->_sv_field_config();
		
		# 修改完成
		self::_sv_finish();
		
    }
	
	# 取得内容表的字段名，以免新建字段的标识已被使用
    private static function _UsedEnameArray(){
		$tbls = array();
		switch(self::$SourceType){
			case 'channel':
				$tbls = array('archives'.self::$SourceConfig['stid'],'archives_'.self::$SourceID);
				break;
			case 'mchannel':
				$tbls = array('members','members_sub');
				if(self::$SourceID) $tbls[] = 'members_'.self::$SourceID;
				break;
			case 'fchannel':
				$tbls = array('farchives','farchives_'.self::$SourceID);
				break;
			case 'catalog':
				$tbls = array('catalogs');
				break;
			case 'cotype':
				$tbls = array('coclass'.self::$SourceID);
				break;
			case 'commu':
				$tbls = array(self::$SourceConfig['tbl']);
				break;
			case 'pusharea':
				$tbls = array(cls_PushArea::ContentTable(self::$SourceID));
				break;
		}
		return cls_DbOther::ColumnNames(implode(',',$tbls));
    }
	# 取得内容表的表名
	# 首先需要初始化载体
    protected static function _ContentTable($FieldConfig = array()){
		switch(self::$SourceType){
			case 'channel':
				$Table = empty($FieldConfig['iscommon']) ? 'archives_'.self::$SourceID : 'archives'.self::$SourceConfig['stid'];
				break;
			case 'mchannel':
				$Table = empty($FieldConfig['iscommon']) ? 'members_'.self::$SourceID : 'members_sub';
				break;
			case 'fchannel':
				$Table = empty($FieldConfig['iscommon']) ? 'farchives_'.self::$SourceID : 'farchives';
				break;
			case 'catalog':
				$Table = 'catalogs';
				break;
			case 'cotype':
				$Table = 'coclass'.self::$SourceID;
				break;
			case 'commu':
				$Table = self::$SourceConfig['tbl'];
				break;
			case 'pusharea':
				$Table = cls_PushArea::ContentTable(self::$SourceID);
				break;
		}
		return $Table;
    }
	
	# 禁止字段标识为数据库保留关键词
    private static function _isDBKeepWord($ename){
		$sysparams = cls_cache::cacRead('sysparams');
		if(!empty($sysparams['keepwords']) && in_array($ename,$sysparams['keepwords'])) return true;
		return false;
    }
	# 字段标识的正则规则
    private static function _EnameRegular($SourceType){
		$Regular = '[^a-zA-Z_0-9]+|^[0-9_]+';
		if(self::_SourceVar($SourceType,'RegAdd')) $Regular .= self::_SourceVar($SourceType,'RegAdd');
		return "/$Regular/";
    }
	
    private static function _ViewOne(){
		if(!self::_PreDataType()){
			self::_ViewOneForm();
		}
    }
	# 预选字段类型，只有增加字段时需要使用
    private static function _PreDataType(){
		if(!empty(self::$oldField)) return false;
		$datatype = empty(self::$fmdata['datatype']) ? '' : self::$fmdata['datatype'];
		
		if(!$datatype){ # 尚未选择字段类型
			self::_FieldFormHeader(1);
			trbasic('字段类型','fmdata[datatype]',makeoption(self::datatype()),'select');
			if(self::$SourceType == 'channel'){
				trbasic('选择数据表','',makeradio('fmdata[iscommon]',array(1 => "文档主表(archives".self::$SourceConfig['stid'].")",0 => "模型表archives_".self::$SourceID)),'',array('guide' => '文档列表需要展示的字段通常放在主表，大字段或只需要在详情页面展示的字段放到模型表'));
			}
			tabfooter('bsubmit_datatype','继续');
			self::_FieldFormFooter();
			return true;
		}elseif(!submitcheck('bsubmit_cacc') && $datatype == 'cacc'){ # 如果是类目字段，先要选择类目字段的类系属性
			self::_FieldFormHeader(1);
			trbasic('字段类型','',self::datatype($datatype),'');
			trhidden('fmdata[datatype]',$datatype);
			if(self::$SourceType == 'channel'){//选择是否主表
				trbasic('选择数据表','',makeradio('fmdata[iscommon]',array(1 => "文档主表(archives".self::$SourceConfig['stid'].")",0 => "模型表archives_".self::$SourceID),empty(self::$fmdata['iscommon']) ? 0 : 1),'',array('guide' => '文档列表需要展示的字段通常放在主表，大字段或只需要在详情页面展示的字段放到模型表'));
			}
			$coidsarr = array('0' => '栏目');
			$cotypes = cls_cache::Read('cotypes');
			foreach($cotypes as $k => $v) if(empty($v['self_reg'])) $coidsarr[$k] = $v['cname'];
			trbasic('选项来源类系','fmdata[coid]',makeoption($coidsarr),'select');
			tabfooter('bsubmit_cacc','继续');
			self::_FieldFormFooter();
			return true;
		}
		return false;
	}
	# 表单之字段类型
    protected static function _fm_datatype(){
		trbasic('字段类型','',self::datatype(self::$datatype),'');
		if(self::$isNew) trhidden('fmdata[datatype]',self::$datatype);
	}
	
	# 表单之中文名称
    protected static function _fm_cname(){
		$Value = self::$isNew ? '' : self::$oldField['cname'];
		trbasic('中文标题','fmdata[cname]',$Value,'text',array('validate' => ' onfocus="initPinyin(\'fmdata[ename]\')"' . makesubmitstr('fmdata[cname]',1,0,0,30)));
	}
	
	# 表单之iscommon设置
    protected static function _fm_iscommon(){
		if(self::$isNew){
			if(self::$SourceType == 'channel'){//选择是否主表
				trhidden('fmdata[iscommon]',empty(self::$fmdata['iscommon']) ? 0 : 1);
			}elseif(self::$SourceType == 'mchannel'){//是否通用字段
				trhidden('fmdata[iscommon]',empty(self::$SourceID) ? 1 : 0);
			}
		}
	}
		
	# 表单之英文标识
    protected static function _fm_ename(){
		$cms_abs = cls_env::mconfig('cms_abs');
		if(self::$isNew){
			$na = array(
				'validate'=>' offset="1"' . makesubmitstr('fmdata[ename]',1,'tagtype',0,30),
				'guide' => '规定格式：头字符为字母，其它字符只能为"字母、数字、_"。',
				'addstr' => ' <input type="button" value="自动拼音" onclick="autoPinyin(\'fmdata[cname]\',\'fmdata[ename]\')" />',
				);
			trbasic('英文唯一标识','fmdata[ename]','','text',$na);
			$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC("ajax=check_fieldname&sourcetype=".self::$SourceType."&sourceid=".self::$SourceID."&fieldname=%1");
			echo _08_HTML::AjaxCheckInput('fmdata[ename]', $ajaxURL);
		}else{
			trbasic('英文唯一标识','',self::$oldField['ename'],'');
		}
	}
	# 表单之数库表字段长度
    protected static function _fm_length(){
	}
	# 表单之输入不能为空
    protected static function _fm_notnull(){
		$Value = self::$isNew ? 0 : self::$oldField['notnull'];
		trbasic('输入不能为空','fmdata[notnull]',$Value,'radio');
	}
	# 表单之清除内容中的Html代码
    protected static function _fm_nohtml(){
		$Value = self::$isNew ? 0 : self::$oldField['nohtml'];
		trbasic('清除内容中的Html代码','fmdata[nohtml]',$Value,'radio');
	}
	# 表单之提示说明
    protected static function _fm_guide(){
		$Value = self::$isNew ? '' : self::$oldField['guide'];
		trbasic('表单提示说明','fmdata[guide]',$Value,'text',array('guide'=>'换行请使用'.htmlspecialchars('<br>'),'w'=>50,'validate'=>makesubmitstr('fmdata[guide]',0,0,0,80)));
	}
	# 表单之正则检查
    protected static function _fm_regular(){
		$Value = self::$isNew ? '' : self::$oldField['regular'];
		trbasic('输入格式正则检查字串','fmdata[regular]',$Value);
	}
	# 表单之默认输入值
    protected static function _fm_vdefault(){
		$Value = self::$isNew ? '' : str_replace(",",'[##]',self::$oldField['vdefault']);
		trbasic('默认输入值','fmdata[vdefault]',$Value,'text',array('guide'=>'多个默认值以[##] (方括号中加##) 隔开','w'=>50));
	}	
	# 表单之搜索条件
    protected static function _fm_search(){
		if(in_array(self::$SourceType,array('channel','mchannel',)) && (self::$isNew || !empty(self::$oldField['iscustom']))){
			$issearcharr = array('0' => '不参与搜索','1' => '精确搜索','2' => '范围搜索');
			$Value = self::$isNew ? 0 : self::$oldField['issearch'];
			trbasic('可作搜索条件','',makeradio('fmdata[issearch]',$issearcharr,$Value),'');
		}
	}
	# 表单之远程下载方案
    protected static function _fm_rpid(){
		$rprojects = cls_cache::Read('rprojects');
		$rpidsarr = array('0' => '不下载远程附件');
		foreach($rprojects as $k => $v) $rpidsarr[$k] = $v['cname'];
		
		$Value = self::$isNew ? 0 : self::$oldField['rpid'];
		trbasic('远程下载方案','fmdata[rpid]',makeoption($rpidsarr,$Value),'select');
	}
	# 表单之图片加水印
    protected static function _fm_wmid(){
		$watermarks = cls_cache::Read('watermarks');
		$wmidsarr = array('0' => '图片不加水印');
		foreach($watermarks as $k => $v) $wmidsarr[$k] = $v['cname'];
		
		$Value = self::$isNew ? 0 : self::$oldField['wmid'];
		trbasic('图片加水印','fmdata[wmid]',makeoption($wmidsarr,$Value),'select');
	}
    
	/**
     * 表单之图片上传自动压缩
     * 
     * @since nv50
     **/ 
    protected static function _fm_autoCompression()
    {
		$maxWidht = (self::$isNew || !isset(self::$oldField['auto_compression_width'])) ? 0 : (int)self::$oldField['auto_compression_width'];
		trbasic('上传自动压缩尺寸','fmdata[auto_compression_width]',$maxWidht,'text',array('guide'=>'单位：px （默认0为不压缩，设置后图片上传时宽度或高度超出该值时会自动等比压缩成该大小）'));
	}
    
	# 表单之自定参数数组
    protected static function _fm_cfgs(){
		$Value = empty(self::$oldField['cfgs']) ? '' : var_export(self::$oldField['cfgs'],1);
		trbasic('自定配置参数','fmdata[cfgs]',$Value,'textarea',array('w' => 500,'h' => 100,'guide'=>'以array(\'xxx\' => \'yyy\',)格式输入，使用字段配置缓存$field[\'xxx\']可调用该设置'));
	}
	
	# 通用字段修改应用到已有模型
    protected static function _fm_common_to_other(){
		if(in_array(self::$SourceType,array('mchannel')) && !empty(self::$oldField['iscommon']) && empty(self::$SourceID)){
			$mchids = array();
			self::$db->select('tpid')->from(self::_Table())->where(array('type' => 'm'))->_and('tpid<>0')->_and(array('ename' => self::$oldField['ename']))->exec();
			while($r = self::$db->fetch()) $mchids[] = $r['tpid'];
			
			$mchidsarr = array();
			$mchannels = cls_mchannel::InitialInfoArray();
			foreach($mchannels as $k => $v){
				if(in_array($k,$mchids)) $mchidsarr[$k] = $v['cname'];
			}
			if($mchidsarr){
				trbasic('修改应用到已有模型', '', '<label for="all_mchids"><input class="checkbox" type="checkbox" id="all_mchids" onclick="checkall(this.form, \'fmdata[mchids]\', \'all_mchids\')">全选</label>&nbsp;  &nbsp;' . makecheckbox('fmdata[mchids][]', $mchidsarr), '', array('guide' => '只更新修改过的项目，未修改的项目保持原模型设置'));
			}
		}
	}
	# 表单中的分隔标题
    protected static function _fm_separator($title = ''){
		tabfooter();
		tabheader($title);
	}
	# 保留空方法，如果具体类型的类中未定义同名方法，则调用此方法
    protected static function _sv_custom_region(){
	}
	
	# 表单存储之通用部分的数据处理
    private static function _sv_PreCommon(){
		
		# 中文标题
		self::$newField['cname'] = trim(strip_tags(self::$fmdata['cname']));
		if(empty(self::$newField['cname'])) cls_message::show('请输入中文标题。',M_REFERER);
		
		# 英文标识
		if(self::$isNew){
			self::$newField['ename'] = self::InitID(self::$fmdata['ename']);
			if($re = self::CheckNewID(self::$SourceType,self::$SourceID,self::$newField['ename'])) cls_message::show($re,M_REFERER);
		}else{
			self::$newField['ename'] = self::$oldField['ename'];
		}
		
		# 字段类型
		self::$newField['datatype'] = self::$datatype;
		self::$newField['type'] = self::FieldType(self::$SourceType);
		self::$newField['tpid'] = self::$SourceID;
		
		# 内定不能编辑的属性
		self::$newField['issystem'] = empty(self::$oldField['issystem']) ? 0 : 1;  # 是否系统固定字段
		self::$newField['iscustom'] = self::$isNew || !empty(self::$oldField['iscustom']) ? 1 : 0; # 是否自定义字段
		
		# 是否通用字段/主表字段
		if(self::$isNew){
			self::$newField['iscommon'] = empty(self::$fmdata['iscommon']) ? 0 : 1; # 文档模型可以选择主表字段还是模型字段
		}else{
			self::$newField['iscommon'] = self::$oldField['iscommon'];
		}
		# 这个要在处理iscommon参数之后
		self::$newField['tbl'] = empty(self::$oldField['tbl']) ? self::_ContentTable(self::$newField) : self::$oldField['tbl'];
		# 提示说明
		self::$newField['guide'] = empty(self::$fmdata['guide']) ? '' : trim(self::$fmdata['guide']);
		
		# 自定参数数组
		self::$newField['cfgs'] = empty(self::$fmdata['cfgs']) ? '' : trim(self::$fmdata['cfgs']);
		self::$newField['cfgs'] = varexp2arr(self::$newField['cfgs']);
		
		# 其它补全参数:'vieworder','available','issystem','iscustom',
		self::$newField['vieworder'] = empty(self::$oldField['vieworder']) ? 0 : self::$oldField['vieworder'];
		self::$newField['available'] = self::$isNew || !empty(self::$oldField['available']) ? 1 : 0;
    }
	
	
	# 更新字段缓存
    protected static function _sv_finish(){
		adminlog((self::$isNew ? '添加' : '编辑').self::_SourceVar(self::$SourceType,'Name').'字段');
		cls_message::show('字段'.(self::$isNew ? '添加' : '编辑').'完成',axaction(6,self::_RouteUrl('onefinish')));
    }
	
	# 根据字段的新增或修改，对内容数据表做相应的变更
    protected static function _sv_content_table(){
		try{
			self::$newField = self::AlterContentTableByConfig(self::$newField,self::$isNew,self::$oldField);
			return true;
		}catch (Exception $e){
			cls_message::show($e->getMessage());
		}
	}
	
	# 一条新建记录的初始化数据
	private static function _OneBlankField(){
		$AfieldsColumns = self::_AfieldsColumns();
		$BlankInfo = array();
		foreach($AfieldsColumns as $var => $cfg){
			if(isset($cfg['Default'])){
				$BlankInfo[$var] = is_null($cfg['Default']) ? (preg_match("/int/i",$cfg['Type']) ? 0 : '') : $cfg['Default'];
			}else{
				$BlankInfo[$var] = preg_match("/int/i",$cfg['Type']) ? 0 : '';
			}
		}
		return $BlankInfo;
	}
	
	# 新增或存入一条字段配置到初始数据源
	# 注意：self::$newField需要是 (1)基本结构完整 (2)经过前期处理 (3)addslashes转义过的
	private static function _SaveOneConfig(){
		if(empty(self::$SourceConfig) || empty(self::$newField)) return; # 需要经过前期处理
		if(self::isTemplateConfig(self::$SourceType)){ # 存入模板缓存
		
			# 保持cfgs的数组结构
			if(isset(self::$newField['cfgs']) && empty(self::$newField['cfgs'])){
				self::$newField['cfgs'] = '';
			}
			
			# self::$newField默认为表单传入，addslashes转义过的，存文件时需要 去转义
			cls_Array::array_stripslashes(self::$newField);
					
			$CacheArray = self::InitialFieldArray(self::$SourceType,self::$SourceID);
			$CacheArray[self::$newField['ename']] = self::$newField;
			self::SaveInitialCache(self::$SourceType,self::$SourceID,$CacheArray);
			
		}else{ # 存入数据库
			
			# 特殊处理cfgs
			if(isset(self::$newField['cfgs']) && is_array(self::$newField['cfgs'])){
				if(is_array(self::$newField['cfgs'])){
					self::$newField['cfgs'] = empty(self::$newField['cfgs']) ? '' : addslashes(var_export(self::$newField['cfgs'],TRUE));
				}else{
					self::$newField['cfgs'] = empty(self::$newField['cfgs']) ? '' : addslashes(self::$newField['cfgs']);
				}
			}
		
			if(self::$isNew){
				self::$db->insert(self::_Table(),self::$newField)->exec();
			}else{
				# 更新当前字段记录
				self::$db->update(self::_Table(),self::$newField)
						 ->where(array('type' => self::$newField['type']))->_and(array('tpid' => self::$newField['tpid']))->_and(array('ename' => self::$newField['ename']))->exec();
			}
		}
	}
	# 从初始数据源删除一条字段配置记录
	# 对于输入参数不再进行检测及处理，在传入之前需要掌控完整与合法性。
	private static function _DelOneConfig($SourceType = 'channel',$SourceID = 0,$ename = ''){
		if(self::isTemplateConfig($SourceType)){
			$CacheArray = self::InitialFieldArray($SourceType,$SourceID);
			unset($CacheArray[$ename]);
			self::SaveInitialCache($SourceType,$SourceID,$CacheArray);
		}else{
			self::$db->delete(self::_Table())->where(array('type' => self::FieldType($SourceType)))->_and(array('tpid' => $SourceID))->_and(array('ename' => $ename))->exec();
		}
	}
	
	# 针对表单提交的单个字段编辑进行处理
	# 如果具体类型的类中未定义同名方法，则调用此方法
    protected static function _sv_field_config(){
		
		# 因为涉及到储存到缓存，尽可能补全数据结构，与数据表同名
		$AfieldsColumns = self::_AfieldsColumns();
		foreach($AfieldsColumns as $var => $cfg){
			if(!isset(self::$newField[$var])){ # 排除之前已经处理过的数据
				if(isset(self::$fmdata[$var])){//修改的值
					if(preg_match("/int/i",$cfg['Type'])){
						self::$newField[$var] = (int)self::$fmdata[$var];
					}else{
						self::$newField[$var] = trim(self::$fmdata[$var]);
					}
				}elseif(isset(self::$oldField[$var])){//未修改的参数保持原值
					self::$newField[$var] = maddslashes(self::$oldField[$var],true);
				}elseif(isset($cfg['Default'])){//以默认值补全
					self::$newField[$var] = is_null($cfg['Default']) ? (preg_match("/int/i",$cfg['Type']) ? 0 : '') : $cfg['Default'];
				}else{
					self::$newField[$var] = preg_match("/int/i",$cfg['Type']) ? 0 : '';
				}
			}
		}
		# 增加或修改当前字段的配置记录
		self::_SaveOneConfig();
		
		# 会员模型的通用字段的副本处理
		self::_ModifyCommonFieldCopy(@self::$fmdata['mchids']);
		
		# 更新当前载体的字段缓存
		self::UpdateCache(self::$SourceType,self::$SourceID);
	}
	
	# 通用字段的副本处理，目前只涉及会员模型的通用字段
	# $SourceIDs：修改时选择同步到哪些模型，新增时强行增加有效副本到所有模型
    protected static function _ModifyCommonFieldCopy($SourceIDs = array()){
		if(in_array(self::$SourceType,array('mchannel')) && empty(self::$SourceID)){
			$_field = self::$newField; # 需要copy的通用字段资料
			$mchannels = cls_mchannel::InitialInfoArray();
			
			if(self::$isNew){ # 会员模型的通用字段需要每个模型复制出一个afields字段记录
				foreach($mchannels as $k => $v){
					$_field['tpid'] = $k;
					self::$db->insert(self::_Table(),$_field)->exec();
					self::UpdateCache(self::$SourceType,$k);
				}
			}else{ # 会员模型的通用字段修改同步到已有模型的字段记录
				if(empty($SourceIDs) || !is_array($SourceIDs)) return; //表单中选择同步到哪些模型
				foreach(array('ename','type','tpid','vieworder','available',) as $var) unset($_field[$var]);
				foreach($mchannels as $k => $v){
					if(in_array($k,self::$fmdata['mchids'])){
						self::$db->update(self::_Table(),$_field)
								 ->where(array('type' => self::$newField['type']))->_and(array('tpid' => $k))->_and(array('ename' => self::$newField['ename']))->exec();
						self::UpdateCache(self::$SourceType,$k);
					}
				}
			}
		}
	}
	
	# 更新缓存(应用缓存或数据源)，按载体类型，模板内配置缓存传入内容数组$CacheArray
	protected static function _SaveCache($SourceType = 'channel',$SourceID = 0,$CacheArray = '',$isInit = false){
		if(self::isTemplateConfig($SourceType)){
			if(is_array($CacheArray)){ # 来自传入的配置数组
				cls_Array::_array_multisort($CacheArray);# 以vieworder重新排序
			}else{ # 来自完全数据源
				$CacheArray = self::InitialFieldArray($SourceType,$SourceID);
			}
			$CacheName = self::FieldCacheName($SourceType,$isInit); # 在这里区分了 应用缓存/完全数据源
		}else{
			if(!$isInit){
				$CacheArray = self::InitialFieldArray($SourceType,$SourceID);
				$CacheName = self::FieldCacheName($SourceType,false);
			}else{ # 数据库保存则不需要本地数据源
				return;
			}
		}
		
		# 注意这是完全数据源与应用缓存不同的地方
		if(empty($isInit)){
			foreach($CacheArray as $k => &$v){
				if(empty($v['available'])) unset($CacheArray[$k]);
				cls_CacheFile::ArrayAction($v,'cfgs','extract');
			}
		}
		
		cls_CacheFile::Save($CacheArray,$CacheName.$SourceID,$CacheName,$isInit);
	}
	
	# 提取载体类型的额外配置及命名规则
    protected static function _SourceVar($Type = 'channel',$Key = ''){
		$SourceTypes = array(//允许的载体类型
			'channel' => array(
				'ID' => 'chid', # 载体资料的ID变量名
				'Title' => 'cname', # 载体资料的标题变量名
				'Class' => 'cls_channel', # 具体载体操作方法的类名
				'Type' => 'a', # 在字段记录表中的类型type
				'Name' => '文档模型', # 类型命名
				'RegAdd' => '|^ccid(.*?)', # 添加字段标识时附加的正则规则
			),
			'mchannel' => array(
				'ID' => 'mchid',
				'Title' => 'cname',
				'Class' => 'cls_mchannel',
				'Type' => 'm',
				'Name' => '会员模型',
				'RegAdd' => '|^grouptype(.*?)|^currency(.*?)',
			),
			'fchannel' => array(
				'ID' => 'chid',
				'Title' => 'cname',
				'Class' => 'cls_fchannel',
				'Type' => 'f',
				'Name' => '副件模型',
			),
			'catalog' => array( # 注意，这里是作为coid=0的一个类系来定义的
				'ID' => 'caid',
				'Title' => 'title',
				'Class' => 'cls_catalog',
				'Type' => 'cn',
				'Name' => '栏目',
			),
			'cotype' => array(
				'ID' => 'coid',
				'Title' => 'cname',
				'Class' => 'cls_cotype',
				'Type' => 'cn',
				'Name' => '类系',
			),
			'commu' => array(
				'ID' => 'cuid',
				'Title' => 'cname',
				'Class' => 'cls_commu',
				'Type' => 'cu',
				'Name' => '交互项目',
			),
			'pusharea' => array(
				'ID' => 'paid',
				'Title' => 'cname',
				'Class' => 'cls_pusharea',
				'Type' => 'pa',
				'Name' => '推送位',
			),
		);
		$re = isset($SourceTypes[$Type]) ? $SourceTypes[$Type] : array();
		if($Key) $re = isset($re[$Key]) ? $re[$Key] : '';
		return $re;
	
	}
	
	# 对所属载体ID进行初始格式化
    protected static function SourceInitID($SourceType = 'channel',$SourceID = 0){
		$SourceID = trim($SourceID);
		if(in_array($SourceType,array('pusharea'))){
			$SourceID = cls_string::ParamFormat($SourceID);
		}else{
			$SourceID = (int)$SourceID;
		}
		return $SourceID;
	}
	
	# 得到afields的数据表字段结构(完整字段配置数组)
    protected static function _AfieldsColumns(){
		$Columns = cls_DbOther::ColumnNames(self::$Table,true);
		unset($Columns['fid']); # 排除自增量ID
		return $Columns;
	}
	
	
}
