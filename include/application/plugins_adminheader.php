<?php
/**
 * 后台插件控制器公共头部
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('M_ADMIN') || exit('No Permission');
abstract class _08_Plugins_AdminHeader extends _08_Controller_Base implements _08_IPlugin_Admin
{
    protected $_params = array();

    protected $_curuser;

    protected $_view = null;
    
    protected $_url = '';

    /**
     * 建立模型界面句柄
     *
     * @var object
     */
    protected $_build = null;

    public function __construct()
    {
        parent::__construct();
		$front = cls_frontController::getInstance();
        $this->_curuser = cls_UserMain::CurUser();
        $this->_params = $front->getParams();
        $this->_view = new _08_View($front);
        $this->_build = new _08_BuilderHtmls( $this->_view );
    	$this->_url .= "?entry={$this->_params['entry']}";
    	empty($this->params['action']) || $this->_url .= "&action={$this->_params['action']}";
    	empty($this->params['infloat']) || $this->_url .= "&infloat={$this->_params['infloat']}";
    	empty($this->params['handlekey']) || $this->_url .= "&handlekey={$this->_params['handlekey']}";
    }
}