<?php

/**
 * 仅测试使用，用于测试系统其它流程；
 * 具体操作不会发短信，仅写一个文件记录表示发短信
 */
class sms_0test{
	
	// 序列号
	var $userid;
	// 密码
	var $userpw;
	// 余额文件
	var $bfile;
	// 与其它接口保持一致,避免一个警告
	var $baseurl;

	// 第3,4,5个参数不用
	function sms_0test($userid,$userpw,$extra_par3='',$extra_par4='',$extra_par5='')
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
		$path = M_ROOT."dynamic/sms"; 
		$file = "$path/0test_balance.txt";
		if(!is_file($file)){
			mmkdir($path,0);
			$fp = fopen($file, 'wb');
			$fee = rand(50,100);
			flock($fp, 2); fwrite($fp, $fee); fclose($fp);
		}
		$this->bfile = $file;
	}
	
	/**
	 * 余额查询  (注:此方法必须为已登录状态下方可操作)
	 * @return double 余额
	 */
	function getBalance()
	{
		$rnd = rand(1,1000);
		if($rnd<998){ // 模拟,99.8%情况下成功
			$cnt = file_get_contents($this->bfile);
			return array(1,$cnt);
		}else{
			return array(-1,'失败!');
		}	
	}
	
	// 充值
	function chargeUp($count)
	{
		$rnd = rand(1,1000);
		if($rnd<900){ // 模拟,90%情况下成功
			$cnt = file_get_contents($this->bfile);
			$cnt += $count;
			$fp = fopen($this->bfile, 'wb');
			flock($fp, 2); fwrite($fp, $cnt); fclose($fp);
			return array(1,$cnt);
		}else{
			return array(-1,'失败!');
		}	
	}
	
	// 扣费
	function deductingCharge($count)
	{
			$cnt = file_get_contents($this->bfile);
			$cnt -= $count; 
			if((float)$cnt<0) $cnt = 0; 
			$fp = fopen($this->bfile, 'wb');
			flock($fp, 2); fwrite($fp, $cnt); fclose($fp);
			return array(1,$cnt);
	}

	/**
	 * 具体操作不会发短信，仅写一个文件记录表示发短信
	 */
	function sendSMS($mobiles,$content)
	{
		$rnd = rand(1,1000);
		if($rnd<900){ // 模拟,90%情况下成功
			// 已使用db记录,这里不要.txt文本记录了
			// 扣钱 test_balance.txt
			return array(1,"OK!");
		}else{
			return array(-1,'失败!');
		}
	}

}

// 加载本类特有的class
//include_once M_ROOT.'include/general.inc.php';

// 附加说明
// none

?>
