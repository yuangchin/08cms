<?php
/**
 * @package    08CMS.Platform
 * @subpackage 文件系统(FileSystem)，文件处理类
 *
 * @author     Wilson
 * @copyright  Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */

class _08_FilesystemFile
{
    /**
     * 要删除的文件扩展，如果不指定则删除所有
     *
     * @var   private
     * @since 1.0
     */
    private $exts = array();

    /**
     * 当前操作的文件路径
     *
     * @var   private
     * @since 1.0
     */
    private $path_file = '';

    /**
     * 当前操作的文件指针
     *
     * @var   private
     * @since 1.0
     */
    private $fp = null;

    /**
     * 所有已经打开的文件指针
     *
     * @var    private
     * @static 
     * @since  1.0
     */
    private static $fps = array();
    
    private static $instance = null;

    /**
     * 只允许在当前系统中进行文件操作，设为false则允许跨系统操作(需要进一步完善??)
     *
     * @var   private
     * @since 1.0
     */
    private $OnlyInNowSystem = true;
	
    /**
     * 删除一个文件
     *
     * @param  string $path_file 如果删除成功返回TRUE，否则返回FALSE
     * @param  string $exts      允许删除的扩展，不设置则允许所有扩展
     *
     * @return bool              删除成功返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public function delFile($path_file, $exts = '')
    {
        if(empty($path_file)) return false;
        _08_FileSystemPath::filterPath($path_file);
        
        // 定义允许删除的文件扩展，如果不限则留空
        if(!empty($exts)) 
        {
            if(is_array($exts)) {
                $this->exts = $exts;
            } else if(is_string($exts)) {
                $this->exts = explode(',', $exts);
            } else {
                die('第二个参数有误！');
            }
        }
        $this->path_file = $path_file;
        /* 详情请查看：{@link http://docs.php.net/manual/zh/class.splfileinfo.php} */
        $fileinfo = self::getFileInfoObject($path_file);
        if(!$fileinfo->isFile()) return false;
        $ext = explode('.', $this->path_file);
        $ext = $ext[count($ext) - 1];
        
        if(empty($exts) || in_array($ext, $this->exts))
        {
            if(false !== strpos($this->path_file, '..')) 
            {
                return false;
            }
            
            return @unlink($this->path_file);
        }
    }
    
    /**
     * 获取文件信息对象
     * 
     * @param  string $file 要获取的文件
     * @return object       返回获取到的文件信息对象
     * 
     * @since  nv50
     */
    public static function getFileInfoObject( $file )
    {
        return new SplFileInfo($file);
    }

    /**
     * 清除目录文件
     *
     * @param  string $path      要清空的目录
     * @param  string $exts      允许删除的扩展，不设置则允许所有扩展
     * @param  bool   $traversal 是否对该目录遍历，TRUE为遍历，FALSE不遍历
     *
     * @return bool              清空成功返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public function cleanPathFile($path, $exts = '', $traversal = false)
    {
        if(empty($path)) return false;
        $path = M_ROOT . str_replace(array(M_ROOT, '.'), array('', DIRECTORY_SEPARATOR), $path);
        try {
            /* 详情请查看：{@link http://docs.php.net/manual/zh/class.directoryiterator.php} */
            $iterator = new DirectoryIterator($path);
            foreach($iterator as $it)
            {
                if($traversal && $it->isDir() && !$it->isDot()) {
                    $this->cleanPathFile($it->getPathname(), $exts, true);
                }
                if($it->isFile()) {
                    $this->delFile($it->getPathname(), $exts);
                }
            }
            return true;
        } catch (RuntimeException $e) {
            die('系统发生错误，请检查路径是否存在！');
        }
    }

    /**
     * 以fopen方式创建一个文件
     * {@link  http://docs.php.net/manual/zh/function.fopen.php}
     *
     * @param string   $filename         要创建的文件名
     * @param string   $mode             指定所要求到该流的访问类型,为移植性考虑，
     *                                   强烈建议在用 fopen() 打开文件时总是使用 'b' 标记
     * @param bool     $rewind           是否重置文件指针
     * @param bool     $use_include_path 如果需要在 include_path 中搜寻文件的话，
     *                                   可以将该参数 use_include_path 设为 '1' 或 TRUE
     * @param resource $context          在 PHP 5.0.0 中增加了 对上下文(Context)的支持
     * @since 1.1
     */
    public function _fopen($filename, $mode, $rewind = true, $use_include_path = false, $context = null)
    {
        $filename = M_ROOT . str_replace(array(M_ROOT, ':'), array('', DS), $filename);
        #文件操作非法时退出
        if($this->OnlyInNowSystem && !self::checkFile($filename) )
        {
            return false;
        }
        
        $file_name = md5($filename.$mode.$use_include_path);
        /** 
         * 如果文件指针已经存在，则直接调用，不重新打开文件。
         * 防止有些人错误的在循环里打开同一个文件，如：
         * while($i < 10) { $file->_fopen('file.txt')....}
         */
        if ( empty(self::$fps[$file_name]) || !is_resource(self::$fps[$file_name])) {
            if ( is_resource($context) ) {
                $this->fp = @fopen($filename, $mode, $use_include_path, $context);
            } else {
                $this->fp = @fopen($filename, $mode, $use_include_path);
            }
            self::$fps[$file_name] = $this->fp;
        } else {
            $this->fp = self::$fps[$file_name];
        }
        
        $rewind && $this->_rewind();
        $this->path_file = $filename;
        return $this->fp;
    }

    /**
     * 以二进制方式写入数据到文件
     * {@link   http://docs.php.net/manual/zh/function.fwrite.php}
     *
     * @param  mixed $string 要写入的数据字符串，以数组形式传递则可自动调用$this->_fopen打开文件
     * @param  int   $length 数据字符串长度，不指定或指定0则长度为$string长度
     * @param  bool  $fclose 写完是否关闭文件指针，TRUE为关闭，FALSE为不关闭
     * 
     * @return bool           返回写入的字符数，出现错误时则返回 FALSE
     * @since  1.0
     */
    public function _fwrite($string, $length = 0, $fclose = false)
    {
        if( !is_resource($this->fp) && empty($string['file']) )
        {
            return false;
        }
       
        # 如果$string以数组形式传递则可自动调用$this->_fopen打开文件
        if ( is_array($string) && !empty($string['file']) )
        {
            $string['file'] = M_ROOT . str_replace(array(M_ROOT, ':'), array('', DS), $string['file']);
            empty($string['mode']) && $string['mode'] = 'wb';
            
            $this->fp = $this->_fopen($string['file'], $string['mode']);
            
            if( isset($string['close']) && $string['close'] )
            {
                $fclose = (bool) $string['close'];
            }
            isset($string['length']) && ($length = $string['length']);
            if ( isset($string['string']) )
            {
                $string = $string['string'];
            }
            else # 生成一个空文件
            {
            	$string = '';
            }
        }
        
        if(0 == $length) $length = strlen($string);
        $fwrite = fwrite($this->fp, (string)$string, (int)$length);
        $fclose && $this->_fclose();
        return $fwrite;
    }

    /**
     * 以二进制方式读取某个文件数据
     * {@link   http://docs.php.net/manual/zh/function.fread.php}
     *
     * @param  int  $length 要读取取的数据长度，不指定则获取文件大小
     * @param  bool $fclose 读完是否关闭文件指针，TRUE为关闭，FALSE为不关闭
     * 
     * @return              返回所读取的字符串， 或者在失败时返回 FALSE。
     * @since  1.0
     */
    public function _fread($length = 0, $fclose = false)
    {
        if(!is_resource($this->fp) || !is_file($this->path_file)) 
        {
            return false;
        }
        if(0 == $length) $length = filesize($this->path_file);
        $fread = @fread($this->fp, (int)$length);
        $fclose && $this->_fclose();
        return $fread;
    }

    /**
     * 从文件指针中读取一行
     * {@link   http://docs.php.net/manual/zh/function.fgets.php}
     *
     * @param  int  $length 要读取取的数据长度，不指定则为1024字节
     * @param  bool $fclose 读完是否关闭文件指针，TRUE为关闭，FALSE为不关闭
     * 
     * @return              返回所读取的字符串， 或者在失败时返回 FALSE。
     * @since  nv50
     */
    public function _fgets($length = 1024, $fclose = false)
    {
        if(!is_resource($this->fp) || !is_file($this->path_file)) 
        {
            return false;
        }
        
        $fread = @fgets($this->fp, (int)$length);
        $fclose && $this->_fclose();
        return $fread;
    }

    /**
     * 锁定一个文件（注：如果使用了该函数，记得用完后要解锁，否则会出现死锁现象）
     *
     * @param  int  $operation  锁定模式，默认取得独占锁定（写入的程序)
     * @param  int  $wouldblock 如果锁定会堵塞的话（EWOULDBLOCK 错误码情况下），
     *                          该参数会被设置为 TRUE。（Windows 上不支持）
     * @return bool             成功时返回 TRUE， 或者在失败时返回 FALSE
     * @since  1.0
     */
    public function _flock($operation = LOCK_EX, $wouldblock = 0)
    {
        if(!is_resource($this->fp)) 
        {
            return false;
        }

        if(0 == $wouldblock) {
            return @flock($this->fp, $operation);
        } else {
            return @flock($this->fp, $operation, $wouldblock);
        }
    }
    
    /**
     * 返回文件指针读/写的位置
     * 
     * @return int  文件指针读/写的位置
     * 
     * @since  nv50
     */
    public function _ftell()
    {
        if ( is_resource($this->fp) )
        {
            return ftell($this->fp);
        }
        
        return false;        
    }    
    
    /**
     * 偏移文件指针
     * {@link http://docs.php.net/manual/zh/function.fseek.php}
     * 
     * @param int $offset 偏移量
     * @param int $whence SEEK_SET - 设定位置等于 offset 字节。
     *                    SEEK_CUR - 设定位置为当前位置加上 offset。
     *                    SEEK_END - 设定位置为文件尾加上 offset。
     * 
     * @todo 该功能有待完成
     **/
    public function _fseek($offset, $whence = SEEK_SET) 
    {
        if ( is_resource($this->fp) )
        {
            return fseek($this->fp, $offset, $whence);
        }
        return false;
    }
    
    /**
     * 倒回文件指针的位置
     * 
     * @return bool 如果倒回成功返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public function _rewind()
    {
        if ( is_resource($this->fp) )
        {
            return rewind($this->fp);
        }
        return false;
    }
    
    /**
     * 验证当前操作的文件正确性
     * 
     * @param  string $file 要验证的文件
     * @return bool         验证通过返回TRUE，否则返回FALSE
     * 
     * @since  1.0
     */
    public static function checkFile( $file )
    {
        # 防止跳出本系统根目录或其它目录操作文件
        if ( (0 !== stripos($file, M_ROOT)) || (false !== strpos($file, '..')) )
        {
            return false;
        }
        
        return true;
    }
    /**
     * 将文件操作设定为可跨系统进行操作，只用于特殊用途
     * 
     * @since  1.0
     */
    public function AllowOutOfSystem(){
		$this->OnlyInNowSystem = false;
    }
    
    /**
     * 关闭当前文件指针
     * 
     * @return 如果文件未打开或关闭失败返回FALSE，否则返回TRUE  
     * @since  1.0
     */
    public function _fclose()
    {
        if(is_resource($this->fp)) 
        {
            return (bool) fclose($this->fp);
        }
        
        return false;
    }
    
    /**
     * _08_FilesystemFile::__construct()
     * 
     * @return
     */
    private function __construct() {}
    
    /**
     * _08_FilesystemFile::__clone()
     * 
     * @return
     */
    private function __clone(){}

	/**
	 * _08_FilesystemFile::getInstance()
	 * 
	 * @return
	 */
	public static function getInstance()
    {
		if(! (self::$instance instanceof self))
        {
			self::$instance = new self();
		}
        
		return self::$instance;
	}
    
    /**
     * 如果文件指针已经打开则自动关闭文件指针
     *
     * @since 1.0
     */
    public function __destruct()
    {
        if(is_resource($this->fp)) 
        {
            fclose($this->fp);
        }
    }
    
    public static function debug()
    {
        $params = func_get_args();
        file_put_contents(M_ROOT . 'debug.txt', var_export($params, true), FILE_APPEND);
    }
	
	/**
	 * 文件名限制，控制文件系统安全
	 *
	 * @param  string  	$FileName 	文件名称
	 * @param  array  	$AllowExtArray	允许使用的扩展名
	 * @return string  	$str   		返回限制原因，能过验证则返回空
	 */
	public static function CheckFileName($FileName,$AllowExtArray = array('htm','html')){
		if(!$FileName) return '请指定文件名';
		if(preg_match("/[^a-z_A-Z0-9\.\-]+/",$FileName)) return '文件名称只允许包含字母、数字、下划线(-_)、点(.)等字符';
		$FileName = strtolower($FileName);
		if(!$AllowExtArray) $AllowExtArray = array('htm','html');
		if(!is_array($AllowExtArray)) $AllowExtArray = array($AllowExtArray);
		if(!($ext = mextension($FileName))) return '请指定文件扩展名';
		if(!in_array($ext,$AllowExtArray)) return '扩展名只允许为：'.implode(',',$AllowExtArray);
		return false;
	}
	
    /**
     * 过滤不合法的文件参数（不包含文件后缀的文件名参数）
     * 
     * @param  string $fileparam 要过滤的文件参数
     * @since  1.0
     */
    public static function filterFileParam( &$fileparam )
    {
        $fileparam = preg_replace('/[^\w\-\.]/', '', $fileparam);
    }
}