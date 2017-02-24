<?php
!defined('M_COM') && exit('No Permission');
class cron_clr_session extends cron_exec{    
	public function __construct(){
	    parent::__construct();
		$this->main();
    }
	function main(){
		global $onlinehold;
		empty($onlinehold) && $onlinehold = 6;	
		$this->db->query("DELETE FROM {$this->tblprefix}msession WHERE mslastactive<(".TIMESTAMP."-$onlinehold*3600)");	
		}
}