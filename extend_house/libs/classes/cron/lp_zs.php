<?php
!defined('M_COM') && exit('No Permission');
class cron_lp_zs extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." SET lpczsl='0', czzdj='0', czzgj='0', czpjj='0', lpesfsl='0', csjdj='0', csjgz='0', cspjj='0'";
		$this->db->query($sql);
		
		$timestamp = TIMESTAMP; 
		//'lpczsl'] = $r['z'];//楼盘出租房源数量
		//'czzdj'] = $r['d'];//出租最低价
		//'czzgj'] = $r['g'];//出租最高价
		//'czpjj'] = round($r['p'],2);//出租平均价
		$sqlin = "SELECT pid3,COUNT(*) AS z,MIN(zj) AS d,MAX(zj) AS g,ROUND(AVG(zj),2) AS p FROM {$this->tblprefix}".atbl(2)." 
				WHERE checked=1 AND (enddate=0 OR enddate>$timestamp) GROUP BY pid3";
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." a,($sqlin) t
				SET a.lpczsl=t.z, a.czzdj=t.d, a.czzgj=t.g, a.czpjj=t.p
				WHERE a.aid=t.pid3";
		#echo $sql; 
		$this->db->query($sql);
		
		//'lpesfsl'] = $r['z'];//楼盘出售房源数量
		//'csjdj'] = $r['d'];//出售最低价
		//'csjgz'] = $r['g'];//出售最高价
		//'cspjj'] = round($r['p'],2);//出售平均价
		// ? 出售平均价 用 dj, zj ? 
		$sqlin = "SELECT pid3,COUNT(*) AS z,MIN(zj) AS d,MAX(zj) AS g,ROUND(AVG(dj),2) AS p FROM {$this->tblprefix}".atbl(3)." 
				WHERE checked=1 AND (enddate=0 OR enddate>$timestamp) GROUP BY pid3";
		$sql = "UPDATE {$this->tblprefix}".atbl(4)." a,($sqlin) t
				SET a.lpesfsl=t.z, a.csjdj=t.d, a.csjgz=t.g, a.cspjj=t.p
				WHERE a.aid=t.pid3";
		#echo $sql; 
		$this->db->query($sql);
		#echo '<br>'.(microtime(1)-$t1);
		
	}	
}
