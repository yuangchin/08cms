<?php
/**
 * 08CMS绑定接口
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('OTHER_SITE_BIND_PATH') || die('Access forbidden!');
class bind08CMSInterface
{
    /**
     * 对外站的会员未绑定本站的会员进行打开绑定模板
     *
     * @param array $auth  当前授权对象
     * @param array $minfo 用户信息
     * @since 1.0
     */
    public static function BindTemplate($auth, $minfo)
    {
        global $cms_abs, $mcharset, $infloat, $timestamp, $handlekey, $hostname, $type, $cms_top;
		$curuser = cls_UserMain::CurUser();
        $username = substr($_SESSION[otherSiteBind::$authfields[$type]], 0, 10);
        #$username = $auth->getUserName();
        $useravatar = $auth->getUserAvatar();
        $post_url = $cms_abs . substr($_SERVER['REQUEST_URI'], 1);
        
        # QQ换了一种验证方式，所以改成直接绑定
        #include_once OTHER_SITE_BIND_PATH . '08cms_bind_template.php';
        
        $minfo = (array) $minfo;
        
        if(empty($curuser->info['mid']))
        {
            # 判断QQ登录后的用户名是否存在本站，存在时重置用户名，不存在则直接使用
            while(cls_userinfo::checkUserName($username) && (strlen($username) < 20))
            {
                $username .= cls_string::Random(1);
            }
            $minfo['mname'] = $username;
            $minfo['password'] = cls_string::Random(6);
            empty($useravatar) || $minfo['thumb'] = $useravatar;
            $minfo['email'] = substr($_SESSION[otherSiteBind::$authfields[$type]], 0, 10) . '@' . $cms_top;  
            self::actionBind('bindregister', $minfo);         
        }       
        else
        {
            $minfo['mname'] = $curuser->info['mname'];
            $minfo['password'] = $curuser->info['password'];
            $minfo['password2'] = $curuser->info['password'];
            self::actionBind('bindlogin', $minfo); 
        }
    }

    /**
     * 开始进行绑定操作
     *
     * @param string $act 值为bindregister即注册绑定，否则都视为登录绑定
     * @since 1.0
     */
    public static function actionBind($act, $minfo = array())
    {
        global $mname, $password, $email, $regcode, $thumb, $type, $mcharset, $tblprefix, $db, $censoruser, $onlineip, $timestamp, $password2;
		$curuser = cls_UserMain::CurUser();
		$mconfigs = cls_env::mconfig();
        empty($_SESSION[otherSiteBind::$authfields[$type]]) && otherAuthFactory::UcActive('错误请求，当前登陆信息已经过期！');
        $act = strtolower(trim($act));
        # 直接默认为注册状态
        foreach(array('mname', 'password', 'thumb', 'email') as $key)
        {
            empty($minfo[$key]) || $$key = $minfo[$key];
            $key == 'password' && $password2 = $minfo[$key];
        }
		
		//预处理会员帐号、密码、Email
		foreach(array('mname','password','email') as $key){
			if($act != 'bindregister' && $key == 'email') continue;//登录不需要email
			$re = cls_userinfo::CheckSysField(@$$key,$key,$act == 'bindregister' ? 'add' : 'login');
			if($re['error']){
				otherAuthFactory::UcActive($re['error']);
			}else $$key = $re['value'];
		}
		
        $username = trim($mname);
        switch($act) {
            // 注册绑定
            case 'bindregister' :
		        $password2 = trim($password2);
                if($password != $password2) cls_message::show('密码与确认密码不相同！',axaction(1,M_REFERER));
				//UC会员同步注册
				$_ucid = cls_ucenter::register($username,$password,$email,TRUE);
				
                $mchid = 1;#加入基本会员。
                $mchannel = cls_cache::Read('mchannel',$mchid);
                $newuser = new cls_userinfo;
                if($mid = $newuser->useradd($username,_08_Encryption::password($password),$email,$mchid))
                {
                    if(strtolower($type) == 'qq')
                    {
                        $auth = otherAuthFactory::Create($type);
                        $qqNickName = $auth->getUserName();
            			$newuser->updatefield('qq_nickname', $db->escape($qqNickName)); 
                    }
                    
        			$newuser->updatefield(otherSiteBind::$authfields[$type], $_SESSION[otherSiteBind::$authfields[$type]]);
                    empty($_ucid) || $newuser->updatefield(cls_ucenter::UC_UID, $_ucid);
        			#$autocheck = $mchannel['autocheck'];
        			$autocheck = 1;
					$newuser->check($autocheck);
        			if($autocheck == 1){
						cls_userinfo::LoginFlag($mid,_08_Encryption::password($password));
        			}elseif($autocheck == 2){
						cls_userinfo::SendActiveEmail($newuser->info);
        			}
        			$newuser->updatedb();
        			otherAuthFactory::UcActive(!$autocheck ? '用户等待审核' : ($autocheck == 2 ? '会员激活邮件已发送到您的邮箱，请进入邮箱激活完成绑定' : '会员登录成功。'));
                } else {
                    otherAuthFactory::UcActive('会员注册失败，请重新注册。');
                }
			break;
            // 登录绑定，这种方式已经对UC那块失效，因为已经获取不到明文密码来验证
            default :
				//登录前的预检测
                $curuser->loginPreTesting("javascript_alert: window.close(); ");//本系统登录的预检测
				
				//将登录帐号的会员资料读入$curuser，如会员不存在，则保持为游客，后续将进行整合UC中的会员
				$curuser->merge_user($username);
				
				//结合当前登录帐号及密码，将UC会员与本站会员进行整合，并处理同步登录
				if($re = $curuser->UCLogin($username,$password)) otherAuthFactory::UcActive($re);
                $windid = cls_WindID_Send::getInstance();
                $windid->setter('is_show_error_message', false);
    		    $windid->synLogin( $username, $password );
				
				//正式验证登录，及登录后操作
				$flag = false;
	//			if ( $curuser->info['password'] == _08_Encryption::password($password) ){
					$curuser->updatefield(otherSiteBind::$authfields[$type],$_SESSION[otherSiteBind::$authfields[$type]]);//可以重新绑定号码
					$curuser->updatedb();
					
					if ($curuser->info['checked'] == 1) {
						$curuser->OneLoginRecord(@$expires);
						otherAuthFactory::UcActive('绑定成功。');
					} elseif ($curuser->info['checked'] == 2) {#需要邮件激活，本次登录不成功，重新发送激活邮件
						exit('<script type="text/javascript"> alert("会员需要通过Email激活，现在重发激活邮件到您的邮箱。"); location.href = "'. self::getActivationJumpUrl($curuser->info, 'uc_action') . '"; </script>');
					} else {
					    otherAuthFactory::UcActive('会员需要管理员审核后才能正常登录',axaction(1,$forward));
					}
	//			} else {
//					$curuser->logincheck(-1,$username,$password);
//					otherAuthFactory::UcActive('会员帐号绑定失败');
//				}
			break;
        }
    }

    /**
     * 如果已经绑定则进行登录
     *
     * @since 1.0
     */
    public static function Login08CMS($minfo)
    {
        global $enable_uc, $timestamp, $onlineip, $type, $ckpath, $db;
        $curuser = cls_UserMain::CurUser();
		$curuser->currentuser();
        // 当会员模型需要手动激活审核或邮件激活的情况下(2为发送邮件到会员邮箱)
		if(empty($minfo['checked'])){
        	otherAuthFactory::UcActive('会员帐号需要管理员审核！');
		}elseif(intval(@$minfo['checked']) == 2) {//需要邮件激活，重发激活email
			mheader('Location:'. self::getActivationJumpUrl($minfo, 'uc_action'));
        }elseif(intval(@$minfo['checked']) == 1){
            //可以重新绑定号码
            if(!empty($curuser->info['mid']))
            {
                $curuser->OneLoginRecord();
				$k = otherSiteBind::$authfields[$type];
                $v = $_SESSION[otherSiteBind::$authfields[$type]];
                $db->update('#__members', array($k => ''))->where(array('mid' => $curuser->info['mid']))->exec(); #重置
                $curuser->info[otherSiteBind::$authfields[$type]] = '';
                                
                if(strtolower($type) == 'qq')
                {
                    $auth = otherAuthFactory::Create($type);
                    $qqNickName = $auth->getUserName();
                    $curuser->updatefield('qq_nickname', $db->escape($qqNickName));
                }
                
                $curuser->updatefield(otherSiteBind::$authfields[$type],$_SESSION[otherSiteBind::$authfields[$type]]);//重新绑定号码
                $curuser->updatedb();
                otherAuthFactory::UcActive('重新绑定成功！');
            }
                    
            cls_userinfo::LoginFlag($minfo['mid'],$minfo['password']);            
        	otherAuthFactory::UcActive('登录成功！');
		}
    }

    /**
     * 获取激活跳转URL
     *
     * @param  string $action 跳转参数
     * @return string         返回要跳转的URL
     * @since  1.0
     */
    public static function getActivationJumpUrl($minfo, $action)
    {
        global $cms_abs;
		return cls_userinfo::SendActiveEmailUrl(@$minfo['mname'],@$minfo->info['email'],$cms_abs . substr($_SERVER['REQUEST_URI'], 1) . "&act={$action}")	;	
    }
}