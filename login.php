<?php
defined('M_UPSEN') || define('M_UPSEN', TRUE);
defined('NOROBOT') || define('NOROBOT', TRUE);
include_once dirname(__FILE__).'/include/general.inc.php';
//$mobiledir = cls_env::mconfig('mobiledir'); //整到类中去了就要这一句
_08_FilesystemFile::filterFileParam($mobiledir); 
$ismobiledir = defined('IN_MOBILE') ? "$mobiledir/" : ''; //echo $ismobiledir; //处理手机版地址
if($ex = exentry('login')){include($ex);mexit();} 
empty($action) && $action = 'login';
empty($mode) && $mode = '';
empty($forward) && $forward = M_REFERER;
$gets = cls_env::_GET_POST('token');
if(!$forward || preg_match('/\b(?:login|register)\.php(\?|#|$)/i', $forward))$forward = $cms_abs.(defined('IN_MOBILE') ? $ismobiledir : 'adminm.php');#整合pw后考虑pw返回的跳转连接
switch($action){
case 'login':
	if($enable_pptin && $pptin_url && $pptin_login && $mode != 'js'){//除了显示js登录模板，直接跳转到通行证服务端
		$forward = substr($cms_abs, 0, -1);//??
		$url = $pptin_url.$pptin_login;
		$url .= (in_str('?',$url) ? '&' : '?') . 'forward=' . rawurlencode($forward);
		mheader('location:'.$url);
	}
	if(!submitcheck('cmslogin')){
		$temparr = array('forward' => rawurlencode($forward));
		if($mode == 'js'){//前台js调用登录信息的处理
			# 让它在过去就"失效"
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			# 永远是改动过的
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
			# HTTP/1.1
			header("Cache-Control: no-store, no-cache , must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			# HTTP/1.0
			header("Pragma: no-cache");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			js_write(cls_tpl::SpecialHtml(empty($memberid) ? 'jslogin' : 'jsloginok',array('forward' => rawurlencode($forward))),defined('IN_MOBILE'));
			mexit();
		}else{
			if(!($tplname = cls_tpl::SpecialTplname('login',defined('IN_MOBILE')))){
				include_once M_ROOT."include/adminm.fun.php";
				_header('会员登陆');
				echo form_str('cmslogin',"?forward=".rawurlencode($forward));
				tabheader_e();
				echo '<tr class="header"><td colspan="2"><b>会员登陆&nbsp; &nbsp; >><a href="'.$cms_abs.'tools/lostpwd.php"'.(empty($infloat)?'':" onclick=\"return floatwin('open_$handlekey',this)\"").'>找回密码</a> <a href="'.$cms_abs.$ismobiledir.'register.php"'.(empty($infloat)?'':" onclick=\"return floatwin('open_$handlekey',this)\"").'>注册</a></b></td></tr>';
				trbasic('会员名称','username', '', 'text', array('validate' => ' rule="text" must="1" min="3" max="15" warn="用户名应为3-15个字节！"'));
				trbasic('登陆密码','password','','password', array('validate' => ' rule="text" must="1" min="1" max="15" warn="密码空或包含非法字符（不能用全0作密码）！"'));
				$cookiearr = array('0' => '永久记住','-1' => '不记住','2592000' => '1个月', '7776000' => '3个月',);
				trbasic('记住登录状态','expires',makeoption($cookiearr,0),'select');
				tr_regcode('login');
				echo '<script type=\"text/javascript\">window._08cms_validator && _08cms_validator.submit(function(){if(this.client_t)this.client_t.value=(new Date).getTime()})</script>';
				trhidden('client_t','');
                if (isset($gets['token']))
                {
                    trhidden('token', $gets['token']);
                }
				tabfooter('cmslogin','登陆');
				mexit('</div></body></html>');
			}else{
				$html = cls_SpecialPage::Create(
					array(
						'tplname' => $tplname,
						'_da' => array('forward' => rawurlencode($forward),),
						'LoadAdv' => true,
						'NodeMode' => defined('IN_MOBILE'),
					)
				);
                if (isset($gets['token']))
                {
                    $trhidden = <<<HTML
                    <input type="hidden" name="token" value="{$gets['token']}" />
HTML;
                    $html = preg_replace('/(<form.*>)/', "$1\n{$trhidden}", $html);
                }
                
				exit($html);
			}
		}
	}else{
		//验证码
		regcode_pass('login',empty($regcode) ? '' : trim($regcode)) || cls_message::show('验证码错误',axaction(1,M_REFERER));
		
		//预处理会员帐号、密码
		foreach(array('username' => 'mname','password' => 'password') as $key => $type){
			$re = cls_userinfo::CheckSysField(@$$key,$type,'login');
			if($re['error']){
				cls_message::show($re['error'],axaction(1,M_REFERER));
			}else $$key = $re['value'];
		}
		$md5_password = _08_Encryption::password($password);
		
		
		//登录前的预检测
        $curuser->loginPreTesting(axaction(1,M_REFERER));//本系统登录的预检测
		
		//将登录帐号的会员资料读入$curuser，如会员不存在，则保持为游客，后续将进行整合UC中的会员
		$curuser->merge_user($username);
		
		//结合当前登录帐号及密码，将UC会员与本站会员进行整合，并处理同步登录
		if($re = $curuser->UCLogin($username,$password)) cls_message::show($re,axaction(1,M_REFERER));
       
       	# 同步登录通行证
		cls_WindID_Send::getInstance()->synLogin( $username, $password );
        
        # 重新设置报头，以免UC或PW设置了头影响了本站的内容输出
        header("Content-type:text/html;charset=$mcharset");
		
		//正式验证登录，及登录后操作
		if($curuser->info['password'] == $md5_password){
			if($curuser->info['checked'] == 1){
                # 如果是微信登录时绑定微信号
        		if (isset($gets['token']))
                {
                    $token = _08_Encryption::getInstance($gets['token'])->deCryption();
                    @list($FromUserName, $token, $time, $hash) = explode(',', $token);
                    if (empty($time) || (TIMESTAMP - $time > 1800))
                    {
                        cls_message::show('微信登录请求已超时，请重新扫描二维码进行登录。');
                    }
                    if (empty($token) || ($token != _08_Encryption::password($weixin_token)))
                    {
                        cls_message::show('请求错误，请重新扫描二维码进行登录。');
                    }
                    if (empty($FromUserName))
                    {
                        cls_message::show('不存在需要绑定的微信用户。');
                    }
                    
                    $curuser->updatefield('weixin_from_user_name', $db->escape($FromUserName)); 
                    $curuser->updatedb();
                    
                    cls_message::show('微信绑定成功', _08_CMS_ABS);
                }
				$curuser->OneLoginRecord(@$expires);
				//cls_message::show('会员登录成功',axaction(2,$forward));		
				//登陆成功后，让会员选择进入首页还是进入会员中心
				$_url_arr = array();			
				if(!empty($forward)){
					$_url_arr['返回'] = $forward; 
					$refurl = str_replace(array('index.html','index.htm','index.php'),"",$forward);
					if($refurl!=$cms_abs.$ismobiledir) $_url_arr['首页'] = $cms_abs.$ismobiledir;
				}
				defined('IN_MOBILE') || $_url_arr['会员中心'] = $cms_abs.$ismobiledir.'adminm.php'; //网页版才有会员中心			
				cls_message::show('会员登录成功',$_url_arr);
			}elseif($curuser->info['checked'] == 2){#需要邮件激活，本次登录不成功，重新发送激活邮件
				cls_message::show('会员需要通过Email激活，现在重发激活邮件到您的邮箱',cls_userinfo::SendActiveEmailUrl($username,$curuser->info['email'],$forward));
			}else cls_message::show('未审会员!',axaction(1,M_REFERER));
		}
        else
        {
            $curuser->loginFailureHandling($username, $password, axaction(1, M_REFERER));//登录失败
        }
	}
	break;

case 'weixin_login':
    if (isset($gets['token']))
    {
        $token = _08_Encryption::getInstance($gets['token'])->deCryption();
        @list($FromUserName, $token, $time, $hash) = explode(',', $token);
        if (empty($time) || (TIMESTAMP - $time > 1800))
        {
            cls_message::show('链接已超时，请重新扫描二维码进行登录。');
        }
        if (empty($hash) || empty($token) || ($token != _08_Encryption::password($weixin_token)))
        {
            cls_message::show('请求错误，请重新扫描二维码进行登录。');
        }
        
        $userinfo = $curuser->getUserInfo('mid, mname', array('weixin_from_user_name' => $FromUserName));
        if (empty($userinfo))
        {
            header('Location: ' . _08_CMS_ABS . $mobiledir . '/login.php?is_weixin=1&token=' . $gets['token']);
            exit;
        }
        
		$newuser = new cls_userinfo(); //注意不用$curuser
		$newuser->activeuser($userinfo['mid']);
		$newuser->OneLoginRecord();
		//cls_outbug::main("_08_M_Weixin_Event::Scan-28b",'','wetest/log_'.date('Y_md').'.log',1);
		$db->update('#__msession', array('scene_id'=>$scene_id))->where(array('msid'=>$m_cookie['msid']))->exec();
        $_SESSION[$hash] = $time;
		//$_SESSION["mid_$hash"] = $userinfo['mid'];
        cls_message::show('登录成功。', _08_CMS_ABS);
    }
    
    cls_message::show('登录失败。');
    break;
    
case 'quit':#兼容PHPWind通行证
case 'logout':
    ob_start();
	cls_ucenter::logout();    
    # 向WINDID服务端发送同步退出请求
	cls_WindID_Send::getInstance()->synLogout();
	cls_userinfo::LogoutFlag();
    $contents = ob_get_contents();
    ob_end_clean();
    $gets = cls_env::_GET('datatype, callback');
    if (isset($gets['datatype']) && (strtolower($gets['datatype']) === 'js'))
    {
        cls_phpToJavascript::toAjaxSynchronousRequest($contents);
        $ajax = _08_C_Ajax_Controller::getInstance();
        exit($ajax->format(array('error' => '', 'message' => '会员退出成功'), $gets['callback']));
    }
    else
    {
        echo $contents;
    	if(!$forward || preg_match('/\badminm.php(\?|#|$)/i', $forward) || preg_match('/\blogin.php(\?|#|$)/i', $forward))$forward = $cms_abs.$ismobiledir; //.'index.php'
    	cls_message::show('会员退出成功',$forward);
    }

}
?>
