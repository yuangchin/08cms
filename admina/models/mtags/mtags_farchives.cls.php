<?php
/**
 * 副件类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_farchives extends cls_mtagsHeader
{    
    const SCLASS_VAL = 'casource';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 显示标识管理里的类目select选项
     * 
     * @param array $oarr 当前标识配置数组
     */ 
    public function showCotypesSelect(array $oarr)
    {
    	$sclass = (isset($this->params['sclass']) ? $this->params['sclass'] : @$oarr['setting'][self::SCLASS_VAL]);
        trbasic(
            '*选择副件分类','mtagnew[setting]['.self::SCLASS_VAL.']',
            makeoption(cls_fcatalog::fcaidsarr(), cls_fcatalog::getNewFcaid($sclass)), 
            'select', 
            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"")
        );
    }
    
    /**
     * 获取标识管理里的sclass ID
     * 
     * @param array $setting 当前标识配置数组
     */ 
    public function getSclass(array $setting)
    {
        return @$setting[self::SCLASS_VAL];
    }
}