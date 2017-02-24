<?php
define('NOROBOT', TRUE);
define('M_UPSEN', TRUE);
define('M_MCENTER', TRUE); // 用于代表以下操作是属性会员中心
include_once dirname(__FILE__).'/include/general.inc.php';
include_once M_ROOT."include/adminm.fun.php";
include_once M_ROOT."include/field.fun.php";

# 通过entry.php进而载入$action.inc.php
cls_AdminmPage::Create(array('isEntry' => true,));