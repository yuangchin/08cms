<?php
!defined('M_COM') && exit('No Permission');
class cron_sitemap extends cron_exec{    
	public function __construct(){
		parent::__construct();
		$this->main();  
    }
	public function main(){
		# 需要优化这个计划任务，结合周期与计划任务周期，每次只执行一个等?????????????
		$sitemaps = cls_cache::Read('sitemaps');
		foreach($sitemaps as $k => $v){
		cls_SitemapPage::Create(array('map' => $k,'inStatic' => true));
		}
	}
}

