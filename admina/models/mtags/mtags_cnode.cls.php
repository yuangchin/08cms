<?php
/**
 * 类目节点
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_cnode extends cls_mtags_catalogs
{
    /**
     * 显示标识管理里的类目select选项
     * 
     * @param array $oarr 当前标识配置数组
     */ 
    public function showCotypesSelect(array $oarr)
    {
        global $cotypes;
        // 0=栏目; 1=类系
    	$sclass = (isset($this->params['sclass']) && $this->params['sclass'] != '' ? $this->params['sclass'] : @$oarr['setting'][parent::SCLASS_VAL]);
        $config = array('-1' => '请选择', 'ca' => '(0)栏目');
    	foreach($cotypes as $k=>$v) 
        {
            $v['sortable'] && $config['co' . $k] = "($k)$v[cname]";
        }
        ksort($config);
        
        trbasic(
            '节点展示类系','mtagnew[setting]['.parent::SCLASS_VAL.']',
            makeoption($config, $sclass), 
            'select', 
            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"", 'guide' => '该功能针对开始进入 "插入原始标识" 的选项定位')
        );
    }
}