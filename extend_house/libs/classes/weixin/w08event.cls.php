<?php
// 事件响应操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08Event extends cls_w08EventBase{

	function __construct($post,$wecfg){
		parent::__construct($post,$wecfg); 
	}
	
	//*
    function reSubscribe(){  
        // 扫描带参数二维码事件: MsgType:event, Event:subscribe, EventKey:qrscene_123ABC, 
		// Ticket:TICKET(二维码的ticket，可用来换取二维码图片)
		//login/bind/getpw/???
		//sendaid_7654321
		//sendmid_1234567
		//uparc_3
		//upcu_4
		#if(!empty($this->eventKey) && is_string($this->eventKey)){ //qrscene_开头的时间ID
			//$t = var_export($this->eventKey,1);
			//$t2 - substr($this->eventKey,0,8);
			//die($this->remText("2ekey:$t."));
		if(substr($this->eventKey,0,8)=='qrscene_'){
			return $this->reScan(substr($this->eventKey,8));
		}else{ 
			return $this->reSubscribeBase();
		}
	}
	
    /*/ 取消关注事件
    function reUnsubscribe(){ 
		return $this->reUnsubscribeBase(1);
		die('');
	}*/
	
	// 用户已关注时,扫描带参数二维码的事件推送
    function reScan($fromSub=''){
		return $this->reScanBase($fromSub);
    }
	
	function scanLogin(){
		die($this->remText($this->scanLoginBase())); 
	}
	
	// 点击找回密码(用于电脑端,客户端不需要密码登录)
	function scanGetpw(){ 
		die($this->remText($this->scanGetpwBase())); 
	}

	function scanScansend(){
		$this->scanScansendBase();
		//$this->sendInfo = $this->exXXXXX(); //扩展
		if(empty($this->sendCfgs[2])){ //id为空
			$msg = $this->scan_Extra(1);
		}elseif($this->sendCfgs[0]=='a'){
			$fields = array(
				'subject' => '楼盘',
				'address' => '地址',
			);
			if(in_array($this->sendCfgs[1],array(4,115,116))){
				if($this->sendCfgs[1]==4){
					$fields['tel'] = '电话';
					$fields['dj'] = '均价';
				}else{
					$fields['lxdh'] = '联系电话';
					$fields['xingming'] = '联系人';	
				}
				$msg = $this->getScansendText($this->sendCfgs[2],$fields,'{mobileurl}archive.php?aid={aid}'); 
			}else{
				$msg = $this->scan_Extra(1);
			}
		}elseif($this->sendCfgs[0]=='m'){
			if($this->sendCfgs[1]==2){
				$fields = array(
					'xingming' => '经纪人',
					'email' => '电子邮件',
				);
				$url = '{mobileurl}index.php?caid=13&mid={mid}';
			}elseif($this->sendCfgs[0]=='m' && $this->sendCfgs[1]==3){
				$fields = array(
					'cmane' => '经纪公司',
					'lxdh' => '联系电话',
					'email' => '电子邮件',
					'caddress' => '地址',
				);
				$url = '{mobileurl}index.php?caid=13&addno=3&mid={mid}';
			}else{
				$fields = array(
					'companynm' => '公司',
					'lxdh' => '联系电话',
					'email' => '电子邮件',
					'dizhi' => '地址',
				);
				$url = '';
			}
			$msg = $this->getScansendText($this->sendCfgs[2],$fields,$url);	
		}else{
			$msg = $this->scan_Extra(1);	
		}
		die($this->remText($msg));
	}
/*
	{if empty($ismem)}
		{if in_array($chid,array(4,115,116))}
		{subject}{if $address}，地址：{address}{/if}{if $tel}，电话:{tel}{/if}{if $dj}，均价：{dj}元{/if}【{hostname}】
		{else}
		{subject}{if $address}，地址：{address}{/if}{if $lxdh}，联系电话:{lxdh}{/if}{if $xingming}，联系人：{xingming}{/if}【{hostname}】
		{/if}
	{else}
		{if $mchid==2}
		姓名：{xingming}{if $lxdh}，联系方式：{lxdh}{/if}，电子邮件：{email}【{hostname}】
		{elseif $mchid==3}
		{cmane}{if $lxdh}，联系方式：{lxdh}{/if}{if $caddress}，地址：{caddress}{/if}，电子邮件：{email}【{hostname}】
		{else}
		{companynm}{if $lxdh}，联系方式：{lxdh}{/if}{if $dizhi}，地址:{dizhi}{/if}，电子邮件：{email}
		{/if}
	{/if}
*/
	
	function scanScanupload(){
		$msg = $this->scanScanuploadBase(1);
		die($this->remText($msg));
	}

	function scan_Extra($re=0){
		$qrInfo = $this->getQrinfo($this->eventKey);
		$msg = "无对应操作：[$this->eventKey - {$qrInfo['smod']}], 请联系管理员！";
		if($re) return $msg;
		$msg = $this->remText($msg);
		die($msg);
	}
	
	// 点击自定义菜单:我的附近
	function clickMylocal(){
		$cms_abs = cls_env::mconfig('cms_abs');
		$pinfo = $this->savePos(0);
		if(empty($pinfo)){
			$msg = "未能检测到您的地理位置信息；请重新关注,提示【是否允许公众号使用其地理位置】时选【是】即可使用本功能。";
		}else{
			$msg = "您的位置信息是：【{$pinfo['longitude']},{$pinfo['latitude']}】！\n";
			$msg .= " 您可以使用以下服务：\n";
			$msg .= " <a href='{$cms_abs}info.php?fid=203&map={$pinfo['latitude']},{$pinfo['longitude']}&type=newcorp'>附近楼盘</a>；\n";
			$msg .= " <a href='{$cms_abs}info.php?fid=203&map={$pinfo['latitude']},{$pinfo['longitude']}&type=2ndcorp'>附近二手</a>；\n";
			$msg .= " <a href='{$cms_abs}info.php?fid=203&map={$pinfo['latitude']},{$pinfo['longitude']}&type=sevcorp'>附近出租</a>；\n";
		}
		$msg = $this->remText($msg);
		die($msg);
	}
	// 点击自定义菜单:无对应操作的处理
	// 处理之前兼容事件 ... 
	function click_Extra(){
		$msg = "无对应操作：[{$this->eventKey}], 请联系管理员！";
		$msg = $this->remText($msg);
		die($msg);
	}

}
