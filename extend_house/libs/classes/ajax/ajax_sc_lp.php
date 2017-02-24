<?php
/**
 * 小区内容页的出租信息通知、二手房信息通知，楼盘内容页的关注楼盘
 *
 * @example   请求范例URL：index.php?/ajax/sc_lp/aid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_SC_LP extends _08_Models_Base
{
    public function __toString()
    {
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP;
		$curuser   = $this->_curuser;
		$memberid = empty($curuser->info['mid']) ? 0 : $curuser->info['mid'];
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
        $cuid = 7;

		//请指定收藏对象
		if(empty($aid)) return "var r='请指定收藏对象';";
		//请先登录会员
		if(empty($memberid)) return "var r='请先登录会员';";
		//当前功能关闭
		if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) {
			return "var r='当前功能关闭';";
		}
		//您没有关注权限
		if(!$curuser->pmbypmid($commu['pmid'])) {
			return "var r='您没有关注权限';";
		}
        //请指定收藏对象
		$arc = new cls_arcedit;
		$arc->set_aid($aid,array('au'=>0));
		if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])){
			return "var r='请指定收藏对象';";
		};
        $sqlstr = '';
        foreach(array('new','old','rent',) as $v){ 
            !empty($this->_get[$v]) && $sqlstr .= ",$v=1";
        }
        if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}$commu[tbl] WHERE mid='$memberid' AND ".substr($sqlstr,1)." AND aid='$aid'")){
    	       return "var r='亲，已经收藏了！'";
        }else{
    	   $db->query("INSERT INTO {$tblprefix}$commu[tbl] SET aid='$aid',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp',checked=1 $sqlstr");
        }
		//收藏成功
		return "var r=5;";



	}
}