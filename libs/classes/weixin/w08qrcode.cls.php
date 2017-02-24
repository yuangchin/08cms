<?php
// 事件响应操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08Qrcode extends cls_wmpQrcode{

	public $_db = NULL;
	public $qrexpired = '5'; //5分钟过期...

	function __construct($wecfg){ 
		parent::__construct($wecfg); 
		$this->_db = _08_factory::getDBO();
	}

    function getQrcode($smod, $type='temp', $extp=''){
		return $type=='temp' ? $this->getQTemp($smod,$extp) : $this->getQLimit($smod,$extp);
		/*
		这些数字区间，看怎样规划更合理...??? 
		// 1-10000               : 1-1万(固定二维码:保留区)
		// 10001-99999           : 10001-99999(固定二维码:使用区)
		//  100000<1000123       : 10-100万 保留(固定)
		// [1-9]+[999,999]       : 测试(临时二维码)
		// [100-428]+[9,999,999] : 正式使用(临时二维码)
		// 同类sid,5分钟内获取一个相同的ID,10分中后失效,
		
		//login
		//sendaid_7654321
		//sendmid_1234567
		//uparc_3
		//upcu_4
		*/
	}

	// 正式使用(临时二维码) [100-428]+[9,999,999] : 
	// 同类sid,5分钟内获取一个相同的ID,10分中后失效,(设置给微信的为最大值：7天（即604800秒）), 定时清理1天内的数据
    function getQTemp($smod, $extp=''){
		global $m_cookie;
		$timeNmin = TIMESTAMP-($this->qrexpired*60); //5分钟
		$row = $this->_db->select()->from('#__weixin_qrcode')->where(array('cuser'=>$m_cookie['msid'],'smod'=>$smod))
		 	->_and(array('ctime'=>$timeNmin),'>')->exec()->fetch();
		if($row){ 
			$this->_db->update('#__weixin_qrcode', array('ctime'=>TIMESTAMP))->where(array('cuser'=>$m_cookie['msid'],'smod'=>$smod))
				->_and(array('ctime'=>$timeNmin),'>')->exec();
			$sid = $row['sid']; 
			$ticket = $row['ticket']; //return $row['sid'];
		}else{
			$sid = mt_rand(1001000123,4289876999); 
			while($this->_db->select('sid')->from('#__weixin_qrcode')->where(array('sid'=>$sid))->exec()->fetch()){
				$sid = mt_rand(1001000123,4289876999); //如果访问量很多，是否计数等待??? 
			}
			$qrdata = $this->qrcodeTicket($sid, 'temp'); 
			$ticket = $qrdata['ticket']; 
			$data = array(
				'sid' => $sid,
				'smod' => $smod,
				'extp' => $extp, //Label
				'ticket' => $ticket,
				'ctime' => TIMESTAMP,
				'cuser' => $m_cookie['msid'],
			); 
			$this->_db->insert('#__weixin_qrcode', $data)->exec();
		}
		$ret = array('sid'=>$sid, 'ticket'=>$ticket, 'url'=>$this->qrcodeShowurl($ticket));
		return $ret;
	}

	// 正式使用(固定二维码) [10012,99987] :
	// 同类sid,5分钟内获取一个相同的ID,10分中后失效, (不用清理)
    function getQLimit($smod, $extp=''){
		global $m_cookie;
		$timeNmin = TIMESTAMP-($this->qrexpired*60); //5分钟
		$row = $this->_db->select()->from('#__weixin_qrlimit')->where(array('cuser'=>$m_cookie['msid'],'smod'=>$smod))
		 	->_and(array('ctime'=>$timeNmin),'>')->exec()->fetch();
		if($row){ 
			$this->_db->update('#__weixin_qrlimit', array('ctime'=>TIMESTAMP,'extp'=>$extp,))->where(array('cuser'=>$m_cookie['msid'],'smod'=>$smod))
			 	->_and(array('ctime'=>$timeNmin),'>')->exec();
			$sid = $row['sid']; 
			$ticket = $row['ticket'];
		}else{
			$sid = mt_rand(10012,99987); 
			while($this->_db->select('sid')->from('#__weixin_qrlimit')->where(array('sid'=>$sid))->_and(array('ctime'=>$timeNmin),'>')->exec()->fetch()){
				$sid = mt_rand(10012,99987); //如果访问量很多，是否计数等待??? 
			}
			$row = $this->_db->select()->from('#__weixin_qrlimit')->where(array('sid'=>$sid))->exec()->fetch(); //可能存在，可能不存在...
			$data = array(
				'smod' => $smod,
				'extp' => $extp, //Label
				'ctime' => TIMESTAMP,
				'cuser' => $m_cookie['msid'],
			); //print_r($row);
			if(empty($row['ticket'])){
				$qrdata = $this->qrcodeTicket($sid, 'fnum'); 
				$ticket = $qrdata['ticket']; 
				$data = $data + array(
					'sid' => $sid,
					'ticket' => $ticket,
				); 
				$this->_db->insert('#__weixin_qrlimit', $data)->exec();
			}else{
				$ticket = $row['ticket'];
				$this->_db->update('#__weixin_qrlimit', $data)->where(array('sid'=>$sid))->exec();
			}

		}
		$ret = array('sid'=>$sid, 'ticket'=>$ticket, 'url'=>$this->qrcodeShowurl($ticket));
		return $ret;
	}

}
