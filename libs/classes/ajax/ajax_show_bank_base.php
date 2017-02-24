<?php
/**
 * 引入支付宝银行支付支持的页面
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/show_bank/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 * @since     nv50
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Show_Bank_Base extends _08_Models_Base
{
    public function __toString()
    {
        exit(_08_Loader::import('images:common:bank:index', array(), '.html'));
    }
}