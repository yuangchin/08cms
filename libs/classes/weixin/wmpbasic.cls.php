<?php
/**
 * Class cls_wmpBasic 基本接口，随微信规则更新
 *
 * 获取access token
 * 获取微信服务器IP地址
 */

class cls_wmpBasic{

	/**
	 * @var array 试号信息
	 * appID => '',appsecret => ''
	 */
	public $cfg = array();

	/**
	 * @var int access_token的有效期,目前为2个小时
	 */
	private $act_life = 5400; //秒(1.5h) (200/2000次/天)
	/**
	 * 获取access_token接口调用请求(网址)
	 * @var string
	 */
	private $act_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
	/**
	 * 获取微信服务器IP地址接口调用请求(网址)
	 * @var string
	 */
	private $ip_url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=%s';
	public $actoken = '';
	
	function __construct($cfg=array(),$cache=1){
		$this->cfg = $cfg;
		$this->actoken = $this->getAccessToken($cache);
	}
    
	// 从缓存取；没有的化,先更新获取,再存缓存
    function getAccessToken($cache=1){
		$appid = $this->cfg['appid']; 
		$appsecret = $this->cfg['appsecret'];
		if($cache && $accessToken=cls_w08Basic::accTokenCache($appid,$this->act_life,'get')){ 
			return $accessToken;
		}
		$url = sprintf($this->act_url,$appid,$appsecret); //echo $url;
		$data = cls_w08Basic::getResource($url,3); //print_r($data);
		//$data = '{"access_token":"ZolFhJs4NA3KUMOJE93MDbVffk4_YbgESj8tLYhmLW'.time().'","expires_in":7200}'; //模拟OK数据
		//$data = '{"errcode":40001,"errmsg":"invalid appsecret, view more at http://t.cn/RAEkdVq hint: [..Ze.A0384vr20]"}'; 
		//$data = ''; //错误测试
		$data = cls_w08Basic::jsonDecode($data,$this->act_url); 
		if(!empty($data['access_token'])){
			return cls_w08Basic::accTokenCache($appid,$data['access_token'],'save');
		}else{ // ???? 怎么出现这种情况？!
			return cls_w08Basic::debugError('获取access_token失败','',$this->act_url);
		}
	}
	
	static function checkSignature($wecfg=array()){
		$signature = @$_GET["signature"];
        $timestamp = @$_GET["timestamp"];
        $nonce = @$_GET["nonce"];
		$tmpArr = array($wecfg['token'], $timestamp, $nonce);
		sort($tmpArr, SORT_STRING); // use SORT_STRING rule
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
    /**
     * 验证消息真实性
     **/
    static function checkValid($wecfg=array()){        
        if(!self::checkSignature($wecfg)) return false;
		#if(!strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')) return false;
		//测试号:HTTP_USER_AGENT=Mozilla/4.0
		//ip判断 ??? 又要远程抓一次数据，不要了。
		return true;
		//return $f1 ? true : false;
		/*/签名验证
        if(isset($_GET["echostr"])){
           if(self::checkSignature()){
                echo $_GET["echostr"];
                exit;
           }           
           return false;
        }*/
    }
	
    /**
     * 获取微信服务器ip列表
     * @return json 返回json格式的ip列表
     */
    function getWeixinIP(){    
        $data = cls_w08Basic::getResource(sprintf($this->ip_url,$this->actoken),3); 
		return cls_w08Basic::jsonDecode($data,$this->ip_url); 
    }



}
