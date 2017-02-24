<?php
// 群发信息
// 随微信规则更新
// Peace注:测试号,没有成功发送... [40130]invalid openid list size, at least two openid hint: [UN9rpA0601age8]
// 除文本外消息外，其它接口需要相应的media_id

class cls_wmpMsgmass extends cls_wmpBasic{
	
	protected $msgSend = "https://api.weixin.qq.com/cgi-bin/message/mass/send";
	protected $msgSall = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall";
	protected $msgSurl = "";
	protected $upVideo = "https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo";
	
	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}	

    /**群发文本客服消息
     */
    public function sendText($content, $group_id=0){
		$content = cls_w08Basic::jsonEncode($content); 
		$filter = $this->getFilter($group_id);
		$url = $this->msgSurl."?access_token={$this->actoken}";
		$paras = '{'.$filter.',"msgtype":"text","text":{"content":'.$content.'}}';
		//echo $paras; //die();
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->msgSurl);
    }

    /**
     * 群发图片客服消息
     */
    public function sendImage($media_id, $group_id=0) {
		$filter = $this->getFilter($group_id);
		$url = $this->msgSurl."?access_token={$this->actoken}";
		$paras = '{'.$filter.',"msgtype":"image","image":{"media_id":'.$media_id.'}}';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->msgSurl);
    }

    /**
     * 群发语音客服消息
     */
    public function sendVoice($media_id, $group_id=0) {
		$filter = $this->getFilter($group_id);
		$url = $this->msgSurl."?access_token={$this->actoken}";
		$paras = '{'.$filter.',"msgtype":"voice","voice":{"media_id":'.$media_id.'}}';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->msgSurl);
    }

    /**
     * 群发视频客服消息
	 * 注意分两次getResource，详情请看官方文档
     */
    public function sendVideo($media_id, $group_id=0, $title='', $description='') {
		$url = $this->upVideo."?access_token={$this->actoken}";
		$msgup = array();
		$msgup['media_id'] = $media_id;
		$msgup['title'] = $title;
		$msgup['description'] = $description;
		$paras = cls_w08Basic::jsonEncode($message); //echo $paras;
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		$re = cls_w08Basic::jsonDecode($data,$this->upVideo);
		if(!empty($re['media_id'])){
			$filter = $this->getFilter($group_id);
			$url = $this->msgSurl."?access_token={$this->actoken}";
			$paras = '{'.$filter.',"msgtype":"mpvideo","mpvideo":{"media_id":'.$re['media_id'].'}}';
			$data = cls_w08Basic::getResource(array(
				'urls' => $url,
				'timeOut' => 3,
				'method' => 'POST',
				'postData' => $paras,
			)); 
			return cls_w08Basic::jsonDecode($data,$this->msgSurl);
		}
    }

    /**
     * 群发图文客服消息
     */
    public function sendNews($media_id, $group_id=0) {
		$filter = $this->getFilter($group_id);
		$url = $this->msgSurl."?access_token={$this->actoken}";
		$paras = '{'.$filter.',"msgtype":"mpnews","mpnews":{"media_id":'.$media_id.'}}';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->msgSurl);
    }
	
    /**
     * 群发卡券消息
     */
    public function sendCard($card_id, $group_id=0) {
		$filter = $this->getFilter($group_id);
		$url = $this->msgSurl."?access_token={$this->actoken}";
		$paras = '{'.$filter.',"msgtype":"wxcard","wxcard":{"card_id":'.$card_id.'}}';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->msgSurl);
    }
	
	// group_id: 0:  群发所有；
	//     非0数字:  按会员组群发；
	//     openid1,openid2…:   按openid群发【订阅号不可用，服务号认证后可用】
    public function getFilter($group_id=0){
		$re = '';
		$this->msgSurl = $this->msgSall;
		if(empty($group_id)){
			$re = '"filter":{"is_to_all":true}';
		}elseif(strstr($group_id,',')){
			$re = '"touser":["'.str_replace(',','","',$group_id).'"]';
			$this->msgSurl = $this->msgSend;
		}else{
			$re = '"filter":{"is_to_all":false,"group_id":"'.$group_id.'"}';
		}
		return $re;
	}

}
