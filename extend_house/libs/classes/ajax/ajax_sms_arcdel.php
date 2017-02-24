<?php
/**
 * 功能: 根据手机确认码 删除文档信息
 *
 * @example   /index.php?/ajax/sms_arcdel/mod/arcxdel/act/send/code/803634/tel/13223332244/ids/234,123,89
 * @author    peace@08cms
 * @copyright 2008 - 2014 08CMS, Inc. All rights reserved.
 *
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_sms_arcdel extends _08_M_Ajax_sms_msend_Base
{
	public $mod = 'arcxdel'; //固定
	public $act = 'send'; //固定，按send规范扩展，但这里不发短信，只根据aids删除文档 
	
    public function __toString()
    {       
		$this->sms = new cls_sms(); 
		$this->tpl = $this->sms->smsTpl($this->mod);
		$this->aids = empty($this->_get['aids']) ? '' : $this->_get['aids']; 
		//安全综合检测
		$re = $this->check_all(); 
		if($re['error']) return $re;
		//执行操作
		return $this->sms_delete();
    }
	
	// delete(删除信息)
	// chid : 文档模型
	// ids : 文档ids
	// 其它参数见:sms_send()
    public function sms_delete()
    {   
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$re = array('error'=>'', 'message'=>'');
		// 再次认证?, 'must':必须经过前一步发认证码
		$rc = $this->sms_check(1,'must');
		if($rc['error'] || empty($rc['tel']) || $rc['tel']!==$this->tel){
			return $rc;	
		} 
		if(@$rc['tel']!==$this->tel){
			return array('error'=>'checkNumber', 'message'=>"{$this->tel}号码错误");
		} 
		$ids = empty($this->_get['ids']) ? '' : $this->_get['ids']; //echo $ids;
		if(empty($ids)){ // || !in_array($chid,array(2,3,9,10))
			$re['error'] = 'ErrorParas';	
			$re['message'] = '参数错误';
			return $re;
		}
		$ids = explode(',',$ids); 
		$arc = new cls_arcedit; $cnt = 0;
		$arr = array('2'=>'11','3'=>'16','9'=>'24','10'=>'25');
		foreach($arr as $chid=>$tbid){
		foreach($ids as $aid){
			$aid = intval($aid); if(empty($aid)) continue;
			$sql = "SELECT lxdh FROM {$tblprefix}archives_$chid WHERE aid='$aid' AND lxdh='{$this->tel}'";
			$r = $db->fetch_one($sql); //echo "\n$sql\n"; //atbl($chid)
			if(!empty($r)){
				$arc->set_aid($aid,array('chid'=>$chid));
				$arc->arc_delete(0);
				$cnt++;
			}
		} }
		unset($arc);
    	if($cnt>0){ 
			$re['message'] = "{$cnt}条记录删除成功";
		}else{
			$re['error'] = 'ErrorDelete';	
			$re['message'] = "没有符合条件的记录";	
		}
		//清除cookie, 前台判断
		return $re;
	}
}