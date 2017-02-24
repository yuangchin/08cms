<?php
/**
 * 百度编辑器控制器基类
 * 
 * @since     nv50
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_Ueditor_Base extends _08_Controller_Base
{
    private $type = 'image';
    private $params = array();
    private $configs = array();
	private $toolbars = array();
    
    /**
     * 获取配置
     * 
     * @return string         返回格式化后的JSON数据
     */
    public function config()
    {
        return $this->_formatData($this->getConfigs());
    }
    
    /* 列出图片 */
    public function listimage()
    {
        $this->type = 'image';
        $path = M_ROOT . $this->_mconfigs['dir_userfile'] . DS . 'image';
        $images = _08_FileSystemPath::map(array($this, 'getFiles'), $path);        
        $result = $this->_listStatus($images);        
        return $this->_formatData($result);
    }
    
    /* 列出文件 */
    public function listfile()
    {
        $this->type = 'file';
        $path = M_ROOT . $this->_mconfigs['dir_userfile'] . DS . 'file';
        $files = _08_FileSystemPath::map(array($this, 'getFiles'), $path);
        $result = $this->_listStatus($files);
        return $this->_formatData($result);
    }
    
    /**
     * 获取文件夹内的文件
     * 
     * @param  object $item  文件对象节点
     * @return array  $files 返回获取到的文件信息数组，获取失败时返回空数组
     * 
     * @since  nv50
     */
    public function getFiles( $item )
    {
        $file = array();
        $localfiles = cls_atm::getLocalFilesExts($this->type);
        $ext = substr(strrchr($item->getFilename(), '.'), 1);
        if ( $ext && array_key_exists($ext, $localfiles) )
        {
            $file['url'] = cls_url::localToUrl($item->getPathname());
            $file['mtime'] = $item->getMTime();
        }
        
        return $file;
    }
    
    /**
     * 列表状态
     * 
     * @param  array $list   列表数据
     * @return array $status 返回列表状态数组
     * 
     * @since  nv50
     */
    protected function _listStatus( array $files )
    {
        $files = array_filter($files);
        /* 获取指定范围的列表 */
        $list = cls_Array::limit($files, $this->params['start'], $this->params['end']);
        if ( empty($list) )
        {
            $status = array(
                "state" => "no match file",
                "list" => array(),
                "start" => $this->params['start'],
                "total" => 0
            );
        }
        else
        {
            $status = array(
                "state" => "SUCCESS",
                "list" => $list,
                "start" => $this->params['start'],
                "total" => count($list)
            );
        }
        
        return $status;
    }
    
    public function __construct()
    {
        parent::__construct();
        /* 获取参数 */
        $this->params = array();
        if ( isset($this->_get['size']) )
        {
            $this->params['size'] = (int)$this->_get['size'];
        }
        else
        {
            $this->params['size'] = 20;
        }
        
        if ( isset($this->_get['start']) )
        {
            $this->params['start'] = (int)$this->_get['start'];
        }
        else
        {
            $this->params['start'] = 0;
        }
        
        $this->params['end'] = $this->params['start'] + $this->params['size'];
        
        $file = _08_FilesystemFile::getInstance();
        $file->_fopen( dirname(__FILE__) . ":config.json", 'r' );
        $this->configs = $file->_fread();
        $this->configs = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $this->configs), true);
    }
    
    /**
     * 获取配置信息
     * 
     * @param array 返回获取到的配置信息
     * 
     * @since nv50
     */
    public function getConfigs()
    {
        # 图片文件配置
        $imageTypes = cls_atm::getLocalFilesExts('image');
        $this->configs['imageAllowFiles'] = array_keys($imageTypes);
        $this->_setType($this->configs['imageAllowFiles']);
        $this->configs['imageManagerAllowFiles'] = $this->configs['imageAllowFiles'];
		$this->configs['catcherUrlPrefix'] = _08_CMS_ABS; //启用ftp附件,后续再测试
        $this->configs['imageMaxSize'] = $this->configs['scrawlMaxSize'] = $this->configs['catcherMaxSize'] = $this->_getMaxSize($imageTypes);
        
        # 视频、FLASH文件配置
        $videoTypes = array_merge(cls_atm::getLocalFilesExts('media'), cls_atm::getLocalFilesExts('flash'));
        $this->configs['videoAllowFiles'] = array_keys($videoTypes);
        $this->_setType($this->configs['videoAllowFiles']);
        $this->configs['videoMaxSize'] = $this->_getMaxSize($videoTypes);
        
        # 其它文件配置
        $fileTypes = cls_atm::getLocalFilesExts('file');
        $this->configs['fileAllowFiles'] = array_keys($fileTypes);
        $this->_setType($this->configs['fileAllowFiles']);
        $this->configs['fileManagerAllowFiles'] = $this->configs['fileAllowFiles'];
        $this->configs['fileMaxSize'] = $this->_getMaxSize($fileTypes);
        return $this->configs;
    }
    
    /**
     * 设置类型，给类型名称变成扩展后缀名称
     * 
     * @param  array $typeValues 要设置的类型数组
     * 
     * @since  nv50
     */
    protected function _setType( array &$typeValues )
    {
        foreach ( $typeValues as &$value ) 
        {
            $value = '.' . $value;
        }
    }
    
    protected function _getMaxSize( array $typeValues )
    {
        $max = 0;
        foreach ( $typeValues as &$value ) 
        {
            $value['maxsize'] *= 1024;
            if ( $value['maxsize'] > $max )
            {
                $max = $value['maxsize'];
            }
        }
        return $max;
    }
    
    /**
     * 格式化数据
     * 
     * @param  mixed  $result 要格式化的数据
     * @return string         返回格式化后的JSON数据
     */
    protected function _formatData( $result )
    {
        return _08_Documents_JSON::encode($result);
    }
    
    public function __call($name, $argc)
    {
        return $this->_formatData(array( 'state'=> '请求地址出错' ));
    }
    
    /**
     * 获取风格工具栏菜单
     * 
     * @param  string $toolbars_name 风格名称
     * @return string                返回风格工具栏菜单数据
     * 
     * @since  nv50
     */
    public function getToolbars($toolbars_name)
    {
        // import下，include_once只包含一次，第二次要`缓存`
		if(empty($this->toolbars[$toolbars_name])){
			$this->toolbars[$toolbars_name] = _08_Loader::import(dirname(__FILE__) . DS . $toolbars_name);
		}
		return $this->toolbars[$toolbars_name];
    }
}