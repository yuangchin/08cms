<?php
/**
 * 类目类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_catalogs extends cls_mtagsHeader
{    
    const SCLASS_VAL = 'listby';
    
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
        global $cotypes;
        // 0=栏目; 1=类系
    	$sclass = (isset($this->params['sclass']) && $this->params['sclass'] != '' ? $this->params['sclass'] : @$oarr['setting'][self::SCLASS_VAL]);
        $config = array('ca' => '(0)栏目');
    	foreach($cotypes as $k=>$v) 
        {
            $v['sortable'] && $config['co' . $k] = "($k)$v[cname]";
        }
        
        trbasic(
            '*列表展示类系','mtagnew[setting]['.self::SCLASS_VAL.']',
            makeoption($config, $sclass), 
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
