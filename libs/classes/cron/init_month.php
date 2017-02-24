<?php
!defined('M_COM') && exit('No Permission');
class cron_init_month extends cron_exec{    
	public function __construct(){
		parent::__construct();		
		$this->main();
    }
	public function main(){
		$na = stidsarr(1);
		foreach($na as $k => $v){
			if($ntbl = atbl($k,1)){
				$this->db->query("UPDATE {$this->tblprefix}$ntbl SET mclicks=0,mdowns=0,mplays=0");
			}
		}		
		}
}

