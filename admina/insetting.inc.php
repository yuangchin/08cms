<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
$curuser->info['isfounder'] || cls_message::show('只有创始人才可以执行当前操作。');
if(!submitcheck('bsubsit')){
	tabheader('官方架构升级模式','newform',"?entry=$entry");
	trbasic('系统开发模式','',makeradio('mconfigsnew[cms_idkeep]',array(0 => '基本客户模式',2 => '二次开发模式',1 => '官方升级模式'),@$mconfigs['cms_idkeep']),'',
	array('guide' => '基本客户模式：屏蔽部分重要架构的新增及删除、数据表的操作。<br>
	架构定制模式：开放重要架构的新增及删除、数据表的操作，但不使用升级保留id段。<br>
	官方升级模式：最高级模式，开放重要架构的新增及删除、数据表的操作，插入架构资料使用官方升级保留id段。
	'));
	tabfooter();
	
	tabheader('架构保护模式设置');
	trbasic('架构保护模式下受保护的栏目','mconfigsnew[deep_caids]',$mconfigs['deep_caids'],'text',array('w' => 60,'guide'=>'逗号分隔多个id'));
	trbasic('架构保护模式下受保护的类系','mconfigsnew[deep_coids]',$mconfigs['deep_coids'],'text',array('w' => 60,'guide'=>'逗号分隔多个id'));
	trbasic('架构保护模式下受保护的组系','mconfigsnew[deep_gtids]',$mconfigs['deep_gtids'],'text',array('w' => 60,'guide'=>'逗号分隔多个id'));
	tabfooter('bsubsit');
}else{
	$mconfigsnew['deep_caids'] = trim($mconfigsnew['deep_caids']);
	$mconfigsnew['deep_coids'] = trim($mconfigsnew['deep_coids']);
	$mconfigsnew['deep_gtids'] = trim($mconfigsnew['deep_gtids']);
	saveconfig('view');
	cls_message::show('网站设置完成',"?entry=$entry");
}
