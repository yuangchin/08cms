<?php
// 08cms菜单操作
// 如果08cms系统修改,就改这个文件，不用改wmp*文件

class cls_w08MenuBase extends cls_wmpMenu{
//class cls_w08Basic{
	
	public $oauth = NULL;
	
	function __construct($cfg=array()){
		parent::__construct($cfg); //$this->wxmenu = new cls_wecMenu($cfg);
		$ocfg = cls_w08Basic::getConfig(0, 'sid');
		$this->oauth = new cls_wmpOauth($ocfg);
	}
	
	// 批量生成菜单
	// 返回:res,nextid
	function batch($type,$nextid){ 
	  	define('WX_ERR_RETURN',1); //返回原始数组
		$db = _08_factory::getDBO(); //$db->setDebug();
		$query = $db->select()->from("#__weixin_config")->where(array('weixin_enable'=>1,'weixin_cache_id'=>$type))
		->_and(array('weixin_id'=>$nextid),'>=')
		->order('weixin_id')->limit(2)->exec();
		$cfgs = array();
		while($row = $db->fetch($query)){
			$cfgs[] = $row;
		}
		if(!empty($cfgs[0])){
			$wmDefCfg = cls_cache::exRead('wxconfgs');
			$wecfg = cls_w08Basic::getConfigFmt($cfgs[0]);
			//$wecfg = getConfig('weixin_id', $type='sid'); weixin_cache_id
			$wmDbCfg = cls_w08Menu::getMenuData($wecfg, ''); 
			$wmDefList = cls_w08Menu::getMenuDef($wmDbCfg, $wmDefCfg, $wecfg['cache_id']);
			$wem = new cls_w08Menu($wecfg); 
			$res = $wem->create($wmDefList); 
		}else{
			$res = array();	
			$wecfg = array();	
		} //echo "<pre>"; print_r($cfgs);
		$nextid = empty($cfgs[1]) ? 0 : $cfgs[1]['weixin_id'];
		return array('wecfg'=>$wecfg,'res'=>$res,'nextid'=>$nextid);
	}
	
	// 08格式的menu数组，
	function get(){ 
		$menu = $this->menuGet(); //print_r($menu);
		$re=array(); $i=0; $j=0; 
		if(isset($menu['menu']['button'])){
			$menu = $menu['menu']['button']; 
			foreach($menu as $k=>$v){
				$i++; $j=0;
				if(empty($v['sub_button'])){
					$val = isset($v['url']) ? $v['url'] : $v['key'];
					$re["{$i}0"] = array('type'=>$v['type'], 'name'=>$v['name'], 'val'=>$val, );
				}else{
					$re["{$i}0"] = array('name'=>$v['name']);
					foreach($v['sub_button'] as $k2=>$v2){
						$j++; 
						$val = isset($v2['url']) ? $v2['url'] : $v2['key'];
						$re["$i$j"] = array('type'=>$v2['type'], 'name'=>$v2['name'], 'val'=>$val, );
					}
				}
			}
		}
		return $re;
	}

	// {$mobileurl},{cms_abs},oauth=base/uinfo,
	function create($mcfg=array()){ 
		$menu = array(); $re = array(); 
		for($i=1;$i<=3;$i++){
			if(empty($mcfg["{$i}0"]['name'])) continue;
			$rei = array();
			$rei['name'] = $mcfg["{$i}0"]['name'];
			$subs = array();
			for($j=1;$j<=5;$j++){
				if(empty($mcfg["{$i}{$j}"]['name'])) continue;
				$name = $mcfg["{$i}{$j}"]['name'];
				$val = $this->fmtUrl($mcfg["{$i}{$j}"]['val']);
				$type = strpos($val,'://') ? 'view' : 'click'; //其它操作?! 
				$key = $type=='view' ? 'url' : 'key';
				$subs[] = array('type'=>$type, 'name'=>$name, $key=>$val, );
			}
			if(empty($subs)){
				$val = $this->fmtUrl($mcfg["{$i}0"]['val']);
				$type = $rei['type'] = strpos($val,'://') ? 'view' : 'click'; //其它操作?! 
				$key = $type=='view' ? 'url' : 'key';
				$rei[$key] = $val;
			}else{
				$rei['sub_button'] = $subs;
			}
			$re[] = $rei; 
		} //print_r($re);
		return $this->menuCreate($re);
	}

	// delete
	function del(){ 
		return $this->menuDelete();
	}
	
	//{$mobileurl},{cms_abs},oauth=base/uinfo, 此方法，扩展需求比较多…
	function fmtUrl($url){ 
		$url = str_replace(array("&amp;"),array("&"),$url); 
		$url = cls_w08Basic::fmtUrl($url,$this->cfg);
		if(strpos($url,'://')){
			if(!strpos($url,'?')) $url .= "?"; // 格式化后好加参数
			$fixs = array('oauth'=>'','state'=>''); 
			foreach($fixs as $key=>$v){
				preg_match("/$key=(\w+)/i",$url,$m);
				if(!empty($m)){
					$url = str_replace(array("?{$m[0]}","&{$m[0]}"),array("?",""),$url);
					$fixs[$key] = $m[1];
				}else{
					$fixs[$key] = '';	
				}
			} 
			$url .= '&is_weixin=1';
			if(!empty($fixs['oauth'])){
				if(!empty($this->cfg['fromid'])){ $url .= "&mp".$this->cfg['fromid_type']."=".$this->cfg['fromid']; }
				$url = $this->oauth->getCode($url, $fixs['oauth'], $fixs['state']); 
			}
		} //echo "\n::".urldecode($url);
		return $url;
	}
	
	function getMenuData($wecfg, $tab){ 
		$re = array(); 
		//if($type=='sid' && !empty($tab)) return $re; //未加aid,mid参数,点总站以外菜单时不要这个数据
		if(!empty($wecfg['fromid_type']) && $wecfg['fromid_type']=='sid'){ //总站用mcid=0配置
			$whr = array('mcid'=>'0');
		}elseif(!empty($wecfg['appid'])){ //单个商家/文档公众号配置
			$whr = array('appid'=>$wecfg['appid']);
		}else{ //if(!empty($tab)){ //默认商家/文档公众号配置
			$whr = array('mcid'=>$tab);
		}
		//if(empty($tab)){
			$db = _08_factory::getDBO();
			$row = $db->select()->from('#__weixin_menu')
				->where($whr)->exec();
			while($row = $db->fetch()){
				$re[$row['key']] = $row;
			} //print_r($re);
		//}
		return $re;
	}
	
	function getMenuDef($dbCfg, $defCfg, $tab=''){
		//print_r($dbCfg);
		//兼容之前:读tpl下cache
		$re = empty($dbCfg) ? (empty($defCfg[$tab]['default']) ? $defCfg['0']['default'] : $defCfg[$tab]['default']) : $dbCfg; 
		//print_r($re);
		return $re;
	}
	
	function getMenuPick($defCfg, $tab=''){ 
		$nowdef = empty($defCfg[$tab]['default']) ? array() : $defCfg[$tab]['default'];
		$npicks = empty($defCfg[$tab]['picks']) ? array() : $defCfg[$tab]['picks'];
		$data = $defCfg['sys_confgs']['picks']+$nowdef+$npicks; 
		$re = ''; $vals = ',';
		foreach($data as $key=>$v){
			if(empty($v['val'])) continue;
			if(strstr($vals,',('.$v['val'].'),')) continue;
			$re .= (empty($re) ? '' : ',')."['$v[name]','$v[val]']";
			$vals .= ',('.$v['val'].'),';
		}
		$re = "[$re];"; //print_r($re);
		return $re;
	}

}
