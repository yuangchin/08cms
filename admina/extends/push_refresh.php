<?PHP
/*
** 批量同步来源
** 可执行单个或多个推送位的排序
*/

(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('normal')) cls_message::show($re);
if(!($paid = cls_PushArea::InitID(@$paid)))  cls_message::show('请指定正确的推送位');

$num = cls_pusher::RefreshPaid($paid);

cls_message::show("从来源内容同步了{$num}条有效信息","?entry=extend&extend=pushs&paid=$paid");
