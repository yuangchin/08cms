<?php
!defined('M_COM') && exit('No Permission');

// 
class cron_baidu_mob_push extends cron_exec{
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){ 
	
		$file = M_ROOT.'baidu_mob_push.xml';
		if(!is_file($file)){ 
			return $this->logger('xml文件不存在！'); 
		}
		$data = file(M_ROOT.'baidu_mob_push.xml');
		$updtime = filemtime($file);
		$urls = array(); 
		
		//读取xml
		if(!function_exists('simplexml_load_string') || !function_exists('curl_init')){
			return $this->logger('simplexml_load_string或curl_init函数不可用,请设置php.ini'); 	
		}
		$xml = simplexml_load_string(file_get_contents($file));
		if(empty($xml->url)){ // $array['url']
			return $this->logger('没有最新内容'); 
		}
		foreach($xml->url as $v){
			$urls[] = $v;
		} //echo "<pre>"; print_r($urls); die();
		
		$cms_abs = cls_env::mconfig('cms_abs'); 
		$api = cls_env::mconfig('baidu_push_api');
		if(empty($api)){
			return $this->logger('api地址未填写'); 
		}
		
		$ch = curl_init();
		$options =  array(
				CURLOPT_URL => $api,
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => implode("\n", $urls),
				CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
		);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);

		$message = json_decode($result);
		if(!empty($message->message)){
			return $this->logger($message->message);
		}elseif(isset($message->success)){
			// 注意:生成xml文件时,已经确保有这个参数,现在只更新即可；
			// 注意更新时间为生成文件的时间。
			$this->db->query("UPDATE {$this->tblprefix}mconfigs SET value='$updtime' WHERE varname='push_time'"); 
			cls_SitemapPage::Create(array('map' => 'baidu_mob_push','inStatic' => true)); //相当于清空旧资料
			return $this->logger('成功推送');
		}
		
	}
	// 记录或显示
	public function logger($msg=''){ 
		if(in_array(cls_env::GetG('action'),array('runTest','sitemapsedit'))){
			echo '提示信息:'; print_r($msg);
			@adminlog('baidu主动推送',$msg);
		}else{
			defined('M_ADMIN') && @adminlog('baidu主动推送',$msg);
		}
	}
}
