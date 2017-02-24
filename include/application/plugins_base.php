<?php
/**
 * 插件架构基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission'); 
class _08_Plugins_Base
{
    /**
     * 监听插件钩子对象句柄
     * 
     * @var array
     */
    protected static $_listeners = array();
    
    /**
     * 插件列表
     * 
     * @var array
     */
    protected static $_plugins = array();
    
    private static $instance = null;
    
    /**
     * 获取插件数据
     * 
     * @return array             存放插件数组
     * 
     * @since  nv50
     */
    public function getPluginsData()
    {
        self::$_plugins = cls_cache::Read('plugins');
        $updatetime = @filemtime(_08_PLUGINS_PATH);
        if ( empty(self::$_plugins) || ($updatetime != @self::$_plugins['updatetime']) )
        {
            self::$_plugins = array();
            _08_FileSystemPath::map(array($this, '_getPluginsData'), _08_PLUGINS_PATH, false);
            _08_FileSystemPath::map(array($this, '_getPluginsData'), _08_EXTEND_PLUGINS_PATH, false);
            self::$_plugins['updatetime'] = $updatetime;
            cls_CacheFile::Save(self::$_plugins, 'plugins');
        }
        
        return self::$_plugins;
    }
    
    /**
     * 获取插件信息
     * 暂时用遍历目录来获取，后期当要在后台编辑时考虑是否存放数据库。。
     * 
     * @param object $iterator 要获取的插件目录迭代器对象
     * @since nv50
     */
    public function _getPluginsData( DirectoryIterator $iterator )
    {
        $_plugins = array();
        if ( $iterator->isDir() && !$iterator->isDot() )
        {
            $file = _08_FilesystemFile::getInstance();
            if ( is_file($iterator->getPathname() . '_plugin.php') )
            {
                $pluginFile = ($iterator->getPathname() . '_plugin.php');
            }
            else
            {
            	$pluginFile = $iterator->getPathname() . DS . $iterator->getBasename() . '_plugin.php';
            }
            
            if ( $file->_fopen($pluginFile, 'r') )
            {
                $contents = $file->_fread(0, true);
                if ( preg_match("/Plugin\s+Name\s*:*(.*)$/im", $contents, $plugin_name) )
                {
                    $plugin_name[1] = trim($plugin_name[1]);
                    
                    $_plugins['Name'] = $plugin_name[1];
                    $_plugins['Id'] = $iterator->getBasename();
                    foreach ( array('Version', 'Author', 'Description') as $_name ) 
                    {
                        $_plugins[$_name] = '';
                    }
                    
                    if ( preg_match("/Description\s*:*(.*)$/im", $contents, $description) )
                    {
                        $_plugins['Description'] = trim($description[1]);
                    }
                    
                    if ( preg_match("/Author\s*:*(.*)$/im", $contents, $author) )
                    {
                        $_plugins['Author'] = trim($author[1]);
                    }
                    
                    if ( preg_match("/Version\s*:*(.*)$/im", $contents, $author) )
                    {
                        $_plugins['Version'] = trim($author[1]);
                    }
                    
                    $_plugins['Enable'] = false;
                    if ( preg_match("/Enable\s*:*(.*)$/im", $contents, $author) )
                    {
                        $_plugins['Enable'] = (strtolower(trim($author[1])) == 'yes' ? 'true' : 'false');
                    }
                    
                    if ( $_plugins['Enable'] )
                    {
                        self::$_plugins['plugins']['Yes'][$_plugins['Id']] = $_plugins;
                    }
                    else
                    {
                    	self::$_plugins['plugins']['No'][$_plugins['Id']] = $_plugins;
                    }
                }
            }
        }
    }
    
    /**
     * 获取激活的插件
     * 
     * @return array 返回插件接口对象数组
     * @since  nv50
     */
    protected function _getActivePlugins()
    {
        if ( empty(self::$_listeners['classes']) )
        {
            self::$_listeners['classes'] = array();
            if ( !isset(self::$_plugins['plugins']['Yes']) )
            {
                self::$_plugins = $this->getPluginsData();
            }
            
            # 加载启用的插件主文件
            if ( isset(self::$_plugins['plugins']['Yes']) )
            {
                foreach ( (array) self::$_plugins['plugins']['Yes'] as $plugin ) 
                {
                    if ( isset($plugin['Id']) )
                    {
                        if ( !_08_Loader::import(_08_EXTEND_PLUGINS_PATH . $plugin['Id'] . DS . $plugin['Id'] . '_plugin') )
                        {
                            _08_Loader::import(_08_EXTEND_PLUGINS_PATH . $plugin['Id'] . '_plugin');
                        }
                        
                        if ( !_08_Loader::import(_08_PLUGINS_PATH . $plugin['Id'] . DS . $plugin['Id'] . '_plugin') )
                        {
                            _08_Loader::import(_08_PLUGINS_PATH . $plugin['Id'] . '_plugin');
                        }
                    }
                }
            }
            
            foreach ( get_declared_classes() as $class ) 
            {
                $reflectionClass = new ReflectionClass($class);
                if ( $reflectionClass->implementsInterface('_08_IPlugins') )
                {
                    $parentClass = $reflectionClass->getParentClass();
                    if ( is_object($parentClass) && ($parentClass->getName() != '_08_Controller_Base') )
                    {
                        self::$_listeners['classes'][$class] = $reflectionClass;
                    }
                }
            }
        }
        
        return self::$_listeners;
    }
    
    /**
     * 注册插件
     * 
     * @param string $hook     把插件注册到该钩子下
     * @param mixed  $callback 在该钩子下要执行的方法
     * 
     * @since nv50
     */
    public static function register( $hook, $callback )
    {
        /**
         * 如果该插件被继承扩展了则调用子类
         * 注：子类的命名规则为 parentClass_Sub 加上_Sub后缀
         **/
        if (is_array($callback) && isset($callback[0]))
        {
            $subClass = $callback[0] . '_Sub';
            if (class_exists($subClass))
            {
                $callback[0] = $subClass;
            }
        }
        
        self::$_listeners['methods'][$hook][] = $callback;
    }
    
    /**
     * 触发Hook里所有插件
     * 
     * @param string $hook  要触发该（钩子）的所有插件
     * @since nv50
     */
    public function trigger( $hook )
    {
        $contents = array();
        $hooks = $this->_getActivePlugins();
        if ( isset($hooks['methods'][$hook]) && self::checkInterface($hooks['classes'], $hook) )
        {
            foreach ( (array)$hooks['methods'][$hook] as $method )
            {
                if ( is_array($method) )
                {
                    if ( is_object($method[0]) )
                    {
                        $method[0] = get_class($method[0]);
                    }
                    if ( isset($hooks['classes'][$method[0]]) )
                    {
                        $hookReflection = $hooks['classes'][$method[0]];
                        $contents = array_merge($contents, (array) $this->__trigger($hookReflection, $method[1]));   
                        unset($hooks['classes'][$method[0]]);
                    }            
                }
                else
                {
                	foreach ( (array) $hooks['classes'] as $hook ) 
                    {
                        $contents = array_merge($contents, $this->__trigger($hook, $method));
                    }
                }
            }
        }
        
        return $contents;
    }
    
    /**
     * 触发单个插件
     * 
     * @param  object $hook   要触发的钩子接口
     * @param  string $method 注册到钩子里的方法
     * @return mixed  $items  返回方法执行后的返回数据
     * 
     * @since  nv50
     */
    private function __trigger( ReflectionClass $hookReflection, $method )
    {
        $items = array();
        if ( $hookReflection->hasMethod($method) )
        {
            $reflectionMethod = $hookReflection->getMethod($method);
            if ( $reflectionMethod->isStatic() )
            {
                $items = $reflectionMethod->invoke(null);
            }
            else
            {
            	$hookInstance = $hookReflection->newInstance();
                $items = $reflectionMethod->invoke($hookInstance);
            }
        }
        
        return $items;
    }
    
    /**
     * 检查接口
     * 
     * 保证开发的插件主类必须继承我们特定的接口：
     * 前台插件必须继承：     _08_Plugins_SiteHeader   类
     * 后台插件必须继承：     _08_Plugins_AdminHeader  类
     * 会员中心插件必须继承： _08_Plugins_MemberHeader 类
     * 会员空间插件必须继承： _08_Plugins_MspaceHeader 类
     * 手机版插件必须继承：   _08_Plugins_MobileHeader 类
     * 
     * @param  array  要检测的插件反射类
     * @param  string 要触发的钩子
     * @return bool   如果有通过的反射类则返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    private static function checkInterface( array &$pluginReflectionClass, $hook )
    {
        @list($namespace, $action) = explode('.', (string) $hook);
        $namespace = strtolower($namespace);
        $parentClass = '_08_Plugins_' . ucfirst($namespace) . 'Header';
        #if ( in_array($namespace, array('admin', 'site', 'member', 'mspace', 'mobile')) )
        if ( $namespace )
        {
            foreach ( $pluginReflectionClass as &$class ) 
            {
                if ( !$class->isSubclassOf($parentClass) )
                {
                    unset($class);
                }
            }
            
            if ( !empty($pluginReflectionClass) )
            {
                return true;
            }
        }
        
        return false;     
    }
    
    public static function getInstance()
    {
        if ( !(self::$instance instanceof self) )
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    protected function __construct()
    {
        self::$_plugins = array();
    }
    
    private function __clone(){}
}