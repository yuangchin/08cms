<?php
/* 
** 交互项目的方法汇总
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_commubase{
	
	# 配置的数据表名
	public static function Table($NoPre = false){
		return ($NoPre ? '' : '#__').'acommus';
	}
	
	# 缓存名称
    public static function CacheName(){
		return 'commus';
    }
	
	# 关联内容表的表名
    public static function ContentTable($cuid = 0){
		return cls_commu::Config($cuid,'tbl');
    }
	
	# 推送指定交互信息到指定推送位
	# loadtype : 0.手动推送, 11.手动添加, 21.自动推送
	public static function push($cuid,$cid,$paid,$loadtype=0){
		if($PushArea = cls_PushArea::Config($paid)){
			if($cuid != $PushArea['sourceid']) return false;
			if($Info = cls_commu::OneInfo($cid,$cuid)){
				return cls_pusher::push($Info,$paid,$loadtype);
			}
		}
		return false;
	}
	# 自动推送
	public static function autopush($cuid,$cid){ 
		$pa = cls_pusher::paidsarr('commus',$cuid); 
		foreach($pa as $paid=>$paname){ 
			$pusharea = cls_PushArea::Config($paid);
			if(!empty($pusharea['autopush'])){ //不用返回值
				cls_commu::push($cuid,$cid,$paid,21); 
			}
		}
	}
	# 根据aid,mid,批量删除推送位; 用于userbase.cls.php/arcedit.cls.php中的删除操作
	// cuid,key,kid等,不会是外界输入
	public static function delpushs($cuid,$key='mid',$kid='0'){ 
		global $db,$tblprefix;
		$table = cls_commu::ContentTable($cuid);
		$query = $db->query("SELECT cid FROM {$tblprefix}$table WHERE $key='$kid'");
		while($r = $db->fetch_array($query)){
			cls_pusher::DelelteByFromid($r['cid'],'commus',$cuid);
		}
	}
	
	# 读取一条交互信息，只针对有关联内容表的交互
	public static function OneInfo($cid,$cuid){
		$re = array();
		if(!($cid = (int)$cid)) return $re;
		if($ContentTable = cls_commu::ContentTable($cuid)){
			global $db,$tblprefix;
			$re = $db->fetch_one("SELECT * FROM {$tblprefix}$ContentTable WHERE cid='$cid'");
		}
		return $re ? $re : array();
	}
	
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($cuid = 0,$Key = ''){
		$re = cls_cache::Read(cls_commu::CacheName());
		if($cuid){
			$cuid = (int)$cuid;
			$re = isset($re[$cuid]) ? $re[$cuid] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 更新缓存
	public static function UpdateCache(){
		$CacheArray = cls_commu::InitialInfoArray();
		foreach($CacheArray as &$r){
			unset($r['vieworder'],$r['content'],$r['cfgs0']);
			cls_CacheFile::ArrayAction($r,'cfgs','extract');
		}
		cls_CacheFile::Save($CacheArray,cls_commu::CacheName());
	}
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$re = array();
		$db = _08_factory::getDBO();
		$db->select('*')->from(cls_commu::Table())->order('vieworder,cuid')->exec();
		while($r = $db->fetch()){
			cls_CacheFile::ArrayAction($r,'cfgs','varexport');
			$re[$r['cuid']] = $r;
		}
		return $re;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($id){
		if(!($id = (int)$id)) return array();
		$db = _08_factory::getDBO();
		$re = $db->select('*')->from(cls_commu::Table())->where(array('cuid' => $id))->exec()->fetch();
		cls_CacheFile::ArrayAction($re,'cfgs','varexport');
		return $re ? $re : array();
	}
	
	
}
