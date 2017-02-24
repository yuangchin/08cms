<?php
!defined('M_COM') && exit('No Permission');
class cron_ip_banned extends cron_exec{    
	public function __construct(){
		parent::__construct();		
        $this->main();  
    }
	public function main(){
		cls_CacheFile::Update('bannedips',1); 
		}
}


