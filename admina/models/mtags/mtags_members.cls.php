<?php
/**
 * 会员类型标识处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_mtags_members extends cls_mtagsHeader
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
        global $mchannels;
    	$sclass = (isset($this->params['sclass']) ? $this->params['sclass'] : @$oarr['setting'][self::CHIDS]);
        $config = array();
    	foreach($mchannels as $k=>$v) 
        {
            $config[$k] = "($k)$v[cname]";
        }
        $chsourcearr = array('0' => '不限模型','1' => '激活模型','2' => '手动指定',);
//        trbasic(
//            '*允许以下会员模型','mtagnew[setting]['.self::CHIDS.']',
//            makeoption($config, $sclass), 
//            'select', 
//            array('validate' => "onchange=\"document.forms[0].action='{$this->url}&sclass=' + this.value;document.forms[0].submit();\"")
//        );
        sourcemodule('会员模型限制',
			'mtagnew[setting]['.self::CHSOURCE.']',
			$chsourcearr,
			empty($oarr['setting'][self::CHSOURCE]) ? '' : $oarr['setting'][self::CHSOURCE],
			'2',
			'mtagnew[setting]['.self::CHIDS.'][]',
			cls_mchannel::mchidsarr(),
			!empty($oarr['setting'][self::CHIDS]) ? explode(',',$oarr['setting'][self::CHIDS]) : array()
		);
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