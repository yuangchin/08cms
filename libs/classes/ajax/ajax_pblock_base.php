<?php
/**
 * 后台推送管理中间AJAX菜单
 * 
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/pblock/paid/_pusharea/t/1393406011198/datatype/xml/&callback=$_E6Cv_7opS
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Pblock_Base extends _08_Models_Base
{
    public function __toString()
    {
        $paid = isset($this->_get['paid']) ? trim($this->_get['paid']) : '';
        return cls_PushArea::BackMenuBlock($paid);
    }
}