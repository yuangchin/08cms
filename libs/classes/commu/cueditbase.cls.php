<?php
/*
** 单个交互添加或编辑的操作类, 添加主要在前台etools下，要考虑兼容
** sv方法中设置return_error表示出错时返回error，而不是用message跳出
*/
!defined('M_COM') && exit('No Permisson');
class cls_cueditbase extends cls_cubasic{

	public $isadd = 0;//添加模式
	public $fmpre = 'fmdata';//form中的数组名称//允许自行设置
	public $predata = array();//预定资料数组
	public $enddata = array();//处理后的资料数组

	public $cfgs = array();//设置项配置
	public $items_did = array();//暂存已处理过的项目
	public $fmdata = array();//表单提交后的数据数组
	public $fulldata = array();//完整数据,包含cid,checked等
	
	public $pinfo = array();//交互对象info
	public $fnoedit = array(); //不允许编辑字段, 如'fnoedit' => array('jiaxiaokoubei','kaoshihegelv'),  //驾校口碑,考试合格率
	
    function __construct($cfg = array()){
		parent::__construct($cfg);
		$this->cid = empty($cfg['cid']) ? 0 : max(0,intval($cfg['cid']));
		if($this->cid){
			$this->isadd = 0;
		}else{
			$this->isadd = 1;
		} 
		if(!empty($cfg['fmpre'])) $this->fmpre = $cfg['fmpre'];
		if(isset($cfg['fnoedit'])){	
			$this->fnoedit = $cfg['fnoedit'];
		}
    }
	
	// 添加交互，初始化检查，在前台/etools中使用,不能用top_head()
	// pchid: 认证chids, 0-不认证,1认证; pchid不用具体指定某个pid的文档/会员模型
	// setCols: $this->additems();
    function add_init($pid=0,$pnullmsg='',$cfg=array()){
		global $inajax, $infloat, $handlekey, $in_mobile; 
		//处理手机版提交:in_mobile=1, 提示信息使用手机版样式
		if(!empty($in_mobile)) define('IN_MOBILE', TRUE);
		$curuser = cls_UserMain::CurUser();
		empty($pnullmsg) && @$pnullmsg = "请指定[{$this->cucfgs['cname']}]对象"; 
		empty($inajax) || $this->A['url'] .= "&inajax=$inajax";
		
		$burl = "?cuid=$this->cuid";
		$this->burl = $burl;
		$this->A['url'] = $this->burl.(empty($this->A['url']) ? '' : $this->A['url']);
		foreach(array('infloat','inajax','js') as $k){
			global $$k; $v = $$k;
			if($v && !strstr('$v=',$this->A['url'])) $this->A['url'] .= "&$k=$v";
		}
		
		$this->cucfgs || $this->message('不存在的交互项目。');  
		$pmsg = $curuser->noPm($this->cucfgs['pmid']); 
		if(isset($this->cucfgs['pmid']) && !empty($pmsg)) $this->message("无权限提示：\n$pmsg"); 
		if(empty($this->cucfgs['available'])) $this->message('该功能已关闭。');
		if(!empty($this->A['pchid']) && $pid){
			$pinfo = $this->pinfo = $this->getPInfo($this->A['ptype'],$pid,@$cfg['pdetail']); 
			$pinfo || $this->message($pnullmsg); 
			$pchid = @$pinfo[($this->A['ptype']=='m'?'m':'').'chid'];
			if(empty($this->cucfgs['chids']) || !in_array($pchid,$this->cucfgs['chids'])){
				$this->message($pnullmsg);	
			}
		}elseif(!empty($this->A['pchid']) && empty($cfg['pidskip'])){
			$this->message($pnullmsg);
		}
		// 处理cfg参数
		//if(!empty($cfg['chkData'])){
			//if(empty($this->predata)) $this->message('不存在数据！'); 
		//}
		if(!empty($cfg['setCols'])){
			$this->additems();
		}
	}
	// 添加交互，加载通用头部html
    function add_header(){
		global $inajax, $infloat, $handlekey;
		$cms_abs = cls_env::mconfig('cms_abs');
		$mc_dir = cls_env::mconfig('mc_dir');
		include_once M_ROOT."./include/adminm.fun.php"; // 要用 _header(),_footer()函数
		if(empty($inajax)){
			_header(); 
		}
	}
	// 添加交互，加载通用页面结束html
    function add_footer(){
		global $inajax, $infloat, $handlekey;
		if(empty($inajax)){
			_footer();
		}
	}
	// 添加交互，显示交互对象的一个连接行
    function add_pinfo($cfg=array()){
		if(empty($this->pinfo) && !empty($cfg['pid'])){ 
			$this->pinfo = $this->getPInfo($this->A['ptype'],$cfg['pid']); 
		}
		$title = empty($cfg['title']) ? ($this->ptype=='m' ? '被评会员' : '被评文档') : $cfg['title'];
		$link = $this->getPLink($this->pinfo, $cfg);
		echo trbasic($title,'',$link,'');
	}
	
	
	function setvar($key,$var){
		$this->$key = $var;	
	}

	//清除设置项目
	function del_item($key){
		unset($this->cfgs[$key]);
		return false;
	}
	
	//是否一个已存在的项目
	function is_item($key = ''){
		return isset($this->cfgs[$key]) ? true : false;
	}
	
	// 复制一个字段，用于回复等 $oA->addcopy('content', 'reply','','0');
	function addcopy($from='content', $to='reply', $title='', $notnull='def'){
		$this->fields[$to] = $this->fields[$from];
		$this->fields[$to]['ename'] = $to; 
		$this->fields[$to]['cname'] = $title ? $title : '回复'; 
		if(!($notnull==='def')) $this->fields[$to]['notnull'] = $notnull; 
		$this->additem($to,array('_type' => 'field'));
	}
	
	// (添加/编辑)添加架构字段
	// $copy:复制的一个字段，用于回复等 $oA->fm_additems(array('content'=>'reply'));
	function additems($copy = array()){
		foreach($this->fields as $k => $v){//后台架构字段
			$this->additem($k,array('_type' => 'field'));
			if(isset($copy[$k])){
				$this->addcopy($k, $copy[$k]);
			}
		}
	}
	
	//添加设置项目，进行项目初始化
	function additem($key,$cfg = array()){
		$this->cfgs[$key] = $cfg;
		$re = $this->one_item($key,'init');
		if($re == 'undefined') $this->del_item($key);
	}
	
	function call_method($func,$args = array()){
		if(method_exists($this,$func)){
			return call_user_func_array(array(&$this,$func),$args);
		}else return 'undefined';
	}
	
	//方法优先次序：user_$key(定制) -> type_$type(类型) -> 通用方法
	//操作项方法需要处理：init：初始化 fm：显示 sv：数据处理
	function one_item($key,$mode = 'init'){
		if(!isset($this->cfgs[$key])) return false;
		$re = $this->call_method("user_$key",array($key,$mode));//定制方法
		if($re == 'undefined'){
			switch($this->cfgs[$key]['_type']){
				case 'field':
					$re = $this->type_field($key,$mode);
				break;
				//case 'ugid':
					//$re = $this->type_ugid($key,$mode);
				//break;
			}
		}
		if(in_array($mode,array('fm','sv',))) $this->items_did[] = $key;//记录已处理的项目
		return $re;
	}
	
	// cfg['hidden'] = 1 : 隐藏[高级设置]
	// $cfg['hidstr'] : 高级设置的提示信息
	function fm_header($title = '',$url = '',$cfg = array()){
		if(!empty($cfg['hidden'])){ 
			global $setMoreFlag;
			$cfg['hidstr'] = empty($cfg['hidstr']) ? "高级设置" : $cfg['hidstr'];
			$setMoreFlag = str_replace('.','',microtime(1));
			$title = "<span id='setMore_$setMoreFlag' style='display:inline-block;float:right;cursor:pointer' onclick='setMoerInfo(\"$setMoreFlag\",".$this->mc.")'> $cfg[hidstr] </span>$title";
		}
		
		$title || $title = $this->cucfgs['cname'].'&nbsp; -&nbsp; '.($this->isadd ? '添加交互' : '交互详情');
		
		$url = $this->burl.(empty($url) ? '' : $url);
		if(!in_str('cid=',$url)) $url .= "&cid={$this->cid}"; 
		if($url){
			//if($this->isadd){ //后台等,添加【对文档/会员】交互,如何处理？
			//}else{
			//}
			tabheader($title,'cudetial',$url,2,1,1);
		}else{
			tabheader($title);
		}
	}
	
	// 展示指定项，$incs为空表示为所有剩余项
	// $noinc=array()，在$incs基础上排除$noinc中的字段，为空则不排除。
	// $cfg[noaddinfo] : 是否显示添加者信息, 1:不显示, 0:显示(默认)
	function fm_items($incs='', $noinc=array(), $cfg=array()){
		if(!empty($incs)) $incs = array_filter(explode(',',$incs));
		if(empty($incs)) $incs = array_keys($this->cfgs);//展示剩余项
		foreach($incs as $key){
			if(!empty($noinc) && in_array($key,$noinc)) continue;
			if(!in_array($key,$this->items_did)){
				$this->one_item($key,'fm');
			}
		}
		if(empty($this->isadd) && empty($cfg['noaddinfo'])){ //修改状态下, 显示发布者相关信息
			$data = $this->predata['mname'].' (ID:'.$this->predata['mid'].')';
			trbasic('发布者','',$data,'');	
			$date = $this->predata['createdate'];
			trbasic('发布日期','',($date ? date('Y-m-d H:i:s',$date) : '-'),'');
			if(isset($this->predata['ip'])){ // ??? 
				$ip = $this->predata['ip'];
				trbasic('发布者IP','',$ip,'');	
			}
		}
	}
	
	function fm_footer($button = '',$bvalue = ''){
		tabfooter($button,$button ? ($bvalue ? $bvalue : ($this->isadd ? '添加' : '提交')) : '');
		global $setMoreFlag; //处理隐藏[高级设置]的初始化js
		if(!empty($setMoreFlag)){
			echo '<script type="text/javascript">setMoerInfo("'.$setMoreFlag.'",'.$this->mc.')</script>';
			$setMoreFlag = '';	
		}
	}
	
	/**
	*显示验证码
	* @param    string    $type  验证码类型  默认为archive
	*					  type的值可以在/dynamic/syscache/cfregcodes.cac.php里配置
	*/
	//显示验证码
	function fm_regcode($type='commu',$params = array()){
		if($type && $this->isadd && $this->mc){
			tr_regcode($type,$params);
		}
	}
	
	//处理验证码
	function sv_regcode($type='commu', $return_error = 0){
		global $regcode;
		if($type && $this->isadd && $this->mc){
			if(!regcode_pass($type,empty($regcode) ? '' : trim($regcode))) return $this->message('验证码错误',axaction(2,M_REFERER),$return_error);
		}
	}
	
	//(仅编辑使用)编辑提交后,通用的提交后处理，可以通过定制方法进行扩展
	//根据设置项及初始化配置进行综合处理
	//cfg[message]:提示信息
	function sv_all_common($cfg = array()){
		
		$this->sv_set_fmdata();//设置$this->fmdata中的值
		$this->sv_items();//保存数据到数组，此时未执行数据库操作
		$this->sv_update();//执行自动操作及更新以上变更
		$this->sv_upload();//上传处理
		$this->sv_finish($cfg);//结束时需要的事务，包括操作记录、成功提示等
		
	}
	
	//处理指定项，否则展示所有剩余项
	function sv_items($incs = ''){
		if(!empty($incs)) $incs = array_filter(explode(',',$incs));
		if(empty($incs)) $incs = array_keys($this->cfgs);//保存剩余项
		foreach($incs as $key){
			if(!in_array($key,$this->items_did)){
				$this->one_item($key,'sv');
			}
		}
	}
	
	// 更新回复时间
	//$oA->sv_retime('replydate','reply'); 有回复内容才更新
	function sv_retime($key,$rek=''){
		if(!empty($this->enddata[$rek])){ 
			$this->enddata[$key] = TIMESTAMP;	
		}
	}
	
	// 通用保存一个架构外的字段(一定是指定的某个字段)
	// $oA->sv_excom('checked',!empty($reply)); 
	// iskey : 从fmdata中取key对应的值
	function sv_excom($key,$val=0,$iskey=0){
		$val = $iskey ? $this->fmdata[$val] : $val; //不用屏蔽错误,好调试。
		$this->enddata[$key] = $val;
	}
	
	function sv_set_fmdata(){
		$this->fmdata = &$GLOBALS[$this->fmpre];//因为字段处理方法未进一步优化，这里需要是引用
	}	
	
	//添加交互之后，更新内容时出现异常的话，需要删除新增加的交互记录
	//mname,password,email之外项目的意外错误，都需要使用此方法
	function sv_rollback(){
		if($this->cid && $this->isadd){
			$c_upload = cls_upload::OneInstance();
			$this->delete($this->cid);
			$c_upload->closure(1);
		}
	}
	
	function sv_fail($return_error = 0){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1);
		return $this->message('交互添加失败',M_REFERER);
	}
	
	// 执行插入数据：内定数据, 模型设置数据, arr附加数据(如aid=>$aid,tocid=>$tocid)
	// 执行加分
	function sv_insert($arr=array()){
		global $timestamp;
		$curuser = cls_UserMain::CurUser();
		$data = &$this->enddata;
		if(empty($curuser->info['mid'])){
			$_dm['mid'] = 0;
			$_dm['mname'] = '游客';
		}else{
			$_dm['mid'] = $curuser->info['mid'];
			$_dm['mname'] = $curuser->info['mname'];
		}
		if(isset($arr['checked'])){ //参数优先
			$_dm['checked'] = $arr['checked'];
		}elseif(!isset($this->cucfgs['autocheck'])){ // 无配置,默认为1
			$_dm['checked'] = 1;	
		}else{
			if($ischeck = $curuser->pmautocheck(@$this->cucfgs['autocheck'])) $_dm['checked'] = 1;
			else $_dm['checked'] = 0;
		}
		$_dm['createdate'] = $timestamp;
		if(!empty($arr)){ //检测附加的字段是否存在
			$dbfields = empty($arr) ? array() : $this->getFields();
			foreach($arr as $k=>$v){
				if(!in_array($k,$dbfields)){ unset($arr[$k]); }
				//if(isset($data[$k])) { unset($arr[$k]); }
				//else{ $arr[$k] = cls_string::SafeStr($arr[$k]); }
			}
		}
		$this->fulldata = $data = array_merge($_dm, $data, $arr); // 后者数据优先
		$flist = ''; $fdata = array();
		foreach($data as $k=>$v){
			$flist .= (empty($flist) ? '' : ' ,')."$k ";
			$fdata[] = $v;
		}
		$this->db->insert($this->table(), $flist,array($fdata))->exec();
		$this->cid = $this->db->insert_id();
		if(!$this->cid){
			$this->message('添加错误！');		
		}else{
			empty($_dm['mid']) || $this->setCrids('add', $_dm['mid']);	
			cls_commu::autopush($this->cuid, $this->cid); //自动推送
		}
        
        return $this->cid;
	}
	
	//执行自动操作及更新以上变更
	//update之前,可根据需要扩展:如：$this->enddata += array('myupdate'=>TIMESTAMP);
	function sv_update(){
		$this->db->update($this->table(), $this->enddata)->where('cid='.$this->cid)->exec();
	}
	
	//上传处理
	function sv_upload(){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1,$this->cid,'comments'); //交互的附件,怎么存？"commu$this->cuid"
		$c_upload->saveuptotal(1);
	}
	
	// 重复操作检查与设置
	// act=check,save,both
	function sv_repeat($arr=array(), $act='check'){
		global $m_cookie;
		$curuser = cls_UserMain::CurUser(); $mid = $curuser->info['mid'];
		$key = "08cms_cuid_{$this->cuid}_{$mid}_";
		empty($arr) || $key .= implode('_',$arr);
		if(!empty($this->cucfgs['repeattime'])){
			if(in_array($act,array('check','both'))){ //检查
				empty($m_cookie[$key]) || $this->message('操作请不要过于频繁。',axaction(2,M_REFERER));
				if($act=='both') $this->sv_repeat($arr, 'save');	
			}
			if(in_array($act,array('save','both'))){ //保存
				msetcookie($key,1,$this->cucfgs['repeattime'] * 60);
			}
		}
	}
		
	# 处理一条交互(如评论)投票(顶1,踩2)
	function sv_Vote($cid, $fix='opt', $no='1', $nos='1,2', $add=1){
		global $m_cookie;
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$cid = intval($cid);
		$field = strstr(",$nos,",",$no,") ? "$fix$no" : '';
		$key = "08cms_1Vote_{$this->cuid}_{$cid}_$field";
		// 3状态： 成功, 已投票, 错误
		if($field && empty($m_cookie[$key])){ //有效
			msetcookie($key,1,365 * 86400);
			$re = $this->db->query("UPDATE {$tblprefix}".$this->cucfgs['tbl']." SET $field=$field+$add WHERE cid='$cid'");
			return ($re) ? 'OK' : 'Error'; //成功/失败
		}elseif($field){ //已投票
			return 'Repeat';
		}else{ //错误
			return 'Error';
		}	
	}
	
	# 处理文档心情投票(好文/枪手/雷人/囧) [如看完新闻后您的评价是]
	// $pfield数据库确认存在的字段
	function sv_Mood($pfield='aid', $fix='opt', $no='1', $nos='1,2', $add=1){
		global $m_cookie, $timestamp;
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$curuser = cls_UserMain::CurUser();
		$pfield = preg_replace('/[^\w]/', '', $pfield);
		if(empty($no) && empty($nos)){
			$field = $fix; 
		}else{
			$field = strstr(",$nos,",",$no,") ? "$fix$no" : '';
		} //echo "($field)"; 
		$pid = $this->pinfo['_pid'];
		$key = "08cms_1Mood_{$this->cuid}_{$pid}_0"; //$field(一个pid只投一次)
		// 3状态： 成功, 已投票, 错误
		if($field && empty($m_cookie[$key])){ //有效
			msetcookie($key,1,365 * 86400);
			if(!($row = $this->db->fetch_one("SELECT cid FROM {$tblprefix}".$this->cucfgs['tbl']." WHERE $pfield='$pid'"))){
				$acheck = empty($this->cucfgs['autocheck']) ? '' : ",checked=1";
				$sql = "INSERT INTO {$tblprefix}".$this->cucfgs['tbl']." SET $pfield='$pid',$field='1',createdate='$timestamp'$acheck";
			}else{
				$sql = "UPDATE {$tblprefix}".$this->cucfgs['tbl']." SET $field=$field+1 WHERE $pfield='$pid'";
			}
			$re = $this->db->query($sql);
			return ($re) ? 'OK' : 'Error'; //成功/失败
		}elseif($field){ //已投票
			return 'Repeat';
		}else{ //错误
			return 'Error';
		}	
	}
	
	# 处理文档/会员的收藏
	// $pfield数据库确认存在的字段
	function sv_Favor($pfield='aid'){
		global $m_cookie, $timestamp, $onlineip;
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$curuser = cls_UserMain::CurUser();
		$memberid = $curuser->info['mid'];
		$pfield = preg_replace('/[^\w]/', '', $pfield);
		$pid = @$this->pinfo['_pid'];
		$key = "08cms_Favor_{$this->cuid}_{$memberid}_{$pid}"; //加{$memberid}为考虑切换用户测试收藏;
		if(!$memberid) return 'noLogin'; //$oA->message('请先登录会员。'); //; 
		// 3状态： 成功, 已收藏, 错误
		if(empty($m_cookie[$key])){ //有效
			msetcookie($key,1,365 * 86400);
			if(!($row = $this->db->fetch_one("SELECT cid FROM {$tblprefix}".$this->cucfgs['tbl']." WHERE mid='$memberid' AND $pfield='$pid'"))){
				$acheck = empty($this->cucfgs['autocheck']) ? '' : ",checked=1";
				$sql = "INSERT INTO {$tblprefix}".$this->cucfgs['tbl']." SET $pfield='$pid',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp'$acheck "; 
				$re = $this->db->query($sql); //chid='{$arc->archive['chid']}',
				return ($re) ? 'OK' : 'Error'; //成功/失败
			}else{
				return 'Repeat';
			}
			$re = $this->db->query($sql);
			return ($re) ? 'OK' : 'Error'; //成功/失败
		}else{ //错误
			return 'Repeat';
		}	
	}

	//结束时需要的事务， 如：操作记录及成功提示
	function sv_finish($cfg = array()){
		if(empty($cfg['message'])) $cfg['message'] = '交互'.($this->isadd ? '添加' : '修改').'完成';
		if(empty($cfg['record'])) $cfg['record'] = ($this->isadd ? '添加' : '修改').'交互';
		$this->mc || adminlog($cfg['record']);
		//$cfg['jumptype']  信息提示之后的处理方式：比如关闭当前窗口、跳转到别的页面
		$this->message($cfg['message'],empty($cfg['jumptype'])?axaction(6,M_REFERER):$cfg['jumptype']);
	}

	//ajax提交:结束时需要的事务
	function sv_ajend($exmsg,$expars=array()){
		$fmdata = $this->fulldata;
		$fmdata['cid'] = $this->cid;
		$reinfo = array('error'=>'', 'message'=>'提交成功！', 'result'=>'succeed', 'cu_data'=>$fmdata);
		$exmsg && $reinfo['exmsg'] = $exmsg; //$this->message('Error-T1(message错误信息)');
        if(!empty($expars['aj_minfo'])){ //同时返回会员资料
			$user = new cls_userinfo;
			$user->activeuser($fmdata['mid']); //,$detail
			$reinfo['aj_minfo'] = $user->info;
			if(cls_env::mconfig('ftp_enabled')){
				$ufields = cls_cache::Read('mfields',$user->info['mchid']);
				foreach($ufields as $fk => $fv){
					if($fv['datatype'] == 'image' && isset($reinfo['aj_minfo'][$fk])){
						$reinfo['aj_minfo'][$fk] = cls_url::tag2atm($reinfo['aj_minfo'][$fk]);
					}
				}
			}
        }
        if(!empty($expars['aj_ainfo']) && isset($fmdata['aid'])){ //同时返回文档资料
			$arc = new cls_arcedit;
			$arc->set_aid($fmdata['aid'],array('au'=>0)); //,'ch'=>$detail
			cls_ArcMain::Parse($arc->archive);
            $reinfo['aj_ainfo'] = $arc->archive;
        }
		return $reinfo; //$this->rejson($fmdata); //返回josn
	}

	//单个交互字段
	//cfg带入传入的配置，以传入的配置优先
	function type_field($key,$mode = 'init'){
		global $mctypes;
		$cfg = &$this->cfgs[$key];
		//if(empty($this->fields[$key]) || $this->IsSysField($key)) return $this->del_item($key);

		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				if($this->fhidden && in_array($key,$this->fhidden)){ //要做隐藏处理的字段，特殊处理
					trbasic("<font color='blue'>{$this->fields[$key]['cname']}</font>",'',cls_string::SubReplace($this->predata[$key]),'');
				}elseif(in_array($key,$this->fnoedit)){ //不允许编辑字段
					$a_field = new cls_field;
					$cfg = array_merge($this->fields[$key],$cfg);
					$a_field->init($cfg,isset($this->predata[$key]) ? $this->predata[$key] : '');
					$a_field->isadd = $this->isadd;
					$varr = $a_field->varr('noedit_'.$this->fmpre,'');
					$arr1 = array('<input ','<option ','<textarea ');
					$arr2 = array('<input disabled ','<option disabled ','<textarea disabled ');
					$varr = str_replace($arr1,$arr2,$varr);
					trspecial("<font color='blue'>{$varr['trname']}</font>",$varr);
					unset($a_field);
					//echo "<script>try{\$id('noedit_{$this->fmpre}[$key]').disabled=true;}catch(ex){}<-/script->";
				}else{
					$a_field = new cls_field;
					$cfg = array_merge($this->fields[$key],$cfg);
					$a_field->init($cfg,isset($this->predata[$key]) ? $this->predata[$key] : '');
					$a_field->isadd = $this->isadd;
					$a_field->trfield($this->fmpre);
					unset($a_field);
				}
			break;
			case 'sv'://保存处理
				global $sptype,$spsize;
				if(isset($this->fmdata[$key]) && $field = @$this->fields[$key]){
					$c_upload = cls_upload::OneInstance();
					$cfg && $field = array_merge($field,$cfg);
					if($field['datatype'] == 'htmltext' && $sptype == 'auto'){
						$spsize = empty($spsize) ? 5 : max(0,intval($spsize));
						$this->fmdata[$key] = SpBody($this->fmdata[$key],$spsize * 1024,'[##]');
					}
					
					$a_field = new cls_field; 
					$a_field->init($field,isset($this->predata[$key]) ? $this->predata[$key] : '');
					$this->fmdata[$key] = $a_field->deal($this->fmpre,'');
					if($a_field->error){//捕捉出错信息
						$this->sv_rollback();
						return $this->message($a_field->error,M_REFERER);
					}
					unset($a_field); 
					
					$this->enddata[$key] = $this->fmdata[$key]; 
					if($arr = multi_val_arr($this->fmdata[$key],$this->fields[$key])){
							foreach($arr as $x => $y){
								$this->enddata[$key.'_'.$x] = $y;
							}
					}
					
				} 
			break;
		}
	}

}
