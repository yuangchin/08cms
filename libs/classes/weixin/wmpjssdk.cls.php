<?php
class cls_wmpJssdk extends cls_wmpBasic{

	private $cachePath = 'weixin/jsapi_ticket.json';
	private $cacheFull = '';

	public function __construct($cfg=array()){
		parent::__construct($cfg); 
		$this->cacheInit();
	}

	public function getSignPackage() {
		$jsapiTicket = $this->getJsApiTicket();
		// 注意 URL 一定要动态获取，不能 hardcode.
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		$signature = sha1($string);
		$signPackage = array(
			"appId"     => $this->cfg['appid'],
			"nonceStr"  => $nonceStr,
			"timestamp" => $timestamp,
			"url"       => $url,
			"signature" => $signature,
			"rawString" => $string
		);
		return $signPackage; 
	}

	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	private function getJsApiTicket() {
	// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
	$data = json_decode(file_get_contents($this->cacheFull));
	if ($data->expire_time < time()) {
		$accessToken = $this->getAccessToken();
		// 如果是企业号用以下 URL 获取 ticket
		// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
		$res = cls_w08Basic::getResource($url,3);
		$res = json_decode($res);
		$ticket = $res->ticket; 
		if ($ticket) {
			$data->expire_time = time() + 7000;
			$data->jsapi_ticket = $ticket;
			$this->cacheSave($data);
		}
	}else{
		$ticket = $data->jsapi_ticket;
	} //echo $ticket;
	return $ticket;
	}

	private function cacheInit() {
		$this->cacheFull = _08_CACHE_PATH.$this->cachePath; 
		if(!is_file($this->cacheFull)){
			mmkdir($this->cacheFull,1,1); 
			$this->cacheSave(array('jsapi_ticket'=>'','expire_time'=>''));
		}
		//die($this->cacheFull);
	}
  
	private function cacheSave($data) {
		$fp = fopen($this->cacheFull, "w");
		fwrite($fp, json_encode($data));
		fclose($fp);
	}

  /*private function getAccessToken(){}*/
  /*private function httpGet($url){}*/
  
}

