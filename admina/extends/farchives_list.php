<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
# 检查是否通过farchives.php入口初始化
foreach(array('fromsql','wheresql','filterstr',) as $k){
	if(empty($$k)) cls_message::show('请先初始化。');
}
if(!submitcheck('bsubmit')){
	echo form_str($actionid.'arcsedit',"?entry=$entry$extend_str&page=$page");
	//某些固定页面参数
	trhidden('fcaid',$fcaid);
	tabheader_e();
	echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
	echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索标题或作者\">&nbsp; ";
	echo ''.cls_fcatalog::areaShow($fcaid,@$farea,'Search','farea').'&nbsp; '; //选择地区
	echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('-1' => '审核状态','0' => '未审','1' => '已审'),$checked)."</select>&nbsp; ";
	echo "<select style=\"vertical-align: middle;\" name=\"valid\">".makeoption(array('-1' => '有效状态','0' => '无效','1' => '有效'),$valid)."</select>&nbsp; ";
	echo strbutton('bfilter','筛选');
	tabfooter();

	//列表区
	tabheader("信息列表 - ".cls_fcatalog::Config($fcaid,'title'),'','',10);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",'ID');
	$cy_arr[] = array("标题$vflag",'txtL');
	$area_coid && $cy_arr[] = '地区';
	$cy_arr[] = '审核';
	$cy_arr[] = '排序';
	if(!cls_fcatalog::Config($fcaid,'nodurat')){
		$cy_arr[] = '开始日期';
		$cy_arr[] = '到期时间';
	}
	$cy_arr[] = '更多';
	$cy_arr[] = '编辑';
	trcategory($cy_arr);

	$pagetmp = $page; //echo "SELECT * $fromsql $wheresql";
	do{
		$query = $db->query("SELECT * $fromsql $wheresql ORDER BY a.vieworder DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
		$pagetmp--;
	} while(!$db->num_rows($query) && $pagetmp);

	$itemstr = '';
	while($r = $db->fetch_array($query)){
		$_views = array();
		$_views['select'] = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[aid]]\" value=\"$r[aid]\">";
		$_views['aid'] = $r['aid'];

		$_views['subject'] = "<span ".($r['color']?'style="color:'.$r['color'].'"':'').">".mhtmlspecialchars($r['subject'])."</span>";
		if($vflag) $_views['subject'] = "<a href=\"?entry=extend&extend=farchiveinfo&aid=$r[aid]&detail=1\" target=\"_blank\">".$_views['subject']."</a>";
		if($r['arcurl']) $_views['subject'] .= '(静态)';
		
		$_views['check'] = $r['checked'] ? 'Y' : '-';
		$_views['order'] = $r['vieworder'];
		$_views['startdate'] = $r['startdate'] ? date('Y-m-d',$r['startdate']) : '-';
		$_views['enddate'] = $r['enddate'] ? date('Y-m-d',$r['enddate']) : '-';
		$_views['view'] = "<a id=\"{$actionid}_info_$r[aid]\" href=\"?entry=extend&extend=farchiveinfo&aid=$r[aid]\" onclick=\"return showInfo(this.id,this.href)\">查看</a>";
		$_views['edit'] = "<a href=\"?entry=extend&extend=farchive&aid=$r[aid]\" onclick=\"return floatwin('open_farchive',this)\">详情</a>";
		

		$itemstr .= "<tr class=\"txt\">\n";
		$itemstr .= "<td class=\"txtC w40\">{$_views['select']}</td>\n";
		$itemstr .= "<td class=\"txtC w40\">{$_views['aid']}</td>\n";
		$itemstr .= "<td class=\"txtL\">{$_views['subject']}</td>\n";
		if($area_coid){ 
			$vstr = cls_catalog::cnstitle($r['farea'],1,$area_arr,0);
			$vstr = cls_string::CutStr($vstr, 60);
			$itemstr .= "<td class=\"txtC\">$vstr</td>\n";
		}
		$itemstr .= "<td class=\"txtC w35\">{$_views['check']}</td>\n";
		$itemstr .= "<td class=\"txtC w35\"><input type='text' value='{$_views['order']}' class='w50' name='orders[{$_views['aid']}]'></td>\n";
		if(!cls_fcatalog::Config($fcaid,'nodurat')){
			$itemstr .= "<td class=\"txtC w100\">{$_views['startdate']}</td>\n";
			$itemstr .= "<td class=\"txtC w100\">{$_views['enddate']}</td>\n";
		}
		$itemstr .= "<td class=\"txtC w35\">{$_views['view']}</td>\n";
		$itemstr .= "<td class=\"txtC w35\">{$_views['edit']}</td>\n";;
		$itemstr .= "</tr>\n";
	}
	$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$multi = multi($counts,$atpp,$page,"?entry=$entry$extend_str$filterstr");

	echo $itemstr;
	tabfooter();
	echo $multi;

	tabheader('操作项目');
	$s_arr = array();
	if(allow_op('fdel')) $s_arr['delete'] = '删除';
    if (allow_op('fcheck'))
    {
    	$s_arr['check'] = '启用';
    	$s_arr['uncheck'] = '不启用';
    }
	$s_arr['static'] = '生成静态';
	$s_arr['unstatic'] = '解除静态';
	if($s_arr){
		$soperatestr = '';
		foreach($s_arr as $k => $v) $soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" id=\"arcdeal[$k]\" name=\"arcdeal[$k]\" value=\"1\"" . ($k == 'delete' ? ' onclick="deltip()"' : '') . "><label for=\"arcdeal[$k]\">$v</label> &nbsp;";
		trbasic('选择操作项目','',$soperatestr,'');
	}
	//trbasic('<input type="checkbox" value="1" name="arcdeal[vieworder]" class="checkbox">&nbsp;设置排序','','<input name="arcorder">','');
	echo ''.cls_fcatalog::areaShow($fcaid,@$arcfarea,'Sets','arcfarea').'&nbsp; '; //选择地区
	tabfooter('bsubmit');

}else{
	//if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=$entry$extend_str&page=$page$filterstr");
	if(empty($selectid)) cls_message::show('请选择信息',"?entry=$entry$extend_str&page=$page$filterstr");
	$arc = new cls_farcedit;
	foreach($selectid as $aid){
		$arc->set_aid($aid);
		if(!empty($arcdeal['delete'])){
			$arc->arc_delete();
			continue;
		}
		if(!empty($arcdeal['check'])){
			$arc->arc_check(1);
		}elseif(!empty($arcdeal['uncheck'])){
			$arc->arc_check(0);
		}
		if(!empty($arcdeal['static'])){
			$arc->tostatic();
		}elseif(!empty($arcdeal['unstatic'])){
			$arc->unstatic();
		}elseif(!empty($arcdeal['farea'])){
			$arc->set_column($mode_arcfarea,$arcfarea,$area_coid,'farea',"farchives",19,1);
		} 
		$iOrder = @$orders[$aid];
		$iOrder = empty($iOrder) ? 0 : max(0,intval($iOrder));
		$arc->updatefield('vieworder',$iOrder); 
		$arc->updatedb();
	}
	unset($arc);

	adminlog('副件信息管理','副件信息列表管理');
	cls_message::show('副件信息操作完成',"?entry=$entry$extend_str&page=$page$filterstr");

}
