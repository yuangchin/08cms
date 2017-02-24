<?php
/**
 * 视图
 * {@link http://docs.php.net/manual/zh/class.arrayobject.php}
 *
 * @author    Wilson
 * @copyright Copyright (C) 2013, 08CMS Inc. All rights reserved.
 * @version   1.0
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_View extends ArrayObject implements _08_IView
{
    private $array;

    public function __construct()
    {
        if ( class_exists('cls_frontController') )
        {
            $front = cls_frontController::getInstance();
            $this->array = $front->getParams();
        }
        else
        {
        	$this->array = array();
        }
    }

    /**
     * 把变量加入视图属性里让模板调用
     *
     * @param string $key   属性名称
     * @param mixed  $value 属性值
     *
     * @since 1.0
     */
    public function assign( $keys, $value = '' )
    {
        if ( is_array($keys) )
        {
        	foreach ( $keys as $key => $value )
            {
                $this->array[$key] = $value;
            }
        }
        else if ( is_string($keys) )
        {
        	$this->array[$keys] = $value;
        }
    }

    /**
     * 导入模板
     *
     * @param string $file_name 要导入的模板名称
     * @since 1.0
     */
    public function display( $file_name, $ext = '.html', $path = _08_V_PATH )
    {        
        parent::__construct( $this->array, ArrayObject::ARRAY_AS_PROPS );
        
        $file = ($path . strtolower(str_replace(':', DIRECTORY_SEPARATOR, $file_name) . $ext));
        if ( is_file($file) )
        {
            return include_once $file;
        }
    }
}