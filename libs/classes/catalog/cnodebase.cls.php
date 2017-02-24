<?php
/**
* 类目节点的处理方法
* 本类为cls_cnode的基类
* NodeMode：是否手机节点
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
defined('M_COM') || exit('No Permission');
class cls_cnodebase{
	
	# 根据传入的参数，取得节点字串
	public static function cnstr($Params = array()){
		#cnstr()需要放出来定扩展
		return cnstr($Params); 
	}
	
	
	# 指定节点的某个附加页的url
	# NodeMode：是否手机节点
	public static function url($cnstr,$addno = 0,$NodeMode = 0){
		$Node = cls_node::cnodearr($cnstr,empty($NodeMode) ? 0 : 1);
		$addno = (int)$addno;
		$urlvar = 'indexurl'.($addno ? $addno : '');
		return isset($Node[$urlvar]) ? $Node[$urlvar] : '#';
	}
	
	# 清除系统首页静态
	public static function UnStaticIndex(){
		$cnformat = idx_format();
		m_unlink($cnformat);
		return '静态解除成功';
	}
	
	# 修复节点静态链接
	public static function BlankStaticUrl($cnstr,$force=0){//force:强行覆盖第一个文件，为0时为修复链接
		$enablestatic = cls_env::mconfig('enablestatic');
		if($enablestatic && $cnstr){
			if(!$cnode = cls_node::read_cnode($cnstr)) return;
			$statics = empty($cnode['statics']) ? array() : explode(',',$cnode['statics']);
			for($i = 0;$i <= $cnode['addnum'];$i++){
				if(empty($statics[$i]) ? $enablestatic : 0){
					$cnfile = M_ROOT.cls_url::m_parseurl(cls_node::cn_format($cnstr,$i,$cnode),array('page' => 1));
					if($force || !is_file($cnfile)) @str2file(_08_HTML::DirectUrl("index.php?$cnstr".($i ? "&addno=$i" : '')),$cnfile);
				}
			}
		}
	}
		
	# 更新缓存
	public static function UpdateCache($NodeMode = 0){
		$CacheArray = cls_cnode::CacheArray($NodeMode);
		cls_CacheFile::Save($CacheArray,cls_cnode::CacheName($NodeMode));
	}
	
	# 取得配置数据表名
	public static function Table($NodeMode = 0,$NoPre = false){
		return ($NoPre ? '' : '#__').($NodeMode ? 'o_' : '').'cnodes';
	}
	
	# 从数据库获得生成缓存所需要的数组
	public static function CacheArray($NodeMode = 0){
		$CacheArray = array();
		$db = _08_factory::getDBO();
		$db->select('ename,alias,appurl,tid')->from(cls_cnode::Table($NodeMode))->where(array('closed' => 0))->exec();
		while($r = $db->fetch()){
			$cnstr = $r['ename'];
			unset($r['ename']);
			foreach(array('alias','appurl','tid',) as $k) if(!$r[$k]) unset($r[$k]);
			$CacheArray[$cnstr] = $r;
		}
		return $CacheArray;
	}
	
	# 缓存名称
    public static function CacheName($NodeMode = 0){
		return ($NodeMode ? 'o_' : '').'cnodes';
    }
	
}
