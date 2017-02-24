<?php
// 08cms推送位相关

class cls_w08PushBase{

	static $push_fields = array( //这里处理通用字段；各系统，对返回的数据扩展
		'title' => array('subject','abstract','cominfo'), //推送位-描述字段来源(可系统扩展)
		'url' =>  array('url','logo','mlogo'), //推送位-图片地址字段来源(可系统扩展)
		'desc' =>  array('abstract','description','cominfo'), //推送位-描述字段来源(可系统扩展)
		'picurl' => array('thumb','logo','mlogo'), //推送位-图片地址字段来源(可系统扩展)
	);
   
	static function getPushArea(){
		$wmcfg = cls_cache::exRead('wxconfgs');
		$areas = $wmcfg['sys_confgs']['push_more'];
		$data = array();
		foreach($areas as $paid=>$v){
			$pcfg = cls_PushArea::Config($paid);
			$data[$paid]['cname'] = $pcfg['cname'];
			$data[$paid]['data'] = cls_w08push::getPushData($paid,99,0);
		}
		return $data;
	}
	
	// from db 查询 推送数据 (click菜单,后台群发信息使用)
	static function getPushData($paid='',$limit=9,$more=1){
		$db = _08_factory::getDBO();
		$pcfg = cls_PushArea::Config($paid);
		$data = array();
    	$db->select()->from("#__$paid")
              ->where('checked=1')
              //->_and(array('m.checked'=>1))
			  ->order('vieworder')->limit($limit)
              ->exec();
		while($row = $db->fetch()){
			$tmp = array();
			$tmp['dpush'] = $row;
			$tmp['dfrom'] = cls_w08push::getPushFromData($row['fromid'],$pcfg['sourcetype'],$pcfg['sourceid']);
			unset($tmp['dfrom']['content']);
			cls_w08push::getPushField($tmp);
			$data[] = $tmp; 
    	}
		$more && cls_w08push::getPushMore($paid,$data); //组[更多>>]项?... 使用参数？扩展？...
		return $data;
	}
	//得到一条文档/会员/交互/类目数据
    static function getPushFromData($pid,$type,$modid=0){
		$pid = intval($pid);
		$pinfo = array();
		if($type=='archives'){
			$arc = new cls_arcedit;
			$arc->set_aid($pid,array('au'=>0,'ch'=>1));
			$pinfo = $arc->archive;
			$pinfo && cls_ArcMain::Parse($pinfo);	
		}elseif($type=='members'){
			$user = new cls_userinfo;
			$user->activeuser($pid,1);
			$pinfo = $user->info;
		}elseif($type=='catalogs'){
			$pinfo = cls_catalog::Config($modid,$pid);
		}elseif($type=='commus'){
			$cucfg = cls_cache::Read('commu',$modid);
			$cutab = $cucfg['tbl'];
			$pinfo = $db->select()->from("#__$cutab")->where('checked=1')->exec()->fetch();
		} //print_r($pinfo);
		return $pinfo; 
	}
	// 从原始数据中获取一个字段的值
    static function getPushField(&$tmp){ 
		$mdata = array_merge($tmp['dfrom'],$tmp['dpush']);
		foreach(cls_w08push::$push_fields as $key=>$fields){
			$tmp[$key] = ''; 
			foreach($fields as $fid){
				if(isset($mdata[$fid])){ 
					$tmp[$key] = $mdata[$fid];
					break;
				}
			} //print_r("\n::".$tmp[$key]);
		}
		$tmp['pushid'] = $mdata['pushid'];
		if(!empty($tmp['url'])){
			$tmp['url'] = cls_url::view_url($tmp['url']);
		}
		if(!empty($tmp['picurl'])){
			$oldarr = explode('#', $tmp['picurl']);
        	$tmp['picurl'] = cls_url::tag2atm($oldarr[0]);
		}
	}
	// 组more链接
    static function getPushMore($paid,&$data){ 
		$wmcfg = cls_cache::exRead('wxconfgs');
		if(isset($wmcfg['sys_confgs']['push_more'][$paid])){
			$link = cls_w08Basic::fmtUrl($wmcfg['sys_confgs']['push_more'][$paid]);
			$row = array(
				'title' => '更多......>>', 
				'desc' => '', 
				'picurl' => '', 
				'url' => $link
			);
			$data[] = $row;
		}
	}

}
