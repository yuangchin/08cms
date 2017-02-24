<?php
// 语义理解接口
// 随微信规则更新 7000009:请求语义服务失败 

class cls_wmpSemantic extends cls_wmpBasic{
	
	private $yuyiUrl = 'https://api.weixin.qq.com/semantic/semproxy/search';
	
	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}
    
    /**发送文本客服消息
     *
     * @param $openid
     * @param $content
     *
     * @return bool|mixed
     */
    public function getResult($q, $city, $category, $uid){
		$url = $this->yuyiUrl."?access_token={$this->actoken}";
		$message = array();
		$message['q'] = $q;
		$message['city'] = $city;
		$message['category'] = $category;
		$message['uid'] = $uid;
		$paras = cls_w08Basic::jsonEncode($message);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->yuyiUrl);
    }

}
