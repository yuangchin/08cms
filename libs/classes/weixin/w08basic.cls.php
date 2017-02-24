<?php
// 对接08cms基本函数和配置
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08Basic{
	
    static $cfgs = array();
	static $toks = array();
	static $now_url = ''; //当前url，用于调试
   
	// getWeixinURL
	static function getWeixinURL($type='sid',$key=0){
		//http://x1.08cms.com/auto/index.php?/weixin/init/mid/539/
		if($type=='sid'){
			$para = "weixin=";
		}elseif($type=='aid'){
			$para = "weixin=init&aid=".($key ? $key : '{aid}');	
		}elseif($type=='mid'){
			$para = "weixin=init&mid=".($key ? $key : '{mid}');	
		}else{
			$para = "weixin=";	
		}
		return cls_env::mconfig('cms_abs')._08_Http_Request::uri2MVC($para);
	}
	
	//替换地址:{aid},{mid},{cms_abs},{mobileurl}
	static function fmtUrl($url,$wecfg=array()){ 
		$from = array('{mobileurl}','{cms_abs}',);
		$to = array(cls_env::mconfig('mobileurl'),cls_env::mconfig('cms_abs'));
		$url = str_replace($from, $to, $url);
		foreach(array('mid','aid') as $key){
			if(strpos($url,'{'.$key.'}') && !empty($wecfg['fromid']) && $wecfg['fromid_type']==$key){
				$url = str_replace('{'.$key.'}', $wecfg['fromid'], $url);
			}
		}
		return $url;
	}
   
	// 保存位置信息
	static function posSave($post=NULL,$cfg=array()){
		$data['type'] = empty($data['Scale']) ? 'Auto' : 'Send';
		$data['latitude'] = floatval($post->Latitude);# 地理位置纬度
		$data['longitude'] = floatval($post->Longitude);# 地理位置经度
		$data['exter'] = empty($data['Scale']) ? $data['Precision'] : $data['Scale'];
		$data['ctime'] = $post->CreateTime;
		$data['appid'] = $cfg['appid'];
		$data['openid'] = (string) $post->FromUserName; 
		//save;
	}
	// 获取位置信息
	static function posGet($actoken=''){
		$url = $this->reAuto."{$actoken}";
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'GET',
			'postData' => '',
		)); 
		return cls_w08Basic::jsonDecode($data,$this->reAuto);
	}

	static function debugError($msg='',$arr=array(),$url=''){
		if(is_array($arr) && !empty($arr['errcode'])){
			$msg = cls_wmpError::errGet($arr['errcode']);
			if(strpos($msg,'(unKnow)') && !empty($arr['errmsg'])){
				$msg = '['.$arr['errcode'].']'.$arr['errmsg'];
			}
			$msg = "$url<br>$msg";
		}elseif(is_object($arr) || is_array($arr)){ 
			$msg .= "$url<br>".var_export($arr,1); 
		}else{
			$msg .= "$url<br>".$arr;
		} 
		$msg && $msg = "$msg<br>";
		$debug = cls_env::mconfig('weixin_debug');
		if(defined('WX_ERR_RETURN')){ 
			$arr['message'] = $msg;
			$arr['url'] = $url;
			return $arr; 
		}else{ 
			if(defined('_08CMS_WEIXIN_CONTROLLER') && empty($debug)){
				die(''); //这个回复微信服务器
			}else{
				cls_message::show($msg); //这个给人看的
			}
		}
	}
	
    /**
     * 把数据转成JSON格式（让支持中文显示）
     * @param  mixed $datas         要转换的数据
     * @param  bool  $restoreCoding 还原编码，如果为TRUE时把数据还原回原来数据编码
     * @param  bool  $conversion    是否转换中文，TRUE为转换，FALSE为不转换，注：用JQuery的getJSON方法时请不要转换
     * @return mixed                返回转换后的JSON格式数据
     */
	static function jsonEncode($datas, $restoreCoding=false, $conversion = true){ 
		return _08_Documents_JSON::encode($datas, $restoreCoding, $conversion);
	}
	
	static function jsonDecode($data,$url=''){ 
		if(empty($data)) return self::debugError($url.'<br>[Remote]获取远程数据错误，请检查php扩展和服务器环境<br>','');
		$arr = json_decode($data,1); //print_r($data); 
		$arr = self::iconv('utf-8',self::getConfig('mcharset','baseinc'),$arr);
		if(!empty($arr['errcode'])){
			return self::debugError($arr['errcode'],$arr,$url);
		}else{
			return $arr;	
		}
		//转码：不是每个数据都要转换，需要转化的都到这里指定 
		//.... !empty($arr) && , && isset($obj['openid']) //nickname:用户信息; groups:分组信息 //menu 
		#if(isset($arr['nickname']) || isset($arr['groups']) || isset($arr['menu']) || isset($arr['is_add_friend_reply_open'])){
			
		#}//*/
	}
	
	// 这里主要处理缓存, 具体获取AccessToken有wmpBasic::getAccessToken负责
	// $type:get/save : 这里存数据库, 看是否考虑改为存文件？
	// -> 
    public static function accTokenCache($appid, $val='', $type='get'){
		if($type=='get' && isset(self::$toks[$appid])){ 
			return self::$toks[$appid];
		}elseif($type=='get'){ 
			$cfg = self::getConfig($appid,'appid'); //print_r($cfg);
			if(!empty($cfg['actoken']) && intval(@$cfg['acexp'])+$val>TIMESTAMP){
				return $cfg['actoken'];
			}else{
				return '';	
			}
		}else{
			self::$toks[$appid] = $val;
			$db = _08_factory::getDBO();
			$data = array(
				'weixin_actoken' => $val,
				'weixin_acexp' => TIMESTAMP,
			); 
			$db->update('#__weixin_config', $data)->where(array('weixin_appid'=>$appid))->exec();
			return $val;
		}
	}
	
	// 返回系统配置/参数：(说明)
	// -> $key=charset,$type='baseinc/mconfig'  -> 返回系统参数:baseinc,mconfig参数
	// -> $key=0,$type='sid'                    -> 返回总站公众号配置
	// -> $key=8888,$type='aid/mid',            -> 返回aid/mid=8888的公众号配置
	// -> $key=wx20b06b3c8d4e2a46,$type='appid' -> 返回appid=wx20b06b3c8d4e2a46的公众号配置
	// -> $key=gh_a94178b33562,$type='orgid'    -> 返回orgid=gh_a94178b33562的公众号配置
    static function getConfig($key='0', $type='sid'){
		$ckey = "$key-$type"; 
		if(isset(self::$cfgs[$ckey])){ 
			return self::$cfgs[$ckey]; 
		}
		if(in_array($type,array('baseinc','mconfig'))){
			$recfg = $type=='baseinc' ? cls_env::getBaseIncConfigs($key) : cls_env::mconfig($key);
		}else{ 
			$db = _08_factory::getDBO(); //$db->setDebug();
			$db->select()->from('#__weixin_config c');
			if(in_array($type,array('sid','aid','mid'))){ 
				//$db->where(array('weixin_fromid' => $key))->and(array('weixin_fromid_type'=> $type));
				$db->where(array('weixin_fromid' => $key, 'weixin_fromid_type'=> $type));
			}
			if(in_array($type,array('appid','orgid'))){ 
				$fkey = "weixin_$type";
				$db->where(array($fkey => $key));
			}
			/*if($type=='weixin_id'){
				$db->where(array('weixin_id' => $key));
			}*/
			$rearr = $db->exec()->fetch(); 
			if(!$rearr) return array(); //临时调试的号码,不在我们系统内,每次更新...,如果是文件缓存,则不会有这个问题
			$recfg = self::getConfigFmt($rearr);
		}
		self::$cfgs[$ckey] = $recfg;
		return $recfg;
	}
	static function getConfigFmt($rearr){
		$recfg = array();
		foreach($rearr as $k=>$v){
			$recfg[substr($k,7)] = $v; 
		}
		return $recfg;
	}
    /**
     * cURL并发获取资源，功能有点类似于多线程，但要注意这与多线程是不同的
     * @param  mixed $params  要获取资源的链接信息参数
     * @param  int   $timeOut 超时时间值
     * @param  bool  $getInfo 是否返回连接资源句柄信息，TRUE 返回，FALSE 不返回
     * @return array          返回获取到的资源链接
     * @example $contents = _08_Http_Request::getResources('http://www.baidu.com/', 1);
                $contents = _08_Http_Request::getResources(array('http://www.baidu.com/', 'http://www.google.com.hk/'), 1);
                // 该调用方法参数可以不对应，但urls必须存在
                // 未定义'method'时默认为 GET, postData 可以是GET或DELETE方法时的URL，也可以是POST时的数据
                // timeOut按urls对齐，如果未设置则自动使用getResources方法参数二的值
                $contents = _08_Http_Request::getResources(
                    array( 'urls' => array('http://www.baidu.com/', 'http://www.google.com.hk/'), 
                           'timeOut' => array(5),
                           'method' => 'POST',
                           'postData' => array('test' => 'postdatas') )
                );
     */
    static function getResource($params, $timeOut=5, $getInfo=false){
		return _08_Http_Request::getResources($params, $timeOut, $getInfo);
	}
	
	// 编码转换
    static function iconv($from,$to,$source){
        return cls_string::iconv($from,$to,$source);
    } 
	
}
