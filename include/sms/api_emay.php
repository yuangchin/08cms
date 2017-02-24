<?php

/**
 * 验证流程概要:
 * 第一次使用时，需使用[序列号]和[密码]进行login(登录操作),并在登录同时产生一个session key
 * 登录成功后，称为[已登录状态],需要保存此产生的session key,用于以后的相关操作(如发送短信等操作)
 * logout(注销操作)后, session key将失效，并且不能再发短信了, 除非再进行login(登录操作)
 */
class sms_emay{
	
	/**
	 * 网关地址 
	 */     
	var $url = 'http://sdk999ws.eucp.b2m.cn:8080/sdk/SDKService?wsdl'; 
	
	/**
	 * 序列号,请通过亿美销售人员获取
	 */
	var $serialNumber;
	
	/**
	 * 密码,请通过亿美销售人员获取
	 */
	var $password;
	
	/**
	 * 登录后所持有的SESSION KEY，即可通过login方法时创建
	 */
	var $sessionKey = '111110'; //可任意6位数- 345678/111110
	
	/**
	 * webservice客户端
	 */
	var $soap;
	
	/**
	 * 默认命名空间
	 */
	var $namespace = 'http://sdkhttp.eucp.b2m.cn/';
	
	/**
	 * 往外发送的内容的编码,默认为 GBK
	 */
	var $outgoingEncoding = "gbk";
	
	/**
	 * @param string $url 			网关地址
	 * @param string $serialNumber 	序列号,请通过亿美销售人员获取
	 * @param string $password		密码,请通过亿美销售人员获取
	 * @param string $sessionKey	登录后所持有的SESSION KEY，即可通过login方法时创建
	 * @param string $extra_par3-5  第3,4,5个参数不用
	 */
	function sms_emay($serialNumber,$password,$extra_par3='',$extra_par4='',$extra_par5='')
	{
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$this->timeout = empty($sms_cfg_timeout) ? 3 : $timeout;
		$this->serialNumber = $serialNumber;
		$this->password = $password;
		//$this->sessionKey = 'EMAYID'; //亿美分配Key
		/**
		 * 初始化 webservice 客户端
		 * @param string $proxyhost		可选，代理服务器地址，默认为 false ,则不使用代理服务器
		 * @param string $proxyport		可选，代理服务器端口，默认为 false
		 * @param string $proxyusername	可选，代理服务器用户名，默认为 false
		 * @param string $proxypassword	可选，代理服务器密码，默认为 false
		 * @param string $timeout		连接超时时间，默认0，为不超时
		 * @param string $response_timeout		信息返回超时时间，默认30
		 */	
		$proxyhost = false; $proxyusername = false; 
		$proxyport = false; $proxypassword = false;
		$this->soap = new nusoap_client($this->url,false,$proxyhost,$proxyport,$proxyusername,$proxypassword,$this->timeout,10); 
		$this->soap->soap_defencoding = $mcharset;
		$this->soap->decode_utf8 = $mcharset=='gbk' ? false : true;				
	}

	function setNameSpace($ns)
	{
		$this->namespace = $ns;
	}
	
	/**
	 * 指定一个 session key 并 进行登录操作
	 * @param string $sessionKey 指定一个session key 
	 * @return int 操作结果状态码
	 * 代码如:
	 * $sessionKey = $smsdo->generateKey(); //产生随机6位数 session key
	 * if ($smsdo->login($sessionKey)==0)
	 * {
	 * 	 //登录成功，并且做保存 $sessionKey 的操作，用于以后相关操作的使用
	 * }else{
	 * 	 //登录失败处理
	 * } 
	 */
	function login()
	{
		$params = array('arg0'=>$this->serialNumber,'arg1'=>$this->sessionKey, 'arg2'=>$this->password);
		$result = $this->soap->call("registEx",$params,	$this->namespace);
		return $this->getReInfo($result);
	}
	
	/**
	 * 注销操作  (注:此方法必须为已登录状态下方可操作)
	 * 
	 * @return int 操作结果状态码
	 * 
	 * 之前保存的sessionKey将被作废
	 * 如需要，可重新login
	 */
	function logout()
	{
		$params = array('arg0'=>$this->serialNumber,'arg1'=>$this->sessionKey);
		$result = $this->soap->call("logout", $params ,
			$this->namespace
		);
		return $this->getReInfo($result);
	}

	/**
	 * 短信发送  (注:此方法必须为已登录状态下方可操作)
	 * @param array $mobiles		手机号, 最多为200个手机号码，如 array('159xxxxxxxx'),如果需要多个手机号群发,如 array('159xxxxxxxx','159xxxxxxx2') 
	 * @param string $content		短信内容，最多500个汉字或1000个纯英文
	 * @param string $sendTime		定时发送时间，格式为 yyyymmddHHiiss, 即为 年年年年月月日日时时分分秒秒,例如:20090504111010 代表2009年5月4日 11时10分10秒
	 * 								如果不需要定时发送，请为'' (默认)
	 * @param string $addSerial 	扩展号, 默认为 ''
	 * @param string $charset 		内容字符集, 默认GBK
	 * @param int $priority 		优先级, 默认5
	 * @return int 操作结果状态码
	 */
	function sendSMS($mobiles,$content,$sendTime='',$addSerial='',$priority=5)
	{
		$mcharset = cls_env::getBaseIncConfigs('mcharset'); //global $mcharset; 
		$this->post->timeout = $this->timeout; 
		if(is_string($mobiles)) $mobiles = explode(',',$mobiles);
		$content = cls_string::iconv($mcharset,"gbk",$content);
		$params = array('arg0'=>$this->serialNumber,'arg1'=>$this->sessionKey,'arg2'=>$sendTime,
			'arg4'=>$content,'arg5'=>$addSerial, 'arg6'=>$mcharset,'arg7'=>$priority
			); //print_r($mobiles); print_r($content);
		/**
		 * 多个号码发送的xml内容格式是 
		 * <arg3>159xxxxxxxx</arg3>
		 * <arg3>159xxxxxxx2</arg3>
		 * ....
		 * 所以需要下面的单独处理
		 */
		foreach($mobiles as $mobile)
		{
			array_push($params,new soapval("arg3",false,$mobile));	
		}
		$result = $this->soap->call("sendSMS",$params,$this->namespace);
		return $this->getReInfo($result);
	}
	
	/**
	 * 余额查询(注:此方法必须为已登录状态下方可操作)
	 * @return double 余额
	 */
	function getBalance()
	{
		$this->post->timeout = $this->timeout; 
		$params = array('arg0'=>$this->serialNumber,'arg1'=>$this->sessionKey);
		$res1 = $this->soap->call("getBalance",$params,$this->namespace); 
		if(strstr($res1,"-")){
			$res1 = str_replace('.0','',$res1);
			$_re = $this->getReInfo($res1); 
			return array('0',"[$_re[1]]"); 	
		}else{
			if(substr($res1,0,1)=='.') $res1 = "0$res1"; // .5
			return array('1',$res1); 	
		}
		//if(is_numeric($res1)) return array('1',$res1); 
		//else return array('-1',0); 
		// getEachFee:查询单条费用  (注:此方法必须为已登录状态下方可操作)
		// @return double 单条费用
		// $params = array('arg0'=>$this->serialNumber,'arg1'=>$this->sessionKey);
		// $res2 = $this->soap->call("getEachFee",$params,$this->namespace);
		//return array(1,$res2);
		
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
			/*
			'1' => '操作成功',
			'10' => '客户端注册失败',
			'11' => '企业信息注册失败',
			'13' => '充值失败',
			'17' => '发送信息失败',
			'18' => '发送定时信息失败',
			'22' => '注销失败',
			'303' => '客户端网络故障',
			'305' => '服务器端返回错误，错误的返回值（返回值不是数字字符串）',
			'307' => '目标电话号码不符合规则，电话号码必须是以0、1开头',
			'308' => '新密码不是数字，必须是数字',
			'997' => '平台返回找不到超时的短信，该信息是否成功无法确定',
			'998' => '由于客户端网络问题导致信息发送超时，该信息是否成功下发无法确定',
			'999' => '操作频繁',
			*/
		);	
		return array($no,isset($a[$no]) ? $a[$no] : '(未知错误)');
		//return isset($a[$no]) ? $a[$no] : '(未知错误)';
	}

}

// 加载本类特有的class
require_once M_ROOT.'/include/nusoaplib/nusoap.php';

// 附加说明
/** getMO() : 
 * 由于服务端返回的编码是UTF-8,所以需要进行编码转换
 echo "短信内容:".iconv("UTF-8","GBK",$mo->getSmsContent());
 手机号码(字符串数组,最多为200个手机号码)
 短信内容(最多500个汉字或1000个纯英文，emay服务器程序能够自动分割；亿美有多个通道为客户提供服务，所以分割原则采用最短字数的通道为分割短信长度的规则，请客户应用程序不要自己分割短信以免造成混乱)
 */

?>
