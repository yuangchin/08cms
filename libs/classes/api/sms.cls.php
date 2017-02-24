<?php

/**
 * 手机短信接口类
 *
 * 说明：
 *
 * @author    08CMS
 */
 
class cls_sms{

	public  $cfg_mchar = 70; // 一条信息,文字个数(小灵通65个字)
	public  $cfg_mtels = 200; // 一次发送,最多200个手机号码个数
	public  $cfg_timeout = 3; //超时时间
	
	public  $api       = ''; //api接口类型(提供商)
	public  $smsdo     = NULL; //api对象
	public  $cfgs      = array(); //api配置
	public  $cfga      = array(); //用于设置
	
	//public function __destory(){  }
	public function __construct(){ 
		$sms_cfg_api = cls_env::mconfig('sms_cfg_api');
		$sms_cfg_uid = cls_env::mconfig('sms_cfg_uid');
		$sms_cfg_upw = cls_env::mconfig('sms_cfg_upw');
		$sms_cfg_pr3 = cls_env::mconfig('sms_cfg_pr3');
		$sms_cfg_pr4 = cls_env::mconfig('sms_cfg_pr4');
		$sms_cfg_pr5 = cls_env::mconfig('sms_cfg_pr5');
		$this->api = $api = !empty($sms_cfg_api) ? $sms_cfg_api : ''; 
		require M_ROOT."include/sms/basic_cfg.php"; // 加载配置,不用once,否则万一某个页面用两次则后一次加载不到
		$this->cfga = $sms_cfg_aset;
		$this->cfg_timeout = !empty($sms_cfg_tmieout) ? $sms_cfg_tmieout : '3';
		if(isset($sms_cfg_aset[$api])){
			$this->cfgs = $sms_cfg_aset[$api];
			$class = "sms_$api";
			$uid = !empty($sms_cfg_uid) ? $sms_cfg_uid : '';
			$upw = !empty($sms_cfg_upw) ? $sms_cfg_upw : '';
			$pr3 = !empty($sms_cfg_pr3) ? $sms_cfg_pr3 : '';
			$pr4 = !empty($sms_cfg_pr4) ? $sms_cfg_pr4 : '';
			$pr5 = !empty($sms_cfg_pr5) ? $sms_cfg_pr5 : '';
			// 统一实例化一个 api对象 // load sms libs
            _08_FilesystemFile::filterFileParam($api);
			require_once M_ROOT."include/sms/api_{$api}.php";
			cls_env::SetG('sms_cfg_tmieout',$this->cfg_timeout);
			$this->smsdo = new $class($uid,$upw,$pr3,$pr4,$pr5);
			$this->smsdo->timeout = $this->cfg_timeout;
		}	
	}
	
	/**
	 * 短信接口是否关闭
	 *
	 * @return	bool	---		0-开启,1关闭
	 *
	 **/
	public function isClosed(){
		if(!empty($this->cfgs)){
			return false;
		}else{
			return true;
		} //&&$sms_cfg_api!='(close)'
	}

    /**
     * 各模块是否启用 (无启用开关设置的，不能用这个方法；如：commtpl，membexp)
     * @param	string	$module 要启用的短信模块名称
     * @param	bool	$tpl    1 获取短信模板内容
     * @return	bool	---		0-开启,1关闭
     * @return	$smstpl	短信模板内容
     *
     * @author icms <icms@foxmail.com>
     **/
    public function smsEnable($module){
		if(is_numeric($module)){
			$module = "confirm$module";	
		}
        if($this->isClosed()){
            return false;
        }else{
            $smsenalbe = cls_cache::cacRead('smsconfigs',_08_USERCACHE_PATH);
            if (empty($smsenalbe[$module]['open'])) {
                return false;
            }else{
                return true;
            }
        }
    }

    /**
     * 获取各模块短信模板
     * @param $module 模块名称如：register，或会员认证ID如：1(转化为：confirm$mctid)
	 * @param $checkcode 是否认证码类型
     *
     * @author icms <icms@foxmail.com>
     */
    function smsTpl($module,$checkcode=1){
        if(empty($module)){
			$module = 'commtpl';
		}elseif(is_numeric($module)){
			$module = "confirm$module";	
		}
		$smscfgsave = cls_cache::cacRead('smsconfigs',_08_USERCACHE_PATH);
        $smstpl = !empty($smscfgsave[$module]['tpl']) ? $smscfgsave[$module]['tpl'] : (empty($checkcode) ? '' : @$smscfgsave['commtpl']['tpl'] );
		if(empty($smstpl) && $checkcode){ //模版为空且为认证码类型
			$hostname = cls_env::mconfig('hostname'); //很多接口要求签名,用这个默认签名
			$smstpl = '您的确认码为{$smscode}。本信息自动发送，请勿回复。【{$hostname}】';
		}
        return $smstpl;
    }

    /**
	 * 余额查询
	 * 结果说明：array(1,1234.5): 成功,余额为1234.5；array(-1,'失败原因'): 
	 *
	 * @return	array	---		结果数组	如：array(1,1234.5)
	 *
	 **/
	public function getBalance(){
		return $this->smsdo->getBalance();	
	}
	
	/**
	 * 短信发送，支持短信模版替换，
	 * 
	 * @param	string	$mobiles 	手机号码,参考sendSMS()
	 * @param	string	$tpl 		支持模版，如：{$subject}{$name}标记
	 * @param	array	$source		替换源：array('subject'=>'hellow 08cms!','name'=>'peace',)
	 * @param	string	$type 		发送方式/发送身份,参考sendSMS()
	 *
	 * @return	array	---		结果数组,参考sendSMS()
	 *
	 **/
	public function sendTpl($mobiles,$tpl,$source,$type='scom'){
		$tpl = str_replace(array("\r\n","\r","\n"),array(' ',' ',' '),$tpl);
		if(preg_match_all('/{\s*(\$[a-zA-Z_]\w*)\s*}/i', $tpl, $matchs)){
			if(!empty($matchs[0])){
				foreach($matchs[0] as $v){
					$k = str_replace(array('{','$','}'),'',$v);
					$val = isset($source[$k]) ? $source[$k] : (isset($GLOBALS[$k]) ? $GLOBALS[$k] : "{\$$k}");
					$tpl = str_replace($v,$val,$tpl);
				}
			}
		}
		return $this->sendSMS($mobiles,$tpl,$type);
	}
	
	/**
	 * 短信发送
	 * 
	 * @param	string	$mobiles 	手机号码,array/string(英文逗号分开)
	 * @param	string	$content 	255个字符以内
	 * @param	string	$type 		发送方式,发送身份 ：
	 *					scom=默认,普通会员发送,检测余额, 
	 *					sadm=管理员(不检测余额), 
	 *					ctel=手机认证(不检测登陆,每次一个号码,70字以内)
	 *					$mid=会员id(整数),以$mid的用户发送并扣余额,(!!!)调用发送的地方请控制好权限,否则,会扣完$mid的余额
	 *
	 * @return	array	---		结果数组,如：array(1,'操作成功'): 
	 *
	 **/
	public function sendSMS($mobiles,$content,$type='scom'){
		global $db,$tblprefix,$timestamp,$onlineip;
		$curuser = cls_UserMain::CurUser();
		// 格式化 $mobiles,$content, 
		$atel = $this->telFormat($mobiles);
		if($type=='ctel'){
			$amsg = $this->msgCount($content,$this->cfg_mchar);
			$atel = array($atel[0]); //只取第一个号码
		}else{
			$amsg = $this->msgCount($content);
		} //echo "::::"; print_r($atel);
		if(empty($atel)) return array('-2','号码不正确!');
		if(empty($amsg)) return array('-2','信息内容为空!');
		if($smax = $this->check_smax($atel)) return array('-2','同一号码一天内最大发送信息次数不能超过'.$smax.'次!');
		if($ipmax = $this->check_ipmax()) return array('-2','同一IP发送间隔太短,需要大于'.$ipmax.'秒!');
		$nmsg = count($atel)*$amsg[1];
		// 需扣费计算条数,检查余额
		$balance = $this->smsdo->getBalance();
		if((float)$balance[1]<=0){
			$mobiles = implode(',',$atel);
			$this->balanceWarn("--tels:$mobiles\n --cmsg:$content"); //写记录
			return array('-2','系统余额不足,请联系管理员!');		
		}
		$is_send = 1; // 指定会员mid的状态,可发送
		$m_id = $curuser->info['mid'];	
		$m_name = $curuser->info['mname'];	
		$m_charge = isset($curuser->info['sms_charge']) ? $curuser->info['sms_charge'] : 0;
		if(intval($type)){
			$send_user = new cls_userinfo;
			$send_user->activeuser($type, 1);
			$m_id = $send_user->info['mid'];	
			$m_name = $send_user->info['mname'];	
			$m_charge = $send_user->info['sms_charge'];
			if($nmsg>$m_charge){
				$is_send = 0; // 不可发送
			}
		}
		if($type=='scom'&&$nmsg>$m_charge){
			return array('-2','余额不足!');	
		}
		if($is_send){ // 以$mid的用户发送,该会员有余额才发送 
			// 超过最大能够发送的号码,分组发送
			if(count($atel)>$this->cfg_mtels){
				$groups = array_chunk($atel,$this->cfg_mtels);
				$res = array('-2','群发失败!');
				$flag = false; //成功标记
				foreach($groups as $group){ 
					$res_temp = $this->smsdo->sendSMS($group,$amsg[0]);
					if($res_temp[0]=='1'){ //只要一组发送成功,则都算成功.
						$res = $res_temp;	
					}
				}
			}else{
				$res = $this->smsdo->sendSMS($atel,$amsg[0]);
			}
			// 扣余额(条)
			if(($type=='scom'||intval($type)) && $res[0]=='1'){
				$sql = "UPDATE {$tblprefix}members SET sms_charge='".($m_charge-$nmsg)."' WHERE mid='$m_id'";
				$db->query($sql);
			}
			$restr = "".implode('|',$res)."|$nmsg";
		}else{ // $mid会员没有余额,不执行发送
			$res = array('-2','余额不足');
			$restr = "-2|余额不足|$nmsg";
		}
		// 写记录-db
		$stel = implode(',',$atel); 
		if(strlen($stel)>255) $stel = substr($stel,0,240).'...'.substr($stel,strlen($stel)-5,255);
		$sql = "INSERT INTO {$tblprefix}sms_sendlogs SET 
		  mid='".($type=='ctel' ? 0 : $m_id)."',mname='$m_name',stamp='$timestamp',ip='$onlineip',
		  tel='$stel',msg='".maddslashes($amsg[0],1)."',res='$restr',api='".$this->api."/$type',cnt='$nmsg'";
		$db->query($sql);
		// 扣钱 for 0test_balance.txt
		if($this->api=='0test' && $res[0]=='1'){
			$this->smsdo->deductingCharge($nmsg);
		}
		return $res;
	}
	
	//单个号码,一天内最多能接收短信的次数; 群发取前24个号码字符。
	//返回: 0:可发送, 具体数字（最大限额）不可发送
    public function check_smax($tel)
    {
		$db = _08_factory::getDBO();
		$smax = intval(cls_env::mconfig('sms_cfg_smax'));
		$smax || $smax = 10;
		if(is_array($tel)) $tel = implode(',',$tel);
		$row = $db->select('COUNT(*)')->from('#__sms_sendlogs')
			->where("stamp >= ".(TIMESTAMP-86400)."")
			->_and('tel')->like($tel, '_%')
			->exec()->fetch(); //var_dump($row['COUNT(*)']); ->setDebug()
		$cnt = empty($row['COUNT(*)']) ? 0 : $row['COUNT(*)'];
		$flag = $cnt >= $smax ? $smax : 0;
		return $flag;
	}
	
	//单个IP两次发送信息的最短时间间隔，0为不限制，请根据短信运营商设置。
	//返回: 0:可发送, 具体数字（最大限额）不可发送 $db->select('*')->from('#__members')->where('mname')->like('a')->_and('mname')->like('d')->exec()->fetch();
    public function check_ipmax()
    {
		$db = _08_factory::getDBO();
		$ipmax = intval(cls_env::mconfig('sms_cfg_ipmax')); ;
		if(empty($ipmax)) return false;
		$ip = cls_env::OnlineIP();
		$row = $db->select('stamp')->from('#__sms_sendlogs')
			->where(array('ip'=>$ip)) // "ip='$ip'"
			->_and('res')->like('1|OK!')
			->order("cid DESC")//->setDebug()
			->exec()->fetch(); //var_dump($row); 
		$stamp = empty($row['stamp']) ? 0 : $row['stamp'];
		$flag = TIMESTAMP-$stamp >= $ipmax ? 0 : $ipmax;
		return $flag;
	}
	
	/**
	 * 余额报警检测,余额报警记录
	 * 
	 * @param	int		$flag 	int/string数字/
	 *					数字,多少小时被修改(记录了余额不足)过,
	 *					flag=str,记录信息内容
	 *
	 * @return	NULL	
	 *
	 **/
	function balanceWarn($flag){
		global $db,$tblprefix,$timestamp,$onlineip; 
		$curuser = cls_UserMain::CurUser();
		$path = M_ROOT."dynamic/sms";  
		$file = "$path/balance_apiwarn.wlog"; 
		if(is_numeric($flag)){ //检查文件,多少时间(day)内修改过
			if(is_file($file)){ 
				$flag = $flag*24*3600; //天
				if($timestamp - filemtime($file) < $flag) return true;
				else return false;
			}else{
				return false;
			}
		}else{ 
			mmkdir($path,0);
			$data = '';
			if(is_file($file)){
				$data = file_get_contents($file);
			}
			$fp = fopen($file, 'wb');
			$data = date('Y-m-d H:i:s')."^ ".$curuser->info['mname']." ^ $onlineip \n $flag\r\n\r\n$data";
			flock($fp, 2); fwrite($fp, $data); fclose($fp);
		}
	}

	/**
	 * 电话号码 格式化/过滤
	 * 
	 * @param	array	$tel 	初始的电话号码array/string
	 * @return	array	$re		格式化并过滤后的电话号码
	 *
	 **/
	public function telFormat($tel){
		if(is_string($tel)){
			$tel = str_replace(array("-","("," ",')'),'',$tel);
			$tel = str_replace(array("\r\n","\r","\n",';'),',',$tel);
			$arr = explode(',',$tel);
		}else{
			$arr = $tel;	
		}
		$arr = array_filter($arr);
		$re = array();
		for($i=0;$i<count($arr);$i++){
			//  手机/^1\d{4,10}$/; 95168合法号码/^[1-9]{1}\d{4,10}$/; 0769-12345678小灵通
			if(preg_match('/^\d{5,12}$/',$arr[$i])) $re[] = $arr[$i];
		}
		return $re;	
	}
	/**
	 * 短信内容 截取/计数
	 * 
	 * @param	string	$msg 	初始的短信内容
	 * @param	int		$slen 	最多截取多少文字
	 * @return	array	$re		返回array(文字,信息条数,文字个数)
	 *
	 **/
	public function msgCount($msg,$slen=255){
		//global $mcharset; 
		$hostname = cls_env::mconfig('hostname'); //很多接口要求签名,用这个默认签名
		$msg = str_replace('{$hostname}',$hostname,$msg);
		$mcharset = cls_env::getBaseIncConfigs('mcharset');	
		$clen = $mcharset=='utf-8' ? 3 : 2; //中文宽度
		$cmax = min(array($slen,255)); //最多取255个字
		$n = strlen($msg); //php函数原始长度
		$p = 0; //指针
		$cnt = 0; // 计数,英文算一个字符
			for($i=0; $i<$n; $i++) {
				if($p>=$n) break; //结尾
				if($cnt>=$cmax) break; //最大文字个数
				if(ord($msg[$p]) > 127) { $p += $clen; }
				else { $p++; }
				$cnt++;
			}
			$msg = substr($msg,0,$p);
		if($cnt>$this->cfg_mchar){ // >70字
			$ncnt = ceil($cnt/($this->cfg_mchar-5)); //(70-3)个字算一条信息
			// (dxton.com开发文档) --- “短信长度”如何收费？
			// 70字符内1条收费，超70字符,按65字符/条，多条收费。(目前运营商行业标准）
		}else{
			$ncnt = 1;
		}
		return array($msg,$ncnt,$cnt); 
	}
	
	// 以下为某些接口 的扩展操作。
	public function login(){
		return $this->smsdo->login();
	}
	public function logout(){
		return $this->smsdo->logout();
	}
	public function chargeUp($charge){
		return $this->smsdo->chargeUp($charge);
	}

	//用inculde里面的http(),经常发不出去,用此替代
	static function getHttpData($url){
		$options = array(  
			CURLOPT_RETURNTRANSFER => true,  
			CURLOPT_HEADER         => false,  
			CURLOPT_POST           => true,  
			CURLOPT_POSTFIELDS     => '',  
		);  
		$ch = curl_init($url);  
		curl_setopt_array($ch, $options);  
		$html = curl_exec($ch);  
		curl_close($ch);
		return $html;	
	}

}
