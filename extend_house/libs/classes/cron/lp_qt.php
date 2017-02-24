<?php
!defined('M_COM') && exit('No Permission');
class cron_lp_qt extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		
  
		$this->db->query("UPDATE {$this->tblprefix}archives15 SET ayss=0,adps=0,lpczsl=0,lpesfsl=0");
		
		
		//楼盘内交互数量统计：意向(ayss),楼盘关注，印象ayxs
		$commu = cls_cache::Read('commu',3);
		$sqlin = "SELECT aid,COUNT(*) AS z FROM {$this->tblprefix}$commu[tbl] GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." a,($sqlin) t
				SET a.ayss=t.z WHERE a.aid=t.aid";
		#echo $sql; 
		$this->db->query($sql);
		
		
		//楼盘内交互数量统计：留言
		//游客的页算？mname != '' AND mid != '' 
		$commu = cls_cache::Read('commu',48); 
		$sqlin = "SELECT aid,COUNT(*) AS z FROM {$this->tblprefix}$commu[tbl] WHERE tocid = '0' and mname != '' GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." a,($sqlin) t
				SET a.adps=t.z WHERE a.aid=t.aid";
		#echo $sql; 
		$this->db->query($sql);
		
		
		//楼盘内合辑数量统计：问答 
		//$commu = cls_cache::Read('commu',2);
		$sqlin = "SELECT b.pid AS pid, COUNT(*) AS z FROM {$this->tblprefix}".atbl(106)." a INNER JOIN {$this->tblprefix}aalbums b ON b.inid=a.aid WHERE b.arid='1' GROUP BY b.pid";
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." a,($sqlin) t
				SET a.awds=t.z WHERE a.aid=t.pid";
		$this->db->query($sql);
		
		//楼盘合辑：
		//lpczsl ：楼盘内出租房源数量x
		//lpesfsl ：楼盘内二手房源数量x
		$chids = array('lpczsl'=>'2','lpesfsl'=>'3');
		foreach($chids as $k => $v){
		$sqlin = "SELECT a.pid3 AS pid, COUNT(*) AS z FROM {$this->tblprefix}".atbl($v)." a  GROUP BY a.pid3";
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." a,($sqlin) t
				SET a.".$k."=t.z WHERE a.aid=t.pid";
		$this->db->query($sql);
		}
		
	}	
}

