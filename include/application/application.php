<?php
/**
 * 应用集合操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || define('_08CMS_APP_EXEC', true);
class _08_Application
{
    /**
     * 是否使用新架构
     * 
     * @var   bool
     * @since 1.0
     */
    public static $__isNewStructure = false;
    
    /**
     * 应用句柄集
     * 
     * @var   array 
     * @since 1.0
     */
    protected static $_instances = array();
    
    /**
     * 获取应用对象
     * 
     * @param  mixed  $client 客户端标识符或名称
     * @param  array  $config 配置数组
     * @param  string $prefix 应用前缀
     * 
     * @return object         返回应用对象句柄，如果获取不成功返回null
     * @since  1.0
     */
    public static function getInstance($client, $config = array(), $prefix = '_08')
    {
        $key = md5($prefix . $client . serialize( array($config) ));
        try
        {
            /**
             * 使用前端控制器(MVC架构模式)进入应用，一般用于从浏览器、CLI方式打开的应用
             */
            if ( is_null($client) )
            {
                self::$_instances[$key] = new self();
                $front = cls_frontController::getInstance();
                $front->route();
            }
            else
            { 
                /**
                 * 自定义方式进入应用，一般用于类似于CLI之类的控制台使用（注：该方式使用资源会比较少，
                 * 所以有时不需要这么多资源请求时的应用可用该方式进入）
                 */
            	if (empty(self::$_instances[$key]))
                {
                    $class_name = $prefix . $client;
                    if ( class_exists($class_name) )
                    {
                        self::$_instances[$key] = new $class_name($config);
                    }
                    else 
                    {
                        cls_HttpStatus::trace(500);
                        self::$_instances[$key] = null;
                    }
                }
            }
        }
        catch (_08_ApplicationException $error)
        {
            die($error->getMessage());
        }    
        
        return self::$_instances[$key];
    }
    
    /**
     * 运行判断是否使用新架构
     */
    public function run()
    {
        if ( self::$__isNewStructure )
        {
            return true;
        }
        
        return false;
    }
}