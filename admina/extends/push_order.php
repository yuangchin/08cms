<?PHP
/*
** 对推送信息刷新排序的窗口化操作
** 可执行单个或多个推送位的排序
*/

(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('normal')) cls_message::show($re);

if(!($paid = cls_PushArea::InitID(@$paid)))  cls_message::show('请指定正确的推送位');

cls_pusher::ORefreshPaid($paid);

cls_message::show('推荐位排序更新完成',"?entry=extend&extend=pushs&paid=$paid");
