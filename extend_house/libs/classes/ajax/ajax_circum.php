<?php
/**
 * 楼盘内容页下方地图中周边的显示
 *
 * @example   请求范例URL：index.php?/ajax/circum/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Circum extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-Type:text/html;CharSet=$mcharset");		
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP; 
		
		# 周边
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));		
		$caid  = empty($this->_get['caid']) ? 0 : max(1,intval($this->_get['caid']));		
		$latitude  = empty($this->_get['latitude']) ? 0 : floatval($this->_get['latitude']);
		$longitude  = empty($this->_get['longitude']) ? 0 : floatval($this->_get['longitude']);	
		$isxq  = empty($this->_get['isxq']) ? 0 : 1;	
		
		#3公里
		$distance  = empty($this->_get['distance']) ? 3 : max(0,intval($this->_get['distance']));
		$markerfield = 'dt';	
		#地图范围搜索sql生成		
		$sqlstr = cls_dbother::MapSql($latitude, $longitude, $distance, 1, $markerfield);
		
		
		//针对二手房，出租房源的周边	
		$chid  = empty($this->_get['chid']) ? 0 : max(1,intval($this->_get['chid']));	
		if(in_array($chid,array('2','3'))){			
			$_pid = $db->result_one("SELECT pid3 FROM {$tblprefix}".atbl($chid)." WHERE aid = '$aid'");
			//1.如果该房源属于某个现有小区，则先找出该房源所属的小区，然后输出楼盘合辑内的周边	
			//2.如果该房源属于临时小区，则按照范围来输出周边配套
			if(empty($_pid)){		
				$_fysql = "SELECT *,{$markerfield}_0 as lat,{$markerfield}_1 as lng 
							FROM {$tblprefix}".atbl(8)." WHERE chid = '8' ".(empty($caid) ? '' : "AND caid = '$caid'")." AND ".$sqlstr;
			}
			$aid = empty($_pid)? $aid : $_pid;
		}
		 
		
		if(empty($caid)){//针对楼盘，小区下的周边楼盘
			$chid = 4;
			$custom = 1;
			$fields = empty($isxq) ? array('subject', 'arcurl', 'tel', 'sldz') : array('aid','subject','arcurl7','lpczsl','lpesfsl','address');
		}else{//针对楼盘，小区下的周边配套
			$chid = 8;
			$custom = 0;
			$fields = array('subject', 'abstract');
		}			
		$sqlstr .= empty($caid) ? (empty($isxq) ? " AND (leixing='0' OR leixing='1')" : " AND (leixing='0' OR leixing='2')")." LIMIT 50" : '';	
		$sqlstr = "SELECT *,{$markerfield}_0 as lat,{$markerfield}_1 as lng 
		FROM {$tblprefix}".atbl($chid)." a " . ($custom ? "INNER JOIN {$tblprefix}archives_$chid b ON a.aid=b.aid WHERE a.aid!='$aid' AND" : " INNER JOIN {$tblprefix}aalbums c ON a.aid=c.inid 
		WHERE c.pid='$aid' AND  a.chid='$chid' AND c.arid=1 AND a.caid='$caid' AND ") . " a.checked=1 AND (a.enddate=0 OR a.enddate>'$timestamp') ".(empty($caid)?' AND '.$sqlstr:'');		
		
		//$_fysql不为空，则为房源的周边sql
		$sqlstr = empty($_fysql) ? $sqlstr : $_fysql;
		$query = $db->query($sqlstr);		
		$querydata = array();
		while($row = $db->fetch_array($query)){
			$caid || cls_ArcMain::Url($row, empty($isxq) ? 0 : -1);
			!isset($row['arcurl']) && $row['arcurl'] = cls_ArcMain::Url($row);
			$val = array('lat' => $row['lat'], 'lng' => $row['lng'], 'aid' => $row['aid'], 'arcurl' => $row['arcurl']);			
			foreach($fields as $k)$val[$k] = $row[$k];
			$querydata[] = $val;
		}
		header("Content-type:text/javascript;charset=$mcharset");
		echo "_08cms.\$.map_markers['$caid\$$distance']=" . jsonEncode($querydata);
	}
}