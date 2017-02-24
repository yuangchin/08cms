<?php
defined('M_COM') || exit('No Permission');
class cls_ArchivePage extends cls_ArchivePageBase{ 
	
	# 获得页面模板
	protected function _ParseSource(){
		$this->_ParseSource = $this->_Arc->tplname($this->_SystemParams['addno']); 
		// -------------------- 扩展开始 : (楼盘,二手,出租按类系获取模版), 排除手机版
		if(in_array($this->_MainData['chid'],array(2,3,4)) && !defined('IN_MOBILE')){ 
			$chids = array(4=>array('楼盘',12),3=>array('二手',43),2=>array('出租',44),);
			$cc_tpl_cfgs = cls_cache::Read('cc_tpl_cfgs','','',0,1); 
			$arc_tpls = cls_cache::Read('arc_tpls'); 
			$addno = $this->_SystemParams['addno'];
			$chid = $this->_MainData['chid']; 
			$coid = $chids[$chid][1]; 
			$ccid = $this->_fmtCcid($coid); //_MainData["ccid$coid"]; 
			if($tid = @$cc_tpl_cfgs[$coid][$ccid]){
				$tcfg = @$arc_tpls[$tid]['cfg']; 
				if(!empty($tcfg[$addno]['tpl'])){
					$this->_Arc->arc_tpl = $arc_tpls[$tid];	
					$this->_ParseSource = $tcfg[$addno]['tpl'];
				}else{
					$this->_Arc->arc_tpl = array();	
					$this->_ParseSource = '';
				}
			} //print_r($tcfg);
		}
		// --------------------  扩展结束
		if(!$this->_ParseSource){
			throw new cls_PageException($this->_PageName().' - 未绑定模板');
		}
	}
	
	// 临时：对多选项 取第一个数字
	protected function _fmtCcid($coid){
		$ccid = $this->_MainData["ccid$coid"];
		if(strstr($ccid,',')) $ccid = substr($ccid,strpos($ccid,',')+1);
		return intval($ccid);
	}
	
}
