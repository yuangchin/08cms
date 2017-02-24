<?php
/**
 * 微信操作，获取二维码sensid，ajax的用户信息等
 *
 * @example   请求范例URL：...
 * @author    ...
 * @copyright Copyright (C) ...
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Weixin_Ops_Base extends _08_Models_Base
{
    public $act = '';
	public $appid = '';
	public $wecfg = array();
	
	public function __toString(){
		$this->check(); 
		$this->init();
		$method = $this->act; //echo $method; //print_r($this->_mconfigs);
		if(method_exists($this,$method)){
			return $this->$method();
		}
        return array();
    }

    public function init(){
		$this->act = empty($this->_get['act']) ? '' : $this->_get['act'];
		$this->appid = empty($this->_get['appid']) ? '' : $this->_get['appid'];
		if(empty($this->appid)){
			$this->wecfg = cls_w08Basic::getConfig(); //总站
		}
    }
	
	// 检测,核心为空,各系统扩展
    public function check(){
    	return $this->checkBase();
    }
	// 检测,核心方法
    public function checkBase(){ 
		//$re = array('error'=>'', 'message'=>'');
		//
		// print_r($this->_mconfigs);
		$curuser = cls_UserMain::CurUser();
		if($curuser->isadmin()) return ''; //管理员测试不用这个检测
		if($ore = cls_Safefillter::refCheck('',0)){ 
			cls_message::show("Outsend:$ore"); //"不是来自{$cms_abs}的请求！"
		}
		//return $re;
    }
	
	//检查传图
	public function chkUpload(){
		global $m_cookie; //$m_cookie['msid'] = 'yWcSGO';
		$db = _08_factory::getDBO(); 
		$scene = @$this->_get['scene'];  
		$extp = empty($this->_get['extp']) ? '' : $this->_get['extp'];
		$stamp08 = empty($this->_get['stamp08']) ? '' : $this->_get['stamp08'];
		$sign08 = empty($this->_get['sign08']) ? '' : $this->_get['sign08'];
		//$stampnow = TIMESTAMP; //注意:用总站的wecfg
		$signenc = md5(cls_env::mconfig('authkey').$stamp08.$extp); 
		if(empty($extp) || ((TIMESTAMP-$stamp08)>60*60) || $signenc!=$sign08){
			$res['error'] = '登录失败';
			$res['message'] = "超时或认证失败，请重新扫描。";	
			return $res;
		}
		$whrarr = array('sid'=>$scene,'extp'=>$extp,'cuser'=>$m_cookie['msid'],'smod'=>'scanupload'); //安全吗？!!! 
		$row = $db->select()->from('#__weixin_qrlimit')->where($whrarr)->_and(array('ctime'=>(TIMESTAMP-5*60)),'>')->exec()->fetch();
		if(!empty($row['openid'])){ //!empty($row['extp']) && 
			$db = _08_factory::getDBO(); //$db->setDebug();
			$row = $db->select()->from('#__weixin_msgget')
				->where(array('openid'=>$row['openid'],))->_and(array('ctime'=>(TIMESTAMP-5*60)),'>')->exec();
			while($rowp = $db->fetch()){
				$res['res'][] = $rowp;
			} //print_r($re);
			$res['message'] = "近5分钟传的图…";
			$res['error'] = '';
		}else{
			$res['error'] = "noScan";	
			$res['message'] = "还未扫描。";	
		}
		return $res;
	}
	
	//检查扫描开关
	public function chkQropen($qrmod){
		if(!empty($this->_mconfigs['weixin_debug'])){ 
			return; //调试状态不检查了
		}
		$qrmod || $qrmod = '_null_';
		$chkmod = '-'; $varmods = ','; 
		$arr = array('login','getpw','scansend','scanupload');
		$chkmod = in_array($qrmod,$arr) ? $qrmod : 'error_mod';
		foreach($arr as $k){
			$varmods .= empty($this->_mconfigs["weixin_$k"]) ? '' : "$k,";
		}
		//echo "($varmods:$chkmod)";
		if(!strpos($varmods,$chkmod)){
			cls_message::show("[$qrmod]相关微信功能开关未开启");
		}
	}
	
    public function getQrcode(){ //注意:用总站的wecfg
		$qrmod = empty($this->_get['qrmod']) ? 'login' : $this->_get['qrmod'];
		$this->chkQropen($qrmod);
		$extp = empty($this->_get['extp']) ? '' : $this->_get['extp'];
		//if(strlen($extp)<6){  }
		$extp = cls_w08Basic::iconv('utf-8',cls_w08Basic::getConfig('mcharset','baseinc'),$extp);
		$wxqr = new cls_w08Qrcode($this->wecfg); 
		$qrcode = $wxqr->getQrcode($qrmod, 'limit', $extp); 
		$qrcode['stamp08'] = TIMESTAMP; //注意:用总站的wecfg
		$qrcode['sign08'] = md5(cls_env::mconfig('authkey').TIMESTAMP.$extp); 
		return $qrcode;
	}
	
    public function getUserInfo(){
		/*/权限?! (会员管理文档的微信配置时，可能不适用)
		$curuser = cls_UserMain::CurUser();
		$re1 = $curuser->NoBackFunc('member') ? 1 : 0;
		$re2 = $curuser->NoBackFunc('normal') ? 1 : 0;
		$re3 = $curuser->NoBackFunc('weixin') ? 1 : 0;
		$re0 = $re1 + $re2 + $re3;
		if($re0==3) cls_message::show("没有权限"); */
		$wecfg = cls_w08Basic::getConfig($this->appid, 'appid');
		$ustr = empty($this->_get['ustr']) ? '' : $this->_get['ustr'];
		$ustr = str_replace(array('~'),array('-'),$ustr);
		$weixin = new cls_wmpUser($wecfg);
		$data = $weixin->getUserBatch($ustr);
		return $data;
	}

	// getAccess
    public function xxx_getAccess(){
		;//
    }


}