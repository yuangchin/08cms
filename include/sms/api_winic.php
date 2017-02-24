<?php
/**
 * sms_winic；
 */
class sms_winic{
	
	// 序列号
	var $userid;
	// 密码
	var $userpw;
	// base path
	var $baseurl = 'http://service.winic.org';
	// post对象
	var $post;

	// 第3,4,5个参数不用
	function sms_winic($userid,$userpw,$extra_par3='',$extra_par4='',$extra_par5='')
	{
		$this->userid = $userid;
		$this->userpw = $userpw;
		$this->getInit();
	}
	
	/**
	 * 余额 初始化
	 */
	function getInit()
	{
		$this->post = new http(); 
		$this->post->setCookies(60);
	}
	
	/**
	 * sms_winic；
	 * 超过70个字符会自动分多条发送。短信内容不支持空格(webservice接口支持)。
	 * 建议每次提交在100个号内，超过请自行做循环
	 * 每次GET提交请不要大于100个号码。Post方式每次提交请不要大于5000个号码，多个手机号码用 , 英文逗号隔
	 * HTTP接口发送和接收的短信内容必须是GB2312编码。HTTP发送,内容不支持空格
	 */
	function sendSMS($mobiles,$content)
	{
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$this->post->timeout = $this->timeout; 
		if(is_array($mobiles)) $mobiles = implode(',',$mobiles);
		$content = cls_string::iconv($mcharset,"gbk",$content);
		$content = str_replace(array(' ','　',"\r","\n"),'',$content); // 短信内容不支持空格???
		$content = urlencode($content); 
		$path = "/sys_port/gateway/?id={$this->userid}&pwd={$this->userpw}&to=$mobiles&content=$content";
		$html = $this->post->fetchtext("$this->baseurl$path",'POST');
		// -02/Send:2/Consumption:0/Tmoney:0/sid:
		return $this->getReInfo($html);
	}
	
	/**
	 * 余额查询 
	 * @return double 余额
	 */
	function getBalance()
	{
		$this->post->timeout = $this->timeout; 
		$path = ":8009/webservice/public/remoney.asp?uid={$this->userid}&pwd={$this->userpw}";
		$html = $this->post->fetchtext("$this->baseurl$path",'POST');
		if(substr($html,0,1)=='-'){
			return array('-1',0);
		}else{
			if(substr($html,0,1)=='.') $html = "0$html";
			return array(1,$html);
		}
	}
	
	/**
	 * 返回值-描述 对应表
	 */
	function getReInfo($info)
	{
		if(strlen($info)>3) $no = substr($info,0,3);
		else $no = $info; //var_export($info); echo "($no)";
		if($no=='000') $no = '1';
		$a = array(
			'nul' => '无接收数据',
			'1'   => '操作成功',
			'-01' => '当前账号余额不足！',
			'-02' => '当前用户ID错误！',
			'-03' => '当前密码错误！',
			'-04' => '参数不够或参数内容的类型错误！',
			'-05' => '手机号码格式不对！',
			'-06' => '短信内容编码不对！',
			'-07' => '短信内容含有敏感字符！',
			'-09' => '系统维护中.. ',
			'-10' => '手机号码数量超长！', //短信内容超长！（70个字符）目前已取消
			'-11' => '短信内容超长！',
			'-12' => '其它错误！',
		);	
		$msg = isset($a[$no]) ? $a[$no] : '(未知错误)';
		return array($no,"$msg($info)");
	}

}

// 加载本类特有的class
include_once M_ROOT."include/http.cls.php";


?>