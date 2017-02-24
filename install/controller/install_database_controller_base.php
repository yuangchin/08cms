<?php
/**
 * 数据库设置控制器基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_C_Install_Database_Controller_Base extends _08_Install_Base
{
    final public function execute()
    {   
        $this->_checkToken();
        $this->_view->assign(
            array(
                'task' => $this->_request['task'], 
                'configs' => $this->__getBaseIncConfigs(),
                'admininfo' => $this->__getAdminInfo(), 
                'install_token' => $this->_request['install_token'],
                'sql_file' => $this->_sqlPath . parent::SQL_FILE
            )
        );
        
        if ( method_exists($this, 'getDataPakageName') )
        {
            $this->_view->assign( 'pakageName', $this->getDataPakageName() );
        }
        else
        {
        	$this->_view->assign( 'pakageName', '测试' );
        }
         
        $this->_view->display('database', '.php', _08_INSTALL_PATH . 'view' . DS);
    }
    
    /**
     * 获取数据库配置信息
     * 
     * @since nv50
     */
    private function __getBaseIncConfigs()
    {
        $configs = cls_envBase::getBaseIncConfigs('dbhost, dbuser, dbpw, dbname, tblprefix, adminemail');
        empty($configs['dbhost']) && $configs['dbhost'] = 'localhost';
        empty($configs['dbuser']) && $configs['dbuser'] = 'root';
        empty($configs['dbpw']) && $configs['dbpw'] = '';
        $pre = cls_string::Random(1, 2);
        empty($configs['dbname']) && ($configs['dbname'] = ($pre . cls_string::Random(7)));
        empty($configs['tblprefix']) && ($configs['tblprefix'] = ($pre . cls_string::Random(3) . '_'));
        empty($configs['adminemail']) && $configs['adminemail'] = 'admin@your.com';
        
        return $configs;
    }
    
    /**
     * 获取管理员信息
     * 
     * @since nv50
     */
    private function __getAdminInfo()
    {
        return array('username' => 'admin', 'password1' => 'admin', 'email' => 'admin@domain.com', 'site_name' => '');
    }
}