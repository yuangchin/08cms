<?
//定位于在浮动窗中操作
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('normal') || cls_message::show('您没有当前项目的管理权限。');
($aid = max(0,intval($aid))) || cls_message::show('请指定文档。');
$cid = empty($cid) ? '' : $cid;
$isnew = empty($isnew) ? '0' : $isnew; //echo $isnew;
$arc = new cls_arcedit;
$arc->set_aid($aid,array('ch'=>1));
$chid = $arc->archive['chid'];
$zschids = array(
	'4'=>array('zu'=>2,'shou'=>3),
	'115'=>array('zu'=>119,'shou'=>117),
	'116'=>array('zu'=>120,'shou'=>118),
);
$channel = &$arc->channel;
$fields = cls_cache::Read('fields',$chid);
$action = empty($action) ? 'def' : $action;
$baseurl = "?entry=$entry$extend_str&aid=$aid"; //&action=$action
$react = empty($react) ? '' : $react; 
$reflag = ''; if($react=='This') $reflag='checked="checked"'; 
$acttab = array();

if($isnew){
	$acttab['def'] = '楼盘当前价格编辑';
    $navstr = 'estate';
}else{
	$acttab['def'] = '小区当前价格编辑';
    $navstr = 'housing_estate';
}

$acttab['list'] = '历史价格列表';

$acttab['edit'] = '历史价格编辑';
$actitm = " : ";
$actmow = " ";

foreach($acttab as $k => $v){
	if($k==$action) $actmow = $v; $actitm = " - ";//$action = empty($action) ? 'def' : $action;
}
$page = !empty($page) ? max(1, intval($page)) : 1;
$keyword = empty($keyword) ? '' : $keyword;
$djfrom = empty($djfrom) ? '' : $djfrom;
$djto = empty($djto) ? '' : $djto;
$chname = ($isnew==0) ? '小区' : '楼盘';

if((strstr("def,edit",$action))&&(!submitcheck('bsubmit'))){//价格修改
    $action=='def' ? backnav($navstr,'price') : backnav($navstr.'_historical','list');
    tabheader("<font color=red>".$arc->archive['subject']."</font> - $actmow",'archivedetail',"$baseurl&action=$action&page=$page",2,1,1);
	trhidden('fmdata[caid]',$arc->archive['caid']);
	trhidden('action',$action);
	trhidden('aid',$aid);
	trhidden('cid',$cid);
	trhidden('isnew',$isnew);
	trhidden('page',$page);
	trhidden('keyword',$keyword);
	$subject_table = atbl($chid); 
	$a_field = new cls_field;
	$fix_fields = array();
	$fields = cls_cache::Read('field',$chid);

	if(strstr("edit",$action)){ // 历史价格修改
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
			if($action == 'def'){
			trbasic('提示','','该操作是编辑楼盘信息里面的价格，编辑“历史价格”可在历史价格列表里面操作，两者要区分。','',array('x_guide'=>'备注信息'));
			}
			foreach(array('dj','jgjj','jdjj','bdsm') as $k){
				p_editfield(array('fn'=>$k,'a_field'=>$a_field,'fix_fields'=>$fix_fields,'chid'=>$chid,'arc'=>$arc));
			}		
		}else{ //小区
			
			//参考值:小区对应二手房价格
			$ref = $db->fetch_one("SELECT AVG(dj) as dj,MAX(dj) as jgjj,MAX(dj) as jdjj FROM {$tblprefix}".atbl($zschids[$chid]['shou'])." WHERE pid3=$aid");
			if(!$ref['dj']) $ref = array('dj'=>'(无)','jgjj'=>'(无)','jdjj'=>'(无)');
			//实际值:小区的隐藏资料
			$rec = $db->fetch_one("SELECT  dj,  csjgz as jgjj,  csjdj as jdjj FROM {$tblprefix}".atbl($chid)." WHERE aid=$aid");
			
			trbasic('提示','','小区相关价格为对应房源的平均价格','',array('x_guide'=>'备注信息'));
			trbasic('均价','fmdata[dj]',$rec['dj'],'text',array('guide'=>"参考值:$ref[dj]; 单位是：元/M<sup>2</sup>，不填写为待定"));
			trbasic('最高价','fmdata[jgjj]',$rec['jgjj'],'text',array('guide'=>"参考值:$ref[jgjj]"));
			trbasic('最低价','fmdata[jdjj]',$rec['jdjj'],'text',array('guide'=>"参考值:$ref[jdjj]"));
		}
        trbasic('添加时间','fmdata[createdate]',date('Y-m-d'),'calendar');
	}
	tabfooter('bsubmit');
	a_guide('archivedetail');

}else if(($action=='list')){//历史价格列表
	
	$wheresql = "";
	$fromsql = "FROM {$tblprefix}housesrecords r";
	//需要考虑角色的栏目管理权限
	$wheresql .= " AND r.aid='$aid' AND isnew=$isnew ";	
	$wheresql = $wheresql ? 'WHERE '.substr($wheresql,5) : '';
	if($keyword){
		$timef = strtotime($keyword);
		if($timef) $wheresql .= " AND (r.createdate>='".$timef."' AND r.createdate<='".($timef+86400)."')";
		else $wheresql .= " AND (r.message LIKE '%".addslashes($keyword)."%'  )";
	}
    if($djfrom && $djto){
        $wheresql .= " AND (r.average>='".$djfrom."' AND r.average<='".($djto)."')";
    } elseif($djfrom=='' && $djto) {
        $wheresql .= " AND r.average<='".($djto)."'";
    }elseif($djfrom && $djto=='') {
        $wheresql .= " AND r.average>='".$djfrom."'";
    }
    $filterstr = '';
    foreach(array('keyword','djfrom','djto') as $k)$filterstr .= "&$k=".urlencode($$k);

    if(!submitcheck('bsubmit')){
        backnav($navstr,'list');

        echo form_str($actionid.'arcsedit',"$baseurl&action=$action&page=$page&isnew=$isnew");
		//某些固定页面参数
		trhidden('aid',$aid);	
		trhidden('isnew',$isnew);
		trhidden('action',$action);		
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "(记录时间或变动说明)关键词&nbsp; <input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\">&nbsp; ";
        echo '均价&nbsp;<input type="text" class="txt1" title="请输入最低价格" name="djfrom" value="'.$djfrom.'" size="6"> - <input class="txt1" type="text" title="请输入最高价格" name="djto" value="'.$djto.'" size="6">元&nbsp;';

        echo strbutton('bfilter','筛选');

		tabfooter();
		//列表区	
		tabheader("内容列表",'','',9);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
		$cy_arr[] = '记录时间';
		$cy_arr[] = '均价';
		$cy_arr[] = '最高价';
		$cy_arr[] = '最低价';
		$cy_arr[] = '价格说明';
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
			$editstr = "<a href=\"$baseurl&action=edit&page=$page&keyword=$keyword&cid=$r[cid]&isnew=$isnew\">编辑</a>";
			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\" >$selectstr</td>\n";
			$itemstr .= "<td class=\"txtC\">".date('Y-m-d',$r['createdate'])."</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[average]</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[highest]</td>\n";
			$itemstr .= "<td class=\"txtC\">$r[lowest]</td>\n";
			$itemstr .= "<td class=\"txtC\">$msg</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$editstr</td>\n";			
			$itemstr .= "</tr>\n";
		}
	
		$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
		$multi = multi($counts, $atpp, $page, "$baseurl&action=$action&isnew=$isnew".$filterstr);
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
		tabfooter('bsubmit');
		a_guide('archivesedit');		
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
		adminlog('记录更新管理','记录列表管理操作');
		cls_message::show('记录操作完成',"$baseurl&action=$action&page=$page&keyword=$keyword&isnew=$isnew");
	}
	
}else if(submitcheck('bsubmit')){ 
	$setstr = "highest='$fmdata[jgjj]',average='$fmdata[dj]',lowest='$fmdata[jdjj]'";
	$setstr .= empty($fmdata['bdsm'])?'': ",message='$fmdata[bdsm]' ";//楼盘才有变动说明，小区没有变动说明
	$setadd = "aid=$aid,isnew=$isnew";	
	if(strstr("edit",$action)){ 		
		$db->query("UPDATE {$tblprefix}housesrecords SET $setstr,createdate='".strtotime($fmdata['createdate'])."' WHERE cid=$cid");
		adminlog('价格-修改记录');
		cls_message::show('记录编辑完成',axaction(6,M_REFERER));
	}else{
		if($action=='def'){ // 价格编辑 
			$_the_recent_price  = $db->result_one("SELECT average FROM {$tblprefix}housesrecords WHERE aid='$aid' ORDER BY cid DESC");
			$_the_price_diff = $fmdata['dj'] - $_the_recent_price;//根据提交过来的数据与最近新的历史价格相比，判断升价还是降价
			$_price_trend = 0;
			if($_the_price_diff >0)$_price_trend = 1;//升价
			if($_the_price_diff <0)$_price_trend = 2;//降价
			if($isnew==1){ //楼盘
				$db->query("UPDATE {$tblprefix}".atbl($chid)." SET dj='$fmdata[dj]' WHERE aid=$aid");
				$db->query("UPDATE {$tblprefix}archives_$chid SET jgjj='$fmdata[jgjj]',jdjj='$fmdata[jdjj]',bdsm='$fmdata[bdsm]' WHERE aid=$aid");				
			}else{				
				$db->query("UPDATE {$tblprefix}".atbl($chid)." SET csjgz='$fmdata[jgjj]',dj='$fmdata[dj]',csjdj='$fmdata[jdjj]' WHERE aid=$aid");
			}
			//修改楼盘表，价格趋势字段
			$db->query("UPDATE {$tblprefix}".atbl($chid)." SET price_trend = '$_price_trend' WHERE aid=$aid");
			adminlog('价格-修改记录');
			//增加记录到历史价格           
			$db->query("INSERT INTO {$tblprefix}housesrecords SET $setadd,$setstr,createdate='".strtotime($fmdata['createdate'])."'");
		
			$arc->auto();
			$arc->updatedb();
			$arc->autostatic();
		}
	}
    cls_message::show('记录编辑完成',axaction(2,M_REFERER)); 
}
?>
