<?php
/**
 * 安装包基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08_INSTALL_EXEC') || exit('No Permission');
abstract class _08_Install_Base
{
    /**
     * 结构数据包
     */
    const SQL_FILE = '08cms.sql';
        
     /**
     * 需要安装的包表数组文件
     */
    const TABLES_FILE = 'tables.php';
   
    /**
     * 语言包对象句柄
     * 
     * @var   object
     * @since nv50
     */
    protected $_langs;
    
    /**
     * GET/POST请求的数据
     * 
     * @var   array
     * @since nv50
     */
    protected $_request;
    
    /**
     * 视图句柄
     * 
     * @var   array
     * @since nv50
     */
    protected $_view;
    
    /**
     * SQL存放路径
     * 
     * @var string
     */
    protected $_sqlPath = '';
    
    /**
     * Load Data方式的SQL存放路径
     * 
     * @var string
     */
    protected $_sqlLoadPath = '';
    
    protected $_lockFile;
    
     /**
     * 所有需要处理表的数组 
     * 
     * @var array
     */
    protected $installTableNames = array();
	
	
    public function __construct()
    {
        $this->_lockFile = (M_ROOT . 'dynamic/install.lock');
        $this->_sqlPath = _08_INSTALL_PATH . 'sql' . DS;        
        $this->_sqlLoadPath = ($this->_sqlPath . 'load_files' . DS);
        $this->_request = cls_envBase::_GET_POST();
        if ( isset($this->_request['task']) )
        {
            $this->_request['task'] = strtolower(trim($this->_request['task']));
        }
        else
        {
        	$this->_request['task'] = null;
        }
        $this->__checkEnvironment();
        
        $this->_view = new _08_View();
		
        $this->installTableNames = @include($this->_sqlPath . self::TABLES_FILE);
    }
    
    /**
     * 检查环境
     * 
     * @since nv50
     */
    private function __checkEnvironment()
    {
        cls_envBase::__checkEnvironment();
        if ( is_file($this->_lockFile) && ($this->_request['task'] != 'complete') )
        {
            $this->_stop('重新安装请先删除 dynamic/install.lock 文件。');
        }
        
       $this->_checkFileOrPath($this->_sqlPath . self::SQL_FILE);
       $this->_checkFileOrPath($this->_sqlPath . self::TABLES_FILE);
    }
    
    /**
     * 终止安装并打印信息
     * 
     * @param string $message 终止时要显示的信息
     * @since nv50
     */
    protected function _stop( $message )
    {
        die($message);
    }    
    
    /**
     * 检查安装路径或文件是否存在，不存在则终止安装
     * 
     * @param string $file 要检查的路径或文件
     */
    protected function _checkFileOrPath( $file )
    {
        if ( !file_exists($file) )
        {
            $this->_stop('安装包不完整，请重新上传。');
        }        
    }
    
    /**
     * 生成安装令牌
     */
    protected function _createInstallToken()
    {
        return md5(cls_string::Random(6));
    }
    
    /**
     * 验证安装令牌
     */
    protected function _checkToken()
    {
        if ( !isset($this->_request['install_token']) || ($this->_request['install_token'] != @$_SESSION['install_token']) )
        {
            $this->_stop('操作非法或已超时。');
        }
    }
    
    /**
     * 获取安装程序的版本信息
     * 
     * @return string 返回安装程序的版本信息
     * 
     * @since  nv50
     */
    protected function _getIversion()
    {
        if ( method_exists('_08_C_Install_Database_Controller', 'getDataPakageName') )
        {
            $_08_C_Install_Database = _08_factory::getInstance('_08_C_Install_Database_Controller');
            $pakageName = $_08_C_Install_Database->getDataPakageName();
        }
        else
        {
        	$pakageName = '';
        }
        
        $msconfigs = cls_envBase::getBaseIncConfigs('mcharset,cms_version');
        $iversion = ' v'.$msconfigs['cms_version'].' '.str_replace('-','',strtolower($msconfigs['mcharset']));
        return $pakageName . '系统 ' . $iversion;
    }
}