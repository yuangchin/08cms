<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
foreach(array('cotypes','channels','grouptypes','vcps','currencys','permissions','catalogs','mtpls',) as $k) $$k = cls_cache::Read($k);
if(!$aid = max(0,intval(@$aid))) cls_message::show('请指定文档。');
$arc = new cls_arcedit;
$arc->set_aid($aid,array('ch'=>1,'au'=>0,));
tabheader('基本信息 - '.$arc->archive['subject']);

//带参数兼容如下几种情况，这里不合适，就扩展吧？！
$curltype = empty($curltype) ? '0' : $curltype; // 0:一般文档连接(默认), skip:不要连接, m:会员中心连接
$exmobile = empty($exmobile) ? '0' : $exmobile; // 1:要手机版连接, 0:不显示手机版连接(默认) 

if($curltype!='skip'){
	cls_ArcMain::Url($arc->archive,-1);
	$str = '';
	for($i = 0;$i <= @$arc->arc_tpl['addnum'];$i ++) $str .= "&gt;<a href='".$arc->archive['arcurl'.($i ? $i : '')]."' target='_blank'>".($i ? "附$i" : "首页")."</a> &nbsp;";
	trbasic('前台页面预览','',$curltype=='m' ? "&gt;<a href='".$arc->archive['marcurl']."' target='_blank'>详情</a>" : $str,'');
}

if(!empty($exmobile)){
	$arc->ChangeNodeMode(1);
	cls_ArcMain::Url($arc->archive,-1);
	$str = '';
	for($i = 0;$i <= @$arc->arc_tpl['addnum'];$i ++) $str .= "&gt;<a href='".$arc->archive['arcurl'.($i ? $i : '')]."' target='_blank'>".($i ? "附$i" : "首页")."</a> &nbsp;";
	trbasic('手机版预览','',$str,'');
}

trbasic('文档模型','',$arc->channel['cname'],'');
trbasic('作者/ID','',$arc->archive['mname']." &nbsp;/ &nbsp;{$arc->archive['mid']}",'');
trbasic('添加时间','',date("Y-m-d H:i:s",$arc->archive['createdate']),'');
trbasic('更新时间','',date("Y-m-d H:i:s",$arc->archive['updatedate']),'');
trbasic('刷新时间','',date("Y-m-d H:i:s",$arc->archive['refreshdate']),'');
trbasic('到期时间','',$arc->archive['enddate'] ? date("Y-m-d H:i:s",$arc->archive['enddate']) : '-','');
trbasic('审核/编辑','',($arc->archive['checked'] ? '审核': '未审').' &nbsp;/ &nbsp;'.($arc->archive['editor'] ? $arc->archive['editor'] : '-'),'');
trbasic('点击数','',$arc->archive['clicks'],'');
tabfooter();

?>
