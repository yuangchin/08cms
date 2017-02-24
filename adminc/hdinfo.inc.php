<?php
!defined('M_COM') && exit('No Permission');
cls_cache::Load('cotypes,channels,grouptypes,vcps,currencys,permissions,catalogs');
if(!$aid = max(0,intval($aid))) cls_message::show('请指定文档。');
$arc = new cls_arcedit;
$arc->set_aid($aid,array('ch'=>1,'au'=>0,));
tabheader('基本信息');
cls_ArcMain::Url($arc->archive,-1);
$str = '';
for($i = 0;$i <= @$arc->channel['addnum'];$i ++) $str .= "><a href='".$arc->archive['arcurl'.($i ? $i : '')]."' target='_blank'>".($i ? "附$i" : "首页")."</a> &nbsp;";
trbasic('前台页面展示','',$str,'');
trbasic('文档标题','',$arc->archive['subject'],'');
trbasic('会员名称','',$arc->archive['mname'],'');
trbasic('添加时间','',date("Y-m-d H:i:s",$arc->archive['createdate']),'');
trbasic('更新时间','',date("Y-m-d H:i:s",$arc->archive['updatedate']),'');
trbasic('重发布时间','',date("Y-m-d H:i:s",$arc->archive['refreshdate']),'');
trbasic('到期时间','',$arc->archive['enddate'] ? date("Y-m-d H:i:s",$arc->archive['enddate']) : '-','');
trbasic('审核状态','',($arc->archive['checked'] ? '审核': '解审').'&nbsp;&nbsp;/&nbsp;&nbsp;'.($arc->archive['editor'] ? $arc->archive['editor'] : '-'),'');
trbasic('点击数','',$arc->archive['clicks'],'');
tabfooter();
tabheader('其它信息');
trbasic('模型','',$arc->channel['cname'],'');
tabfooter();

?>