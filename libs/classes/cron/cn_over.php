<?php
!defined('M_COM') && exit('No Permission');
class cron_cn_over extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();
	}
	public function main(){
		$cotypes = cls_cache::Read('cotypes');
		foreach($cotypes as $k => $v){
			if($v['emode']){
				$na = stidsarr(1);
				foreach($na as $kk => $v){
					if($ntbl = atbl($kk,1)){
						$this->db->query("UPDATE {$this->tblprefix}$ntbl SET ccid$k=0,ccid{$k}date=0 WHERE ccid{$k}date<>0 AND ccid{$k}date< " . TIMESTAMP,'SILENT');
					}
				}
			}
		}
		}
}

