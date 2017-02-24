<?PHP
/*
** 管理后台脚本，兼容了文档添加与详情编辑，如果拆分两者脚本，可在详情脚本中去除添加专用部分的代码
** 如通过url传入$chid，可基本兼容不同模型的文档操作
*/
/* 参数初始化代码 */
$chid = 111;//指定chid
#-----------------
$caid = 599;
cls_env::SetG('chid',$chid);
cls_env::SetG('caid',$caid);
$pid = empty($pid)?'':max(1,intval($pid));//初始化合辑id，有可能使用其它id样式传进来，如$hejiid等，要转为使用pid


$oA = new cls_archive();

/* 0为详情编辑，1为文档添加系 */
$isadd = $oA->isadd;
$pchid = 4; //添加时-选择所属合辑(楼盘)
!empty($isadd) && empty($pid) && $pid = -1;//楼盘合辑内添加楼栋时$pid为具体值

$oA->top_head();//文件头部

/* 读取现有可用资料，如模型、字段、及文档 */
$oA->read_data();

/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
$chid = &$oA->chid;
$arc = &$oA->arc;
$channel = &$oA->channel;
$fields = &$oA->fields;
#-----------------
$oA->fields_did[] = 'shapan';//暂时不让添加修改标注，即楼栋坐标
if(!submitcheck('bsubmit')){
	
	if($isadd){//添加才需要
		//添加时预处理类目
		$oA->fm_pre_cns();
	}
	
	//分析当前会员的权限
	$oA->fm_allow();
	
	//($title,$url)，url中可不指定chid或aid
	$oA->fm_header("详情设置","?entry=extend$extend_str&pid=$pid");
	
	//处理合辑，请指定合辑id变量名，留空默认为pid
	if($pid)$oA->fm_album('pid');
	else $oA->fm_album('pid',0,"ajax=exarc_list"); 
	
	//处理栏目，通过传入数组，可指定特别的展示需求，如array('topid' => 5,'hidden' => 1)等
	$oA->fm_caid(array('hidden' => 1));
	
	//($coids)，处理分类，$coids：array(3,4,5)
	$oA->fm_ccids(array());	

	//($arr,$noinc)，$arr字段标识数组，为空则处理所有，$noinc=1排除模式
	$oA->fm_fields(array('subject'),0);
	$oA->fm_fields(array('unit','floor','hushu'),0);
	$oA->fm_fields(array('zxcd','xszt'),0);	
	$oA->fm_fields(array('sgjd','kpsj','jfsj'),0);
	$oA->fm_fields(array('shapan'),0);
	$oA->fm_fields(array('xkzh','dongtai'),0);
	$oA->fm_fields(array(),0);
	
	$oA->fm_footer();
	
	//($title)，$title手动设置标题
	$oA->fm_header('扩展设置','',array('hidden'=>1));
	
	//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids')
	//展示多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	$oA->fm_params(array('createdate','clicks','jumpurl','customurl'));
	
	//输入跟submitcheck(按钮名称)相同的值
	$oA->fm_footer('bsubmit');
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	$oA->fm_guide_bm('','0');
	
}else{
	/*
	** 注意：数据处理端同样要严格指定哪些是需要处理的字段或类系!
	** 
	** 
	*/
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
	//处理多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	$oA->sv_params(array('createdate','clicks','jumpurl','customurl'));
	//$oA->sv_param('arctpls');
	
	//执行自动操作及更新以上变更
	$oA->sv_update();
	
	//上传处理
	$oA->sv_upload();
	
	//要指定合辑id变量名$pidkey、合辑项目$arid
	//要指定合辑id变量名$pidkey、合辑项目$arid
    $_arc = new cls_arcedit; //商业地产-合辑兼容
    $_arc->set_aid($fmdata['pid'],array('au'=>0,'ch'=>0)); 
    $_arid = $_arc->archive['chid']==4 ? 1 : 35;//指定合辑项目id
	$oA->sv_album('pid',$_arid); 
	//$oA->sv_album('pid',1);
	
	//自动生成静态
	$oA->sv_static();
	
	//结束时需要的事务，包括自动生成静态，操作记录及成功提示
	$oA->sv_finish();
}
?>
