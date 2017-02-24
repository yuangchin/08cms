<?PHP
/* 参数初始化代码 */
$_init = array(//会员中心可以不传入任何参数
);
#-----------------
$cuid = 42; 
$commu = cls_cache::Read('commu',$cuid); 

$memid = $curuser->info['mid'];
$mchid = $curuser->info['mchid'];
$mname = $curuser->info['mname'];
$grp34 = $curuser->info['grouptype34'];

$fadd = 0; // 增加交互
$fedt = 1; // 修改标记(专家名称)
if($grp34){
	$title = "专家资料修改";	
	$fedt = 0;
}else{
	$title = '专家申请';
	$val = $db->result_one("SELECT mid FROM {$tblprefix}$commu[tbl] WHERE mid='$memid'");
	if($val){ // 
		$title .= ' --- (资料待审核,可继续修改资料)';
	}else{
		$title .= ' --- (新申请)';
		$fadd = 1;
	}
} 


$oA = new cls_member($_init);
$oA->TopHead();//文件头部
$oA->TopAllow();//分析操作权限

$mfexp = array('dantu','ming','danwei','quaere');
foreach($oA->fields as $k => $v){//后台架构字段
	if(in_array($k,$mfexp)){
		$oA->additem($k,array('_type' => 'field'));
	}
}

if(!submitcheck('bsubmit')){
	
	//($title,$url)，url中可不指定mchid或mid
	$oA->fm_header($title,"?action=$action");
	$oA->fm_items();
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	if(!$fedt) echo "<script type='text/javascript'>\$id('fmdata[ming]').readOnly = true;\$id('fmdata[ming]').style.border=0;</script>";
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	
	//提交后的处理
	//$oA->sv_all_common(array('message'=>'专家资料设置完成！'));
	
	/*
		if(empty($fmdata['dantu'])){
			$member_image = $db->result_one("select image from {$tblprefix}members_sub where mid = '$memberid'");
			$fmdata['dantu'] = 	$member_image;
		}	
		$fmdata['ming']=empty($fmdata['ming'])?$mname:$fmdata['ming'];	
	*/
	
	//设置$this->fmdata中的值
	$oA->sv_set_fmdata();
	
	//这个需要在mname,password,email之后执行
	$oA->sv_add_init();
	
	//进行余下的所有项目处理，此时未执行数据库操作
	$oA->sv_items();
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
	//增加交互
	if($fadd){
		$sqlins = "mid='$memid',mname='{$curuser->info['mname']}',createdate='$timestamp',checked='".@$commu['autocheck']."'";		
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET $sqlins"); // ip='$onlineip',
	}
	//结束时需要的事务，包括操作记录、成功提示等
	$oA->sv_finish(array('message'=>'专家资料设置完成！'));	
}
?>
