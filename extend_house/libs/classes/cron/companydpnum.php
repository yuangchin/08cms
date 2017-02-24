<?php
!defined('M_COM') && exit('No Permission');
class cron_companydpnum extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		//装饰公司点评数量
		$this->db->query("UPDATE {$this->tblprefix}members a,(SELECT COUNT(*) dpnum,tomid FROM {$this->tblprefix}commu_yezhupl GROUP BY tomid) b SET a.dpnum=b.dpnum WHERE a.mid=b.tomid");
	
		}
}
