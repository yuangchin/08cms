<?php
/**
 * 收藏问答
 *
 * @example   请求范例URL：/index.php?/ajax/sc_wenda/aid...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_SC_WenDa extends _08_Models_Base
{
    public function __toString()
    {
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP; 
		$curuser   = $this->_curuser;
		$memberid = empty($curuser->info['mid']) ? 0 : $curuser->info['mid'];
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
		
		$cuid = 39;
		//请指定收藏问答
		if(empty($aid)) return "var data=1";
		//请先登录会员
		if(empty($memberid)) return "var data=2";
		//当前功能关闭
		if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) {
			return "var data=3";
		}
		//您没有关注权限		
		if(!$curuser->pmbypmid($commu['pmid'])) {
			return "var data=4";
		}
		//请指定收藏对象
		$arc = new cls_arcedit;
		$arc->set_aid($aid,array('au'=>0));
		if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])){
			return "var data=1";
		};
		//亲，您已经收藏了
		if($result = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}$commu[tbl] WHERE mid='$memberid' AND aid='$aid'")){
			return "var data=5";
		}	
		
		$sqlstr = "aid='$aid',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',checked=1";
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET $sqlstr");
		//收藏成功
		return "var data=6";
    }
}
