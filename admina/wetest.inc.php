<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();

//权限
if($re = $curuser->NoBackFunc('weixin')) cls_message::show($re);
$action = empty($action) ? 'bugmain' : $action;

$aid = empty($aid) ? 0 : intval($aid);
$mid = empty($mid) ? 0 : intval($mid);
//$tab = isset($tab) ? $tab : 'bugmain';
if(!empty($mid)){
	$key = $mid;
	$type = 'mid';
}elseif(!empty($aid)){
	$key = $aid;
	$type = 'aid';
}else{
	$key = 0;
	$type = 'sid';
} //公众号配置
$wecfg = cls_w08Basic::getConfig($key, $type); 
$wemenu = cls_cache::exRead('wxconfgs');
$weuapi = cls_w08Basic::getWeixinURL($type,$key).'/';
$envGP = cls_env::_GET_POST(); $debug = @$envGP['debug']; //print_r($debug);

//默认值...
$dcfg = array('api','appid','token','appsecret','orgid','openid');
foreach($dcfg as $k){
	$$k = empty($debug[$k]) ? (empty($wecfg[$k]) ? '' : $wecfg[$k]) : $debug[$k];
}
$api || $api = $weuapi;
$orgid || $orgid = 'gh_'.cls_string::Random(12);
$openid || $openid = 'open_'.cls_string::Random(24); //tlixahY2kgCVm9u2tuNgvcI

backnav('weixin',$action);
echo "\n<script>\n"; include(_08_INCLUDE_DIR.'/js/weixin.js'); echo "\n</script>\n";

if($action=='bugmain'){

	$debug['type'] = empty($debug['type']) ? 'Location' : $debug['type'];
	  
	tabheader("调试中心 (for : type=$type, key=$key)",'debugtable',"?entry=$entry&aid=$aid&mid=$mid&action=$action",2,1,1);
	
	trbasic('接口地址','debug[api]', $api, 'text', array('w' => 120,));
	trbasic('AppId *','debug[appid]', $appid, 'text', array('w' => 80,));
	trbasic('Token *','debug[token]', $token, 'text', array('w' => 80,));
	trbasic('AppSecret *','debug[appsecret]', $appsecret, 'text', array('w' => 80,));

	$acta = array(	
		'Signurl'=>'测试接入', //(取消关注) ($wecfg=array())
		'Subscribe'=>'关注', //(取消关注)
		'Send'=>'发信息',
		'qrGet'=>'获取二维码',
		'qrPush'=>'推送二维码',
		'Click'=>'Click相关',
		'Location'=>'地理位置', // 113.740003,23.008843,17   23.018224, 113.760718 (x:0.03, 0.01)
		'oaLink'=>'授权链接(会员中心)',
		'Spic'=>'发图片(传图使用)',
	);
	$acts = '';  
	foreach($acta as $k=>$v){
		if($k=='oaLink') $acts .= "<br>";
		$acts .= "<label><input class=\"radio\" type=\"radio\" name=\"debug[type]\" value=\"$k\" onclick=\"wxSetDebugType('$k')\"".($debug['type'] == $k ? ' checked="checked"' : '').">$v</label> &nbsp; ";
	}
	$long = 113.700003+mt_rand(10000,60000)/1000000; 
	$lati = 22.9951234+mt_rand(1000,18000)/1000000; 
	$prec = mt_rand(500000,3000000)/10000;
	$pick = "<input class='btnmap' type='button' onmouseover='this.onfocus()' onfocus=\"_08cms.map.setButton(this,'marker','debug[sitemap]','','19','$lati,$long');\" /> <label class='maplab' for='debug[sitemap]'>纬度,经度：</label>
	<input class='maptxt' type='text' id='debug[sitemap]' name='debug[sitemap]' value='$lati,$long'>";

	trbasic('调试类型','',"$acts",''); //http://mp.weixin.qq.com/wiki/home/index.html
	trbasic("<!--,Sub,qrP,Cli,-->事件Key",'debug[key]',empty($debug['key']) ? 'KEY_'.strtoupper(cls_string::Random(8)) : $debug['key'],'text',array('w'=>80,'guide'=>'1.关注一般不需要KEY,扫描带参数二维码事件KEY类似:qrscene_123123; <br>2.点菜单事件请填写菜单中对应Click的KEY; <br>3. 推送二维码请填写二维码的场景值;'));
	trbasic("<!--,qrG,-->事件Key",'debug[qrmod]',empty($debug['qrmod']) ? 'login' : $debug['qrmod'],'text',array('w'=>80,'guide'=>'如：login, getpw, scansend, scanupload 等系统能处理的扫描[模块];'));
	trbasic("<!--,qrG,-->附加参数",'debug[extp]',empty($debug['extp']) ? date('H').'_'.date('mdis') : $debug['extp'],'text',array('w'=>80,'guide'=>'如：4_7654321, cuid_1234567, mchid_2323 等系统能处理的扫描[模块];'));
	trbasic("<!--,Sen,-->信息内容",'debug[detial]','你好！微信测试！'.date('Y-m-d H:i:s'),'textarea',array('w'=>420,'h'=>80,));
	trbasic("<!--,Loc,-->Precision",'debug[prec]',"$prec",'text',array('w'=>80,));
	trbasic("<!--,Loc,-->坐标位置",'',$pick,'',array('w'=>80,));
	trbasic("<!--,Loc,-->自动/主动",'debug[loctype]',empty($debug['loctype']) ? 'auto' : $debug['loctype'],'text',array('w'=>80,'guide'=>'auto/send'));
	//<!--,Spic,-->
	trbasic("<!--,Spi,-->PicUrl",'debug[PicUrl]',@$debug['PicUrl'],'text',array('w'=>80,));
	trbasic("<!--,Spi,-->MediaId",'debug[MediaId]',@$debug['MediaId'],'text',array('w'=>80,));
/*	
[User Message]=</b>(_08_M_Weixin_Event::Scan-27a
[ToUserName]=(gh_a94178b33562)
[FromUserName]=(oA1n9tl_fnXi8ouleydL0hkvVBwI)
[CreateTime]=(1437721595)
[MsgType]=(image)
[PicUrl]=(http://mmbiz.qpic.cn/mmbiz/kCd8LZdoPibUpCj1IhZLdtNmGTHI8UN6etJATibgCJxTQhTo0ZOBkc5AslmQbC4u7AbtgqUyYIU3xvLTqDyIAgtw/0)
[MsgId]=(6174967231493613929)
[MediaId]=(fp60ATiIUa80QCr73PZsDfPFw2khbpf641m_d62g19aqwhvLPx_FjNQNWa9OKZyw)
*/	
	trhidden('action',$action);
	trbasic('OrgId *','debug[orgid]', $orgid, 'text', array('w' => 80,));
	trbasic('OpenID *','debug[openid]', $openid, 'text', array('w' => 80,));
	tabfooter('bsubmit'); 
	echo "\r\n<script type='text/javascript'>wxSetDebugType('{$debug['type']}',1);</script>";
	
	echo "<table width='100%'><tr><td class='txtL'><hr>调试结果："; //print_r($debug);
	if(submitcheck('bsubmit')){
		$data = "<ToUserName><![CDATA[$orgid]]></ToUserName>";
		$data .= "<FromUserName><![CDATA[$openid]]></FromUserName>";
		$data .= "<CreateTime>".time()."</CreateTime>";
		if($debug['type']=='Signurl'){
			//$wecfg['token'] = $token; //保证token一致...
			$signurl = cls_w08Tester::getSignurl($debug, $api);
			echo "\n<br>接入链接: <a href='$signurl' target='_blank'>".cls_string::CutStr($signurl,56)."".substr($signurl,-12,12)."</a>";
			echo "\n<br>接入Url: $signurl";
		}
		if($debug['type']=='Subscribe'){
			$data .= "<MsgType><![CDATA[event]]></MsgType>";
			$data .= "<Event><![CDATA[".strtolower($debug['type'])."]]></Event>";
			if($debug['key'] && strstr($debug['key'],'qrscene_')){
				$data .= "<EventKey><![CDATA[{$debug['key']}]]></EventKey>";
			}
		}
		if($debug['type']=='Send'){
			$data .= "<MsgType><![CDATA[text]]></MsgType>";
			$detial = cls_w08Basic::iconv(cls_w08Basic::getConfig('mcharset','baseinc'),'utf-8',$debug['detial']);
			$data .= "<Content><![CDATA[$detial]]></Content>";
		}
		if($debug['type']=='qrGet'){
			$wxqr = new cls_w08Qrcode($wecfg); 
			$tmp = $wxqr->getQrcode($debug['qrmod'], 'limit', $debug['extp']); 
			echo "\n<br>场景ID: {$tmp['sid']} ; ticket: {$tmp['ticket']} ";
			echo "\n<br>二维码Url: {$tmp['url']}"; 
			echo "\n<br>二维码链接: <a href='{$tmp['url']}' target='_blank'><img src='{$tmp['url']}' width='230'></a>";
		}
		if($debug['type']=='qrPush'){
			$data .= "<MsgType><![CDATA[event]]></MsgType>";
			$data .= "<Event><![CDATA[SCAN]]></Event>";
			$data .= "<EventKey><![CDATA[".$debug['key']."]]></EventKey>";
			$data .= "<Ticket><![CDATA[Ticket_".cls_string::Random(24)."]]></Ticket>";
		}
		if($debug['type']=='Click'){
			$data .= "<MsgType><![CDATA[event]]></MsgType>";
			$data .= "<Event><![CDATA[CLICK]]></Event>";
			$data .= "<EventKey><![CDATA[{$debug['key']}]]></EventKey>";
		}
		if($debug['type']=='Location'){
			$smap = explode(',',$debug['sitemap']); 
			if($debug['loctype']=='send'){
				$data .= "<MsgType><![CDATA[location]]></MsgType>";
				$data .= "<Location_X>".$smap[0]."</Location_X>";
				$data .= "<Location_Y>".$smap[1]."</Location_Y>";
				$data .= "<Scale>".mt_rand(5,15)."</Scale>";
				$data .= "<Label><![CDATA[SomePlace]]></Label>";
				//$data .= "<MsgId>1234567890123456</MsgId>	";
			}else{
				$data .= "<MsgType><![CDATA[event]]></MsgType>";
				$data .= "<Event><![CDATA[LOCATION]]></Event>";
				$data .= "<Latitude>".$smap[0]."</Latitude>";
				$data .= "<Longitude>".$smap[1]."</Longitude>";
				$data .= "<Precision>{$debug['prec']}</Precision>";
			}
		}
		if($debug['type']=='oaLink'){ 
			$url = "{mobileurl}wxlogin.php?oauth=snsapi_base&state=mlogin"; //&_tm=".time()."
			$wesid = cls_w08Basic::getConfig();
			$wem = new cls_w08MenuBase($wesid);
			$url = $wem->fmtUrl($url);
			echo "\n<br>授权链接Url: <input name='' type='text' value='{$url}' style='width:100%'>"; 
			echo "\n<br>Html链接: <a href='{$url}' target='_blank'>Html链接</a> 点这里不能打开，请复制到手机打开。";
		}
		if($debug['type']=='Spic'){ 
			$data .= "<MsgType><![CDATA[image]]></MsgType>";
			$data .= "<PicUrl><![CDATA[$debug[PicUrl]]]></PicUrl>";
			$data .= "<MediaId><![CDATA[$debug[MediaId]]]></MediaId>";
/*	$detial = cls_w08Basic::iconv(cls_w08Basic::getConfig('mcharset','baseinc'),'utf-8',$debug['detial']);
[User Message]=</b>(_08_M_Weixin_Event::Scan-27a
[ToUserName]=(gh_a94178b33562)
[FromUserName]=(oA1n9tl_fnXi8ouleydL0hkvVBwI)
[CreateTime]=(1437721595)
[MsgType]=(image)
[PicUrl]=(http://mmbiz.qpic.cn/mmbiz/kCd8LZdoPibUpCj1IhZLdtNmGTHI8UN6etJATibgCJxTQhTo0ZOBkc5AslmQbC4u7AbtgqUyYIU3xvLTqDyIAgtw/0)
[MsgId]=(6174967231493613929)
[MediaId]=(fp60ATiIUa80QCr73PZsDfPFw2khbpf641m_d62g19aqwhvLPx_FjNQNWa9OKZyw)
*/
		}
		
		$data = "<xml>$data</xml>";
		if(in_array($debug['type'],array('Subscribe','Send','qrPush','Click','Location','Spic',))){
			$dstr = cls_w08Tester::showInfo($data); 
			echo "提交的数据：<pre>$dstr";
			$data = cls_w08Basic::getResource(array(
				'urls' => $api,
				'timeOut' => 3,
				'method' => 'POST',
				'postData' => $data,
			)); //print_r($debug);
			echo "<hr>返回的结果：";
			echo empty($data) ? 'NULL' : cls_w08Tester::showInfo($data);
			//echo "提示：由于xml中的文字为utf8，如果系统为gbk版，则显示的xml中有乱码算正常现象";
			echo "</pre>";
		}
	}
	echo "</td><tr>\n</table>";


}elseif($action=='tmptest'){
	
	$post = cls_w08Tester::getPost();
	$post->Event = 'CLICK';
	$post->EventKey = 'MENU_PUSH_push_144';
	print_r($post);
	
	new cls_w08Event($post,$wecfg); //cls_w08Basic::getConfig('wx3b915d8db305b742','appid');
	
	die();
	echo "<br>".dechex(6677);
	echo "<br>".dechex(7173);
	echo "<br>".dechex(13780);
	echo "<br>";
	
	/*
	$wecfg = cls_w08Basic::getConfig(); 
	//$url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=%&media_id=%";
	//$url = sprintf($url, $wecfg['actoken'], '6174967231493613929');
	$url = "http://mp.weixin.qq.com/debug/zh_CN/htmledition/images/bg/bg_logo1f2fc8.pngx";
	$url = "http://mmbiz.qpic.cn/mmbiz/kCd8LZdoPibUpCj1IhZLdtNmGTHI8UN6etJATibgCJxTQhTo0ZOBkc5AslmQbC4u7AbtgqUyYIU3xvLTqDyIAgtw/0";
	$media = _08_Http_Request::getResources($url); var_dump($media);
	file_put_contents("./aaa.jpg", $media); die($media);
	return $qrcode;
	//*/

	$openid = 'oyDK8vjjcn3cFbxMLaMBhKEsYbCk';
	$mname08 = 'jp000';
	$password = 'jp000';
	$mchid = 1;
	
	$re = cls_w08User::bindUser($openid,$mname08,$password);
	print_r($re);
	die();

	$re = cls_w08User::addUser($openid,$mname08,$password,$mchid);
	print_r($re);
	die();

	$wecfg = cls_w08Basic::getConfig('wx3b915d8db305b742','appid'); //
	
	$wem = new cls_wmpMenu($wecfg);
	$re = $wem->menuGet();
	echo $re.'<pre>';
	print_r($re);
	echo $re.'<br>';
	
	// snsapi_base, snsapi_userinfo
	$url = "{mobileurl}wxlogin.php?test=3&oauth=snsapi_base&state=getpw";
	$wem = new cls_w08MenuBase($wecfg);
	$url = $wem->fmtUrl($url);
	echo $url.'<br>';
	

	echo ucfirst('abc').'<br>';
	echo ucfirst('ABC').'<br>';
	echo ucfirst('AbC').'<br>';
	$wxqr = new cls_w08Qrcode($wecfg);
	//echo cls_w08EventBase::getSceneID('login', 'test');
	$tmp = $wxqr->getQrcode('sendaid_7654321', 'temp', 'test'); print_r($tmp); echo "\n<br>\n";
	$lmt = $wxqr->getQrcode('login', 'limit', 'test'); print_r($lmt);
	
	//93964	 	login	 	好好2	gQEM8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0dVV1ViRWJsVWpzcmIxckRqV2tNAAIENqUtVgMEAAAAAA==
	$wx2 = new cls_wmpQrcode($wecfg); echo "<pre>";
	$lmt = $wx2->qrcodeTicket(93964, 'fnum'); print_r($lmt); 
	$lmt = $wx2->qrcodeTicket(19927, 'fnum'); print_r($lmt);
	//qrcodeTicket($sid,$type='temp',$exp=86400)
	

}elseif($action=='xxx'){
	

}elseif($action=='xxx'){

	
}elseif($action=='xxx' && $tab=='msgget'){ echo 'xx';


}elseif($action=='xxx'){
	
	echo '完善中…';
	
} //echo "$action=='message' && $tab=='msgget'";

?>
