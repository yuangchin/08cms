<?php
!defined('M_COM') && exit('No Permission');
class cron_anlinum extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		
		$na = array('101'=>'31');//4楼盘模型对应合辑项目32，101设计师模型对应合辑项目31,102案例。
		$this->db->query("UPDATE {$this->tblprefix}".atbl(101)." SET anlinum=0 WHERE anlinum>0");
		foreach($na as $k=>$v){
			$this->db->query("UPDATE {$this->tblprefix}".atbl($k)." a,(SELECT COUNT(*) num,pid$v FROM {$this->tblprefix}".atbl(102)." GROUP BY pid$v having pid$v>0) b SET anlinum=b.num WHERE a.aid=b.pid$v");
		}

	}
}
