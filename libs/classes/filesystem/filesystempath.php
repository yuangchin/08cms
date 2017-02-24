<?php
/**
 * @package    08CMS.Platform
 * @subpackage 文件系统(FileSystem)，目录处理类
 *
 * @author     Wilson
 * @copyright  Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
class _08_FileSystemPath
{

    private $iterator = null;

    /**
     * 要处理的路径
     *
     * @var   string
     * @since 1.0
     */
    private $path;

    /**
     * 当目录不存在时是否创建
     *
     * @var    bool
     * @static
     * @since  1.0
     */
    private static $create = false;

    /**
     * 设置不了权限的目录
     *
     * @var    array
     * @static
     * @since  1.0
     */
    private static $error_path = array();

    /**
     * 要处理的路径模式，默认为0777
     *
     * @var    string
     * @static
     * @since  1.0
     */
    private static $mode = 0777;

    /**
     * 构造方法
     *
     * @param string $path   要操作的路径
     * @param bool   $create 当目录不存在时是否创建，TRUE为创建，默认为FALSE为不创建
     * @param int    $mode   默认的 mode 是 0777，意味着最大可能的访问权。
     */
    public function __construct($path, $create = false, $mode = 0777)
    {
        if(self::checkPath($path, $create, $mode)) {
            $this->path = $path;
            self::$mode = $mode;
            self::$create = $create;
            $this->iterator = new RecursiveDirectoryIterator($path);
        } else {
            die('参数必须为合法目录，并且必须存在！');
        }
    }

    /**
     * 检查目录是否存在
     *
     * @param  string $path   要检查的目录
     * @param  bool   $create 当目录不存在时是否创建，TRUE为创建，默认为FALSE为不创建
     *
     * @return bool           目录存在或创建成功时返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public static function checkPath($path, $create = false, $mode = 0777)
    {
        $path = M_ROOT . str_replace(array(M_ROOT, '.'), array('', DIRECTORY_SEPARATOR), $path);
        if(is_dir($path)) {
            return true;
        } else {
            if($create) {
                return self::Create($path, $mode);
            } else {
                return false;
            }
        }
    }
    
    /**
     * 清空目录
     * 
     * @param string $dir 要清空的目录
     * @since nv50
     */
    public static function clear( $dir )
    {        
    	$directory = dir($dir);
        $file = _08_FilesystemFile::getInstance();
    	while($entry = $directory->read()){
    		$filename = $dir.'/'.$entry;
    		if(is_file($filename)) 
            {
                $file->delFile($filename);
            }
    	}
    	$directory->close();
    	@touch($dir.'/index.htm');
    	@touch($dir.'/index.html');
    }
    
    /** 
     * 将回调函数作用到给定目录上
     * 
     * @param  callable $function   回调函数或名称，具体可看：{@link http://docs.php.net/manual/zh/language.types.callable.php}
     * @param  string   $path       要应用回调函数的目录
     * @param  bool     $traversal  是否遍历目录，TRUE为遍历，FALSE为不遍历
     * @static array    $returnInfo
     * @return array    $returnInfo 返回callback函数的返回值汇总
     * 
     * @since  nv50
     **/
    public static function map($function, $path, $traversal = true)
    {
        static $returnInfo = array();
        if ( is_dir($path) )
        {
            $iterator = new DirectoryIterator($path);
            foreach ( $iterator as $fileInfo )
            {
                if ( !$fileInfo->isDot() && (strrchr($fileInfo->getPathname(), '.') != '.svn') )
                {
                    if ( $fileInfo->isDir() && $traversal )
                    {
                        self::map($function, $fileInfo->getPathname(), $traversal);
                    }
                    else
                    {
                        $returnInfo[] = call_user_func($function, $fileInfo);
                    }
                }
            }
        }
        
        return $returnInfo;
    }

    /**
     * 创建目录
     *
     * 直接传递$path参数，以便外部可不通过构造本类而直接调用方法创建目录
     *
     * @param  string $path   要创建的目录
     * @param  int    $mode   默认的 mode 是 0777，意味着最大可能的访问权。
     * @return bool           目录存在或创建成功时返回TRUE，否则返回FALSE
     * @link   http://docs.php.net/manual/zh/function.mkdir.php
     * @since  1.0
     */
    public static function Create($path, $mode = 0777)
    {
        if(is_dir($path)) {
            return true;
        } else {
            $path_str = '';
            $parts = preg_split('@[\\\|/].*?@', $path);
            if(is_array($parts)) {
                foreach ($parts as $path) {
                    $path_str .= $path . DS;
                    if(!is_dir($path_str)) {
                        @mkdir($path_str, $mode);
                    }
                }
            }
            if(is_dir($path_str)) {
                file_put_contents(
                    $path_str . 'index.html',
                    "<html>\n\t<body bgcolor=\"#FFFFFF\">\n\t</body>\n</html>"
                );
                file_put_contents(
                    $path_str . 'index.htm',
                    "<html>\n\t<body bgcolor=\"#FFFFFF\">\n\t</body>\n</html>"
                );
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 检查路径权限是否可修改
     *
     * @param  string $path 要检查的路径
     * @return bool         检查路径权限是否可修改，如果可以则返回TRUE，否则返回FALSE
     * @static
     * @since  1.0
     */
    public static function checkChmod($path)
    {
        $perms = fileperms($path);

        if($perms !== false)
        {
            // 尝试变更原有权限，如果有修改权限则返回TRUE并设置回原有权限
            if(@chmod($path, $perms ^ 0001))
            {
                @chmod($path, $perms);
                return true;
            }
        }
        return false;
    }

    /**
     * 返回磁盘或目录的可用空间
     *
     * @param  string $directory 给出一个包含有一个目录的字符串
     * @return float             返回可用空间大小（单位：字节数）
     *
     * @since  1.0
     */
    public static function getDiskFreeSpace( $directory )
    {
        return disk_free_space($directory);
    }

    /**
     * 格式化字节单位
     *
     * @param  int    $bytes 要格式化的字节数
     * @return string        返回格式化后的单位信息
     * @since  1.0
     */
    public static function byteConvert($bytes)
    {
        $s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $e = floor(log($bytes)/log(1024));

        return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
    }

    /**
     * 循环递归设置目录权限
     *
     * @return mixed 如果设置成功返回空数组，否则未构造本类时返回FALSE，否则返回设置不成功的路径数组
     * @since  1.0
     */
    public function setPermissions()
    {
        if((null == $this->iterator) || (!self::checkChmod($this->path))) return false;
        foreach($this->iterator as $path)
        {
            if($path->isDir())
            {
                if(!self::checkChmod($path) || (false == @chmod($path, self::$mode)))
                {
                    self::$error_path[] = $path;
                }
                self::__construct($path, self::$create, self::$mode);
                $this->setPermissions();
            }
        }

        return self::$error_path;
    }
    
    /**
     * 过滤路径，让操作的路径保持在本系统根目录以下
     * 
     * @param  mixed $path 要过滤的路径
     * 
     * @since  1.0
     */
    public static function filterPath( &$path )
    {
        if ( is_array($path) )
        {            
            foreach($path as &$_path)
            {
                self::filterPath($_path);
            }
        }
		else
		{
			$path = M_ROOT . str_replace(array(M_ROOT), array(''), $path);
		}
    }
    
    /**
     * 过滤目录参数
     * 
     * @param string $param 要过滤的目录参数
     */
    public static function filterPathParam( $param )
    {
        $param = preg_replace('/[^\w\-]+/', '', $param);
    }
	
	/**
	 * 单个路径名称检查，控制文件系统安全
	 *
	 * @param  string  	$PathName 	文件名称
	 * @param  array  	$RegPattern	正则规则，留空为默认格式
	 * @return string  	$str   		返回限制原因，能过验证则返回空
	 */
	public static function CheckPathName($PathName,$RegPattern = ''){
		if(!$PathName) return '请指定路径名称';
		if(empty($RegPattern)) $RegPattern = "/[^\w\-]+/";
		if(preg_match($RegPattern,$PathName)) return '路径名称只允许包含字母、数字、下划线(-_)、点(.)等字符';
		return false;
	}
	
	
	
}