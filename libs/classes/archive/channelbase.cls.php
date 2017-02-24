<?php
/* 
** 副件模型的方法汇总
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_channelbase{
	
	public static function Table($NoPre = false){
		return ($NoPre ? '' : '#__').'channels';
	}
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($ID = 0,$Key = ''){
		$re = cls_cache::Read(cls_channel::CacheName());
		if($ID){
			$ID = cls_channel::InitID($ID);
			$re = isset($re[$ID]) ? $re[$ID] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	# 缓存名称
    public static function CacheName(){
		return 'channels';
    }
	
	# 对ID进行初始格式化
    public static function InitID($ID = 0){
		return max(0,intval($ID));
	}
	# 返回 ID=>名称 的列表数组
	public static function chidsarr($all=0,$noViewID = 0){
		$channels = cls_channel::Config();
		$narr = array();
		foreach($channels as $k => $v){
			if($all || $v['available']){
				if(!$noViewID) $v['cname'] .= "($k)";
				$narr[$k] = $v['cname'];
			}
		}
		return $narr;
	}
	
	# 更新缓存
	public static function UpdateCache(){
		$CacheArray = self::InitialInfoArray();
		foreach($CacheArray as &$r){
			unset($r['vieworder'],$r['content'],$r['cfgs0'],$r['remark']);
			cls_CacheFile::ArrayAction($r,'cfgs','extract');
		}
		cls_CacheFile::Save($CacheArray,cls_channel::CacheName());
	}

	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$re = array();
		$db = _08_factory::getDBO();
		$db->select('*')->from(cls_channel::Table())->order('vieworder,chid')->exec();
		while($r = $db->fetch()){
			cls_CacheFile::ArrayAction($r,'cfgs','varexport');
			$re[$r['chid']] = $r;
		}
		return $re;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($id){
		if(!($id = cls_channel::InitID($id))) return array();
		$db = _08_factory::getDBO();
		$re = $db->select('*')->from(cls_channel::Table())->where(array('chid' => $id))->exec()->fetch();
		cls_CacheFile::ArrayAction($re,'cfgs','varexport');
		return $re ? $re : array();
	}
	
	
}
