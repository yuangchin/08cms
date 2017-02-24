<?php
/* 
** 推送位的方法汇总
** 模板配置缓存有两种：应用缓存(pushareas.cac.php)，对应初始完全数据源( _pushareas.cac.php)
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_PushAreaBase{
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($paid = '',$Key = ''){
		$re = cls_cache::Read(cls_PushArea::CacheName());
		if($paid){
			$paid = cls_PushArea::InitID($paid);
			$re = isset($re[$paid]) ? $re[$paid] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 读取字段配置，从应用缓存中读取
    public static function Field($paid = '',$FieldName = ''){
		$re = array();
		if(cls_PushArea::Config($paid)){
			$re = cls_cache::Read('pafields',$paid);
			if($FieldName){
				$re = isset($re[$FieldName]) ? $re[$FieldName] : array();
			}
		}
		return $re;
    }
	
	# 缓存名称
    public static function CacheName($isInit = false){
		return ($isInit ? '_' : '').'pushareas';
    }
	
	# 关联内容表的表名
    public static function ContentTable($paid = 0){
		$paid = cls_PushArea::InitID($paid);
		return $paid ? $paid : '';
    }
	
	# 更新应用缓存
	public static function UpdateCache(){
		cls_PushArea::_SaveCache();
	}
	
	# 更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($CacheArray = ''){
		cls_PushArea::_SaveCache($CacheArray,true);
	}
	
	# 对ID进行初始格式化
    public static function InitID($paid = ''){
		$paid = empty($paid) ? '' : trim(strtolower($paid));
		return cls_string::ParamFormat($paid);
	}
	
	# 检查新定义的paid是否合法
	public static function CheckNewID($paid = ''){
		if(!($paid = cls_PushArea::InitID($paid))) return '唯一标识不能为空';
		if(!preg_match("/push_/i",$paid)) return '请以push_为前缀';
		if(cls_PushArea::InitialOneInfo($paid)) return '指定的唯一标识被占用';
		return '';
	}
	
	# 检查内容表是否正常
	public static function CheckTable($paid = ''){
		if(!cls_PushArea::InitialOneInfo($paid)) return '未指定推送位';
		$ContentTable = cls_PushArea::ContentTable($paid);
		if(!($ColumnArray = @cls_DbOther::ColumnNames($ContentTable))) return "内容表{$ContentTable}不存在";
		if(!($Fields = cls_fieldconfig::InitialFieldArray('pusharea',$paid))) return "推送位未配置字段";
		$iColumnArray = cls_DbOther::ColumnNames('init_push');
		$iColumnArray = array_unique(array_merge($iColumnArray,array_keys($Fields)));
		if($diff = array_diff($iColumnArray,$ColumnArray)){
			return "缺少以下字段：".implode(',',$diff);
		}
		return '';
	}
	
	# 修复内容表(特别针对手动在缓存中添加推送位或字段配置)
	public static function RepairTable($paid = ''){
		if(!($PushArea = cls_PushArea::InitialOneInfo($paid))) return false;
		$_RepairOK = true;
		$ContentTable = cls_PushArea::ContentTable($paid);
		if(!($ColumnArray = @cls_DbOther::ColumnNames($ContentTable))){
			# 新建内容初始表
			cls_PushArea::_AddContentTable($paid,$PushArea['cname'].'推送位表');
		}else{ # 补全初始表的字段
			$iColumnArray = cls_DbOther::ColumnNames('init_push',true);
			$db = _08_factory::getDBO();
			foreach($iColumnArray as $k => $v){
				if(!in_array($k,$ColumnArray)){
					$v['Field'] = 'Add '.$v['Field'];
					$db->alterTable('#__'.$ContentTable,$v);
					$ColumnArray[] = $k;
				}
			}
		}
		# 补全字段配置中的字段
		$Fields = cls_fieldconfig::InitialFieldArray('pusharea',$paid);
		foreach($Fields as $k => $v){
		  	if(!in_array($k,$ColumnArray)){
				try{
					cls_fieldconfig::AlterContentTableByConfig($v,true);
				}catch (Exception $e){
					$_RepairOK = false;
					continue;
				}
		  	}
		}
		return $_RepairOK;
	}
	
	
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	# 做了以分类排序及指定分类的处理，$ptid=-1按原始来源输出，$ptid=0时，以所属分类进行排序
	public static function InitialInfoArray($ptid = -1){
		$ptid = (int)$ptid;
		$CacheArray = cls_cache::Read(cls_PushArea::CacheName(true),'','',1);
		$re = array();
		if($ptid == -1){ # 按原始来源
			$re = $CacheArray;
		}elseif(!$ptid){ # ptid = 0 以所属分类进行排序
			$pushtypes = cls_pushtype::InitialInfoArray();
			foreach($pushtypes as $k => $v){
				foreach($CacheArray as $x => $y){
					if($y['ptid'] == $k) $re[$x] = $y;
				}
			}
		}else{ # 指定分类
			foreach($CacheArray as $x => $y){
				if($y['ptid'] == $ptid) $re[$x] = $y;
			}
		}
		return $re;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($id){
		$id = cls_PushArea::InitID($id);
		$CacheArray = cls_PushArea::InitialInfoArray();
		return empty($CacheArray[$id]) ? array() : $CacheArray[$id];
	}
	
	
	# 新增或修改一条配置，同时处理新增字段及关联的内容表
	# 这里不包含sourcefields的配置修改，只是保持原值
	# 注意：$newConfig是addslashes之后的数组
	public static function ModifyOneConfig($nowID,$newConfig = array(),$isNew = false){
		
		$nowID = cls_PushArea::InitID($nowID);
		if(empty($newConfig)) return;
		
		# 仅处理配置记录
		cls_Array::array_stripslashes($newConfig);
		$nowID = cls_PushArea::_SaveOneConfig($nowID,$newConfig,$isNew);
		
		# 新建记录的字段及关联内容表的处理
		if($isNew){
			# 新增推送位时，对推送分类字段进行特别处理
			$ContentTableParams = array();
			if(isset($newConfig['cname'])){
				$ContentTableParams['cname'] = trim(strip_tags($newConfig['cname']));
			}
			for($k = 1;$k <= 2;$k ++){
				if(isset($newConfig["classoption$k"])){
					$ContentTableParams["classoption$k"] = $newConfig["classoption$k"];
					unset($newConfig["classoption$k"]);
				}
			}
			cls_PushArea::_AddContentTable_Fields($nowID,$ContentTableParams);
		}
		
		# 更新配置应用缓存
		cls_PushArea::UpdateCache();
		
		return $nowID;
	}
	
	# 复制一个推送位
	# 注意：$newConfig是addslashes之后的数组
	public static function CopyOneConfig($fromID,$toID,$newConfig = array()){
		
		if(!($fromCfg = cls_pusharea::InitialOneInfo($fromID))){
			throw new Exception('请指定正确的推荐位。');
		}
		cls_Array::array_stripslashes($newConfig);
		$newConfig = array_merge($fromCfg,$newConfig);
		
		# 处理配置记录
		$nowID = cls_PushArea::_SaveOneConfig($toID,$newConfig,true);
		
		# 处理字段记录
		try {
			cls_fieldconfig::CopyOneSourceFields('pusharea',$fromID,$nowID);
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}
		
		# 修复内容表
		cls_PushArea::RepairTable($nowID);
		
		# 更新配置应用缓存
		cls_PushArea::UpdateCache();
		
		return $nowID;
	}
	
	public static function DeleteOne($paid,$force = false){
		global $tblprefix;
		$paid = cls_PushArea::InitID($paid);
		if(!($pusharea = cls_PushArea::InitialOneInfo($paid))) return '请指定正确的推送位';
		
		$db = _08_factory::getDBO();
		if(!$force && $db->result_one("SELECT COUNT(*) FROM {$tblprefix}.".cls_PushArea::ContentTable($paid),0,'SILENT')){
			return '推送位中没有关联的推送信息才能删除';
		}
		
		cls_fieldconfig::DeleteOneSourceFields('pusharea',$paid);
		$db->query("DROP TABLE IF EXISTS {$tblprefix}".cls_PushArea::ContentTable($paid),'SILENT');
		
		# 保存
		$CacheArray = cls_PushArea::InitialInfoArray();
		unset($CacheArray[$paid]);
		cls_PushArea::SaveInitialCache($CacheArray);
		
		# 更新配置应用缓存
		cls_PushArea::UpdateCache();
	}
	
	# 增加一个新的内容关联表
	protected static function _AddContentTable($newID = 0,$Comment = '推送位表'){
		global $db,$tblprefix;
		$newID = cls_PushArea::InitID($newID);
		if(!$newID) return false;
		$db->query("CREATE TABLE {$tblprefix}".cls_PushArea::ContentTable($newID)." LIKE {$tblprefix}init_push");
		$db->query("ALTER TABLE {$tblprefix}".cls_PushArea::ContentTable($newID)." COMMENT='".$Comment."'");
	
	
	}
	# 为新配置增加内容关联表及字段配置
	protected static function _AddContentTable_Fields($newID = 0,$AddParams = array()){
		$newID = cls_PushArea::InitID($newID);
		if(!$newID) return false;
		
		# 增加内容表
		cls_PushArea::_AddContentTable($newID,@$AddParams['cname'].'推送位表');
		
		# 补上字段配置记录
		$initfields = array (
		  'subject' => 
		  array (
			'datatype' => 'text',
			'cname' => '标题',
			'issystem' => '1',
			'length' => '100',
			'nohtml' => '1',
			'notnull' => '1',
			'mode' => '1',
		  ),
		  'url' => 
		  array (
			'datatype' => 'text',
			'cname' => 'URL',
			'length' => '255',
			'nohtml' => '1',
			'notnull' => '1',
			'mode' => '1',
		  ),
		  'thumb' => 
		  array (
			'datatype' => 'image',
			'cname' => '缩略图',
			'nohtml' => '1',
		  ),
		);
		for($k = 1;$k <= 2;$k ++){
			$_field = array(
				'cname' => "推送分类$k",
			);
			if(empty($AddParams["classoption$k"])){
				$_field['datatype'] = 'select';
			}else{
				$_field['datatype'] = 'cacc';
				$_field['coid'] = $AddParams["classoption$k"] < 0 ? 0 : intval($AddParams["classoption$k"]);
			}
			$initfields["classid$k"] = $_field;
		}
		$i = 0;
		foreach($initfields as $k => $v){
			$v['ename'] = $k;
			$v['type'] = 'pa';
			$v['tpid'] = $newID;
			$v['iscommon'] = 1;
			$v['vieworder'] = ++$i;
			$v['tbl'] = cls_PushArea::ContentTable($newID);
			cls_fieldconfig::ModifyOneConfig('pusharea',$newID,$v);
		}
	}	
	
	
	# 仅新增或存入一条配置记录到初始数据源，不影响字段记录及关联内容表
	protected static function _SaveOneConfig($nowID,$newConfig = array(),$isNew = false){
		
		# 预检测数据
		$nowID = cls_PushArea::InitID($nowID);
		if(!$isNew){
			if(!($oldConfig = cls_PushArea::InitialOneInfo($nowID))) cls_message::show('请指定正确的推送位。');
			$nowID = $oldConfig['paid'];
		}else{
			if($re = cls_PushArea::CheckNewID($nowID)) cls_message::show($re);
			$newConfig['cname'] = trim(strip_tags(@$newConfig['cname']));
			if(!$newConfig['cname']) cls_message::show('请输入推送位名称');
			if(empty($newConfig['sourcetype']) || !cls_PushArea::SourceType($newConfig['sourcetype'])) cls_message::show('请输入推送来源。');
			if(!($newConfig['ptid'] = max(0,intval(@$newConfig['ptid'])))) cls_message::show('请选择分类');
			if(!cls_pushtype::InitialOneInfo($newConfig['ptid'])) cls_message::show('指定的推送分类不存在');
			$oldConfig = cls_PushArea::_OneBlankInfo($nowID);
		}
		
		# 格式化数据
		if(isset($newConfig['cname'])){
			$newConfig['cname'] = trim(strip_tags($newConfig['cname']));
			$newConfig['cname'] = $newConfig['cname'] ? $newConfig['cname'] : $oldConfig['title'];
		}
		if(isset($newConfig['ptid'])){
			$newConfig['ptid'] = max(0,intval($newConfig['ptid']));
		}
		if(isset($newConfig['maxorderno'])){
			$newConfig['maxorderno'] = min(99,max(2,intval($newConfig['maxorderno'])));
		}
		if(isset($newConfig['orderspace'])){
			$newConfig['orderspace'] = min(3,max(0,intval($newConfig['orderspace'])));
		}
		if(isset($newConfig['copyspace'])){
			$newConfig['copyspace'] = min(3,max(0,intval($newConfig['copyspace'])));
		}
		if(isset($newConfig['smallson'])){
			$newConfig['smallson'] = empty($newConfig['smallson']) ? 0 : 1;
		}
		if(isset($newConfig['sourceadv'])){
			$newConfig['sourceadv'] = empty($newConfig['sourceadv']) ? 0 : 1;
		}
		if(isset($newConfig['sourcefields'])){
			if(empty($newConfig['sourcefields']) || !is_array($newConfig['sourcefields'])){
				$newConfig['sourcefields'] = array();
			}
		}
		foreach(array('smallids','sourcesql','script_admin','script_detail','script_load',) as $var){
			if(isset($newConfig[$var])){
				$newConfig[$var] = empty($newConfig[$var]) ? '' : trim($newConfig[$var]);
			}
		}
		
		# 赋值
		$InitConfig = cls_PushArea::_OneBlankInfo($nowID); # 完全的配置结构
		foreach($InitConfig as $k => $v){
			if(in_array($k,array('paid'))) continue;
			if(isset($newConfig[$k])){ # 赋新值
				$oldConfig[$k] = $newConfig[$k];
			}elseif(!isset($oldConfig[$k])){ # 新补的字段
				$oldConfig[$k] = $v;
			}
		}		
		
		# 保存
		$CacheArray = cls_PushArea::InitialInfoArray();
		$CacheArray[$nowID] = $oldConfig;
		cls_PushArea::SaveInitialCache($CacheArray);
		
		return $nowID;
		
	}

	# 得到字段来源的设置列表及其注释	
	public static function SourceFieldArray($type,$typeid,$onlysql = 0){
		$re = array();
		switch($type){
			case 'archives'://typeid为模型chid
				$tbls = array(atbl($typeid));
				if(!$onlysql){
					$tbls[] = "archives_$typeid";
					$arc_tpl = cls_tpl::arc_tpl($typeid);
					for($i = 0;$i <= @$arc_tpl['addnum'];$i ++){
						$key = 'arcurl'.($i ? $i : '');
						$re['{'.$key.'}'] = $key.' - 内容页'.($i ? "附$i" : '').'url';
					}
					$re['{marcurl}'] = 'marcurl - 会员空间内容页url';
				}
			break;
			case 'members':
				$tbls = array("members");
				if(!$onlysql){
					$tbls[] = "members_$typeid";
					$tbls[] = "members_sub";
				}
				$re['{mspacehome}'] = 'mspacehome - 会员空间url';
			break;
			case 'commus':
				$tbls = array(cls_commu::Config($typeid,'tbl'));
			break;
			case 'catalogs':
				$tbls = $typeid ? array("coclass$typeid") : array('catalogs');
				$cnstr = $typeid ? "ccid$typeid={ccid}" : 'caid={caid}';
				$re["[cnode::$cnstr::0::0]"] = "类目节点 - [cnode::属性字串::附加页::手机版]";
				$re["[mcnode::$cnstr::0]"] = '会员频道节点 - [mcnode::属性字串::附加页]';
			break;
		}
		
		$dbfields = cls_cache::Read('dbfields');
		
		foreach($tbls as $key => $tbl){
			$na = cls_DbOther::ColumnNames($tbl);
			$tbltype = '';
			if($key == 1){
				$tbltype = ' - 模型表';
			}elseif($key == 2){
				$tbltype = ' - 会员副表';
			}
			foreach($na as $k){
				if(!isset($re[$k])){
					$dtbl = $tbl;
					if($type == 'archives' && !in_str('_',$tbl)){
						$dtbl = "archives";
					}elseif($type == 'catalogs'){
						$dtbl = $typeid ? 'coclass' : 'catalogs';
					}
					$re['{'.$k.'}'] = $k.(empty($dbfields[$dtbl.'_'.$k]) ? '' : ' - '.$dbfields[$dtbl.'_'.$k]).$tbltype;
				}
			}
		}
		return $re;
	}
	
	# 日期字段-用于：到期日期来源字段 的设置
	public static function DateFieldArray($type,$chid,$onlysql = 0){
		$re = array(''=>'-请选择字段-'); //$sfields = array('' => '插入内容来源字段') + 
		if(!in_array($type,array('archives','members'))) return $re; 
		if($type=='members'){
			$fields = cls_cache::Read('mfields',$chid);
			$ugidarr = array();
			$grouptypes = cls_cache::Read('grouptypes');
			foreach($grouptypes as $gk=>$gv){
				if($gk<=2) continue; echo "";
				if(!empty($gv['mchids']) && strstr(",{$gv['mchids']},",",$chid,")) continue; // 排除【在以下模型中禁止使用】的设置：'mchids' => ',1,3,11,12,13,14,15', 
				$re["grouptype{$gk}date"] = '会员组体系['.$gv['cname']."]到期时间(grouptype{$gk}date)"; 
			}
		}else{
			$fields = cls_cache::Read('fields',$chid);
			$re['enddate'] = '内置[到期时间](enddate)';
		}
		foreach($fields as $k=>$v){
			if($v['datatype']=='date'){
				$re[$k] = '架构['.$v['cname']."]($k)";
			}		
		}
		return $re;
	}
	
	public static function SourceType($Type = '',$Key = ''){
		$SourceTypeArray = array(
			'archives' => array(
				'title' => '文档',
				'cache' => 'channels',
			),
			'members' => array(
				'title' => '会员',
				'cache' => 'mchannels',
			),
			'catalogs' => array(
				'title' => '分类',
				'cache' => 'cotypes',
			),
			'commus' => array(
				'title' => '交互',
				'cache' => 'commus',
			),
		);
		$re = $SourceTypeArray;
		if($Type){
			$re = isset($re[$Type]) ? $re[$Type] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
	}
	
	public static function SourceIDArray(){
		$re = array();
		$SourceTypeArray = cls_PushArea::SourceType();
		foreach($SourceTypeArray as $k => $v){
			$na = cls_cache::Read($v['cache']);
			if($k == 'catalogs') $na = array(0 => array('cname' => '栏目','self_reg' => 0)) + $na;
			foreach($na as $x => $y){
				if($k == 'catalogs' && $y['self_reg']) continue;
				$re[$k.'_'.$x] = "{$v['title']}_{$x}_{$y['cname']}";
			}
		}
		return $re;
	}
	
	public static function SourceIDTitle($type,$typeid){
		static $sarr;
		$sarr || $sarr = cls_PushArea::SourceIDArray();
		return empty($sarr[$type.'_'.$typeid]) ? '-' : $sarr[$type.'_'.$typeid];
	}
	
	# 管理后台的左侧展开菜单的显示
	public static function BackMenuCode(){
		$pushtypes = cls_cache::Read('pushtypes');
		$pushareas = cls_PushArea::Config();
		$curuser = cls_UserMain::CurUser();
		$na = array();
		if(!$curuser->NoBackFunc('pusharea')){
			$na['_pusharea'] = array('title' => '推送位架构','level' => 0,'active' => 1,);
		}
		$na['_all'] = array('title' => '全部推送位','level' => 0,'active' => 1,);
		foreach($pushtypes as $k => $v){
			$na["_$k"] = array('title' => $v['title'],'level' => 0,'active' => 0,);
			$i = 0;$n = false;
			foreach($pushareas as $x => $y){
				if($y['ptid'] == $k){
					$na[$x] = array('title' => $y['cname'],'level' => 1,'active' => 1,);
					if(cls_pusher::HaveNewToday($x)){
						$na[$x]['title'] = "<font color='#FF0000'>{$y['cname']}</font>";
						$n = true;
					}
					$i ++;
				}
			}
			if(!$i){
				unset($na["_$k"]);
			}elseif($n){
				$na["_$k"]['title'] = "<font color='#FF0000'>{$v['title']}</font>";
			}
		}
		return ViewBackMenu($na,3);
	}
	
	# 管理后台的左侧单个分类的管理节点展示(ajax请求)
	public static function BackMenuBlock($paid = 0){
		$UrlsArray = cls_PushArea::BackMenuBlockUrls($paid);
		return _08_M_Ajax_Block_Base::getInstance()->OneBackMenuBlock($UrlsArray);
	}
	
	# 管理后台的左侧单个分类的管理节点url数组，可以根据需要在应用系统进行扩展
	protected static function BackMenuBlockUrls($paid){
		$UrlsArray = array();
		$paid = cls_PushArea::InitID($paid);
		if($paid == '_pusharea'){
			$UrlsArray['推送位管理'] = "?entry=pushareas";
			$UrlsArray['推送位分类'] = "?entry=pushtypes";
			$UrlsArray['推送位修复'] = "?entry=pushareas&action=repair";
		}elseif($paid == '_all'){
			$UrlsArray['更新排序'] = "?entry=extend&extend=push_order_all";
			$UrlsArray['同步来源'] = "?entry=extend&extend=push_refresh_all";
		}elseif($pusharea = cls_PushArea::Config($paid)){
			$UrlsArray['推送管理'] = "?entry=extend&extend=pushs&paid=$paid";
			if(in_array($pusharea['sourcetype'],array('archives','members',))){
				$UrlsArray['加载信息'] = "?entry=extend&extend=push_load&paid=$paid";
			}
			$UrlsArray['更新排序'] = "?entry=extend&extend=push_order&paid=$paid";
			$UrlsArray['同步来源'] = "?entry=extend&extend=push_refresh&paid=$paid";
		}
		return $UrlsArray;
	}
	
	# 指定应用缓存/完全数据源的方式更新缓存
	protected static function _SaveCache($CacheArray = '',$isInit = false){
		if(!is_array($CacheArray)){ # 来自传入的配置数组
			$CacheArray = cls_PushArea::InitialInfoArray();
		}
		if(!$isInit){
			foreach($CacheArray as $k => $v){
				if(empty($v['available'])) unset($CacheArray[$k]);
			}
		}
		cls_Array::_array_multisort($CacheArray,'vieworder',true);# 以vieworder重新排序
		cls_CacheFile::Save($CacheArray,cls_PushArea::CacheName($isInit),'',$isInit);
		
	}
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
			'paid' => cls_PushArea::InitID($ID),
			'cname' => '',
			'ptid' => 0,
			'sourcetype' => '',
			'sourceid' => 0,
			'smallids' => '',
			'smallson' => 0,
			'sourcesql' => '',
			'sourcefields' => array(),
			'sourceadv' => 0,
			'vieworder' => 0,
			'autopush' => 0, //自动推送
			'enddate_from' => '', //到期日期来源字段,文档会员
			'forbid_useradd' => 0, //禁止手动添加
			'available' => 1,
			'apmid' => 0,
			'autocheck' => 1,
			'maxorderno' => 10,
			'mspace' => 0,
			'orderspace' => 0,
			'copyspace' => 0,
			'script_admin' => '',
			'script_detail' => '',
			'script_load' => '',
		);
	}
	
}
