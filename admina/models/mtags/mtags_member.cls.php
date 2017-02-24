<?php
/**
 * 会员类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_member extends cls_mtags_members
{    
    /**
     * 显示标识管理里的类目select选项
     * 
     * @param array $oarr 当前标识配置数组
     */ 
    public function showCotypesSelect(array $oarr)
    {
        global $mchannels;
    	$sclass = (isset($this->params['sclass']) ? $this->params['sclass'] : @$oarr['setting'][parent::CHIDS]);
        $config = array('0' => '不限模型', '');
    	foreach($mchannels as $k=>$v)
        {
            $config[$k] = "($k)$v[cname]";
        }
        trbasic(
            '允许以下会员模型','mtagnew[setting]['.parent::CHIDS.']',
            makeoption($config, $sclass), 
            'select', 
            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"", 'guide' => '该功能针对开始进入 "插入原始标识" 的选项定位')
        );
    }
}