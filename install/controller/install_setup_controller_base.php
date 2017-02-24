<?php
/**
 * 安装配置控制器基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_C_Install_Setup_Controller_Base extends _08_Install_Base
{
    /**
     * 开始执行安装配置
     */
    public function execute()
    {
        $this->_checkToken();
        if ( isset($this->_request['install_database']) )
        {
            $this->__validatorDataBaseInfo($this->_request['install_database']);
            $this->__validatorAdminInfo($this->_request['install_admin']);
            $configs = array('dbhost' => $this->_request['install_database']['dbhost'], 'dbuser' => $this->_request['install_database']['dbuser'],
                             'dbpw' => $this->_request['install_database']['dbpw'], 'dbname' => $this->_request['install_database']['dbname'], 
                             'tblprefix' => $this->_request['install_database']['tblprefix'], 'pconnect' => false, 'dbcharset' => '',
                             'flag' => false);
        }
        else
        {
        	$this->_stop('请先填写完整信息。');
        }
        
        $_08_M_Install_Database = _08_factory::getInstance('_08_M_Install_Database', $configs);
        # 验证通过后创建/base.inc.php文件
        $createBaseIncFileStatus = $_08_M_Install_Database->createBaseIncFile($this->_request['install_database']);
        if ( !$createBaseIncFileStatus )
        {
            $this->_stop('/base.inc.php创建失败，请确保该文件名或所在的目录可读写。');
        }
        
        $urlParams = array('task' => 'execute', 'install_token' => $this->_request['install_token']);
        if ( isset($this->_request['install_database']['extdata']) )
        {
            $urlParams['extdata'] = $this->_request['install_database']['extdata'];
        }
        if ( isset($this->_request['install_database']['backup']) )
        {
            $urlParams['backup_enable'] = $this->_request['install_database']['backup'];
        }
        
        $urlParams['username'] = $this->_request['install_admin']['username'];
        if ( isset($this->_request['install_admin']['email']) )
        {
            $urlParams['email'] = $this->_request['install_admin']['email'];
        }
        if ( isset($this->_request['install_admin']['site_name']) )
        {
            $urlParams['site_name'] = $this->_request['install_admin']['site_name'];
        }
        $urlParams['password'] = $this->_request['install_admin']['password1'];
        file_put_contents(M_ROOT.'dynamic' . DIRECTORY_SEPARATOR . 'site_info.tmp.php', "<?php\r\n return " . var_export($urlParams, true) . ';');
        $urlParams['site_name'] = ''; // nginx对URL的中文要特别处理，换方式保存(iis下也出现乱码...)
        $this->_view->assign( array( 'jumpurl' => ('?' . http_build_query($urlParams)), 'install_token' => $this->_request['install_token'] ) ); 
        $this->_view->display('setup', '.php', _08_INSTALL_PATH . 'view' . DS);
    }
    
    /**
     * 验证数据库表单信息
     * 
     * @param array $install_database 安装界面提交的数据库信息
     */
    private function __validatorDataBaseInfo( array &$install_database )
    {        
        if ( isset($install_database['dbhost']) && (trim($install_database['dbhost']) != '') )
        {
            $install_database['dbhost'] = trim($install_database['dbhost']);
        }
        else
        {
        	$install_database['dbhost'] = 'localhost';
        }
        
        if ( isset($install_database['dbuser']) && (trim($install_database['dbuser']) != '') )
        {
            $install_database['dbuser'] = trim($install_database['dbuser']);
        }
        else
        {
        	$install_database['dbuser'] = 'root';
        }
        
        if ( isset($install_database['dbpw']) )
        {
            $install_database['dbpw'] = trim($install_database['dbpw']);
        }
        else
        {
        	$install_database['dbpw'] = '';
        }
        
        if ( isset($install_database['dbname']) && (trim($install_database['dbname']) != '') )
        {
            $install_database['dbname'] = trim($install_database['dbname']);
        }
        else
        {
        	$this->_stop('数据表名称不能为空。');
        }
        
        if ( !empty($install_database['adminemail']) && !preg_match('/[\w-]+@([\w-]+\.)+[\w-]+/', $install_database['adminemail']) )
        {
            $this->_stop('系统Email不合法。');
        }
        
        if ( empty($install_database['tblprefix']) )
        {
            $this->_stop('数据表前缀不能为空。');
        }
        else if ( preg_match('/^\d|[^\w]/', $install_database['tblprefix']) )
        {
            $this->_stop('数据表前缀不合法。');
        }
    }
    
    /**
     * 验证管理员表单信息
     * 
     * @param array $install_database 安装界面提交的管理员信息
     */
    private function __validatorAdminInfo( array &$install_admin )
    {      
        if ( isset($install_admin['username']) && (trim($install_admin['username']) != '') )
        {
            $install_admin['username'] = trim($install_admin['username']);
            if(preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^Guest/is", $install_admin['username']))
            {
            	$this->_stop('创始人帐号不合法。');
            }
        }
        else
        {
        	$this->_stop('创始人帐号不能为空。');
        }   
        
        if ( isset($install_admin['password1']) && (trim($install_admin['password1']) != '') )
        {
            $install_admin['password1'] = trim($install_admin['password1']);
        }
        else
        {
        	$this->_stop('创始人密码不能为空。');
        }
        
        $passLength = mb_strlen($install_admin['password1'], cls_envBase::getBaseIncConfigs('mcharset'));
        if ( $install_admin['password1'] != @$install_admin['password2'] )
        {
            $this->_stop('创始人密码与重输创始人密码不一致。');
        }
        elseif( ($passLength < 5) || ($passLength > 15) )
        {
            $this->_stop('创始人密码长度不对。');
        }      
        
        if ( !empty($install_admin['email']) && !preg_match('/[\w-]+@([\w-]+\.)+[\w-]+/', $install_admin['email']) )
        {
            $this->_stop('创始人Email不合法。');
        }
    }
}