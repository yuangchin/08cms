<?php
/**
 * ajax提交POST表单，通用处理代码
 *
 * @author    Peace@08cms
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_cuscloupan extends _08_M_Ajax_cuAjaxPost_Base
{
    
	// 扩展 
    public function __toString()
    {
		
		$this->cuaj_post_init();
		if(!in_array($this->cuid,array(31))) cls_message::show('参数错误！');
		
		$oA = new cls_cuedit($this->defCfgs());  
		$oA->add_init($this->defPid(),'',array('setCols'=>1)); 
		$this->pinfo = $oA->pinfo; 
        
		$oA->sv_repeat($this->repCookie(), 'both'); // array('aid'=>$aid,'tocid'=>$tocid)
		$oA->sv_set_fmdata();//设置$this->fmdata中的值 
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_regcode("commu$this->cuid"); //认证码靠后,如果之前出现问题返回,认证码还有效
		$this->cid = $oA->sv_insert($this->extFields());//array('aid'=>$aid,'tocid'=>$tocid,'ip'=>$onlineip,)
		#$oA->sv_upload();//上传处理
		
		//统计综合评分
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$curuser = cls_UserMain::CurUser();
		$mid = $memberid = $curuser->info['mid'];
		$tjarr = $db->fetch_one("SELECT COUNT(*) num,SUM(service) service,SUM(price) price,SUM(design) design,SUM(process) process,SUM(aftersale) aftersale FROM {$tblprefix}$commu[tbl] WHERE tomid='$mid' AND checked=1");
		$zonghepf = 0;
		$temparr = array('service','price','design','process','aftersale');
		foreach($temparr as $k) $zonghepf +=$tjarr[$k]+$fmdata[$k];
		$zonghepf = round($zonghepf/($tjarr['num']+1)/5);
		$db->query("UPDATE {$tblprefix}members SET zonghepf='$zonghepf' WHERE mid='$mid'");
		
		//附加操作, 发短信, 自定义操作..... 
		return $oA->sv_ajend('提交成功！',array('aj_ainfo'=>$this->aj_ainfo,'aj_minfo'=>$this->aj_minfo));//结束时需要的事务
        //'';//$contents;
		
    }
	
}