<?PHP
/*
** 管理后台脚本，兼容了推送添加与详情编辑
** 通过url指定paid，指定pushid为编辑，否则为添加
*/

$oA = new cls_push();

$oA->top_head();//文件头部

/* 预查资料的完整性及权限 */
$oA->pre_check();

/* 表单开始 */
if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定paid或pushid
	$oA->fm_header("","?entry=extend$extend_str");
	
	//($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array(),0);
	
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('更多设置');
	
	//展示多个属性项
	//可选项目array('startdate','enddate',)
	$oA->fm_params(array());
	
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	
	//分析权限，添加权限或后台管理权限
	$oA->sv_allow();
	
	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	
	//处理多个属性项
	//可选项目array('startdate','enddate','fixedorder',)
	$oA->sv_params(array());
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
		
	//结束时需要的事务，操作记录及成功提示
	$oA->sv_finish();
	
}
?>