<?php
/* 
** 副件模型的方法汇总
** 使用模板配置缓存进行保存数据源，目前 完全数据源=应用缓存。
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）。
*/
!defined('M_COM') && exit('No Permission');
class cls_fchannelbase{


	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($chid = 0,$Key = ''){
		$re = cls_cache::Read(cls_fchannel::CacheName());
		if($chid){
			$chid = cls_fchannel::InitID($chid);
			$re = isset($re[$chid]) ? $re[$chid] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 读取字段配置
    public static function Field($chid = '',$FieldName = ''){
		$re = array();
		if(cls_fchannel::Config($chid)){
			$re = cls_cache::Read('ffields',$chid);
			if($FieldName){
				$re = isset($re[$FieldName]) ? $re[$FieldName] : array();
			}
		}
		return $re;
    }
	
	# 对ID进行初始格式化
    public static function InitID($chid){
		return max(0,intval($chid));
	}
	
	# 缓存名称
    public static function CacheName(){
		return 'fchannels';
    }
	
	# 得到关联的内容表
	# chid = 0时表示为主表，否则为模型表
	public static function ContentTable($chid = 0){
		$chid = (int)$chid;
		return 'farchives'.($chid ? "_$chid" : '');
	}
	
	# 返回 ID=>名称 的列表数组
	public static function fchidsarr(){
		$fchannels = cls_cache::Read(cls_fchannel::CacheName());
		$narr = array();
		foreach($fchannels as $k => $v) $narr[$k] = $v['cname']."($k)";
		return $narr;
	}
	
	# 更新应用缓存
	public static function UpdateCache(){
		cls_fchannel::_SaveCache();
	}
	
	# 更新数据源，相当于更新数据表，需要传入数组
	public static function SaveInitialCache($CacheArray = array()){
		cls_fchannel::_SaveCache($CacheArray);
	}
	
	# 动态的副件模型资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		return cls_cache::Read(cls_fchannel::CacheName(),'','',1);
	}
	
	# 动态的单个副件模型资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($chid){
		$chid = (int)$chid;
		$CacheArray = cls_fchannel::InitialInfoArray();
		return empty($CacheArray[$chid]) ? array() : $CacheArray[$chid];
	}
	
	# 删除一条记录
	public static function DeleteOne($chid = 0){
		if(!($fchannel = cls_fchannel::InitialOneInfo($chid))) cls_message::show('指定的模型不存在。');
		
		# 检查是否有相关的副件分类
		$fcatalogs = cls_fcatalog::InitialInfoArray();
		foreach($fcatalogs as $k => $v){
			if($v['chid'] == $chid) cls_message::show('请先删除相关联的副件分类。');
		}
		
		# 删除模型表
		$db = _08_factory::getDBO();
		$db->dropTable('#__'.cls_fchannel::ContentTable($chid),true);
		
		# 删除字段配置记录及缓存
		cls_fieldconfig::DeleteOneSourceFields('fchannel',$chid);
		
		# 更新当前数据源及缓存
		$CacheArray = cls_fchannel::InitialInfoArray();
		unset($CacheArray[$chid]);
		cls_fchannel::SaveInitialCache($CacheArray);
	}
	
	# 新建一条记录
	# 后续需要处理出错后的rollback???
	public static function AddOne($UserConfig = array()){
		global $db,$tblprefix,$dbcharset;
		$CacheArray = cls_fchannel::InitialInfoArray();
		if($newID = auto_insert_id('fchannels')){
			
			# 生成副件模型记录
			cls_Array::array_stripslashes($UserConfig);
			$CacheArray[$newID] = array_merge(cls_fchannel::_OneBlankInfo($newID),$UserConfig);
			cls_fchannel::SaveInitialCache($CacheArray);//先更新缓存
			
			# 生成内容表
			$db->query("CREATE TABLE {$tblprefix}".cls_fchannel::ContentTable($newID)." (
						aid mediumint(8) unsigned NOT NULL default '0',
						PRIMARY KEY (aid))".(mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM DEFAULT CHARSET=$dbcharset" : " TYPE=MYISAM"));
			
			# 补上字段配置记录
			$newField = array(
				'ename' => 'subject', 
				'cname' => '标题', 
				'datatype' => 'text', 
				'type' => 'f',
				'tbl' => cls_fchannel::ContentTable(), 
				'tpid' => $newID, 
				'issystem' => '1', 
				'iscommon' => '1', 
				'available' => '1', 
				'length' => '255', 
				'notnull' => '1', 
			);
			cls_fieldconfig::ModifyOneConfig('fchannel',$newID,$newField);
		}
		return $newID ? $newID : 0;
	}
	
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
				'chid' => $ID,
				'cname' => '',
		);
	}
	
	# 保存应用缓存/完全数据源，
	protected static function _SaveCache($CacheArray = ''){
		if(!is_array($CacheArray)){ # 从文件刷新缓存
			$CacheArray = cls_fchannel::InitialInfoArray();
		}else{ # 来自传入的配置数组
			ksort($CacheArray);# 重新排序
		}
		cls_CacheFile::Save($CacheArray,cls_fchannel::CacheName());
	}
	
	
	
}
