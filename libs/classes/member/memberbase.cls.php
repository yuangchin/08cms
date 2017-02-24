<?php
/*
** 单个会员添加或编辑的操作类，考虑到添加与详情的差异性比较大，添加与详情流程脚本进行分离
** sv方法中设置return_error表示出错时返回error，而不是用message跳出
** mname,password,email将在字段中排除出来，做特别处理
*/
!defined('M_COM') && exit('No Permisson');
class cls_memberbase{
	protected static $mc = 0;//会员中心
	public $isadd = 0;//添加模式
	public $mid = 0;//会员id
	public $mchid = 0;//会员模型id
	public $noTrustee = 0;//为1时，不允许会员中心代管
	public $fmpre = 'fmdata';//form中的数组名称//允许自行设置
	public $predata = array();//预定资料数组
	public $mchannel = array();//会员模型
	public $fields = array();//模型字段
	public $cfgs = array();//设置项配置
	public $items_did = array();//暂存已处理过的项目
	public $auser = NULL;//会员对象
	public $fmdata = array();//表单提交后的数据数组
	
    function __construct($cfg = array()){
		self::$mc = defined('M_ADMIN') ? 0 : 1;
		$this->mid = empty($cfg['mid']) ? 0 : max(0,intval($cfg['mid']));
		if(self::$mc){//会员中心只用于修改自已的资料
			$this->isadd = 0;
		}else{
			$this->isadd = $this->mid ? 0 : 1;//$isadd通过是否指定mid来识别。0为编辑，1为添加
		}
		$this->mchid = empty($cfg['mchid']) ? 0 : max(0,intval($cfg['mchid']));
		if(!empty($cfg['fmpre'])) $this->fmpre = $cfg['fmpre'];
		if(!empty($cfg['noTrustee'])) $this->noTrustee = 1;
    }
	
	function setvar($key,$var){
		$this->$key = $var;	
	}
	
	public function message($str = '',$url = '')
    {
		cls_message::show($str, $url);
	}
	
	//清除设置项目
	protected function del_item($key){
		unset($this->cfgs[$key]);
		return false;
	}
	
	//是否一个已存在的项目
	protected function is_item($key = ''){
		return isset($this->cfgs[$key]) ? true : false;
	}
	
	protected function IsSysField($ename){
		return in_array($ename,array('mname','password','email')) ? true : false;
	}
	
	//添加设置项目，进行项目初始化
	function additem($key,$cfg = array()){
		$this->cfgs[$key] = $cfg;
		$re = $this->one_item($key,'init');
		if($re == 'undefined') $this->del_item($key);
	}
	
	protected function call_method($func,$args = array()){
		if(method_exists($this,$func)){
			return call_user_func_array(array(&$this,$func),$args);
		}else return 'undefined';
	}
	
	//方法优先次序：user_$key(定制) -> type_$type(类型) -> 通用方法
	//操作项方法需要处理：init：初始化 fm：显示 sv：数据处理
	protected function one_item($key,$mode = 'init'){
		if(!isset($this->cfgs[$key])) return false;
		$re = $this->call_method("user_$key",array($key,$mode));//定制方法
		if(!isset($this->cfgs[$key]['_type'])) $this->cfgs[$key]['_type'] = 'common';
		if($re == 'undefined'){
			switch($this->cfgs[$key]['_type']){
				case 'field':
					$re = $this->type_field($key,$mode);
				break;
				case 'ugid':
					$re = $this->type_ugid($key,$mode);
				break;
			}
		}
		if(in_array($mode,array('fm','sv',))) $this->items_did[] = $key;//记录已处理的项目
		return $re;
	}

	// $cfg['title'] = '专家详情' // 自定义浮动窗等的title
	// $cfg['isself'] = 1 后台修改自己的pw
	function TopHead($cfg=array()){
		$curuser = cls_UserMain::CurUser();
		if(self::$mc){
			if(!defined('M_COM')) exit('No Permission');
		}else{
			if(!defined('M_COM') || !defined('M_ADMIN')) exit('No Permission');
			aheader();
			if(empty($cfg['isself'])){
				if($re = $curuser->NoBackFunc('member')) $this->message($re);
			}
		}
		$title = isset($cfg['title']) ? $cfg['title'] : "会员".($this->isadd ? '添加' : '详情')."";
		echo "<title>$title</title>";
		
		//读取及初始化资料，如模型、字段、及会员对象
		$this->ReadInfo();//读取会员资料
		$this->ReadConfig();//读取配置
	}
	
	//会员详情时，读取指定会员mid的原有资料
	function ReadInfo(){
		if(!$this->isadd){//仅详情时有效
			if(self::$mc){
				$this->auser = cls_UserMain::CurUser();
				$this->mid = $this->auser->info['mid'];
			}else{
				if(!$this->mid) $this->message('请指定会员。');
				$curuser = cls_UserMain::CurUser();
				if($this->mid == $curuser->info['mid']){
					$this->auser = cls_UserMain::CurUser();
					$this->auser->detailed || $this->auser->detail_data(); //当前会员可能没有detail资料
				}else{
					$this->auser = new cls_userinfo;
					if(!($this->auser->activeuser($this->mid,2))) $this->message('请指定会员。');
				}
			}
			$this->mchid = $this->auser->info['mchid'];
			$this->predata = $this->auser->info;
	#		if(!$this->admin_pm($this->predata['caid'])) $this->message('您没有指定栏目的管理权限 !');???????????????
		}
	}
	
	//读取会员模型及字段配置 
	function ReadConfig(){
		if(!($this->mchannel = cls_cache::Read('mchannel',$this->mchid))) $this->message('请指定会员类型。');
		$this->fields = cls_cache::Read('mfields',$this->mchid);
		foreach($this->fields as $k => $v){//排除系统内置字段
			if($this->IsSysField($k)) unset($this->fields[$k]);
		}
		if(self::$mc){//在会员中心排除会员认证字段
			$mctypes = cls_cache::Read('mctypes');
			foreach($mctypes as $k => $v){
				if(!empty($v['available']) && strstr(",$v[mchids],",",".$this->mchid.",")){ //允许的会员模型
					unset($this->fields[$v['field']]);
				}
			}
		}
	}
	
	//需要处理会员中心/管理后台、添加/编辑的差别
	function TopAllow(){
		$curuser = cls_UserMain::CurUser();
		if(self::$mc){//会员中心只能编辑自已的资料，不能进行添加操作
			if($this->isadd) $this->message('被禁止的功能。');//会员中心不能增加会员 
			if($this->noTrustee && $curuser->getTrusteeshipInfo()) $this->message('您是代管用户，当前操作仅原用户本人有权限！');
		}else{
			if($re = $this->NoBackPm($this->mchid)) $this->message($re);
			if($this->isadd){
			
			}else{
				if($this->predata['isfounder'] && $curuser->info['mid'] != $this->predata['mid']) $this->message('创始人资料只能由本人管理。');
			}
		}
	}
	
	//管理角色的会员模型管理权限，仅在管理后台中使用
	function NoBackPm($mchid = 0){
		$curuser = cls_UserMain::CurUser();
		if(self::$mc) return '';
		if(!$mchid) return '请指定会员类型';
		return $curuser->NoBackPmByTypeid($mchid,'mchid');
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
		
		$title || $title = (empty($this->predata['mname']) ? $this->mchannel['cname'] : $this->predata['mname']).'&nbsp; -&nbsp; '.($this->isadd ? '添加会员' : '会员详情');
		if($url){
			if($this->isadd){
				if(!in_str('mchid=',$url)) $url .= "&mchid={$this->mchid}"; 
			}else{
				if(!in_str('mid=',$url)) $url .= "&mid={$this->mid}"; 
			}
			tabheader($title,'memberdetial',$url,2,1,1);
		}else{
			tabheader($title);
		}
	}
	
	// 展示指定项，$incs为空表示为所有剩余项
	// $noinc=array()，在$incs基础上排除$noinc中的字段，为空则不排除。
	function fm_items($incs = '',$noinc = array()){
		if(!empty($incs)) $incs = array_filter(explode(',',$incs));
		if(empty($incs)) $incs = array_keys($this->cfgs);//展示剩余项
		foreach($incs as $key){
			if(!empty($noinc) && in_array($key,$noinc)) continue;
			if(!in_array($key,$this->items_did)){
				$this->one_item($key,'fm');
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
	
	//管理后台：$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	function fm_guide_bm($str = '',$type = 0){
		if(self::$mc){
			m_guide($str,$type ? $type : '');
		}else{
			if(!$str){
				$str = $this->isadd ? 'memberadd' : 'memberdetail';
				if(is_file(M_ROOT."dynamic/aguides/{$str}_{$this->mchid}.php")) $str .= "_{$this->mchid}";
				$type = 0;
			}
			a_guide($str,$type);
		}
	}
	
	//通用的提交后处理，可以通过定制方法进行扩展
	//根据设置项及初始化配置进行综合处理
	//cfg[message]:提示信息
	function sv_all_common($cfg = array()){
		
		//设置$this->fmdata中的值
		$this->sv_set_fmdata();
		
		//这三个需要特别处理，跟新增及ucenter有关
		$this->sv_items('mname,password,email');
		
		//这个需要在mname,password,email之后执行
		$this->sv_add_init();
		
		//处理UC/WINDID同步事务
		$this->sv_ucenter();
		
		//进行余下的所有项目处理，此时未执行数据库操作
		$this->sv_items();
		
		//执行自动操作及更新以上变更
		$this->sv_update();
		
		//自动推送
		if($this->isadd){ 
			$this->auser->autopush();
		}
		
		//上传处理
		$this->sv_upload();
		
		//结束时需要的事务，包括操作记录、成功提示等
		$this->sv_finish($cfg);
		
	}
	
	//修改自已密码的提交处理
	function sv_all_password_self(){
		
		//设置$this->fmdata中的值
		$this->sv_set_fmdata();
		
		//进行余下的所有项目处理，此时未执行数据库操作
		$this->sv_items();
		
		//执行自动操作及更新以上变更
		$this->sv_update();
		
		//结束时需要的事务，包括操作记录、成功提示等
		$this->sv_finish(array('message' => '密码修改成功','record' => '修改自已密码'));
		
	}
	
	//空间静态的提交后处理
	function sv_all_static(){
		
		//设置$this->fmdata中的值
		$this->sv_set_fmdata();
		
		//进行余下的所有项目处理，此时未执行数据库操作
		$this->sv_items();
		
		//执行自动操作及更新以上变更
		$this->sv_update();
		
		//生成静态目录缓存，只能在数据库更新之后执行
		cls_CacheFile::Update('mspacepaths');
		
		//空间静态更新，不要在sv_items执行，因为需要在所有项目执行并更新数据库之后才能生成空间静态
		$message = $this->sv_static_update();
		
		//结束时需要的事务，包括操作记录、成功提示等
		$this->sv_finish(array('message' => $message ? $message : '静态空间设置成功','record' => '设置静态空间'));
	}
	
	//空间静态更新
	function sv_static_update(){
		global $timestamp;
		$message = '';
		if(!empty($this->fmdata['update']) && !empty($this->auser->info['mspacepath'])){
			// 生成静态时cls_Mspacebase::IndexUrl()里面的判断需要先更新msrefreshdate：
			$this->auser->updatefield('msrefreshdate',$timestamp);
			$this->auser->updatedb();
			$message = cls_Mspace::ToStatic($this->auser->info['mid']);
		}
		return $message;
	}
	
	//处理指定项，否则展示所有剩余项
	function sv_items($incs = ''){
		if(!empty($incs)) $incs = array_filter(explode(',',$incs));
		if(empty($incs)) $incs = array_keys($this->cfgs);//展示剩余项
		foreach($incs as $key){
			if(!in_array($key,$this->items_did)){
				$this->one_item($key,'sv');
			}
		}
	}
	
	function sv_set_fmdata(){
		$this->fmdata = &$GLOBALS[$this->fmpre];//因为字段处理方法未进一步优化，这里需要是引用
	}	
	
	//添加会员之后，更新内容时出现异常的话，需要删除新增加的会员记录
	//mname,password,email之外项目的意外错误，都需要使用此方法
	function sv_rollback(){
		if($this->mid && $this->isadd){
			$c_upload = cls_upload::OneInstance();
			$this->auser->delete();
			$c_upload->closure(1);
		}
	}
	
	function sv_fail($return_error = 0){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1);
		return $this->message('会员添加失败',M_REFERER);
	}
	
	//增加会员的初始化操作
	function sv_add_init(){
		if(!$this->isadd) return;
		$na = array('mname' => '会员帐号','password' => '会员密码','email' => 'E-mail',);
		foreach($na as $key => $val){
			$$key = '';
			if($this->is_item($key) && !empty($this->fmdata[$key])) $$key = $this->fmdata[$key];
			if(!$$key) $this->message("增加会员需要输入 $val",M_REFERER);
		}
		if(empty($this->auser))  $this->auser = new cls_userinfo;
		if($this->mid = $this->auser->useradd($mname,_08_Encryption::password($password),$email,$this->mchid)){
			$this->auser->check(1);
		}else{
			$this->sv_fail();
		}
	}
		
	//处理uc/WINDID同步的相关事务
	function sv_ucenter()
    {
	    global $onlineip;
		$na = array('mname' => '会员帐号','password' => '会员密码','email' => 'E-mail',);
		if($this->isadd){
			foreach($na as $key => $val){
				$$key = '';
				if($this->is_item($key) && !empty($this->fmdata[$key])) $$key = $this->fmdata[$key];
				if(!$$key) $this->message("同步注册需要输入 $val",M_REFERER);
			}
            # UCenter
            if(cls_ucenter::init())
            {
    			$uc_uid = cls_ucenter::register($mname,$password,$email,FALSE); //后台添加不需要同步登录
    			empty($uc_uid) || $this->auser->updatefield(cls_ucenter::UC_UID, $uc_uid);           
            }
            
        	# 同步注册用户到WINDID，目前该方法只用于后台添加用户
        	$pw_uid = cls_WindID_Send::getInstance()->synRegister($mname, $password, $email, $onlineip, false);
        	empty($pw_uid) || $this->auser->updatefield(cls_Windid_Message::PW_UID, $pw_uid);
		}else{
			unset($na['mname']);//编辑时不需要mname
			foreach($na as $key => $val){
				$$key = '';
				if($this->is_item($key) && @$this->fmdata[$key] != @$this->predata[$key]) $$key = $this->fmdata[$key];
			}
			if(!$password && !$email) return;//未修改密码或email
            
            # UCenter
            if(cls_ucenter::init())
            {
                if($re = cls_ucenter::edit($this->auser->info['mname'],$password,$email)) $this->message($re,M_REFERER);
            }
            # 同步修改资料
            $updata_arr = array('email' => $email);
            if($password) $updata_arr['password'] = $password;
            cls_WindID_Send::getInstance()->editUser($this->auser->info['mid'], '', $updata_arr);
		}
	}
	
	//执行自动操作及更新以上变更
	function sv_update(){
		$this->auser->updatedb();
	}
	
	//上传处理
	function sv_upload(){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1,$this->mid,'members');
		$c_upload->saveuptotal(1);
	}
		
	//结束时需要的事务， 如：操作记录及成功提示
	function sv_finish($cfg = array()){
		if(empty($cfg['message'])) $cfg['message'] = '会员'.($this->isadd ? '添加' : '修改').'完成';
		if(empty($cfg['record'])) $cfg['record'] = ($this->isadd ? '添加' : '修改').'会员';
		self::$mc || adminlog($cfg['record']);
        //$cfg['jumptype']  信息提示之后的处理方式：比如关闭当前窗口、跳转到别的页面
		$this->message($cfg['message'],empty($cfg['jumptype'])?axaction(6,M_REFERER):$cfg['jumptype']);
	}
	
	//只用于添加新会员
	protected function user_mname($key,$mode = 'init'){
		global $cms_abs;
		if(!$this->isadd) return $this->del_item($key);//修改时此项无效
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示，如何校验重名?????????
				$guide = '请输入3-15位字符';
				trbasic(self::NotNullFlag().'会员帐号',"{$this->fmpre}[mname]",isset($this->predata['mname']) ? $this->predata['mname'] : '','text',array('validate'=>makesubmitstr("{$this->fmpre}[mname]",1,0,3,15),'guide' => $guide,));
				$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC('ajax=check_member_info&filed=mname&val=%1');
				echo _08_HTML::AjaxCheckInput($this->fmpre.'[mname]', $ajaxURL);
			break;
			case 'sv'://保存处理
				$re = cls_userinfo::CheckSysField(@$this->fmdata[$key],$key,$this->isadd ? 'add' : 'edit');
				if($re['error']){
					$this->message($re['error'], M_REFERER);
				}else $this->fmdata[$key] = $re['value'];
			break;
		}
	}
	
	protected function user_password($key,$mode = 'init'){
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$guide = $this->isadd ? '请输入1-15位密码' : '修改密码请输入1-15位密码，否则请留空，表示不修改密码';
				$title = $this->isadd ? (self::NotNullFlag().'会员密码') : '修改密码';
				trbasic($title,"{$this->fmpre}[$key]",'',$key,array('validate'=>' autocomplete="off"'.makesubmitstr("{$this->fmpre}[$key]",$this->isadd?1:0,0,1,15),'guide' => $guide,));
			break;
			case 'sv'://保存处理，UC的同步进行汇总处理，不在这里做处理
				$re = cls_userinfo::CheckSysField(@$this->fmdata[$key],$key,$this->isadd ? 'add' : 'edit');
				if($re['error']){
					$this->message($re['error'], M_REFERER);
				}else $this->fmdata[$key] = $re['value'];
				
				if(!$this->isadd){//增加会员另外处理
					if(empty($this->fmdata[$key])) return;//不修改密码
					if(!$this->auser->updatefield($key,_08_Encryption::password($this->fmdata[$key]))) $this->del_item($key);
				}//通过统一的增加会员方法处理后续事务
			break;
		}
	}
	
	
	//带旧密码验证及二次输入密码
	//注：只用于修改自已的密码
	protected function user_password_self($key,$mode = 'init'){
		$curuser = cls_UserMain::CurUser();
		if($this->isadd) return $this->del_item($key);//只用于修改密码
		if($curuser->info['mid'] != $this->auser->info['mid']) return $this->del_item($key);//只用于修改自已的密码
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$guide = '请输入1-15位密码';
				trbasic(self::NotNullFlag().'原始密码',"{$this->fmpre}[opassword]",'','password', array('validate' => ' autocomplete="off" '.makesubmitstr("{$this->fmpre}[opassword]",1,0,0,15),'guide' => $guide,));
				trbasic(self::NotNullFlag().'新密码',"{$this->fmpre}[npassword]",'','password', array('validate' => ' autocomplete="off" '.makesubmitstr("{$this->fmpre}[npassword]",1,0,0,15),'guide' => $guide,));
				trbasic(self::NotNullFlag().'重复密码',"{$this->fmpre}[npassword2]",'','password', array('validate' => ' autocomplete="off" '.makesubmitstr("{$this->fmpre}[npassword2]",1,0,0,15),'guide' => $guide,));
			break;
			case 'sv':
				foreach(array('opassword','npassword','npassword2',) as $var){
					$re = cls_userinfo::CheckSysField(@$this->fmdata[$var],'password','edit');
					if($re['error']){
						$this->message($re['error'], M_REFERER);
					}else $this->fmdata[$var] = $re['value'];
				}
				if(_08_Encryption::password($this->fmdata['opassword']) != $this->auser->info['password']) $this->message('原始密码错误',M_REFERER);
				if($this->fmdata['npassword'] != $this->fmdata['npassword2']) $this->message('两次输入密码不一致',M_REFERER);
				if($this->fmdata['opassword'] == $this->fmdata['npassword']) $this->message('新密码与旧密码相同',M_REFERER);
				//UC处理及会员设置为登录状态
				if($this->auser->updatefield('password',_08_Encryption::password($this->fmdata['npassword'])))
                {
					if($re = cls_ucenter::edit($this->auser->info['mname'],$this->fmdata['npassword'])) $this->message($re,M_REFERER);
                    # 同步修改WINDID用户密码
                    cls_WindID_Send::getInstance()->editUser(
                        $curuser->info['mid'], 
                        $this->fmdata['opassword'], 
                        array('password' => $this->fmdata['npassword'])
                    );
					cls_userinfo::LoginFlag($this->auser->info['mid'],_08_Encryption::password($this->fmdata['npassword']));
				}
			break;
		}
	}
	
	protected function user_email($key,$mode = 'init'){
        global $mid,$cms_abs;
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$guide = '请输入正确的email格式';
				trbasic(self::NotNullFlag().'E-mail',"{$this->fmpre}[$key]",isset($this->predata[$key]) ? $this->predata[$key] : '','text',array('validate'=>makesubmitstr("{$this->fmpre}[$key]",1,'email',0,50),'guide' => $guide,));
				if($this->isadd){
					$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC('ajax=check_member_info&filed=email&val=%1');
					echo _08_HTML::AjaxCheckInput($this->fmpre.'[email]', $ajaxURL);
				}
			break;
			case 'sv'://保存处理
				$re = cls_userinfo::CheckSysField(@$this->fmdata[$key],$key,$this->isadd ? 'add' : 'edit', $mid);
				if($re['error']){
					$this->message($re['error'], M_REFERER);
				}else $this->fmdata[$key] = $re['value'];
				
				if(!$this->isadd){//增加会员另外处理
					if(!$this->auser->updatefield($key,$this->fmdata[$key])) $this->del_item($key);
				}//通过统一的增加会员方法处理后续事务
			break;
		}
	}
	
	//会员组设置，手动项可设置，否则只能查看
	//onlyset：只显示可设置的项
	//'ismust'=>1, //是否必选
	//'notime'=>1, //不显示时间
	//'afirst'=>array(''=>'-请选择-'), //第一个选项配置, 默认为array('0' => '组外会员')
	protected function type_ugid($key,$mode = 'init'){
		$grouptypes = cls_cache::Read('grouptypes');
		$gtid = max(0,intval(str_replace('ugid','',$key)));
		if(!$gtid || ($gtid== 2) || empty($grouptypes[$gtid])) return $this->del_item($key);//排除及管理组系
		if(!($ugidsarr = ugidsarr($gtid,$this->mchid,1))) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		
		$_can_set = false;//是否可以设置，还是仅显示
		if(self::$mc){
			if(!$grouptypes[$gtid]['mode']) $_can_set = true;
		}else{
			if($grouptypes[$gtid]['mode'] < 2) $_can_set = true;
		}
		if(!empty($cfg['onlyset']) && !$_can_set) return $this->del_item($key);//只允许显示可设置项
		
		switch($mode){
			case 'init'://初始化
				$grouptypes[$gtid]['ismust'] = empty($cfg['ismust']) ? '' : "<span style='color:#F00'> * </span>";
			break;
			case 'fm'://表单显示
				if($_can_set){
					$afirst = empty($cfg['afirst']) ? array('0' => '组外会员') : $cfg['afirst'];
					$ismust = empty($cfg['ismust']) ? '' : " rule='must' ";
					$str = makeselect("{$this->fmpre}[grouptype$gtid]",makeoption($afirst + $ugidsarr,!empty($this->predata["grouptype$gtid"]) ? $this->predata["grouptype$gtid"] : 0),$ismust);
					empty($cfg['notime']) && $str .= " 结束日期：".OneCalendar("{$this->fmpre}[grouptype{$gtid}date]",!empty($this->predata["grouptype{$gtid}date"]) ? date('Y-m-d',$this->predata["grouptype{$gtid}date"]) : '');
				}else{
					$str = !empty($this->predata["grouptype$gtid"]) ? $ugidsarr[$this->predata["grouptype$gtid"]] : '组外会员';
					$str = "<b>$str</b>";
					$str .= " 结束日期：".(!empty($this->predata["grouptype{$gtid}date"]) ? date('Y-m-d',$this->predata["grouptype{$gtid}date"]) : '无限期');
				}
				trbasic(@$grouptypes[$gtid]['ismust'].$grouptypes[$gtid]['cname'],'',$str,'');
			break;
			case 'sv'://保存处理
				if($_can_set){
					$this->fmdata["grouptype$gtid"] = empty($this->fmdata["grouptype$gtid"]) ? 0 : trim($this->fmdata["grouptype$gtid"]);
					$this->fmdata["grouptype{$gtid}date"] = empty($this->fmdata["grouptype{$gtid}date"]) || !cls_string::isDate($this->fmdata["grouptype{$gtid}date"]) ? 0 : strtotime($this->fmdata["grouptype{$gtid}date"]);
					$this->auser->handgroup($gtid,$this->fmdata["grouptype$gtid"],$this->fmdata["grouptype{$gtid}date"]);
				}else return $this->del_item($key);//不是手动设置组,不做修改
			break;
		}
	}
	
	//只用于会员编辑
	protected function user_mtcid($key,$mode = 'init'){
		if($this->isadd) return $this->del_item($key);
		if(!($mtcidsarr = $this->auser->mtcidsarr())) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				trbasic('会员空间模板方案',"{$this->fmpre}[mtcid]",makeoption($mtcidsarr,$this->auser->info['mtcid']),'select');
			break;
			case 'sv'://保存处理
				$this->fmdata[$key] = empty($this->fmdata[$key]) ? 0 : trim($this->fmdata[$key]);
				$mtckeys = array_keys($mtcidsarr);
				if(in_array($this->fmdata[$key],$mtckeys)){ 
					$this->auser->updatefield('mtcid',$this->fmdata[$key]);
				}else return $this->del_item($key);//如果不在选择范围，则不做修改
			break;
		}
	}
	
	//400电话设置
	//注：因为完全可以不使用网站官方提供的总机，所以不管webcall_enable，都可以使用400电话
	protected function user_webcall($key,$mode = 'init'){
		global $webcallpmid;
		if($this->isadd || !self::$mc) return $this->del_item($key);
		if(empty($webcallpmid) || $this->auser->noPm($webcallpmid)) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		$keyurl = $key.'url';
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				trbasic('400电话号码',"{$this->fmpre}[$key]",$this->auser->info[$key],'text',array('w' => 30,'validate' => makesubmitstr("{$this->fmpre}[$key]",0,0,6,20),));
				trbasic('400免费拨打链接',"{$this->fmpre}[$keyurl]",$this->auser->info[$keyurl],'textarea',array('validate' => makesubmitstr("{$this->fmpre}[$keyurl]",0,0,10,255),));
			break;
			case 'sv'://保存处理
				$this->fmdata[$key] = empty($this->fmdata[$key]) ? '' : trim($this->fmdata[$key]);
				$this->fmdata[$keyurl] = empty($this->fmdata[$keyurl]) ? '' : trim($this->fmdata[$keyurl]);
				$this->auser->updatefield($key,$this->fmdata[$key]);
				$this->auser->updatefield($keyurl,$this->fmdata[$keyurl]);
			break;
		}
	}
	
	//单个会员字段
	//cfg带入传入的配置，以传入的配置优先
	protected function type_field($key,$mode = 'init'){
		$cfg = &$this->cfgs[$key];
		if(empty($this->fields[$key]) || $this->IsSysField($key)) return $this->del_item($key);
		
		if(self::$mc){//会员中心不能对认证字段进行设置
			$mctypes = cls_cache::Read('mctypes');
			foreach($mctypes as $k => $v){
				if(!empty($v['available']) && $v['field']==$key && strstr(",$v[mchids],",",".$this->mchid.",")) return $this->del_item($key);
			}
		}
		
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$a_field = new cls_field;
				$cfg = array_merge($this->fields[$key],$cfg);
				$a_field->init($cfg,isset($this->predata[$key]) ? $this->predata[$key] : '');
				$a_field->isadd = $this->isadd;
				$a_field->trfield($this->fmpre);
				unset($a_field);
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
					
					$this->auser->updatefield($key,$this->fmdata[$key],$field['tbl']);
					if($arr = multi_val_arr($this->fmdata[$key],$field)) foreach($arr as $x => $y) $this->auser->updatefield($key.'_'.$x,$y,$field['tbl']);
				}
			break;
		}
	}
	
	//会员空间目录
	protected function user_mspacepath($key,$mode = 'init'){
		global $mspacepmid;
		$cfg = &$this->cfgs[$key];
		if(!$mspacepmid || $this->auser->noPm($mspacepmid)) return $this->del_item($key);
		
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$na = array(
				'w' => '15',
				'guide' => '由小写字母数字及_组成，以字母开头。输入空值表示使用动态空间。',
				'validate' => makesubmitstr("{$this->fmpre}[mspacepath]",0,0,2,15),
				'addstr' => "<input type=\"button\" value=\"检查重名\" onclick=\"check_repeat('{$this->fmpre}[mspacepath]','mdirname');\">",
				);
				trbasic('设置静态目录',"{$this->fmpre}[mspacepath]",empty($this->auser->info[$key]) ? '' : $this->auser->info[$key],'text',$na);
				trbasic('同时更新静态','',OneCheckBox("{$this->fmpre}[update]",'现在更新',1),'');
			break;
			case 'sv'://保存处理
				//更新静态操作不要在这里执行，因为需要在所有项目执行并更新数据库之后才能生成空间静态
				global $db,$tblprefix,$mspacedir,$timestamp;
				if(isset($this->fmdata[$key])){
					$this->fmdata[$key] = strtolower(trim(strip_tags($this->fmdata[$key])));
					if(!$this->fmdata[$key] || preg_match("/[^a-z_0-9]+/",$this->fmdata[$key])) $this->fmdata[$key] = '';
					$o_mspacepath = $this->auser->info[$key];
					if($this->auser->info[$key] != $this->fmdata[$key]){
						if($this->fmdata[$key]){
							if($db->result_one("SELECT mspacepath FROM {$tblprefix}members WHERE mid<>'{$this->auser->info['mid']}' AND mspacepath='{$this->fmdata[$key]}'")){
								$this->message('静态空间目录已被占用',M_REFERER);
							}
							if($this->auser->info[$key] && is_dir(M_ROOT.$mspacedir.'/'.$this->auser->info[$key])){
								if(!rename(M_ROOT.$mspacedir.'/'.$this->auser->info[$key],M_ROOT.$mspacedir.'/'.$this->fmdata[$key])) $this->fmdata[$key] = '';
							}elseif(!mmkdir(M_ROOT.$mspacedir.'/'.$this->fmdata[$key],0)) $this->message('静态空间目录无法生成',M_REFERER);
						}
						$this->auser->updatefield($key,$this->fmdata[$key]);
					}
					if($this->auser->info[$key]){
						$ifile = M_ROOT.$mspacedir.'/'.$this->auser->info[$key].'/index.php';
						if(!is_file($ifile)) str2file('<?php $mid = '.$this->auser->info['mid'].'; include dirname(dirname(__FILE__)).\'/index.php\'; ?>',$ifile);
					}elseif($o_mspacepath){
						if(!_08_FileSystemPath::CheckPathName($o_mspacepath)) clear_dir(M_ROOT.$mspacedir.'/'.$o_mspacepath,true);
						$this->auser->updatefield('msrefreshdate',0);
					}
				}
			break;
		}
	}
	
	//会员空间静态状态
	protected function user_static_state($key,$mode = 'init'){
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				trbasic('空间首页','',"<a href=\"{$this->auser->info['mspacehome']}\" target=\"_blank\">>>预览</a>",'');
				trbasic('空间静态更新','',empty($this->auser->info['msrefreshdate']) ? '尚未生成' : date('Y-m-d H:i',$this->auser->info['msrefreshdate']).' 更新','');
			break;
			case 'sv'://保存处理
			break;
		}
	}
	
	//设置托管者名单
	protected function user_trusteeship($key,$mode = 'init'){
		global $cms_abs,$g_apid;
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$mnames = array();
				if($this->auser->info['trusteeship'] && $mids = array_filter(explode(',',$this->auser->info['trusteeship']))){
					foreach($mids as $mid){
						if($mname = cls_userinfo::getNameForId($mid)) $mnames[] = $mname;
					}
				}
                $url = $cms_abs . 'adminm.php?from_mid=' . $this->auser->info['mid'];
                $call_function = _08_HTML::createCopyCode('call_function', $url, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    			trbasic("设置托管名单","{$this->fmpre}[$key]",$mnames ? implode(',',$mnames) : '','text', array('guide'=>'<br />( 格式：会员1,会员2 )。多个代管者以逗号分开；输入本站会员的会员帐号，留空为解除所有托管会员。','w' => 70,));
				trbasic("代管地址 {$call_function}",'url', $url,'text',array('guide'=>'<br />托管人可以经以上代管地址进入你的会台中心进行信息管理','w' => 70,'validate' => 'readonly'));
			break;
			case 'sv'://保存处理
				$this->fmdata[$key] = empty($this->fmdata[$key]) ? '' : trim($this->fmdata[$key]);
				$this->auser->setTrusteeshipUser($this->fmdata[$key]);
			break;
		}
	}
	
	//会员空间静态状态
	protected static function NotNullFlag(){
		return '<font color="red"> * </font>';
	}
    
	//绑定QQ与新浪微博
	protected function user_openid_sinauid($key,$mode = 'init'){
		$curuser = cls_UserMain::CurUser();
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
                cls_phpToJavascript::showOtherBind();
    			trbasic("绑定QQ", '', (empty($curuser->info['openid']) ? '<a target="_self" onclick="OtherWebSiteLogin(\'qq\', 600, 470);" href="javascript:void(0)" title="QQ帐号登录" class="qqbnt l" style="color:green;">开始登录绑定</a>' : '<a target="_self" onclick="OtherWebSiteLogin(\'qq_reauth\', 600, 470);" href="javascript:void(0)" title="QQ帐号登录" class="qqbnt l" style="color:green;">重新绑定</a>'), 'string');
    			trbasic("绑定新浪微博", '', (empty($curuser->info['sina_uid']) ? '<a onclick="OtherWebSiteLogin(\'sina\', 600, 400);" href="javascript:void(0)" title="新浪微博帐号登录" class="wbbnt" target="_self" style="color:green;">开始登录绑定</a>' : '<a onclick="OtherWebSiteLogin(\'sina_reauth\', 600, 400);" href="javascript:void(0)" title="新浪微博帐号登录" class="wbbnt" target="_self" style="color:green;">重新绑定</a>'), 'string');
			break;
			case 'sv'://保存处理
			break;
		}
	}
}
