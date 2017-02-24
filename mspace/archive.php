<?php
define('_08_MSPACE', true);
defined('M_UPSEN') || define('M_UPSEN', TRUE);
defined('UN_VIRTURE_URL') || define('UN_VIRTURE_URL', TRUE);//需要处理伪静态
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';

cls_MspaceArchive::Create(); # 暂不支持空间域名绑定???
