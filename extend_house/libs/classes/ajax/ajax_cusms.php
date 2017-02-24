<?php
/**
 * ajax提交POST表单，处理房产意向
 *
 * @author    Peace@08cms
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_cusms extends _08_M_Ajax_cuAjaxPost_Base
{
    
    public function __toString()
    {
    	
		$this->cuaj_post_init();
		
		#扩展
		if(!in_array($this->cuid,array(46))) cls_message::show('参数错误！');
		
		$oA = new cls_cuedit($this->defCfgs());  
		$oA->add_init($this->defPid(),'',array('setCols'=>1,'pdetail'=>1)); 
		$this->pinfo = $oA->pinfo; 
        
		$oA->sv_repeat($this->repCookie(), 'both'); // array('aid'=>$aid,'tocid'=>$tocid)
		$oA->sv_set_fmdata();//设置$this->fmdata中的值 
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_regcode("commu$this->cuid"); //认证码靠后,如果之前出现问题返回,认证码还有效
		$this->cid = $oA->sv_insert($this->extFields());//array('aid'=>$aid,'tocid'=>$tocid,'ip'=>$onlineip,)
		#$oA->sv_upload();//上传处理
		//附加操作, 发短信, 自定义操作..... 
		$sms = new cls_sms(); //发短信...
		$commu = $this->cucfgs;
    	if(!empty($commu['issms']) && !empty($this->pinfo['lxdh']) && !$sms->isClosed()){
    		empty($commu['smscon']) && $commu['smscon'] = '您好！联系人{$uname}（电话{$utel}）对你的房源（{$subject}）有意向需求，请回复！';
    		$redata = array('uname'=>$GLOBALS[$this->fmpre]['uname'],'utel'=>$GLOBALS[$this->fmpre]['utel'],'subject'=>$this->pinfo['subject']);
    		$commu['smsfee'] = $commu['smsfee']=='get' ? $this->pinfo['mid'] : 'sadm';
    		$msg = $sms->sendTpl($this->pinfo['lxdh'],$commu['smscon'],$redata,$commu['smsfee']);
    	}
		return $oA->sv_ajend('提交成功！',array('aj_ainfo'=>$this->aj_ainfo,'aj_minfo'=>$this->aj_minfo));//结束时需要的事务
		
    }
    
}    