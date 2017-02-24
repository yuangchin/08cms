<?php
/**
 * 会员列表扩展
 *
 * @example   请求范例URL：http://192.168.1.11/house/index.php?/ajax/pageload_users/aj_model/m%2C2%2C1/aj_check/1/aj_pagenum/1/aj_pagesize/4/datatype/json/ordermode/0/aj_whrfields/xingming%2Clike%2C%3Blxdh%2Clike%2C%3Bszqy%2Clike%2C/aj_deforder/grouptype16%20DESC%2Clastactive%20DESC/aj_thumb/thumb/caid/3/callback/?/domain/192.168.1.11
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2015 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pageload_users extends _08_M_Ajax_pageload_Base{
    
	public function __toString(){
		
		//初始化及模拟da处理
		$this->_initDa();
		if($this->mcfgs[0]!=='m' || !in_array($this->mcfgs[1],array(2,3,11,12,13))) die("Error::mchid=$this->mcfgs[1]"); //注意根据模型判断
        //加载模版函数
        include_once(cls_tpl::TemplateTypeDir('function').'utags.fun.php');
		//常规sql条件
		$this->_getSql(); 
        $this->_getOrder();
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} 
		//全部sql及结果
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit"; //echo "\n$sql\n<br>\n";
		$result = $this->_getData($sql); 
		$result = $this->_fmtData($result); 
        return $result; 
		
    }
	
    public function _fmtData($result){
		if(empty($result)) return $result;
		$mctypes = cls_cache::Read('mctypes');
		$ugarr17 = cls_cache::Read('usergroups',17);
		foreach($result as $k=>$v){
			foreach(array(1,2,3) as $ck){ //会员认证
				if(!empty($v["mctid$ck"])){
					$mctype = $mctypes[$ck];
					$result[$k]["mctid{$ck}icon"] = cls_url::tag2atm($mctype['icon']);
				}
			}
			if(!empty($v["grouptype17"])){ //等级
				$ugid = $v["grouptype17"]; 
				$result[$k]["grouptype17icon"] = cls_url::tag2atm($ugarr17[$ugid]['ico']);
				
			}
			if($szqy = $v["szqy"]){ //服务区域
				$szqya = cls_cache::Read('coclasse',1,$szqy);
				$result[$k]["szqytitle"] = $szqya['title'];
			}
			if($pid4 = $v["pid4"]){ //服务区域
				$user = new cls_userinfo;
				$user->activeuser($pid4,0);
				$result[$k]["pid4title"] = $user->info['cmane'];
				$result[$k]["pid4mspacehome"] = $user->info['mspacehome'];
			}
			foreach(array(2,3) as $hk){ //会员认证
				$key = "cntchid$hk";
				$$key = cls_DbOther::ArcLimitCount($hk, '', 'valid', $v['mid']);
				$result[$k][$key] = $$key;
			}
		}
		return $result;
	}
}