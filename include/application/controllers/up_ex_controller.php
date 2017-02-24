<?php
/**
 * 上传接口控制器
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_C_Up_ex_controller extends _08_C_Upload_Controller
{
    
    /**
     * 在构造方法里检测上传权限，如果无权限时终止上传
     */
    public function __construct()
    {
        parent::__construct(); //print_r($this);
        $this->type = empty($this->_get['type']) ? 'images' : preg_replace('/[^\w\[\]]/', '', $this->_get['type']);
        $this->lfile = (substr($this->type,-1) == 's' ? substr($this->type,0,-1) : $this->type);
        
        $this->browser = _08_Browser::getInstance();
		$this->encode = empty($this->_get['encode']) ? '' : strtolower($this->_get['encode']);
        $this->result = array();
        $this->isExit = true;
        $this->_get['wmid'] = (empty($this->_get['wmid']) ? 0 : (int) $this->_get['wmid']);
        $this->_get['auto_compression_width'] = (empty($this->_get['auto_compression_width']) ? 0 : (int) $this->_get['auto_compression_width']);
        $this->localname = 'Filedata';
    }
   
    /**
     * 显示上传按钮
     */
    public function select_button()
    {
        @$maxcount = intval($this->_get['maxcount']);
		$maxcount = empty($maxcount) ? 50 : $maxcount; // 一次最多让上传50个文件
		
    	$tmp = cls_atm::getLocalFilesExts($this->type);
    	$ftypes='';$otype='';
        foreach($tmp as $v)
        {
            $v['extname'] = strtolower($v['extname']);
            if($v['islocal']) $otype.=",\"$v[extname]\":[$v[minisize],$v[maxsize]]";
            $ftypes .= ((empty($ftypes) ? '' : ';') . '*.' . $v['extname']);
        }
        $otype=substr($otype,1);
        
        $configs = array('base_inc_configs' => cls_env::getBaseIncConfigs('mcharset, ckpre'), 
                         '_get' => @$this->_get,
                         'ftypes' => $ftypes,
                         'otype' => $otype,
                         'type' => $this->type,
                         'maxcount' => $maxcount,
                         'timestamp' => TIMESTAMP,
                         'mconfigs' => $this->_mconfigs );
        
        $this->_view->assign($configs);
        
    }
    
}
