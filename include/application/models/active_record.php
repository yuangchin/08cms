<?php
/**
 * 一个简单的AR类
 * 任务类只要继承于本类都会自动创建出CRUD操作方法
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_M_Active_Record extends _08_Models_Base
{    
    public function __call( $name, $param = null )
    {
        if ( is_null($param) )
        {
            return call_user_func(array($this->_db, $name));
        }

        return call_user_func_array(array($this->_db, $name), $param);        
    }

    public function __get( $name )
    {
        return $this->_db->getter($name);
    }

    public function __set( $name, $value )
    {
        return $this->_db->setter($name, $value);
    }

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = $this->getTableName();
    }
    
    abstract function getTableName( $tableID = '' );
}