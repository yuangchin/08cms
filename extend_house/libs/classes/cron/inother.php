<?php
!defined('M_COM') && exit('No Permission');
class cron_inother extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		
		//新房团购人数统计
		$commu = cls_cache::Read('commu',8);
		$this->db->query("UPDATE {$this->tblprefix}".atbl(5)." SET awgs='0'");
		$sqlin = "SELECT aid,COUNT(*) AS z FROM {$this->tblprefix}$commu[tbl] GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}".atbl(5)." a,($sqlin) t
				SET a.awgs=t.z WHERE a.aid=t.aid";
		#echo $sql; 
		$this->db->query($sql);
		
		//资讯评论统计
		$this->db->query("UPDATE {$this->tblprefix}".atbl(1)." SET adps='0'");
		$sqlin = "SELECT aid,COUNT(*) AS z FROM {$this->tblprefix}commu_zixun WHERE tocid='0' GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}".atbl(1)." a,($sqlin) t
				SET a.adps=t.z WHERE a.aid=t.aid";
		#echo $sql; 
		$this->db->query($sql);
		
		//已审核的看房报名人数统计
		$this->db->query("UPDATE {$this->tblprefix}".atbl(110)." SET awgs='0'");
		$sqlin = "SELECT aid,COUNT(*) AS z FROM {$this->tblprefix}commu_kanfang WHERE checked = '1' GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}".atbl(110)." a,($sqlin) t
				SET a.awgs=t.z WHERE a.aid=t.aid";
		#echo $sql; 
		$this->db->query($sql);
		
	}	
}
