<?php
defined('M_COM') || exit('No Permission');
class cls_CnodePage extends cls_CnodePageBase{
	
	# 分析节点字串(扩展)
	protected function _Cnstr(){ 
		if($this->_inStatic) return; // (生成静态不需要这样处理)
		//备份原始cnstr
		$cnstr_bak = @$this->_SystemParams['cnstr']; 
		
		$caid = empty($this->_QueryParams['caid']) ? 0 : $this->_QueryParams['caid'];
		$addno = $this->_SystemParams['addno'];

		if($caid==2 && in_array($addno,array(1,2))){ //楼盘
			$this->_CnstrSkipCcids();
		}
		
		// array('0','0,1','0,2','0,6','0,43','0,1,4'),
		if($caid==3){ //二手房
			//$ccida = $this->_CnstrSingle(); 
			$this->_CnstrSkipCcids();
		} 
		
		if($caid==4){ //出租
			//$ccida = $this->_CnstrSingle(); 
			$this->_CnstrSkipCcids();
		} 
		
		if($caid==559){ //特价房
			$this->_CnstrSkipCcids(array(18));
		}
		
		if($caid==511){ //家装案例
			$this->_CnstrSkipCcids();
		}
		
		/*
		if($caid==516){ //问答及子类
			$this->_CnstrSkipCcids();
		}*/
		$catalogs = cls_cache::Read('catalogs');
		$pcaids = $caid ? cls_catalogbase::Pccids($caid,0,1) : array(-1); //资讯
		if(in_array($caid,$pcaids)){
			$this->_CnstrSkipCcids();
		}
		
		$cnstr_new = @$this->_SystemParams['cnstr'];
		if($cnstr_bak && $cnstr_bak!=$cnstr_new){
			$this->_Cfg['AllowStatic'] = 0;
		}
	}
	//*/

	# 节点字串处理(公用), cnstr没有交叉节点,如手机版,此时返回的要需要带进_CnstrSkipCcids里面的1个或0个类系ID(数组)
	# array():仅栏目caid为节点; array(1):仅ccid1为节点(无caid); array(20):仅ccid20为节点(无caid); 
	protected function _CnstrSingle(){ 
		if(!empty($this->_QueryParams['caid'])) return array();
		$coids = array(1,2,3,4,5,6,12,14,43,45); //可能组成节点的ccid，如过需要配置别的ccid节点，请在此扩展
		foreach($coids as $coid){
			$ccid = empty($this->_QueryParams['ccid'.$coid]) ? 0 : $this->_QueryParams['ccid'.$coid];
			if($ccid) return array($coid);
		}
		return array();
	}

	# 节点字串处理(公用), cnstr保留$keep中的ccidX,其它的去除
	protected function _CnstrSkipCcids($keep=array()){ 
		$cnstr = $this->_SystemParams['cnstr'];
		$cotypes = cls_cache::Read('cotypes');
		foreach($cotypes as $k=>$v){
			if(!empty($keep) && in_array($k,$keep)) continue; 
			$key = "ccid$k"; //去除类系组节点cnstr中的ccid$k
			$cnstr = preg_replace("/&(?:$key)=[^&]*|\b(?:$key)=[^&]*&?/", '', $cnstr);
		}
		$this->_SystemParams['cnstr'] = $cnstr;
	}
	
}
