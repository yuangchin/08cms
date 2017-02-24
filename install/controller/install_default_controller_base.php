<?php
/**
 * 默认安装控制器基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_C_Install_Default_Controller_Base extends _08_Install_Base
{
    /**
     * PHP环境扩展检测，目前只支持PHP5.2和PHP5.3的环境检测
     * 
     * @var   array
     * @since nv50
     */
    private $extension_loaded = array('curl', 'openssl', 'sockets', 'mbstring', 'gd', 'mcrypt', 'mysql', 'mysqli', 'pdo', 'pdo_mysql');
    
    private $paths = array('./', './base.inc.php', './template', './mspace',
                           './userfiles 和 ./userfiles/*' => './userfiles', './dynamic 和 ./dynamic/*' => './dynamic');
    
    final public function execute()
    {
        if ( !IS_WIN )
        {
            array_push($this->extension_loaded, 'zlib');
        }  
        /**
         * 环境检测：
         * 扩展系统如果有使用到不同的扩展则：新建一个类名为：_08_C_Install_Default_Controller 然后继承本类，
         * 再写一个名为：getExtensionLoaded 的方法，返回一个存放需要检测的扩展数组即可。
         */
        if ( method_exists($this, 'getExtensionLoaded') )
        {            
            $this->extension_loaded = array_merge( (array) $this->getExtensionLoaded(), $this->extension_loaded );
        }
        
        /**
         * 目录文件夹权限检查
         */        
        if ( method_exists($this, 'getPaths') )
        {
            $this->paths = array_merge( (array) $this->getPaths(), $this->paths );
        }
        
        $_SESSION['install_token'] = $this->_createInstallToken();
        $this->_view->assign( array('extension_loaded' => $this->extension_loaded, 'paths' => $this->paths,
                                    'install_token' => $_SESSION['install_token']) );        
        $this->_view->display('default', '.php', _08_INSTALL_PATH . 'view' . DS);
    }
}