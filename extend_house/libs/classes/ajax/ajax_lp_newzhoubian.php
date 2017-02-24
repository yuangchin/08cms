<?php
/**楼盘/小区合辑内的周边配套（这个是新的，地图上显示合辑内周边配套以及百度地图默认的周边）
 * 
 *
 * @example   请求范例URL：index.php?/ajax/lp_zhoubian/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_LP_NewZhouBian extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-type: application/json; charset=UTF-8");	
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP; 
		$cms_abs = cls_env::mconfig('cms_abs');
		$entry  = empty($this->_get['entry']) ? '' : trim($this->_get['entry']);
		/*
		# 周边
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));		
		$caid  = empty($this->_get['caid']) ? 0 : max(1,intval($this->_get['caid']));
        //半径(公里)
        $r  = empty($this->_get['r']) ? 0 : max(1,intval($this->_get['r']))/1000;
        $lng  = empty($this->_get['lng']) ? 0 : trim($this->_get['lng']);
        $lat  = empty($this->_get['lat']) ? 0 : trim($this->_get['lat']);
		*/
        switch($entry){
			case 'allzhoubian';
			$chid  = empty($this->_get['chid']) ? 0 : trim($this->_get['chid']);
				switch($chid){					
					case 4:
					default:												
						$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
						$r = empty($this->_get['r']) ? 0 : max(1,intval($this->_get['r']));
						$lng  = empty($this->_get['lng']) ? 0 : trim($this->_get['lng']);
						$lat  = empty($this->_get['lat']) ? 0 : trim($this->_get['lat']);			
						$boundsStr = cls_dbother::MapSql($lat,$lng,$r,1,'dt');
						$whereStr = " WHERE a.aid != '$aid' AND a.checked=1 AND (c.leixing=0 OR c.leixing=1) AND " . $boundsStr;
						$sql = "SELECT * FROM {$tblprefix}" . atbl($chid) . " a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid " . $whereStr;			
						$countSql =  "SELECT COUNT(*) AS cnt FROM {$tblprefix}" . atbl($chid) . " a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid " . $whereStr;
						$count = $db->fetch_one($countSql);
						$ret = array('lp'=>array('project'=>array(),));
						$query = $db->query($sql);
						$ccid18Sel = $this->ccidSel(18);
						while($row=$db->fetch_array($query)){
							 cls_ArcMain::Url($row,-1);
							 $thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : $cms_abs . $row['thumb'];
							 $salestat = $this->ccidText($row['ccid18'],$ccid18Sel);
							 $salecolorarr = array(194=>'daishou',195=>'qifang',196=>'zaishou',197=>'weifang',198=>'showwan');
							 $salecolor = empty($salecolorarr[$row['ccid18']]) ? '' : $salecolorarr[$row['ccid18']];
							 $tmp = array('aid'=>$row['aid'],'subject'=>$row['subject'],'price'=>$row['dj'],'thumb'=>$thumb,'salestat'=>$salestat,'salecolor'=>$salecolor,'address'=>$row['address'],'lng'=>$row['dt_1'],'lat'=>$row['dt_0'],'url'=>$row['arcurl'],'url1'=>$row['arcurl1']);
							 $ret['lp']['project'][] = $tmp;
						}
						#周边配套查询
						$caidArr = array(26=>'school',27=>'bus',28=>'cy',29=>'hospital',596=>'bank',608=>'supermark',609=>'park',610=>'fun');
						$ret['caidconfig'] = $caidArr;
						$sql = "SELECT a.aid,a.caid,a.subject,a.abstract,a.dt_0,a.dt_1 From {$tblprefix}" . atbl(8) . " a INNER JOIN {$tblprefix}aalbums b ON b.inid=a.aid WHERE b.pid='$aid'";
						$query = $db->query($sql);		
						while($row=$db->fetch_array($query)){
								if(!empty($caidArr[$row['caid']])){
									$tmp = array('aid'=>$row['aid'],'subject'=>$row['subject'],'abstract'=>$row['abstract'],'lng'=>$row['dt_1'],'lat'=>$row['dt_0']);
									$ret[$caidArr[$row['caid']]]['project'][] = $tmp;
								 }					 
							}				
						return $ret;
					break;
					case 115:
						$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
						$r = empty($this->_get['r']) ? 0 : max(1,intval($this->_get['r']));
						$lng  = empty($this->_get['lng']) ? 0 : trim($this->_get['lng']);
						$lat  = empty($this->_get['lat']) ? 0 : trim($this->_get['lat']);			
						$boundsStr = cls_dbother::MapSql($lat,$lng,$r,1,'dt');
						$whereStr = " WHERE a.aid != '$aid' AND a.checked=1  AND " . $boundsStr;
						$sql = "SELECT * FROM {$tblprefix}" . atbl($chid) . " a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid " . $whereStr;			
						$countSql =  "SELECT COUNT(*) AS cnt FROM {$tblprefix}" . atbl($chid) . " a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid " . $whereStr;
						$count = $db->fetch_one($countSql);
						$ret = array('lp'=>array('project'=>array(),));
						$query = $db->query($sql);
						$ccid18Sel = $this->ccidSel(18);
						while($row=$db->fetch_array($query)){
							 cls_ArcMain::Url($row,-1);
							 $thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : $cms_abs . $row['thumb'];
							 #$salestat = $this->ccidText($row['ccid18'],$ccid18Sel);
							 $salestat =  '';
							 $salecolorarr = array(194=>'daishou',195=>'qifang',196=>'zaishou',197=>'weifang',198=>'showwan');
							 $salecolor = 'zaishou';
							 $tmp = array('aid'=>$row['aid'],'subject'=>$row['subject'],'price'=>$row['dj'],'thumb'=>$thumb,'salestat'=>$salestat,'salecolor'=>$salecolor,'address'=>$row['address'],'lng'=>$row['dt_1'],'lat'=>$row['dt_0'],'url'=>$row['arcurl']);
							 $ret['lp']['project'][] = $tmp;
						}
						#周边配套查询
						$caidArr = array(26=>'school',27=>'bus',28=>'cy',29=>'hospital',596=>'bank',608=>'supermark',609=>'park',610=>'fun');
						$ret['caidconfig'] = $caidArr;
						$sql = "SELECT a.aid,a.caid,a.subject,a.abstract,a.dt_0,a.dt_1 From {$tblprefix}" . atbl(8) . " a INNER JOIN {$tblprefix}aalbums_arcs b ON b.inid=a.aid WHERE b.pid='$aid'";
						$query = $db->query($sql);	
						while($row=$db->fetch_array($query)){
								if(!empty($caidArr[$row['caid']])){
									$tmp = array('aid'=>$row['aid'],'subject'=>$row['subject'],'abstract'=>$row['abstract'],'lng'=>$row['dt_1'],'lat'=>$row['dt_0']);
									$ret[$caidArr[$row['caid']]]['project'][] = $tmp;
								 }					 
							}			
						return $ret;
					break;					
					case 116:											
						$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
						$r = empty($this->_get['r']) ? 0 : max(1,intval($this->_get['r']));
						$lng  = empty($this->_get['lng']) ? 0 : trim($this->_get['lng']);
						$lat  = empty($this->_get['lat']) ? 0 : trim($this->_get['lat']);			
						$boundsStr = cls_dbother::MapSql($lat,$lng,$r,1,'dt');
						$whereStr = " WHERE a.aid != '$aid' AND a.checked=1 AND (c.leixing=0 OR c.leixing=1) AND " . $boundsStr;
						$sql = "SELECT * FROM {$tblprefix}" . atbl($chid) . " a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid " . $whereStr;			
						$countSql =  "SELECT COUNT(*) AS cnt FROM {$tblprefix}" . atbl($chid) . " a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid " . $whereStr;
						$count = $db->fetch_one($countSql);
						$ret = array('lp'=>array('project'=>array(),));
						$query = $db->query($sql);
						$ccid18Sel = $this->ccidSel(18);
						while($row=$db->fetch_array($query)){
							 cls_ArcMain::Url($row,-1);
							 $thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : $cms_abs . $row['thumb'];
							 #$salestat = $this->ccidText($row['ccid18'],$ccid18Sel);
							 $salestat = '';
							 $salecolorarr = array(194=>'daishou',195=>'qifang',196=>'zaishou',197=>'weifang',198=>'showwan');
							 $salecolor = empty($row['ccid18']) || empty($salecolorarr[$row['ccid18']]) ? '' : $salecolorarr[$row['ccid18']];
							 $tmp = array('aid'=>$row['aid'],'subject'=>$row['subject'],'price'=>$row['dj'],'thumb'=>$thumb,'salestat'=>$salestat,'salecolor'=>$salecolor,'address'=>$row['address'],'lng'=>$row['dt_1'],'lat'=>$row['dt_0'],'url'=>$row['arcurl'],'url1'=>$row['arcurl1']);
							 $ret['lp']['project'][] = $tmp;
						}
						#周边配套查询
						$caidArr = array(26=>'school',27=>'bus',28=>'cy',29=>'hospital',596=>'bank',608=>'supermark',609=>'park',610=>'fun');
						$ret['caidconfig'] = $caidArr;
						$sql = "SELECT a.aid,a.caid,a.subject,a.abstract,a.dt_0,a.dt_1 From {$tblprefix}" . atbl(8) . " a INNER JOIN {$tblprefix}aalbums_arcs b ON b.inid=a.aid WHERE b.pid='$aid'";
						$query = $db->query($sql);		
						while($row=$db->fetch_array($query)){
								if(!empty($caidArr[$row['caid']])){
									$tmp = array('aid'=>$row['aid'],'subject'=>$row['subject'],'abstract'=>$row['abstract'],'lng'=>$row['dt_1'],'lat'=>$row['dt_0']);
									$ret[$caidArr[$row['caid']]]['project'][] = $tmp;
								 }					 
							}				
						return $ret;
					break;
					
				}
				break;
		}
        /*
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
		*/
	}
	
	 protected function ccidSel($ccid){
	   $c = cls_cache::Read('coclasses',$ccid);
	   if($c = cls_cache::Read('coclasses',$ccid)){
		   $result = array();
		   foreach($c as $k=>$v){
			   $result[$k] = $v['title'];
			 }
			return $result;
		}
		return array(); 
   }
   
   protected function ccidText($ccidValue,$ccidSel){
		   $result = '';
		   $arr = explode(',',$ccidValue);
		   $arr = array_filter($arr);
		   foreach($arr as $v){
			   $result .= empty($ccidSel[$v]) ? '' : '/'.$ccidSel[$v];  
		  }		  
		  return ltrim($result,'/');
	}
}