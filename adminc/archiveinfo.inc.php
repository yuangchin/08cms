<?php
!defined('M_COM') && exit('No Permission');
foreach(array('cotypes','channels','grouptypes','vcps','currencys','permissions','catalogs',) as $k) $$k = cls_cache::Read($k);
if(!$aid = max(0,intval($aid))) cls_message::show('请指定文档。');
$arc = new cls_arcedit;
$arc->set_aid($aid,array('ch'=>1,'au'=>0,));
tabheader('基本信息');
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