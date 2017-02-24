<?php
/**
 * 后台常规管理中间AJAX菜单
 * 
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/ablock/datatype/xml/caid/4/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Ablock_Base extends _08_Models_Base
{
    public function __toString()
    {
        $caid = isset($this->_get['caid']) ? (int)$this->_get['caid'] : 0;
        return cls_cotype::BackMenuBlock(0, $caid);
    }
}