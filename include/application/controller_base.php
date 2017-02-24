<?php
/**
 * 控制器基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_Controller_Base extends _08_Application_Base implements _08_IController
{
    /**
     * 视图句柄
     * 
     * @var   object
     * @since 1.0
     */
    protected $_view = null;
    
    public function __construct()
    {
        parent::__construct();        
        $this->_view = new _08_View();
    }
    
    public function init()
    {
    }
    
    public function __end()
    {
        # 让action的初始化定为 index
        ($this->_action == 'init') && ($this->_action = 'index');
        $this->_view->display($this->_controller . ':' . $this->_action, '.php');
    }
}
