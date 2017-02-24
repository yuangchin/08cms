<?php
/**
 * 自动加载类
 *
 * 增加此类可让外部文件new一个类时自动加载该类所在的文件
 * 注：要new的类所在的文件必须在self::registerPrefix()方法注册的目录范围内，并且该类名必须与注册的目录前缀前三个符字相同，
 *     且类名后缀必须与文件名相同，本类默认支持当前目录，如果想支持其它目录只要在本类的setup方法中增加调用
 *     self::registerPrefix 并把要支持的目录与前缀作为参数传递即可。
 * 如：假设self::registerPrefix注册的是self::registerPrefix('_08cms_', dirname(__FILE__));  那类名前缀前三位就是_08 ,
 *     当前路径下的所有文件包含的类名前缀为：_08的并且后缀与文件名相同的都会被自动加载，但文件名与目录必须为小写，
 * 如：1、$load = new _08cms_Loader(); 则会自动加载本目录的 loader.php文件，类名为：_08cms_Loader
 *     2、$test = new _08_test();      则会自动加载本目录的 test.php文件，类名为：_08_test
 *     3、$test = new _08_test_file(); 如果有多个下划线则以最后一串字符串为文件名，在会自动加载本目录的 file.php 文件。
 *                                     类名为：_08_test_file
 *
 * @package   08CMS.Platform
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('M_COM') || die('Access forbidden!');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
class _08_Loader
{
    /**
     * 模型前缀
     * 
     * @var   string
     * @since nv50
     */
    const MODEL_PREFIX = '_08_M_';
    
    /**
     * 控制器前缀
     * 
     * @var   string
     * @since nv50
     */
    const CONTROLLER_PREFIX = '_08_C_';
    
    // 加载文件列表
    protected static $_files = array();

    // 自动加载该数组元素目录下所有文件
    protected static $_prefixes = array();
	
    // 单例加载器对象
    protected static $_Loader = null;
	
    // 自动加载缓存类文件map
    protected static $_maplist = array();
	
    // 新增加的map标记
    protected static $_addflag = 0;

    /**
     * 初始化缓存，优先使用m_excache, 再次使用文件缓存；
	 * 加载目录：没有的化,重新生成；
	 * 加载类map：析构方法中更新；
     */
    function __construct(){
		
		$m_excache = cls_excache::OneInstance();
		$mapfixs = _08_SYSCACHE_PATH.'sysparams.cac.php'; //自动加载的前缀与路径组合
		$mappath = _08_USERCACHE_PATH.'autoload_pathmap.php';
		$mapfile = _08_USERCACHE_PATH.'autoload_filemap.php';
		//初始化缓存的类加载路径map
		$modflag = @filemtime($mappath)>filemtime($mapfixs); //修改标记
		if($modflag && $m_excache->enable && $re = $m_excache->get(md5('autoload_pathmap'))){
			 self::$_prefixes = $re;
		}elseif($modflag && @include($mappath)){ 
			 self::$_prefixes = $autoload_pathmap;
		}else{ 
			// 如果未设置系统参数或是系统参数格式错误时就按系统默认的前缀加载
			require_once $mapfixs;
			if(empty($sysparams['autoload']) || !is_array($sysparams['autoload']))
			{
				foreach( array(
						dirname(__FILE__),
						M_ROOT . _08_ADMIN,
						M_ROOT . _08_ADMIN . DS . 'extends'
				) as $path ) {
					self::registerPrefix( '_08cms_', $path );
				}
				self::registerPrefix( 'cls_', dirname(__FILE__) );
			}
			else
			{
                self::autoLoadPathConfigs( $sysparams['autoload'] );
			}
			if($m_excache->enable) $m_excache->set(md5('autoload_pathmap'),self::$_prefixes); //m_excache缓存
			self::saveCacheMap('path',self::$_prefixes); //文件缓存
		}
		//初始化缓存的类加载文件map
		if($m_excache->enable && $re = $m_excache->get(md5('class_filemap'))){
			 self::$_maplist = $re;
		}elseif(is_file($mapfile) && include($mapfile)){ //is_file($mapfile) && empty($no)
			 self::$_maplist = $autoload_filemap;
		}
        self::autoLoadRegister();
	
	
	} //$this->setup();

    /**
     * 注册自动加载文件列表
     * @param string $class 类名
     * @param string $path  类所在的文件路径
     */
    public static function register($class, $path = '')
    {
        if(false !== stripos($path, M_ROOT . 'include'))
        {
            $class = '_08cms_' . ucfirst($class);
        }
        if(!empty($class) && is_file($path))
        {
            if(empty(self::$_files[$class]))
            {
                self::$_files[$class] = $path;
            }
        }
    }

    /**
     * 加载文件
     * @param string $class 类名
     * @return bool         如果加载成功返回TRUE，否则返回FALSE
     */
    private static function load($class)
    {
        if(class_exists($class)) return true;
       	if (isset(self::$_files[$class]))
        {
			include self::$_files[$class];
			return true;
		}

		return false;
    }

    /**
     * 自动加载所需要的文件
     * @param  string $class 要加载的类名
     * @return bool          如果加载成功或加载文件已经存在返回TRUE，否则返回FALSE
     */
    private static function _autoload ($class)
    {   
        foreach(self::$_prefixes as $prefix => $v)
        {
            if (0 === strpos($class, $prefix))
            {
				return self::_load($class, $v, $prefix);
			}
        }
    }

    /**
     * 加载文件
     *
     * @param  string $class  要加载的类名
     * @param  array  $paths  文件路径
     * @return bool           加载成功返回TRUE，否则返回FALSE
     */
    private static function _load($class, $paths, $prefix)
    {
		if (false !== strpos($class, '\\'))
        {
            $fileName = strtolower(str_replace('\\', DS, substr($class, strlen($prefix))) . '.php');
        }
        else
        {
        	$prefix2 = substr($prefix, 0, -1);
            preg_match("/^({$prefix2})([^_]*)_(\w+)/i", $class, $parts);
            isset($parts[2]) && ($prefix == '_08') && $parts[2] = substr($parts[2], 1);
            if(empty($parts[3]) || empty($parts[1])) return false;
            if(0 === strpos($prefix, '_08'))
            {
                $fileName = strtolower($parts[3]) . ($parts[2] ? '.' . strtolower($parts[2]) : '') . '.php';
            }
            else
            {
            	$fileName = strtolower($parts[3]) . ".{$parts[1]}.php";
            }
        }
		
		$class = strtolower($class); //类名不区分大小写,但是下标区分
		if(isset(self::$_maplist[$class])){ 
			include self::$_maplist[$class]; //类同名,不同路径,这里还为解决
			if(class_exists($class) || interface_exists($class)) return true; 
			//如果不存在,继续按以下流程找
		} 
		
        foreach ($paths as $k => $v)
        {
            $path = ($v . DIRECTORY_SEPARATOR . $fileName);
            if (file_exists($path)) //文件不存在下,file_exists比is_file快很多
            {
				include $path; 
				if(class_exists($class) || interface_exists($class)){
					self::$_addflag = 1; 
					self::$_maplist[$class] = $path; 
					return true; 
				}else{
					die("找不到：[$class] $path 类库！");	
				}
			} 
        }
    }

    /**
     * 注册要自动加载的路径
     * 注意：如果遍历的目录或文件太多有可能会影响性能，所以请尽量不要在不必要的目录里遍历
     *
     * @param string $prefix    类名前缀
     * @param string $path      要自动加载的路径
     * @param bool   $traversal 是否遍历注册的路径内的所有文件夹，false为不遍历
     */
    public static function registerPrefix($prefix, $path, $traversal = false)
    {
		if (!file_exists($path))
        {
			die('找不到：' . $path . ' 库路径！');
		}

        if($traversal)
        {
				$iterator = new DirectoryIterator($path);
				foreach($iterator as $it_path)
				{
					if(@$it_path->isDir() && !$it_path->isDot())
					{
						//针对本地开发版去掉相关目录,其它目录,需要忽略直接在这里加?!
						if(in_array($it_path->getFileName(),array('.svn','_svn','.git','_git'))){ 
							continue;
						}
						self::registerPrefix($prefix, $it_path->getPathname(), $traversal);
					}
				}
			}
        self::$_prefixes[$prefix][] = $path;
    }

    /**
     * 创建单例加载对象
	 * 注意：要保证new个对象；并用它调用一个方法__construct()；才会执行析构函数
     */
    public static function setup()
    {
		if(empty(self::$_Loader)){
			self::$_Loader = new self();
		}
    }
    
    /**
     * 缓存 自动加载路径类文件map (更新“系统内置缓存”时清理)
     * @param array $type 缓存类别
	 * @param array $cacarr 缓存数组
     */
    public static function saveCacheMap($type,$cacarr)
    {
		$mapfile = "autoload_{$type}map";
		//文件缓存 $mapfile = _08_USERCACHE_PATH.'autoload_filemap.php';
		$cacstr = "<?php\n\$$mapfile = ".var_export($cacarr,TRUE)." ;";
		// 7 => 'E:\\webs\\08svn\\auto_v60\\extend_auto\\libs\\classes\\ajax',
		$mroot = str_replace(array("\\\\","\\"), array("/","/"), M_ROOT);
		$cacstr = str_replace(array("\\\\","\\"), array("/","/"), $cacstr);
		$cacstr = str_replace("'".$mroot, "M_ROOT.'", $cacstr);
		$cacstr = str_replace(array("//"), array("/"), $cacstr);
		$filename = _08_USERCACHE_PATH.$mapfile.'.php';
		$handle = @fopen($filename,"wb");
		if($handle){
			$re = fwrite($handle,$cacstr);
			fclose($handle);
		}else{
			die("缓存无法写入".$filename);
		}
		/*
		$re = file_put_contents(_08_USERCACHE_PATH.$mapfile.'.php',$cacstr);
		if(false === $re){
			die("缓存无法写入"._08_USERCACHE_PATH.$mapfile);
        }*/
    }

    /**
     * 自动路径配置
     * 
     * @param array $configs 要加入自动加载路径的配置前缀与路径
     * @since nv50
     */
    public static function autoLoadPathConfigs( array $configs )
    {
        foreach( $configs as $prefix => $paths )
		{
		    if ( is_array($paths) )
            {
                $path_arr = $paths;
            }
            else
            {
            	$path_arr = explode(',', $paths);
            }
			
			foreach( $path_arr as $path )
			{
				$split_path = explode('|', $path);
                $split_path[0] = trim($split_path[0]);
                # 过滤根目录（注：该自动加载必须是放本程序根目录下）
				if( 0 === strpos($split_path[0], M_ROOT) )
				{
					$path = ( M_ROOT . str_replace(array(M_ROOT, '.'), array('', DS), $split_path[0]) );
				}
                else
                {
                    $path = (M_ROOT . str_replace('.', DS, $split_path[0]));
                }
				self::registerPrefix( $prefix, $path, (isset($split_path[1]) && $split_path[1] ? true : false) );
			}
		}
    }
    
    /**
     * 开始自动注册
     * 
     * @since nv50
     */
    public static function autoLoadRegister()
    {
		spl_autoload_register(array('_08_Loader', 'load'));
		spl_autoload_register(array('_08_Loader', '_autoload')); 
    }
    

    
    /**
     * 引入一个文件，该文件从根目录算起
     * 
     * @param  string $file   要引入的文件
     * @param  array  $params 要散列的参数
     * @param  string $ext    文件后缀
     * 
     * @return mixed        引入成功返回引入文件所返回的信息，失败返回FALSE
     */
    public static function import( $file, $params = array(), $ext = '.php' )
    {
        $params = (array) $params;
        $file = (M_ROOT . str_replace(array(M_ROOT, ':'), array('', DS), $file) . $ext);
        if ( is_file($file) )
        {
            empty($params) || extract($params);
            return (include_once $file); 
        }
        
        return false;
    }
	
    /**
     * 析构方法, 如果进程中有增加的类map，则更新缓存
     * 
     * @return null 
     */
	 
	function __destruct(){
		if(!empty(self::$_addflag)){
			$m_excache = cls_excache::OneInstance();
			if($m_excache->enable) $m_excache->set(md5('autoload_filemap'),self::$_maplist); //m_excache缓存
			self::saveCacheMap('file',self::$_maplist); //文件缓存
		}
	}
	
}