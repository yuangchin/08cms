<?php
/**
 * 文档类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_archives extends cls_mtagsHeader
{
    const CHSOURCE = 'chsource';
    
    const CHIDS = 'chids';
    
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
        global $channels;
        // 0=激活模型; 1=手动指定
        $config = array(' - 激活模型 - ');
    	foreach($channels as $k=>$v) 
        {
            $config[$k] = "($k)$v[cname]";
        }
        $sclasses = (isset($this->params['sclass']) && $this->params['sclass'] != '' ? $this->params['sclass'] : @$oarr['setting'][self::CHIDS]);
        
		trbasic("*允许以下文档模型",
			'',"<select onchange=\"setIdWithS(this);document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\" id=\"mselect_mtagnew[setting][".self::CHIDS."]\" style=\"vertical-align: middle;\">" . makeoption($config, $sclasses) . "</select><input type=\"text\" value=\"". $sclasses ."\" name=\"mtagnew[setting][".self::CHIDS."]\" id=\"mtagnew[setting][".self::CHIDS."]\" class=\"w55\" onblur=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"/>",'');
    }
    
    /**
     * 获取标识管理里的sclass ID
     * 
     * @param array $setting 当前标识配置数组
     */ 
    public function getSclass(array $setting)
    {
        return @$setting[self::CHIDS];
    }
}