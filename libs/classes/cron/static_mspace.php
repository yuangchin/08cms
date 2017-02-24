<?php
!defined('M_COM') && exit('No Permission');
class cron_static_mspace extends cron_exec{    
	public function __construct(){
	    parent::__construct();
		$this->main();
		}
	public function main(){
		global $mspacedir,$mspacepmid;
		$query = $this->db->query("SELECT * FROM {$this->tblprefix}members WHERE mspacepath<>''");
		$mids = $dirs = array();
		while($r = $this->db->fetch_array($query)){
		if(empty($mspacepmid) || !mem_pmbypmid($r,$mspacepmid)){
			$mids[] = $r['mid'];
			$dirs[] = $r['mspacepath'];
		}
	}
	$mids && $this->db->query("UPDATE {$this->tblprefix}members SET mspacepath='',msrefreshdate=0 WHERE mid ".multi_str($mids));
	foreach($dirs as $v){
		if(!_08_FileSystemPath::CheckPathName($v)) clear_dir(M_ROOT."$mspacedir/$v",true);
		}   
		
		}
}



