<?php
/**
 * 检查标签模板名称是否存在
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/check_mtagtemplate/val/test/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 * @since     nv50
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Check_Mtagtemplate_Base extends _08_Models_Base
{
    public function __toString()
    {
        $re = '';
		_08_Loader::import(_08_ADMIN . ':mtags:_taginit');
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        $val = empty($this->_get['val']) ? '' : cls_string::iconv("utf-8", $mcharset, $this->_get['val']);
        $val = trim($val);
        $older = empty($this->_get['older']) ? '' : cls_string::iconv("utf-8", $mcharset, $this->_get['older']);
        if($val == $older) return $re;
        if(is_file( cls_tpl::TemplateTypeDir('tpl').$val)) $re = $val.'已经存在！';   
		return $re;     
    }
}