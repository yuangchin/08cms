<?php
!defined('M_COM') && exit('No Permission');
class cron_buynum extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		$na = array('103'=>'33','105'=>'35','14'=>'45');//103商品模型对应交互项目33，105团购模型对应交互项目35,14团购模型对应交互项目45。
		foreach($na as $k=>$v){
			$commu = cls_cache::Read('commu',$v);
			if($k==14){
				$this->db->query("UPDATE {$this->tblprefix}".atbl($k)." a,(SELECT COUNT(*) num,aid FROM {$this->tblprefix}".$commu['tbl']." GROUP BY aid) b SET awgs=b.num WHERE a.aid=b.aid");
			}else{
				$this->db->query("UPDATE {$this->tblprefix}".atbl($k)." a,(SELECT COUNT(*) num,aid FROM {$this->tblprefix}".$commu['tbl']." GROUP BY aid) b SET anlinum=b.num WHERE a.aid=b.aid");
			}
		}

	}
}
