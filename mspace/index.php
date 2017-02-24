<?PHP
define('_08_MSPACE', true);
defined('M_UPSEN') || define('M_UPSEN', TRUE);
defined('UN_VIRTURE_URL') || define('UN_VIRTURE_URL', TRUE);//需要处理伪静态
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';

$_params = empty($mid) ? array() : array('mid' => $mid); # 兼容动态页面绑定二级域名,mid在子目录脚本在定义，非GP变量
cls_MspaceIndex::Create($_params);
