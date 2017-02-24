<?php

/**
 * @author lyq2014
 * @copyright 2014
 */ 
$cuid = 50; //接受外部传chid，但要做好限

$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'a',
	'pchid' => '',
	'caid' => '',
	'url' => '', //表单url，必填，不需要加入mchid
	'select'=>'',
	'from'=>'',
	'where' => "", //附加条件,前面需要[ AND ]
);

$oA = new cls_cuedit($_init);

$oA->top_head(array('setCols'=>1));
//$oA->items_did[] = 'tjdqsj';
$oA->items_did[] = 'valid';


if(!submitcheck('bsubmit')){
    backnav('distribution','add');
	$oA->fm_header("","");
    $oA->fm_items('xingming');
    $oA->fm_items('lxdh');//电话唯一
    $oA->fm_items('xingbie');
    $oA->fm_items('valid'); 
	$oA->fm_items('');		
	$oA->fm_footer('bsubmit');
	$oA->guide_bm('','0');
}else{
	$oA->sv_set_fmdata();//设置$this->fmdata中的值
	$oA->sv_items();//保存数据到数组，此时未执行数据库操作
	$oA->sv_insert();//执行insert
	$oA->sv_upload();//上传处理
    $oA->sv_finish(array('message'=>'添加成功'));
}
	
?>