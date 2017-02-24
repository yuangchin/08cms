<?php
/**
 * 编辑器控制器
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
_08_Loader::import(_08_INCLUDE_PATH . 'ck_public_class');
class _08_C_Editor_Controller extends _08_Controller_Base
{
    /**
     * 扩展插件路径
     * 
     * @var string
     */
    private $extPluginsPath;
    
    /**
     * 插件路径
     * 
     * @var string
     */
    private $pluginsPath;
    
    private $editorPublicClassObject = null;
    
    /**
     * 分页管理插件
     */
    public function paging_management()
    {
        _08_Loader::import($this->pluginsPath . __FUNCTION__);
    }
    
    /**
     * 汽车行情，只是放调用代码，如果不存在扩展系统文件时不会引入文件
     */
    public function hangqing()
    {
        _08_Loader::import($this->extPluginsPath . __FUNCTION__, $this->_get);        
    }
    
    /**
     * 汽车图片，只是放调用代码，如果不存在扩展系统文件时不会引入文件
     */
    public function chetu()
    {
        _08_Loader::import($this->extPluginsPath . __FUNCTION__, $this->_get);        
    }
    
    /**
     * 楼盘信息，只是放调用代码，如果不存在扩展系统文件时不会引入文件
     */
    public function house_info()
    {
        _08_Loader::import($this->extPluginsPath . __FUNCTION__);        
    }
    
    /**
     * 选小区图，只是放调用代码，如果不存在扩展系统文件时不会引入文件
     */
    public function plot_pigure()
    {
        _08_Loader::import($this->extPluginsPath . __FUNCTION__);        
    }
    
    /**
     * 选户型图，只是放调用代码，如果不存在扩展系统文件时不会引入文件
     */
    public function size_chart()
    {
        _08_Loader::import($this->extPluginsPath . __FUNCTION__);        
    }
    
    public function __construct()
    {
        parent::__construct();
        $path = 'classes:ueditor:plugins:';
        $this->pluginsPath = _08_LIBS_PATH . $path;
        $this->extPluginsPath = _08_EXTEND_LIBS_PATH . $path;
    }
}