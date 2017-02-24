<?php
/**
 * 会员节点
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_mcnode extends cls_mtags_mccatalogs 
{    
    const SCLASS_VAL = 'cnsource';
    
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
        $config['mcnid'] = '自定义节点';
        
        trbasic(
            '*列表展示类系','mtagnew[setting]['.self::SCLASS_VAL.']',
            makeoption($config, $sclass), 
            'select', 
            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"")
        );
    }
    
}