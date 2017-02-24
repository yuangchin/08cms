<?
!defined('M_COM') && exit('No Permission');
($aid = max(0,intval($aid))) || cls_message::show('请指定文档。');

$mchid = $curuser->info['mchid'];
$infloat = empty($infloat) ? '0' : $infloat;

$sql_ids = "SELECT CONCAT(loupan,',',xiezilou,',',shaopu) as lpids FROM {$tblprefix}members_$mchid WHERE mid='$memberid'"; 
$lpids = $db->result_one($sql_ids); //echo $sql_ids.":$lpids<BR>$oA->aid";
if(empty($lpids)) $lpids = 0;
if(!strstr(",$lpids,",','.$aid.',')) $oA->message('对不起，您没有权限管理此楼盘。');

$cid = empty($cid) ? '' : $cid;
$isnew = empty($isnew) ? '0' : $isnew;
$arc = new cls_arcedit;
$arc->set_aid($aid,array('ch'=>1));
$chid = $arc->archive['chid'];
$channel = &$arc->channel;
$fields = cls_cache::Read('fields',$chid);
$action2 = empty($action2) ? 'def' : $action2;
$page = !empty($page) ? max(1, intval($page)) : 1;
$keyword = empty($keyword) ? '' : $keyword;
$baseurl = "?action=$action&aid=$aid&infloat=$infloat"; 
$react = empty($react) ? '' : $react; 
$reflag = ''; if($react=='This') $reflag='checked="checked"'; 
$atpp = 10;

$acttab = array();
if($isnew){
	$acttab['def'] = '楼盘价格编辑';
}else{
	$acttab['def'] = '小区价格编辑';
}
$acttab['list'] = '历史价格列表';

$acttab['edit'] = '历史价格修改';

$actdiv = ''; $actitm = " : "; $actmow = " ";
foreach($acttab as $k => $v){
  	if($k!='edit') $actdiv .= "$actitm<a href='$baseurl&action2=$k&isnew=$isnew' style=\"".($action2 == $k?"color:red;":'')."\">$v</a>";
	if($k==$action2) $actmow = $v; $actitm = " - ";
}
$actdiv = "<div style='width:350px;float:right;'>选择操作$actdiv</div>";
$chname = ($isnew==0) ? '小区' : '楼盘';

if((strstr("def,edit",$action2))&&(!submitcheck('bsubmit'))){
	tabheader("$actdiv$chname - <font color='red'>".$arc->archive['subject']."</font> - $actmow",'archivedetail',"$baseurl&action2=$action2&page=$page&keyword=$keyword",2,1,1);
	trhidden('fmdata[caid]',$arc->archive['caid']);
	trhidden('action',$action2);
	trhidden('aid',$aid);
	trhidden('cid',$cid);
	trhidden('isnew',$isnew);
	trhidden('page',$page);
	trhidden('keyword',$keyword);
	$subject_table = atbl($chid); 
	$a_field = new cls_field;
	$fix_fields = array();
	$fields = cls_cache::Read('field',$chid);
	if(strstr("edit",$action2)){ // 历史价格修改
		$farr = array('dj','jgjj','jdjj','bdsm');
		$rec = $db->fetch_one("SELECT average as dj,highest as jgjj,lowest as jdjj,message as bdsm,createdate FROM {$tblprefix}housesrecords WHERE cid=$cid");
		for($i=0;$i<count($farr);$i++){
			$fn = $farr[$i];
			$fix_fields[] = $fn;
			if(($field = cls_cache::Read('field',$chid,$fn)) && $field['available']){
				$a_field->init($field,$rec[$fn]);
				$a_field->trfield('fmdata');
			}
		}
		trbasic('添加时间','fmdata[createdate]',date('Y-m-d',$rec['createdate']),'calendar');
	}else{ // 历史价格编辑/增加
		if($isnew==1){ //楼盘
			if($action2 == 'def'){
			trbasic('提示','','该操作是编辑楼盘信息里面的价格，编辑“历史价格”可在历史价格列表里面操作，两者要区分。','',array('x_guide'=>'备注信息'));
			}
			
			foreach(array('dj','jgjj','jdjj','bdsm') as $k){
				p_editfield(array('fn'=>$k,'a_field'=>$a_field,'fix_fields'=>$fix_fields,'chid'=>$chid,'arc'=>$arc));
			}
			trbasic('历史价格','','<input name="history" type="radio" value="Skip" />不增加历史价格 &nbsp; <input name="history" type="radio" value="Add" checked="checked"/>增加到历史价格','');
			trbasic('返回处理','','<input name="react" type="radio" value="List" checked="checked"/>返回历史价格列表 &nbsp; <input name="react" type="radio" value="Close" $reflag/>返回默认页','');
		}else{ //小区
			trbasic('提示','','小区相关价格为对应房源的平均价格','',array('x_guide'=>'备注信息'));
			//参考值:小区对应二手房价格
			$ref = $db->fetch_one("SELECT AVG(dj) as dj,MAX(dj) as jgjj,MAX(dj) as jdjj FROM {$tblprefix}".atbl(3)." WHERE pid3=$aid");
			if(!$ref['dj']) $ref = array('dj'=>'(无)','jgjj'=>'(无)','jdjj'=>'(无)');
			//实际值:小区的隐藏资料
			$rec = $db->fetch_one("SELECT   cspjj as dj,  csjgz as jgjj,  csjdj as jdjj FROM {$tblprefix}".atbl(4)." WHERE aid=$aid");

			trbasic('均价','fmdata[dj]',$rec['dj'],'text',array('guide'=>"参考值:$ref[dj]; 单位是：元/M<sup>2</sup>，不填写为待定"));
			trbasic('最高均价','fmdata[jgjj]',$rec['jgjj'],'text',array('guide'=>"参考值:$ref[jgjj]"));
			trbasic('最低均价','fmdata[jdjj]',$rec['jdjj'],'text',array('guide'=>"参考值:$ref[jdjj]"));
		
			trbasic('历史价格','','<input name="history" type="radio" value="Skip" />不增加历史价格 &nbsp; <input name="history" type="radio" value="Add" checked="checked"/>增加到历史价格','');
			
			trbasic('返回处理','','<input name="react" type="radio" value="List" checked="checked"/>返回历史价格列表 &nbsp; <input name="react" type="radio" value="Close" $reflag/>返回默认页','');
		}
	}
	tabfooter('bsubmit');

}else if($action2=='list'){
	
	$wheresql = "";
	$fromsql = "FROM {$tblprefix}housesrecords r";
	//需要考虑角色的栏目管理权限
	$wheresql .= " AND r.aid='$aid' AND isnew=$isnew ";	
	$wheresql = $wheresql ? 'WHERE '.substr($wheresql,5) : '';
	if($keyword){ 
		$wheresql .= " AND (r.message LIKE '%".addslashes($keyword)."%'  )";
	}
	$filterstr = '';
	
	if(!submitcheck('ybsubmit')){		
		echo form_str('action2id'.'arcsedit',"$baseurl&action2=list&page=$page&isnew=$isnew");
		//某些固定页面参数
		trhidden('aid',$aid);	
		trhidden('isnew',$isnew);
		trhidden('action',$action2);		
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "(记录时间或变动说明)关键词&nbsp; <input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\">&nbsp; ";
	
		echo strbutton('bfilter','筛选');

		tabfooter();
		//列表区	
		tabheader($actdiv."内容列表",'','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
		$cy_arr[] = '记录时间';
		$cy_arr[] = '均价';
		$cy_arr[] = '最高均价';
		$cy_arr[] = '最低均价';
		$cy_arr[] = '变动说明';
		$cy_arr[] = '修改';
		trcategory($cy_arr);
	
		$pagetmp = $page; 
		do{
			$query = $db->query("SELECT r.* $fromsql $wheresql ORDER BY r.createdate DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
	
		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[cid]]\" value=\"$r[cid]\">";
			$msg = cls_string::CutStr($r['message'],36);
			$editstr = "<a href=\"$baseurl&action2=edit&page=$page&keyword=$keyword&cid=$r[cid]&isnew=$isnew\" onclick=\"return floatwin('open_arcexit',this)\">详情</a>";
			$itemstr .= "<tr class=\"txt\"  style=\"text-align:center;\"><td class=\"txtC w40\" >$selectstr</td>\n";
			$itemstr .= "<td class=\"txtC\">".date('Y-m-d',$r['createdate'])."</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[average]</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[highest]</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[lowest]</td>\n";
			$itemstr .= "<td class=\"txtC\">$msg</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$editstr</td>\n";			
			$itemstr .= "</tr>\n";
		}
	
		$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
		$multi = multi($counts, $atpp, $page, "$baseurl&action2=$action2&keyword=$keyword&isnew=$isnew");
		echo $itemstr;
		tabfooter();
		echo $multi;
	
		//操作区
		tabheader('操作项目');
		$s_arr = array();
		$s_arr['delete'] = '删除';
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
		tabfooter('ybsubmit');
	}else{
		if(empty($arcdeal) && empty($albumsnew)) cls_message::show('请选择操作项目',axaction(0,M_REFERER));
		if(empty($selectid) && empty($albumsnew)) cls_message::show('请选择记录',axaction(0,M_REFERER));

		if(!empty($selectid)){
			foreach($selectid as $cid){
				$db->query("DELETE FROM {$tblprefix}housesrecords WHERE cid=$cid");
			}
		}

		$arc->auto();
		$arc->updatedb();
		$arc->autostatic();
		cls_message::show('记录操作完成',"$baseurl&action2=$action2&page=$page&keyword=$keyword&isnew=$isnew");
	}
	
}else if(submitcheck('bsubmit')){ 

	$setstr = "highest='$fmdata[jgjj]',average='$fmdata[dj]',lowest='$fmdata[jdjj]',message='$fmdata[bdsm]'";
	$setadd = "aid=$aid,isnew=$isnew";
	if($action2 == 'edit'){ 	
		$db->query("UPDATE {$tblprefix}housesrecords SET $setstr,createdate='".strtotime($fmdata['createdate'])."' WHERE cid=$cid");
		cls_message::show('记录编辑完成',axaction(6,M_REFERER));
	}else if($action2=='def'){ // 价格编辑 
		$_the_recent_price  = $db->result_one("SELECT average FROM {$tblprefix}housesrecords WHERE aid='$aid' ORDER BY cid DESC");
		$_the_price_diff = $fmdata['dj'] - $_the_recent_price;//根据提交过来的数据与最近新的历史价格相比，判断升价还是降价
		$_price_trend = 0;
		if($_the_price_diff >0)$_price_trend = 1;//升价
		if($_the_price_diff <0)$_price_trend = -1;//降价
		
		if($isnew==1){ //楼盘
			$db->query("UPDATE {$tblprefix}".atbl(4)." SET dj='$fmdata[dj]' WHERE aid=$aid");
			$db->query("UPDATE {$tblprefix}archives_$chid SET jgjj='$fmdata[jgjj]',jdjj='$fmdata[jdjj]',bdsm='$fmdata[bdsm]' WHERE aid=$aid");
		}else{
			$db->query("UPDATE {$tblprefix}".atbl(4)." SET csjgz='$fmdata[jgjj]',cspjj='$fmdata[dj]',csjdj='$fmdata[jdjj]' WHERE aid=$aid");
		}	
		
		//修改楼盘表，价格趋势字段
		$db->query("UPDATE {$tblprefix}".atbl(4)." SET price_trend = '$_price_trend' WHERE aid=$aid");

		if($history=='Add'){ 				
			$db->query("INSERT INTO {$tblprefix}housesrecords SET $setadd,$setstr,createdate='$timestamp'");
		}
		$arc->auto();
		$arc->updatedb();
		$arc->autostatic();
	}

	if($react=='Close'){		
		cls_message::show('记录编辑完成',axaction(6,M_REFERER)); 
	}else{
		cls_message::show('记录操作完成',"$baseurl&action2=list&isnew=$isnew");
	}

}
?>
