<?php
/**
 * 保存日志到文件
 * 
 * @package     08CMS.Platform
 * @subpackage  Log
 * @author      Wilson <Wilsonnet@163.com>
 * @copyright   Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
defined('M_COM') || exit('No Permisson');

# 暂时存放这，待日志系统完善后把该设置移到系统配置缓存
define('_08_LOG_FILE_PATH', _08_CACHE_PATH . 'log' . DS);
_08_FileSystemPath::checkPath( substr(_08_LOG_FILE_PATH, 0, -1), true );

class _08_Logger_To_File extends _08_Logger
{
    protected $_levels = array(
		_08_Log::EMERGENCY => 'EMERGENCY',
		_08_Log::ALERT => 'ALERT',
		_08_Log::CRITICAL => 'CRITICAL',
		_08_Log::ERROR => 'ERROR',
		_08_Log::WARNING => 'WARNING',
		_08_Log::NOTICE => 'NOTICE',
		_08_Log::INFO => 'INFO',
		_08_Log::DEBUG => 'DEBUG'
    );
        
    public function __construct( array &$options )
    {
        parent::__construct($options);
        
        if ( empty($options['log_file_path']) )
        {
            $options['log_file_path'] = _08_LOG_FILE_PATH;
        }
    }
    
    /**
     * 添加日志信息到文件
     * 
     * @param object $log_instance _08_Log_Message类对象句柄
     */
    public function addMessage( _08_Log_Message $log_instance )
    {
        $save_file = $this->_options['log_file_path'] . 'log_' . date('Y-m-d', time()) . '.php';
        
        empty($this->_options['category']) || $log_instance->__category = $this->_options['category'];
        if ( is_file($save_file) )
        {
            $log_message = '';
        }
        else
        {
            $log_message = <<<EOT
#<?php exit('No Permisson'); ?>\r\n
#Fields: date_time\tlevel\tclientip\tcategory\tmessage\r\n
EOT;
        }
        
        $log_message .= <<<EOT
{$log_instance->__date}\t{$this->_levels[$log_instance->__level]}\t{$this->_options['onlineip']}\t{$log_instance->__category}\t{$log_instance->__message}\r\n
EOT;
        $file = _08_FilesystemFile::getInstance();
        $file->_fopen($save_file, 'ab+');
        
        if( $file->_flock() )
        {
            $file->_fwrite($log_message);
            $file->_flock(LOCK_UN);        
        }
    }
}