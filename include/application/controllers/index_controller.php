<?php
/**
 * 默认控制器
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_C_Index_Controller extends _08_Controller_Base
{
    public function init()
    {
        $this->_request->redirect('/');
    }
}