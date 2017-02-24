<?php
/**
 * 推送类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_pushs extends cls_mtagsHeader
{    
    const SCLASS_VAL = 'paid';
    
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
            '*指定推送位','mtagnew[setting]['.self::SCLASS_VAL.']',
            umakeoption(self::_u_paidsarr(), $sclass), 
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
    
    /**
     * 获取推送位数据
     * 
     * @return array $re 返回获取到的推送位数据
     */ 
    public static function _u_paidsarr()
    {
    	$pushtypes = cls_cache::Read('pushtypes');
		$pushareas = cls_PushArea::Config();
    	$re = array();
    	foreach($pushtypes as $k => $v)
        {
    		$na = array();
    		foreach($pushareas as $x => $y){
    			if($k == $y['ptid']) $na[$x] = array('title' => '&nbsp; &nbsp; '.$y['cname']."($x)");
    		}
    		if($na){
    			$re["-$k"] = array('title' => $v['title'],'unsel' => 1);
    			$re += $na;
    		}
    	}	
    	return $re;
    }
}