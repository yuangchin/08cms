<?php
/* 
** 会员空间模板方案，是mtconfig.cls.php的基类
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_mtconfigbase{
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($ID = 0,$Key = ''){
		$re = cls_cache::Read(cls_mtconfig::CacheName());
		if($ID){
			$ID = cls_mtconfig::InitID($ID);
			$re = isset($re[$ID]) ? $re[$ID] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 取得指定会员(mid)所使用的空间方案(可以指定Key取值)
	# 如果mtcid已知，不要使用此方法
	public static function ConfigByMid($mid = 0,$Key = ''){
		global $db,$tblprefix;
		$re = array();
		if($mid = max(0,intval($mid))){
			if($mtcid = $db->result_one("SELECT mtcid FROM {$tblprefix}members WHERE mid='$mid'")){
				$re = cls_mtconfig::Config($mtcid);
			}
		}
		if($Key){
			$re = isset($re[$Key]) ? $re[$Key] : '';
		}
		return $re;
	}
	
	# 对ID进行初始格式化
    public static function InitID($ID = 0){
		return max(0,intval($ID));
	}
	
	# 缓存名称
    public static function CacheName(){
		return 'mtconfigs';
    }
	
	# 更新缓存，按字段缓存名，提供给cls_CacheFile使用
	public static function UpdateCache(){
		cls_mtconfig::SaveInitialCache();
	}
	
	# 更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($CacheArray = ''){
		if(!is_array($CacheArray)){ # 来自传入的配置数组
			$CacheArray = cls_mtconfig::InitialInfoArray();
		}
		cls_Array::_array_multisort($CacheArray,'vieworder',true);# 以vieworder重新排序 //ksort($CacheArray);# 重新排序
		cls_CacheFile::Save($CacheArray,cls_mtconfig::CacheName());
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$CacheArray = cls_cache::Read(cls_mtconfig::CacheName(),'','',1);
		return $CacheArray;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($ID){
		$ID = cls_mtconfig::InitID($ID);
		$CacheArray = cls_mtconfig::InitialInfoArray();
		return empty($CacheArray[$ID]) ? array() : $CacheArray[$ID];
	}
	
	# 新增或存入一条配置到初始数据源
	# 注意：$newConfig是预设为经过addslahes转义后的数组
	public static function ModifyOneConfig($newConfig = array(),$newID = 0){
		
		$newID = cls_mtconfig::InitID($newID);
		cls_Array::array_stripslashes($newConfig);
		
		if($newID){
			if(!($oldConfig = cls_mtconfig::InitialOneInfo($newID))){
				throw new Exception('请指定正确的空间模板方案ID。');
			}
			$nowID = $oldConfig['mtcid'];
		}else{
			$newConfig['cname'] = trim(strip_tags(@$newConfig['cname']));
			if(!$newConfig['cname']){
				throw new Exception('空间模板方案资料不完全。');
			}
			if(!($nowID = auto_insert_id('mtconfigs'))){
				throw new Exception('无法得到新增的空间模板方案ID。');
			}
			$oldConfig = cls_mtconfig::_OneBlankInfo($nowID);
		}
		
		# 格式化数据
		if(isset($newConfig['cname'])){
			$newConfig['cname'] = trim(strip_tags($newConfig['cname']));
			$newConfig['cname'] = $newConfig['cname'] ? $newConfig['cname'] : $oldConfig['cname'];
		}
		if(isset($newConfig['pmid'])){
			$newConfig['pmid'] = max(0,intval($newConfig['pmid']));
		}
		if(isset($newConfig['mchids'])){
			if(empty($newConfig['mchids'])){
				$newConfig['mchids'] = '';
			}elseif(is_array($newConfig['mchids'])){
				$newConfig['mchids'] = implode(',',array_filter($newConfig['mchids']));
			}
		}
		if(isset($newConfig['setting'])){
			$newConfig['setting'] = cls_mtconfig::_NewSetting($newConfig['setting']);
		}
		if(isset($newConfig['arctpls'])){
			$newConfig['arctpls'] = cls_mtconfig::_NewArctpls($newConfig['arctpls']);
		}
		
		# 赋值
		$InitConfig = cls_mtconfig::_OneBlankInfo($nowID); # 完全的配置结构
		foreach($InitConfig as $k => $v){
			if(in_array($k,array('mtcid','issystem',))) continue;
			if(isset($newConfig[$k])){ # 赋新值
				$oldConfig[$k] = $newConfig[$k];
			}elseif(!isset($oldConfig[$k])){ # 新补的字段
				$oldConfig[$k] = $v;
			}
		}	
		
		# 保存
		$CacheArray = cls_mtconfig::InitialInfoArray();
		$CacheArray[$nowID] = $oldConfig;
		cls_mtconfig::SaveInitialCache($CacheArray);
		
		return $nowID;
		
	}
	# 删除一条配置
	public static function DeleteOne($ID){
		$ID = cls_mtconfig::InitID($ID);
		if(!$ID || !($Info = cls_mtconfig::InitialOneInfo($ID))) return '请指定正确的空间模板方案。';
		if(!empty($Info['issystem'])) return '系统内置方案禁止删除。'; 
		
		$CacheArray = cls_mtconfig::InitialInfoArray();
		unset($CacheArray[$ID]);
		cls_mtconfig::SaveInitialCache($CacheArray);
	}
	
	# 格式化空间栏目的模板绑定设置
	protected static function _NewSetting($Config = array()){
		$newConfig = array();
		if(!empty($Config[0]['index'])){
			_08_FilesystemFile::filterFileParam($Config[0]['index']);
			$newConfig[0]['index'] = $Config[0]['index'];
		}
		$mcatalogs = cls_mcatalog::InitialInfoArray();
		foreach($mcatalogs as $k => $v){
			if(isset($Config[$k])){
				$newConfig[$k] = array();
				foreach(array('index','list',) as $var){
					if(!empty($Config[$k][$var])){
						_08_FilesystemFile::filterFileParam($Config[$k][$var]);
						$newConfig[$k][$var] = $Config[$k][$var];
					}
				}
			}
		}
		return $newConfig;
	}
	# 格式化空间文档内容页的模板绑定设置
	protected static function _NewArctpls($Config = array()){
		$newConfig = array();
		$channels = cls_channel::Config();
		foreach(array('archive','ex1','ex2',) as $var){
			$newConfig[$var] = array();
			foreach($channels as $k => $v){
				if(!empty($Config[$var][$k])){
					  _08_FilesystemFile::filterFileParam($Config[$var][$k]);
					  $newConfig[$var][$k] = $Config[$var][$k];
				}
			}
			if(empty($newConfig[$var])) unset($newConfig[$var]);
		}
		return $newConfig;
	}
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
			'mtcid' => cls_mtconfig::InitID($ID),
			'cname' => '标题',
			'issystem' => 0,
			'mchids' => '',
			'pmid' => 0,
			'vieworder' => '0',
			'setting' => array (),
			'arctpls' => array (),
		);
	}
	
}
