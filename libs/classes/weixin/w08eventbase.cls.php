<?php
// 事件响应操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件
// 各扩展系统需求变化很大,re开头的方法都加Base,扩展类里面不加Base,先检测执行无Base的方法,在找含有Base的方法

class cls_w08EventBase extends cls_wmpMsgresp{

	public $_db = NULL;
	public $qrtable = 'weixin_qrlimit'; //使用哪个二维码表?!, 只使用一个，还是按操作类别分开？
	public $qrexpired = '5'; //5分钟过期...
	public $qrInfo = array();
	
	// 常用值
	public $eventKey = ''; //二维码场景ID
	public $fromName = ''; //微信open_id(用户ID)
	public $ghUser = ''; //开发者微信号
	public $subScan = ''; //扫描先关注再操作标记
	
	public $push_paid = ''; //push_paid
	
	public $sflag = ''; //扫描随机标记,用于安全检查

	function __construct($post,$wecfg){ 
		parent::__construct($post,$wecfg); 
		$method = $this->getMethod('Event'); 
		$this->_db = _08_factory::getDBO();
		$this->init();
		//setQrtable
		return $this->$method();
	}
	
	function init(){
		$this->eventKey = $this->post->EventKey; 
		$this->fromName = $this->post->FromUserName; 
		$this->ghUser = $this->post->ToUserName; 
	}
	
    // 取消关注事件
    function reUnsubscribeBase($re=0){ 
		//取消会员绑定,(是否删除会员？！)
		#$this->_db->update("#__members", array('weixin_from_user_name'=>''))->where(array('weixin_from_user_name'=>$this->fromName))->exec();
		#if($re) return;
		die('');
	}
    
    // 响应关注/扫描带参数二维码事件
    function reSubscribeBase($re=0){ 
		//查找关键字...
		$weres = new cls_w08Response($this->post,$this->cfg,1); 
		$reauto = $weres->getKeyList('','add_friend_autoreply_info'); 
		$msg = empty($reauto) ? "您好，欢迎您关注 ".cls_env::mconfig('hostname')."。" : $reauto;
		//回复
		if($re) return $msg;
		die($this->remText($msg));
    }
    
	// 用户已关注时,扫描带参数二维码的事件推送
    function reScanBase($fromSub=''){ 
		if($fromSub){
			$this->subScan = 1;
			$eventKey = $this->eventKey = $fromSub; 
		}else{
			$eventKey = $this->eventKey; 
		}
		if(!in_array(strlen($eventKey),array(5,10))){ die(''); }
		$this->qrInfo = $this->getQrinfo($eventKey);
		$modKey = ucfirst(strtolower($this->qrInfo['smod'])); //echo ".$modKey.$eventKey";
		if(empty($modKey)){
			die($this->remText("二维码已过期，请重新获取二维码再操作！[$modKey:$eventKey]"));
		}
		$method = method_exists($this,"scan$modKey") ? "scan$modKey" : 'scan_Extra'; //echo "$method,scan$modKey";
		return $this->$method();
    }
	function scanLoginBase(){
		$msg = $this->subScan ? $this->reSubscribeBase(1) : '';
		$qrInfo = $this->getQrinfo($this->eventKey);
		$row = $this->_db->select()->from('#__members')->where(array('weixin_from_user_name'=>$this->fromName))->exec()->fetch(); 
		//用授权链接..., 安全...
		$ocfg = cls_w08Basic::getConfig(0, 'sid');
		$wxmenu = new cls_w08Menu($ocfg);
		if(!$row){  
			$msg .= " 您未用微信扫描登录过本站账户:";
			// snsapi_base snsapi_userinfo //&openid=$this->fromName
			$mname = cls_w08Basic::iconv(cls_env::getBaseIncConfigs('mcharset'),'utf-8',$row['mname']);
			$url = "{mobileurl}wxlogin.php?oauth=snsapi_base&state=binding&scene=$this->eventKey&mname=".urlencode($mname)."&sflag=".$this->sflag."";
			$wesid = cls_w08Basic::getConfig();
			$wem = new cls_w08MenuBase($wesid);
			$url = $wem->fmtUrl($url);
			$msg .= " <a href='$url'>点击绑定或添加账户</a>。";
			//echo ":::".cls_w08Tester::showUrl($url); //echo $row['mname'] . urlencode($row['mname']); 
		}else{
			// snsapi_base snsapi_userinfo //&openid=$this->fromName
			$mname = cls_w08Basic::iconv(cls_env::getBaseIncConfigs('mcharset'),'utf-8',$row['mname']);
			$url = "{mobileurl}wxlogin.php?oauth=snsapi_base&state=dologin&scene=$this->eventKey&mid={$row['mid']}&sflag=".$this->sflag."";
			$wesid = cls_w08Basic::getConfig();
			$wem = new cls_w08MenuBase($wesid);
			$url = $wem->fmtUrl($url);
			$msg .= " <a href='$url'>点击自动登录</a>。";
			//echo ":::".cls_w08Tester::showUrl($url); //echo $row['mname'] . urlencode($row['mname']); 
		} 

		return $msg;
	}
	// 点击找回密码(用于电脑端,客户端不需要密码登录)
	function scanGetpwBase(){ 
		//直接发微信信息...
		$msg = $this->subScan ? $this->reSubscribeBase(1) : '';
		$row = $this->_db->select()->from('#__members')->where(array('weixin_from_user_name'=>$this->fromName))->exec()->fetch();
		if(!$row){  
		   $msg .= " 您未用微信扫描注册过本站账户:\n 1. 请点[微信登录]相关链接绑定或注册帐号！\n 2. 在微信菜单中点[我-会员中心]相关链接绑定或注册帐号！";
		}else{
			// snsapi_base snsapi_userinfo //&openid=$this->fromName
			$mname = cls_w08Basic::iconv(cls_env::getBaseIncConfigs('mcharset'),'utf-8',$row['mname']);
			$url = "{mobileurl}wxlogin.php?oauth=snsapi_base&state=dogetpw&scene=$this->eventKey&mname=".urlencode($mname)."&sflag=".$this->sflag.""; //
			$wesid = cls_w08Basic::getConfig();
			$wem = new cls_w08MenuBase($wesid);
			$url = $wem->fmtUrl($url);
			$msg .= " <a href='$url'>点击重置密码</a>。";
			//echo ":::".cls_w08Tester::showUrl($url); //echo $row['mname'] . urlencode($row['mname']); 
		}
		return $msg;
	}
	function scanScanuploadBase($re=0){
		$msg = "您已开启微信传图模式，点击左下的小键盘图标，发送你需要上传的图片吧。";
		if($re) return $msg;
		die($this->remText($msg));
	}
	function scanScansendBase(){
		$qrInfo = $this->getQrinfo($this->eventKey);
		$this->sendCfgs = explode(',',$qrInfo['extp']);
		$this->getScansendInfo($this->sendCfgs[0], @$this->sendCfgs[2]);
		//print_r($this->sendInfo);
	}
	# (文档/会员)的信息(仅数据)
	function getScansendInfo($type,$pid,$detail=1){
		$pid = intval($pid);
		$pinfo = array(); //echo "($type,$pid,$detail)";
		if($type=='a'){
			$arc = new cls_arcedit;
			$arc->set_aid($pid,array('au'=>0,'ch'=>$detail));
			$pinfo = $arc->archive;
			$pinfo && cls_ArcMain::Parse($pinfo);	
		}elseif($type=='m'){
			$user = new cls_userinfo;
			$user->activeuser($pid,$detail);
			$user->sub_data(); 
			$pinfo = $user->info;
		} 
		if(!empty($pinfo)) $pinfo['_pid'] = $pid; //统一保存pid
		$this->sendInfo = $pinfo;
	}
	function getScansendText($pid,$fields,$tplink){ //$re=array()
		$i = 0; $s = '';
		foreach($fields as $k=>$v){
			if($i==0){
				$s .= "$v [".$this->sendInfo[$k]."] 资料\n"; 
			}else{
				$s .= "$v: ".$this->sendInfo[$k]."\n"; 	
			}
			$i++;
		}
		if(!empty($tplink)){
			$link = str_replace(array('{aid}','{mid}'),$pid,cls_w08Basic::fmtUrl($tplink));
			$s .= "<a href='$link'>详情：>> </a>\n"; 
		}
		return $s;
		/*
		车商[company]资料
		联系人 author
		联系电话 dianhua
		预售价 oldprices
		车辆颜色 maincolor
		详情：>>> 
		二手车[subject]资料
		联系人 author
		联系电话 dianhua
		预售价 oldprices
		车辆颜色 maincolor
		详情：>>> 
		*/	
	}
	
    // 响应上报地理位置事件, 这里保存供使用
	// 用户同意上报地理位置后，每次进入公众号会话时，都会在进入时上报地理位置，或在进入会话后每5秒上报一次地理位置，
	// 公众号可以在公众平台网站中修改以上设置。
    function reLocationBase(){ 
		$this->savePos('auto');
		die('');
    }
    
	// MENU_PUSH_push_144 -=> clickPushBase() clickPush()
	
    // 响应点击事件（即根据用户点击的按钮响应相应的回复）
    function reClickBase(){ 
		$eventKey = ucfirst(strtolower($this->eventKey)); 
		$eventKey = str_replace(array('_','-'),'',$eventKey);
		//$method = method_exists($this,"click$eventKey") ? "click$eventKey" : 'click_Extra';
		$method = "click$eventKey";
		if(method_exists($this,$method)){
			//$method = $method; //废话
		}elseif(substr($this->eventKey,0,10)=='MENU_PUSH_'){ //MENU_PUSH_
			$method = 'clickPush';
			$this->push_paid = substr($this->eventKey,10);
		}else{
			$method = 'click_Extra'; //
		} 
		return $this->$method();
    }
	// 点击自定义菜单:无对应操作的处理
	function click_Extra(){
		$msg = "无对应操作：[{$this->post->EventKey}], 请联系管理员！";
		$msg = $this->remText($msg);
		$msg = cls_w08Basic::iconv(cls_env::getBaseIncConfigs('mcharset'),'utf-8',$msg);
		die($msg);
	}
	
	// 用户点击自定义跳转URL菜单 事件处理
	function reViewBase(){
		die('');
	}
	// 组xml数据 
	function clickPushBase($re=1){
		$data = cls_w08Push::getPushData($this->push_paid);
		$msg = $this->remNews($data);
		// msg .... 组xml数据
		if($re) return $msg;
		die($msg);
	}
	// 各系统扩展
	function clickPush(){
		$msg = $this->clickPushBase(1);
		//echo "<pre>"; print_r($msg); echo "\n\naaddd";
		die($msg);
	}

	//获取场景二维码数据
    function getQrinfo($sid, $table=''){
		$table || $table = $this->qrtable;
		$timeNmin = TIMESTAMP-($this->qrexpired*60*2); //10分钟 //saveState
		$row = $this->_db->select()->from("#__$table")->where(array('sid'=>$sid))->_and(array('ctime'=>$timeNmin),'>')->exec()->fetch(); 
		$this->sflag = cls_string::Random(8);
		$this->_db->update("#__$table", array('sflag'=>$this->sflag,'openid'=>$this->fromName))->where(array('sid'=>$sid))->exec();
		return $row;
	}

}
