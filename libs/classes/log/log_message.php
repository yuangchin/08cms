<?php
/**
 * @package     08CMS.Platform
 * @subpackage  Log
 * @author      Wilson <Wilsonnet@163.com>
 * @copyright   Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
defined('M_COM') || exit('No Permisson');
class _08_Log_Message
{
    public $__category = '';
    
    public $__message = '';
    
    public $__date;
    
    public $__level = _08_Log::INFO;
    
    protected $_levels = array(
		_08_Log::EMERGENCY,
		_08_Log::ALERT,
		_08_Log::CRITICAL,
		_08_Log::ERROR,
		_08_Log::WARNING,
		_08_Log::NOTICE,
		_08_Log::INFO,
		_08_Log::DEBUG
	);
    
    public function __construct( $message, $level, $category, $date )
    {
        $this->__message = (string) $message;
        
        if (!in_array($level, $this->_levels, true))
		{
			$level = _08_Log::INFO;
		}
        $this->__level = $level;
        
        if ( !empty($category) )
		{
			$this->__category = (string) strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $category));
		}
        
        $this->__date = date('Y-m-d H:i:s', time());
    }
}