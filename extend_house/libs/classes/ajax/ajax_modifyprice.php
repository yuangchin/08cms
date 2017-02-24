<?php
/**
 * 取消委托
 *
 * @example   请求范例URL：index.php?/ajax/cancelweituo/wid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_ModifyPrice extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-Type:text/html;CharSet=$mcharset");		
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$curuser   = $this->_curuser;
		$memberid = empty($curuser->info['mid']) ? 0 : $curuser->info['mid'];
		$cid  = empty($this->_get['cid']) ? 0 : max(1,intval($this->_get['cid']));	
		$zj  = empty($this->_get['zj']) ? 0 : max(1,intval($this->_get['zj']));

		if(!$cid || !$zj) mexit('参数出错！');
		$cuid = 36;
		if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) mexit('委托功能已关闭。');
		if(!empty($memberid)){
			if($memberid == 1){//当管理员以托管形式进入会员中心，进行委托房源删除时，获取被托管的会员的ID
				define('M_MCENTER', TRUE); // 用于代表以下操作是属性会员中心
				$member_info = $curuser->isTrusteeship();
				$memberid = $member_info['mid'];
			}			
			
			$db->query("UPDATE {$tblprefix}$commu[tbl] SET zj='$zj' WHERE cid='$cid' AND mid='$memberid'");
			mexit($db->affected_rows() ? 'SUCCEED' : '没有做任何修改。');
		}else{
			mexit('请先登陆会员。');
		}
	}
}