<?php
!defined('M_COM') && exit('No Permission');
class cron_clr_weixin extends cron_exec{    
	public function __construct(){
	    parent::__construct();
		$this->main();
    }
	function main(){
		$stamp1d = TIMESTAMP-86400*1; //1天
		$stamp3d = TIMESTAMP-86400*3; //3天
		$stamp5d = TIMESTAMP-86400*5; //5天
		$stamp30d = TIMESTAMP-86400*30; //30天
		$this->db->query("DELETE FROM {$this->tblprefix}weixin_qrcode WHERE ctime<'$stamp1d'"); //临时二维码
		$this->db->query("DELETE FROM {$this->tblprefix}weixin_msgget WHERE ctime<'$stamp30d'"); //接收信息记录
		$this->db->query("DELETE FROM {$this->tblprefix}weixin_msgsend WHERE ctime<'$stamp30d'"); //发送(回复)信息记录
	}
}