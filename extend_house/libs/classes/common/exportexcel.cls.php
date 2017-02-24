<?php
/**
 * 数据导出到excel类
 * 链接上传递chid/cuid/aid  
   eg:?entry=$entry$extend_str&chid=$chid&cuid=$cuid&aid=$aid
 *
 * 根据传参，可显示文档模型字段以及交互字段 或  模型字段  或 交互字段(字段中不含datatype为image、images、map、htmltext的字段)
 *
 * 处理数据，导出excel文件 
 */
defined('M_COM') || exit('No Permission');
class cls_exportexcel extends cls_exportexcels{	
	
	// 后台扩扎扩展sql
	function exadmin_full_sql($where_str){
		return $where_str;
	}
	
	// 会员中心扩展sql
	function exuser_full_sql($where_str){
		if(!$this->mc) return;
		global $chid,$cuid,$mchid;
		$curuser = cls_UserMain::CurUser();
		$mid = $curuser->info['mid']; 
		$mchid = $curuser->info['mchid']; 
		if($chid==4){
			;//
		}elseif(!empty($chid) && $mchid==3){
			;//经济公司
		}elseif(!empty($chid)){ //针对自己的文档,有无$cuid都一样
			$where_str .=  " AND a.mid='$mid' ";
		}
		return $where_str;	
	}
	
}
