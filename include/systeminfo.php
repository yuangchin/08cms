<?php
/**
 * 系统配置信息
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class _08_SystemInfo
{
    private static $instance = null;
    
    public $info = null;
    
    public function &getInfo()
    {
        global $cms_version;
        if ( is_null($this->info) )
        {
            $this->info = array();
            $this->info['server'] = (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE'));
            $this->info['phpversion'] = phpversion();
            $this->info['dbversion']  = _08_factory::getDBO()->version();
            $this->info['useragent']  = @$_SERVER['HTTP_USER_AGENT'];
            $this->info['cmsversion'] = $cms_version;
            $this->info['os']         = PHP_OS;
            $this->info['copyright']  = <<<EOT
##
# @package		08CMS
# @copyright	Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
##\r\n
EOT;
        }
        
        return $this->info;
    }
    
    private function __clone() {}
    
    private function __construct() {}
    
    public static function getInstance()
    {
        if ( !(self::$instance instanceof self) )
        {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
}