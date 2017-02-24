<?php
/**
 * MVC模型基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_Models_Base extends _08_Application_Base
{
    protected $_db = null;
	protected $_tblprefix = ''; //兼容之前代码,这个还经常使用,后续尽量用_08_MysqlQuery,可以不要这个变量了。
    
    public function __construct()
    {
        parent::__construct();
        $this->_db = _08_factory::getDBO();
		$this->_tblprefix = cls_env::getBaseIncConfigs('tblprefix');
    }
}
