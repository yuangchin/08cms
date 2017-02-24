<?php
/**
 * 日志系统单例类
 * 
 * @package     08CMS.Platform
 * @subpackage  Log
 * @author      Wilson <Wilsonnet@163.com>
 * @copyright   Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 * @example     # 该调用非必须，'types' => 'database' 为把日志存储到数据库（注：该功能目前未实现），默认该值为 'file'
 *              _08_Log::addLogger( array('category' => '类别') );  
 *              _08_Log::add('测试日志操作');    # 一般情况只调用该语句即可
 */
defined('M_COM') || exit('No Permisson');
define('_08_LOG_PATH', _08_CORE_API_PATH . 'log' . DS);

class _08_Log
{
    /**
     * 当前句柄对象
     *
     * @var   object
     * @since 1.0
     */
    protected static $_instance = null;
    
    /**
     * 紧急性信息
     * 
     * @var   int
     * @since 1.0
     */
    const EMERGENCY = 1;
    
    /**
     * 警惕性信息
     * 
     * @var   int
     * @since 1.0
     */
    const ALERT = 2;
    
    /**
     * 危急性信息
     * 
     * @var   int
     * @since 1.0
     */
    const CRITICAL = 4;
    
    /**
     * 错误性信息
     * 
     * @var   int
     * @since 1.0
     */     
    const ERROR = 8;
    
    /**
     * 警告性信息
     * 
     * @var   int
     * @since 1.0
     */    
    const WARNING = 16;
    
     /**
     * 注意性信息
     * 
     * @var   int
     * @since 1.0
     */
    const NOTICE = 32;
    
     /**
     * 信息性信息
     * 
     * @var   int
     * @since 1.0
     */   
    const INFO = 64;
    
    /**
     * 调试性信息
     * 
     * @var   int
     * @since 1.0
     */
    const DEBUG = 128;
    
    /**
     * 不区分性质信息，类似于PHP错误的E_ALL级别
     * {@link http://php.net/manual/en/errorfunc.constants.php}
     */
    const ALL = 30719;
    
    protected $_lookup = array();
    
    protected $_loggers = array();
    
    protected $_configs = array();
    
    /**
     * 添加日志
     * 
     * @param mixed  $message  日志信息或是信息对象
     * @param int    $level    信息级别
     * @param string $category 日志信息分类
     * @param int    $date     日期，如果该值为null时则设置为当前日期
     * 
     * @since 1.0
     */
    public static function add( $message, $level = self::INFO, $category = 'syslog', $date = null )
    {        
        if ( !($message instanceof _08_Log_Message) )
		{
			$message = new _08_Log_Message( (string) $message, $level, $category, $date );
		}
        
        if ( !(self::$_instance instanceof self) )
		{
			self::setInstance(new self);
		}
        
        # 如果外部未设置执行的日志类则自动初始化一个日志类，默认把日志存放到文件里
        if ( empty(self::$_instance->_lookup) )
        {
            self::addLogger( array('types' => 'file'), $level, array($category) );
        }
        
        self::$_instance->addLogMessage( $message );
    }
    
    /**
     * 添加日志信息头
     * 
     * @param array $options    配置信息
     * @param int   $level      信息级别
     * @param array $categories 日志分类数组
     * 
     * @since 1.0
     */
    public static function addLogger( array $options, $level = self::ALL, $categories = array() )
    {
        if ( !(self::$_instance instanceof self) )
		{
			self::setInstance(new self);
		}
        
        # 如果未定义要使用的头后缀则默认一个
        if ( empty($options['types']) )
		{
			$options['types'] = 'file';
		}
        
        $signature = md5(serialize($options));
        
        if ( empty(self::$_instance->_configs[$signature]) )
		{
			self::$_instance->_configs[$signature] = $options;
		}
        
        self::$_instance->_lookup[$signature] = (object) array(
			'level' => $level,
			'categories' => array_map('strtolower', (array) $categories)
        );
    }
    
    /**
     * 开始添加日志信息
     * 
     * @param object $message 日志信息对象句柄{@see _08_LogMessage}
     */
    public function addLogMessage( _08_Log_Message $message )
    {
		$loggers = $this->findLoggers($message->__level, $message->__category);
        
        foreach ( (array) $loggers as $signature )
        {
            if ( empty($this->_loggers[$signature]) )
            {
                $class_name = '_08_Logger_To_' . ucfirst($this->_configs[$signature]['types']);                
                if ( class_exists($class_name) )
				{
				    # 构造日志类对象并传递配置信息
					$this->_loggers[$signature] = new $class_name($this->_configs[$signature]);
				}
				else
				{
					throw new _08_Log_Exception('无法创建日志记录信息头对象');
				}
            }
            
            $this->_loggers[$signature]->addMessage($message);
        }
    }
    
    /**
     * 寻找日志记录信息头
     * 
     * @param  int    $level    日志级别
     * @param  string $category 日志类型
     * @return array  $loggers  返回日志记录信息头数组
     * 
     * @since  1.0
     */
    public function findLoggers( $level, $category )
    {
        $loggers = array();
        $level = (int) $level;
        $category = strtolower($category);
        
        foreach ((array) $this->_lookup as $signature => $rules)
        {
            if ($level & $rules->level)
            {
				if ( empty($category) || empty($rules->level) || in_array($category, $rules->categories) )
				{
					$loggers[] = $signature;
				}
            }
        }
        
        return $loggers;
    }
    
    /**
     * 设置当前操作句柄
     *
     * @param object $instance 操作句柄
     * @since 1.0
     */
    public static function setInstance( $instance )
    {
        if( ($instance instanceof self) || (null == $instance) )
        {
            self::$_instance = & $instance;
        }
    }

    /**
     * 防止外部实例化
     *
     * @since 1.0
     */
    protected function __construct() {}
    
    /**
     * 防止对象被克隆
     *
     * @since 1.0
     */
    protected function __clone() {}
}