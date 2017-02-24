<?php
/**
 * 直播信息添加
 *
 * @author icms <icms@foxmail.com>
 * @copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 *
 */ 
$cuid = 101; //接受外部传chid，但要做好限制
$caid = 606;
$chid = 114;

$aid = empty($aid) ? 0 : max(0,intval($aid));
$aid_url = empty($aid)?'':"&aid=$aid";

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => 0,
	'caid' => $caid,
	'url' => "$aid_url", //表单url，必填，不需要加入mchid
	'select'=>'',
	'from'=>'',
	'where' => "", //附加条件,前面需要[ AND ]
);

$oA = new cls_cuedit($_init);
$oA->top_head(array('setCols'=>1));

if(!submitcheck('bsubmit')){
	$oA->fm_header("","&entry=extend&extend=liveinfo_add$aid_url");
	$oA->fm_items('');		
	$oA->fm_footer('bsubmit');
	$oA->guide_bm('','0');
}else{
	$oA->sv_set_fmdata();//设置$this->fmdata中的值
	$oA->sv_items();//保存数据到数组，此时未执行数据库操作
	$oA->sv_insert(array('aid'=>$aid,'checked'=>1));//执行insert, 附加参数
	$oA->sv_upload();//上传处理
    $oA->sv_finish(array('message'=>'添加成功'));
}
	
?>
