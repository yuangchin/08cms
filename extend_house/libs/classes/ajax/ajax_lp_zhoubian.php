<?php
/**楼盘/小区合辑内的周边配套（这个是新的，地图上显示合辑内周边配套以及百度地图默认的周边）
 * 
 *
 * @example   请求范例URL：index.php?/ajax/lp_zhoubian/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_LP_ZhouBian extends _08_Models_Base
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
        //半径(公里)
        $r  = empty($this->_get['r']) ? 0 : max(1,intval($this->_get['r']))/1000;
        $lng  = empty($this->_get['lng']) ? 0 : trim($this->_get['lng']);
        $lat  = empty($this->_get['lat']) ? 0 : trim($this->_get['lat']);
        
        
        #
        $select_str = '';        
        $from_str = '';
        $where_str = '';
        if(empty($caid)){//周边范围内的楼盘/小区
       	    $fields = empty($isxq) ? array('subject', 'arcurl', 'tel', 'sldz') : array('aid','subject','arcurl7','lpczsl','lpesfsl','address');            
            $select_str = "SELECT a.*,c.sldz,c.tel,c.address ";
            $from_str = " FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid ";
            $where_str = " WHERE a.aid != '$aid' ";
            $where_str .= empty($isxq) ? " AND (c.leixing='0' OR c.leixing='1') " : " AND (c.leixing='0' OR c.leixing='2')";           
            $bounds_str = cls_dbother::MapSql($lat, $lng, $r, 1, 'dt');
            $where_str .= " AND $bounds_str";
            
        }else{//周边配套
            $select_str = "SELECT a.subject,a.abstract,a.dt_0,a.dt_1 ";
            $from_str = " FROM {$tblprefix}".atbl(8)." a INNER JOIN {$tblprefix}aalbums b ON b.inid=a.aid";
            $where_str = " WHERE b.pid= '$aid' ";
            $where_str .= " AND a.caid='$caid' "; 
        }  

        $sql = $db->query("$select_str  $from_str $where_str");
        $data = array();
        if(!empty($caid)){//小区/楼盘周边的配套
            while($row = $db->fetch_array($sql)){
                $data[]= $row;   
            }
        }else{//小区/楼盘周边的小区/楼盘
            while($row = $db->fetch_array($sql)){
     			cls_ArcMain::Url($row, empty($isxq) ? 0 : -1);
    			!isset($row['arcurl']) && $row['arcurl'] = cls_ArcMain::Url($row);
    			$val = array('dt_0' => $row['dt_0'], 'dt_1' => $row['dt_1'], 'aid' => $row['aid'], 'arcurl' => $row['arcurl']);			
    			foreach($fields as $k)$val[$k] = $row[$k];
    			$data[] = $val;  
            }
        }    
		$data = cls_string::iconv($mcharset, "UTF-8", $data);	
       	echo 'var data = ' . json_encode($data) . ';';
	}
}