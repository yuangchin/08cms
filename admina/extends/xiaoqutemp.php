<?
//??????????????????如何解决排序与多页批量处理的关系,如果分页批量处理只有一种操作，是可以支持的。
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('normal') || cls_message::show('您没有当前项目的管理权限。');


$cotypes = cls_cache::Read('cotypes');
$catalogs = cls_cache::Read('catalogs');

$chid = 4;
$caid = 2;
$aid = empty($aid) ? 0 : max(0,intval($aid));
$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
$viewdetail = empty($viewdetail) ? 0 : 1;
$chid = empty($chid) ? 0 : max(0,intval($chid));
$valid = isset($valid) ? $valid : '-1';

$keyword = empty($keyword) ? '' : $keyword;
$action = empty($action) ? '' : $action;
$indays = empty($indays) ? 0 : max(0,intval($indays));
$outdays = empty($outdays) ? 0 : max(0,intval($outdays));

$wheresql = "";
$fromsql = "FROM {$tblprefix}arctemp15 a";



$keyword && $wheresql .= " AND (a.address LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' OR a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%')";

$indays && $wheresql .= " AND a.createdate>'".($timestamp - 86400 * $indays)."'";
$outdays && $wheresql .= " AND a.createdate<'".($timestamp - 86400 * $outdays)."'";

$filterstr = '';
foreach(array('keyword','indays','outdays','action') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));


foreach($cotypes as $k => $v){
	${"ccid$k"} = isset(${"ccid$k"}) ? max(0,intval(${"ccid$k"})) : 0;
	if(!empty(${"ccid$k"})){
		$ccids = sonbycoid(${"ccid$k"},$k);
		if($cnsql = cnsql($k,$ccids,'a.')) $wheresql .= " AND $cnsql";
		${"ccid$k"} && $filterstr .= "&ccid$k=".${"ccid$k"};
	}
}
$wheresql = empty($no_list) ? ($wheresql ? 'WHERE '.substr($wheresql,5) : '') : 'WHERE 0';

if($aid){
	echo "xx";

}else if($action=='select'){

	//列表区
	tabheader("{$catalogs[$caid]['title']} - 内容列表",'','',9);

	$cy_arr = array(array('标题','txtL'),);
	$cy_arr[] = array('地址','txtL');
	$cy_arr[] = '区域';
	$cy_arr[] = '商圈';
	$cy_arr[] = '添加会员';
	$cy_arr[] = '添加时间';
	$cy_arr[] = '选择';
	trcategory($cy_arr); 

	$fromsql = "FROM {$tblprefix}".atbl($chid)." a LEFT JOIN {$tblprefix}archives_$chid c ON (c.aid=a.aid)";
	$wheresql = " where (c.address LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%' OR a.subject LIKE '%".str_replace(array(' ','*'),'%',addcslashes($keyword,'%_'))."%')";

	$pagetmp = $page; 

	do{
		$query = $db->query("SELECT * $fromsql $wheresql ORDER BY a.aid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);

	$itemstr = '';
	while($r = $db->fetch_array($query)){
		$channel = cls_cache::Read('channel',$r['chid']);
		$subjectstr = "".cls_string::CutStr(mhtmlspecialchars($r['subject']),27)."";

		$ccid1str = cls_catalog::cnstitle($r['ccid1'],1,cls_cache::Read('coclasses',1));
		$ccid2str = cls_catalog::cnstitle($r['ccid2'],1,cls_cache::Read('coclasses',2));
		$adddatestr = $r['createdate'] ? date('Y-m-d',$r['createdate']) : '-';
		$checkstr = $r['checked'] ? 'Y' : (in_array($r['chkstate'],array(1,2)) ? $r['chkstate'] : 0).'/'.$channel['chklv'];

		$itemstr .= "<tr class=\"txt\"><td class=\"txtL\">$subjectstr</td>\n";
		$itemstr .= "<td class=\"txtL\">".cls_string::CutStr(mhtmlspecialchars($r['address']),20)."</td>\n";
		$itemstr .= "<td class=\"txtC\">$ccid1str</td>\n";
		$itemstr .= "<td class=\"txtC\">$ccid2str</td>\n";
		$itemstr .= "<td class=\"txtC\">$checkstr</td>\n";
		$itemstr .= "<td class=\"txtC w100\">$adddatestr</td>\n";
		$itemstr .= "<td class=\"txtC w300\" onclick=\"get_one('$r[aid]','".mhtmlspecialchars($r['subject'])."')\" style='cursor:pointer'>选择</td>\n";
		$itemstr .= "</tr>\n";
	} 

	$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$multi = multi($counts, $atpp, $page, "?entry=$entry$extend_str$filterstr");
	echo $itemstr;
	tabfooter();
	echo $multi;
	
?>
<script type="text/javascript">
function get_one(id,msg){
	var winpf = window.parent.frames['main'];
	winpf.mjoin_fmid.value = id;
	winpf.mjoin_fmval.value = msg;
	floatwin('close_<?php echo $handlekey; ?>',-1);
}
</script>
<?php

}else if(!submitcheck('bsubmit')){

	echo form_str($actionid.'arcsedit',"?entry=$entry$extend_str&page=$page");
	//搜索区块

	//某些固定页面参数
	trhidden('caid',$caid);

	tabheader_e();
	echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索标题或作者\">&nbsp; ";
	//类系筛选
	$k = 1; $v = $cotypes[$k];
	echo '<span>'.cn_select("ccid$k",array('value' => ${"ccid$k"},'coid' => $k,'notip' => 1,'addstr' => $v['cname'],'vmode' => 0)).'</span>&nbsp; ';

	echo "<input class=\"text\" name=\"outdays\" type=\"text\" value=\"$outdays\" size=\"4\" style=\"vertical-align: middle;\">天前&nbsp; ";
	echo "<input class=\"text\" name=\"indays\" type=\"text\" value=\"$indays\" size=\"4\" style=\"vertical-align: middle;\">天内&nbsp; ";
	echo strbutton('bfilter','筛选');
	tabfooter();


	//列表区
	tabheader("{$catalogs[$caid]['title']} - 内容列表",'','',9);

	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('标题','txtL'),);
	$cy_arr[] = array('地址','txtL');
	$cy_arr[] = '区域';
	$cy_arr[] = '商圈';
	$cy_arr[] = '添加会员';
	$cy_arr[] = '添加时间';
	
	trcategory($cy_arr); 

	$pagetmp = $page; 	
	do{
		$query = $db->query("SELECT * $fromsql $wheresql ORDER BY a.aid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);

	$itemstr = '';
	while($r = $db->fetch_array($query)){
		
		$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[aid]]\" value=\"$r[aid]\">";
		$subjectstr = "".cls_string::CutStr(mhtmlspecialchars($r['subject']),27)."";

		$ccid1str = cls_catalog::cnstitle($r['ccid1'],1,cls_cache::Read('coclasses',1));
		$ccid2str = cls_catalog::cnstitle($r['ccid2'],1,cls_cache::Read('coclasses',2));
		$adddatestr = $r['createdate'] ? date('Y-m-d',$r['createdate']) : '-';
		$dtstr = $r['dt'] ? $r['dt'] : '-';
		$editstr = "<a href=\"?entry=extend&extend=xiaoqutemp&aid=$r[aid]\" onclick=\"return floatwin('open_arcexit',this)\">预览</a>";
		

		$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td><td class=\"txtL\">$subjectstr</td>\n";
		$itemstr .= "<td class=\"txtL\">".cls_string::CutStr(mhtmlspecialchars($r['address']),20)."</td>\n";
		$itemstr .= "<td class=\"txtC\">$ccid1str</td>\n";
		$itemstr .= "<td class=\"txtC\">$ccid2str</td>\n";	
		$itemstr .= "<td class=\"txtC\">$r[mname]</td>\n";
		$itemstr .= "<td class=\"txtC w100\">$adddatestr</td>\n";		
		$itemstr .= "</tr>\n";
	} 

	$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$multi = multi($counts, $atpp, $page, "?entry=$entry$extend_str$filterstr");
	echo $itemstr;
	tabfooter();
	echo $multi;

	//操作区
	tabheader('操作项目');
	$s_arr = array();
	$s_arr['delete'] = '删除';
	$s_arr['check1'] = '审核';	
	if($s_arr){
		$soperatestr = '';
		$i = 1;
		foreach($s_arr as $k => $v){
			$soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[$k]\" value=\"1\"".($k=='delete'?' onclick="deltip()"':'').">$v &nbsp;";
			if(!($i % 5)) $soperatestr .= '<br>';
			$i ++;
		}
		trbasic('选择操作项目','',$soperatestr,'');
	}
	trbasic('<input type="checkbox" value="1" name="arcdeal[mjoin_set]" class="checkbox">&nbsp;合并到现有小区','',"<input class=\"text\" id=\"mjoin_val\" name=\"mjoin_val\" type=\"text\" value=\"\" size=\"36\" style=\"vertical-align: middle;\" title=\"搜索标题或地址\"> <input class='btn' type='button' name='bsubmit' value='搜索' onclick=\"open_sel()\"> ",'');
	echo "<input type='hidden' id='mjoin_id' name='mjoin_id' value=''>";
	tabfooter('bsubmit');
	a_guide('archivesedit');
	
	
?>
<script type="text/javascript">
var mjoin_fmid = $id('mjoin_id'); 
var mjoin_fmval = $id('mjoin_val'); 
function open_sel(){
	var skwd = mjoin_fmval.value;
	if(skwd.length>0){
		floatwin('open_arcexit','?entry=extend&extend=xiaoqutemp&action=select&keyword='+skwd);
	}else{
		alert('请输入关键词后再搜索!');	
	}
}
</script>
<?php

}else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目',axaction(1,M_REFERER));
	if(empty($selectid)) cls_message::show('请选择文档',axaction(1,M_REFERER));
	$arc = new cls_arcedit;
	if(!empty($arcdeal['autokeyword'])){
		include_once M_ROOT."./include/splitword.cls.php";
		$a_split = new SplitWord();
	}
	if(!empty($arcdeal['autothumb'])){
		include_once M_ROOT."./include/upload.cls.php";
		$c_upload = new cls_upload;
	}
	foreach($selectid as $aid){
		if(!empty($arcdeal['delete'])){
			$db->query("DELETE FROM {$tblprefix}arctemp15 WHERE aid='$aid'"); 
			continue;
		}
		if(!empty($arcdeal['check1'])){ //审核:增加到正式小区,更新房源资料,删除临时小区
			$rec = $db->fetch_one("SELECT * FROM {$tblprefix}arctemp15 WHERE aid='$aid'"); //print_r($rec);
			if($rec){
				$bakid = $aid; //临时小区id,备份一下,等会要用上
				$name = $rec['subject']; 
				$chid = 4; $caid = 2;
				$fields = cls_cache::Read('fields',$chid);
				$arc = new cls_arcedit;
				$aid = $arc->arcadd($chid,$caid);
				$arc->arc_ccid($rec["ccid1"],1,0);
				$arc->arc_ccid($rec["ccid2"],2,0);
				
				foreach($fields as $k => $v){
					if(isset($rec[$k])){
						$arc->updatefield($k,$rec[$k],$v['tbl']);						
					}
				}
				$_dt = explode(',',$rec['dt']);				
				$arc->updatefield('dt_0',$_dt[0]);
				$arc->updatefield('dt_1',$_dt[1]);
				$arc->updatefield('leixing',2,'archives_'.$chid);
				$arc->auto();
				$arc->autocheck();
				$arc->updatedb();

				$arc->autostatic();//最后才执行自动静态
				$pid3 = $aid; //正式小区id,(二手/出租)
				
				//自动关联一定范围内的周边配套			
				ex_zhoubian($aid,$chid,$rec['dt']);
				
				
				$sql2 = "UPDATE {$tblprefix}".atbl(2)." SET pid3='$pid3' WHERE lpmc='$name' AND pid3=0 ";
				$sql3 = "UPDATE {$tblprefix}".atbl(3)." SET pid3='$pid3' WHERE lpmc='$name' AND pid3=0 ";
				$db->query($sql2); $db->query($sql3);
				$aid = $bakid; //临时小区id,还原
			}
			$db->query("DELETE FROM {$tblprefix}arctemp15 WHERE aid='$aid'");
			continue;
		}
		if(!empty($arcdeal['mjoin_set'])){
			if($mjoin_id!=''&&$mjoin_val!=''){
				$name = $db->result_one("SELECT subject FROM {$tblprefix}arctemp15 WHERE aid='$aid'");

				if($name){
					$sql2 = "UPDATE {$tblprefix}".atbl(2)." SET pid3='$mjoin_id',lpmc='$mjoin_val' WHERE lpmc='$name' AND pid3=0 ";
					$sql3 = "UPDATE {$tblprefix}".atbl(3)." SET pid3='$mjoin_id',lpmc='$mjoin_val' WHERE lpmc='$name' AND pid3=0 ";
					$db->query($sql2); $db->query($sql3);
				}
				$db->query("DELETE FROM {$tblprefix}arctemp15 WHERE aid='$aid'");
			}
			continue;
		}
		$arc->updatedb();
		$arc->init();
	}
	if(!empty($arcdeal['autothumb'])) $c_upload->saveuptotal(1);
	unset($arc,$a_split,$c_upload);
	adminlog('文档更新管理','文档列表管理操作');
	cls_message::show('文档操作完成',"?entry=$entry$extend_str&page=$page$filterstr");
}

?>
