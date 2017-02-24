<?php
!defined('M_COM') && exit('No Permission');
class cron_update_refreshes extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		
		$this->db->query("UPDATE {$this->tblprefix}members SET refreshes = '0' ");
		
	}	
}
