<?php
/**
 * 应用集合基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
 
defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_Application_Base
{    
    /**
     * 前端控制器句柄
     * 
     * @var   object
     * @since 1.0
     */
    protected $_front = null;
    
    /**
     * HTTP请求句柄
     * 
     * @var   object
     * @since 1.0
     */
    protected $_request = null;
    
    /**
     * 系统配置参数信息数组
     * 
     * @var   array
     * @since 1.0
     */
    protected $_mconfigs = array();
    
    /**
     * 当前控制器
     * 
     * @var   string
     * @since 1.0
     */
    protected $_controller = '';
    
    /**
     * MVC模型对象数组
     *
     * @var   array
     * @since 1.0
     */
    protected static $_models = array();
    
    /**
     * 当前URL
     * 
     * @var   string
     * @since 1.0
     */
    protected $_currentUrl = '';
    
    /**
     * 当前执行动作
     * 
     * @var   string
     * @since 1.0
     */
    protected $_action = '';
    
    /**
     * 当前GET、POST参数数组
     * 
     * @var   array
     * @since 1.0
     */
    protected $_get = array();
    
    const MODEL_PREFIX = '_08_M_';
    
    protected $_curuser = null;
    
	protected $_mcharset = ''; //ajax等处理中,经常使用这个
    
    public function __construct()
    {
        $this->_curuser = cls_UserMain::CurUser();
        $this->_front = cls_frontController::getInstance();
        $this->_controller = $this->_front->getController();
        $this->_action = $this->_front->getAction();
        $this->_get = $this->_front->getParams();
        $this->_currentUrl = cls_url::create(array($this->_controller => $this->_action));
        $this->_request = new _08_Http_Request();
        $this->_mconfigs = cls_cache::Read('mconfigs');
		$this->_mcharset = cls_env::getBaseIncConfigs('mcharset');
    }

    /**
     * 获取模型对象
     *
     * @param string $_name  模型名称
     * @param mixed  $params 模型构造函数参数
     * @since nv50
     */
    public static function getModels( $_name, $params = null )
    {
        if ( is_object($params) )
        {
            $key = md5( $_name . (string) $params );
        }
        else
        {
        	$key = md5( $_name . serialize( (array) $params ) );
        }
        
        if ( empty(self::$_models[$key]) )
        {
            $modelClass = self::MODEL_PREFIX . $_name;
            if ( is_null($params) )
            {
                self::$_models[$key] = new $modelClass();
            }
            else
            {
            	self::$_models[$key] = new $modelClass($params);
            }        	
        }

        return self::$_models[$key];
    }
    
    /**
     * 设置属性
     * 注：如果子类调用该方法时、子类的要判断的属性必需不能为 private
     * 
     * @param string $name  要设置的属性名称
     * @param mixed  $value 属性值
     * 
     * @since nv50
     */
    public function setter($name, $value)
    {        
        if ( property_exists($this, $name) )
        {
            $this->$name = $value;
        }
    }
    
    
    /**
     * 获取属性
     * 注：如果子类调用该方法时、子类的要判断的属性必需不能为 private
     * 
     * @param  string $name  要获取的属性名称
     * @return mixed         返回获取到的属性值，如果不存在该属性或是该属性为private时返回null
     * 
     * @since  nv50
     */
    public function getter($name)
    {    
        if ( property_exists($this, $name) )
        {
            return $this->$name;
        }
        
        return null;
    }
}