<?php
/**
 * 地图
 *
 * @example   请求范例URL：index.php?/ajax/newmap/entry/zhu/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Newmap extends _08_Models_Base
{
    public function __toString()
    {   
        global $ftp_enabled, $ftp_url;
		$mcharset = $this->_mcharset;
		header("Content-type: application/json; charset=UTF-8");	
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP;		
		$entry  = empty($this->_get['entry']) ? '' : trim($this->_get['entry']);
		$zoom = empty($this->_get['zoom']) ? 0 : max(1,intval($this->_get['zoom']));		
		$pagesize = 50;    
		$start = empty($this->_get['start']) ? 1 : max(1,intval($this->_get['start']));
		$pagestart = ($start-1)*$pagesize;
		$timestart = microtime(TRUE);
		$vaildStr = " AND (enddate=0 OR enddate>'".TIMESTAMP."')";
		$xml = '';
		$cms_abs = cls_env::mconfig('cms_abs');
		switch($entry){
		  case 'DistrictPoint'://房源地区显示数量(出售,出租)
          if(empty($this->_get['type'])) return;
              switch($this->_get['type']){
                 case 'chushou':
                    $chid = 3;
                    break;
                 case 'chuzu':
                    $chid = 2;
                 break;
                 case 'loupan':
                    $chid = 4;
                 break;
                 default:
                  return;
              }
          $coclass1 = cls_cache::Read('coclasses', 1);
          $DistrictPoint = array('project'=>array());
          foreach($coclass1 as $k => $v){
            $count = 0;
            if($query = $db->fetch_one('SELECT COUNT(*) cnt FROM ' . $tblprefix . atbl($chid) . ' WHERE ccid1 = ' . $k . $vaildStr)){
                $count = $query['cnt'];
            }
            $DistrictPoint['project'][] = array('name'=>$v['title'],'index'=>$k,'count'=>$count,'px'=>$v['dt_1'],'py'=>$v['dt_0']);
          }
          return $DistrictPoint;
		  break;		  
		  case 'ConditionData'://二手房筛选条件              
		  	  if(!isset($this->_get['mode'])){echo 'null';return;}    
			  switch($this->_get['mode']){
				  case 'chid_3'://二手房	(停用) 
    				  $ConditionData = array();
    				  $ConditionConfig = array('price'=>4,'district'=>1,'area'=>6,'wuye'=>12,'shi'=>'shi','ting'=>'ting');//筛选条件配置项
    				  $ConditionData = $this->getConditions($ConditionConfig,$this->_get['mode']);
    				  //针对二手房特殊添加非配置筛选条件(个人/经纪人)
    				  $ConditionData['publisher'] = array('text'=>array('个人','经纪人'),'value'=>array(1,2));
    				  return $ConditionData; 
				  break;
                  case 'chid_2'://出租
                      $ConditionData = array();
    				  $ConditionConfig = array('price'=>4,'district'=>1,'area'=>6,'wuye'=>12,'shi'=>'shi','ting'=>'ting');//筛选条件配置项
    				  $ConditionData = $this->getConditions($ConditionConfig,$this->_get['mode']);
    				  //针对二手房特殊添加非配置筛选条件(个人/经纪人)
    				  $ConditionData['publisher'] = array('text'=>array('个人','经纪人'),'value'=>array(1,2));
    				  return $ConditionData;
                  break;
                  case 'chid_4'://楼盘(停用)
                      $ConditionData = array();
                      $ConditionConfig = array('price'=>17,'district'=>1,'area'=>6,'purpose'=>12,'salestat'=>18,'shangquan'=>2,'loupantese'=>'tslp','zhuangxiuchengdu'=>'zxcd','louceng'=>'lcs','huanxian'=>'hxs','ditie'=>3,'ditiezhandian'=>14);//筛选条件配置项
    				  $ConditionData = $this->getConditions($ConditionConfig,$this->_get['mode']);
    				  //针对二手房特殊添加非配置筛选条件(个人/经纪人)
    				  return $ConditionData;
                 break;
                 case 'mchid_12'://品牌商家
                      $ConditionData = array();
                      $ConditionConfig = array('district'=>1,'product'=>'zycp',);//筛选条件配置项
    				  $ConditionData = $this->getConditions($ConditionConfig,$this->_get['mode']);
    				  //针对二手房特殊添加非配置筛选条件(个人/经纪人)
    				  return $ConditionData;
                break;
                case 'mchid_11'://装修公司
                      $ConditionData = array();
                      $ConditionConfig = array('district'=>1);//筛选条件配置项
    				  $ConditionData = $this->getConditions($ConditionConfig,$this->_get['mode']);
    				  //针对二手房特殊添加非配置筛选条件(个人/经纪人)
    				  return $ConditionData;
                break;
                case 'mchid_2'://经纪人
                      $ConditionData = array();
                      $ConditionConfig = array('district'=>1);//筛选条件配置项
    				  $ConditionData = $this->getConditions($ConditionConfig,$this->_get['mode']);
    				  //针对二手房特殊添加非配置筛选条件(个人/经纪人)
    				  return $ConditionData;
                break;
			  }
		   break;
		  case 'HouseData'://房源信息(出售,出租)
          if(empty($this->_get['type'])) return;
          $page = max(1,intval(isset($this->_get['page']) ? $this->_get['page'] : 0));$pageNum = 7;//每页条数
          $limitStr = ' LIMIT ' . ($page-1)*$pageNum . ',' . $pageNum;
          $x1 = empty($this->_get['x1']) ? 0 : floatval($this->_get['x1']);
          $x2 = empty($this->_get['x2']) ? 0 : floatval($this->_get['x2']);
          $y1 = empty($this->_get['y1']) ? 0 : floatval($this->_get['y1']);
          $y2 = empty($this->_get['y2']) ? 0 :floatval($this->_get['y2']);
		  $chid = $this->_get['type'] == 'chushou' ? 3 : 2;                
          $whereStr = 'WHERE a.chid= ' . $chid . ' AND a.checked=1 AND a.pid3!=0';
		  $whereStr .= $vaildStr;
          #$whereStr .= " AND a.dt_0 > '$y1' AND a.dt_0 < '$y2' AND a.dt_1 > '$x1' AND a.dt_1 < '$x2'";
		  $whereStr .= " AND a.dt_0 > 0 AND a.dt_1 > 0";
          switch($this->_get['type']){
            case 'chushou':
				!isset($this->_get['fwjg']) || !($fwjg=max(0,intval($this->_get['fwjg']))) || $whereStr .= ' AND CONCAT("\t",c.fwjg,"\t") like "%\t' . $fwjg .'\t%"';//fwjg
				case 'chuzu':
				!isset($this->_get['district']) || !($district = max(0,intval($this->_get['district']))) || !($where_district=cnsql(1, $district, 'a.')) || $whereStr .= ' AND ' . $where_district;//district
                !isset($this->_get['shangquan']) || !($shangquan = max(0,intval($this->_get['shangquan']))) || !($where_shangquan=cnsql(2, $shangquan, 'a.')) || $whereStr .= ' AND ' . $where_shangquan;//shangquan                
				!isset($this->_get['ditie']) || !($ditie = max(0,intval($this->_get['ditie']))) || !($where_ditie=cnsql(3, $ditie, 'a.')) || $whereStr .= ' AND ' . $where_ditie;//ditie
                !isset($this->_get['ditiezhandian']) || !($ditiezhandian = max(0,intval($this->_get['ditiezhandian']))) || !($where_ditiezhandian=cnsql(18, $ditiezhandian, 'a.')) || $whereStr .= ' AND ' . $where_ditiezhandian;//ditiezhandian
                !isset($this->_get['chu']) || !($chu=max(0,intval($this->_get['chu']))) || $whereStr .= ' AND a.chu = "' . $chu .'"';//chu
				!isset($this->_get['wei']) || !($wei=max(0,intval($this->_get['wei']))) || $whereStr .= ' AND a.wei = "' . $wei .'"';//wei
				!isset($this->_get['yangtai']) || !($yangtai=max(0,intval($this->_get['yangtai']))) || $whereStr .= ' AND a.yangtai = "' . $yangtai .'"';//yangtai
				!isset($this->_get['shi']) || !($shi=max(0,intval($this->_get['shi']))) || $whereStr .= ' AND a.shi = "' . $shi .'"';//shi
                !isset($this->_get['ting']) || !($ting=max(0,intval($this->_get['ting']))) || $whereStr .= ' AND a.ting  = "' . $ting . '"';//ting
                !isset($this->_get['fl']) || !($fl=max(0,intval($this->_get['fl']))) || $whereStr .= ' AND c.fl = "' . $fl .'"';//fl
				!isset($this->_get['fangling']) || !($fangling = max(0,intval($this->_get['fangling']))) || !($where_fangling=cnsql(34, $fangling)) || $whereStr .= ' AND ' . $where_fangling;//district			
				!isset($this->_get['cx']) || !($cx=max(0,intval($this->_get['cx']))) || $whereStr .= ' AND c.cx = "' . $cx .'"';//cx
				!isset($this->_get['zxcd']) || !($zxcd=max(0,intval($this->_get['zxcd']))) || $whereStr .= ' AND c.zxcd = "' . $zxcd .'"';//zxcd
                !isset($this->_get['fwpt']) || !($fwpt=max(0,intval($this->_get['fwpt']))) || $whereStr .= ' AND CONCAT("\t",c.fwpt,"\t") like "%\t' . $fwpt .'\t%"';//fwpt
				!isset($this->_get['price']) || !($price = max(0,intval($this->_get['price']))) || !($where_price=cnsql(($chid==3?4:5), $price, 'a.')) || $whereStr .= ' AND ' . $where_price;//price
                !isset($this->_get['area']) || !($area = max(0,intval($this->_get['area']))) || !($where_area=cnsql(6, $area, 'a.')) || $whereStr .= ' AND ' . $where_area;//area
                !isset($this->_get['publisher']) || !($publisher=max(0,intval($this->_get['publisher']))) || $whereStr .= ' AND a.mchid = "' . $publisher . '"';//publisher
                !isset($this->_get['projcode']) || !($projcode=max(0,intval($this->_get['projcode']))) || $whereStr .= ' AND a.pid3 = "' . $projcode . '"';//projcode
                if($chid==2) !isset($this->_get['zlfs']) || !($zlfs=max(0,intval($this->_get['zlfs']))) || $whereStr .= ' AND c.zlfs = "' . $zlfs .'"';//zlfs
				if($chid==2) !isset($this->_get['fkfs']) || !($fkfs=max(0,intval($this->_get['fkfs']))) || $whereStr .= ' AND c.fkfs = "' . $fkfs .'"';//fkfs
				$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_" . $chid . " c ON a.aid=c.aid " . $whereStr . " ORDER BY a.aid DESC " .$limitStr;
				$query = $db->query($sql); 
                $allCount = $db->fetch_one('SELECT FOUND_ROWS() AS cnt');
                $HouseData = array("allcount"=>$allCount['cnt'],'house'=>array(),'project'=>array());
                if(!empty($projcode)){
                  if($projData = $db->fetch_one("SELECT  * FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE a.aid = \"" . $projcode . "\" AND (c.leixing = 0 OR c.leixing = 2)" )){
                    cls_ArcMain::Url($projData,-1);
                    $HouseData['project']['projcode'] = $projData['aid'];//
                    #$HouseData['project']['projname'] = cls_string::iconv($mcharset,'UTF-8',$projData['subject']);
                    $HouseData['project']['projname'] = $projData['subject'];             
                    $HouseData['project']['price'] = $chid ==3 ? $projData['cspjj'] : $projData['czpjj'];//房源均价
                	#$HouseData['project']['address'] = cls_string::iconv($mcharset,'UTF-8',$projData['address']);
                    $HouseData['project']['address'] = $projData['address'];
                	$HouseData['project']['projurl'] = $projData['arcurl7'];
                	$HouseData['project']['projimg'] = empty($projData['thumb']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $projData['thumb'];
                	}
                }
                $shiSel = $this->fieldSel('field',$chid,'shi');
                $tingSel = $this->fieldSel('field',$chid,'ting');
                while($row = $db->fetch_array($query)){
                   cls_ArcMain::Url($row,-1);
                   $thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['thumb'];
                   $room = isset($shiSel[$row['shi']]) ? $shiSel[$row['shi']] : '';
                   $ting = isset($tingSel[$row['ting']]) ? $tingSel[$row['ting']] : '';
                   $price = $row['zj'];
                   #$tmp = array('houseid'=>$row['aid'],'title'=>cls_string::iconv($mcharset,'UTF-8',$row['subject']),'shorttitle'=>cls_string::iconv($mcharset,'UTF-8',cls_string::CutStr($row['subject'],30,'')),'projcode'=>$row['pid3'],'projname'=>cls_string::iconv($mcharset,'UTF-8',$row['lpmc']),'buildarea'=>cls_string::iconv($mcharset,'UTF-8',$row['mj']),'room'=>cls_string::iconv($mcharset,'UTF-8',$room),'hall'=>cls_string::iconv($mcharset,'UTF-8',$ting),'floor'=>cls_string::iconv($mcharset,'UTF-8',$row['szlc']),'totalfloor'=>cls_string::iconv($mcharset,'UTF-8',$row['zxcd']),'price'=>cls_string::iconv($mcharset,'UTF-8',$price),'houseimg'=>$thumb,'houseurl'=>$row['arcurl']);
                   $tmp = array('houseid'=>$row['aid'],'title'=>$row['subject'],'shorttitle'=>cls_string::CutStr($row['subject'],30,''),'projcode'=>$row['pid3'],'projname'=>$row['lpmc'],'buildarea'=>$row['mj'],'room'=>$room,'hall'=>$ting,'floor'=>$row['szlc'],'totalfloor'=>$row['zxcd'],'price'=>$price,'houseimg'=>$thumb,'houseurl'=>$row['arcurl']);
                   $HouseData['house'][] = $tmp;
                }                
                return $HouseData;
                break;                
            default:
                return;
          }	  
		  
		  break;
		  case 'CityPoint': //城市坐标点信息
		  $district = max(0,intval($this->_get['district']));
		  $CityPoint = array('point'=>array());
		  $ccid1 = cls_cache::Read('coclass',1,$district);
		  $CityPoint['point'][] = array('id'=>$ccid1['ccid'],'name'=>$ccid1['title'],'px'=>$ccid1['dt_1'],'py'=>$ccid1['dt_0']);
		  return $CityPoint;
		  break;
		  case 'CommunityPointData';//小区显示(出售,出租)
            if(empty($this->_get['type'])) return;
            $page = max(1,intval(isset($this->_get['page']) ? $this->_get['page'] : 0));$pageNum = 50;//每页条数
            
            $limitStr = ' LIMIT ' . ($page-1)*$pageNum . ',' . $pageNum;
            $district = empty($this->_get['district']) ? 0 : max(0,intval($this->_get['district']));
            #$keyword = empty($this->_get['keyword']) ? '' :$this->unescape($this->_get['keyword']);
			$keyword = empty($this->_get['keyword']) ? '' :@cls_string::iconv("utf-8",$mcharset,$this->_get['keyword']);
            $x1 = empty($this->_get['x1']) ? 0 : floatval($this->_get['x1']);
			$x2 = empty($this->_get['x2']) ? 0 : floatval($this->_get['x2']);
			$y1 = empty($this->_get['y1']) ? 0 : floatval($this->_get['y1']);
			$y2 = empty($this->_get['y2']) ? 0 :floatval($this->_get['y2']);
            switch($this->_get['type']){
                case 'chushou':
                case 'chuzu':
                    $chid = $this->_get['type'] == 'chushou' ? 3 : 2;
                    $whereStr = "WHERE (b.leixing = 0 OR b.leixing = 2) AND a.checked = 1 AND dt_0 > 0 AND dt_1 > 0 " . (empty($district) ? '' : " AND a.ccid1 = '" . $district . "'") . (empty($keyword) ? '' : " AND a.subject like '%" . $keyword . "%'");
                    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . $tblprefix . atbl(4) . " a INNER JOIN " . $tblprefix . "archives_4 b ON a.aid = b.aid " . $whereStr.$limitStr;
                    $query=$db->query($sql); //echo $sql;
                    $allCount = $db->fetch_one('SELECT FOUND_ROWS() AS cnt');
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid12Sel = $this->ccidSel(12);	
                    while($row = $db->fetch_array($query)){
						cls_ArcMain::Url($row,-1);
						$thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['thumb'];
						$wuye = $this->ccidText($row['ccid12'],$ccid12Sel);
						#$housecnt = $chid == 3 ? $row['aesfys'] : $row['aczfys'];
						if($chid == 3){
							$fycnt = $db->fetch_one("select count(*) cnt from " . $tblprefix . atbl(3) ." where pid3 = " . $row['aid']." $vaildStr");
						}else{
							$fycnt = $db->fetch_one("select count(*) cnt from " . $tblprefix . atbl(2) ." where pid3 = " . $row['aid']." $vaildStr");
						}
						$fycnt = empty($fycnt['cnt']) ? 0 : $fycnt['cnt'];
						
						#$tmp = array('projcode'=>$row['aid'],'projname'=>cls_string::iconv($mcharset,'UTF-8',$row['subject']),'housecount'=>$housecnt,'purpose'=>cls_string::iconv($mcharset,'UTF-8',$wuye),'address'=>cls_string::iconv($mcharset,'UTF-8',$row['address']),'px'=>$row['dt_1'],'py'=>$row['dt_0'],'addresslong'=>cls_string::iconv($mcharset,'UTF-8',$row['address']));
						$tmp = array('projcode'=>$row['aid'],'projname'=>$row['subject'],'housecount'=>$fycnt,'purpose'=>$wuye,'address'=>$row['address'],'px'=>$row['dt_1'],'py'=>$row['dt_0'],'addresslong'=>$row['address']);
						$communityData['project'][] = $tmp;
                    }                    	
                    return $communityData;
                break;
                case 'loupan':
                    $chid = 4;             
                    $order = isset($this->_get['order']) ? $this->_get['order'] : '';
                    $orderArr = array('0%0'=>'ORDER BY a.aid DESC','0%1'=>'ORDER BY a.aid ASC','1%0'=>'ORDER BY a.dj DESC','1%1'=>'ORDER BY a.dj ASC','2%0'=>'ORDER BY b.kpsj DESC','2%1'=>'ORDER BY b.kpsj ASC','3%0'=>'ORDER BY b.jgdate DESC','3%1'=>'ORDER BY b.jgdate ASC');            
                    $orderStr = empty($order) ? '' : (isset($orderArr[$order]) ? $orderArr[$order] : '');
                    #$boundStr = " dt_0 > '$y1' AND dt_0 < '$y2' AND dt_1 > '$x1' AND dt_1 < '$x2' ";
                    #$whereStr = "WHERE (b.leixing = 1 OR b.leixing = 2) AND a.checked = 1 AND " . $boundStr . (empty($keyword) ? '' : " AND a.subject like '%" . $keyword . "%'");
                    $whereStr = "WHERE (b.leixing = 1 OR b.leixing = 0) AND a.checked = 1 AND a.dt_1>0 AND a.dt_1>0 " . (empty($keyword) ? '' : " AND a.subject like '%" . $keyword . "%'");
                    
					!isset($this->_get['district']) || !($district = max(0,intval($this->_get['district']))) || !($where_district=cnsql(1, $district, 'a.')) || $whereStr .= ' AND ' . $where_district;//district
					!isset($this->_get['shangquan']) || !($shangquan = max(0,intval($this->_get['shangquan']))) || !($where_shangquan=cnsql(2, $shangquan, 'a.')) || $whereStr .= ' AND ' . $where_shangquan;//shangquan
                    !isset($this->_get['area']) || !($area = max(0,intval($this->_get['area']))) || !($where_area=cnsql(1, $area, 'a.')) || $whereStr .= ' AND ' . $where_area;//area
					!isset($this->_get['purpose']) || !($wuye = max(0,intval($this->_get['purpose']))) || !($where_wuye=cnsql(12, $wuye, 'a.')) || $whereStr .= ' AND ' . $where_wuye;//wuye
                    !isset($this->_get['price']) || !($price = max(0,intval($this->_get['price']))) || !($where_price=cnsql(17, $price, 'a.')) || $whereStr .= ' AND ' . $where_price;//price
                    !isset($this->_get['salestat']) || !($salestat = max(0,intval($this->_get['salestat']))) || !($where_salestat=cnsql(18, $salestat, 'a.')) || $whereStr .= ' AND ' . $where_salestat;//salestat
                    !isset($this->_get['ditie']) || !($ditie = max(0,intval($this->_get['ditie']))) || !($where_ditie=cnsql(3, $ditie, 'a.')) || $whereStr .= ' AND ' . $where_ditie;//ditie
                    !isset($this->_get['ditiezhandian']) || !($ditiezhandian = max(0,intval($this->_get['ditiezhandian']))) || !($where_ditiezhandian=cnsql(18, $ditiezhandian, 'a.')) || $whereStr .= ' AND ' . $where_ditiezhandian;//ditiezhandian
					if(isset($this->_get['loupantese'])){
						$tslp = max(0,intval($this->_get['loupantese']));
						$whereStr .= ' AND CONCAT("\t",b.tslp,"\t") like \'%\t' . $tslp . '\t%\'';
					}
					if(isset($this->_get['zhuangxiuchengdu'])){
						$zhuangxiuchengdu = max(0,intval($this->_get['zhuangxiuchengdu']));
						$whereStr .= ' AND zxcd =' . $zhuangxiuchengdu ;
					}
					if(isset($this->_get['louceng'])){
						$louceng = max(0,intval($this->_get['louceng']));
						$whereStr .= ' AND CONCAT("\t",lcs,"\t") like \'%\t' . $louceng . '\t%\'';
					}
					if(isset($this->_get['huanxian'])){
						$huanxian = max(0,intval($this->_get['huanxian']));
						$whereStr .= ' AND hxs = ' . $huanxian;
					}
					$sql = "SELECT * FROM " . $tblprefix . atbl($chid) . " a INNER JOIN " . $tblprefix . "archives_" . $chid . " b ON a.aid = b.aid " . $whereStr.' '.$orderStr.$limitStr;
                    
					$query=$db->query($sql);
                    #$allCountWhere = "WHERE (b.leixing = 1 OR b.leixing = 2) AND a.checked = 1 AND ". $boundStr . (empty($district) ? '' : " AND a.ccid1 = '" . $district . "'") . (empty($keyword) ? '' : " AND a.subject like '%" . $keyword . "%'");
                    $allCountSql = "SELECT count(*) AS cnt FROM ". $tblprefix . atbl($chid) . " a INNER JOIN " . $tblprefix . "archives_" . $chid . " b ON a.aid = b.aid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid12Sel = $this->ccidSel(12);
                    $ccid18Sel = $this->ccidSel(18);
					
					$ccid18Config = cls_cache::Read('coclasses',18);
					//194=>'1',195=>'2',196=>'3',197=>'4',198=>'5'
                    $salestat_pic = array();
					foreach($ccid18Config as $ccid18Configk => $ccid18Configv){						
						$salestat_pic[$ccid18Configk] = $ccid18Configv['vieworder'];
					}
                    while($row = $db->fetch_array($query)){
                        cls_ArcMain::Url($row,-1);
                        $ccid12Sel = $this->ccidSel(12);
                        $wuye = $this->ccidText($row['ccid12'],$ccid12Sel);
                        $kpsj = date('Y-m-d',$row['kpsj']);
                        $thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['thumb'];
                        $salestat = $this->ccidText($row['ccid18'],$ccid18Sel);
                        $salepic = isset($salestat_pic[$row['ccid18']]) ? $salestat_pic[$row['ccid18']] : '';
                        $project = array('projcode'=>$row['aid'],'projname'=>$row['subject'],'img'=>$thumb,'salecode'=>$row['ccid18'],'salestat'=>$salestat,'salepic'=>$salepic,'purpose'=>$wuye,'price'=>$row['dj'],'tel'=>$row['tel'],'time'=>($row['kpsj']?date('Y-m-d',$row['kpsj']):'待定'),'address'=>$row['address'],'px'=>$row['dt_1'],'py'=>$row['dt_0'],'kfs'=>$row['kfsname'],'url'=>$row['arcurl'],'url1'=>$row['arcurl1'],'url2'=>$row['arcurl2'],'url3'=>$row['arcurl3'],'url4'=>$row['arcurl4'],'url5'=>$row['arcurl5'],'url6'=>$row['arcurl6']);
                        #$tmp = array('projcode'=>$row['aid'],'projname'=>$row['subject'],'projimg'=>$thumb,'price'=>$row['dj'],'purpose'=>$wuye,'address'=>$row['address'],'px'=>$row['dt_1'],'py'=>$row['dt_0'],'addresslong'=>$row['address'],'tel'=>$row['tel']);
                        $communityData['project'][] = $project;
                    }                    
                    return $communityData;
                break;
                case 'Shangjia':
                    $mchid = 12;
                    $order = isset($this->_get['order']) ? $this->_get['order'] : '';
                    $orderArr = array('0%0'=>'ORDER BY a.mid DESC','0%1'=>'ORDER BY a.mid ASC','1%0'=>'ORDER BY a.dj DESC','1%1'=>'ORDER BY a.dj ASC','2%0'=>'ORDER BY b.kpsj DESC','2%1'=>'ORDER BY b.kpsj ASC','3%0'=>'ORDER BY b.jgdate DESC','3%1'=>'ORDER BY b.jgdate ASC');            
                    $orderStr = empty($order) ? '' : (isset($orderArr[$order]) ? $orderArr[$order] : '');
                    //$boundStr = " map_0 > '$y1' AND map_0 < '$y2' AND map_1 > '$x1' AND map_1 < '$x2' ";
					$boundStr = " map_0 > 0 AND map_1 > 0 ";
                    $whereStr = "WHERE a.checked = 1 AND " . $boundStr . (empty($keyword) ? '' : " AND b.companynm like '%" . $keyword . "%'");
                    !isset($this->_get['district']) || !($district = max(0,intval($this->_get['district']))) || !($where_district="c.szqy='".$district."'") || $whereStr .= ' AND ' . $where_district;//district
                    !isset($this->_get['product']) || !($product = max(0,intval($this->_get['product']))) || !($where_product="b.zycp='".$product."'") || $whereStr .= ' AND ' . $where_product;//product
                    
                    $sql = "SELECT * FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr.$orderStr.$limitStr;
                    $query=$db->query($sql);
                    $allCountSql = "SELECT COUNT(*) AS cnt FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid1Sel = $this->ccidSel(1);
                    $ccid31Sel = $this->ccidSel(31);                    
                    while($row = $db->fetch_array($query)){
                        $mspaceurl = cls_Mspace::IndexUrl($row);
                        $district = $this->ccidText($row['szqy'],$ccid1Sel);
                        $product = $this->ccidText($row['zycp'],$ccid31Sel);
                        $thumb = empty($row['pic']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['pic'];
                        $vip = $row['grouptype32']==104 ? 1 : 0;
                        $project = array('projcode'=>$row['mid'],'projname'=>$row['companynm'],'district'=>$district,'vip'=>$vip,'img'=>$thumb,'product'=>$product,'district'=>$district,'conactor'=>$row['conactor'],'tel'=>$row['lxdh'],'address'=>$row['dizhi'],'px'=>$row['map_1'],'py'=>$row['map_0'],'url'=>$mspaceurl);
                        $communityData['project'][] = $project;
                    }                    
                    return $communityData;                    
                break;
                case 'Zhuangxiu':
                    $mchid = 11;
                    $order = isset($this->_get['order']) ? $this->_get['order'] : '';
                    $orderArr = array('0%0'=>'ORDER BY a.mid DESC','0%1'=>'ORDER BY a.mid ASC','1%0'=>'ORDER BY a.dj DESC','1%1'=>'ORDER BY a.dj ASC','2%0'=>'ORDER BY b.kpsj DESC','2%1'=>'ORDER BY b.kpsj ASC','3%0'=>'ORDER BY b.jgdate DESC','3%1'=>'ORDER BY b.jgdate ASC');            
                    $orderStr = empty($order) ? '' : (isset($orderArr[$order]) ? $orderArr[$order] : '');
                    $boundStr = " map_0 > 0 AND  map_1 > 0 ";
                    $whereStr = "WHERE a.checked = 1 AND " . $boundStr . (empty($keyword) ? '' : " AND b.companynm like '%" . $keyword . "%'");
                    !isset($this->_get['district']) || !($district = max(0,intval($this->_get['district']))) || !($where_district="c.szqy='".$district."'") || $whereStr .= ' AND ' . $where_district;//district
                    $sql = "SELECT * FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr.$orderStr.$limitStr;
                    $query=$db->query($sql);
                    $allCountSql = "SELECT COUNT(*) AS cnt FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid1Sel = $this->ccidSel(1);                   
                    while($row = $db->fetch_array($query)){
                        $mspaceurl = cls_Mspace::IndexUrl($row);
                        $district = $this->ccidText($row['szqy'],$ccid1Sel);
                        $thumb = empty($row['pic']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['pic'];
                        $project = array('projcode'=>$row['mid'],'projname'=>$row['companynm'],'district'=>$district,'net'=>$row['internet'],'img'=>$thumb,'district'=> $district,'conactor'=>$row['conactor'],'tel'=>$row['lxdh'],'address'=>$row['dizhi'],'px'=>$row['map_1'],'py'=>$row['map_0'],'url'=>$mspaceurl);
                        $communityData['project'][] = $project;
                    }                    
                    return $communityData; 
                break;
                case 'Jingjiren':
                    $mchid = 2;
                    $order = isset($this->_get['order']) ? $this->_get['order'] : '';
                    $orderArr = array('0%0'=>'ORDER BY a.mid DESC','0%1'=>'ORDER BY a.mid ASC','1%0'=>'ORDER BY a.dj DESC','1%1'=>'ORDER BY a.dj ASC','2%0'=>'ORDER BY b.kpsj DESC','2%1'=>'ORDER BY b.kpsj ASC','3%0'=>'ORDER BY b.jgdate DESC','3%1'=>'ORDER BY b.jgdate ASC');            
                    $orderStr = empty($order) ? '' : (isset($orderArr[$order]) ? $orderArr[$order] : '');
                    $boundStr = " map_0 > '$y1' AND map_0 < '$y2' AND map_1 > '$x1' AND map_1 < '$x2' ";
                    $whereStr = "WHERE a.checked = 1 AND " . $boundStr . (empty($keyword) ? '' : " AND a.companynm like '%" . $keyword . "%'");
                    !isset($this->_get['district']) || !($district = max(0,intval($this->_get['district']))) || !($where_district="c.szqy='".$district."'") || $whereStr .= ' AND ' . $where_district;//district
                    $sql = "SELECT * FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr.$orderStr.$limitStr;
                    $query=$db->query($sql);
                    $allCountSql = "SELECT COUNT(*) AS cnt FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid1Sel = $this->ccidSel(1);                   
                    while($row = $db->fetch_array($query)){
                        $mspaceurl = cls_Mspace::IndexUrl($row);
                        $district = $this->ccidText($row['szqy'],$ccid1Sel);
                        $thumb = empty($row['pic']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['pic'];
                        $project = array('projcode'=>$row['mid'],'projname'=>$row['companynm'],'district'=>$district,'net'=>$row['internet'],'img'=>$thumb,'district'=>$row['dj'],'conactor'=>$row['conactor'],'tel'=>$row['lxdh'],'address'=>$row['dizhi'],'px'=>$row['map_1'],'py'=>$row['map_0'],'url'=>$mspaceurl);
                        $communityData['project'][] = $project;
                    }                    
                    return $communityData; 
                break;
                default:
                return;
            }
		  break;
		  case 'marker'://独立页120在使用该处		 
		  	 $chid = empty($this->_get['chid']) ? 0 : intval($this->_get['chid']);
		  	 if(empty($chid)) return;            
			if(!$loupanid = isset($this->_get['Project']) ? $this->_get['Project'] : '') return;
			if(!$in = $this->str2in($loupanid)) return;
			$inStr = ' AND a.aid IN(' . $in . ') ';                   
			$whereStr = "WHERE a.checked = 1 " . $inStr;
			$sql = "SELECT * FROM " . $tblprefix . atbl($chid) . " a INNER JOIN " . $tblprefix . "archives_" . $chid . " b ON a.aid = b.aid " . $whereStr;
			$allCountSql = "SELECT count(*) AS cnt FROM ". $tblprefix . atbl($chid) . " a INNER JOIN " . $tblprefix . "archives_" . $chid . " b ON a.aid = b.aid " . $whereStr;
			$allCount = $db->fetch_one($allCountSql);
			$communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
			//$ccid12Sel = $this->ccidSel(12);
			//$ccid18Sel = $this->ccidSel(18);
			//$salestat_pic = array(194=>'1',195=>'2',196=>'3',197=>'4',198=>'5');
			$query=$db->query($sql);
			while($row = $db->fetch_array($query)){
				cls_ArcMain::Url($row,-1);								
				$salepic = 2;
				$project = array('projcode'=>$row['aid'],'projname'=>$row['subject'],'salepic'=>$salepic,'price'=>@$row['dj'],'px'=>$row['dt_1'],'py'=>$row['dt_0'],'url'=>@$row['arcurl'],'url1'=>@$row['arcurl1'],'url2'=>@$row['arcurl2'],'url3'=>@$row['arcurl3'],'url4'=>@$row['arcurl4'],'url5'=>@$row['arcurl5'],'url6'=>@$row['arcurl6'],'url7'=>@$row['arcurl7']);
				$communityData['project'][] = $project;
			}                   
			return $communityData;
		  break;
          case 'history' :
            if(empty($this->_get['type'])) return;
            switch($this->_get['type']){
                case 'loupan' ://大地图和独立页120在使用
                    if(!$loupanid = isset($this->_get['HistoryProject']) ? $this->_get['HistoryProject'] : '') return;
                    if(!$in = $this->str2in($loupanid)) return;
                    $inStr = ' AND a.aid IN(' . $in . ') ';
                    $chid = 4;
                    $whereStr = "WHERE (b.leixing = 1 OR b.leixing = 0) AND a.checked = 1 " . $inStr;
                    $sql = "SELECT * FROM " . $tblprefix . atbl($chid) . " a INNER JOIN " . $tblprefix . "archives_" . $chid . " b ON a.aid = b.aid " . $whereStr;
                    $allCountSql = "SELECT count(*) AS cnt FROM ". $tblprefix . atbl($chid) . " a INNER JOIN " . $tblprefix . "archives_" . $chid . " b ON a.aid = b.aid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid12Sel = $this->ccidSel(12);
                    $ccid18Sel = $this->ccidSel(18);
                    #$salestat_pic = array(194=>'1',195=>'2',196=>'3',197=>'4',198=>'5');
					$ccid18Config = cls_cache::Read('coclasses',18);
					//194=>'1',195=>'2',196=>'3',197=>'4',198=>'5'
                    $salestat_pic = array();
					foreach($ccid18Config as $ccid18Configk => $ccid18Configv){						
						$salestat_pic[$ccid18Configk] = $ccid18Configv['vieworder'];
					}
                    $query=$db->query($sql);
                    while($row = $db->fetch_array($query)){
                        cls_ArcMain::Url($row,-1);
                        $ccid12Sel = $this->ccidSel(12);
                        $wuye = $this->ccidText($row['ccid12'],$ccid12Sel);
                        $kpsj = date('Y-m-d',$row['kpsj']);
                        $thumb = empty($row['thumb']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['thumb'];
                        $salestat = $this->ccidText($row['ccid18'],$ccid18Sel);
                        $salepic = isset($salestat_pic[$row['ccid18']]) ? $salestat_pic[$row['ccid18']] : '';
                        $project = array('projcode'=>$row['aid'],'projname'=>$row['subject'],'img'=>$thumb,'salecode'=>$row['ccid18'],'salestat'=>$salestat,'salepic'=>$salepic,'purpose'=>$wuye,'price'=>$row['dj'],'tel'=>$row['tel'],'time'=>$row['kpsj'],'address'=>$row['address'],'px'=>$row['dt_1'],'py'=>$row['dt_0'],'kfs'=>$row['kfsname'],'url'=>$row['arcurl'],'url1'=>$row['arcurl1'],'url2'=>$row['arcurl2'],'url3'=>$row['arcurl3'],'url4'=>$row['arcurl4'],'url5'=>$row['arcurl5'],'url6'=>$row['arcurl6']);
                        $communityData['project'][] = $project;
                    }                   
                    return $communityData;
                break;				
                case 'Shangjia' :
                    if(!$loupanid = isset($this->_get['HistoryProject']) ? $this->_get['HistoryProject'] : '') return;
                    if(!$in = $this->str2in($loupanid)) return;
                    $inStr = ' AND a.mid IN(' . $in . ') ';
                    $mchid = 12;
                    $whereStr = "WHERE a.checked = 1 " . $inStr;
                    $sql = "SELECT * FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $query=$db->query($sql);
                    $allCountSql = "SELECT COUNT(*) AS cnt FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid1Sel = $this->ccidSel(1);
                    $ccid31Sel = $this->ccidSel(31);                    
                    while($row = $db->fetch_array($query)){
                        $mspaceurl = cls_Mspace::IndexUrl($row);
                        $dsitrict = $this->ccidText($row['szqy'],$ccid1Sel);
                        $product = $this->ccidText($row['zycp'],$ccid31Sel);
                        $thumb = empty($row['pic']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['pic'];
                        $vip = $row['grouptype32']==104 ? 1 : 0;
                        $project = array('projcode'=>$row['mid'],'projname'=>$row['companynm'],'vip'=>$vip,'img'=>$thumb,'product'=>$product,'district'=>$dsitrict,'conactor'=>$row['conactor'],'tel'=>$row['lxdh'],'address'=>$row['dizhi'],'px'=>$row['map_1'],'py'=>$row['map_0'],'url'=>$mspaceurl);
                        
                        $communityData['project'][] = $project;
                    }                    
                    return $communityData;
                break;
                case 'Zhuangxiu':
                    if(!$loupanid = isset($this->_get['HistoryProject']) ? $this->_get['HistoryProject'] : '') return;
                    if(!$in = $this->str2in($loupanid)) return;
                    $inStr = ' AND a.mid IN(' . $in . ') ';
                    $mchid = 11;
                    $whereStr = "WHERE a.checked = 1 " . $inStr;
                    $sql = "SELECT * FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $query=$db->query($sql);
                    $allCountSql = "SELECT COUNT(*) AS cnt FROM " . $tblprefix ."members a INNER JOIN " . $tblprefix . "members_" . $mchid . " b ON a.mid = b.mid INNER JOIN " . $tblprefix . "members_sub c ON a.mid = c.mid " . $whereStr;
                    $allCount = $db->fetch_one($allCountSql);
                    $communityData = array('allcount'=>$allCount['cnt'],'project'=>array());
                    $ccid1Sel = $this->ccidSel(1);                 
                    while($row = $db->fetch_array($query)){
                        $mspaceurl = cls_Mspace::IndexUrl($row);
                        $district = $this->ccidText($row['szqy'],$ccid1Sel);
                        $thumb = empty($row['pic']) ? $cms_abs . 'images/common/nopic.gif' : (empty($ftp_enabled)?$cms_abs:$ftp_url) . $row['pic'];
                        $project = array('projcode'=>$row['mid'],'projname'=>$row['companynm'],'district'=>$district,'net'=>$row['internet'],'img'=>$thumb,'district'=>$district,'conactor'=>$row['conactor'],'tel'=>$row['lxdh'],'address'=>$row['dizhi'],'px'=>$row['map_1'],'py'=>$row['map_0'],'url'=>$mspaceurl);
                        $communityData['project'][] = $project;
                    }                    
                    return $communityData;
                default:
                return;
            }
          break;
          default:
          return;
		}

	}
	
    protected function unescape($str){
		$mcharset = strtoupper($this->_mcharset);
		$str = rawurldecode($str);
		preg_match_all("/(?:%u.{4})|&#x.{4};|&#\d+;|.+/U",$str,$r);
		$ar = $r[0];    
		foreach($ar as $k=>$v) {
			if(substr($v,0,2) == "%u"){
				$ar[$k] = iconv("UCS-2",$mcharset,pack("H4",substr($v,-4)));
			}elseif(substr($v,0,3) == "&#x"){
				$ar[$k] = iconv("UCS-2",$mcharset,pack("H4",substr($v,3,-1)));
			}elseif(substr($v,0,2) == "&#") {
		  		$ar[$k] = iconv("UCS-2",$mcharset,pack("n",substr($v,2,-1)));
			}
		}
		return join("",$ar);
	}   
    
    protected function str2in($str){
        $arr = explode(',',$str);
        $arr = array_filter($arr);
        foreach($arr as $k=>$v){
            $arr[$k] = max(0,intval($v));
        }
        return implode(',',$arr);
	}  
	
	/**
	 * 根据字段
	 *
	 **/
	
	protected function fieldSel($class,$chid,$key){
		$field = cls_cache::Read($class,$chid,$key);
		return cls_field::options($field);
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
   
   /**
    * 根据配置参数获取筛选条件
	* @parma array $configs 配置字段参数
	* @parma string $chidMode 模型参数
	*/
   protected function getConditions($configs=array(),$chidMode=''){
	   $conditions = array();
	   foreach($configs as $k => $v){
			   if(is_numeric($v)){
					if($coclass = cls_cache::Read('coclasses', $v)){
						foreach($coclass as $coclass_k => $coclass_v){
								$conditions[$k]['text'][] = $coclass_v['title'];
								$conditions[$k]['value'][] = $coclass_v['ccid'];
							}
					}
				}elseif(is_string($v)){
					$mode = substr($chidMode,0,strpos($chidMode,'_'));
					$modeNum = substr($chidMode,strpos($chidMode,'_')+1);										
					if(!empty($v)){		
						switch($mode){							
							case 'mchid':
								$mchid = intval($modeNum);
                                $field = cls_cache::Read('mfield', $mchid, $v);
                                if(in_array($field['datatype'],array('select','mselect'))){
									$items = explode("\n",$field['innertext']);
									foreach($items as $items_v){
										$items_v = explode('=', $items_v);
										$conditions[$k]['text'][] = $items_v[1];
										$conditions[$k]['value'][] = $items_v[0];
									}
								}elseif(in_array($field['datatype'],array('cacc',))){
									$coclass = cls_cache::Read('coclasses',$field['coid']);
                                    if(31==$field['coid']){//产品分类特殊处理
                                        foreach($coclass as $coclass_k => $coclass_v){
                                            if(0 != $coclass_v['level']){
                                                $conditions[$k]['text'][] = $coclass_v['title'];
                                                $conditions[$k]['value'][] = $coclass_v['ccid'];
                                            }                                         
                                        }
                                    }else{
                                        foreach($coclass as $coclass_k => $coclass_v){
                                            $conditions[$k]['text'][] = $coclass_v['title'];
                                            $conditions[$k]['value'][] = $coclass_v['ccid'];
                                        }
                                    }
								}
							break;					
							case 'chid':				
								$chid = intval($modeNum);
								$field = cls_cache::Read('field', $chid, $v);
								if(in_array($field['datatype'],array('select','mselect'))){
									$items = explode("\n",$field['innertext']);
									foreach($items as $items_v){
										$items_v = explode('=', $items_v);
										$conditions[$k]['text'][] = $items_v[1];
										$conditions[$k]['value'][] = $items_v[0];
									}
								}elseif(in_array($field['datatype'],array('cacc',))){
									$coclass = cls_cache::Read('coclasses',$field['coid']);
									foreach($coclass as $coclass_k => $coclass_v){
										$conditions[$k]['text'][] = $coclass_v['title'];
										$conditions[$k]['value'][] = $coclass_v['ccid'];
									}
								}								
							break;						
						}
					}
				}
	   }
	   return $conditions;
	}
}