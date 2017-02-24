<?php
/**
 * 日志类头
 * 
 * @package     08CMS.Platform
 * @subpackage  Log
 * @author      Wilson <Wilsonnet@163.com>
 * @copyright   Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
defined('M_COM') || exit('No Permisson');
abstract class _08_Logger
{
    protected $_options = array();
    
    public function __construct( array &$options )
	{
	    global $onlineip;
        
		# 设置类的选项
		$this->_options = & $options;
        if ( empty($this->_options['onlineip']) )
        {
            $this->_options['onlineip'] = $onlineip;
        }
	}
    
    abstract public function addMessage( _08_Log_Message $log_instance );
}