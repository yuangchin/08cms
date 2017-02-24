<?php
// 菜单管理
// 随微信规则更新

class cls_wmpMenu extends cls_wmpBasic{

    protected $menuUrl = 'https://api.weixin.qq.com/cgi-bin/menu/%s?access_token=%s';
	protected $oauth = NULL;
	
	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}
	
    // 查询当前使用的自定义菜单结构
    public function menuGet(){
        $url = sprintf($this->menuUrl, 'get', $this->actoken);
		$data = cls_w08Basic::getResource($url,3);
		return cls_w08Basic::jsonDecode($data,$this->menuUrl);
    }
    
	// 创建菜单
    public function menuCreate($mcfg=array()){
		$mcfg['button'] = $mcfg;
		$url = sprintf($this->menuUrl, 'create', $this->actoken); //print_r($mcfg); echo "\n\n\n\n\n";
		$paras = cls_w08Basic::jsonEncode($mcfg); //print_r($mcfg); die();
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->menuUrl);
    }    
	
    // 删除当前使用的自定义菜单
    public function menuDelete(){
        $url = sprintf($this->menuUrl, 'delete', $this->actoken);
		$data = cls_w08Basic::getResource($url,3);
		return cls_w08Basic::jsonDecode($data,$this->menuUrl);
    }
	
	/*
	
    /**
     * 长链接转短链接接口
     *-/
    function shortUrl($longurl){
		$url = $this->short_url."{$this->actoken}";
		//$longurl = str_replace(array('&','#'),array('%26','%23'),$longurl); //urlencode($longurl);
		$paras = "{\"action\":\"long2short\",\"long_url\":\"$longurl\"}"; 
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); //echo $data;
		return cls_w08Basic::jsonDecode($data,$this->short_url);
    }
	
	*/
	
}
