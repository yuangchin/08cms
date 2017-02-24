<?php
/**
 * 会员收藏文档会员列表
 *
 * @example   请求范例URL：index.php?/ajax/pageload_ccid1/aj_model/co,1/caid/1/ccid10/1369/aj_unsets/chids,logo,wangyouyinxiang,maxyoudian,quedian,waiguan,neishi,kongjian,caokong,dongli,youhao,yuqingfenxi,zhuangpeigongyi,shouhou,anquan,qita,zongjie/aj_thumb/xitupian,100,80/aj_pagesize/10/aj_pagenum/2/domain/192.168.1.11/
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pageload_mcu extends _08_M_Ajax_pageload_Base{
	
    public function __toString(){
		//初始化及模拟da处理
		$this->_initDa(); 
        //加载模版函数
        include_once(cls_tpl::TemplateTypeDir('function').'utags.fun.php');
		//常规sql条件
		$this->_getSql(); 
        $this->_getOrder();
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} 
		// 扩展:id只在会员收藏的数据表中.
		$where .= $this->_mcuWhere(); 
		//全部sql及结果
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit"; //echo "\n$sql\n<br>\n";
		$result = $this->_getData($sql); 
        return $result; 
    }
	
	public function _mcuWhere(){
		$curuser = cls_UserMain::CurUser();
		$mid = $curuser->info['mid'];
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$mcfgs = $this->mcfgs;
		if($mcfgs[0]=='m'){
			$cuid = 11;
			$pfid = 'm.mid';
			$cufie = 'tomid';
		}elseif($mcfgs[0]=='a'){
			$cuid = $mcfgs[1]==4 ? 7 : 6;	
			$pfid = 'a.aid';
			$cufie = 'aid';
		}else{
			die('Error!');	
		}
		$cucfg = cls_cache::Read('commu',$cuid);
		$cutab = $cucfg['tbl'];
		$ids = ''; 
		$sql = "SELECT $cufie FROM {$tblprefix}$cutab WHERE mid='$mid'"; // AND incheck4=1
		$query = $db->query($sql); 
		while($row = $db->fetch_array($query)){    
			$ids .= ','.$row[$cufie];
		}
		$ids = empty($ids) ? "-1" : substr($ids,1); 
		$res = " AND $pfid IN($ids)";
		return $res;
	}
	
}