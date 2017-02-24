<?php
/**
 * 性能分析操作类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2012, 08CMS Inc. All rights reserved.
 */
defined('M_COM') || exit('No Permission');
class _08_Profiler
{
    /**
     * 当前对象句柄集
     *
     * @var    array
     * @static
     * @since  1.0
     */
    private static $instance = array();

    /**
     * 获取程序开始执行时间
     *
     * @var   int
     * @since 1.0
     */
    protected $_start_time = 0;

    /**
     * 在输出中要使用的前缀
     *
     * @var   string
     * @since 1.0
     */
    protected $_prefix = '';

    /**
     * 获取信息到缓冲区
     *
     * @var   array
     * @since 1.0
     */
    protected $_buffer = array();

    /**
     * 存储上一段内存
     *
     * @var   float
     * @since 1.0
     */
    protected $_previous_mem = 0.0;

    /**
     * 存储上一段时间
     *
     * @var   float
     * @since 1.0
     */
    protected $_previous_time = 0.0;

    /**
     * 判断服务器系统是否为WIN
     *
     * @var   bool
     * @since 1.0
     */
    protected $_iswin = true;

    public function __construct($prefix = '') 
    {
		$this->_start_time = $this->getMicrotime();
		$this->_prefix = $prefix;
		$this->_buffer = array();
		$this->_iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
	}

    /**
     * 格式化输出性能分析信息
     *
     * @param  string $label 信息标签
     * @return string $mark  返回分析信息
     *
     * @since  1.0
     */
	public function mark($label = '') 
    {
		$current = self::getMicrotime() - $this->_start_time;
        $current_mem = $this->getMemory();
        if(is_numeric($current_mem)) {
			// byte 转 MB
			$current_mem = $current_mem / 1048576;
    		$mark = sprintf(
    			'<code>%s %.3f seconds (+%.3f); %0.2f MB (%s%0.3f)</code>',
    			$this->_prefix,
    			$current,
    			$current - $this->_previous_time,
    			$current_mem,
    			($current_mem > $this->_previous_mem) ? '+' : '',
                $current_mem - $this->_previous_mem
    		);
        } else if(is_string($current_mem) && $current_mem != '') {
    		$mark = sprintf(
    			'<code>%s %.3f seconds (+%.3f); %s</code>',
    			$this->_prefix,
    			$current,
    			$current - $this->_previous_time,
    			$current_mem
    		);
        } else {
            $mark = sprintf(
                '<code>%s %.3f seconds (+%.3f)</code>',
                $this->_prefix, $current,
                $current - $this->_previous_time
            );
        }

		$this->_previous_time = $current;
		$this->_previous_mem = $current_mem;
		$this->_buffer[] = $mark;

		return $mark . (empty($label) ? '' : " - $label");
	}

    /**
     * 获取当前进程所使用的内存信息
     *
     * @return int 返回使用的内存信息
     *
     * @since  1.0
     */
    public function getMemory() 
    {
		if (function_exists('memory_get_usage')) 
        {
            // 该函数返回的只是PHP内存状况，返回单位：bytes  170672
			return memory_get_usage();
		} 
        else 
        {
			$output = array();
            // 获取当前进程PID
			$pid = getmypid();

            /**
             * 由于在生产环境中exec函数有可能被屏蔽，所以可能返回不成功
			 * 以下如果是WIN系统，如果执行的是这步则返回string，并且返回的是服务器软件（如APACHE）+ PHP内存状况
             * 除非使用的是CGI这类单独进程运行的PHP
             */
			if ($this->_iswin) 
            {
				@exec('tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output);
				if (!isset($output[5])) 
                {
					$output[5] = null;
				} 
                else 
                {
				    $split = explode(':', $output[5]);
                    // 格式：20,916 K
                    $output[5] = $split[1];
				}
				return @$output[5];
			} 
            else 
            { // 其它系统
				@exec("ps -o rss -p $pid", $output);
				return @$output[1] * 1024;
			}
		}
	}

    /**
     * 获取程序当前执行时间
     *
     * @static
     * @return float 返回当前浮点数时间值
     *
     * @since  1.0
     */
    public static function getMicrotime() 
    {
		list ($usec, $sec) = explode(' ', microtime());

		return ((float) $usec + (float) $sec);
	}

    /**
     * 获取程序运行时间
     *
     * @return float 返回程序运行时间
     * @since  1.0
     */
    public function getEndTime() 
    {
        return self::getMicrotime() - $this->_start_time;
    }

    /**
     * 获取保存到缓冲区的性能分析信息
     *
     * @return array 缓冲区信息数组
     *
     * @since  1.0
     */
	public function getBuffer() 
    {
		return $this->_buffer;
	}

    /**
     * 生成一个调试回溯信息
     * (PHP 4 >= 4.3.0, PHP 5)
     * {@link http://docs.php.net/manual/zh/function.debug-backtrace.php}
     *
     * @param  string $in_args      该参数不为空时返回该值存在$in_args的信息数组
     * @param  bool   $debug_enable 是否开启生成调试信息
     * @return array  $backtrace    返回一个存储回溯信息的数组
     * @since  1.0
     */
    public function getDebugBacktrace($in_args = '', $debug_enable = true)
    {
        if ( !$debug_enable ) return false;
        
        if ( function_exists('debug_backtrace') )
        {
            $backtrace = debug_backtrace();
		    $index = count($backtrace) - 1;
			if (isset($backtrace[$index]['file']))
			{
				self::replaceBacktracePath($backtrace[$index]['file']);
			}
            
			if ( !isset($backtrace[$index]['line']) )
			{
				$backtrace[$index]['line'] = '';
			}
            
			if ( isset($backtrace[$index]['args']) && is_array($backtrace[$index]['args']) )
			{
			    $args_index = count($backtrace[$index]['args']) - 1;
				self::replaceBacktracePath($backtrace[$index]['args'][$args_index]);
                $backtrace[$index]['args'] = $backtrace[$index]['args'][$args_index];
			}
            
			if ( !isset($backtrace[$index]['class']) )
			{
				$backtrace[$index]['class'] = '';
			}
            
			if ( !isset($backtrace[$index]['function']) )
			{
				$backtrace[$index]['function'] = '';
			}
            
			if ( !isset($backtrace[$index]['type']) )
			{
				$backtrace[$index]['type'] = '';
			}
            
			if ( !isset($backtrace[$index]['object']) )
			{
				$backtrace[$index]['object'] = null;
			}
            
            return $backtrace[$index];
        }
        
        return self::debugPrintBacktrace();  
    }
    
    /**
     * 获取关于最后一个发生的错误的信息
     * (PHP 5 >= 5.2.0)
     * {@link http://docs.php.net/manual/zh/function.error-get-last.php}
     * 
     * @return array $error_get_last 如果有错误发生则返回最后生成错误的代码信息，否则返回null
     */
    public static function getLastError()
    {
        $error_get_last = null;
        if ( function_exists('error_get_last') )
        {
            $error_get_last = error_get_last();
        }
        
        return $error_get_last;
    }
    
    /**
     * 打印一条回溯
     * 
     * @return string $debug_print_backtrace 返回一条打印的回溯信息
     */
    public static function debugPrintBacktrace()
    {
        $debug_print_backtrace = '';
        if ( function_exists('debug_print_backtrace') )
        {
            #ob_end_clean();
            ob_start();
            debug_print_backtrace();
            $debug_print_backtrace = ob_get_contents();
            ob_end_clean();
            self::replaceBacktracePath($debug_print_backtrace);
        }
        
        return $debug_print_backtrace;
    }
    
    /**
     * 获取一个出错回调信息
     * 
     * @param  string $string        传入一个出错信息的原语句
     * @return string $error_filestr 返回回调信息
     * 
     * @since  1.0
     */
    public static function getDebugBacktraceMessage( $string )
    {
        $error_filestr = '';
		$error_info = self::getDebugBacktrace($string);
		if( !empty($error_info) )
		{
			if ( is_string($error_info) || empty($error_info['file']) || empty($error_info['line']) )
			{
				$error_filestr = '<br />' . $error_info;
			}
			else
			{
                if ( empty($error_info['function']) )
                {                    
                	$error_filestr = ($error_info['file'] . ' : ' . $error_info['line']);
                }
                else
                {
				    $error_filestr = ($error_info['function'] . '(' . $error_info['args'] . ') called at [' . $error_info['file'] . ' : ' . $error_info['line'] . ']');
                }
			}
		}
        
        return $error_filestr;
    }
    
    /**
     * 替换路径，尽量不暴露路径给外部
     * 该处的 / 看用什么方法代替好，既不把目录暴露给外部，又能让站长一看便知道哪个文件哪行的。
     */
    public static function replaceBacktracePath( &$path )
    {
        $path = (str_replace(array(M_ROOT, '\\'), array('/', '/'), $path));
    }

    /**
     * 获取当前对象句柄集
     *
     * @return array self::$instance 返回当前对象集
     *
     * @static
     * @since  1.0
     */
    public static function getInstance($prefix = '') 
    {
        if(empty(self::$instance[$prefix])) 
        {
            self::$instance[$prefix] = new self($prefix);
        }
        return self::$instance[$prefix];
    }
}