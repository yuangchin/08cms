<?php
/**
 * 核心工厂类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

abstract class _08_factory
{
    /**
     * 获取数据库对象
     * 
     * @static
     */
    private static $db = array();
    
    /**
     * 应用句柄
     * 
     * @var    array
     * @since  1.0
     * @static
     */ 
	private static $application = array();
    
    private static $instances = array();
    
    /**
     * 获取子类与父类之间切换的对象，当子类不存在时自动实例化父类
     * 注：父类与子类之间的规则是：父类在子类的名称后面加 '_Base' 或 'Base' 后缀
     * 
     * @param  string $sub_class 要实例化的子类
     * @param  mixed  $param     要传递构造函数的参数
     * @return object            返回实际实例化的对象
     * 
     * @since  nv50
     */
    public static function getInstance( $sub_class, $param = null )
    {
        # 定义_Base后缀的父类名称
        $parentClass = $sub_class . '_Base';
        # 如果父类名称不是_Base后缀则定义类名称为Base后缀
        if ( !class_exists($parentClass) )
        {
            $_parentClass = $parentClass;
            $parentClass = $sub_class . 'Base';
            
            if ( !class_exists($parentClass) )
            {
                die("Fatal error: Class '{$_parentClass}' or '{$parentClass}' not found");
            } 
        }        
                     
        # 先默认要实例化的类为父类
        $newClass = $parentClass;  
        
        if ( class_exists($sub_class) && is_subclass_of($sub_class, $parentClass) )
        {
            # 如果子类存在时实例化子类，否则实例化父类
            $newClass = $sub_class;
        }
        
        if ( is_null($param) )
        {
            if ( empty(self::$instances[$newClass]) )
            {
                self::$instances[$newClass] = new $newClass();
            }
            
            return self::$instances[$newClass];
        }
        else
        {
        	return new $newClass( $param );
        }
    }
    
    /**
     * 获取标识管理对象实例
     * 
     * @param  string $tclass 标识类型
     * @return object         对象实例
     * 
     * @since  nv50
     */ 
    public static function getMtagsInstance($tclass)
    {
        $class_name = "cls_mtags_$tclass";
        if(class_exists($class_name))
        {
            return new $class_name();
        }  
    }
    
    /**
     * 获取应用对象
     * 
     * @param  mixed  $id     客户端标识符或名称
     * @param  array  $config 配置数组
     * @param  string $prefix 应用前缀
     * 
     * @return object         返回应用对象句柄
     * @since  nv50
     */
    public static function getApplication($id = null, array $config = array(), $prefix = '_08')
    {
        $key = md5( $prefix . $id . serialize( $config ) );
        if ( empty(self::$application[$key]) )
        {
            self::$application[$key] = _08_Application::getInstance($id, $config, $prefix);
        }
        
        return self::$application[$key];
    }
    
    /**
     * 获取数据库对象，写该方法是扩展$db的作用域，让数据库对象在函数里使用时不用global，尽量减少global的使用。
     * 
     * @param  array  $config 数据库链接配置信息
     *         格式： array('dbhost' => $dbhost, 'dbuser' => $dbuser, 'dbpw' => $dbpw, 'dbname' => $dbname, 
     *                      'tblprefix' => $tblprefix, 'pconnect' => $pconnect, 'dbcharset' => $dbcharset)
     * @return object
     * 
     * @since nv50
     */ 
    public static function getDBO( array $config = array() )
    {
        global $db; # 暂时保留这global的使用，以后如果不使用general.inc.php的$db时可去掉。

        # 去掉global后该模块可删除
        $dbDriversClass = '_08_MysqlQuery';
        if( ($db instanceof $dbDriversClass) && empty($config))
        {
            return $db;
        }
        
        ksort($config);
        $key = md5(serialize($config));
        if( empty(self::$db[$key]) )
        {
            # 定义配置信息
            if ( empty($config) )
            {
                $config = self::getDBOConfig();
            }
            
            # 连接数据库
            self::$db[$key] = new $dbDriversClass( $config );
        }
        
        return self::$db[$key];
    }
    
    /**
     * 获取DBO配置
     * 
     * @return array $config 返回获取到的DBO配置
     * @since  nv50
     */
    public static function getDBOConfig()
    {
        $config = cls_env::getBaseIncConfigs('dbcharset, mcharset, dbhost, drivers, dbport, dbuser, dbpw, dbname, tblprefix, pconnect');
        if ( empty($config['dbcharset']) && in_array(strtolower($config['mcharset']), array('gbk','big5','utf-8')) )
        {
            $config['dbcharset'] = str_replace('-', '', $config['mcharset']);
        }
        empty($config['drivers']) && $config['drivers'] = 'Mysql';
        empty($config['dbport']) && $config['dbport'] = '3306';
        
        return $config;
    }
    
    /**
     * 获取支付网关对象
     * 
     * @param  string $payType 网关名称，目前支持： alipaydirect -- 支付宝即时到账
     * @return object          返回支付网关对象
     * 
     * @since  nv50
     */
    public static function getPays($payType)
    {
        $class = _08_Loader::MODEL_PREFIX . ucfirst($payType);
        return self::getInstance($class);
    }
}