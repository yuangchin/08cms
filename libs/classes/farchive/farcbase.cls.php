<?php
defined('M_COM') || exit('No Permission');
class cls_farcbase{
	var $aid = 0;
	var $archive = array();
	var $chid = 0;
	var $catalog = array();
	var $detailed = 0;
	var $auser = '';
	var $updatearr = array();
	function init(){
		$this->aid = 0;
		$this->chid = 0;
		$this->archive = array();
		$this->catalog = array();
		$this->detailed = 0;
		$this->auser = '';
		$this->updatearr = array();
	}
	function set_aid($aid,$edit = 1,$ttl = 0){
		global $db,$tblprefix;
		$this->init();
		$aid = max(0,intval($aid));
		if($aid && $this->archive = $db->fetch_one("SELECT * FROM {$tblprefix}farchives WHERE aid='$aid'",$ttl)){
			$this->aid = $aid;
			$this->chid = $this->archive['chid'];
			if($r = $db->fetch_one("SELECT * FROM {$tblprefix}farchives_{$this->chid} WHERE aid='$aid'",$ttl)){
				$this->archive = array_merge($r,$this->archive);
				unset($r);
			}
			$this->catalog = cls_fcatalog::Config($this->archive['fcaid']);
			if($edit) $this-> arcuser();
			else cls_url::arr_tag2atm($this->archive,'f');
		}
		return $this->aid;
	}
	function arcuser(){
		if(!$this->auser){
			$this->auser = new cls_userinfo;
			$this->auser->activeuser($this->archive['mid']);
		}
	}
	function arcadd($chid = 0,$fcaid = 0){
		global $db,$tblprefix,$timestamp;
		$curuser = cls_UserMain::CurUser();
		if(!($chid = cls_fchannel::InitID($chid))) return 0;
		if(!($fcaid = cls_fcatalog::InitID($fcaid))) return 0;
		$db->query("INSERT INTO {$tblprefix}farchives SET chid='$chid',fcaid='$fcaid',mid='{$curuser->info['mid']}',mname='{$curuser->info['mname']}',createdate='$timestamp'");
		if($aid = $db->insert_id()){
			$db->query("INSERT INTO {$tblprefix}farchives_$chid SET aid='$aid'");
			$this->set_aid($aid,1);
			$this->auser->basedeal('farchive',1,1,'副件发布',1);
			return $aid;
		}else return 0;
	}
	function autocheck(){
		$this->auser->pmautocheck($this->catalog['autocheck']) && $this->arc_check(1,0);
	}
	function arcformat(){
		global $infohtmldir;
		$u = empty($this->archive['customurl']) ? (empty($this->catalog['customurl']) ? '{$infodir}/a-{$aid}-{$page}.html' : $this->catalog['customurl']) : $this->archive['customurl'];
		return cls_url::m_parseurl($u,array('aid' => $this->aid,'infodir' => $infohtmldir,'y' => date('Y',$this->archive['createdate']),'m' => date('m',$this->archive['createdate']),'d' => date('d',$this->archive['createdate']),));
	}
	function arccolor(){
		global $color;
		if($color){
			$this->updatefield('color',$color == '#' ? '' : $color);
		}
	}
	function arc_check($checked=1,$updatedb=0){
		if(empty($this->aid)) return;
		$curuser = cls_UserMain::CurUser();
		if($this->archive['checked'] == $checked) return;
		$this->updatefield('checked',$checked);
		$this->updatefield('editor',$curuser->info['mname']);
		$updatedb && $this->updatedb();
	}
	function arc_delete($onlynotcheck=0){
		global $db,$tblprefix,$infohtmldir;
		if(empty($this->aid)) return;
		if($onlynotcheck && $this->archive['checked']) return; 
		$this->archive['arcurl'] && m_unlink($infohtmldir.'/'.substr($this->archive['arcurl'],0,-6).'{$page}.html');//删除相应的静态文件
		$db->query("DELETE FROM {$tblprefix}farchives_{$this->chid} WHERE aid='".$this->aid."'", 'UNBUFFERED');
		$db->query("DELETE FROM {$tblprefix}farchives WHERE aid='".$this->aid."'", 'UNBUFFERED');
		$this->init();
	}
	
	/*用于操作多选类系字段处理 
	mode 添加或者删除或者重设模式 ids表单指定的ID组合 coid类系ID cuname自定类目字段名字 	$tbl更新字段的表
	smode=0 默认是用类系里的是否多选配置
	*/
	function set_column($mode,$ids,$coid,$cuname,$tbl='farchives',$smode=0){
		$cotypes = cls_cache::Read('cotypes');
		$idarr = idstr_mode($mode,!$smode?$cotypes[$coid]['asmode']:$smode,$ids,$this->archive[$cuname],1);
		$this->updatefield($cuname,$idarr,$tbl);
	}
	
	function updatefield($fieldname,$newvalue,$tbl='farchives'){
		if(isset($this->archive[$fieldname]) && ($this->archive[$fieldname] != stripslashes($newvalue))){
			$this->archive[$fieldname] = stripslashes($newvalue);
			$this->updatearr[$tbl][$fieldname] = $newvalue;
		}
	}
	function updatedb(){
		global $db,$tblprefix,$timestamp;
		if(empty($this->aid)) return;
		$this->updatearr && $this->updatearr['farchives']['updatedate'] = $timestamp;
		foreach(array('farchives',"farchives_{$this->chid}") as $tbl){
			if(!empty($this->updatearr[$tbl])){
				$sqlstr = '';foreach($this->updatearr[$tbl] as $k => $v) $sqlstr .= ($sqlstr ? "," : "").$k."='".$v."'";
				$sqlstr && $db->query("UPDATE {$tblprefix}$tbl SET $sqlstr WHERE aid={$this->aid}");
			}
		}
		$this->updatearr = array();
	}
	function unstatic(){
		global $db,$tblprefix,$infohtmldir;
		if(empty($this->aid) || !$this->archive['arcurl']) return false;
		m_unlink($this->arcformat());
		$db->query("UPDATE {$tblprefix}farchives SET arcurl='' WHERE aid='".$this->aid."'");
		return true;
	}
	function tostatic(){
		$re = cls_FarchivePage::Create(array('arc' => $this,'inStatic' => true));
		return $re;
	}
}