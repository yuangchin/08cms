<?php
!defined('M_COM') && exit('No Permission');
class cron_cmu_ansnum extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		$this->db->query("UPDATE {$this->tblprefix}members_sub ms,{$this->tblprefix}members m SET ansnum=0 
					WHERE m.mid=ms.mid AND m.grouptype34='106'");
		
		$sql = "UPDATE {$this->tblprefix}members_sub ms,{$this->tblprefix}members m,
		  (SELECT mid,COUNT(cid) AS stat FROM {$this->tblprefix}commu_answers 
		   WHERE checked='1' AND toaid='0' AND tocid='0' GROUP BY mid
		   ) t SET ms.ansnum=t.stat
		  WHERE m.mid=ms.mid AND m.mid=t.mid AND m.grouptype34='106'
		";
		$this->db->query($sql); 

	}
}
