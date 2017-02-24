<?php
/* 
** 推送分类的有关方法汇总
** 目前应用缓存与完全数据源是同一个
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_PushTypeBase{
	
	# 缓存名称
    public static function CacheName(){
		return 'pushtypes';
    }
	
	# 返回 ID=>名称 的列表数组
	public static function ptidsarr(){
		$pushtypes = cls_cache::Read(cls_PushType::CacheName());
		$narr = array();
		foreach($pushtypes as $k => $v) $narr[$k] = $v['title'];
		return $narr;
	}
	
	# 更新缓存，按字段缓存名，提供给cls_CacheFile使用
	public static function UpdateCache(){
		cls_PushType::SaveInitialCache();
	}
	
	# 更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($CacheArray = ''){
		if(!is_array($CacheArray)){ # 来自传入的配置数组
			$CacheArray = cls_PushType::InitialInfoArray();
		}
		cls_Array::_array_multisort($CacheArray,'vieworder',true); # 以vieworder重新排序
		cls_CacheFile::Save($CacheArray,cls_PushType::CacheName());
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$CacheArray = cls_cache::Read(cls_PushType::CacheName(),'','',1);
		return $CacheArray;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($id){
		
		$id = (int)$id;
		$CacheArray = cls_PushType::InitialInfoArray();
		return empty($CacheArray[$id]) ? array() : $CacheArray[$id];
		
	}
	# 新增或存入一条配置到初始数据源
	public static function ModifyOneConfig($newConfig = array(),$newID = 0){
		
		$newID = (int)$newID;
		cls_Array::array_stripslashes($newConfig);
		if($newID){
			if(!($oldConfig = cls_PushType::InitialOneInfo($newID))) cls_message::show('请指定正确的推送分类。');
			$nowID = $oldConfig['ptid'];
		}else{
			$newConfig['title'] = trim(strip_tags(@$newConfig['title']));
			if(!$newConfig['title']) cls_message::show('分类资料不完全');
			$nowID = auto_insert_id('pushtypes');
			if(!$nowID) cls_message::show('无法得到新增的推送分类ID。');
			$oldConfig = cls_PushType::_OneBlankInfo($nowID);
		}
		
		# 格式化数据
		if(isset($newConfig['title'])){
			$newConfig['title'] = trim(strip_tags($newConfig['title']));
			$newConfig['title'] = $newConfig['title'] ? $newConfig['title'] : $oldConfig['title'];
		}
		if(isset($newConfig['remark'])){
			$newConfig['remark'] = trim(strip_tags($newConfig['remark']));
		}
		if(isset($newConfig['vieworder'])){
			$newConfig['vieworder'] = max(0,intval($newConfig['vieworder']));
		}
		
		# 赋值
		$InitConfig = cls_PushType::_OneBlankInfo($nowID); # 完全的配置结构
		foreach($InitConfig as $k => $v){
			if(in_array($k,array('ptid'))) continue;
			if(isset($newConfig[$k])){ # 赋新值
				$oldConfig[$k] = $newConfig[$k];
			}elseif(!isset($oldConfig[$k])){ # 新补的字段
				$oldConfig[$k] = $v;
			}
		}	
		
		# 保存
		$CacheArray = cls_PushType::InitialInfoArray();
		$CacheArray[$nowID] = $oldConfig;
		cls_PushType::SaveInitialCache($CacheArray);
		
		return $nowID;
		
	}
	public static function DeleteOne($ID){
		
		$ID = (int)$ID;
		if(!$ID || !($Info = cls_PushType::InitialOneInfo($ID))) return '请指定正确的推送分类。';
		
		if($PushAreas = cls_pusharea::InitialInfoArray($ID)){
			return '请先删除分类内的推送位。';
		}
		
		$CacheArray = cls_PushType::InitialInfoArray();
		unset($CacheArray[$ID]);
		cls_PushType::SaveInitialCache($CacheArray);
	}
	
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
			'ptid' => (int)$ID,
			'title' => '',
			'vieworder' => '0',
			'remark' => '',
		);
	}
	

	
}
