<?php
/**
 * 副件类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_farchive extends cls_mtags_farchives
{
    /**
     * 显示标识管理里的类目select选项
     * 
     * @param array $oarr 当前标识配置数组
     */ 
    public function showCotypesSelect(array $oarr)
    {
    	$sclass = (isset($this->params['sclass']) ? $this->params['sclass'] : @$oarr['setting'][parent::SCLASS_VAL]);
        $config = cls_fcatalog::fcaidsarr();
        $config[0] = '请选择';
        ksort($config);
        trbasic(
            '选择副件分类','mtagnew[setting]['.parent::SCLASS_VAL.']',
            makeoption($config, cls_fcatalog::getNewFcaid($sclass)), 
            'select', 
            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"", 'guide' => '该功能针对开始进入 "插入原始标识" 的选项定位')
        );
    }
}