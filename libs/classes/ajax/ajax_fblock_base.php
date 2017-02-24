<?php
/**
 * 后台广告或附件管理中间AJAX菜单
 * 
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/fblock/fcaid/0/t/1393405964325/datatype/xml/&callback=$_8VWDOV4HP
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Fblock_Base extends _08_Models_Base
{
    public function __toString()
    {
        $fcaid = isset($this->_get['fcaid']) ? trim($this->_get['fcaid']) : 0;
        return cls_fcatalog::BackMenuBlock($fcaid);
    }
}