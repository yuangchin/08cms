<?php
/**
 * sms_emhttp；
 * http://sdk4report.eucp.b2m.cn:8080/
 * 这个接口是不需要账号和IP地址绑定的，账号是以6SDK开头的
 * http://sdkhttp.eucp.b2m.cn/
 * 这个接口是需要账号和IP地址绑定的，账号以3SDK开头的
 */
class sms_emhttp{
	
	// 序列号
	var $userid;
	// 密码/Key
	var $userpw;
	// SDK-Map
	var $urlmap = array(
		'3SDK' => 'http://sdkhttp.eucp.b2m.cn/sdkproxy/',
		'6SDK' => 'http://sdk4report.eucp.b2m.cn:8080/sdkproxy/',
		'_NUL' => 'http://sdk4report.eucp.b2m.cn:8080/sdkproxy/', //按官方文档
	);
	// base path
	var $baseurl = ''; 
	// post对象
	var $post;

	// 第3,4,5个参数不用
	function sms_emhttp($userid,$userpw,$extra_par3='',$extra_par4='',$extra_par5='')
	{
		$this->userid = $userid;
		$this->userpw = $userpw;
		$fix = substr($userid,0,4);
		if(isset($this->urlmap[$fix])){
			$this->baseurl = $this->urlmap[$fix];	
		}else{
			$this->baseurl = $this->urlmap['_NUL'];	
		}
		$this->getInit();
	}
	
	/**
	 * 余额 初始化
	 */
	function getInit()
	{
		$this->post = new http();
		//$this->post->timeout = $this->timeout;
		$this->post->setCookies(60);
	}
	
	/**
	 * 发送短信；(utf-8编码)
	 */
	function sendSMS($mobiles,$content)
	{
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$this->post->timeout = $this->timeout; 
		if(is_array($mobiles)) $mobiles = implode(',',$mobiles);
		$content = cls_string::iconv($mcharset,"utf-8",$content);
		$content = str_replace(array(' ','　',"\r","\n"),'',$content); // 短信内容不支持空格???
		$content = urlencode($content); 
		$path = "sendsms.action?cdkey={$this->userid}&password={$this->userpw}&phone=$mobiles&message=$content&addserial=";
		$html = cls_sms::getHttpData("$this->baseurl$path");
		$erno = $this->getEVal($html,'error');
		$emsg = $this->getEVal($html,'message');
		return $this->getReInfo(!$erno, $emsg);
	}
	
	/**
	 * 余额查询 
	 */
	function getBalance()
	{
		$this->post->timeout = $this->timeout; 
		$path = "querybalance.action?cdkey={$this->userid}&password={$this->userpw}";
		$html = $this->post->fetchtext("$this->baseurl$path",'POST');
		$erno = $this->getEVal($html,'error');
		$emsg = $this->getEVal($html,'message');
		if(substr($emsg,0,1)=='-'){
			$emsg = $this->getReInfo(str_replace('.0','',$emsg));
			return array('0',"[$emsg[1]]"); 	
		}else{
			if(substr($emsg,0,1)=='.') $emsg = "0$html";
			return array('1',$emsg); 	
		}
	}
	
	/**
	 * 返回值-描述 对应表
	 */
	function getReInfo($no)
	{
		if($no=='0') $no = '1';
		$a = array(
		
			'1'=>'操作成功',
			'-1'=>'系统异常',
			'-101'=>'命令不被支持',
			'-102'=>'用户信息删除失败',
			'-103'=>'用户信息更新失败',
			'-104'=>'指令超出请求限制',
			'-111'=>'企业注册失败',
			'-117'=>'发送短信失败',
			'-118'=>'获取MO失败',
			'-119'=>'获取Report失败',
			'-120'=>'更新密码失败',
			'-122'=>'用户注销失败',
			'-110'=>'用户激活失败',
			'-123'=>'查询单价失败',
			'-124'=>'查询余额失败',
			'-125'=>'设置MO转发失败',
			'-127'=>'计费失败零余额',
			'-128'=>'计费失败余额不足',
			'-1100'=>'序列号错误,序列号不存在内存中,或尝试攻击的用户',
			'-1102'=>'序列号正确,Password错误',
			'-1103'=>'序列号正确,Key错误',
			'-1104'=>'序列号路由错误',
			'-1105'=>'序列号状态异常 未用1',
			'-1106'=>'序列号状态异常 已用2 兼容原有系统为0',
			'-1107'=>'序列号状态异常 停用3',
			'-1108'=>'序列号状态异常 停止5',
			'-113'=>'充值失败',
			'-1131'=>'充值卡无效',
			'-1132'=>'充值卡密码无效',
			'-1133'=>'充值卡绑定异常',
			'-1134'=>'充值卡状态异常',
			'-1135'=>'充值卡金额无效',
			'-190'=>'数据库异常',
			'-1901'=>'数据库插入异常',
			'-1902'=>'数据库更新异常',
			'-1903'=>'数据库删除异常',
		);	
		return array($no,isset($a[$no]) ? $a[$no] : '(未知错误)');
		//return isset($a[$no]) ? $a[$no] : '(未知错误)';
	}
	
	// 结果处理
	// <response><error>0</error><message>3.0</message></response>
	function getEVal($data='',$tag=''){
		preg_match("/<$tag>(.*)<\/$tag>/i",$data,$vals); 
		if(isset($vals[1])){
			return $vals[1];	
		}else{
			return '';	
		}
	}

}

// 加载本类特有的class
include_once M_ROOT."include/http.cls.php";


?>