<?php
/**
 * 典型的MVC前端控制器单例类，目前目录暂定为：
 * M：/include/application/models
 * 后台V: /admina/views
 * 前台V: /template
 * C: /include/application/controllers
 * 如果想增加或修改目录时，只要把该目录注册到自动加载栈即可，注意：V里的操作类必须继承于_08_IController接口
 *
 * @author    Wilson
 * @copyright Copyright (C) 2013, 08CMS Inc. All rights reserved.
 * @version   1.0
 */

class cls_frontController
{
    /**
     * 控制器名称
     *
     * @since 1.0
     */
    protected $_controller = '';

    /**
     * 控制器行为
     *
     * @since 1.0
     */
    protected $_action = '';

    /**
     * 控制器操作参数
     *
     * @since 1.0
     */
    protected $_params = array();

    /**
     * 当前对象句柄
     *
     * @static
     * @since  1.0
     */
    protected static $_instance = null;

    /**
     * 控制器路由
     * 注：该路由必须符合以下条件才会运行：
     * 1、类必须存在，并必须符合自动加载规则和继承于_08_IController接口
     * 2、类里的方法必须与URI的action参数一致，如果该参数为空，则自动调用init()方法
     *
     * @since  1.0
     */
    public function route()
    {
        $class_name = $this->getControllerClass();
        if( class_exists( $class_name ) )
        {
            $reflection = new ReflectionClass( $class_name );
            // 检查是否已经实现了该接口
            if( $reflection->implementsInterface('_08_IController') )
            {
                $action = $this->getAction();
                $hasAction = $reflection->hasMethod( $action );
                _08_Application::$__isNewStructure = true;
                // 检查方法是否已定义，方法名不区分大小写
                if( $hasAction || $reflection->hasMethod( '__call' ) )
                {
                    try
                    {
                        $controller = $reflection->newInstance();
                        if ( $hasAction )
                        {
                            $method = $reflection->getMethod( $action );
                            $method->invoke( $controller );
                        }
                        else
                        {
                        	$method = $reflection->getMethod( '__call' );
                            $method->invoke( $controller, $action, null );
                        }
    
                        # 自定义一个 __end 魔术方法，让控制器做善后工作，类似于析构函数
                        if ( $reflection->hasMethod( '__end' ) )
                        {
                            $endMethod = $reflection->getMethod( '__end' );
                            $endMethod->invoke( $controller );
                        }
                    }
                    catch (ReflectionException $error)
                    {
                        throw new _08_ApplicationException($error->getMessage());
                    }
                }
            }
            else
            {
                _08_Application::$__isNewStructure = false;
            	throw new _08_ApplicationException('The controller must inherit Interface : _08_IController');
            }
        }
        else if ( self::checkActionMVC() )
        {
            _08_Application::$__isNewStructure = false;
            throw new _08_ApplicationException('Controller Not Found!');
        }
        
        $this->__doPluginsAction();
    }
    
    /**
     * 获取控制器调用类
     * 
     * @return string $class_name 返回控制器调用类的名称
     * @since  1.0
     */
    private function getControllerClass()
    {        
        // 只兼容之前自定义加载类以下两种命名规则
        $class_name = $this->getController();
        if ( defined('M_ADMIN') )
        {
            if( class_exists( 'cls_' . $class_name ))
            {
                $class_name = 'cls_' . $class_name;
            }
            else if( class_exists('_08_' . $class_name) )
            {
                $class_name = '_08_' . $class_name;
            }
            else
            {
            	$class_name = '_08_C_Admin_' . ucfirst($class_name) . '_Controller';
            }
        }
        else
        {
        	$class_name = '_08_C_' . ucfirst($class_name) . '_Controller';
        }
        
        return $class_name;
    }

    /**
     * 获取控制器
     *
     * @return string 返回控制器名称
     * @since  1.0
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * 获取控制器行为
     *
     * @return string 获取控制器行为
     * @since  1.0
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 获取控制器参数
     *
     * @return array 获取控制器参数
     * @since  1.0
     */
    public function getParams()
    {
        return $this->_params;
    }

    public static function getInstance(array $params = array())
    {
        if( !(self::$_instance instanceof self) )
        {
            self::$_instance = new self($params);
        }
        
        return self::$_instance;
    }

    private function __clone() {}

    /**
     * 构造路由参数
     *
     * @since 1.0
     */
    private function __construct( array $params = array() )
    {
        if ( empty($params) )
        {
            $params = cls_env::_GET_POST();
        }
        
        if ( defined('M_ADMIN') )
        {
            $this->adminRoute($params);
        }
        else
        {
        	$this->siteRoute($params);
        }
    }
    
    /**
     * 后台路由
     */
    private function adminRoute(array $params)
    {
        if( empty( $params ) || empty( $params['entry'] ) ) return false;
        if( $params['entry'] == 'extend' && isset( $params['extend'] ) )
        {
            $this->_controller = $params['extend'];
        }
        else
        {
            $this->_controller = trim( $params['entry'] );
        }

        if( empty( $params['action'] )  )
        {
            $params['action'] = 'init';
        }
        else
        {
            $params['action'] = trim( $params['action'] );
        }

        $this->_action = $params['action'];
        $this->_params = $params;
    }
    
    /**
     * 检查是否执行MVC架构
     */
    public static function checkActionMVC()
    {
        if ( isset($_SERVER['QUERY_STRING']) )
        {
            if ( 0 === strpos($_SERVER['QUERY_STRING'], '/') )
            {
                return true;
            }
        }        
        
        return false;
    }
    
    /**
     * 前台路径规则
     */
    private function siteRoute(array $_params)
    {
        // 暂时以URI开头为 /?/ 的规则使用新架构，先不考虑CLI运行情况
        if ( self::checkActionMVC() )
        {
            if ( false !== strpos($_SERVER['QUERY_STRING'], '?') )
            {
                $_SERVER['QUERY_STRING'] = str_replace('?', '', $_SERVER['QUERY_STRING']);
            }
            $_SERVER['QUERY_STRING'] = str_replace(array('&', '='), '/', $_SERVER['QUERY_STRING']);

            //$queryString = rawurldecode($_SERVER['QUERY_STRING']);
			//$queryString = urldecode($_SERVER['QUERY_STRING']);
			$queryString = $_SERVER['QUERY_STRING'];
            $queryString .= (substr($queryString, strlen($queryString) - 1) == '/' ? '' : '/');
            $request_uris = explode('/', $queryString);
			foreach($request_uris as $k => $v){
                $request_uris[$k]=urldecode($v);
            }
            unset($request_uris[0]);
            if ( isset($request_uris[1]) )
            {
                _08_FileSystemPath::filterPathParam($request_uris[1]);
                $this->_controller = $request_uris[1];
                unset($request_uris[1]);
            }
            else
            {
            	$this->_controller = 'index';
            }
            
            if ( !empty($request_uris[2]) && !in_array(substr($request_uris[2], 0, 1), array('?', '&')) )
            {
                _08_FileSystemPath::filterPathParam($request_uris[2]);
                $this->_action = $request_uris[2];
                unset($request_uris[2]);
            }
            else
            {
            	$this->_action = 'init';
            }
            
            $params = array(); $prevValue = '';
            foreach ( $request_uris as $key => $value ) 
            {
                if ( $key % 2 == 0 && $prevValue )
                {
                    if (preg_match('/^(\w+)\[(\w+)\]$/i', $prevValue, $prevKeys))
                    {
                        $params[$prevKeys[1]][$prevKeys[2]] = addslashes($value);
                    }
                    else
                    {
                    	$params[$prevValue] = addslashes($value);
                    }
                }
                $prevValue = $value;
            }
			#这个notify_time参数用于支付宝即时到账 把'+'转成''
			if(isset($params['notify_time']) && !empty($params['notify_time']))
			{
				$params['notify_time'] = urldecode($params['notify_time']);
			}	
            
            $this->_params = $params + $_params;
        }
        else
        {
        	$this->_params = $_params;
        }
    }
    
    /**
     * 执行插件动作
     * 
     * @since nv50
     */
    private function __doPluginsAction()
    {
        if ( defined('M_ADMIN') && isset($this->_params['entry']) )
        {
            $uri = $this->_params['entry'];
            if ( isset($this->_params['action']) )
            {
                $uri .= '.' . $this->_params['action'];
            }
            _08_Plugins_Base::getInstance()->trigger('admin.' . $uri);
        }
    }
}