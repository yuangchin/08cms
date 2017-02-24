<?PHP
defined('M_UPSEN') || define('M_UPSEN', TRUE);
defined('NOROBOT') || define('NOROBOT', TRUE);
defined('UN_VIRTURE_URL') || define('UN_VIRTURE_URL', TRUE);//需要处理伪静态
include_once dirname(__FILE__).'/include/general.inc.php';

$_QueryParams = cls_env::_GET_POST();
if(!empty($_QueryParams['aid'])){
	cls_FarchivePage::Create();
}else{
	cls_FreeinfoPage::Create();
}