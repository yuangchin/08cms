<?php
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
m_clear_ob(1);
empty($action) && $action = '';
/**
 * 允许Ajax跨域，只要在调用JS时多传递一个参数： domain=news.08cms.com   这个域可自定义，
 * 考虑到安全问题，这个域必须在后台： 系统设置 -> 附属设置 -> 域名管理 里同时存在该域的网址才行
 * 
 * @example $.get($cms_abs + "tools/ajax.php?action=get_regcode&domain=" + document.domain, function(data) { .... });
 */
if ( !empty($domain) )
{
    if ( $domain == $cms_top )
    {
        $domainValue = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $cms_top . '/';
    }
    else
    {
    	$domains = cls_cache::Read('domains');
        foreach($domains['to'] as $domainValue)
        {
            if ( $domainValue && ($hostInfo = parse_url($domainValue)) )
            {
                if ( ($domain == $hostInfo['host']) )
                {
                    $domainValue = $hostInfo['host'];
                    break;
                }
            }
        }
    }
    $domainValue && cls_HttpStatus::trace(array('Access-Control-Allow-Origin' => substr($domainValue, 0, -1)));
}

switch($action){
case 'ajax_arc_list': //选择所属合辑(特价房/团购/装修案例添加),参考tools下的相关代码
/*
	$chid = max(0,intval($chid));
	$mid = max(0,intval($mid));
	!empty($keywords) && $keywords = @cls_string::iconv("UTF-8",$mcharset,$keywords);
	$result = array(); 
	if($ntbl = atbl($chid)){ 
		$db->select('a.*,c.*')->from("#__{$ntbl} a")->innerJoin("#__archives_{$chid} c");
		$db->_on("a.aid=c.aid")->where("checked=1 AND ".(empty($mid) ? "leixing IN(0,1)" : "mid=$mid").""); 
		$db->_and('a.subject')->like($keywords)->limit(100)->exec();
		//if(!empty($query)){
			while($r=$db->fetch()){
				$thumb = $r['thumb'];
				$thumb = empty($thumb) ? '' : '[图]';
				$result[] = array('aid' => $r['aid'], 'subject'=>$thumb.$r['subject'],'create'=>date('Y-m-d',$r['createdate']));
			}
		//}
	}
	echo cls_message::ajax_info($result);
*/
break;
case 'checkUnique': // 游客发布房源时，检查电话号码是否存在
/*	cls_cache::Load('mctypes,mchannels'); //,mchannels,channels
	$val = empty($val) ? '-1' : $val;

	$sql = "SELECT mid FROM {$tblprefix}members_sub WHERE lxdh='$val'";
	$mid = $db->result_one($sql);
	// 是否普通会员或经纪人
	$sql = "SELECT mid FROM {$tblprefix}members WHERE mid='$mid' AND mchid IN(1,2)";
	$sid = $db->result_one($sql);
	$msg = $sid ? '号码已经存在于系统会员中，不能使用！' : '';

	mexit($msg);*/
	break;	
case 'lp_commus': //楼盘内容页印象、评分: (ajax)	
	/*
	header("content-type: text/javascript; charset=$mcharset");
	$aid = empty($aid) ? 0 : max(0,intval($aid));
	$fields = cls_cache::Read( 'cufields',2);
	
	
	//楼盘印象显示
	$_sql = $db->query("SELECT cid,impression,renshu FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1' order by cid DESC  limit 15");
	$_total_num = $db->result_one("SELECT SUM(renshu) FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1'");
	$s0 = '';
	while($_rows = $db->fetch_array($_sql)){
		$s0 .= "<a style='cursor:pointer' rel='nofollow' onclick=\"add_yinxiang2('".$_rows['cid']."','".$_rows['impression']."')\">".$_rows['impression']."<span id=\"yx_".$_rows['cid']."\">(".(round($_rows['renshu']/$_total_num,3)*100)."%)</span></a>";
	}
	$s0 = str_replace(array("'","\n","\r"),array("\\'","\\n","\\r"),$s0); //echo "alert('$s0');";
	echo "if(\$('#items')){ \$('#items').html('$s0'); }";
	
	//楼盘点评分数显示	
	$acolor = array('#F27C78','#EFBE23','#8DCA48','#8BD3E9','#6BB6D6','#BDA3E2','#5B89C7','#E192C2','#EF9B39');
	$cn = 0;
	$_str = 'total';
	foreach($fields as $k => $v){
		if($v['available']) $_str .= ",".$k;
	}
	$_dp_arr = $db->fetch_one("SELECT ".$_str." FROM {$tblprefix}commu_dp WHERE aid = '$aid' AND mname = '' ");
	$show_pf = ''; 
	$show_pf .= "<div class=\"tc fw6 mb5\">综合评分<b id=\"total\">".(empty($_dp_arr['total'])?'0':$_dp_arr['total'])."</b>/100</div><ul>";					
	foreach($fields as $k2=>$v2){
		$key = $v2['ename']; 
		if($key!='pjzj'){
			if($v2['datatype'] != 'select'){
				$cn++; 
				$cn = $cn%9;
				$show_pf .= "<li>";
				$show_pf .= "<span>$v2[cname]：</span>";
				$show_pf .= "<span class='per'><em id=\"".substr($k2,0,strpos($k2,'r'))."ys\" style=\"width:".(empty($_dp_arr[substr($k2,0,strpos($k2,'r'))]) ? '0' : $_dp_arr[substr($k2,0,strpos($k2,'r'))])."%;background-color:$acolor[$cn] \"></em></span>";
				$show_pf .= "<span id=\"".substr($k2,0,strpos($k2,'r'))."\">".(empty($_dp_arr[substr($k2,0,strpos($k2,'r'))]) ? '0' : $_dp_arr[substr($k2,0,strpos($k2,'r'))])."分</span>"; 
				$show_pf .= "<span id=\"".$k2."\">".(empty($_dp_arr[$k2]) ? '0' : $_dp_arr[$k2])."人&nbsp;&nbsp;</span>";
				$show_pf .= "</li>"; 	
			}
		} 
	}
					
	$show_pf .= "";
	echo "\n\n\$('#lp-content-left').html('".str_replace(array("'","\n","\r"),array("\\'","\\n","\\r"),$show_pf)."');";
	
	//我要评分
	
	$pf_str = '';
	$pf_str .= "<div class=\"tc fw6 mb5\">我要评分</div><ul class=\"lp-tlist3\" id=\"stars\">";					
	foreach($fields as $k3=>$v3){ 
		$key = $v3['ename']; 
		if($key!='pjzj'){//排除留言和回复
			if($v3['datatype']=='select'){
				$pf_str .=  "<li>";
				$pf_str .=  "<i class='l lbl'>$v3[cname]：</i><i class='star l'><b></b><div class='blank0'></div>";
				for($i=1;$i<11;$i++) $pf_str .= "<a date-ename='".$v3['ename']."'>".$i."</a>";
				$pf_str .=  "</i><i class='tip'><b>0</b>分</i>"; 
				$pf_str .=  "</li>"; 
			}
		} 
	}
	$pf_str .="";
	echo "\n\n\$('#lp-content-right').html('".str_replace(array("'","\n","\r"),array("\\'","\\n","\\r"),$pf_str)."');";		
	*/
break;
case 'yuedu_xinqing'://读完文章后，心情
/*
	$fields = cls_cache::Read('cufields','41');
	$aid = empty($aid)?0:max(1,intval($aid));
	$dianping = $db->fetch_one("SELECT * FROM {$tblprefix}commu_zxdp where aid = '$aid'");
	$str = '<ul>';
	foreach($fields as $k => $v){
		$str .= "<li><div class=\"gsmod\">";
		$str .= "<div class=\"sz\" id=\"".$k."\">(".(empty($dianping[$k])?'0':$dianping[$k]).")</div>";
		$str .= "<div class=\"gsbar\"><div class=\"actbar\"></div></div></div>";
		$str .= "<div class=\"gsface\"><span id=\"".$aid."_".$v['ename']."\" class=\"facemod facemod".$k." ".(isset($_COOKIE['HiK_08cms_cuid14_dp_'.$v['ename'].'_'.$aid])?'on':'')."\" onclick=\"return zixun_dianping(".$aid.",'".$v['ename']."');\" onmouseover=\"window.status='done';return true;\"></span></div></li>";
	}
	$str .= '</ul>';
	echo "\n\n\$('#xinqing').html('".str_replace(array("'","\n","\r"),array("\\'","\\n","\\r"),$str)."');";*/
break;

case 'lpexist': // 检测楼盘是否重复: (ajax)
	/*header("content-type: text/html; charset=$mcharset");
	$lpname = cls_string::iconv('utf-8',$mcharset,$lpname);
	if($rec = $db->result_one("SELECT aid FROM {$tblprefix}".atbl(4)." WHERE subject='$lpname'")){
		mexit("[$lpname] 已经存在！");
	}else{
		mexit("succeed");	
	}*/

case 'setanswer':
	/*header("content-type: text/html; charset=$mcharset");
	$aid = empty($aid) ? 0 : max(0,intval($aid));
	if(empty($aid)) mexit('参数出错！');
	$db->query("UPDATE {$tblprefix}".atbl(106)." SET close='".(!empty($type)?0:1)."' WHERE aid='$aid'");
	mexit('succeed');*/
break;
case 'relArchives': // 售楼公司-管理的楼盘
/*
	if($mcharset != "utf-8"){
		$keywords = cls_string::iconv('utf-8',$mcharset,@$keywords);
		$subject = cls_string::iconv('utf-8',$mcharset,@$subject);
	}
	$chid = empty($chid) ? 0 : max(0,intval($chid)); 
	if(!$chid) cls_message::show('文档模型参数错误。');
	$sql = "SELECT a.aid,a.subject FROM {$tblprefix}".atbl($chid)." a ";
	$sql .= " INNER JOIN  {$tblprefix}archives_$chid c ON c.aid=a.aid";
	$sql .= " WHERE c.leixing IN(0,1) ";
	$keywords && $sql .= " AND (a.subject LIKE '%$keywords%') ";
	$sql .= " ORDER BY aid DESC ";
	
	$query=$db->query("$sql limit 100"); $s = "";
	if(!empty($query)){
		while($r=$db->fetch_array($query)){ // ($r[mname])
			$s .= "<div onclick=\"relAddItem(this,$r[aid])\"><span id='arcid_$r[aid]'>$r[subject]</span></div>";	
		}
	}
	//echo "$s";
	exit(js_callback($s));
	exit();*/

case 'ftend': //房团结束判断 action=ftend&enddate={$enddate}&type=1; $timestamp
/*	header("Content-Type:text/html;CharSet=$mcharset");
	empty($enddate) && $enddate = 0; //$enddate = $timestamp+1;
	empty($type) && $type = '1';
	$flag = ($enddate<$timestamp)?true:false; //未过期
	if($flag){
		// 过期要处理的东西....
		//echo "alert('xx1');";
		switch($action){
			case 'xxxxx':
				break;
			default: //1
				echo "document.getElementById('ftend_link1').className='btn';";  // ftend_link1-btn btn_old
				echo "document.getElementById('ftend_link1').className='btn-old';"; 
				echo "document.getElementById('ftend_baoming').innerHTML='已经过期 (加样式)';";  // ftend_baoming 
				break;
		}
		//echo "alert('xx2');";
	}*/
	exit();
case 'map':
/*	empty($entry) && $entry = '';
	$xml = '';
	$pagesize = 50;    
    $start = max(1,intval(@$start));
    $start == 0 && $start = 1;   
	$pagestart = ($start-1)*$pagesize;
	$timestart = microtime(TRUE);
    $zoom = max(0,intval(@$zoom));
	foreach(array('mconfigs') as $k) $$k = cls_cache::Read($k);
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
        exit("<floors time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");
    break;
	case 'xin':# 新房列表              
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
		if(!empty($station))unset($metro);
		$where = "chid=4 AND checked=1 AND (leixing=0 OR leixing=1)";
		u_check_bounds($bounds);
		$where .= " AND dt_1>'$bounds[0]' AND dt_1<'$bounds[2]' AND dt_0>'$bounds[1]' AND dt_0<'$bounds[3]'";
		empty($area) || $where .= ' AND ' . cnsql(1, $area, 'a.');        
		empty($type) || $where .= ' AND ' . cnsql(12, $type , 'a.');
		empty($price) || $where .= ' AND ' . cnsql(17, $price, 'a.');			 
		empty($metro) || $where .= ' AND ' . cnsql(3, $metro, 'a.');
		empty($station) || $where .= ' AND ' . cnsql(14, $station, 'a.');
		if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";		
		$sqlsub = " FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE $where";
		$query = $db->query("SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,a.dj,a.ccid18 $sqlsub ORDER BY c.aid DESC LIMIT $pagestart,$pagesize");
        //if($start!=1) exit("SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,a.dj,a.ccid18 $sqlsub ORDER BY c.aid DESC LIMIT $pagestart,$pagesize");
        $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;"); 
        while($row = $db->fetch_array($query)){           
			$row = mhtmlspecialchars($row);
			$xml .= "<floor aid=\"$row[aid]\" subject=\"$row[subject]\" x=\"$row[dt_1]\" y=\"$row[dt_0]\" price=\"".$row['dj'] ."\" stat=\"".($row['ccid18'] - 193)."\" />";
		} 
		exit("<floors total=\"".$r_cnt['cnt']."\" time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");
	case 'houses':# 楼盘信息
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><hosues>";
		#"<!--SELECT COUNT(*)$sqlsub-->"
		$aid = empty($aid)?0:max(0,intval($aid));
		$sql = "SELECT a.*,c.*,k.subject AS kfs FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid LEFT JOIN {$tblprefix}".atbl(13)." k ON a.pid6=k.aid WHERE c.aid='$aid' AND a.checked=1 AND a.dt!='' AND (a.enddate=0 OR a.enddate>'$timestamp') LIMIT 1";
		if($row = $db->fetch_one($sql)){		
			arc_parse($row);
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
			echo "<house aid=\"$row[aid]\" title=\"$row[subject]\" mid=\"$row[mid]\" img=\"$thumbUrl\" company=\"$row[kfs]\" phone=\"$row[tel]\" address=\"$row[address]\" date=\"$row[kprq]\" stat=\"" . ($row['ccid18'] ? $row['ccid18'] - 193 : 0) . "\" type=\"" . implode(' | ', $result) . "\" wygs=\"$row[wygs]\" dj=\"$row[dj]\" arcurl=\"$row[arcurl]\" $arcurlx />";
		}
		exit('</hosues>');
    case 'zhuang':#装修公司
  		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
		$where = "checked=1 AND mchid=11";		
		u_check_bounds($bounds);
		$where .= " AND map_1>'$bounds[0]' AND map_1<'$bounds[2]' AND map_0>'$bounds[1]' AND map_0<'$bounds[3]'";
		empty($area) || $where .= " AND szqy='".max(0,intval($area))."'";
        if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND b.companynm LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";		
		$sqlsub = " FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_11 b ON b.mid=m.mid WHERE $where";	   
		$query = $db->query("SELECT SQL_CALC_FOUND_ROWS*,m.*,mchid,map_0,map_1,companynm,conactor,dizhi,internet $sqlsub ORDER BY m.mid DESC LIMIT $pagestart,$pagesize");
		 $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");
        while($row = $db->fetch_array($query)){
			$mspaceurl = mhtmlspecialchars(cls_Mspace::IndexUrl($row));			
            $row['companynm'] = mhtmlspecialchars($row['companynm']);       
			$xml .= "<floor aid=\"$row[mid]\" subject=\"$row[companynm]\" x=\"$row[map_1]\" y=\"$row[map_0]\" stat=\"" . ($row['mchid'] - 10) . "\" />";
		}
		exit("<floors total=\"$r_cnt[cnt]\" time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");
	case 'zhua' :#单个装修
        header("Content-type: application/xml; charset=$mcharset");
        $mid = max(0,intval($aid));
        $row = $db->fetch_one("SELECT * FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_11 b ON b.mid=m.mid WHERE m.mid='$mid'");
        if($row){            
            $row = mhtmlspecialchars($row);
            $mspaceurl = cls_Mspace::IndexUrl($row);
            $vip = $row['grouptype31']==102 ? 1 : 0;  
		  $xml = "<member mid=\"".$row['mid']."\" subject=\"".$row['companynm']."\" vip=\"".$vip."\" url=\"".$mspaceurl."\" address=\"".$row['dizhi']."\" conactor=\"".$row['conactor']."\"  tel=\"".$row['lxdh']."\" img=\"".$row['pic']."\" />";
		}
        echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
		exit("<floors time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>"); 
        break;
    case 'mgs':#商家
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
		$where = "checked=1 AND mchid=12";			
		u_check_bounds($bounds);
		$where .= " AND map_1>'$bounds[0]' AND map_1<'$bounds[2]' AND map_0>'$bounds[1]' AND map_0<'$bounds[3]'";
		empty($area) || $where .= " AND szqy='".max(0,intval($area))."'";
        empty($product) || $where .= " AND zycp = '".max(0,intval($product))."'";
        if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND b.companynm LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";
		$sqlsub = " FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON m.mid=s.mid INNER JOIN {$tblprefix}members_12 b ON b.mid=m.mid WHERE $where";
		$query = $db->query("SELECT SQL_CALC_FOUND_ROWS*,m.*,mchid,map_0,map_1,companynm $sqlsub ORDER BY m.mid DESC LIMIT $pagestart,$pagesize");
		$r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");
        while($row = $db->fetch_array($query)){				
            $row['companynm'] = mhtmlspecialchars($row['companynm']);          	
			$xml .= "<floor aid=\"$row[mid]\" subject=\"$row[companynm]\" x=\"$row[map_1]\" y=\"$row[map_0]\" stat=\"" . ($row['mchid'] - 10) . "\" />";
        }
		exit("<floors total=\"$r_cnt[cnt]\" time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");
    case 'shang' :#单个商家信息
    	header("Content-type: application/xml; charset=$mcharset");
        $mid = max(0,intval($aid));
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
		exit("<floors time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");    
	case 'zhu':#小区(以小区名和出租数量显示)
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
		#if(!empty($station))unset($metro);
		u_check_bounds($bounds);
		$where	= "a.chid=4 AND a.checked=1 AND a.dt!='' AND c.chid=2 AND c.checked=1 AND (c.enddate=0 OR c.enddate>'$timestamp')"
				. " AND a.dt_1>'$bounds[0]' AND a.dt_1<'$bounds[2]' AND a.dt_0>'$bounds[1]' AND a.dt_0<'$bounds[3]' AND (f.leixing in (0,2))";
		empty($area) || $where .= ' AND ' . cnsql(1, $area, 'c.');
		empty($type) || $where .= ' AND ' . cnsql(12, $type, 'c.');
		empty($price) || $where .= ' AND ' . cnsql(5, $price, 'c.');     
		#empty($metro) || $where .= ' AND ' . cnsql(3, $metro, 'a.');
		#empty($station) || $where .= ' AND ' . cnsql(14, $station, 'a.');
		empty($room) || $where .= " AND c.shi='".max(0,intval($room))."'";
        empty($ting) || $where .= " AND c.ting='".max(0,intval($ting))."'";
        empty($chu) || $where .= " AND c.chu='".max(0,intval($chu))."'";
        empty($wei) || $where .= " AND c.wei='".max(0,intval($wei))."'";
        empty($mian) || $where .= ' AND ' .cnsql(6, $mian, 'c.');
        empty($puber) || $where .= " AND c.mchid='".max(0,intval($puber))."'";//个人发布，经纪人发布
		if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND c.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";
		$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,c.mchid,count(c.aid) AS count FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}".atbl(2)." c ON c.pid3=a.aid INNER JOIN {$tblprefix}archives_4 f ON a.aid = f.aid INNER JOIN {$tblprefix}archives_2 d ON c.aid=d.aid WHERE $where GROUP BY a.aid LIMIT $pagestart,$pagesize";
        $query = $db->query($sql);
        $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");
		while($row = $db->fetch_array($query)){		
			$row = mhtmlspecialchars($row);		
			$xml .= "<floor aid=\"$row[aid]\" subject=\"$row[subject]\" x=\"$row[dt_1]\" y=\"$row[dt_0]\" count=\"$row[count]\" />";
		}
		exit("<floors total=\"$r_cnt[cnt]\" time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");
	case 'zhufang':# (小区)出租房信息列表数据
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><hosues>";
        $aid = max(0,intval($aid));
		$sql = "SELECT * FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE a.aid='$aid' AND a.dt!='' AND checked=1 AND (enddate=0 OR enddate>'$timestamp') LIMIT 1";
		if($row = $db->fetch_one($sql)){
		    arc_parse($row);       
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
			$aid = empty($aid)?0:max(0,intval($aid));
			$where = "a.chid=2 AND a.pid3='$aid' AND a.checked=1 AND (a.enddate=0 OR a.enddate>'$timestamp')";		
			empty($type) || $where .= ' AND ' . cnsql(12, $type, 'a.');
			empty($price) || $where .= ' AND ' . cnsql(5, $price, 'a.');
            empty($room) || $where .= " AND a.shi='".max(0,intval($room))."'";            
            empty($ting) || $where .= " AND a.ting='".max(0,intval($ting))."'";
            empty($chu) || $where .= " AND a.chu='".max(0,intval($chu))."'";
            empty($wei) || $where .= " AND a.wei='".max(0,intval($wei))."'";
            empty($mian) || $where .= ' AND ' .cnsql(6, $mian, 'a.'); 
            empty($puber) || $where .= " AND a.mchid='".max(0,intval($puber))."'";//个人发布，经纪人发布
            if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";          
			$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.*,c.* FROM {$tblprefix}".atbl(2)." a INNER JOIN {$tblprefix}archives_2 c ON a.aid=c.aid WHERE $where ORDER BY $order,refreshdate DESC";# LIMIT $start,6";
			$query = $db->query($sql);
            $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");  
			$thumbUrl = cls_url::tag2atm($row['thumb']);        
            echo "<floor aid=\"$row[aid]\" title=\"".$row['subject']."\" address=\"".$row['sldz']."\" count=\"".$r_cnt['cnt']."\" junjia=\"".$row['czpjj']."\" thumb=\"$thumbUrl\" url=\"".urlencode($row['arcurl7'])."\" />";#         
            function fieldSel($class,$chid,$key){
                $arr = array();
                $field = cls_cache::Read($class,$chid,$key);
               	$items = explode("\n", $field['innertext']);
                if($items['available']) return $arr;             
			    foreach($items as $v){
 						$v = explode('=', $v);
 						if(!isset($v[1])) {
 						    $arr[] = $v[1];                         
                         }else{
                           $arr[$v[0]] = $v[1]; 
                         }
                    }
                return $arr;
           }
           //单选字段配置
            $shiSel = fieldSel('field',2,'shi');
            $tingSel = fieldSel('field',2,'ting');
            $chuSel = fieldSel('field',2,'chu');
            $weiSel = fieldSel('field',2,'wei');
            $yangSel = fieldSel('field',2,'yangtai');                            
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
                echo "<house aid=\"$con[aid]\" subject=\"$con[subject]\" refresh=\"" . $refresh . "\" shi=\"$shi\" ting=\"$ting\" chu=\"$chu\" wei=\"$wei\" yang=\"$yang\" floors=\"$con[zlc]\" floor=\"$con[szlc]\" area=\"$con[mj]\" price=\"$zj\" puber=\"$con[mchid]\" url=\"$con[arcurl]\" thumb=\"$thumbUrl\" />";
			}
		}
		exit('</hosues>');
	case 'mai':# 二手房楼盘
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>";
		if(!empty($station))unset($metro);
#		$page = empty($page) ? 1 : max(1, intval($page));
#		$start = ($page - 1) * 50;
		u_check_bounds($bounds);
		$where	= "a.chid=4 AND a.checked=1 AND a.dt!='' AND c.chid=3 AND c.checked=1 AND (c.enddate=0 OR c.enddate>'$timestamp')"
				. " AND a.dt_1>'$bounds[0]' AND a.dt_1<'$bounds[2]' AND a.dt_0>'$bounds[1]' AND a.dt_0<'$bounds[3]' AND (f.leixing in (0,2))";
		
		empty($area) || $where .= ' AND ' . cnsql(1, $area, 'a.');
		empty($type) || $where .= ' AND ' . cnsql(12, $type, 'c.');
		empty($price) || $where .= ' AND ' . cnsql(4, $price, 'c.');
		//empty($metro) || $where .= ' AND ' . cnsql(3, $metro, 'a.');
		//empty($station) || $where .= ' AND ' . cnsql(14, $station, 'a.');
		empty($room) || $where .= " AND c.shi='".max(0,intval($room))."'";
        empty($ting) || $where .= " AND c.ting='".max(0,intval($ting))."'";
        empty($chu) || $where .= " AND c.chu='".max(0,intval($chu))."'";
        empty($wei) || $where .= " AND c.wei='".max(0,intval($wei))."'";
        empty($mian) || $where .= ' AND ' .cnsql(6, $mian, 'c.');
        empty($puber) || $where .= " AND c.mchid='".max(0,intval($puber))."'";//个人发布，经纪人发布
		if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND c.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'";
		$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.aid,a.subject,a.dt_1,a.dt_0,c.mchid,count(c.aid) AS count FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}".atbl(3)." c ON c.pid3=a.aid INNER JOIN {$tblprefix}archives_4 f ON a.aid=f.aid WHERE $where GROUP BY a.aid LIMIT $pagestart,$pagesize";
		
        $query = $db->query($sql);
        $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");	
		while($row = $db->fetch_array($query)){
			$row = mhtmlspecialchars($row);
			$result = array();
			$xml .= "<floor aid=\"$row[aid]\" subject=\"$row[subject]\" x=\"$row[dt_1]\" y=\"$row[dt_0]\" count=\"$row[count]\" />";
		}
		exit("<floors total=\"$r_cnt[cnt]\" time=\"" . (microtime(TRUE) - $timestart) . "\">$xml</floors>");	
    case 'maifang':# 出租房源信息列表数据
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><hosues>";
		$sql = "SELECT * FROM {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON a.aid=c.aid WHERE a.aid='$aid' AND a.dt!='' AND checked=1 AND (enddate=0 OR enddate>'$timestamp') LIMIT 1";
		if($row = $db->fetch_one($sql)){		    
		    cls_ArcMain::Parse($row);
            //var_dump($row['arcurl7']);exit;					
            $page = empty($page) ? 1 : max(1, intval($page));
	#		$start = ($page - 1) * 6;
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
			$aid = empty($aid)?0:max(0,intval($aid));
			$where = "a.chid=3 AND a.pid3='$aid' AND a.checked=1 AND (a.enddate=0 OR a.enddate>'$timestamp')";
			foreach(array('cotypes') as $k) $$k = cls_cache::Read($k);  
			empty($type) || $where .= ' AND ' . cnsql(12, $type, 'a.');
			empty($price) || $where .= ' AND ' . cnsql(4, $price, 'a.');
            empty($room) || $where .= " AND a.shi='".max(0,intval($room))."'";
            empty($ting) || $where .= " AND a.ting='".max(0,intval($ting))."'";
            empty($chu) || $where .= " AND a.chu='".max(0,intval($chu))."'";
            empty($wei) || $where .= " AND a.wei='".max(0,intval($wei))."'";
            empty($mian) || $where .= ' AND ' .cnsql(6, $mian, 'a.');
            empty($puber) || $where .= " AND a.mchid='".max(0,intval($puber))."'";//个人发布，经纪人发布
            if(!empty($keyword) && $keyword = @cls_string::iconv('UTF-8',$mcharset,$keyword)) $where .= " AND a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword, '%_'))."%'"; 
			$sql = "SELECT SQL_CALC_FOUND_ROWS*,a.*,c.* FROM {$tblprefix}".atbl(3)." a INNER JOIN {$tblprefix}archives_3 c ON a.aid=c.aid WHERE $where ORDER BY $order,refreshdate DESC";# LIMIT $start,6";            
            $query = $db->query($sql);
            $r_cnt = $db->fetch_one("SELECT FOUND_ROWS() AS cnt;");                  
            echo "<floor aid=\"$row[aid]\" title=\"".$row['subject']."\" address=\"".$row['sldz']."\" count=\"".$r_cnt['cnt']."\" junjia=\"".$row['cspjj']."\" thumb=\"".$row['thumb']."\" url=\"".urlencode($row['arcurl7'])."\" />";#         
            function fieldSel($class,$chid,$key){
                $arr = array();
                $field = cls_cache::Read($class,$chid,$key);
               	$items = explode("\n", $field['innertext']);
                if($items['available']) return $arr;          
			    foreach($items as $v){
 						$v = explode('=', $v);
 						if(!isset($v[1])) {
 						    $arr[] = $v[1];                      
                         }else{
                           $arr[$v[0]] = $v[1];
                         }
                    }
                return $arr;
           }
           //单选字段配置
            $shiSel = fieldSel('field',2,'shi');
            $tingSel = fieldSel('field',2,'ting');
            $chuSel = fieldSel('field',2,'chu');
            $weiSel = fieldSel('field',2,'wei');
            $yangSel = fieldSel('field',2,'yangtai');                          
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
                echo "<house aid=\"$con[aid]\" subject=\"$con[subject]\" refresh=\"" . $refresh . "\" shi=\"$shi\" ting=\"$ting\" chu=\"$chu\" wei=\"$wei\" yang=\"$yang\" floors=\"$con[zlc]\" floor=\"$con[szlc]\" area=\"$con[mj]\" price=\"$zj\" puber=\"$con[mchid]\" url=\"$con[arcurl]\" thumb=\"$thumbUrl\" />";
			}
		}
		exit('</hosues>');  
        
	case 'condition'://地图全局搜索条件。(租房，二手房列表中的搜索条件建立在此基础上)
		header("Content-type: application/xml; charset=$mcharset");
		echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?><conditions>";
		empty($value) && $value = '';$t = $value;
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
		if(@$fcdisabled3){
			unset($conditions[3], $conditions[14]);
			$cnrels = array_filter($cnrels, create_function('$v', 'return $v!=2;'));
		}
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
    						echo "$v[0]," . (empty($v[1]) ? $v[0] : $v[1]) . '|';
    					}
    					echo '"/>'; 
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
		exit('</conditions>');*/
	/*case 'circum':
		# 周边
		(!is_numeric($latitude) || !is_numeric($longitude) || !is_numeric($distance)) && die();
		$latitude = floatval($latitude);
		$longitude = floatval($longitude);
		$aid = empty($aid)?0:max(0,intval($aid));
		$caid = empty($caid)?0:max(0,intval($caid)); 
		#3公里
		$distance = empty($circum_km) ? 3 : $circum_km;
		$markerfield = 'dt';	
		#地图范围搜索sql生成		
		$sqlstr = cls_dbother::MapSql($latitude, $longitude, $distance, 1, $markerfield);
		
		//针对二手房，出租房源的周边			
		if(!empty($chid) && is_numeric($chid) && in_array($chid,array('2','3'))){
			$chid = (int)$chid;
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
				break;
	}
	break;*/
case 'ajaxloupan':
/*
	!empty($keywords) && $keywords = @cls_string::iconv("UTF-8",$mcharset,$keywords);
	$query = $db->query("select a.*,s.* from {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 s on a.aid=s.aid where a.chid=4 and (a.subject like '%$keywords%' or s.address like '%$keywords%') AND (s.leixing=0 OR s.leixing=2) AND a.checked = '1' limit 0,100");
	$result = array();
	if(!empty($query)){
		while($row=$db->fetch_array($query)){
			$result[] = array('aid' => $row['aid'], 'subject'=>$row['subject'],'ccid1'=>$row['ccid1'],'ccid2'=>$row['ccid2'],'ccid3'=>$row['ccid3'],'ccid14'=>$row['ccid14'],'address'=>$row['address'],'dt'=>$row['dt'],'thumb'=>cls_url::view_atmurl(preg_replace('','',$row['thumb']))); [#\d*]
		}
	}
	echo cls_message::ajax_info($result);
*/
break;
case 'modifyprice':
	/*header("Content-Type:text/html;CharSet=$mcharset");
	$cid = empty($cid) ? '' : max(0,intval($cid));
	$zj = empty($zj) ? '' : max(0,floatval($zj));
	if(!$cid || !$zj) mexit('参数出错！');
	$cuid = 36;
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) mexit('委托功能已关闭。');
	if(!empty($memberid)){
		$db->query("UPDATE {$tblprefix}$commu[tbl] SET zj='$zj' WHERE cid='$cid' AND mid='$memberid'");
		mexit($db->affected_rows() ? 'SUCCEED' : '没有做任何修改。');
	}else
		mexit('请先登陆会员。');*/
break;
case 'delweituo':
/*	header("Content-Type:text/html;CharSet=$mcharset");
	$cid = empty($cid) ? '' : max(0,intval($cid));
	$cuid = 36;
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) mexit('委托功能已关闭。');
	if(!empty($memberid)){
		if($memberid == 1){//当管理员以托管形式进入会员中心，进行委托房源删除时，获取被托管的会员的ID
			define('M_MCENTER', TRUE); // 用于代表以下操作是属性会员中心
			$member_info = $curuser->isTrusteeship();
			$memberid = $member_info['mid'];
		}
		$db->query("DELETE FROM {$tblprefix}weituos WHERE cid='$cid' AND fmid='$memberid'");
		$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$cid' AND mid='$memberid'");
		mexit($db->affected_rows() ? 'SUCCEED' : '操作失败。');
	}else
		mexit('请先登陆会员。');*/
break;
case 'cancelweituo':
/*	header("Content-Type:text/html;CharSet=$mcharset");
	$wid = empty($wid) ? '' : max(0,intval($wid));
	if(!$wid) mexit('参数出错！');
	$cuid = 36;
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) mexit('委托功能已关闭。');
	if(!empty($memberid)){
		if($memberid == 1){//当管理员以托管形式进入会员中心，进行委托房源删除时，获取被托管的会员的ID
			define('M_MCENTER', TRUE); // 用于代表以下操作是属性会员中心
			$member_info = $curuser->isTrusteeship();
			$memberid = $member_info['mid'];
		}
		$db->query("DELETE FROM {$tblprefix}weituos WHERE wid='$wid' AND fmid='$memberid'");
		mexit($db->affected_rows() ? 'SUCCEED' : '操作失败。');
	}else	mexit('请先登陆会员。');*/
break;
case 'fangyuan':
/*	header("Content-Type:text/html;CharSet=$mcharset");
	if(!empty($aids)){		
		$aids = explode(',',$aids);		
		$chid = $caid == 3?'3':'2';
		$i = 0;
		$_data = array();
		foreach($aids as $k => $v){
			$v = empty($v) ? '0' : max(0,intval($v));
			$_sql = $db->query("SELECT * FROM {$tblprefix}".atbl($chid)." WHERE aid = '$v'");
			$_result = $db->fetch_array($_sql);
			if($_result){
				$_data[$i]['arcurl'] = cls_ArcMain::Url($_result);
				$_data[$i]['aid'] = $_result['aid'];
				$_data[$i]['subject'] = $_result['subject']; 
				$_data[$i]['zj'] = @$_result['zj'];
				$_data[$i]['mj'] = @$_result['mj'];
				$_data[$i]['arcurl'] = $_result['arcurl'];
				$i++;
			}
		}
		// echo jsonEncode($_data);
		$_data = cls_string::iconv($mcharset, "UTF-8", $_data);
		echo 'var fangyuan = ' . json_encode($_data) . ';';
	}*/
break;
//对楼盘添加印象
case 'addyinxiang':
/*	header("Content-Type:text/html;CharSet=$mcharset");	
	//$cid = empty($cid) ? '0' : max(0,intval($cid));
	$aid = empty($aid) ? '0' : max(0,intval($aid));
	$yinxiang = cls_string::iconv('utf-8',$mcharset,$yinxiang);	
	empty($yinxiang) && exit("印象不能为空。");	
	if(!$_cfgs = cls_cache::Read('commu',44)){
		echo "<script>alert(\"请指定正确的交互项目。\");return;</script>";	
	}
	$show_num = 15;

	
	$_cid = $db->result_one("SELECT cid FROM {$tblprefix}commu_impression WHERE aid='$aid' AND impression = '$yinxiang'");
	if(empty($_cid)){
		$check = $curuser->pmautocheck(@$_cfgs['autocheck'],'cuadd') ? 1 : 0 ;
		$_insert_sql = "INSERT INTO {$tblprefix}commu_impression SET aid = '$aid',impression = '$yinxiang',createdate = '$timestamp',renshu = '1',checked = '$check',ip = '$onlineip'";		
		if($db->query($_insert_sql)){			
			if($curuser->pmautocheck($_cfgs['autocheck'],'cuadd')) exit("添加印象成功，刷新即可查看。");
			else exit("添加印象成功，请等待审核。");
		}
	}else{
		!empty($subm) && exit('该印象已存在，请添加其他的印象。');//subm用来区分是通过点击印象还是提交印象传递过来的参数
		$db->query("UPDATE {$tblprefix}commu_impression SET renshu = renshu + 1 WHERE cid = '$_cid'");
		$_sql = $db->query("SELECT cid,impression,renshu FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1' ORDER BY cid DESC");
		$_total_num = $db->result_one("SELECT SUM(renshu) FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1'");
		$_yx_arr = array();
		$i = 1;		
		while($_rows = $db->fetch_array($_sql)){
			if($i == $show_num) break;//限制输出的印象个数
			$_yx_arr[]['cid'] = $_rows['cid'];
			$_yx_arr[]['baifenbi'] = round($_rows['renshu']/$_total_num,3)*100;
			$i++;
		}
		$_cid_check = $db->result_one("SELECT * FROM {$tblprefix}commu_impression WHERE cid = '$_cid' and checked = '1'");
		//针对已提交，还没审核，又有新的用户提交同样的印象
		if(empty($_cid_check)){
			exit("添加印象成功，请等待审核。");			
		}else{
			echo json_encode($_yx_arr);
		}
	}	*/
break;
//对楼盘进行评分

case 'add_point':
/*	
	header("Content-Type:text/html;CharSet=$mcharset");
	$mid = $memberid;
	$mname = $curuser->info['mname'];
	$ip = $onlineip;	
	$aid = empty($aid) ? '0' : max(0,intval($aid));
	$point = empty($point)?0:max(0,intval($point));
	$fields = cls_cache::Read('cufields',2);
	$_files_name_arr = array_keys($fields);
	if(empty($field) || !in_array($field,$_files_name_arr) || !in_array($field."rs",$_files_name_arr)){
		exit("评分错误，字段{$field}不是正常数据表的字段。");		
	}
	
	$_cookname = "08cms_loupan_".$aid."_".$field;
	$m_cookie[$_cookname] = empty($m_cookie[$_cookname])?'0':$m_cookie[$_cookname];
	$_arr = array();
	if(!$m_cookie[$_cookname]){
		//限时评分
		cls_cache::Load('commus');
		$_repeattime = empty($commus['2']['repeattime'])?'-1':$commus['2']['repeattime'] * 60;
		msetcookie($_cookname,1,$_repeattime);	
		//查找原来的平均分，人数，然后处理提交过来的数据，计算平局分以及人数再存进数据库
		$point = $point * 10;
		if($_arr = $db->fetch_one("SELECT * FROM {$tblprefix}commu_dp WHERE aid = '$aid' AND mname = '' AND mid = '' ")){
			$_total_point = $_arr[$field] * $_arr[$field.'rs'] + $point;
			$_people_num  = $_arr[$field.'rs'] + 1;
			$_avg_point = round($_total_point/$_people_num,2);
			//查找数据中不为0，并且启用的平均分的字段，算出total字段的分数
			
			//_all_point:获得启用的平均分的总和
			$_all_point = 0;
			//_all_num:获得启用的平均分的个数
			$_all_num = 0;
			foreach($fields as $k => $v){
				if(strstr($k,'rs')  && !strstr($k,$field)){
					$k = substr($k,0,strpos($k,'rs'));						
					$_key_name = substr($k,strpos($k,'rs'));
					$_all_point += $_arr[$k];
					$_all_num ++;
				}
			}
			//因为排除了链接传递进来的平均分字段，下面要加上			
			$_total = round(($_all_point + $_avg_point)/($_all_num + 1),2);
			$db->query("UPDATE {$tblprefix}commu_dp SET cuid = '2',".$field." = '$_avg_point',".$field."rs = '$_people_num',total = '$_total' WHERE aid = '$aid' AND mname = ''");
			$_arr = array('field' =>$field,'point'=> $_avg_point, 'renshu' =>$_people_num,'total'=>$_total,'repeattime'=>$_repeattime);		
		}else{
			$db->query("INSERT INTO {$tblprefix}commu_dp SET aid = '$aid',cuid = '2',".$field." = '$point',".$field."rs = '1'");
			$_arr = array('field' =>$field,'point'=> $point, 'renshu' =>1,'total'=>$point);
		}
	}else{
		$_arr = array('error'=>'1');
	}
	//返回这一个字段的人数以及平均分		
	echo json_encode($_arr);	*/
break;

// 楼盘检索--楼盘名称、楼盘地址
//ajax中url传递过来的信息中要包含：
//chid:模型ID
//search_mode:查询的字段
case 'search_choice': 
	/*header("Content-type:text/html;Charset=$mcharset");
	$chid = empty($chid)?0:max(2,intval($chid));
	if(!in_array($chid,array(2,3,4))){
		echo "<li style='padding:10px'>模型id无效，查询结束。</li>";
		exit;
	}
	//默认searchmode为subject
	$search_mode = empty($search_mode) ? array('subject') : array_filter(explode(",",$search_mode));
	$val = trim(cls_string::iconv('utf-8',$mcharset,$val));	
	$fields = cls_cache::Read('fields',$chid);
	$_sql_str = '';
	if(!empty($val)){
		foreach($search_mode as $k){		
			$_sql_str .= " OR ".($fields[$k]['tbl']=="archives_".$chid ? "c." : "a.")."$k LIKE '%".$val."%' ";
		}
		$_sql_str .= " OR a.subjectstr LIKE '%".$val."%' ";
		$_sql_str = " AND (".substr($_sql_str,3).")";
	}
	//排除小区
	if($chid == 4) $_sql_str .= " AND c.leixing != '2' ";
	$_sql = "SELECT a.*,c.address FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON a.aid = c.aid WHERE 1=1 $_sql_str LIMIT 10";
	
	$_query=$db->query("$_sql"); 
	$str = "";
	$data = array();
	while($r=$db->fetch_array($_query)){
		$_url = cls_url::view_arcurl($r);
		$data[]=array('url'=>$_url,'subject'=>$r['subject'],'address'=>$r['address']);
	}
	echo jsonEncode($data);*/
break;

case 'add_zixun_pl'://用于：资讯评论
	//返回：	
	//  -1：内容为空/
/*	header("Content-type:text/html;Charset=$mcharset;");
....
*/
	
	break;

case 'jsNowTime': // 用于：团购记时 - 使用服务器时间
	/*header("Content-Type:aplication/javascript; charset=$mcharset");
	echo "var serverNowTime = '$timestamp';";*/
	break;
case 'get_house_for_area':
 /*   if ( isset($mid) && isset($mchid) && isset($chid) && isset($mcaid) )
    {
        $mid = (int) $mid;
        $mchid = (int) $mchid;
        $chid = (int) $chid;
        $mcaid = (int) $mcaid;
        $page = empty($page) ? 1 : (int)$page;
        
        # 获取经纪公司所有经纪人ID
        if($mchid == 3)
        {
            $db->select('m.mid')
               ->from('#__members m')
               ->innerJoin('#__members_2 d')->_on('m.mid=d.mid')
               ->where(array('pid4'=>$mid))->_and(array('m.incheck4'=>1))
               ->exec();
               
            $mids = array();
    		while($row = $db->fetch())
            {
    			$mids[] = $row['mid'];
    		}
            if ( !empty($mids) )
            {
                $mids = ($mid . ',' . implode(',', $mids));
            }
            else
            {
            	$mids = $mid;
            }
    	}
        else
        {
        	$mids = $mid;
        }
        $offset = 18;
        # 查询数据分页最大页数
        $db->select('pid3, lpmc')
           ->from('#__' . atbl($chid))
           ->where('mid')->_in($mids)
           ->exec();
        $rows = array();
        while($row = $db->fetch())
        {
            $rows[$row['lpmc']] = $row;
            if ( isset($counts[$row['lpmc']]) )
            {
                $counts[$row['lpmc']]++;
            }
            else
            {
            	$counts[$row['lpmc']] = 1;
            }
        }
        
        $page_num = ceil(count($rows) / $offset);
        
        # 如果当前页面大于数据最大页数则设置当前页面与数据最大页同步
        $page > $page_num && $page = $page_num;
        if ( in_array($page, array(0, 1)) )
        {
            $limit = 0;
        }
        else
        {
        	$limit = ($page - 1) * $offset;
        }
        
        # 判断上一页页码
        if ( $page - 1 > 0 )
        {
            $prevPage = $page - 1;
            $prevPageStr = '<a style="cursor: pointer;" onclick="showHouseList'.$chid.'('.$prevPage.')"><<上一页</a>';
        }
        else
        {
        	$prevPage = 0;
            $prevPageStr = '<span style="color:#ccc;"><<上一页</span>';
        }
        
        if ( $page + 1 > $page_num )
        {
            $nextPage = $page_num;
            $nextPageStr = '<span style="color:#ccc;">下一页>></span>';
        }
        else
        {
        	$nextPage = $page + 1;
            $nextPageStr = '<a style="cursor: pointer;" onclick="showHouseList'.$chid.'('.$nextPage.')">下一页>></a>';
        }
        
        $nbsp = ($page < 10 ? '&nbsp;' : '');        
        $page_string = "<br />{$prevPageStr}&nbsp;{$nbsp}{$page}{$nbsp}&nbsp;{$nextPageStr}";
        $string = '';
        $baseurl = "{$cms_abs}{$mspacedir}/index.php"; //伪静态,需要这个参数
        
        foreach(cls_Array::limit($rows, $limit, $offset) as $key => $value)
        {
            $title = cls_string::CutStr($value['lpmc'], 13);
            $string .= <<<HTML
                <a href="{$baseurl}?mcaid={$mcaid}&addno=1&mid={$mid}&extra=area:{$value['pid3']}" class="STYLE5">{$title}({$counts[$value['lpmc']]})</a><br />
HTML;
        }
        exit('<div style="height:330px;">' . $string . '</div>' . ($page_num > 1 ? $page_string : ''));
    }    */
break;

}
/*
function u_check_bounds(&$bounds){
	if(!$isbad = !$bounds){
		$bounds = explode(',', $bounds);
		foreach($bounds as $v)if(!$v || !is_numeric($v))$isbad = true;
	}
	if($isbad || count($bounds) != 4)exit('<floors total="0"/>');
}*/
