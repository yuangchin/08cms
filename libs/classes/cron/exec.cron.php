<?php
defined('M_COM') || exit('No Permission');
class cron_exec{
	protected $db;
	protected $tblprefix;
	public function __construct(){
		$this->db = _08_factory::getDBO();
		$this->tblprefix = cls_env::getBaseIncConfigs('tblprefix');
	}
}


