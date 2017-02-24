<?php
/**
 * 后台会员管理中间AJAX菜单
 * 
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/mblock/datatype/xml/paid/4/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Mblock_Base extends _08_Models_Base
{
    public function __toString()
    {
        $mchid = isset($this->_get['mchid']) ? (int)$this->_get['mchid'] : 0;
        return cls_mchannel::BackMenuBlock($mchid);
    }
}