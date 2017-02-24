<?php
/**
 * 汽车系统数据库设置控制器
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_C_Install_Database_Controller extends _08_C_Install_Database_Controller_Base
{
    /**
     * 数据包名称
     */
    public function getDataPakageName()
    {
        return '汽车';
    }
}