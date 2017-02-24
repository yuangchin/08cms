<?php
/**
 * 安装完成控制器基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_C_Install_Complete_Controller_Base extends _08_Install_Base
{
    public function execute()
    {
        $this->_checkToken();
         
        $this->_view->assign( array( 'iversion' => $this->_getIversion() ) );
        $this->_view->display('complete', '.php', _08_INSTALL_PATH . 'view' . DS);
    }
}