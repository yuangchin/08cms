<?php
/**
 * @package   08CMS.Site
 * @copyright Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 */
if (version_compare(PHP_VERSION, '5.2.3', '<'))
{
	die('您的主机需要使用PHP 5.2.3或更高版本才能运行此版本的08CMS !');
}

defined('M_UPSEN') || define('M_UPSEN', TRUE);
defined('UN_VIRTURE_URL') || define('UN_VIRTURE_URL', TRUE);//需要处理伪静态
include_once dirname(__FILE__).'/include/general.inc.php';
# cls_env::CheckSiteClosed(); # 这个要根据不同的入口分别定义
# 慢慢转单一入口架构
if ( _08_factory::getApplication()->run() )
{
    exit;
}
cls_CnodePage::Create();
