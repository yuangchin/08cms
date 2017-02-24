<?php
/* 
** 独立页面的方法汇总
** 配置储存于模板目录，应用缓存与数据源是同一个,数据源读取本地文件，不从扩展缓存(memcached)中读取。
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）。
*/
!defined('M_COM') && exit('No Permission');
class cls_FreeInfobase{
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($ID = 0,$Key = ''){
		$re = cls_cache::Read(cls_FreeInfo::CacheName());
		if($ID){
			$ID = cls_FreeInfo::InitID($ID);
			$re = isset($re[$ID]) ? $re[$ID] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	# 缓存名称
    public static function CacheName(){
		return 'freeinfos';
    }
	
	# 对ID进行初始格式化
    public static function InitID($ID = 0){
		return max(0,intval($ID));
	}
	# 更新缓存，按字段缓存名，提供给cls_CacheFile使用
	public static function UpdateCache(){
		cls_FreeInfo::SaveInitialCache();
	}
	
	# 独立页面的Url
	function Url($fid = 0){
		$fid = cls_FreeInfo::InitID($fid);
		if(empty($fid) || !($FreeInfo = cls_FreeInfo::Config($fid))) return '#';
        $mconfigs = cls_cache::Read('mconfigs');
        if (!empty($mconfigs['virtualurl']) && !empty($mconfigs['rewritephp']))
        {
            $pageUrl = 'info' . $mconfigs['rewritephp'] . "fid-$fid.html";
        }
        else
        {
        	$pageUrl = "info.php?fid=$fid";
        }
		return cls_url::view_url(empty($FreeInfo['arcurl']) ? $pageUrl : $FreeInfo['arcurl']);
	}
	
	# 更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($CacheArray = ''){
		if(!is_array($CacheArray)){ # 来自传入的配置数组
			$CacheArray = cls_FreeInfo::InitialInfoArray();
		}
		ksort($CacheArray);# 重新排序
		cls_CacheFile::Save($CacheArray,cls_FreeInfo::CacheName());
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$CacheArray = cls_cache::Read(cls_FreeInfo::CacheName(),'','',1);
		return $CacheArray;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($ID){
		$ID = cls_FreeInfo::InitID($ID);
		$CacheArray = cls_FreeInfo::InitialInfoArray();
		return empty($CacheArray[$ID]) ? array() : $CacheArray[$ID];
	}
	# 新增或存入一条配置到初始数据源
	# 注意：$newConfig是预设为经过addslahes转义后的数组
	public static function ModifyOneConfig($newConfig = array(),$newID = 0){
		
		$newID = cls_FreeInfo::InitID($newID);
		cls_Array::array_stripslashes($newConfig);
		
		if($newID){
			if(!($oldConfig = cls_FreeInfo::InitialOneInfo($newID))){
				throw new Exception('请指定正确的独立页ID。');
			}
			$nowID = $oldConfig['fid'];
		}else{
			$newConfig['cname'] = trim(strip_tags(@$newConfig['cname']));
			if(!$newConfig['cname']){
				throw new Exception('独立页资料不完全。');
			}
			if(!($nowID = auto_insert_id('freeinfos'))){
				throw new Exception('无法得到新增的独立页ID。');
			}
			$oldConfig = cls_FreeInfo::_OneBlankInfo($nowID);
		}
		
		# 格式化数据
		if(isset($newConfig['cname'])){
			$newConfig['cname'] = trim(strip_tags($newConfig['cname']));
			$newConfig['cname'] = $newConfig['cname'] ? $newConfig['cname'] : $oldConfig['cname'];
		}
		if(isset($newConfig['tplname'])){
			_08_FilesystemFile::filterFileParam($newConfig['tplname']);
		}
		if(isset($newConfig['customurl'])){
			$newConfig['customurl'] = preg_replace("/^\/+/",'',trim($newConfig['customurl']));
		}
		if(isset($newConfig['canstatic'])){
			$newConfig['canstatic'] = empty($newConfig['canstatic']) ? 0 : 1;
			if(!$newConfig['canstatic']) $newConfig['arcurl'] = '';
		}
		
		# 赋值
		$InitConfig = cls_FreeInfo::_OneBlankInfo($nowID); # 完全的配置结构
		foreach($InitConfig as $k => $v){
			if(in_array($k,array('fid'))) continue;
			if(isset($newConfig[$k])){ # 赋新值
				$oldConfig[$k] = $newConfig[$k];
			}elseif(!isset($oldConfig[$k])){ # 新补的字段
				$oldConfig[$k] = $v;
			}
		}	
		
		# 保存
		$CacheArray = cls_FreeInfo::InitialInfoArray();
		$CacheArray[$nowID] = $oldConfig;
		
		cls_FreeInfo::SaveInitialCache($CacheArray);
		
		return $nowID;
		
	}
	
	# 删除一条配置
	public static function DeleteOne($ID){
		$ID = cls_FreeInfo::InitID($ID);
		if(!$ID || !($Info = cls_FreeInfo::InitialOneInfo($ID))) return '请指定正确的独立页。';
		if($re = cls_FreeInfo::UnStatic($ID,true)) return $re; # 同时需要删除相应的静态文件
		$CacheArray = cls_FreeInfo::InitialInfoArray();
		unset($CacheArray[$ID]);
		cls_FreeInfo::SaveInitialCache($CacheArray);
	}
		
	# 将一个独立页生成静态
	public static function ToStatic($fid=0){
		$re = cls_FreeinfoPage::Create(array('fid' => $fid,'inStatic' => true));
		return $re;
	}
	
	# 解除或删除一个独立页的静态
	public static function UnStatic($fid=0,$isDelete = false){
		$fid = cls_FreeInfo::InitID($fid);
		if(!($FreeInfo = cls_FreeInfo::Config($fid))){
			return '请指定正确的独立页面。';
		}
		if($StaticFormat = cls_FreeInfo::_StaticFormat($fid)){
			m_unlink($StaticFormat);
		}
		# 如果是删除独立页，则不需要更新记录
		if(!$isDelete){
			try {
				cls_FreeInfo::ModifyOneConfig(array('arcurl' => ''),$fid);
			} catch (Exception $e){
				return $e->getMessage();
			}
		}
	}
	
	# 系统默认的静态格式
	public static function DefaultFormat(){
		return '{$infodir}/f-{$fid}-{$page}.html';
	}
	
	# 得到静态页格式，其中{$page}尚未解析，保持为占位符
	public static function _StaticFormat($fid=0){
		$fid = cls_FreeInfo::InitID($fid);
		if(!($FreeInfo = cls_FreeInfo::Config($fid))) return '';
		$u = empty($FreeInfo['customurl']) ? cls_FreeInfo::DefaultFormat() : $FreeInfo['customurl'];
		return cls_url::m_parseurl($u,array('fid' => $fid,'infodir' => cls_env::GetG('infohtmldir'),));
	}
	
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
			'fid' => cls_FreeInfo::InitID($ID),
			'cname' => '',
			'tplname' => '',
			'customurl' => '',
			'arcurl' => '',
			'canstatic' => '1',
		);
	}
	
}
