<?php
/**
 * 根据后台短信模块启用情况; 获取配置信息,获取验证码,发送短信
 *
 * @example   请求范例URL：/index.php?/ajax/sms_msend/mod/arc2pub/act/
                           /index.php?/ajax/sms_msend/mod/arc2pub/act/code/tel/132233322433
						   /index.php?/ajax/sms_msend/mod/arc2pub/act/send/tel/13223332244/msgpara/a,3,4567
							 > msgpara=a,3,456
							 > a代表文档，m代表会员，
							 > 3,代表文档模型，或会员模型
							 > 456,代表文档id，或会员id。
 * @author    Peace <@08cms.com>
 * @copyright Copyright (C) 2008 - 2015 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_sms_msend_Base extends _08_Models_Base
{
	public $mod = ''; //公用参数：mod (短信“启用模块”中启用的模块id)
	public $act = 'init'; //init(初始化),code(发送认证码),send(发送信息)
	public $tel = ''; //号码
	public $tpl = ''; //配置中的短信模版
	public $msg = ''; //扩展中的短信内容
	public $mpar = array();
	public $dbarr = array();
	//private $re = array('error'=>'', 'message'=>'');
	public $sms = null; //new cls_sms();
	
    public function __toString(){
		$this->init(); 
		//安全综合检测
		$re = $this->check_all(); 
		if($re['error']) return $re;
		//执行操作
		$func = "sms_".$this->act;
		return $this->$func();
    }
	
	/* ==================== 短信操作相关方法 ==================== */
	
	// init(初始化)
    public function init(){   
		$this->mod = empty($this->_get['mod']) ? '' : $this->_get['mod'];
		$this->act = empty($this->_get['act']) ? 'init' : $this->_get['act'];
		$this->tel = empty($this->_get['tel']) ? '' : $this->_get['tel'];
		$this->sms = new cls_sms();
		$this->tpl = $this->sms->smsTpl($this->mod);
		// 信息详情，配置/获取
		if($this->act!=='check'){
			$this->set_content();
		}
	}
	
	// sms_init(初始化)
    public function sms_init(){   
		$re = array('error'=>'', 'message'=>'初始化成功');
		$re['tpl'] = $this->tpl;
		$this->check_init('init'); 
		return $re;
	}
	
	// code(发送认证码)
	// tel : 手机(电话)号码
    public function sms_code(){   
		$re = array('error'=>'', 'message'=>'');
		$code = cls_string::Random(6, 1); 
		$tel = $this->tel;
		$this->msg = str_replace(array('{$smscode}','%s','{$smscode}'), $code, $this->tpl);
		$sre = $this->sms->sendTpl($tel,$this->msg,$this->dbarr,'ctel');
    	if($sre[0]==1){ 
			global $m_cookie; 
			$ckkey = 'smscode_'.$this->mod; 
			$cksave = authcode(TIMESTAMP."\t$code\t$tel", 'ENCODE'); 
			msetcookie($ckkey, $cksave, 3600); 
			$re['message'] = '发送成功';
			$re['stamp'] = TIMESTAMP;
		}else{
			$re['error'] = 'ErrorSend';	
			$re['message'] = $sre[1];	
		}
		return $re;
	}

	// check(检查认证码是否输入正确)
	// send : 发送时再认证
	// must : 发短信时认证,且必须经过发认证码（根据需要扩展）
	// url : code : 认证码
	// url : stamp : 时间戳
    public function sms_check($send=0,$must=0){   
		$re = array('error'=>'', 'message'=>'');
		@$pass = smscode_pass($this->mod,$this->_get['code'],$this->_get['tel']); //带了tel参数就一起认证
		$isjs = empty($this->_get['isjs']) ? '' : $this->_get['isjs'];
		if($isjs){ //使用js类认证,按它要的规范返回
			if($pass){
				$restr = '';
			}else{ 
				$restr = '认证码错误或超时';
			}
        	return $restr;	
		}elseif($send){ //发送信息认证
			if(empty($cksave) && empty($must)){ //没有经过发认证码过程：sms_code()
				$re['message'] = 'OK';
			}elseif($pass){
				$re['message'] = 'OK';
				$re['tel'] = @$this->_get['tel'];
			}else{ // 如果smscode_mod不为空，发送时再次认证? 
				$re['error'] = 'checkError';
				$re['message'] = '认证码错误或超时';
			}
		}elseif($pass){ 
			$re['message'] = 'OK';
		}else{ 
			$re['error'] = 'checkError';
			$re['message'] = '认证码错误或超时';
		}
		return $re;
	}
	
	// send(发送信息)
	// code : 认证码 --- 执行了发“认证码”操作，需要此参数
	// stamp : 时间戳 --- 执行了发“认证码”操作，需要此参数
	// tel : 手机(电话)号码
	// msg : 短信内容(注意url提交短信内容不能太长,<200汉字为宜)
    public function sms_send(){   
		$re = array('error'=>'', 'message'=>'');
		$tel = $this->tel;
		// 发送时再次认证? 
		//$rc = $this->sms_check(1);
		//regcode_pass($rname,$code='')
		$regcode = @$this->_get['regcode'];
		//if(!regcode_pass('register',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码输入错误！',M_REFERER);
		if(!regcode_pass('freesms',empty($regcode) ? '' : trim($regcode))){
			$re['error'] = 'ErrorCode';	
			$re['message'] = '验证码输入错误！';
			return $re;
		} 
		$sre = $this->sms->sendTpl($tel,$this->msg,$this->dbarr,'sadm');
    	if($sre[0]==1){ 
			$re['message'] = '发送成功';
		}else{
			$re['error'] = 'ErrorSend';	
			$re['message'] = $sre[1];	
		}
		//默认清除cookie, 除非!empty(设置：cksmscode_noclear=1)
		$ckkey = 'smscode_'.$this->mod; 
		#$cksmscode_noclear = empty($this->_get['cksmscode_noclear']) ? '0' : $this->_get['cksmscode_noclear'];
		#$cksmscode_noclear || msetcookie($ckkey, '', -3600);
		mclearcookie($ckkey);
		return $re;
	}
	
	/* ==================== 安全检测相关方法 ==================== */
	
	//安全综合检测
    public function check_all(){
		//url检测
		$re = $this->check_curl(); 
		if($re['error']) return $re;
		//act检测：//为空或未开启或未定义的操作
		if(empty($this->mod) || !$this->sms->smsEnable($this->mod) || !in_array($this->act,array('init','code','check','send'))){ 
			return array('error'=>'close', 'message'=>'模块关闭或参数不合法');
		}
		//是否sms_init初始化检测
		if(in_array($this->act,array('code','send'))){ 
			$re = $this->check_init('check');
			if($re['res']!=='OK') return array('error'=>'Timout', 'message'=>'超时或未初始化');	
			$this->tel = empty($this->_get['tel']) ? '' : $this->_get['tel'];
			if(!preg_match("/^\d{3,4}[-]?\d{7,8}$/", $this->tel)){
				$re['error'] = 'ErrorNumber';	
				$re['message'] = '号码错误';
				return $re;	
			}
		}
		return array('error'=>'', 'message'=>'');	
	}
	
	//url外部网页提交检测
    public function check_curl(){
		$re = array('error'=>'', 'message'=>'');
		$curuser = cls_UserMain::CurUser();
		if($curuser->isadmin()) return $re; //管理员测试不用这个检测
		if($ore = cls_Safefillter::refCheck('',0)){ // die("不是来自{$cms_abs}的请求！");
			return array('error'=>"Outsend", 'message'=>$ore);
		}
		return $re;
	}
	
	//加密cookie:初始化,检测,设置
    public function check_init($act='init',$data=''){
		global $authorization, $m_cookie; //TIMESTAMP;
		$ckkey = 'smsinit_'.$this->mod; 
		$re = 'OK'; $ckval = '';
		if($act=='init'){
			$ckval = TIMESTAMP.':'.md5(TIMESTAMP."$ckkey$authorization");
			msetcookie($ckkey, $ckval, 3600); //echo "$ckkey, $ckval";
		}elseif($act=='check'){ 
			$ckval = @$m_cookie[$ckkey].":";
			$ckarr = explode(':',$ckval);
			$ckchk = md5($ckarr[0]."$ckkey$authorization");
			$re = strstr($ckval,$ckchk) ? 'OK' : 'Error'; 
		} //echo $ckval;
		return array('res'=>$re,'val'=>$ckval);
	}

	//set_contentBase
    public function set_contentBase(){ 
		if($this->act=='init') return;
		//由msgpara参数获取的db资料：
		$arr = explode(',',@$this->_get['msgpara']); // msgpara=a|m,3,4567
		if(count($arr)==3 && is_numeric($arr[1]) && is_numeric($arr[2])){ 
			$this->mpar['type'] = $arr[0]=='m' ? 'm' : 'a';
			$this->mpar['chid'] = $arr[1];
			$this->mpar['infoid'] = $arr[2];
			$this->get_dbdata($this->mpar['infoid'],$this->mpar['type']);
			$key = $this->mpar['type']=='m' ? 'mchid' : 'chid';
			if(empty($this->dbarr)){
				cls_message::show('[null]信息参数错误！');		
			}elseif(@$this->dbarr[$key]!==@$this->mpar['chid']){
				cls_message::show('[chid]信息参数错误！');	
			}
		} 
		// 获取模版
		if($this->act=='code'){ //认证码类：
			$this->msg = $this->tpl; 
		}else{ //send-发送类
			if(!empty($this->dbarr)){
				$exmsg = $this->get_msgExt(); 
			}
			if(!empty($exmsg)){
				$this->msg = $exmsg;
			}else{ 
				if(strpos($this->tpl,'{$smscode}') || strpos($this->tpl,'%s')){ 
					$this->msg = '';
				}else{
					$this->msg = $this->tpl;	
				}
			} 
			if(empty($this->msg)){
				cls_message::show('信息内容为空！');		
			}
		} 
		// 合并数据
		$this->dbarr = empty($this->dbarr) ? $this->_get : array_merge($this->dbarr, $this->_get); 
		// 修正数据
		$this->fix_msgArr();
	}
	//set_content(扩展方法，各扩展系统可重写)
    public function set_content(){ 
		$this->set_contentBase(); 
	}
	
	//get_dbdata
    public function get_dbdata($pid,$type='a',$re=0){
		$pid = intval($pid);
		$pinfo = array();
		if($type=='a'){
			$arc = new cls_arcedit;
			$arc->set_aid($pid,array('au'=>0,'ch'=>1));
			$pinfo = $arc->archive;
			$pinfo && cls_ArcMain::Parse($pinfo);	
		}elseif($type=='m'){
			$user = new cls_userinfo;
			$user->activeuser($pid,1);
			$pinfo = $user->info;
		} //print_r($pinfo);
		if($re) return $pinfo; 
		$this->dbarr = $pinfo;
	}
	
	//* 扩展系统示例：
	// getmsg:得到含有占位符的信息
    public function get_msgExt(){   
		$msg = '';
		if($this->mpar['type']=='a'){ // && $this->mpar['chid']==4
			$msg = '{subject}'; 
		}elseif($this->mpar['type']=='m'){ // && $this->mpar['chid']=='2'
			$msg = '{company}';
		}
		return $msg;
	}
	// fix_msg:修正信息
    public function fix_msgArrBase(){   
		$this->dbarr['mykey'] = '自定义';
		//没有$的加上$,两个$$的还原一个$
		$this->msg = str_replace(array('{','{$$'),array('{$','{$'),$this->msg);
		$hostname = cls_env::mconfig('hostname'); //很多接口要求签名,用这个默认签名
		if(!strpos($this->msg,$hostname) || !strpos($this->msg,'hostname}'))
		$this->msg = $this->msg.'【{$hostname}】';
	}
	// fix_msg:修正信息(扩展方法，各扩展系统可重写)
    public function fix_msgArr(){   
		$this->fix_msgArrBase();
	}
	
}

