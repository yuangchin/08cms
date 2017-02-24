<?php
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT.'include/adminm.fun.php';
$mobfid = empty($mobfid) ? 0 : intval($mobfid); //手机版的独立页id
if($mobfid) define('IN_MOBILE', TRUE);
$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = 'forward='.rawurlencode($forward);
empty($action) && $action ='';
$mid = empty($mid) ? 0 : intval($mid); 
$id = empty($id) ? '' : cls_string::ParamFormat($id); 
$phonefields = empty($phonefields) ? authcode('phone,tel,lxdh','','08cms'.$authkey) : $phonefields;
$phonefields = str_replace(array("'",'"',"<",'>'),'',$phonefields); //不能直接用cls_envBase::repGlobalURL(); 等
$phonefields = str_replace(' ','+',@$phonefields); //encode中可能出现+,从地址拦接收后成为了空格
if($action == 'getpwd' && !empty($mid) && !empty($id)){
	$cmember = $db->fetch_one("SELECT m.mid,m.mname,m.email,s.confirmstr FROM {$tblprefix}members m,{$tblprefix}members_sub s WHERE m.mid='$mid' AND s.mid=m.mid");
	if(!$cmember || !$cmember['confirmstr']) cls_message::show('无效操作');
	list($dateline,$deal,$confirmid) = explode("\t",$cmember['confirmstr']);
	if($dateline < $timestamp - 86400 * 3 || $deal != 1 || $confirmid != $id){
		cls_message::show('无效操作');
	}
	if(!submitcheck('bgetpwd')){
		_header('会员找回密码','curbox');
		tabheader('会员密码设置','getpwd',"?action=getpwd&mid=$mid&id=$id&mobfid=$mobfid",2,0,1);
		trbasic('会员名称','',$cmember['mname'],'');
		trbasic('<font color="red">*</font>输入新密码','npassword','','password', array('validate' => makesubmitstr('npassword',1,0,3,15)));
		trbasic('<font color="red">*</font>重新输入新密码','npassword2','','password', array('validate' => makesubmitstr('npassword2',1,0,3,15)));
		tr_regcode('register');
		tabfooter('bgetpwd');
	}else{ 
		if(!regcode_pass('register',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误');
		$npassword = trim($npassword);
		$npassword2 = trim($npassword2);
		if($npassword != $npassword2) cls_message::show('两次输入密码不一致');
		if(!$npassword || strlen($npassword) > 15 || $npassword != addslashes($npassword)){
			cls_message::show('会员密码不合规范');
		}
		if($re = cls_ucenter::edit($cmember['mname'],$npassword)) cls_message::show($re);
        
        # 同步更新WINDID用户密码
        cls_WindID_Send::getInstance()->editUserInfo( $mid,array('password' => $npassword) );# 同步更新WINDID密码
        
		$npassword = _08_Encryption::password($npassword); 
		$db->query("UPDATE {$tblprefix}members SET password='$npassword' WHERE mid='$mid'");
		$db->query("UPDATE {$tblprefix}members_sub SET confirmstr='' WHERE mid='$mid'");
		cls_message::show('会员找回密码成功！',"{$cms_abs}login.php");
	}
}else{
	$sms = new cls_sms();

	if(!submitcheck('blostpwd')){ 
		_header('会员找回密码','curbox');
        $way = empty($way) ? 'email' : $way;
		
		$types = array('email'=>array("电子邮箱找回","?way=email&phonefields=$phonefields"));
		if(!$sms->isClosed()){
			$types = array('phone'=>array("手机短信找回","?way=phone&phonefields=$phonefields")) + $types;
		}
		if(!empty($weixin_getpw)){
			$types = array('weixin'=>array("微信扫描找回","?way=weixin&phonefields=$phonefields")) + $types;
		}
		url_nav($types,$way);

		if($way == 'phone'){
			tabheader('会员找回密码','lostpwd',"?way=$way&phonefields=$phonefields&$forwardstr",2,0,1);
			trbasic('<font color="red">*</font>会员名称','mname', '', 'text', array('validate' => makesubmitstr('mname',1,0,0,15)));
			$ajaxurl = $cms_abs._08_Http_Request::uri2MVC("ajax=mobcode&val=%1");
			echo <<<EOT
					<tr><td width="150px" class="item1"><b><font color="red">*</font>手机号码</b></td>
					<td class="item2">
					<input type="text" size="25" id="lxdh" name="phone" value="" warn="请用全部数字填写，座机小灵通请加区号" rule="text" must="1" mode="0" regx="/^\s*(?:[48]00-?\d{3}-?\d{4}|(?:00?[1-9]\d{1,2}-?)?[2-8]\d{6,7}|1[358]\d{9})\s*$/" min="0" max="15"><div class="validator_message init" style="display: none;">OK</div>
					<a id="tel_code" href="javascript:" onclick="sendCerCode('lxdh','register','tel_code');">【免费获取验证码】</a>
					<a id="tel_code_rep" style="margin-left:39px;color:#CCC; display:none"><span id="tel_code_rep_in">60</span>秒后重新获取</a>
					</td></tr>
					<tr><td width="150px" class="item1"><b><font color="red">*</font>确 认 码</b></td>
					<td class="item2">
					<input type="text" size="6" id="msgcode" name="msgcode" value="" warn="请输入6位验证码" rule="text" regx="/^\s*\d{6}\s*$/" must="1" rev="确认码" pass="OK" mode="0" min="0" max="15"><div class="validator_message init" style="display: none;">OK</div>
					若1分钟后仍未收到短信,可重发。
					</td></tr>
					<script type="text/javascript">
						window._08cms_validator && _08cms_validator.init("ajax","msgcode",{ url: '$ajaxurl' });
					</script>
					<script type='text/javascript' src='{$cms_abs}include/sms/cer_code.js'></script>

EOT;
		}elseif ($way == 'weixin') {
			tabheader('会员找回密码','lostpwd',"",2,0,1);
			echo "\n<tr><td colspan=2 style='text-align:center'><img src='' width='320' id='scanimg'></td></tr>";
			trbasic('提示','tips', '用微信扫描即可在手机上显示(重置)密码；仅绑定微信用户可用。', '');
			echo <<<EOT
					<script type="text/javascript">
                        function getQrcode(qrmod){
                            $('#scanimg').attr('src','');
                            var extp = Math.random().toString(36).substr(2); 
                            var url = 'ajax=Weixin_Ops&act=getQrcode&qrmod='+qrmod+'&extp='+extp+'&datatype=js&varname=data';
                            $.getScript(CMS_ABS + uri2MVC(url), function(){
                                $('#scanimg').attr('src',data.url);
                                $('#qr_sid').html('qr_sid='+data.sid);
                                $('#qr_stamp08').html('qr_stamp08='+data.stamp08);
                                $('#qr_sign08').html('qr_sign08='+data.sign08);
                                //console.log('getQrcode:'+extp); //调试
                            });
                        }
                        $(document).ready(function(){ getQrcode('getpw'); });
					</script>    
EOT;
        }else{
			tabheader('会员找回密码','lostpwd',"?way=$way&phonefields=$phonefields&$forwardstr",2,0,1);
			trbasic('会员名称','mname', '', 'text', array('validate' => makesubmitstr('mname66',1,0,0,15)));
			trbasic('会员Email','email', '', 'text', array('validate' => makesubmitstr('email',1,'email',0,80)));
			tr_regcode('register');
		}
		tabfooter($way=='weixin' ? '' : 'blostpwd');
	}else{
		if ($way == 'phone' && !$sms->isClosed()) {
			@list($inittime, $initcode) = maddslashes(explode("\t", authcode($m_cookie['08cms_msgcode'],'DECODE')),1);
			if($timestamp - $inittime > 1800 || $initcode != $msgcode) cls_message::show('手机确认码有误', M_REFERER);
			$mname = trim($mname);
			$phone = trim($phone);

			$_len = cls_string::CharCount($mname);// 其它系统编码跟当前系统不同时, 请先转化为当前系统编码
			if ($_len < 3 || $_len > 15) {
				cls_message::show('会员名称长度不规范');
			}
			$guestexp = '\xA1\xA1|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
			if (preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\s\t\<\>\&]|$guestexp/is", $mname)) {
				cls_message::show('会员名称不合规范');
			}

			$actuser = new cls_userinfo;
			if(!$actuser->activeuserbyname($mname)){
				cls_message::show('指定会员不存在或手机号码错误');
			}else{
				$actuser->detail_data();
				$info = $actuser->info;
                $cnt = 0; //电话号码在字段中的个数
                $phonearr = explode(',',authcode($phonefields, 'DECODE','08cms'.$authkey));
                foreach($phonearr as $key){
					//在这些字段中,找到手机号码即可算通过
                    if(!empty($info[$key]) && $info[$key]==$phone){
                    	$cnt++;
                        break;
                    }                    
                    /*
                    $v = $key; //之前用v
                    if (in_array($v,$info)){
						if(empty($info[$v])) cls_message::show('指定会员不存在或手机号码错误!');
					}*/
				}
                if(empty($cnt)) cls_message::show('指定会员不存在或手机号码错误!');
			}
			if ($actuser->isadmin()) {
				cls_message::show('管理员不能使用此功能！');
			}
			$mid = $info['mid'];
			unset($actuser);
			$confirmid = cls_string::Random(6);
			$confirmstr = "$timestamp\t1\t$confirmid";
			//var_dump($cmember);die;
			$db->query("UPDATE {$tblprefix}members_sub SET confirmstr='$confirmstr' WHERE mid='$mid'");
			$forward = "{$cms_abs}tools/lostpwd.php?action=getpwd&mid=$mid&id=$confirmid";
			//cls_message::show('验证已通过!',$forward);
			header("Location: $forward");
		}elseif ($way == 'weixin') {
        	echo '-end-';
        } else {
			if (!regcode_pass('register', empty($regcode) ? '' : trim($regcode))) {
				cls_message::show('验证码错误');
			}
			$mname = trim($mname);
			$email = trim($email);
			$_len = cls_string::CharCount($mname);// 其它系统编码跟当前系统不同时, 请先转化为当前系统编码
			if ($_len < 3 || $_len > 15) {
				cls_message::show('会员名称长度不规范');
			}
			$guestexp = '\xA1\xA1|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
			if (preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\s\t\<\>\&]|$guestexp/is", $mname)) {
				cls_message::show('会员名称不合规范');
			}
			if (!$email || !cls_string::isEmail($email)) {
				cls_message::show('会员Email不规范');
			}
			$cmember = $db->fetch_one(
				"SELECT mid,mname,email FROM {$tblprefix}members WHERE mname='$mname' AND email='$email'"
			);
			if (!$cmember) {
				cls_message::show('指定会员不存在或Email错误');
			}
			$actuser = new cls_userinfo;
			$actuser->activeuser($cmember['mid']);
			if ($actuser->isadmin()) {
				cls_message::show('管理员不能使用此功能！');
			}
			unset($actuser);
			$confirmid = cls_string::Random(6);
			$confirmstr = "$timestamp\t1\t$confirmid";
			$db->query("UPDATE {$tblprefix}members_sub SET confirmstr='$confirmstr' WHERE mid='$cmember[mid]'");
			$url = "{$cms_abs}" . (empty($mobfid) ? "tools/lostpwd.php?" : "info.php?fid={$mobfid}&") . "action=getpwd&mid=$cmember[mid]&id=$confirmid";
			mailto(
				"$mname <$email>",
				'member_getpwd_subject',
				'member_getpwd_content',
				array('mid' => $cmember['mid'], 'mname' => $mname, 'url' => $url, 'onlineip' => $onlineip)
			);
			cls_message::show('取回密码的方法成功发送到您的电子邮箱!', $forward);

		}
	}
}
