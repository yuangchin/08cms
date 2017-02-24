<?php
/**
 * 应用安装基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_Install_Application_Base extends _08_Install_Base
{    
    /**
     * 开始运行安装向导
     * 
     * @since  nv50
     */
    public function run()
    {
        # 获取模块控制器
		$controller = $this->_fetchController( $this->_request['task'] );
        ob_start();
        $controller->execute();
		$contents   = ob_get_contents();
        ob_end_clean();
        // 如果传递了该参数时，视图里只显示模块的控制器视图内容
        if ( isset($this->_request['datatype']) )
        {
            exit($contents);
        }
        
        $msconfigs = cls_envBase::getBaseIncConfigs('mcharset,cms_version');
        
        # 设置安装版面皮肤名称并把模块控制器传入皮肤内部
        $layouts    = 'layouts';
        $this->_view->assign( array('contents' => $contents, 'iversion' => $this->_getIversion(), 'mcharset' => $msconfigs['mcharset'],
                              'date' => date('Y', time()), 'task' => $this->_request['task']) );
        $this->_view->display($layouts, '.php', _08_INSTALL_PATH . 'view' . DS);
    }
    
    /**
     * 获取控制器
     * 
     * @param  string $task 要获取的任务控制器前缀
     * @return object       返回控制器对象
     * 
     * @since  nv50
     */
    protected function _fetchController( $task )
    {
        if ( empty($task) )
        {
            $task = 'default';
        }
        
        $class = '_08_C_Install_' . ucfirst($task) . '_Controller';
        return _08_factory::getInstance($class);
    }
}