<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
# 检查是否通过farchives.php入口初始化
foreach(array('fcaid','fromsql','wheresql','filterstr',) as $k){
	if(empty($$k)) cls_message::show('请先初始化。');
}
$action = empty($action) ? '' : $action;
if(!submitcheck('bsubmit') && !submitcheck('updateorder')){
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
	tabheader('信息列表 (<a href="?entry=extend&extend=adv_management&fcaid='.$fcaid.'">获取代码</a> >> <a href="?entry=extend&extend=adv_management&action=view&fcaid='.$fcaid.'">浏览</a> >> <a href="?entry=extend&extend=adv_management&action=recache&src_type=other&fcaid='.$fcaid.'">更新缓存</a>)','','',10);
	$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",'ID');
	$cy_arr[] = '标题|L';
	$area_coid && $cy_arr[] = '地区';
	$cy_arr[] = '浏览量';
	$cy_arr[] = '启用';
	$cy_arr[] = '排序';
	if(!cls_fcatalog::Config($fcaid,'nodurat')){
		$cy_arr[] = '开始日期';
		$cy_arr[] = '到期时间';
	}
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
		$_views['views'] = (int)$r['views'];
		$_views['check'] = $r['checked'] ? 'Y' : '-';
		$_views['order'] = $r['vieworder'];
		$_views['startdate'] = $r['startdate'] ? date('Y-m-d',$r['startdate']) : '-';
		$_views['enddate'] = $r['enddate'] ? date('Y-m-d',$r['enddate']) : '-';
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
		$itemstr .= "<td class=\"txtC\">{$_views['views']}</td>\n";
		$itemstr .= "<td class=\"txtC w35\">{$_views['check']}</td>\n";
		$itemstr .= "<td class=\"txtC w35\"><input type='text' value='{$_views['order']}' class='w50' name='orders[{$_views['aid']}]'></td>\n";
		if(!cls_fcatalog::Config($fcaid,'nodurat')){
			$itemstr .= "<td class=\"txtC w100\">{$_views['startdate']}</td>\n";
			$itemstr .= "<td class=\"txtC w100\">{$_views['enddate']}</td>\n";
		}
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
	if($s_arr){
		$soperatestr = '';
		foreach($s_arr as $k => $v) $soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" id=\"arcdeal[$k]\" name=\"arcdeal[$k]\" value=\"1\"" . ($k == 'delete' ? ' onclick="deltip()"' : '') . "><label for=\"arcdeal[$k]\">$v</label> &nbsp;";
		trbasic('选择操作项目','',$soperatestr,'');
	}
	echo ''.cls_fcatalog::areaShow($fcaid,@$arcfarea,'Sets','arcfarea').'&nbsp; '; //选择地区
	echo '</table><br /><input class="btn" type="submit" name="bsubmit" value="提交">&nbsp;&nbsp;
          <input class="btn" type="submit" name="updateorder" value="更新排序">
          </form></body></html>';

} else if(submitcheck('updateorder')) {
    if(!is_array($orders)) die('数据格式有误，请检查后再重新提交！');
    $arc = new cls_farcedit;
    foreach($orders as $k => $v) {
        $arc->set_aid((int)$k);
        $arc->updatefield('vieworder',$v);
        $arc->updatedb();
    }
	unset($arc);
    _08_Advertising::cleanTag($fcaid);

	adminlog('广告信息管理','副件信息列表管理');
	cls_message::show('更新排序完成',"?entry=$entry$extend_str&page=$page$filterstr");
} else {
	if(empty($arcdeal)) cls_message::show('请选择操作项目',"?entry=$entry$extend_str&page=$page$filterstr");
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
		}elseif(!empty($arcdeal['farea'])){
			$arc->set_column($mode_arcfarea,$arcfarea,$area_coid,'farea',"farchives",19,1);
		}
		$arc->updatedb();
	}
	unset($arc);
    _08_Advertising::cleanTag($fcaid);
	
	adminlog('广告信息管理','广告信息列表管理');
	cls_message::show('广告信息操作完成',"?entry=$entry$extend_str&page=$page$filterstr");

}
