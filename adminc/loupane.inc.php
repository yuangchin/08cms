<?PHP
//$chid = 4;//指定chid
//cls_env::SetG('chid',$chid);

$mchid = $curuser->info['mchid'];

$oA = new cls_archive();

//0为详情编辑，1为文档添加
$isadd = $oA->isadd;

//文件头部
$oA->top_head();

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 会员中心只能编辑本人发布的文档 */
//$oA->allow_self();
$sql_ids = "SELECT CONCAT(loupan,',',xiezilou,',',shaopu) as lpids FROM {$tblprefix}members_$mchid WHERE mid='$memberid'"; 
$lpids = $db->result_one($sql_ids); //echo $sql_ids.":$lpids<BR>$oA->aid";
if(empty($lpids)) $lpids = 0;
if(!strstr(",$lpids,",','.$oA->aid.',')) $oA->message('对不起，您没有权限管理此楼盘。');

/* 设置表单数据数组名，不设则默认为fmdata */
//$oA->setvar('fmdata','archivenew');

/* 设置允许处理的类系，不设则按主表所有类系 */
//$oA->setvar('coids',array(2,3,4));

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;

$skip = 'onlyold'; //忽略onlyold,onlynew
$ftemp = &$oA->fields; //cls_cache::Read('fields',$chid);
$fields = array(); 
foreach($ftemp as $k => $field){
	if($field['cname']){
		//$field['cname'] = str_replace('楼盘','小区',$field['cname']);
		if(!isset($field[$skip])) $fields[$k] = $field; 
	}
} 
unset($fields['jgjj'],$fields['jdjj'],$fields['bdsm']);
$oA->fields = $fields;

#-----------------

if(!submitcheck('bsubmit')){
	
	if($isadd){//添加才需要
		//添加时预处理类目
		//会员中心设置提示信息,如下a,b样式。limit,valid数据，请先计算出。
		//a: $madd_msg = $oA->getmtips(array('check'=>1,'limit'=>array($rules['total'],$total),'valid'=>array($rules['valid'],$valid),),'');
		//   $oA->fm_guide_bm("madd_ch02",'fix'); //madd_ch02中这是占位符号{$madd_msg},则$madd_msg会自动加到madd_ch02中去。
		//b: $msg = $oA->getmtips(array('check'=>1,'limit'=>array($rules['total'],$total),),'');
		//   $oA->fm_guide_bm($msg,'fix');
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	/*
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("","?action=$action");
	
	//处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_album('pid');
	
	$oA->fm_caid(array('hidden'=>1)); //处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	// 处理类系,区域-商圈,地铁-站点,其它类系
	$oA->fm_rccid1(); // 1,2,
	$oA->fm_rccid3(); // 3,14
	$oA->fm_ccids(array()); 
	// 选择项提前
	$oA->fm_fields(array('zxcd','lcs','hxs','tslp')); 
	$oA->fm_footer();
	
	$fix_arr = array('wydz','ltbk','keywords','abstract');
	$oA->fm_header('基本资料');
	$oA->fm_fields(array('subject','kprq','jfrq','dj','dt',)); //提前项目
	//$oA->fm_relalbum('5',12, '楼盘视频');
	//$oA->fm_relalbum('6',13, '楼盘开发商');
	$oA->fm_fields(array_merge($fix_arr,array('content','thumb','loupanlogo','lphf','lppmtu',)),1); //排除项目
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('图文说明');
	$oA->fm_fields(array('content'));
	$oA->fm_fields_other($fix_arr); //排除项目,放后面
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('扩展设置','',array('hidden'=>1));
	$oA->fm_fields($fix_arr);
	$oA->fm_params(array('jumpurl','ucid','createdate','clicks',));
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	*/
	//($title,$url)，url中可不指定chid或aid
	//$oA->coids_showed = array('41'); //不显示
	$oA->fm_header("$channel[cname] - 基本属性","?action=$action");
	$oA->fm_album('pid'); //处理合辑，请指定合辑id变量名，留空默认为pid
	$oA->fm_caid(array('hidden'=>1)); //处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_fields(array('subject')); //标题
	$oA->fm_ccids(array('12','18')); //物业类型,销售状态
	$oA->fm_fields(array('kprq','jfrq','dj')); //开盘日期,交房日期,均价
	$oA->fm_fields(array('zxcd','lcs','tslp')); //装修程度,楼层,特色楼盘,
	$oA->fm_footer();
	
	$oA->fm_header("$channel[cname] - 地理信息");
	$oA->fm_rccid1(); // 1,2,区域
	$oA->fm_rccid3(); // 3,14,地铁
	$oA->fm_fields(array('hxs','address','jtxl','dt','pano')); //环线,楼盘地址,交通线路,地图,街景场景ID
	$oA->fm_footer();
	
	$oA->fm_header("$channel[cname] - 服务信息");
	$oA->fm_fields(array('tel','sldz')); //销售电话,售楼地址
	$oA->fm_fields(array('wyf','wygs','wydz')); //物业费,物业公司,物业地址
	//$oA->fm_relalbum('6',13, '楼盘开发商');
	$oA->fm_fields(array('xkzh','ltbk','qtbz')); //许可证号,论坛板块,其他备注
	$oA->fm_footer();
    
	// bgmj,symj,kjmj,bzccg,bzcmj,dtcg,dtmj,dts,dtfq,ktkfsj,wltx,wsj,afxt,gsfs,gdxt,pfxt,pwxt
	// spzmj,zlc,mk,js,tygl,wlm,dccg,wsj,dts,dtfq,ktkfsj,wltx,gsfs,gdxt,pfxt,pwxt,lnpt
    if($oA->arc->archive['chid']!=4){
    	$oA->fm_header("$channel[cname] - 商业信息");
    	$oA->fm_fields(array('bgmj','symj','kjmj','bzccg','bzcmj','dtcg','dtmj','dts','dtfq','ktkfsj','wltx','wsj','afxt','gsfs','gdxt','pfxt','pwxt','spzmj','zlc','mk','js','tygl','wlm','dccg','wsj','dts','dtfq','ktkfsj','wltx','gsfs','gdxt','pfxt','pwxt','lnpt'));
    	$oA->fm_footer();
    }
	
	$a1 = array('thumb','loupanlogo','lphf','lppmtu'); //图
	$a2 = array('keywords','abstract');
	$oA->fm_header("$channel[cname] - 其它属性");
	//$oA->fm_ccids(); //显示其它类系
	$oA->fm_fields_other(array_merge($a1,$a2,array('content'))); //排除项目,放后面
	$oA->fm_footer();
	
	$oA->fm_header('图文说明');
	//$oA->fm_relalbum('5',12, '楼盘视频');
	$oA->fm_fields_other(array_merge($a2,array('content'))); //图
	$oA->fm_fields(array('content'));
	$oA->fm_footer();
	
	$oA->fm_header('扩展设置','',array('hidden'=>1));
	//$oA->fm_fields_other(); //剩余,自定义
	$oA->fm_fields($a2);
	$oA->fm_params(array());
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{

	if($isadd){
		
		//需传入验证码类型，否则默认为'archive'
		$oA->sv_regcode('archive');
		
		//添加时预处理类目，可传$coids：array(1,2)
		$oA->sv_pre_cns(array());
		
	}
	
	//分析权限，添加权限或后台管理权限
	$oA->sv_allow();
	
	if($isadd){
		//增加一个文档
		if(!$oA->sv_addarc()){
			//添加失败处理
			$oA->sv_fail();
		}
	}
	
	//类目处理，可传$coids：array(1,2)
	$oA->sv_cns(array());

	//字段处理，可传$nos：array('ename1','ename2')
	$oA->sv_fields(array());
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	//处理多个属性项，管理后台默认为array('createdate')，会员中心默认为array('ucid')
	$oA->sv_params(array());
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();

	//要指定合辑id变量名$pidkey、合辑项目$arid
	$oA->sv_album('pid',0);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>

