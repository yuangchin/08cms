<?php
/**
 * 
*** 以下问题, 请与短信供应上联络：
-1- (和平鸽)你们那个接口，延时厉害啊！(前天我在你们网站测试时，是很快的。)
--- 创瑞短信公司的客户经理 您现在不是免审,早上这一块发短信的人又多。延时肯定的会有的。
#2# 当发送含有一些关键字时；
### 当时显示发送成功，其实没有发送成功；
### 进cr6868.com网站后台可发现有“驳回”提示；
### 但我们系统已经是按当时状态(成功)的来处理，这个是个问题。
 * 
 * sms_cr6868；
 */
class sms_cr6868{
	
	// 序列号
	var $userid;
	// 密码
	var $userpw;
	// base path
	var $baseurl = 'http://web.cr6868.com/asmx/smsservice.aspx';
	// post对象
	var $post;

	// 第3,4,5个参数不用
	function __construct($userid,$userpw,$extra_par3='',$extra_par4='',$extra_par5=''){ 
	//{
		$this->userid = $userid;
		$this->userpw = $userpw;
		$this->baseurl = $this->baseurl."?name=$this->userid&pwd=$this->userpw";
		$this->getInit();
	}
	
	/**
	 * http 初始化
	 */
	function getInit()
	{
		$this->post = new http(); 
		$this->post->setCookies(60);
	}
	
	/**
	 * sms_cr6868；
	 * 发送内容（1-500 个汉字）UTF-8编码
	 * http://web.cr6868.com/asmx/smsservice.aspx?name=13537432147&pwd=xxx&content=test测试msg[和平鸽]&mobile=13537432147&type=pt
	 */
	function sendSMS($mobiles,$content)
	{
		$this->post->timeout = $this->timeout; 
		if(is_array($mobiles)) $mobiles = implode(',',$mobiles);
		
		$content = str_replace(array(' ','　',"\r","\n","&","#"),'',$content); // 短信内容不支持空格???
        $content = str_replace(array('[',']'),array('【','】'),$content); // 具体咨询短信供应商
        
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        $content = cls_string::iconv($mcharset,"utf8",$content);
		//$content = urlencode($content); //不能用这个函数
        
		$path = "&type=pt&content=$content&mobile=$mobiles";
		$html = $this->post->fetchtext("$this->baseurl$path",'POST'); //print_r("r:($html)"); die();
        //$html = '0, 20130821110353234137876543,0,500,0,提交成功';
		$re = $this->fmtInfo($html); //echo 'xxx'; print_r($html); die('yy'); //."$this->baseurl$path"
		return $this->getReInfo($re[0]);
	}
	
	/**
	 * 余额查询 
	 * @return double 余额
	 */
	function getBalance()
	{
		$this->post->timeout = $this->timeout; 
		$path = "&type=balance";
		$html = $this->post->fetchtext("$this->baseurl$path",'GET');
		$re = $this->fmtInfo($html); 
		if($re[0]==='0' && is_numeric($re[1])){
            return array(1,$re[1]);
		}else{
            $msg = $this->getReInfo($re[0]);
			return array('-1',0,'msg'=>$msg[1]);
		}
	}
	
	function fmtInfo(&$info)
	{
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$info = cls_string::iconv("utf8",$mcharset,$info);
		$a = explode(',',$info);
		if(count($a)>=2){
			return array($a[0],$a[1]);
		}
		return array('-2','其它错误！');
	}

	/**
	 * 返回值-描述 对应表
	 */
	function getReInfo($no)
	{
		$nobak = $no; //
        $conv = array(
			'0'  => '1', 
			'1'  => '9',
		);
		if(isset($conv[$no])) $no = $conv[$no];
		// 接口0为成功,1为含有敏感词汇; 本系统api规范为1为成功
		$a = array(
			'1'  => '操作成功', //0
			'9'  => '含有敏感词汇！', //1
			'2'  => '余额不足',
			'3'  => '没有号码',
			'4'  => '包含sql语句',
			'10' => '账号不存在',
			'11' => '账号注销',
			'12' => '账号停用',
			'13' => 'IP鉴权失败',
			'14' => '格式错误',
			'-1' => '系统异常',
			'-2' => '其它错误！',
		);	//echo $no;
		$msg = isset($a[$no]) ? $a[$no] : '(未知错误)';
		return array($no,"{$msg}".($no==1 ? '' : "[error:$nobak]"));
	}

}

// 加载本类特有的class
include_once M_ROOT."include/http.cls.php";


?>