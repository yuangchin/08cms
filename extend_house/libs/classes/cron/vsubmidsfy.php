<?php
/*
  # 会员中心：统计：[function u_memberstat($mid,$minute = 60){//固定时间间隔统计会员的相关状况] 如下：
  $na['vesfys'] = $this->db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(3)." WHERE mid='$mid' AND chid=3 AND checked=1 AND (enddate=0 OR enddate>$timestamp)");
  $na['vczfys'] = $this->db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(2)." WHERE mid='$mid' AND chid=2 AND checked=1 AND (enddate=0 OR enddate>$timestamp)");
*/
!defined('M_COM') && exit('No Permission');
class cron_vsubmidsfy extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		
		$timestamp = TIMESTAMP;
		$cfgs = array('vesfys'=>3,'vczfys'=>2);

        $query = $this->db->query("SELECT mid FROM {$this->tblprefix}members_3");
        while($row = $this->db->fetch_array($query)){
			$mid =  $row['mid']; //echo $mid.', ';     
			$mids = get_subMids($mid);
			foreach($cfgs as $field=>$chid){
				$sql = "SELECT COUNT(*) FROM {$this->tblprefix}".atbl($chid)." WHERE mid IN($mids) AND checked=1 AND (enddate=0 OR enddate>$timestamp)";
				$cnt = $this->db->result_one($sql);
				$cnt && $this->db->result_one($sql);
				$this->db->query("UPDATE {$this->tblprefix}members_sub SET $field='$cnt' WHERE mid=$mid"); 
			}
        }
	}
}
