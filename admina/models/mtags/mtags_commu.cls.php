<?php
/**
 * 交互类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_commu extends cls_mtags_commus 
{
    /**
     * 显示标识管理里的类目select选项
     * 
     * @param array $oarr 当前标识配置数组
     */ 
    public function showCotypesSelect(array $oarr)
    {
        global $commus;
    	$sclass = (isset($this->params['sclass']) ? $this->params['sclass'] : @$oarr['setting'][parent::SCLASS_VAL]);
        $config = array('请设置交互项目');
    	foreach($commus as $k=>$v) 
        {
            $config[$k] = "($k)$v[cname]";
        }
        trbasic(
            '指定交互项目','mtagnew[setting]['.parent::SCLASS_VAL.']',
            makeoption($config, $sclass), 
            'select', 
            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"", 'guide' => '该功能针对开始进入 "插入原始标识" 的选项定位')
        );
    }
}