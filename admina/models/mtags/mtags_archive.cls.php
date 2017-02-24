<?php
/**
 * 单个文档类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_archive extends cls_mtags_archives
{
    const CHID = 'chid';
    /**
     * 显示标识管理里的类目select选项
     * 
     * @param array $oarr 当前标识配置数组
     */ 
    public function showCotypesSelect(array $oarr)
    {
        global $channels;
        // 0=激活模型; 1=手动指定
        $config = array(0 => '不设置', -1 => '激活模型');
    	foreach($channels as $k=>$v) 
        {
            $config[$k] = "($k)$v[cname]";
        }
        $sclasses = (isset($this->params['sclass']) && $this->params['sclass'] != '' ? $this->params['sclass'] : @$oarr['setting'][self::CHID]);
        
		trbasic("允许以下文档模型",
			'',"<select onchange=\"setIdWithS(this);document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\" id=\"mselect_mtagnew[setting][".self::CHID."]\" style=\"vertical-align: middle;\">" . makeoption($config, $sclasses) . "</select><input type=\"text\" value=\"". $sclasses ."\" name=\"mtagnew[setting][".self::CHID."]\" id=\"mtagnew[setting][".self::CHID."]\" class=\"w55\" onblur=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"/>",'', array('guide' => '该功能针对开始进入 "插入原始标识" 的选项定位'));
    }
    
    /**
     * 获取标识管理里的sclass ID
     * 
     * @param array $setting 当前标识配置数组
     */ 
    public function getSclass(array $setting)
    {
        return @$setting[self::CHID];
    }
}