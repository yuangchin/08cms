<?php
/* 
** 副件模型的方法汇总
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_mchannelbase{
	
	public static function Table($NoPre = false){
		return ($NoPre ? '' : '#__').'mchannels';
	}
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($ID = 0,$Key = ''){
		$re = cls_cache::Read(cls_mchannel::CacheName());
		if($ID){
			$ID = cls_mchannel::InitID($ID);
			$re = isset($re[$ID]) ? $re[$ID] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 缓存名称
    public static function CacheName(){
		return 'mchannels';
    }
	
	# 对ID进行初始格式化
    public static function InitID($ID = 0){
		return max(0,intval($ID));
	}

	# 返回 ID=>名称 的列表数组
	public static function mchidsarr($noViewID = 0){
		$mchannels = cls_cache::Read('mchannels');
		$narr = array();
		foreach($mchannels as $k => $v){
			if(!$noViewID) $v['cname'] .= "($k)";
			$narr[$k] = $v['cname'];
		}
		return $narr;
	}
	
	# 更新缓存
	public static function UpdateCache(){
		$CacheArray = cls_mchannel::InitialInfoArray();
		foreach($CacheArray as &$r){
			unset($r['cfgs0'],$r['content']);
			cls_CacheFile::ArrayAction($r,'cfgs','extract');
		}
		cls_CacheFile::Save($CacheArray,cls_mchannel::CacheName());
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialInfoArray(){
		$re = array();
		$db = _08_factory::getDBO();
		$db->select('*')->from(cls_mchannel::Table())->order('mchid')->exec();
		while($r = $db->fetch()){
			cls_CacheFile::ArrayAction($r,'cfgs','varexport');
			$re[$r['mchid']] = $r;
		}
		return $re;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($id){
		if(!($id = (int)$id)) return array();
		$db = _08_factory::getDBO();
		$re = $db->select('*')->from(cls_mchannel::Table())->where(array('mchid' => $id))->exec()->fetch();
		return $re ? $re : array();
	}
	
	# 管理后台的左侧展开菜单的显示
	public static function BackMenuCode(){
		$linknodes = cls_cache::Read('linknodes');
		$na = array();
		if($a_vmchids = empty($linknodes['mnodes']) ? array() : array_keys($linknodes['mnodes'])){
			$mchidsarr = array(0 => '全部会员') + cls_mchannel::mchidsarr(1);
			foreach($mchidsarr as $k => $v){
				if(in_array($k,$a_vmchids)) $na[$k] = array('title' => $v,'level' => 0,'active' => 1,);
			}
		}
		return ViewBackMenu($na,2);
	}
	
	# 管理后台的左侧单个分类的管理节点展示(ajax请求)
	public static function BackMenuBlock($mchid = 0){
		$UrlsArray = cls_mchannel::BackMenuBlockUrls($mchid);
		return _08_M_Ajax_Block_Base::getInstance()->OneBackMenuBlock($UrlsArray);
	}
	
	# 管理后台的左侧单个分类的管理节点url数组，可以根据需要在应用系统进行扩展
	protected static function BackMenuBlockUrls($mchid = 0){
		$UrlsArray = array();
		$mchid = max(0,intval($mchid));
		$aurls = cls_cache::Read('aurls');
		$linknodes = cls_cache::Read('linknodes');
		if(!empty($linknodes['mnodes'][$mchid])){
			$suffix = $mchid ? "&mchid=$mchid" : '';
			$auidsarr = explode(',',$linknodes['mnodes'][$mchid]);
			foreach($auidsarr as $k){
				if(!empty($aurls[$k])){
					$UrlsArray[$aurls[$k]['name']] = $aurls[$k]['link'].$suffix;
				}
			}
		}
		return $UrlsArray;
	}
	
	
	
}
