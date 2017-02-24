<?php
!defined('M_COM') && exit('No Permisson');
class cls_userbase{
	var $info = array();
	var $updatearr = array();
	var $subed = 0;
	var $detailed = 0;
	var $apms = array();//管理角色权限
    
    protected $_db = null;
    protected $_mconfigs = array();
    protected $_timestamp = 0;
    protected $_onlineip = '';
    
	function init(){
		$this->info = array();
		$this->detailed = 0;
		$this->subed = 0;
		$this->updatearr = array();
	}
	function currentuser(){
		global $m_cookie,$db,$tblprefix,$onlineip,$timestamp,$msessionexist;
		if(defined('M_NOUSER') || (defined('ISROBOT') && ISROBOT)){
			$this->info = self::nouser_info();
			$this->info += array('onlineip' => $onlineip,'mslastactive' => $timestamp,'lastolupdate' => $timestamp,'errtimes' => 0,'errdate' => 0,);
			return;
		}
		$memberid = 0;$memberpwd = '';
        
        /**
         * 某些浏览器使用多进程的方式启动窗口使swfupload上传控件COOKIE不能带过去，
         * 在这用这方法带COOKIE到其它进程里。
         **/
        $post = cls_env::_POST();
        if (isset($post['userauth']))
        {
            $m_cookie['userauth'] = $post['userauth'];
        }
        if (isset($post['msid']))
        {
            $m_cookie['msid'] = $post['msid'];
        }
        
		if(!empty($m_cookie['userauth'])) @list($memberpwd,$memberid) = maddslashes(explode("\t", authcode($m_cookie['userauth'], 'DECODE')), 1);
		if(!($memberid = max(0,intval($memberid)))) list($memberpwd,$memberid) = array('',0);
		$msessionexist = 0;
		if($msid = isset($m_cookie['msid']) ? $m_cookie['msid'] : ''){
			if($memberid){
				$sqlstr = "SELECT ms.*,m.* FROM {$tblprefix}msession ms,{$tblprefix}members m WHERE ms.mid=m.mid AND ms.msid='$msid' AND m.mid='$memberid' AND m.password='$memberpwd' AND m.checked=1";
			}else $sqlstr = "SELECT * FROM {$tblprefix}msession WHERE msid='$msid'";
			if($msession = $db->fetch_one($sqlstr)){
				$msessionexist = 1;
				$memberid || $msession = array_merge($msession,self::nouser_info());
			}
		}
		if(!$msessionexist){
			if($memberid){
				if(!($msession = $db->fetch_one("SELECT * FROM {$tblprefix}members WHERE mid='$memberid' AND password='$memberpwd' AND checked=1"))){
					list($memberpwd,$memberid) = array('',0);
				}
			}
			$memberid || $msession = self::nouser_info();
			$msession += array('onlineip' => $onlineip,'mslastactive' => $timestamp,'lastolupdate' => $timestamp,'errtimes' => 0,'errdate' => 0,);
			if(!$msid){
				$msession['msid'] = cls_string::Random(6);
				msetcookie('msid',$msession['msid'],365*86400);
			}else $msession['msid'] = $msid;
		}
        $this->info = $msession;
		
		$this->info['mspacehome'] = cls_Mspace::IndexUrl($this->info);
		$this->groupclear(1);
		$this->updatesession();
	}
    /**
     * 会员中心的代管操作
     * 使用被代管者的身份登录会员中心
     */
    public function mcTrustee(){
		global $onlineip,$timestamp,$memberid;
        // 判断是否为托管操作，并且是否有权限
        if($info = $this->isTrusteeship()) {
            $trusteeship = array('from_mid' => $this->info['mid'], 'from_mname' => $this->info['mname']);
			foreach(array('msid','onlineip','mslastactive','lastolupdate','errtimes','errdate',) as $var){
				if(isset($this->info[$var])) $info[$var] = $this->info[$var];//继承操作者的msid等session资料
			}
            $this->info = $info;
            $memberid = $info['mid'];
            $this->info['atrusteeship'] = $trusteeship;//代管标记
        } else if(defined('M_MCENTER')){
            mclearcookie('trusteeship');
        }
	}


    /**
     * 判断是否为托管操作，并且是否有权限
     *
     * @return 如果是则返回托管会员信息，否则返回FALSE
     * @since  1.0
     */
    public function isTrusteeship()
    {
        global $from_mid, $g_apid;
        // 目前托管只应用于会员中心
        if(!defined('M_MCENTER')) return false;
        if(!empty($from_mid)) {
            $from_mid = (int)$from_mid;
        } else {
            $trusteeship = self::TrusteeCookieInfo();//代管cookie
            if($trusteeship)
            {
                $from_mid = intval($trusteeship['from_mid']);
            }
        }

        if(!empty($from_mid) && ($from_mid != $this->info['mid']))//在传入$from_id或代管cookie的情况下
        {
			$msg = '';
			$from_user = new cls_userinfo;
			$from_user->activeuser($from_mid,1);
			if(!$from_user->info['mid']){
				$msg = '指定了无效会员';
			}elseif(empty($this->info['isfounder']) && $re = $from_user->noPm(@$g_apid)){//该会员不允许被托管
				$msg = $re;
			}elseif(!$this->inTrusteeshipList(@$from_user->info['trusteeship']) && $this->NoBackFunc('trusteeship')) {//代管权限
				$msg = '不在代管者名单或管理角色没有代管权限';
			}
			if($msg){
				mclearcookie('trusteeship');
				_header();
				cls_message::show('因为以下原因，您无法代管该会员中心：<br>'.$msg);
			}else{
				$trusteeship = array('from_mid' => $from_user->info['mid'], 'from_mname' => $from_user->info['mname']);
				msetcookie('trusteeship', serialize($trusteeship));//浏览器有效期
				return $from_user->info;
			}
       }
        return false;
    }

    /**
     * 判断当前登录用户是否在其它会员的托管列表中
     *
     * @param  string  $trusteeship 其它会员的托管列表
     * @return bool           在列表中返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public function inTrusteeshipList($trusteeship = '')
    {
        if(!$trusteeship) return false;
        $mids = explode(',',$trusteeship);
        return in_array($this->info['mid'], $mids);
    }

    /**
     * 获取cookie中的代管用户信息
     *
     * @return 获取成功返回用户ID和名称，否则返回 false
     * @since  1.0
     */
    public static function TrusteeCookieInfo()
    {
        global $m_cookie;
        if(!empty($m_cookie['trusteeship'])) {
            return unserialize(stripslashes($m_cookie['trusteeship']));
        }
        return false;
    }

    /**
     * 获取代管用户信息
     *
     * @return 获取成功返回用户ID和名称，否则返回 false
     * @since  1.0
     */
    public function getTrusteeshipInfo()
    {
		return empty($this->info['atrusteeship']) ? false : $this->info['atrusteeship'];
    }

    /**
     * 设置允许托管选中会员信息的会员
     *
     * @param  string $usernames 允许托管的会员名称
     * @return bool             设置成功返回TRUE，否则返回FALSE
     *
     * @since  1.0
     */
    public function setTrusteeshipUser($usernames,$updatedb = 0)
    {
        global $g_apid;
        if($this->noPm($g_apid)) return false;
        $user_ids = array();
        // 如果非解除操作
        if($users = array_filter(explode(',', (string) $usernames)))
        {
            foreach($users as $user)
            {
                if($id = self::getIdForName(trim($user))) $user_ids[] = $id;
            }
        }
		$this->updatefield('trusteeship',$user_ids ? implode(',', $user_ids) : '');
		$updatedb && $this->updatedb();
		return true;
    }

    /**
     * 通过用户名获取用户ID
     *
     * @param  string 会员名称
     * @return        返回用户ID
     *
     * @since  1.0
     */
    public static function getIdForName($user)
    {
        $user = addslashes($user);
        $user_info = self::getUserInfo('mid', array('mname'=>$user));
        return (int)$user_info['mid'];
    }
	
    /**
     * 通过用户ID获取用户名
     *
     * @param  string 会员id
     * @return        返回用户名
     *
     * @since  1.0
     */
    public static function getNameForId($mid)
    {
		$mname = '';
        if($mid = max(0,intval($mid))){
			$info = self::getUserInfo('mname', "mid = $mid");
			if(!empty($info['mname'])) $mname = $info['mname'];
		}
        return $mname;
    }

    /**
     * 获取用户信息
     *
     * @param  string $field 要获取的字段
     * @param  string $where 要获取的条件
     * @param  bool   batch  是否要获取批量信息，TRUE为是，FALSE为只获取单条信息
     * @return object        获取成功返回当前数据库指针，否则返回FALSE
     *
     * @since  1.1
     */
    public static function getUserInfo($field = '*', $where='', $batch = false)
    {
        $db = _08_factory::getDBO();
        $db->select($field)->from('#__members')->where($where)->exec();
        # 获取批量用户信息
        if($batch)
        {
            $datas = array();
            while ( $row = $db->fetch() )
            {
                $datas[] = $row;
            }
            return $datas;
        }
        
        return $db->fetch();
    }

    /**
     * 以会员名称方式判断是否存在这会员
     *
     * @param  string $user       会员名称
     * @return int                返回存在的行数，不存在则返回 0
     *
     * @since  1.0
     */
    public static function checkUserName($user)
    {
        $checked = self::getUserInfo('COUNT(*) AS num', array('mname'=>$user));
        return (int)$checked['num'];
    }


	function vsrecord(){
		global $vs_holdtime,$db,$tblprefix,$timestamp,$m_cookie;
		$vs_holdtime = empty($vs_holdtime) ? 0 : max(0,min(300,intval($vs_holdtime)));
		if(empty($vs_holdtime) || empty($this->info)) return;
        if ( empty($this->info['msid']) )
        {
            $this->info['msid'] = @$m_cookie['msid'];
        }
		$db->insert( '#__visitors', 
			array(
				'url' => 'http://'.M_SERVER.M_URI, 
				'robot' => ISROBOT ? 1 : 0, 
				'msid' => $this->info['msid'], 
				'onlineip' => $this->info['onlineip'], 
				'useragent' => @$_SERVER['HTTP_USER_AGENT'], 
				'mid' => $this->info['mid'],
				'mname' => $this->info['mname'],
				'createdate' => TIMESTAMP,
			)
    	)->exec();
		if(!($timestamp % 10)) $db->query("DELETE FROM {$tblprefix}visitors WHERE createdate<'".($timestamp - 60 * $vs_holdtime)."'");
		return;
	}
	function updatesession(){
		global $m_cookie,$onlinetimecircle,$db,$tblprefix,$timestamp,$onlineip,$maxerrtimes,$minerrtime;
		static $sessionupdated;
		if($sessionupdated || !defined('M_UPSEN')) return;
		$onlinetimecircle || $onlinetimecircle = 10;
		if($onlinetimecircle && $this->info['mid'] && $timestamp - $this->info['lastolupdate'] > $onlinetimecircle * 60){//隔一定时间将最后在线时间写入会员表
			$lastolupdate = $timestamp;
			$db->query("UPDATE {$tblprefix}members SET lastactive='{$this->info['mslastactive']}' WHERE mid='{$this->info['mid']}'");
		}else $lastolupdate = $this->info['lastolupdate'];
		if(!empty($m_cookie['msid'])){
			if($db->result_one("SELECT 1 FROM {$tblprefix}msession WHERE msid='{$this->info['msid']}'")){
				$db->query("UPDATE {$tblprefix}msession SET
					  mid='{$this->info['mid']}',
					  mname='{$this->info['mname']}',
					  onlineip='$onlineip',
					  mslastactive='$timestamp',
					  lastolupdate='$lastolupdate'
					  WHERE msid='{$this->info['msid']}'");
			}else{
				$db->query("INSERT INTO {$tblprefix}msession (msid,onlineip,mid,mname,mslastactive) VALUES
					  ('{$this->info['msid']}','$onlineip','{$this->info['mid']}','{$this->info['mname']}','$timestamp')", 'SILENT');
				if($this->info['mid'] && $timestamp - $this->info['lastactive'] > 21600){
					$db->query("UPDATE {$tblprefix}members SET lastip='$onlineip',lastactive='$timestamp' WHERE mid='{$this->info['mid']}'");
				}
			}
			if($maxerrtimes && $this->info['errtimes'] && ($timestamp - $this->info['errdate'] > $minerrtime * 60)){
				$db->query("UPDATE {$tblprefix}msession SET errtimes=0,errdate=0 WHERE msid='{$this->info['msid']}'");
				$this->info['errtimes'] = 0;
			}
		}
		$sessionupdated = 1;
	}
    
    /**
     * 登录前预检测，如果用户被锁定则终止
     * 
     * @param string $callbackurl 返回网址，如果传递了该参数则会返回该网址
     */
    public function loginPreTesting( $callbackurl = '' )
    {
        if($this->_mconfigs['maxerrtimes'] && $this->info['errtimes'] >= $this->_mconfigs['maxerrtimes'])
        {        
            $this->showLoginMessage('登录尝试过多暂时锁定，请于'.
                                    ($this->_mconfigs['minerrtime'] - intval(($this->_timestamp - $this->info['errdate']) / 60)) . 
                                    '分钟后再登录。', $callbackurl);
        }
    }
    
    /**
     * 登录成功后重置错误SESSION记录
     */
    public function resetErrorMsession()
    {
        if(empty($this->info['msid'])) return;
        
        if ( $this->_mconfigs['maxerrtimes'] )
        {
            $this->_db->update('#__msession', array('errtimes' => 0, 'errdate' => 0))
                      ->where(array('msid' => $this->info['msid']))->exec();
        }
    }
    
    /**
     * 登录失败处理
     * 
     * @param string $username    当前登录的用户名
     * @param string $password    当前登录的用户密码
     * @param string $callbackurl 返回网址，如果传递了该参数则会返回该网址
     * 
     * @since 1.0
     */
    public function loginFailureHandling( $username = '',$password = '', $callbackurl = '' )
    {
        $maxerrtimes = (int) $this->_mconfigs['maxerrtimes'];
        $timestamp = $this->_timestamp;
        
		$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
		record2file('badlogin',mhtmlspecialchars($timestamp."\t".stripslashes($username)."\t".$password."\t".$this->_onlineip));
		if( $maxerrtimes && !empty($this->info['msid']) )
        {
		    $x=intval($this->info['errtimes']);
            $this->_db->update('#__msession', array('errtimes' => $x+1, 'errdate' => $timestamp))
                      ->where(array('msid' => $this->info['msid']))->exec();
			$num = $maxerrtimes - $this->info['errtimes'] - 1;
            
            if ( $num > 0 )
            {
                $msg =  "您的登录信息有误，还可以尝试 $num 次！";
            }
            else
            {
            	$msg =  '错误登录满'.$maxerrtimes.'次，'.($this->_mconfigs['minerrtime'] - intval(($timestamp - $this->info['errdate']) / 60)).'分钟内请不要再尝试。';
            }
		}
        else
        {
            $msg = '会员登录失败。';
        }
        
        $this->showLoginMessage($msg, $callbackurl);
    }
    
    /**
     * 打印登录时相关信息
     * 
     * @param string $message     要打印的相关信息
     * @param string $callbackurl 打印后返回的URL
     * 
     * @since 1.0
     */
    private function showLoginMessage( $message, $callbackurl = '' )
    {
        $callbackurl = trim($callbackurl);
        if ( false !== stripos($callbackurl, 'javascript_alert') )
        {
            $javascripts = substr($callbackurl, 17);
            cls_message::show($message, 'javascript:(function(){ alert(\'' . $message . '\'); ' . $javascripts . '})();');
            
        }
        else
        {
        	cls_message::show($message, $callbackurl);
        }        
    }
    
    // 该函数暂时保留以做兼容，以后调用请直接调用以上函数
	function logincheck($mode = 0,$username = '',$password = ''){
		switch($mode){
			case 0://登录前
				$this->loginPreTesting();
			break;
			case 1://登录成功
				$this->resetErrorMsession();
			break;
			case -1://登录失败
				$this->loginFailureHandling($username, $password);
			break;
		}
	}
	
	//用于将另一名会员的资料合并到当前会员
	function merge_user($mname = ''){
		if(!$mname) return $this->info['mid'];
		$auser = new cls_userinfo;
		$auser->activeuserbyname($mname);
		$this->info = array_merge($this->info,$auser->info);
		unset($auser);
		return $this->info['mid'];
	
	}	
	
	function activeuserbyname($mname,$detail = 0,$ttl = 0){
		global $db,$tblprefix;
		$this->init();
		if($mname && $this->info = $db->fetch_one("SELECT m.*,s.* FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid WHERE mname='$mname'",$ttl)){
			$this->subed = 1;
			$detail && $this->detail_data($ttl);
		}else{
			$this->info = self::nouser_info();
		}
		$this->info['mspacehome'] = cls_Mspace::IndexUrl($this->info);
		$this->groupclear(1);
		return $this->info['mid'];
	}
	function activeuser($mid,$detail=0,$ttl = 0){
		global $db,$tblprefix;
		$this->init();
		if($mid && $this->info = $db->fetch_one("SELECT m.*,s.* FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid WHERE m.mid='$mid'",$ttl)){
			$this->subed = 1;
			$detail && $this->detail_data($ttl);
		}else{
			$this->info = self::nouser_info();
		}
		$this->info['mspacehome'] = cls_Mspace::IndexUrl($this->info);
		$this->groupclear(1);
		return $this->info['mid'];
	}
	public static function nouser_info(){
		$sysparams = cls_cache::cacRead('sysparams');
		return $sysparams['nouser'];
	}	
	
	function useradd($mname = '',$password = '',$email = '',$mchid = 0){
		global $db,$tblprefix,$timestamp,$onlineip;
		if(!$mname || !$mchid) return 0;
        $salt = cls_string::Random(6);
		$db->query("INSERT INTO {$tblprefix}members SET mname='$mname',password='$password',email='$email',mchid='$mchid',regdate='$timestamp',regip='$onlineip',lastvisit='$timestamp', salt = '$salt'");
		if(!($mid = $db->insert_id())){
			return 0;
		}else{
			$db->query("INSERT INTO {$tblprefix}members_sub SET mid='$mid'");
			$db->query("INSERT INTO {$tblprefix}members_$mchid SET mid='$mid'");
			$this->info = $db->fetch_one("SELECT m.*,s.* FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid WHERE m.mid='$mid'");
			$this->info['mspacehome'] = cls_Mspace::IndexUrl($this->info);
			$this->subed = 1;
			$this->detail_data();
			
			//以下更新不需要即时更新到数据库，因为执行这个方法之后，后续都会有统一的updatedb操作
			$this->InitCurrency();
			$this->groupinit();	
			$this->updatefield('mtcid',($mtcid = array_shift(array_keys($this->mtcidsarr()))) ? $mtcid : 0);
			return $mid;
		}
	}
	function sub_data($ttl = 0){
		global $db,$tblprefix;
		if(empty($this->info['mid'])) return;
		if($this->subed) return;
		if($member = $db->fetch_one("SELECT * FROM {$tblprefix}members_sub WHERE mid=".$this->info['mid'],$ttl)) $this->info = array_merge($this->info,$member);
		unset($member);
		$this->subed = 1;
	}
	function detail_data($ttl = 0){
		global $db,$tblprefix;
		if(empty($this->info['mid']) || $this->detailed) return;
		!$this->subed && $this->sub_data();
		if($r = $db->fetch_one("SELECT * FROM {$tblprefix}members_{$this->info['mchid']} WHERE mid='".$this->info['mid']."'",$ttl)){
			$this->info = array_merge($r,$this->info);
			unset($r);
		}
		$this->detailed = 1;
	}
	function check($check=1,$updatedb=0){//$check执行审核或解审的操作
		if(!$this->info['mid'] || $this->info['checked'] == $check) return;
		if(!$check && $this->info['isfounder']) return;
		$this->updatefield('checked',$check);
		$updatedb && $this->updatedb();
	}
	function autopush(){ //自动推送
		$pa = cls_pusher::paidsarr('members',$this->info['mchid']);
		foreach($pa as $paid=>$paname){ 
			$pusharea = cls_PushArea::Config($paid);
			if(!empty($pusharea['autopush'])){ //不用返回值
				cls_pusher::push($this->info,$paid,21); 
			}
		}
	}
	
	// 开启注册手机短信认证码(则自动认证:会员认证-手机认证)
	function automcert($smstelfield,$smstelval){ 
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$timestamp = TIMESTAMP;
		$mctypes = cls_cache::Read('mctypes');
		$msgcode = cls_env::GetG('msgcode');
		$memberid = $this->info['mid'];
		$mctid = 0;
		foreach($mctypes as $k => $v){
			if($v['field']==$smstelfield && $v['mode']==1){
				$mctype = $v;
				$mctid = $k;
				break;
			}
		} 
		if(!$mctid) return;
		$db->query("INSERT INTO {$tblprefix}mcerts SET mid='$memberid',mname='{$this->info['mname']}',mctid='$mctid',createdate='$timestamp',checkdate='$timestamp',content='$smstelval',msgcode='$msgcode'");
		if($mcid = $db->insert_id()){
			//if($mctype['autocheck']){
				$this->updatefield("mctid$mctid",$mctid); //直接审核
				if($mctype['award']) $this->updatecrids(array($mctype['crid'] => $mctype['award']),0,"$mctype[cname] 加分");
				$this->updatedb();
			//}
		}
	}	
	/**
	 * 删除用户：
	 *
	 */
	function delete(){
		global $db,$tblprefix;
		if(!$this->info['mid'] || $this->info['isfounder']) return false;
		/********** extend_example/libs/xxxx/userinfo.cls.php中同名函数的扩展部分(主要处理,相关文档交互等) ***************/
		$this->_delete();
		return true;
	}
	function _delete(){
		global $db,$tblprefix,$mspacedir;
		$mid = $this->info['mid']; 
		
		// 删除-特有表
		$db->query("DELETE FROM {$tblprefix}webcall      WHERE mid='$mid'",'UNBUFFERED'); // 400电话
		$db->query("DELETE FROM {$tblprefix}pms          WHERE fromid='$mid' OR toid='$mid'",'UNBUFFERED'); // 站内短信(收发)
		$db->query("DELETE FROM {$tblprefix}sms_sendlogs WHERE mid='$mid' ",'UNBUFFERED'); // 短信发送记录
		
    	//公共-静态文件
		$dir  = $db->result_one("SELECT mspacepath FROM {$tblprefix}members WHERE mid='$mid'");
    	if(!_08_FileSystemPath::CheckPathName($dir)) clear_dir(M_ROOT.$mspacedir.'/'.$dir,true);
		
		//删除会员的相关推送信息
		cls_pusher::DelelteByFromid($this->info['mid'],'members');
		
		/* // 删除-附件 (文档的附件已经删除，主要是会员资料等附件) ??? 
		if($iskeep){
			$query = $db->query("SELECT * FROM {$tblprefix}userfiles WHERE mid='$mid'");
			while($r = $db->fetch_array($query)){
				atm_delete($r['url'],$r['type']);
			} 
			$db->query("DELETE FROM {$tblprefix}userfiles WHERE mid='$mid'",'UNBUFFERED'); 
		}*/
		
		// 删除-会员表 
		$db->query("DELETE FROM {$tblprefix}members_{$this->info['mchid']} WHERE mid='$mid'",'UNBUFFERED');
		$db->query("DELETE FROM {$tblprefix}members_sub WHERE mid='$mid'",'UNBUFFERED');
		$db->query("DELETE FROM {$tblprefix}members WHERE mid='$mid'",'UNBUFFERED');
		// 
		$this->init();
	}
	function push($paid){
		if(cls_pusher::SourceNeedAdv($paid)){
			$this->detail_data();
		}
		return cls_pusher::push($this->info,$paid);
	}
	
	function handgroup($gtid,$ugid=0,$endstamp=-1,$updatedb = 0){//-1按会员组有效期0无限期>0实际输入时间
		global $timestamp;
		$grouptypes = cls_cache::Read('grouptypes');
		if(!$this->info['mid'] || empty($grouptypes[$gtid]) || $grouptypes[$gtid]['mode'] > 1) return;
		$mchid = $this->info['mchid'];
		if($ugid && !in_array($mchid,explode(',',$grouptypes[$gtid]['mchids']))){
			$usergroups = cls_cache::Read('usergroups',$gtid);
			if(in_array($mchid,explode(',',$usergroups[$ugid]['mchids'])) && ($endstamp <= 0 || $endstamp > $timestamp)){
				$this->updatefield('grouptype'.$gtid,$ugid);
				$this->updatefield('grouptype'.$gtid.'date',$endstamp == -1 ? ($usergroups[$ugid]['limitday'] ? ($timestamp + $usergroups[$ugid]['limitday'] * 86400) : 0) : $endstamp);
			}else $ugid = 0;
		}else $ugid = 0;
		if(!$ugid){
			$this->updatefield('grouptype'.$gtid,0);
			$this->updatefield('grouptype'.$gtid.'date',0);
		}
		$updatedb && $this->updatedb();
	}
	function mtcidsarr(){
		$na = cls_mtconfig::Config();
		$re = array();
		foreach($na as $k => $v){
			if((!$v['mchids'] || in_array($this->info['mchid'],explode(',',$v['mchids']))) && $this->pmbypmid($v['pmid'])) $re[$k] = $v['cname'];
		}
		return $re;
	}
	function isadmin(){
		if(!$this->info['mid'] || !$this->info['checked']) return false;
		return $this->info['grouptype2'] || $this->info['isfounder'];
	}
    
    /**
     * 判断当前用户是否登录
     * 
     * @return bool 如果已经登录返回TRUE，否则返回FALSE
     * @since  nv50
     */
    public function isLogin()
    {
        if (empty($this->info['mid']))
        {
            return false;
        }
        
        return (bool) $this->info['mid'];
    }
	
    /**
     * 分析管理角色权限，指定$Type则返回指定类型的权限数组，否则返回完整的权限数组。
     */
	function aPermissions($Type = ''){
		$TypeArray = array('menus','funcs','caids','mchids','fcaids','cuids','checks','extends',);
		if(!$Type){
			if(empty($this->apms)){
				foreach($TypeArray as $var) $this->apms[$var] = !empty($this->info['isfounder']) ? array('-1') : array();
				if(empty($this->info['isfounder'])){
					$amconfigs = cls_cache::Read('amconfigs');
					$ausergroup = cls_cache::Read('usergroup',2,@$this->info['grouptype2']);
					$a_amconfig = array();
					
					//计算多角色累加权限
					if(($ids = @$ausergroup['amcids'].','.@$this->info['amcids']) && $ids = array_unique(array_filter(explode(',',$ids)))){
						foreach($ids as $v){
							if(!empty($amconfigs[$v])){
								foreach($amconfigs[$v] as $k => $z){
									if(empty($a_amconfig[$k])){
										$a_amconfig[$k] = $z;
									}elseif($z) $a_amconfig[$k] .= ",$z";
								}
							}
						}
					}
					if($a_amconfig){
						foreach($TypeArray as $var){
							if($a_amconfig[$var]){
								$this->apms[$var] = array_unique(explode(',',$a_amconfig[$var]));
							}else unset($this->apms[$var]);
						}
					}else $this->apms = array();
				}
			}
			return $this->apms;
		}else{
			if(!in_array($Type,$TypeArray)) return array();
			$this->aPermissions();
			return empty($this->apms[$Type]) ? array() : $this->apms[$Type];
		}
	}
	
	//管理后台内容管理权限，识别指定类型id的内容管理权限，返回无权限原因
	function NoBackPmByTypeid($typeid,$type = 'caid'){
		if($this->info['isfounder']) return '';
		$na = array('caid' => '栏目','mchid' => '会员类型','fcaid' => '副件分类','cuid' => '交互类型',);
		if(!isset($na[$type])) return '指定了错误的内容权限类型';
		if(array_intersect(array(-1,$typeid),$this->aPermissions("{$type}s"))){
			return '';
		}else return self::NoBackMessage("指定{$na[$type]}");
	}
	
	//管理后台功能权限，返回无权限原因
	function NoBackFunc($name){
		if(!empty($this->info['isfounder'])) return '';
		if(array_intersect(array(-1,$name),$this->aPermissions('funcs'))){
			return '';
		}else{
			$amfuncs = cls_cache::exRead('amfuncs');
			$na = array();
			foreach($amfuncs as $k => $v){
				foreach($v as $k0 => $v0){
					$na[$k0] = $v0;
				}
			}
			return self::NoBackMessage(empty($na[$name]) ? '当前项目' : "{$na[$name]}($name)");
		}
	}
	private static function NoBackMessage($content){
		$re = '您没有 "'.$content.'" 的管理后台权限。<br>';
		$re .= '请管理员(创始人或后台权限管理员)通过以下方式(之一)进行权限配置：<br>';
		$re .= "1) 调整您所在的管理组，或为您单独附加相关的管理角色：<a href=\"?entry=amembers&action=edit&isframe=1\" target=\"_blank\">>>进入</a><br>";
		$re .= "2) 调整您所在管理组的所赋管理角色：<a href=\"?entry=usergroups&action=usergroupsedit&gtid=2\" onclick=\"return floatwin('open_grouptypesedit',this)\">>>进入</a><br>";
		$re .= "3) 调整管理角色的详细配置：<a href=\"?entry=amconfigs&action=amconfigsedit&isframe=1\" target=\"_blank\">>>进入</a><br>";
		return $re;
	}	
	function basedeal($dname,$mode=1,$count=1,$reason='',$updatedb=0){//会员操作之后的积分基本策略的处理,$mode为1添加0为删除
		$currencys = cls_cache::Read('currencys');
		if(!$this->info['mid']) return;
		$crids = array();
		foreach($currencys as $k => $v){
			if($v['available'] && !empty($v['bases'][$dname])) $crids[$k] = $mode ? $count * $v['bases'][$dname] : -$count * $v['bases'][$dname];
		}
		$crids && $this->updatecrids($crids,$updatedb,$reason ? $reason : '积分增减策略');
	}
	function paydeny($aid,$isatm=0){//$isatm为1，表示为附件
		$grouptypes = cls_cache::Read('grouptypes');
		if(empty($this->info['mid'])) return false;
		foreach($grouptypes as $gtid => $grouptype){//免费订阅
			if(!$grouptype['forbidden'] && !empty($this->info['grouptype'.$gtid])){
				$usergroup = cls_cache::Read('usergroup',$gtid,$this->info['grouptype'.$gtid]);
				if(!empty($usergroup['deny'.($isatm ? 'atm' : 'arc')])) return true;
			}
		}
		if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}subscribes WHERE aid='$aid' AND mid='".$this->info['mid']."' AND isatm='$isatm'")) return true;
		return false;
	}
	function payrecord($aid,$isatm=0,$cridstr='',$updatedb=0){
		global $db,$tblprefix,$timestamp;
		if(empty($this->info['mid'])) return;
		$db->query("INSERT INTO {$tblprefix}subscribes SET
				mid='".$this->info['mid']."',
				mname='".$this->info['mname']."',
				aid='$aid',
				cridstr='$cridstr',
				isatm='$isatm',
				createdate='$timestamp'");
	}
	function checkforbid($var){//禁止返回flase
		if(!$var || empty($this->info['grouptype1']) || !($usergroup = cls_cache::Read('usergroup',1,$this->info['grouptype1']))) return true;
		return in_array($var,explode(',',$usergroup['forbids'])) ? false : true;
	}
	function check_allow($var){
		$grouptypes = cls_cache::Read('grouptypes');
		if(!$var || !$this->info['mid']) return 0;
		if($this->info['isfounder']) return 1;
		foreach($grouptypes as $k => $v){
			if(!$v['forbidden'] && $this->info["grouptype$k"] && $usergroup = cls_cache::Read('usergroup',$gtid,$this->info['grouptype'.$gtid])){
				if(in_array($var,explode(',',$usergroup['allows']))) return 1;
			}
		}
		return 0;
	}

	// 用于判断文档/交互等的自动审核
	// 0:不自动审核,1:自动审核,负数:权限方案
	function pmautocheck($pmid=0){
		return $pmid < 0 ? $this->pmbypmid(-$pmid) : $pmid;
	}

	//请使用pmbypmid替换，为了兼容旧版本，暂时保留下来。
	function pmbypmids($pname,$pmid=0){
		return mem_pmbypmid($this->info,$pmid);
	}

	//根据权限方案分析权限，只返回true(有权限)/false(无权限)，，使用noPm可返回无权限的原因
	function pmbypmid($pmid=0){
		return mem_pmbypmid($this->info,$pmid);
	}
	//根据权限方案分析权限，在无权限时将返回原因，有权限则返回false
	function noPm($pmid = 0){
		return cls_Permission::noPmReason($this->info,$pmid);
	}

	//确认当前会员是否有权限在指定类目($sarr)添加指定模型($chid)文档
	//在无权限时将返回原因，否则返回false
	function arcadd_nopm($chid,$sarr=array()){
		if(!$this->checkforbid('issue')) return '对不起，您被禁言了';
		if(!$chid || !($channel = cls_channel::Config($chid))) return '请指定要发布的文档类型';
		if($re = $this->noPm($channel['apmid'])) return $re;
		foreach($sarr as $k =>$v){
			if($k == 'caid'){
				if(!$a = cls_cache::Read('catalog',$v)) return '请指定要发布在哪个栏目';
				if($a['isframe']) return '栏目['.$a['title'].']中不能发布文档';
				if(!in_array($chid,explode(',',$a['chids']))) return $channel['cname'].'不能发布到栏目['.$a['title'].']';
			}elseif($coid = intval(str_replace('ccid','',$k))){
				$cotypes = cls_cache::Read('cotypes');
				if(empty($cotypes[$coid]) || $cotypes[$coid]['self_reg']) continue;
				if(!empty($sarr["ccid$coid"]) && $ccids = array_filter(explode(',',$sarr["ccid$coid"]))){
					foreach($ccids as $x){
						if(!($a = cls_cache::Read('coclass',$coid,$x))) return '指定的['.$cotypes[$coid]['cname'].']分类不存在';
						if($a['isframe']) return '分类['.$a['title'].']中不能发布文档';
						if(!in_array($chid,explode(',',@$a['chids']))) return $channel['cname'].'不能发布到分类['.$a['title'].']';
					}
				}
			}
		}
	}
	
	//已审核的会员在本系统内设置登录标记及记录，
	//$expires：登录有效周期(秒)
	public function OneLoginRecord($expires = 0,$updatedb = true){
		global $timestamp,$onlineip,$memberid,$client_t;//$client_t为客户端时间
		if(!$this->info['mid'] || $this->info['checked'] != 1) return false;
		$this->updatefield('lastvisit', $timestamp);
		$this->updatefield('lastip', $onlineip);
        # 保证每个用户必须要有自己的salt
        if ( empty($this->info['salt']) )
        {
            $this->updatefield('salt', cls_string::Random(6));
        }
		$updatedb && $this->updatedb();
		
		if(!($expires = empty($expires) ? 0 : intval($expires))) $expires = 365 * 86400;
		$expires > 0 && !empty($client_t) && $expires = intval(floatval($client_t) / 1000) - $timestamp + $expires;
		$expires < 0 && $expires = 0; 
		cls_userinfo::LoginFlag($this->info['mid'],$this->info['password'],$expires);
		$memberid = $this->info['mid'];
		$this->resetErrorMsession();
	}				
	
	//设置登录标记
	public static function LoginFlag($mid = 0,$md5_password = '',$expires = 31536000){
		if(!$mid || !$md5_password) return;
		msetcookie('userauth', authcode("$md5_password\t$mid",'ENCODE'),$expires);
	}
	
	//设置退出标记
	public static function LogoutFlag(){
	    global $target;
        
        mclearcookie('trusteeship');
        # 只退出代管用户
        if ( $target === 'atrusteeship' )
        {
            return true;
        }
        
        # 如果是QQ登录时同时退出QQ登录
        if ( isset($_SESSION['openid']) )
        {
            unset($_SESSION['openid']);
        }
        
        $hash = cls_env::getHashValue();
        # 如果是微信登录时同时退出微信登录
        if ( isset($_SESSION[$hash]) )
        {
            unset($_SESSION[$hash]);
        }
        
		mclearcookie();
	}
	
	//登录时合并、同步UC及本系统会员，同时同步登录到UC
	//之前需要做好mname,password的检测
	//返回出错信息
	public function UCLogin($mname,$password,$_ucre = array()){
		$re = '';
		if(!$mname || !$password) return '请输入帐号及密码';
		$_ucre = cls_ucenter::checklogin($mname,$password);//UC登录的预检测//??如UC与本系统有同名会名，但UC通不过检测的话，将不能再登录
		if(!empty($_ucre['error'])) return $_ucre['error'];//在UC中验证通不过的话，中止所有登录操作
		if(isset($_ucre['uid'])){
			$md5_password = _08_Encryption::password($password);
			if($_ucre['uid'] == -1){//本系统有该会员，但UC中不存在，则在UC中注册一个新会员
				if($this->info['mid'] && $md5_password == $this->info['password']){
					cls_ucenter::register($mname,$password,$this->info['email'],TRUE);//注册并同步登录
				}
			}elseif($_ucre['uid'] > 0){//在UC中通过了会员验证
                $user = new cls_UserbaseDecorator($this);
				if($this->info['mid']){//使用UC中的帐号与密码更新本系统
					$user->synUpdateLocalData($password, $_ucre['email']);
				}else{//在本系统添加一个新会员
					$user->synAddLocalUser($mname, $password, $_ucre['email']);
				}
                unset($user);
				cls_ucenter::login($_ucre['uid']);//执行同步登录
			}
		}
		return $re;
	}
	
	//预处理mname,password,email，
	//opmode操作模式:add(新增会员)/edit(修改会员)/login(会员登录)
	public static function CheckSysField($value,$type = 'mname',$opmode = 'add', $mid = 0){
		global $db,$tblprefix,$censoruser,$mcharset, $unique_email;
		$re = array('value' => $value,'error' => '');
		switch($type){
			case 'mname':
				if($opmode == 'edit') return self::_returnError('帐号不允许修改',$re);
				$re['value'] = $value = empty($value) ? '' : trim(strip_tags($value));
				if(empty($value)) return self::_returnError('帐号不能为空',$re);
				$_len = cls_string::CharCount($value);// 其它系统编码跟当前系统不同时, 请先转化为当前系统编码
				if($_len < 3 || $_len > 15) return self::_returnError('帐号长度应为3-15字节',$re);
				if($opmode != 'login'){//注册或增加会员时需要处理的
					$guestexp = '\xA1\xA1|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
					if(preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\s\t\<\>\&]|$guestexp/is",$value)) return self::_returnError('帐号不合规范',$re);
					if(!defined('M_ADMIN') && $censoruser){//管理后台可以添加被禁止的帐号
						$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censoruser = trim($censoruser)), '/')).')$/i';
						if($censoruser && @preg_match($censorexp,$value)) return self::_returnError('帐号被禁止使用',$re);
					}
					if($msg = cls_ucenter::checkname($value)) return self::_returnError($msg,$re);
                    # 验证WINDID服务端用户名
                    if((int)($code = cls_WindID_Send::getInstance()->checkUserInput($value, 1)) < 0)
                    {
                        return self::_returnError( cls_Windid_Message::get($code), $re );
                    }
					if($db->result_one("SELECT mid FROM {$tblprefix}members WHERE mname='$value'")) return self::_returnError('帐号已被注册了',$re);
				}
			break;
			case 'password':
				$re['value'] = $value = empty($value) ? '' : trim($value);
				if(!$value) return $opmode == 'edit' ? $re : self::_returnError('请输入密码',$re);//修改模式下，密码为空表示不作修改，正常返回结果
				if($opmode != 'login'){
					$_len = cls_string::CharCount($value);//???其它系统编码跟当前系统不同时
					if($_len > 15) return self::_returnError('密码长度应小于15字节',$re);
					if($value != addslashes($value)) return self::_returnError('密码不合规范',$re);
                    # 验证WINDID服务端密码
                    if((int)($code = cls_WindID_Send::getInstance()->checkUserInput($value, 2)) < 0)
                    {
                        return self::_returnError( cls_Windid_Message::get($code), $re );
                    }
				}
			break;
			case 'email':
				$re['value'] = $value = empty($value) ? '' : trim($value);
				if(empty($value)) return self::_returnError('请输入Email',$re);
                
                # 一个邮箱只能注册一个用户
                if ( empty($unique_email) )
                {
                    $mid = @intval($mid);					
                    $db->select('mid')->from('#__members')->where(array('email' => $value));
                    if( $mid && ($opmode == 'edit') )
                    {
						$db->_and("mid != {$mid}");
                    }
					$uid = $db->exec()->fetch();
                    if ( $uid )
                    {
                         return self::_returnError('该邮箱已经被其它用户使用！',$re);
                    }
                }
                
                # 验证WINDID服务端Email
                if((int)($code = cls_WindID_Send::getInstance()->checkUserInput($value, 3)) < 0)
                {
                    return self::_returnError( cls_Windid_Message::get($code), $re );
                }
				if(!cls_string::isEmail($value)) return self::_returnError('Email不合规范',$re);
			break;
		}
		return $re;
	}
	protected static function _returnError($error,$re = array()){
		$re['error'] = $error;
		return $re;
	}
	
	//生成重新发送激活邮件的url
	public static function SendActiveEmailUrl($mname,$email,$forward = ''){
		global $cms_abs;
		if(empty($mname) || empty($email)) return '';
		$re = $cms_abs.'tools/memactive.php?action=sendemail';
		$re .= '&mname='.rawurlencode($mname);
		$re .= '&email='.rawurlencode($email);
		$forward && $re .= '&forward='.rawurlencode($forward);
		return $re;
	}
	
	//向指定会员发送激活邮件，邮件可以是指定的其它邮箱
	//info中需要有mid,mname,email
	public static function SendActiveEmail($info = array()){
		global $timestamp,$cms_abs,$db,$tblprefix;
		if(empty($info['mid']) || empty($info['mname']) || empty($info['email'])) return;
		$confirmid = cls_string::Random(6);
		$db->query("UPDATE {$tblprefix}members_sub SET confirmstr='$timestamp\t2\t$confirmid' WHERE mid='{$info['mid']}'");
		$db->query("UPDATE {$tblprefix}members SET checked='2' WHERE mid='{$info['mid']}'");
		mailto($info['email'],'member_active_subject','member_active_content',array(
		'mid' => $info['mid'],'mname' => $info['mname'],'url' => "{$cms_abs}tools/memactive.php?action=emailactive&mid={$info['mid']}&confirmid=$confirmid")
		);
	}
	
	//确认当前会员是否有权限在指定类目($sarr)添加指定模型($chid)文档
	//只返回true(有权限)/false(无权限)，如需要返回无权限的原因，请使用 arcadd_nopm
	function allow_arcadd($chid,$sarr=array()){
		return $this->arcadd_nopm($chid,$sarr) ? false : true;
	}
	function upload_capacity(){
		global $pm_upload,$nouser_capacity;
		if(!$this->info['mid']) return empty($nouser_capacity) ? 0 : $nouser_capacity;
		if($this->info['isfounder']) return -1;//不限容量
		if(!$this->checkforbid('upload') || !$this->pmbypmid(@$pm_upload)) return 0;
		$maxsize1 = 1;$maxsize2 = 0;
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if(!$v['forbidden'] && !empty($this->info["grouptype$k"])){
				$arr = cls_cache::Read('usergroup',$k,$this->info["grouptype$k"]);
				empty($arr['maxuptotal']) && $maxsize1 = 0;
				$maxsize2 = max($maxsize2,$arr['maxuptotal']);
			}
		}
		return empty($maxsize1) ? -1 : max(0,$maxsize2 * 1024 - $this->info['uptotal']);//空间余量(K)
	}
	function updateuptotal($upsize,$reduce=0,$updatedb=0){//$upsize以k为单位
		if(!$this->info['mid']) return;
		$this->updatefield('uptotal',!$reduce ? ($this->info['uptotal'] + $upsize) : max(0,$this->info['uptotal'] - $upsize));
		$updatedb && $this->updatedb();
	}
	function saving($crid,$mode=0,$value = 0,$remark = ''){
		if(empty($value) || empty($this->info['mid'])) return;
		$this->updatecrids(array($crid => $mode ? -$value : $value),1,$remark,1);
	}
	function updatecrids($crids=array(),$updatedb=0,$remark='',$mode=0){//mode为1表示为手动充扣
		global $db,$tblprefix,$timestamp;
		if(empty($this->info['mid'])) return;
		$currencys = cls_cache::Read('currencys');
		if(empty($crids) || !is_array($crids)) return;
		$curuser = cls_UserMain::CurUser();
		foreach($crids as $k => $v){
			if(!$v || ($k && empty($currencys[$k]))) continue;
			$nn = $this->info["currency$k"] + $v;
			$this->updatefield("currency$k",$nn > 0 ? $nn : 0);
			$db->query("INSERT INTO {$tblprefix}currency$k SET
					value='$v',
					mid='".$this->info['mid']."',
					mname='".$this->info['mname']."',
					fromid='".$curuser->info['mid']."',
					fromname='".$curuser->info['mname']."',
					createdate='$timestamp',
					mode='$mode',
					remark='".($remark ? $remark : '其它原因')."'");
		}
		$this->autogroup();
		$updatedb && $this->updatedb();
	}
	function crids_enough($crids=array()){
		if(empty($this->info['mid'])) return false;
		if(empty($crids)) return true;
		foreach($crids as $k => $v){
			if($v < 0 && $this->info['currency'.$k] < abs($v)) return false;
		}
		return true;
	}
	// 定义[CONVMEMBER]常量,用于会员模型转化(升级)等
	function updatefield($fieldname,$newvalue,$tbl='members'){
		if(empty($this->info['mid'])) return false;
		if($tbl == 'members_sub' && !$this->subed){
			$this->sub_data();
		}elseif($tbl == "members_{$this->info['mchid']}" && !$this->detailed){
			$this->detail_data();
		}
		if(defined('CONVMEMBER') || $this->info[$fieldname] != stripslashes($newvalue)){
			$this->info[$fieldname] = stripslashes($newvalue);
			$this->updatearr[$tbl][$fieldname] = $newvalue;
			return true;
		}else return false;
	}
	function autogroup(){
		global $timestamp;
		if(!$this->info['mid']) return;
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if($v['mode'] == 2){
				$nid = 0;
				if(!in_array($this->info['mchid'],explode(',',$v['mchids']))){
					$arr = cls_cache::Read('usergroups',$k);
					foreach($arr as $x => $y){
						if($this->info['currency'.$v['crid']] >= $y['currency'] && in_array($this->info['mchid'],explode(',',$y['mchids']))){
							$nid = $x;
							break 1;
						}
					}
				}
				$nid == $this->info["grouptype$k"] || $this->updatefield("grouptype$k",$nid);
			}
			if($this->info["grouptype{$k}date"] && $this->info["grouptype{$k}date"] < $timestamp){
				$this->updatefield("grouptype$k",0);
				$this->updatefield("grouptype{$k}date",0);
			}
		}
		$this->groupclear();
	}
	function groupclear($updatedb = 0){
		global $timestamp;
		if(!$this->info['mid']) return;
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if($this->info["grouptype{$k}date"] && $this->info["grouptype{$k}date"] < $timestamp){
				$ovid = $ovday = 0;
				if($this->info["grouptype$k"] && ($oug = cls_cache::Read('usergroup',$k,$this->info["grouptype$k"])) && !empty($oug['overugid'])){
					if($nug = cls_cache::Read('usergroup',$k,$oug['overugid'])){
						$ovid = $oug['overugid'];
						$ovday = $oug['limitday'];
					}
				}
				$this->updatefield("grouptype$k",$ovid);
				$this->updatefield("grouptype{$k}date",$ovday ? ($timestamp + $ovday * 86400) : 0);
			}
		}
		$updatedb && $this->updatedb();
	}
	function InitCurrency(){
		$currencys = cls_cache::Read('currencys');
		$crids = array();foreach($currencys as $k => $v) $v['available'] && $v['initial'] && $crids[$k] = $v['initial'];
		$crids && $this->updatecrids($crids,0,'会员注册初始积分。');
	}	
	function InitMtcid(){
		$this->updatefield('mtcid',($mtcid = array_shift(array_keys($this->mtcidsarr()))) ? $mtcid : 0);
	}	
	function groupinit($updatedb = 0){
		global $timestamp;
		if(!$this->info['mid']) return;
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if(!$v['issystem'] && !$this->info['grouptype'.$k] && $v['mode'] != 2){
				if(!in_array($this->info['mchid'],explode(',',$v['mchids']))){
					$arr = cls_cache::Read('usergroups',$k);
					foreach($arr as $x => $y){
						if($y['autoinit'] && in_array($this->info['mchid'],explode(',',$y['mchids']))){
							$this->updatefield('grouptype'.$k,$x);
							$y['limitday'] && $this->updatefield('grouptype'.$k.'date',$timestamp + $y['limitday'] * 86400);
							break;
						}
					}
				}
			}else if(!$v['issystem'] && !$this->info['grouptype'.$k] && $v['mode'] == 2){
				$this->autogroup();
			}
		}
		$updatedb && $this->updatedb();
	}
	function nogroupbymchid(){//当会员模型变化后，检查原先的会员组是否生效
		if(!$this->info['mid']) return;
		$mchid = $this->info['mchid'];
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			if($this->info["grouptype$k"]){
				if(!in_array($mchid,explode(',',$v['mchids']))){
					$ug = cls_cache::Read('usergroup',$k,$this->info["grouptype$k"]);
					if(in_array($mchid,explode(',',$ug['mchids']))) continue;//只这种情况才维持原会员组
				}
				$this->updatefield("grouptype$k",0);
				$this->updatefield("grouptype{$k}date",0);
			}
		}
	}
	
	function autoletter($updatedb=0){
		$mchannel = cls_cache::Read('mchannel',$this->info['mchid']);
		if(isset($mchannel['autoletter']) && $mchannel['autoletter']){
			$this->detail_data();
			if(isset($this->info[$mchannel['autoletter']]) && isset($this->info['letter'])){
				$this->updatefield('letter',autoletter($this->info[$mchannel['autoletter']]));
			}
		}
		$updatedb && $this->updatedb();
	}
	
	function updatedb(){
		global $db,$tblprefix;
		if(empty($this->info['mid'])) return;
		$this->autoletter();
		foreach(array('members','members_sub',"members_{$this->info['mchid']}") as $tbl){
			if(!empty($this->updatearr[$tbl])){
				$sqlstr = '';foreach($this->updatearr[$tbl] as $k => $v) $sqlstr .= ($sqlstr ? "," : "").$k."='".$v."'";
				$sqlstr && $db->query("UPDATE {$tblprefix}$tbl SET $sqlstr WHERE mid=".$this->info['mid']);
			}
		}
		$this->updatearr = array();
	}
    
    public function getter($name)
    {
        if (property_exists($this, $name))
        {
            return $this->$name;
        }
        
        return null;
    }
    
    /**
     * 获取会员支付帐号信息
     * 
     * @param  int   $mid 要获取的会员ID
     * @return array      返回获取到的支付帐号信息
     */
    public function getPaysInfo( $mid, $type = 'alipay' )
    {
        $mid = max(1, (int) $mid);        
        $row = $this->_db->select('salt')->from('#__members')->where(array('mid' => $mid))->limit(1)->exec()->fetch(); 
        switch (strtolower($type))
        {
        	case 'alipay': // 支付宝
                if ($mid === 1)
                {
                    if (!empty($this->_mconfigs['cfg_alipay_keyt']))
                    {
						  $cfg_alipay_keyt = authcode($this->_mconfigs['cfg_alipay_keyt'], 'DECODE', $row['salt']);
						 //使用临时变量，保证二次进入这个函数时，$this->_mconfigs['cfg_alipay_keyt']的值还是和缓存里一样，避免二次解密
                    }
                    
                    return array('alipay_partnerid' => @$this->_mconfigs['cfg_alipay_partnerid'], 
								 'alipay_partnerkey' => @$cfg_alipay_keyt,
                                 'alipay_seller_account' => @$this->_mconfigs['cfg_alipay']);
                }
                else
                {                	
                    $rowPays = $this->_db->select('alipay_seller_account, alipay_partnerid, alipay_partnerkey')
                                         ->from('#__pays_account')
                                         ->where(array('id' => $mid))
                                         ->limit(1)
                                         ->exec()->fetch();       
                    $rowPays['alipay_partnerkey'] = authcode($rowPays['alipay_partnerkey'], 'DECODE', $row['salt']);
                }
                break;
            default : // 财付通
                if ($mid === 1)
                {
                    if (!empty($this->_mconfigs['cfg_tenpay_keyt']))
                    {
                        $this->_mconfigs['cfg_tenpay_keyt'] = authcode($this->_mconfigs['cfg_tenpay_keyt'], 'DECODE', $row['salt']);
                    } 
                    return array('tenpay_partnerkey' => @$this->_mconfigs['cfg_tenpay_keyt'],
                                 'tenpay_seller_account' => @$this->_mconfigs['cfg_tenpay']);
                }
                else
                {
                    $rowPays = $this->_db->select('tenpay_seller_account, tenpay_partnerkey')
                                         ->from('#__pays_account')
                                         ->where(array('id' => $mid))
                                         ->limit(1)
                                         ->exec()->fetch();       
                    $rowPays['tenpay_partnerkey'] = authcode($rowPays['tenpay_partnerkey'], 'DECODE', $row['salt']);
                }
            	break;
        }
        
        return $rowPays;
    }
    
    public function __construct( $mchid = 0 )
    {
        global $timestamp, $onlineip;
        $this->_db = _08_factory::getDBO();
        $this->_mconfigs = cls_cache::Read('mconfigs');
        $this->_timestamp = $timestamp;
        $this->_onlineip = $onlineip;
    }
}
