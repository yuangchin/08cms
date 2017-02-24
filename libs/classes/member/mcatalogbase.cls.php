<?php
/* 
** 会员空间的栏目管理，是mcatalog.cls.php的基类
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_mcatalogbase{
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($ID = 0,$Key = ''){
		$re = cls_cache::Read(cls_mcatalog::CacheName());
		if($ID){
			$ID = cls_mcatalog::InitID($ID);
			$re = isset($re[$ID]) ? $re[$ID] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 取得 ID => 标题 的资料列表数组
	# $mctid指定的空间方案ID，-1为全部栏目，0为未设置空间栏目
	# $AllowUclass=1时，只列出允许添加个人分类的空间栏目
	public static function mcaidsarr($mctid = -1,$AllowUclass = 0){
		
		$narr = array();
		$mcatalogs = cls_mcatalog::Config();
		foreach($mcatalogs as $k => $v){
			if($AllowUclass && empty($v['maxucid'])) continue;
			$narr[$k] = "($k)".$v['title'];
		}
		if($mctid != -1){
			if(!$mctid || !($_msTpls = cls_mtconfig::Config($mctid,'setting'))) return array(); # 指定的空间方案不存在
			$narr = array_intersect_key($narr,$_msTpls);
		}
		return $narr;
	}
	
	
	# 对ID进行初始格式化
    public static function InitID($ID = 0){
		return max(0,intval($ID));
	}
	
	# 缓存名称
    public static function CacheName(){
		return 'mcatalogs';
    }
	
	# 更新缓存，按字段缓存名，提供给cls_CacheFile使用
	public static function UpdateCache(){
		cls_mcatalog::SaveInitialCache();
	}
	
	# 更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($CacheArray = ''){
		if(!is_array($CacheArray)){ # 来自传入的配置数组
			$CacheArray = cls_mcatalog::InitialInfoArray();
		}
		cls_Array::_array_multisort($CacheArray,'vieworder',true);# 以vieworder重新排序
		cls_CacheFile::Save($CacheArray,cls_mcatalog::CacheName());
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$CacheArray = cls_cache::Read(cls_mcatalog::CacheName(),'','',1);
		return $CacheArray;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($ID){
		$ID = cls_mcatalog::InitID($ID);
		$CacheArray = cls_mcatalog::InitialInfoArray();
		return empty($CacheArray[$ID]) ? array() : $CacheArray[$ID];
	}
	
	# 新增或存入一条配置到初始数据源
	# 注意：$newConfig是预设为经过addslahes转义后的数组
	public static function ModifyOneConfig($newConfig = array(),$newID = 0){
		
		$newID = cls_mcatalog::InitID($newID);
		cls_Array::array_stripslashes($newConfig);
		
		if($newID){
			if(!($oldConfig = cls_mcatalog::InitialOneInfo($newID))){
				throw new Exception('请指定正确的空间栏目ID。');
			}
			$nowID = $oldConfig['mcaid'];
		}else{
			$newConfig['title'] = trim(strip_tags(@$newConfig['title']));
			if(!$newConfig['title']){
				throw new Exception('空间栏目资料不完全。');
			}
			if(!($nowID = auto_insert_id('mcatalogs'))){
				throw new Exception('无法得到新增的空间栏目ID。');
			}
			$oldConfig = cls_mcatalog::_OneBlankInfo($nowID);
		}
		
		# 格式化数据
		if(isset($newConfig['title'])){
			$newConfig['title'] = trim(strip_tags($newConfig['title']));
			$newConfig['title'] = $newConfig['title'] ? $newConfig['title'] : $oldConfig['title'];
		}
		if(isset($newConfig['maxucid'])){
			$newConfig['maxucid'] = max(0,intval($newConfig['maxucid']));
		}
		if(isset($newConfig['remark'])){
			$newConfig['remark'] = trim(strip_tags($newConfig['remark']));
		}
		if(isset($newConfig['dirname'])){
			$newConfig['dirname'] = cls_mcatalog::_NewDirname($newConfig['dirname'],$newID);
		}
		
		# 赋值
		$InitConfig = cls_mcatalog::_OneBlankInfo($nowID); # 完全的配置结构
		foreach($InitConfig as $k => $v){
			if(in_array($k,array('mcaid'))) continue;
			if(isset($newConfig[$k])){ # 赋新值
				$oldConfig[$k] = $newConfig[$k];
			}elseif(!isset($oldConfig[$k])){ # 新补的字段
				$oldConfig[$k] = $v;
			}
		}	
		
		# 保存
		$CacheArray = cls_mcatalog::InitialInfoArray();
		$CacheArray[$nowID] = $oldConfig;
		cls_mcatalog::SaveInitialCache($CacheArray);
		
		return $nowID;
		
	}
	
	# 删除一条配置
	public static function DeleteOne($ID){
		$ID = cls_mcatalog::InitID($ID);
		if(!$ID || !($Info = cls_mcatalog::InitialOneInfo($ID))) return '请指定正确的空间栏目。';
		
		# 更新个人分类
		$db = _08_factory::getDBO();
		$db->update('#__uclasses',array('mcaid' => 0))->where(array('mcaid' => $ID))->exec();
		
		$CacheArray = cls_mcatalog::InitialInfoArray();
		unset($CacheArray[$ID]);
		cls_mcatalog::SaveInitialCache($CacheArray);
	}
	# 取得合法的空间栏目静态目录
	# newID=0：为新建的空间栏目取得静态目录
	protected static function _NewDirname($Dirname = '',$newID = 0){
		_08_FilesystemFile::filterFileParam($Dirname);
		$Dirname = strtolower($Dirname);
		if(!$Dirname) return '';
		
		$DirnameArray = cls_mcatalog::_DirnameArray();
		$CacheArray = cls_mcatalog::InitialInfoArray();
		foreach($CacheArray as $k => $v){
			if(empty($v['dirname'])) continue;
			if($Dirname == $v['dirname']){
				if($newID == $k){ # 本栏目名称未变
					continue;
				}else{ # 被其它栏目占用
					while(in_array($Dirname,$DirnameArray)) $Dirname .= 'a';
				}
			}
		}
		return $Dirname;
	}
	# 取得所有的空间栏目静态目录
	protected static function _DirnameArray(){
		$CacheArray = cls_mcatalog::InitialInfoArray();
		$re = array();
		foreach($CacheArray as $k => $v){
			if(!empty($v['dirname'])){
				$re[] = strtolower($v['dirname']);
			}
		}
		return $re;
	}
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
			'mcaid' => cls_mcatalog::InitID($ID),
			'title' => '标题',
			'maxucid' => '0',
			'vieworder' => '0',
			'remark' => '',
			'dirname' => '',
		);
	}
	
}
