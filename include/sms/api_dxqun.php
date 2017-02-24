<?php
/**
 * sms_dxqun；
 * 
 */
class sms_dxqun{
	
	// 序列号
	var $userid;
	// 密码
	var $userpw;
	// base path
	var $baseurl = ''; // http://http.dxsms.com, http://http.chinasms.com.cn, http://http.chinasms.com.cn
	// post对象
	var $post;

	// 第3,4,5个参数不用
	function sms_dxqun($userid,$userpw,$extra_par3='',$extra_par4='',$extra_par5='')
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
	 * sms_dxqun；
	 * 超过70个字符会自动分多条发送。短信内容不支持空格(webservice接口支持)。
	 * 建议每次提交在100个号内，超过请自行做循环
	 * 每次GET提交请不要大于100个号码。Post方式每次提交请不要大于5000个号码，多个手机号码用 , 英文逗号隔
	 * HTTP接口发送和接收的短信内容必须是GB2312编码。HTTP发送,内容不支持空格
	 */
	function sendSMS($mobiles,$content)
	{
		$mcharset = cls_env::getBaseIncConfigs('mcharset'); //global $mcharset; 
		$this->post->timeout = $this->timeout; 
		
		$baseurl = 'http://sms.106jiekou.com/gbk/sms.aspx?'; 
		
		if(is_array($mobiles)) $mobiles = implode(',',$mobiles);
		$content = cls_string::iconv($mcharset,"gbk",$content);
		$content = str_replace(array(' ','　',"\r","\n"),'',$content); // 短信内容不支持空格???
		$content = rawurlencode($content); // urlencode(
		$path = "account={$this->userid}&password=".$this->userpw."&mobile=$mobiles&content=$content"; //md5()
		$html = cls_sms::getHttpData("$baseurl$path");
		$re = $this->getReInfo($html); 
		if($re[0]!='1') { $re[0] = "-".$re[0]; } //错误信息，统一返回-999格式
		return $re;
	}
	
	/**
	 * 余额查询 
	 * @return double 余额
	 */
	function getBalance()
	{
		$this->post->timeout = $this->timeout; 
		$baseurl = 'http://www.dxton.com/webservice/sms.asmx/GetNum?';
		$path = "account={$this->userid}&password=".$this->userpw.""; 
		$html = $this->post->fetchtext("$baseurl$path"); 
		// re : <string xmlns="http://www.dxton.com/">0.900</string>
		if(strstr($html,'</string>')){
			$val = strip_tags($html); 
			$val = preg_replace("/[^0-9.]/",'',$val); //var_dump($val);
			return array('1',$val);
		}else{
			return array('-1',0);
		}
	}
	
	/**
	 * 返回值-描述 对应表
	 */
	function getReInfo($info)
	{
		if(strlen($info)>3) $no = substr($info,0,3);
		else $no = $info; //var_dump($info); echo "($no)";
		if($no=='100') $no = '1';
		$a = array(
			'1'   => '发送成功',
			'101' => '验证失败！',
			'102' => '手机号码格式不正确！',
			'103' => '会员级别不够！',
			'104' => '内容未审核！',
			'105' => '内容过多！',
			'106' => '账户余额不足！',
			'107' => 'Ip受限！',
			'108' => '手机号码发送太频繁',
			'120' => '系统升级！',
		);	
		$msg = isset($a[$no]) ? $a[$no] : '(未知错误)';
		return array($no,"$msg($info)");
	}

}

// 加载本类特有的class
include_once M_ROOT."include/http.cls.php";

/* 之前状态
			'1'   => '发送成功',
			'101' => '验证失败！',
			'102' => '短信不足！',
			'103' => '操作失败！',
			'104' => '非法字符！',
			'105' => '内容过多！',
			'106' => '号码过多！',
			'107' => '频率过快！',
			'108' => '号码内容空',
			'109' => '账号冻结！', 
			'110' => '禁止频繁单条发送！',
			'111' => '系统暂定发送！',
			'112' => '号码错误！',
			'113' => '定时时间格式不对！',
			'114' => '账号被锁，10分钟后登录！',
			'115' => '连接失败！',
			'116' => '禁止接口发送！',
			'117' => '绑定IP不正确！',
			'120' => '系统升级！',
*/

?>