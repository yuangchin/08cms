<?php
/**
 * 缓存读取操作公共类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_cache
{ 

	public static $_08CACHE = array();//将读取过的缓存暂存，重用

    /**
     * 读取通用架构缓存，按$CacheName不同而分别储存于dynamic/cache/(架构)，template/xxx/config/(模板)
     * 允许读取完整缓存中的一个分支
     * 
     * @param  string $CacheName 		缓存名，如channels时读取完整缓存，而channel则读取分支
     * @param  string $BigClass			缓存大分类，或关联类型
     * @param  string $SmallClass		缓存小分类，或具体特征
     * @param  int $noExCache			不启用扩展缓存
     * @param  bool   $config_dir       是否存入模板文件夹的标签类缓存，TRUE为是，FALSE为不是
     * 
     * @return array					返回获取到的系统缓存
     * @since  1.0
     */ 
   
	public static function Read($CacheName,$BigClass = '',$SmallClass = '',$noExCache = 0, $config_dir = false){
		if($BigClass && $SmallClass){
			//三级缓存，例：读取单个(文档模型)字段，Read('field','模型id','字段名')
			$CacheName .= substr($CacheName,-1) == 's' ? 'es' : 's';
			$CacheArray = self::Read($CacheName,$BigClass,'',$noExCache);
			return empty($CacheArray[$SmallClass]) ? array() : $CacheArray[$SmallClass];
		}elseif($BigClass && self::_AllowSmallName($CacheName)){
			$CacheArray = self::Read($CacheName.'s','','',$noExCache);
			return empty($CacheArray[$BigClass]) ? array() : $CacheArray[$BigClass];
		}else{
			$m_excache = cls_excache::OneInstance();
			$noExCache = $m_excache ? $noExCache : 1;
			$Key = self::CacheKey($CacheName,$BigClass,$SmallClass);
			if(!isset(self::$_08CACHE[$Key])){
				if($noExCache || !(self::$_08CACHE[$Key] = $m_excache->get($Key))){
					@include self::CacheDir($CacheName, $config_dir)."$Key.cac.php";
					self::$_08CACHE[$Key] = empty($$Key) ? array() : $$Key;
					if(self::$_08CACHE[$Key] && !$noExCache) $m_excache->set($Key,self::$_08CACHE[$Key]);
				}
			}
			return self::$_08CACHE[$Key];
		}
	}
	
    /**
     * 按全局变量方式加载通用架构缓存
     * 
     * @param  string $Keys 	缓存名，多个缓存以逗号分隔
     * 
     * @since  1.0
     */ 
	public static function Load($Keys = ''){
		//全部启用扩展缓存
		if(empty($Keys)) return;
		$Keys = array_filter(explode(',',$Keys));
		$m_excache = cls_excache::OneInstance();
		foreach($Keys as $Key){
			_08_FilesystemFile::filterFileParam($Key);
			if($Key = trim($Key)){
				global $$Key;
				if(!isset(self::$_08CACHE[$Key])){
					if(!$m_excache->enable || !($$Key = $m_excache->get($Key))){
						@include self::CacheDir($Key)."$Key.cac.php";
						if($m_excache->enable && !empty($$key)) $m_excache->set($Key,$$Key);
					}
					self::$_08CACHE[$Key] = empty($$Key) ? array() : $$Key;
				}
				$$Key = self::$_08CACHE[$Key];
			}
		}
		return;
	}
	
    /**
     * 在当前过程中，中途更新可重用缓存$_08CACHE，之后加载为更新后的缓存，设为NULL，则为删除该项
     * 
     * @param  string $CacheKey 		缓存名
     * @param  $CacheValue				缓存值
     * 
     * @since  1.0
     */ 
	public static function SetNow($CacheKey,$CacheValue = NULL){
		if(isset(self::$_08CACHE[$CacheKey])){
			if(is_null($CacheValue)){
				unset(self::$_08CACHE[$CacheKey]);
			}else{
				self::$_08CACHE[$CacheKey] = $CacheValue;
			}
		}
	}
	
	
    /**
     * 读取单个模板标识缓存，将并将setting中的设置进行合并
     * 
     * @param  string $TagType 	标识类型，如ctag(复合标识)，rtag(区块标识)
     * @param  string $TagName 	标识名称
     * 
     * @since  1.0
     */ 
	public static function ReadTag($TagType,$TagName){
		$TagAarray = self::Read($TagType,$TagName);
		$TagAarray && $TagAarray = array_merge($TagAarray,$TagAarray['setting']);
		unset($TagAarray['setting']);
		return $TagAarray;
	}
	
    /**
     * 生成通用架构缓存的键值(缓存文件名)
     * 
     * @param  string $CacheName 		缓存名，如channels时读取完整缓存，而channel则读取分支
     * @param  string $BigClass			缓存大分类，或关联类型
     * @param  string $SmallClass		缓存小分类，或具体特征
     * 
     * @return string					返回缓存键值(缓存文件名)
     * @since  1.0
     */ 
	public static function CacheKey($CacheName,$BigClass = '',$SmallClass = ''){
		$Key = $CacheName.$BigClass.$SmallClass;
		_08_FilesystemFile::filterFileParam($Key);
		return $Key;
	}
	
	
    /**
     * 得到通用架构缓存的完整保存路径
     * 
     * @param  string $CacheName 		缓存名，如channels时读取完整缓存，而channel则读取分支
     * @param  bool   $config_dir       是否存入模板文件夹的标签类缓存，TRUE为是，FALSE为不是
     * 
     * @return string					返回完整保存路径
     * @since  1.0
     */ 
	public static function CacheDir( $CacheName, $config_dir = false ){
		$_template_config = array(//存入模板文件夹的配置类缓存
		'cnodes','mcnodes','o_cnodes','mtpls','sptpls','jstpls','csstpls','tagclasses',
		'cntpls','mcntpls','cnconfigs','tplcfgs','arc_tpl_cfgs','arc_tpls','ca_tpl_cfgs','tpl_mconfigs','tpl_fields',
		'o_cntpls','o_cnconfigs','o_tplcfgs','o_mtpls','o_arc_tpl_cfgs','o_arc_tpls','o_ca_tpl_cfgs','o_sptpls',
		'fchannels','ffields','fcatalogs','pushtypes','pushareas','pafields','freeinfos','mtconfigs','mcatalogs',
		'_pushareas','_ffields','_pafields', # 完全数据源与应用缓存分离
		);
		$_template_tag = array(//存入模板文件夹的标签类缓存
		'advtag', 'advtags', 'ctag','ctags','rtag','rtags',
		);
		if(in_array($CacheName,$_template_config) || $config_dir){
			return cls_tpl::TemplateTypeDir('config');
		}elseif(in_array($CacheName,$_template_tag)){
			return cls_tpl::TemplateTypeDir('tag');
		}else{
			return _08_CACHE_PATH . 'cache'.DS;
		}
	}
	
    /**
     * 是否允许使用 read('xxx',key) 来读取缓存的 $xxxs[key] 分支
     * 
     */ 
	private static function _AllowSmallName($CacheName){
		$AllowArray = array('channel','catalog','fchannel','fcatalog','player','gmodel','gmission','aurl','commu','abrel','cnrel','mchannel','mctype','pusharea',);
		return in_array($CacheName,$AllowArray) ? true : false;
	}

    /**
     * 获取缓存类属性
     * 
     * @param  string $class_name 缓存类名
     * @param  string $ext        扩展缓存后缀
     * @param  string $cache_dir  缓存路径
     * 
     * @return array $vars 返回获取到的系统缓存
     * @since  1.0
     */ 
    public static function getCacheClassVar($class_name, $ext = '_son', $cache_dir = '')
    {
        $vars = array();
        # 如果有扩展缓存类则读取扩展缓存类
        if(class_exists($class_name . $ext))
        {
            $vars = get_class_vars($class_name . $ext);            
        }
        # 没有扩展缓存类直接读取核缓存类
        else if(class_exists($class_name))
        {
            $vars = get_class_vars($class_name);   
        }
        # 如果缓存不是用类封装时则用原始的调用方式获取
        else
        {
            $vars = self::cacRead($class_name, $cache_dir);
        }
        return $vars;
    }
	
	/**
	 * 优先读取扩展系统中的开发配置缓存
	 * 扩展系统的开发配置缓存目录：extend_sample/dynamic/syscache/，通用核心的开发配置缓存目录：dynamic/syscache/
	 *
	 * @param  string $cname  缓存名称
	 * @param  bool   $noex   不读取扩展缓存（如$m_excache），1为不读取，0为读取，
	 * @return array  $re     返回对应的缓存
	 */
	public static function exRead($cname,$noex = 0){
		if(!$re = self::cacRead($cname,_08_EXTEND_SYSCACHE_PATH,$noex)) $re = self::cacRead($cname,'',$noex);
		return $re;
	}
    
    /**
     * 按指定路径及缓存名的方式读取缓存
     * 通常用于系统内置的开发配置缓存，如果是通用架构缓存，则使用Read方法
     * 
     * @param  string $cname  缓存名称
     * @param  string $cacdir 缓存路径
     * @param  bool   $noex   不读取扩展缓存，1为不读取，0为读取
     * @return array  $$cname 返回缓存数据数组
     * 
     * @static
     * @since  1.0
     */ 
    public static function cacRead($cname,$cacdir='',$noex = 0){
		$m_excache = cls_excache::OneInstance();
		$noex = $m_excache->enable ? $noex : 1;
    	$cacdir || $cacdir = _08_SYSCACHE_PATH;
		_08_FilesystemFile::filterFileParam($cname);
		if(!($cname = trim($cname))) return array();
        # 核心缓存文件
		if(!in_array(substr($cacdir,-1),array('/',DS))) $cacdir .= DS;
    	if($noex){
    		@include $cacdir.$cname.'.cac.php';
    		empty($$cname) && $$cname = array();
    	}else{ # 扩展缓存文件
    		$key = $cname.substr(md5($cacdir),6,10);
    		if(!($$cname = $m_excache->get($key))){
				$$cname = self::cacRead($cname,$cacdir,1);
    			$$cname && $m_excache->set($key,$$cname);
    		}
    	}
    	return $$cname;
    }
    /**
     * 强制从缓存文件重新载入通用架构缓存，暂时保留以兼容旧版本
     * 
     * @param  string $CacheName 		缓存名，如channels时读取完整缓存，而channel则读取分支
     * @param  string $BigClass			缓存大分类，或关联类型
     * @param  string $SmallClass		缓存小分类，或具体特征
     * 
     * @since  1.0
     */ 
	public static function ReLoad($CacheName,$BigClass = '',$SmallClass = ''){
		return self::Read($CacheName,$BigClass,$SmallClass,1);
	}
	/**
	 * 获取dynamic/htmlcac中的缓存子路径，如果不存在就创建目录
	 *
	 * @param  string   $mode   类型
	 * @param  string   $spath  子路径
	 * @return string   $cacdir 路径
	 */
	public static function HtmlcacDir($mode='arc',$spath=''){
		_08_FilesystemFile::filterFileParam($mode);
		_08_FilesystemFile::filterFileParam($spath);
		$cacdir = _08_CACHE_PATH."htmlcac/$mode/";
		if($spath) $cacdir .= $spath.'/';
		is_dir($cacdir) || mmkdir($cacdir);
		return $cacdir;
	}
	
    /**
     * 读取exconfigs缓存
     * 
     * @param  string $key 顶级键; eg: usedcar, qiuzu 等; 为空返回所有
     * @param  string $sub 子健; eg: m1:0,m3:g4,m0:else -=> mchid=1,取0下标, mchid=3取grouptype4下标, 其它会员取else下标(放最后);  为空返回所有子健
     * @return array  $re 返回缓存数据数组
     */ 
    public static function exConfig($key,$sub=0){ //,$ex=array()
		$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
		if(empty($key)) return $exconfigs;
		if(!isset($exconfigs[$key])) return array();
		$tmp = $exconfigs[$key];
		if(strpos($sub,':')){
			$curuser = cls_UserMain::CurUser();	
			$re = array();
			$a0 = explode(',',$sub);
			foreach($a0 as $v){
				$b0 = explode(':',$v);
				$mchid = str_replace('m','',$b0[0]);
				if(empty($mchid) && isset($tmp[$b0[1]])){ 
					$re = $tmp[$b0[1]];
				}elseif($mchid==$curuser->info['mchid']){
					$k = strstr($b0[1],'g') ? $curuser->info[str_replace('g','grouptype',$b0[1])] : $k = $b0[1];
					$re = $tmp[$k];
					break;
				}
			}
		}elseif(isset($tmp[$sub])){  
			$re = $tmp[$sub];
		}else{ 
			$re = $tmp;	
		}
		//if($ex['xxxx']=='xxxx'){ } //扩展
		return $re;
    }
    
}