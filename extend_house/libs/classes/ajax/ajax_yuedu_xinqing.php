<?php
/**
 * 读完文章后，心情
 *
 * @example   请求范例URL：index.php?/ajax/yuedu_xinqing/aid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_AJAX_YueDu_XinQing extends _08_Models_Base
{
    public function __toString()
    {	
		global $m_cookie; //这个cookie要与etools/zvdp.php一致
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));		
	
		$fields = cls_cache::Read('cufields','41');		
		$dianping = $db->fetch_one("SELECT * FROM {$tblprefix}commu_zxdp where aid = '$aid'");
		$str = '<ul>';
		foreach($fields as $k => $v){
			$str .= "<li><div class=\"gsmod\">";
			$str .= "<div class=\"sz\" id=\"".$k."\">(".(empty($dianping[$k])?'0':$dianping[$k]).")</div>";
			$str .= "<div class=\"gsbar\"><div class=\"actbar\"></div></div></div>";
			$str .= "<div class=\"gsface\"><span id=\"".$aid."_".$v['ename']."\" class=\"facemod facemod".$k." ".(isset($m_cookie['08cms_cuid41_dp_'.$v['ename'].'_'.$aid])?'on':'')."\" onclick=\"return zixun_dianping(".$aid.",'".$v['ename']."');\" onmouseover=\"window.status='done';return true;\"></span></div></li>";
		}
		$str .= '</ul>';

		return $str;
		// echo "var xinqing= '".$str."'";
	}
}