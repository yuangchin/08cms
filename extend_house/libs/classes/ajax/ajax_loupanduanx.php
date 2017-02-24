<?php
/**
 * 楼盘订阅-短信扩展
 *
 * @author    Peace@08cms
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_loupanduanx extends _08_M_Ajax_cuAjaxPost_Base
{
   
	 
    public function __toString(){
		
		$this->cuaj_post_init();

		#扩展
		if(!in_array($this->cuid,array(3))) cls_message::show('参数错误！');		
		$oA = new cls_cuedit($this->defCfgs());  
		$oA->add_init($this->defPid(),'',array('setCols'=>1)); 
		$fmdata  = empty($this->_get['fmdata']) ? 0 : $this->_get['fmdata'];
		$quaid=$oA->pinfo;
		$arc = new cls_arcedit;
		$arc->set_aid($quaid['aid'],array('au'=>1,'ch'=>1,'chid'=>4));

		#扩展 约束等..
		//if($this->cuid==3){}
		
        $strfm=$this->_get;
		$fields=$this->extFields();
		$arr = array(); 
		foreach($strfm as $k=>$v){	
		  if(strpos($k,"[dyfl]")){
			$arr[] = $v;
		  }
		}
		$fields['dyfl'] = implode("\t",$arr); 

		$oA->sv_regcode("commu$this->cuid");
		$oA->sv_repeat($this->repCookie(), 'both'); // array('aid'=>$aid,'tocid'=>$tocid)
		$oA->sv_set_fmdata();//设置$this->fmdata中的值 
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$this->cid = $oA->sv_insert($fields);//array('aid'=>$aid,'tocid'=>$tocid,'ip'=>$onlineip,)
		#$oA->sv_upload();//上传处理
		
		//#扩展 附加操作, 发短信, 自定义操作,待扩展重复提交，恶意提交
		$sms = new cls_sms();
		$_tel = $fmdata['sjhm'];
		$msg = array();
		if(!$sms->isClosed() && $_tel && $this->cucfgs['issms']== 1 && !empty($this->cucfgs['smscon']) && in_array(5,$arr)){ //$this->cuid==3 && 
			$msg = $sms->sendTpl($_tel,$this->cucfgs['smscon'],$arc->archive,'sadm');
		}	
		//结束时需要的事务	
		return $oA->sv_ajend('咨询成功。'.(!empty($msg[0]) ? '短信已发送' : ''),array('aj_ainfo'=>$this->aj_ainfo,'aj_minfo'=>$this->aj_minfo));
    }
  
}