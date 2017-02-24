<?php
/* 

// 规范: 加分: 参数为acurrency, 添加时,不管是否审核,(如字段若与不同,再作修改)
//       减分: 参数为ccurrency, 删除(恶意)时,审核操作不处理
//       前缀: cu-交互表, a-archivesXXX, m-member, s=member_sub c-文档/会员模型表

** 交互操作基类,供addbase,editbase,listbase三个类继承
** 有两个(或以上)地方共用，或公共的代码，放到这里

*/
!defined('M_COM') && exit('No Permission');

class cls_cubasic{
	
	public $cuid = 0; //交互项目ID
	public $ptype = ''; //交互对象类型:a-文档, m-会员, e-其它
	public $pchid = 0; //交互对象模型ID,chid,mchid(多个模型,为使逻辑简单也传入进来)
	
	public $mc = ''; //操作者:1-会员中心,0-后台
	public $A = array();//初始化参数存放、如cuid，...
	public $act = ''; //操作类型:list, edit, add
	public $burl = ''; //基本url, edit,list中top_head()处理, etools:add中add_init()处理
	// 初始化配置的参数以&开始, $action,$entry,$extend_str,$cuid,$inajax,$js不用带入,自动出来
	//public $cid = 0;
	
	public $cucfgs = array(); //交互项目缓存
	public $fields = array(); //交互项目字段
	public $fhidden = array(); //使用cls_string::SubReplace($row[$k]),'');隐藏部分联系方式,用于电话号码,Email等字段, 如'fhidden' => is_hidden_connect() ? array('dianhua','email') : array(), 
	
	public $db = null;
	
    function __construct($_cfg){
		$this->init($_cfg);
	}
	
	# 初始化 (cuid)
	function init($_cfg){
		
		$this->cuid = $_cfg['cuid'];
		$this->ptype = @$_cfg['ptype']; //在一些投票等操作中可能只有一个cuid参数,没有ptype；
		!empty($_cfg['pchid']) && $this->pchid = $_cfg['pchid'];
		
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $_cfg;
		
		$this->cucfgs = cls_cache::Read('commu',$this->cuid);
		$this->fields = cls_cache::Read('cufields',$this->cuid); 
		if(isset($_cfg['fhidden'])){	
			$this->fhidden = $_cfg['fhidden'];
		}
		
		$this->db = _08_factory::getDBO();
	}
	
	// culist+cuedit:管理后台+会员中心:使用
	// cfg['chkData']: 检查数据是否存在
	// cfg['setCols']: 添加设置编辑字段
	function top_head($cfg=array()){
		global $action,$entry,$extend_str,$infloat;
		$curuser = cls_UserMain::CurUser();
		if($this->mc){
			!defined('M_COM') && exit('No Permission');
		}else{
			(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
			aheader();
			if($re = $curuser->NoBackFunc('commu')) $this->message($re);
		}
		// 处理url, cuedit,culist使用
		if($this->mc){ //?action=$action&cuid=$cuid&pid=$pid
			$burl = "?action=$action&cuid=$this->cuid";
		}else{ //?entry=$entry$extend_str&caid=$caid&cuid=$cuid&pid=$pid&reid=$reid
			$burl = "?entry=$entry$extend_str&cuid=$this->cuid";
		}
		empty($this->A['caid']) || $burl .= "&caid={$this->A['caid']}";
		$this->burl = $burl;
		$this->A['url'] = $this->burl.(empty($this->A['url']) ? '' : $this->A['url']);
		foreach(array('infloat') as $k){ //'aid','mid','pid'
			global $$k; $v = $$k;
			if($v && !strstr('$v=',$this->A['url'])) $this->A['url'] .= "&$k=$v";
		}//*/

		$this->cucfgs || $this->message('不存在的交互项目。');
		echo "<title>交互管理 - ".$this->cucfgs['cname']."</title>";
		empty($this->A['cid']) || $this->predata = $this->getRow($this->cid, array());
		
		// 处理cfg参数
		if(!empty($cfg['chkData'])){
			if(empty($this->predata)) $this->message('不存在数据！'); 
		}
		if(!empty($cfg['setCols'])){
			$this->additems();
		}
	}
	
	# 得到一笔交互数据
	function getRow($cid, $whrarr=array()){
		$cid = intval($cid);
		$this->db->select('*')->from(self::table());
		$this->db->where(empty($cid) ? '1=1' : array('cid'=>$cid));
		if($whrarr){
			foreach($whrarr as $k=>$v){
				$this->db->_and(array($k=>$v));	
			}
		}
		return $this->db->exec()->fetch();
	}
	
	# 得到交互对象(文档/会员)的信息(仅数据)
	function getPInfo($type='a',$pid,$detail=0){
		$pid = intval($pid);
		$pinfo = array();
		if($type=='a'){
			$arc = new cls_arcedit;
			$arc->set_aid($pid,array('au'=>0,'ch'=>$detail));
			$pinfo = $arc->archive;
			$pinfo && cls_ArcMain::Parse($pinfo);	
		}elseif($type=='m'){
			$user = new cls_userinfo;
			$user->activeuser($pid,$detail);
			$pinfo = $user->info;
		} 
		if(!empty($pinfo)) $pinfo['_pid'] = $pid; //统一保存pid
		return $pinfo;
	}
	
	# 得到交互对象(文档/会员)的一个连接(带a标签)
	// 交互etools提交, 后台编辑等
	// 参数见：cfg参数：cucolsbase.cls.php :: user_subject($mode = 0,$data = array())
	// $data：交互对象数据，如果为数字,则调用getPInfo()获取交互对象数据
	function getPLink($data, $cfg=array()){
		if(is_numeric($data)) $data = $this->getPInfo($this->ptype,$data); 
		$len = empty($cfg['len']) ? 40 : $cfg['len'];
		$dkey = empty($cfg['field']) ? ($this->ptype=='m' ? 'mname' : 'subject') : $cfg['field'];
		$dre = htmlspecialchars(cls_string::CutStr($data[$dkey],$len));
		if(empty($cfg['url']) && ($this->ptype=='a' || $this->ptype=='u')){ //文档   //ptype=='u'为用户自定义sql的类型
			$addno = empty($cfg['addno']) ? 0 : max(0,intval($cfg['addno']));
			if(!empty($cfg['mc'])){  //会员空间    
				cls_ArcMain::Url($data,-1);
				$url = $data['marcurl'];
			}else{ 
				cls_ArcMain::Url($data); 
				$url = $data['arcurl'.(empty($addno)?'':$addno)];
			}
		}elseif(empty($cfg['url']) && $this->ptype=='m'){ //文档
			$url = cls_Mspace::IndexUrl($data);
		}elseif($cfg['url'] == '#'){  // 不需要url链接
			return $dre;
		}else $url = key_replace($cfg['url'],$data); //可以自定义url格式
		return "<a href=\"$url\" target=\"_blank\">$dre</a>";
	}
	
	function getFields($re=''){
		$dbtable = $this->table(1); //没有考虑是否支持sqli，后续确认
		$query = $this->db->query("SHOW FULL COLUMNS FROM $dbtable",'SILENT');
		$a = array();
		while($row = $this->db->fetch_array($query)){
			$a[] = $row['Field'];
		}
		return $a;
	}
	
	//删除这个交互的附件 //upload.cls.php对交互处理不够完善,多个交互cid可能重复...
	//但交互一般不用附件,这里哪个交互如果使用,请在delete之前单独调用
	function delatt($cid){ 
		$query = $db->query("SELECT * FROM {$tblprefix}userfiles WHERE aid='{$cid}' AND tid=16");
		while($r = $db->fetch_array($query)){
			atm_delete($r['url'],$r['type']);
			$uploadsize = ceil($r['size'] / 1024);
			if($mid = $r['mid']){
				$user = new cls_userinfo; //图片可能分为管理员,会员/游客上传,分别处理
				$user->activeuser($mid,0);
				$user->updateuptotal($uploadsize,1,1);
			}
		}
		$db->query("DELETE FROM {$tblprefix}userfiles WHERE aid='{$cid}' AND tid=16", 'UNBUFFERED');
	}
	
	// 删除一条交互数据
	// exkey: 用于同时删除回复, 关系:exkey=$cid
	function delete($cid, $exkey=''){
		//删除交互的关联推送信息
		cls_pusher::DelelteByFromid($cid,'commus',$this->cuid);
		//$this->delatt($cid); //哪个交互如果使用,请在delete之前单独调用
		$this->db->delete($this->table())->where('cid='.$cid)->exec();
		if(!empty($exkey)){
			$this->db->delete($this->table())->where($exkey.'='.$cid)->exec();
		}
	}
	
	# 设置积分，添加资料-增加, 删除恶意-减少
	function setCrids($act, $mid=0){
		$actname = $this->cucfgs['cname'];
		if(empty($mid)){
			$user = cls_UserMain::CurUser();
		}else{
			$user = new cls_userinfo;
			$user->activeuser($mid);
		}
		if($act=='add'){
			$num = 0+intval(@$this->cucfgs['acurrency']);	
			$actname = "发布$actname";
		}else{
			$num = 0-intval(@$this->cucfgs['ccurrency']);	
			$actname = "发布$actname";	
		}
		if($num){
			$user->updatecrids(array(1=>$num),1,$actname);
		}
	}
	
	function table($old=0){
		global $tblprefix;
		if($old) return $tblprefix.$this->cucfgs['tbl'];
		else     return '#__'.$this->cucfgs['tbl'];
	}
	// 统一显示一般提示信息
	function message($str = '',$url = ''){
		//call_user_func($this->mc ? 'mcmessage' : 'amessage',$str,$url);
		//$this->top_head();
		cls_message::show($str, $url);
	}
	// 统一显示ajax提示信息(主要是:收藏,关注等)
	function msgajax($key, $cfg=array()){
		$arr = array(
			'OK' => 'succeed',
			'noLogin' => '请先登录会员。',
			'Repeat' => '不能重复操作。',
			'Error' => '错误！',
		);
		if(isset($cfg[$key])){
			$msg = $cfg[$key];	
		}elseif(isset($arr[$key])){
			$msg = $arr[$key];	
		}else{
			$msg = "未知错误[$key]!";	
		}
		cls_message::show($msg);
	}
	
	//管理后台：$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	function guide_bm($str = '',$type = 0){
		if($this->mc){
			m_guide($str,$type ? $type : '');
		}else{
			a_guide($str,$type);
		}
	}
	
	//设置交互加分(在没有建立cuedit对象下使用), demo: cls_cubasic::setCridsOuter($cuid);
	static function setCridsOuter($cuid,$act='add'){
		$cu = new cls_cuedit(array('cuid'=>$cuid));
		$cu->setCrids($act);
	}
	
	//根据文档栏目算文档模型ID
	static function caid2chid($caid){
		$_tcaid = cls_cache::Read('catalog', $caid);
		$chid = preg_replace("/[^\d]/","",$_tcaid['chids']);
		return $chid;
	}
}
