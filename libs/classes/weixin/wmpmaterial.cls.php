<?php
// 素材管理接口
// 随微信规则更新 7000009:请求语义服务失败 

class cls_wmpMaterial extends cls_wmpBasic{
	
	private $tupUrl = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s';
	private $tgetUrl = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s';
	
	private $mnewsUrl = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=%s';
	private $mnimgUrl = 'https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=%s';
	private $maddUrl = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=%s';
	
	private $mlistUrl = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=%s'; //这个接口每天调用上限只有10此左右
	private $mdelUrl = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=%s';
	
	private $loadUrl = 'https://api.weixin.qq.com/semantic/semproxy/search';
	
	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}
   // 新增临时素材
    public function tmpUpload($path,$type='image'){
		$url = sprintf($this->tupUrl,$this->actoken,$type);
		$paras['file'] = '@'.cls_w08Material::getLocal($path); 
		echo $paras['file']; //'@E:/webs/08tools/yscode/uimgs/logo/gezi1-40x.jpg';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->tupUrl);
    }
	// 获取临时素材
    public function tmpGet($media_id){
		$url = sprintf($this->tgetUrl,$this->actoken,$media_id);
		return $url;
	}
	
	// 新增其他类型永久素材
    public function matAdd($path){
		$url = sprintf($this->maddUrl,$this->actoken);
		$paras['media'] = '@'.cls_w08Material::getLocal($path);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->maddUrl);
	}
	
	// 新增永久图文素材
	// articles:包含:$title,$medid,$content,$url
	// 
    public function matNews($articles,$author='08cms',$digest=0,$cpic=0){
		$url = sprintf($this->mnewsUrl,$this->actoken);
		$paras['articles'] = array();
		foreach($articles as $k=>$v){
			$paras['articles'][] = array(
				"title" => $v['title'],
				"thumb_media_id" => $v['medid'],
				"author" => $author,
				"digest" => $k==$digest ? 1 : 0,
				"show_cover_pic" => $k==$cpic ? 1 : 0,
				"content" => $v['content'],
				"content_source_url" => $v['url'],
			);	
		}
		$paras = cls_w08Basic::jsonEncode($paras);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->mnewsUrl);
	}
    
	// 获取素材列表
	// $type = 图片（image）、视频（video）、语音 （voice）、图文（news）
    public function mgetList($type,$offset=0,$count=20){
		$url = sprintf($this->mlistUrl,$this->actoken);
		$paras = "{\"type\":\"$type\",\"offset\":$offset,\"count\":$count}";
		//$paras['media'] = '@'.cls_w08Material::getLocal($path);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); //echo $data;
		return cls_w08Basic::jsonDecode($data,$this->mlistUrl);
	}
	
	// 删除永久素材
    public function mdelMedia($media_id){
		$url = sprintf($this->mdelUrl,$this->actoken);
		$paras = "{\"media_id\":\"$media_id\"}";
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->mdelUrl);
	}
	
    /** 获取媒体（具体保存媒体由w08相关代码实现）
     */
    public function loadMedia($mediaid){
		if(strpos($mediaid,'://')){ //文件url格式
			$url = $mediaid;	
			//最近更新，永久图片素材新增后，将带有URL返回给开发者，开发者可以在腾讯系域名内使用（腾讯系域名外使用，图片将被屏蔽）。
		}else{
			$url = sprintf($this->loadUrl, $this->actoken, $mediaid);
		}
		$media = _08_Http_Request::getResources($url);
		return $media; //失败为NULL
    }

}
