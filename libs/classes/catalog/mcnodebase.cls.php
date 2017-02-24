<?php
/**
* 会员频道节点的处理方法
* 本类为cls_mcnode的基类
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
defined('M_COM') || exit('No Permission');
class cls_mcnodebase{
	
	# 指定节点的某个附加页的url
	public static function url($cnstr,$addno = 0){
		$Node = cls_node::mcnodearr($cnstr);
		$addno = (int)$addno;
		$urlvar = 'mcnurl'.($addno ? $addno : '');
		return isset($Node[$urlvar]) ? $Node[$urlvar] : '#';
	}
	
	# 更新缓存
	public static function UpdateCache(){
		$CacheArray = cls_mcnode::CacheArray();
		cls_CacheFile::Save($CacheArray,cls_mcnode::CacheName());
	}
	
	# 清除会员频道首页静态
	public static function UnStaticIndex(){
		m_unlink(cls_node::mcn_format('',0));
		return '静态解除成功';
	}
	
	public static function BlankStaticUrl($cnstr,$force=0){//force:强行覆盖第一个文件，为0时为修复链接
		$memberdir = cls_env::mconfig('memberdir');
		if(!($enablestatic = cls_env::mconfig('enablestatic'))) return;
		if(!$cnstr || !($cnode = cls_node::read_mcnode($cnstr))) return;
		$statics = empty($cnode['statics']) ? array() : explode(',',$cnode['statics']);
		for($i = 0;$i <= @$cnode['addnum'];$i++){
			if(empty($statics[$i]) ? $enablestatic : 0){
				$cnfile = M_ROOT.cls_url::m_parseurl(cls_node::mcn_format($cnstr,$i),array('page' => 1));
				if($force || !is_file($cnfile)) @str2file(_08_HTML::DirectUrl(cls_env::mconfig('memberdir')."/index.php?$cnstr".($i ? "&&addno=$i" : '')),$cnfile);
			}
		}
	}
	
	
	# 从数据库获得生成缓存所需要的数组
	public static function CacheArray(){
		$CacheArray = array();
		$db = _08_factory::getDBO();
		$db->select('ename,alias,appurl,tid')->from(cls_mcnode::Table())->where(array('closed' => 0))->exec();
		while($r = $db->fetch()){
			$cnstr = $r['ename'];
			unset($r['ename']);
			foreach(array('alias','appurl','tid',) as $k) if(!$r[$k]) unset($r[$k]);
			$CacheArray[$cnstr] = $r;
		}
		return $CacheArray;
	}
	
	# 取得配置数据表名
	public static function Table($NoPre = false){
		return ($NoPre ? '' : '#__').'mcnodes';
	}
	# 缓存名称
    public static function CacheName(){
		return 'mcnodes';
    }
		
}
