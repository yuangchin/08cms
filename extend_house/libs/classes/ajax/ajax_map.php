<?php
/**
 * 地图
 *
 * @example   请求范例URL：index.php?/ajax/map/entry/zhu/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
//此脚本将慢慢被ajax_newmap.php取代
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Map extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;	
		header("Content-Type:text/html;CharSet=$mcharset");		
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP; 
		
		
		$entry  = empty($this->_get['entry']) ? '' : trim($this->_get['entry']);
		$zoom = empty($this->_get['zoom']) ? 0 : max(1,intval($this->_get['zoom']));		
		$pagesize = 50;    
		$start = empty($this->_get['start']) ? 1 : max(1,intval($this->_get['start']));
		$pagestart = ($start-1)*$pagesize;
		$timestart = microtime(TRUE);
		$xml = '';
		$mconfigs = cls_cache::Read('mconfigs');
		switch($entry){
			case 'zoom' :
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";    
				$coclass1 = cls_cache::Read('coclasses', 1);
				$sql = "select ccid1,count(*) cnt FROM {$tblprefix}".atbl(4)." group by ccid1";
				$query = $db->query($sql);
				while($row=$db->fetch_array($query)){
				   $ccid1title = empty($coclass1[$row['ccid1']]['title']) ? '' : $coclass1[$row['ccid1']]['title'];          
				   (!empty($ccid1title) && !empty($coclass1[$row['ccid1']]['dt_1'])) && $xml .= "<floor subject=\"$ccid1title\" aid=\"".$coclass1[$row['ccid1']]['ccid']."\" count=\"$row[cnt]\" x=\"".$coclass1[$row['ccid1']]['dt_1']."\" y=\"".$coclass1[$row['ccid1']]['dt_0']."\"/>"; 
				}
				exit("<floors time=\"".(microtime(TRUE) - $timestart)."\">".$xml."</floors>");
			break;
			case 'xin':# 新房列表              
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";				
				if(!empty($this->_get['station']))unset($this->_get['metro']);
				$where = "chid=4 AND checked=1 AND (leixing=0 OR leixing=1)";
				$bounds  = empty($this->_get['bounds']) ? '' : trim($this->_get['bounds']);
				$this->u_check_bounds($bounds);
				$where .= " AND dt_1>'$bounds[0]' AND dt_1<'$bounds[2]' AND dt_0>'$bounds[1]' AND dt_0<'$bounds[3]'";
				
				empty($this->_get['area']) || $where .= ' AND ' . cnsql(1, $this->_get['area'], 'a.');        
				empty($this->_get['type']) || $where .= ' AND ' . cnsql(12, $this->_get['type'] , 'a.');
				empty($this->_get['price']) || $where .= ' AND ' . cnsql(17, $this->_get['price'], 'a.');			 
				empty($this->_get['metro']) || $where .= ' AND ' . cnsql(3, $this->_get['metro'], 'a.');
				empty($this->_get['station']) || $where .= ' AND ' . cnsql(14, $this->_get['station'], 'a.');
				$keyword = empty($this->_get['keyword']) ? '' : trim($this->_get['keyword']);
				if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";		
				$sqlsub = " FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE $where";
				$query = $db->query("SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,a.dj,a.ccid18 $sqlsub ORDER BY c.aid DESC LIMIT $pagestart,$pagesize");
				$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;"); 
				while($row = $db->fetch_array($query)){           
					$row = mhtmlspecialchars($row);
					$xml .= "<floor aid=\"".$row['aid']."\" subject=\"".$row['subject']."\" x=\"".$row['dt_1']."\" y=\"".$row['dt_0']."\" price=\"".$row['dj'] ."\" stat=\"".($row['ccid18'] - 193)."\" />";
				} 
				exit("<floors total=\"".$r_cnt['cnt']."\" time=\"".(microtime(TRUE) - $timestart)."\">".$xml."</floors>");
			case 'houses':# 楼盘信息
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><hosues>";
				$aid = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
			
				$sql = "SELECT a.*,c.*,k.subject AS kfs FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid LEFT JOIN {$tblprefix}".atbl(13)." k ON a.pid6=k.aid WHERE c.aid='$aid' AND a.checked=1 AND a.dt!='' AND (a.enddate=0 OR a.enddate>'$timestamp') LIMIT 1";
				if($row = $db->fetch_one($sql)){				
					cls_ArcMain::Parse($row);
					$row = mhtmlspecialchars($row);
					$cols12 = cls_cache::Read('coclasses', 12);
					$row['ccid12'] = explode(',', $row['ccid12']);
					$result = array();
					foreach($row['ccid12'] as $id)$id && $result[] = $cols12[$id]['title'];
					$arcurlx = '';
					for($i=1;$i<7;$i++){
						$arcurlx .= ' arcurl'.$i.'="'.@$row['arcurl'.$i].'" ';
					}
					$thumbUrl = cls_url::tag2atm($row['thumb']);     
					echo "<house aid=\"".$row['aid']."\" title=\"".$row['subject']."\" mid=\"".$row['mid']."\" img=\"".$thumbUrl."\" company=\"".$row['kfs']."\" phone=\"".$row['tel']."\" address=\"".$row['address']."\" date=\"".$row['kprq']."\" stat=\"".($row['ccid18'] ? $row['ccid18'] - 193 : 0)."\" type=\"".implode(' | ', $result)."\" wygs=\"".$row['wygs']."\" dj=\"".$row['dj']."\" arcurl=\"".$row['arcurl']."\" ".$arcurlx." />";
				}
				exit('</hosues>');
			case 'zhuang':#装修公司
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
				$where = "checked=1 AND mchid=11";		
				$bounds  = empty($this->_get['bounds']) ? '' : trim($this->_get['bounds']);
				$this->u_check_bounds($bounds);
				$where .= " AND map_1>'$bounds[0]' AND map_1<'$bounds[2]' AND map_0>'$bounds[1]' AND map_0<'$bounds[3]'";
				
				$area  = empty($this->_get['area']) ? 0 : max(0,intval($this->_get['area']));
				empty($area) || $where .= " AND szqy='".$area."'";
				
				$keyword  = empty($this->_get['keyword']) ? '' : trim($this->_get['keyword']);
				if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND b.companynm LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";		
				$sqlsub = " FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_11 b ON b.mid=m.mid WHERE $where";	   
				$query = $db->query("SELECT SQL_CALC_FOUND_ROWS*,m.*,mchid,map_0,map_1,companynm,conactor,dizhi,internet $sqlsub ORDER BY m.mid DESC LIMIT $pagestart,$pagesize");
				 $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");
				while($row = $db->fetch_array($query)){
					$mspaceurl = mhtmlspecialchars(cls_Mspace::IndexUrl($row));			
					$row['companynm'] = mhtmlspecialchars($row['companynm']);       
					$xml .= "<floor aid=\"".$row['mid']."\" subject=\"".$row['companynm']."\" x=\"".$row['map_1']."\" y=\"".$row['map_0']."\" stat=\"".($row['mchid'] - 10)."\" />";
				}
				exit("<floors total=\"".$r_cnt['cnt']."\" time=\"".(microtime(TRUE) - $timestart)."\">".$xml."</floors>");
			case 'zhua' :#单个装修
				header("Content-type: application/xml; charset=$mcharset");
				$mid  = empty($this->_get['aid']) ? 0 : max(0,intval($this->_get['aid']));			
				$row = $db->fetch_one("SELECT * FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_11 b ON b.mid=m.mid WHERE m.mid='$mid'");
				if($row){            
					$row = mhtmlspecialchars($row);
					$mspaceurl = cls_Mspace::IndexUrl($row);
					$vip = $row['grouptype31']==102 ? 1 : 0;  
				  $xml = "<member mid=\"".$row['mid']."\" subject=\"".$row['companynm']."\" vip=\"".$vip."\" url=\"".$mspaceurl."\" address=\"".$row['dizhi']."\" conactor=\"".$row['conactor']."\"  tel=\"".$row['lxdh']."\" img=\"".$row['pic']."\" />";
				}
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
				exit("<floors time=\"".(microtime(TRUE) - $timestart)."\">".$xml."</floors>"); 
				break;
			case 'mgs':#商家
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
				$where = "checked=1 AND mchid=12";
				$bounds  = empty($this->_get['bounds']) ? '' : trim($this->_get['bounds']);			
				$this->u_check_bounds($bounds);
				$where .= " AND map_1>'$bounds[0]' AND map_1<'$bounds[2]' AND map_0>'$bounds[1]' AND map_0<'$bounds[3]'";
				

				empty($this->_get['area']) || $where .= " AND szqy='".max(0,intval($this->_get['area']))."'";
				empty($this->_get['product']) || $where .= " AND zycp = '".max(0,intval($this->_get['product']))."'";
				
				$keyword  = empty($this->_get['keyword']) ? '' : trim($this->_get['keyword']);		
				if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND b.companynm LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";
				$sqlsub = " FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_12 b ON b.mid=m.mid WHERE $where";
				$query = $db->query("SELECT SQL_CALC_FOUND_ROWS*,m.*,mchid,map_0,map_1,companynm $sqlsub ORDER BY m.mid DESC LIMIT $pagestart,$pagesize");
				$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");
				while($row = $db->fetch_array($query)){				
					$row['companynm'] = mhtmlspecialchars($row['companynm']);          	
					$xml .= "<floor aid=\"".$row['mid']."\" subject=\"".$row['companynm']."\" x=\"".$row['map_1']."\" y=\"".$row['map_0']."\" stat=\"".($row['mchid'] - 10)."\" />";
				}
				exit("<floors total=\"".$r_cnt['cnt']."\" time=\"".(microtime(TRUE) - $timestart)."\">$xml</floors>");
			case 'shang' :#单个商家信息
				header("Content-type: application/xml; charset=$mcharset");
				$mid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));			
				$row = $db->fetch_one("SELECT * FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_12 b ON b.mid=m.mid WHERE m.mid='$mid'");
				if($row){            
					$row = mhtmlspecialchars($row);
					$mspaceurl = cls_Mspace::IndexUrl($row);
					$cols31 = cls_cache::Read('coclasses', 31);
					$vip = $row['grouptype32']==104 ? 1 : 0;           
					$row['zycp'] = empty($cols31[$row['zycp']]) ? '' :$cols31[$row['zycp']]['title'];
				  $xml = "<member mid=\"".$row['mid']."\" subject=\"".$row['companynm']."\" vip=\"".$vip."\"  url=\"".$mspaceurl."\" address=\"".$row['dizhi']."\" conactor=\"".$row['conactor']."\" zycp=\"".$row['zycp']."\" tel=\"".$row['lxdh']."\" img=\"".$row['pic']."\" />";
				}
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
				exit("<floors time=\"".(microtime(TRUE) - $timestart)."\">$xml</floors>");    
			case 'zhu':#小区(以小区名和出租数量显示)
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
				$bounds  = empty($this->_get['bounds']) ? '' : trim($this->_get['bounds']);
				$this->u_check_bounds($bounds);
				$where	= "a.chid=4 AND a.checked=1 AND a.dt!='' AND c.chid=2 AND c.checked=1 AND (c.enddate=0 OR c.enddate>'$timestamp')"
						. " AND a.dt_1>'$bounds[0]' AND a.dt_1<'$bounds[2]' AND a.dt_0>'$bounds[1]' AND a.dt_0<'$bounds[3]' AND (f.leixing in (0,2))";
				
				empty($this->_get['area']) || $where .= ' AND ' . cnsql(1, $this->_get['area'], 'c.');
				empty($this->_get['type']) || $where .= ' AND ' . cnsql(12, $this->_get['type'], 'c.');
				empty($this->_get['price']) || $where .= ' AND ' . cnsql(5, $this->_get['price'], 'c.');
				empty($this->_get['room']) || $where .= " AND c.shi='".max(0,intval($this->_get['room']))."'";
				empty($this->_get['ting']) || $where .= " AND c.ting='".max(0,intval($this->_get['ting']))."'";
				empty($this->_get['chu']) || $where .= " AND c.chu='".max(0,intval($this->_get['chu']))."'";
				empty($this->_get['wei']) || $where .= " AND c.wei='".max(0,intval($this->_get['wei']))."'";
				empty($this->_get['mian']) || $where .= ' AND ' .cnsql(6, $this->_get['mian'], 'c.');
				empty($this->_get['puber']) || $where .= " AND c.mchid='".max(0,intval($this->_get['puber']))."'";//个人发布，经纪人发布
				$keyword = empty($this->_get['keyword']) ? '' : $this->_get['keyword'];
				if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND c.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";
				$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,c.mchid,count(c.aid) AS count FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}".atbl(2)." c ON c.pid3=a.aid INNER JOIN {$tblprefix}archives_4 f ON a.aid = f.aid INNER JOIN {$tblprefix}archives_2 d ON c.aid=d.aid WHERE $where GROUP BY a.aid LIMIT $pagestart,$pagesize";
				$query = $db->query($sql);
				$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");
				while($row = $db->fetch_array($query)){		
					$row = mhtmlspecialchars($row);		
					$xml .= "<floor aid=\"$row[aid]\" subject=\"$row[subject]\" x=\"$row[dt_1]\" y=\"$row[dt_0]\" count=\"$row[count]\" />";
				}
				exit("<floors total=\"".$r_cnt['cnt']."\" time=\"".(microtime(TRUE) - $timestart)."\">".$xml."</floors>");
			case 'zhufang':# (小区)出租房信息列表数据
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><hosues>";
				$aid = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));				
				$order = empty($this->_get['order']) ? '' : trim($this->_get['order']);
				
				$sql = "SELECT * FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE a.aid='$aid' AND a.dt!='' AND checked=1 AND (enddate=0 OR enddate>'$timestamp') LIMIT 1";
				if($row = $db->fetch_one($sql)){
					cls_ArcMain::Parse($row);       
					if(!empty($order)){
						$order = explode(':', $order);
						$order[1] = empty($order[1]) ? 'ASC' : 'DESC';
						if($order[0] == 'area'){
							$order = "mj $order[1]";
						}elseif($order[0] == 'price'){
							$order = "zj $order[1]";
						}else{
							$order = 'a.aid DESC';
						}
					}else{
						$order = 'a.aid DESC';
					}
				
					$where = "a.chid=2 AND a.pid3='$aid' AND a.checked=1 AND (a.enddate=0 OR a.enddate>'$timestamp')";		
					empty($this->_get['type']) || $where .= ' AND ' . cnsql(12, $this->_get['type'], 'a.');
					empty($this->_get['price']) || $where .= ' AND ' . cnsql(5, $this->_get['price'], 'a.');
					empty($this->_get['room']) || $where .= " AND a.shi='".max(0,intval($this->_get['room']))."'";            
					empty($this->_get['ting']) || $where .= " AND a.ting='".max(0,intval($this->_get['ting']))."'";
					empty($this->_get['chu']) || $where .= " AND a.chu='".max(0,intval($this->_get['chu']))."'";
					empty($this->_get['wei']) || $where .= " AND a.wei='".max(0,intval($this->_get['wei']))."'";
					empty($this->_get['mian']) || $where .= ' AND ' .cnsql(6, $this->_get['mian'], 'a.'); 
					empty($this->_get['puber']) || $where .= " AND a.mchid='".max(0,intval($this->_get['puber']))."'";//个人发布，经纪人发布
					
					$keyword = empty($this->_get['keyword']) ? '' : trim($this->_get['keyword']);
					if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";          
					$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.*,c.* FROM {$tblprefix}".atbl(2)." a INNER JOIN {$tblprefix}archives_2 c ON a.aid=c.aid WHERE $where ORDER BY $order,refreshdate DESC";# LIMIT $start,6";
					$query = $db->query($sql);
					$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");  
					$thumbUrl = cls_url::tag2atm($row['thumb']);        
					echo "<floor aid=\"$row[aid]\" title=\"".$row['subject']."\" address=\"".$row['sldz']."\" count=\"".$r_cnt['cnt']."\" junjia=\"".$row['czpjj']."\" thumb=\"$thumbUrl\" url=\"".urlencode($row['arcurl7'])."\" />";#        

				   //单选字段配置
					$shiSel = $this->fieldSel('field',2,'shi');
					$tingSel = $this->fieldSel('field',2,'ting');
					$chuSel = $this->fieldSel('field',2,'chu');
					$weiSel = $this->fieldSel('field',2,'wei');
					$yangSel = $this->fieldSel('field',2,'yangtai');                            
					while($con = $db->fetch_array($query)){
						cls_ArcMain::Parse($con);
						$con = mhtmlspecialchars($con);
						$zj = empty($con['zj']) ? '' : $con['zj'];//租金                                             
						$refresh = '';//刷新时间
						$shi = $shiSel[$con['shi']];//室
						$ting = $tingSel[$con['ting']];//厅
						$chu = $chuSel[$con['chu']];//厨
						$wei = $weiSel[$con['wei']];//卫
						$yang = $yangSel[$con['yangtai']];//阳台
						$refresh = empty($con['refreshdate']) ? '' : mtime_diff($con['refreshdate'],1);  
						$thumbUrl = cls_url::tag2atm($con['thumb']);                             
						echo "<house aid=\"$con[aid]\" subject=\"$con[subject]\" refresh=\"".$refresh."\" shi=\"$shi\" ting=\"$ting\" chu=\"$chu\" wei=\"$wei\" yang=\"$yang\" floors=\"$con[zlc]\" floor=\"$con[szlc]\" area=\"$con[mj]\" price=\"$zj\" puber=\"$con[mchid]\" url=\"$con[arcurl]\" thumb=\"$thumbUrl\" />";
					}
				}
				exit('</hosues>');
			case 'mai':# 二手房楼盘
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
				if(!empty($this->_get['station']))unset($this->_get['metro']);
				$bounds  = empty($this->_get['bounds']) ? '' : trim($this->_get['bounds']);
				$this->u_check_bounds($bounds);
				$where	= "a.chid=4 AND a.checked=1 AND a.dt!='' AND c.chid=3 AND c.checked=1 AND (c.enddate=0 OR c.enddate>'$timestamp')"
						. " AND a.dt_1>'$bounds[0]' AND a.dt_1<'$bounds[2]' AND a.dt_0>'$bounds[1]' AND a.dt_0<'$bounds[3]' AND (f.leixing in (0,2))";
				
				empty($this->_get['area']) || $where .= ' AND ' . cnsql(1, $this->_get['area'], 'a.');
				empty($this->_get['type']) || $where .= ' AND ' . cnsql(12, $this->_get['type'], 'c.');
				empty($this->_get['price']) || $where .= ' AND ' . cnsql(4, $this->_get['price'], 'c.');
				empty($this->_get['room']) || $where .= " AND c.shi='".max(0,intval($this->_get['room']))."'";
				empty($this->_get['ting']) || $where .= " AND c.ting='".max(0,intval($this->_get['ting']))."'";
				empty($this->_get['chu']) || $where .= " AND c.chu='".max(0,intval($this->_get['chu']))."'";
				empty($this->_get['wei']) || $where .= " AND c.wei='".max(0,intval($this->_get['wei']))."'";
				empty($this->_get['mian']) || $where .= ' AND ' .cnsql(6, $this->_get['mian'], 'c.');
				empty($this->_get['puber']) || $where .= " AND c.mchid='".max(0,intval($this->_get['puber']))."'";//个人发布，经纪人发布
				
				$keyword = empty($this->_get['keyword']) ? '' : trim($this->_get['keyword']);
				if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND c.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";
				$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,c.mchid,count(c.aid) AS count FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}".atbl(3)." c ON c.pid3=a.aid INNER JOIN {$tblprefix}archives_4 f ON a.aid=f.aid WHERE $where GROUP BY a.aid LIMIT $pagestart,$pagesize";
				
				
				$query = $db->query($sql);
				$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");	
				while($row = $db->fetch_array($query)){
					$row = mhtmlspecialchars($row);
					$result = array();
					$xml .= "<floor aid=\"$row[aid]\" subject=\"$row[subject]\" x=\"$row[dt_1]\" y=\"$row[dt_0]\" count=\"$row[count]\" />";
				}
				exit("<floors total=\"$r_cnt[cnt]\" time=\"".(microtime(TRUE) - $timestart)."\">".$xml."</floors>");	
			case 'maifang':# 出租房源信息列表数据
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><hosues>";
				$aid = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
				$page = empty($this->_get['page']) ? 1 : max(1,intval($this->_get['page']));
				$order = empty($this->_get['order']) ? '' : trim($this->_get['order']);
				
				$sql = "SELECT * FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE a.aid='$aid' AND a.dt!='' AND checked=1 AND (enddate=0 OR enddate>'$timestamp') LIMIT 1";
				if($row = $db->fetch_one($sql)){		    
					cls_ArcMain::Parse($row);
					if(!empty($order)){
						$order = explode(':', $order);
						$order[1] = empty($order[1]) ? 'ASC' : 'DESC';
						if($order[0] == 'area'){
							$order = "mj $order[1]";
						}elseif($order[0] == 'price'){
							$order = "zj $order[1]";
						}else{
							$order = 'a.aid DESC';
						}
					}else{
						$order = 'a.aid DESC';
					}		
					$where = "a.chid=3 AND a.pid3='$aid' AND a.checked=1 AND (a.enddate=0 OR a.enddate>'$timestamp')";
					$cotypes = cls_cache::Read('cotypes');  
					empty($this->_get['type']) || $where .= ' AND ' . cnsql(12, $this->_get['type'], 'a.');
					empty($this->_get['price']) || $where .= ' AND ' . cnsql(4, $this->_get['price'], 'a.');
					empty($this->_get['room']) || $where .= " AND a.shi='".max(0,intval($this->_get['room']))."'";
					empty($this->_get['ting']) || $where .= " AND a.ting='".max(0,intval($this->_get['ting']))."'";
					empty($this->_get['chu']) || $where .= " AND a.chu='".max(0,intval($this->_get['chu']))."'";
					empty($this->_get['wei']) || $where .= " AND a.wei='".max(0,intval($this->_get['wei']))."'";
					empty($this->_get['mian']) || $where .= ' AND ' .cnsql(6, $this->_get['mian'], 'a.');
					empty($this->_get['puber']) || $where .= " AND a.mchid='".max(0,intval($this->_get['puber']))."'";//个人发布，经纪人发布
					$keyword = empty($this->_get['keyword']) ? '' : trim($this->_get['keyword']);
					if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'"; 
					$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.*,c.* FROM {$tblprefix}".atbl(3)." a INNER JOIN {$tblprefix}archives_3 c ON a.aid=c.aid WHERE $where ORDER BY $order,refreshdate DESC";# LIMIT $start,6";            
					$query = $db->query($sql);
					$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");                  
					echo "<floor aid=\"$row[aid]\" title=\"".$row['subject']."\" address=\"".$row['sldz']."\" count=\"".$r_cnt['cnt']."\" junjia=\"".$row['cspjj']."\" thumb=\"".$row['thumb']."\" url=\"".urlencode($row['arcurl7'])."\" />";#        

				   //单选字段配置
					$shiSel = $this->fieldSel('field',2,'shi');
					$tingSel = $this->fieldSel('field',2,'ting');
					$chuSel = $this->fieldSel('field',2,'chu');
					$weiSel = $this->fieldSel('field',2,'wei');
					$yangSel = $this->fieldSel('field',2,'yangtai');                          
					while($con = $db->fetch_array($query)){
						cls_ArcMain::Parse($con);
						$con = mhtmlspecialchars($con);
						$zj = empty($con['zj']) ? '' : $con['zj'];//租金                                           
						$refresh = '';//刷新时间
						$shi = $shiSel[$con['shi']];//室
						$ting = $tingSel[$con['ting']];//厅
						$chu = $chuSel[$con['chu']];//厨
						$wei = $weiSel[$con['wei']];//卫
						$yang = $yangSel[$con['yangtai']];//阳台
						$refresh = empty($con['refreshdate']) ? '' : mtime_diff($con['refreshdate'],1);  
						$thumbUrl = cls_url::tag2atm($con['thumb']);            
						echo "<house aid=\"".$con['aid']."\" subject=\"".$con['subject']."\" refresh=\"".$refresh ."\" shi=\"".$shi."\" ting=\"".$ting."\" chu=\"".$chu."\" wei=\"".$wei."\" yang=\"".$yang."\" floors=\"".$con['zlc']."\" floor=\"".$con['szlc']."\" area=\"".$con['mj']."\" price=\"".$zj."\" puber=\"".$con['mchid']."\" url=\"".$con['arcurl']."\" thumb=\"".$thumbUrl."\" />";
					}
				}
				exit('</hosues>');  
				
			case 'condition'://地图全局搜索条件。(租房，二手房列表中的搜索条件建立在此基础上)
				header("Content-type: application/xml; charset=$mcharset");
				echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><conditions>";
				$value  = empty($this->_get['value']) ? '' : trim($this->_get['value']);		
				$t = $value;
				$temp = array();
				$cnrels = array();
				$conditions = array();
					switch($value){
					case 'xiaoqu':
					case 'xin':
						$cnrels = array(2);
						$conditions = $value == 'xin' ? array(
							1	=>	'area',
							12	=>	'type',
							17	=>	'price',
						) : array(
							1	=>	'area',
							12	=>	'type',
							3	=>	'metro',
							14	=>	'station',				
						);
						break;
					case 'mai':
						$chid = 3;
						$cnrels = array(2);
						$conditions = array(
							1		=>	'area',
							12		=>	'type',
							'shi'	=>	'room',
							'ting'  =>  'ting',
							'mchid' =>  'puber',
							4		=>	'price',
							6       =>   'mian',
						);
						break;
					case 'zhu':
						$chid = 2;
						$cnrels = array(2);
						$conditions = array(
							1		=>	'area',
							12		=>	'type',
							'shi'	=>	'room',
							'ting'  =>  'ting',
							'mchid' =>  'puber',
							5		=>	'price',
							6       =>   'mian',
						);
						break;
					case 'zhuang' :           
					   $conditions = array(           
							1	    =>	'area',                
					   );
					   break;
					case 'mgs':
					$mchid = 12;
						$conditions = array(
							1		=>	'area', 
							'zycp' => 'product',               
						);
						break;
				}

				/*if(@$fcdisabled3){
					unset($conditions[3], $conditions[14]);
					$cnrels = array_filter($cnrels, create_function('$v', 'return $v!=2;'));
				}*/
				foreach($cnrels as $rid){
					if($cnrel = cls_cache::Read('cnrel', $rid)){
						$temp[$cnrel['coid1']] = &$cnrel;
						unset($cnrel);
					}
				}
				foreach(array('cotypes') as $k) $$k = cls_cache::Read($k);
				foreach($conditions as $key => $value){
					if(is_numeric($key)){//纯类系
						if($coclasses = cls_cache::Read('coclasses', $key)){
							echo "<$value name=\"".($t == 'xiaoqu' && $key == 17 ? '小区价格区间' : $cotypes[$key]['cname'])."\" value=\"";
							foreach($coclasses as $k => $v)echo "$k,$v[title]|";
							if(isset($temp[$key])){
								echo $conditions[$temp[$key]['coid']];
								foreach($temp[$key]['cfgs'] as $k => $v)echo " $k:$v";
							}
							echo '"/>';
						}
					}else{//字段
						if($field = isset($chid) ? cls_cache::Read('field', $chid, $key) : cls_cache::Read('mfield', $mchid, $key)){
							if(in_array($field['datatype'],array('select','mselect'))){//多选，单选字段
								echo "<$value name=\"{$field['cname']}\" value=\"";								
								$items = explode("\n", $field['innertext']);
								foreach($items as $v){
									$v = explode('=', $v);
									echo "$v[0],".(empty($v[1]) ? $v[0] : $v[1])."|";
								}
								echo "\"/>"; 
							}else if(in_array($field['datatype'],array('cacc'))){//类系字段
								$coclasses = cls_cache::Read('coclasses',$field['coid']);                        
								echo "<$value name=\"{$cotypes[$field['coid']]['cname']}\" value=\"";
								foreach($coclasses as $k => $v){
									if(in_array($key,array('zycp',))) {				           
									   if($v['level']!=0) echo "$k,$v[title]|";
									}else{
									   echo "$k,$v[title]|";
									}    					    
								 }			
								echo '"/>';
							}
						}else{//数据直接加的字段(只能手动配置)
							if($key == 'mchid'){                       
							  echo "<$value name=\"发布者\" value=\"1,个人|2,经纪人|\" />";
							}                                			    
						}
					}
				}     
				exit('</conditions>');
		}

	}
	// 参数格式：经,纬,经,纬; 分别把原点定位在：南极,北极,赤道+0度经线,赤道+国际日期变更线 附近看看是否正确？！
    // 用于组类似条件：" AND dt_1>'$bounds[0]' AND dt_1<'$bounds[2]' AND dt_0>'$bounds[1]' AND dt_0<'$bounds[3]'";
    // $re .= ' AND '.$fname.'_1>='.($y - $dfy < -180 ? $y - $dfy + 360 : $y - $dfy).' AND '.$fname.'_1<='.($y + $dfy > 180 ? $y + $dfy - 360 : $y + $dfy);
	protected function u_check_bounds(&$bounds){
		if(!$isbad = !$bounds){
			$bounds = explode(',', $bounds);
			foreach($bounds as $v)if(!$v || !is_numeric($v))$isbad = true;
		}
		if($isbad || count($bounds) != 4)exit('<floors total="0"/>');
	}
	
	protected function fieldSel($class,$chid,$key){
		$field = cls_cache::Read($class,$chid,$key);
		return cls_field::options($field);
   }
}