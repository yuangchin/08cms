<?php
/**
 * 收藏店铺
 *
 * @example   请求范例URL：index.php?/ajax/sc_dianpu/mid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_SC_DianPu extends _08_Models_Base
{
    public function __toString()
    {		
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP; 
		$curuser   = $this->_curuser;
		$memberid = empty($curuser->info['mid']) ? 0 : $curuser->info['mid'];
		$scmid  = empty($this->_get['scmid']) ? 0 : max(1,intval($this->_get['scmid']));

		//请指定收藏对象
		if(empty($scmid)) return "var data=1";
		//请先登录会员
		if(empty($memberid)) return "var data=2";
		//当前功能关闭
		if(!($commu = cls_cache::Read('commu',11)) || !$commu['available']) {
			return "var data=3";
		}
		//您没有关注权限		
		if(!$curuser->pmbypmid($commu['pmid'])) {
			return "var data=4";
		}
		//请指定收藏对象
		if(!($scname = $db->result_one("SELECT mname FROM {$tblprefix}members WHERE mid = '$scmid'"))){
			return "var data=1";
		};
		//亲，您已经收藏了
		if($result = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}$commu[tbl] WHERE mid='$memberid' AND tomid='$scmid'")){
			return "var data=5";
		}	
		
		$sqlstr = "tomid='$scmid',tomname='$scname',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',checked=1";
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET $sqlstr");
		//收藏成功
		return "var data=6";
	}
}