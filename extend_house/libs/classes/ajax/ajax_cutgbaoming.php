<?php
/**
 * ajax提交POST表单，通用处理代码
 *
 * @author    Peace@08cms
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_cutgbaoming extends _08_M_Ajax_cuAjaxPost_Base
{
    
	// 就扩展 
    public function __toString()
    {
		$this->cuaj_post_init();
		
		#扩展
		if(!in_array($this->cuid,array(8,35,45))) cls_message::show('参数错误！');
		
		$oA = new cls_cuedit($this->defCfgs());  
		$oA->add_init($this->defPid(),'',array('setCols'=>1)); 
		
		#扩展
		if($this->cuid==35){
			if(isset($oA->pinfo['tgend']) && ($oA->pinfo['tgend']<TIMESTAMP) && ($oA->pinfo['tgend'] != 0)) cls_message::show('此看房活动已经过期！');
		}
		if($this->cuid==45){
			if(empty($this->_get['fmdata']['yxlp'])) cls_message::show('请选择意向楼盘！');
			if(isset($oA->pinfo['enddate']) && ($oA->pinfo['enddate']<TIMESTAMP) && ($oA->pinfo['enddate'] != 0)) cls_message::show('此看房活动已经过期！');
		}
		
		$oA->sv_regcode("commu$this->cuid");
		$oA->sv_repeat($this->repCookie(), 'both'); // array('aid'=>$aid,'tocid'=>$tocid)
		$oA->sv_set_fmdata();//设置$this->fmdata中的值 
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$this->cid = $oA->sv_insert($this->extFields());//array('aid'=>$aid,'tocid'=>$tocid,'ip'=>$onlineip,)
		#$oA->sv_upload();//上传处理
		
		//#扩展 附加操作, 发短信, 自定义操作..... 
		$spids = array(
			8=>5,
			35=>14,
			45=>110,
		); 
		$spid = $spids[$this->cuid]; 
		$this->_db->query("UPDATE {$this->_tblprefix}archives_$spid SET hdnum = hdnum + 1 WHERE aid = '$this->aid'"); 
		
		return $oA->sv_ajend('提交成功！',array('aj_ainfo'=>$this->aj_ainfo,'aj_minfo'=>$this->aj_minfo));//结束时需要的事务

    }

}