<?php
!defined('M_COM') && exit('No Permission');
class cron_yuyue extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){

		$_now_time = TIMESTAMP; 
		
		//*
		//更新出租房源的刷新时间
		$sqlin = "SELECT aid,MAX(refreshtime) AS refreshtime 
				  FROM {$this->tblprefix}commu_yuyue where refreshtime<='$_now_time' and chid='2' GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}archives11 a,($sqlin) t
				SET a.refreshdate=t.refreshtime WHERE a.aid=t.aid";

		$this->db->query($sql);
		//更新出售房源的刷新时间
		$sqlin = "SELECT aid,MAX(refreshtime) AS refreshtime 
				  FROM {$this->tblprefix}commu_yuyue where refreshtime<='$_now_time' and chid='3' GROUP BY aid";
		$sql = "UPDATE {$this->tblprefix}archives16 a,($sqlin) t
				SET a.refreshdate=t.refreshtime WHERE a.aid=t.aid";

		$this->db->query($sql);

		//删除cummu_yuyue表里面过期的数据
		$this->db->query("DELETE FROM {$this->tblprefix}commu_yuyue WHERE refreshtime <= '$_now_time'");
		//把yuyue字段处理
		//目的：当表yuyue里面没有某房源的预约数据时，房源列表显示‘已约’修改为“预约”
		$this->db->query("UPDATE {$this->tblprefix}archives11 SET yuyue = '0' where yuyue = '1' AND aid not in (SELECT DISTINCT aid FROM {$this->tblprefix}commu_yuyue)");
		$this->db->query("UPDATE {$this->tblprefix}archives16 SET yuyue = '0' where yuyue = '1' AND aid not in (SELECT DISTINCT aid FROM {$this->tblprefix}commu_yuyue)");

		
	}	
}
