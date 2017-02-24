<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('fragment')) cls_message::show($re);
$frcatalogs = cls_cache::Read('frcatalogs');
$tclassarr = cls_Tag::TagClass(true);
empty($action) && $action = 'fragmentsedit';
empty($sclass) && $sclass = 0;
if($action == 'fragmentsedit'){
	backnav('fragment','fragment');
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$frcaid = isset($frcaid) ? max(-1,intval($frcaid)) : -1;
	$checked = isset($checked) ? $checked : '-1';
	$valid = isset($valid) ? $valid : '-1';
	$keyword = empty($keyword) ? '' : $keyword;

	$wheresql = '';
	$fromsql = "FROM {$tblprefix}fragments";

	if($frcaid != -1) $wheresql .= " AND frcaid='$frcaid'";
	if($checked != -1) $wheresql .= " AND checked='$checked'";
	if($valid != -1) $wheresql .= $valid ? " AND startdate<'$timestamp' AND (enddate='0' OR enddate>'$timestamp')" : " AND (startdate>'$timestamp' OR (enddate!='0' AND enddate<'$timestamp'))";
	$keyword && $wheresql .= " AND (ename ".sqlkw($keyword)." OR title ".sqlkw($keyword).")";
	$wheresql = substr($wheresql,5);
	$wheresql = $wheresql ? "WHERE $wheresql" : '';

	$filterstr = '';
	foreach(array('keyword',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	foreach(array('frcaid','checked','valid',) as $k) $$k != -1 && $filterstr .= "&$k=".$$k;

	if(!submitcheck('bsubmit')){
		echo form_str($actionid.'arcsedit',"?entry=$entry&action=$action&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"搜索标题或唯一标识\">&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"frcaid\">".makeoption(array('-1' => '不限分类','0' => '未分类',) + $frcatalogs,$frcaid)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"checked\">".makeoption(array('-1' => '审核状态','0' => '未审','1' => '已审'),$checked)."</select>&nbsp; ";
		echo "<select style=\"vertical-align: middle;\" name=\"valid\">".makeoption(array('-1' => '有效状态','0' => '无效','1' => '有效'),$valid)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选');
		tabfooter();

		//列表区
		tabheader("碎片列表 &nbsp;>><a href=\"?entry=$entry&action=add\" onclick=\"return floatwin('open_fragment',this)\">添加碎片</a>",'','',10);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",array('碎片名称','txtL'),array('唯一标识','txtL'),);
		$cy_arr[] = array('模板类型','txtL');
		$cy_arr[] = '排序';
		$cy_arr[] = '缓存';
		$cy_arr[] = '审核';
		$cy_arr[] = '有效';
		$cy_arr[] = '配置';
		$cy_arr[] = '模板';
		$cy_arr[] = '预览';
		$cy_arr[] = '调用';
		trcategory($cy_arr);

		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * $fromsql $wheresql ORDER BY vieworder LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);

		$itemstr = '';
		while($r = $db->fetch_array($query)){
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[ename]]\" value=\"$r[ename]\">";
			$aidstr = $r['ename'];
            $uri_str = '';
            empty($r['tclass'])|| $uri_str .= "&tclass={$r['tclass']}";
			$subjectstr = mhtmlspecialchars($r['title']);
			$tclassstr = @$tclassarr[$r['tclass']];
			$checkstr = $r['checked'] ? 'Y' : '-';
			$periodstr = $r['period'] ? $r['period'] : '-';
			$validstr = ($r['startdate'] < $timestamp) && (!$r['enddate'] || $r['enddate'] > $timestamp) ? 'Y' : '-';
			$setstr = "<a href=\"?entry=$entry&action=detail&ename=$r[ename]{$uri_str}\" onclick=\"return floatwin('open_fragment',this)\">配置</a>";
			$editstr = "<a href=\"?entry=$entry&action=tpl&ename=$r[ename]{$uri_str}\" onclick=\"return floatwin('open_fragment',this)\">模板</a>";
			$pickstr = "<a href=\"?entry=$entry&action=pick&ename=$r[ename]{$uri_str}\" onclick=\"return floatwin('open_fragment',this)\">方法</a>";
			$viewstr = "<a href=\"?entry=$entry&action=view&ename=$r[ename]{$uri_str}\" onclick=\"return floatwin('open_fragment',this)\">预览</a>";

			$itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td><td class=\"txtL\">$subjectstr</td><td class=\"txtL\">$aidstr</td>\n";
			$itemstr .= "<td class=\"txtL\">$tclassstr</td>\n";
			$itemstr .= "<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"fmdata[$r[ename]][vieworder]\" value=\"$r[vieworder]\"></td>\n";
			$itemstr .= "<td class=\"txtC w40\">$periodstr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$checkstr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$validstr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$setstr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$editstr</td>\n";
			$itemstr .= "<td class=\"txtC w35\">$viewstr</td>\n";;
			$itemstr .= "<td class=\"txtC w35\">$pickstr</td>\n";;
			$itemstr .= "</tr>\n";
		}
		$counts = $db->result_one("SELECT count(*) $fromsql $wheresql");
		$multi = multi($counts,$atpp,$page,"?entry=$entry&action=$action$filterstr");

		echo $itemstr;
		tabfooter();
		echo $multi;

		tabheader('操作项目');
		$s_arr = array();
		$s_arr['delete'] = '删除';
		$s_arr['check'] = '审核';
		$s_arr['uncheck'] = '解审';
		$s_arr['update'] = '更新缓存';
		if($s_arr){
			$soperatestr = '';
			foreach($s_arr as $k => $v) $soperatestr .= "<input class=\"checkbox\" type=\"checkbox\" id=\"arcdeal[$k]\" name=\"arcdeal[$k]\" value=\"1\"" . ($k == 'delete' ? ' onclick="deltip()"' : '') . "><label for=\"arcdeal[$k]\">$v</label> &nbsp;";
			trbasic('选择操作项目','',$soperatestr,'');
		}
		tabfooter('bsubmit');

	}else{
		if(empty($fmdata)) cls_message::show('请选择信息',"?entry=$entry&action=$action&page=$page$filterstr");
		foreach($fmdata as $k => $v) $db->query("UPDATE {$tblprefix}fragments SET vieworder=".max(0,intval($v['vieworder']))." WHERE ename='$k'");
		if(!empty($selectid)){
			if(!empty($arcdeal['delete'])){
				foreach($selectid as $k => $v){
					cls_CacheFile::Del($v['tclass'] ? 'ctag' : 'rtag','fr_'.$k,'');
					if(!_08_FileSystemPath::CheckPathName($v)) clear_dir(M_ROOT."dynamic/fragment/$v",true);
				}
				$db->query("DELETE FROM {$tblprefix}fragments WHERE ename ".multi_str($selectid));
			}else{
				if(!empty($arcdeal['check'])){
					$db->query("UPDATE {$tblprefix}fragments SET checked=1 WHERE ename ".multi_str($selectid));
				}elseif(!empty($arcdeal['uncheck'])){
					$db->query("UPDATE {$tblprefix}fragments SET checked=0 WHERE ename ".multi_str($selectid));
				}
				if(!empty($arcdeal['update'])){
					foreach($selectid as $k => $v){
						if(!_08_FileSystemPath::CheckPathName($v)) clear_dir(M_ROOT."dynamic/fragment/$v",true);
					}
				}
			}
		}
		cls_CacheFile::Update('fragments');
		adminlog('碎片管理','碎片列表管理');
		cls_message::show('碎片操作完成',"?entry=$entry&action=$action&page=$page$filterstr");

	}
}elseif($action == 'add'){
	if(!submitcheck('bsubmit')){
		tabheader('添加碎片','fragmentadd',"?entry=$entry&action=$action",2,1,1);
		trbasic('碎片名称','fmdata[title]','','text',array('validate' => ' onfocus="initPinyin(\'fmdata[ename]\')"' . makesubmitstr('fmdata[title]',1,0,3,30)));
		trbasic('碎片英文标识','','<input type="text" value="" name="fmdata[ename]" id="fmdata[ename]" size="25" ' . makesubmitstr('fmdata[ename]',1,'tagtype',0,30) . ' offset="2">&nbsp;&nbsp;<input type="button" value="检查重名" onclick="check_repeat(\'fmdata[ename]\',\'frnamesame\');">&nbsp;&nbsp;<input type="button" value="自动拼音" onclick="autoPinyin(\'fmdata[title]\',\'fmdata[ename]\')" />','');
		trbasic('碎片分类','fmdata[frcaid]',makeoption(array(0 => '不分类') + $frcatalogs),'select');
		trbasic('模板类型','fmdata[tclass]',makeoption($tclassarr),'select');
		trbasic('开始日期',"fmdata[startdate]",'','calendar',array('guide'=>'不限日期请留空','validate'=>makesubmitstr("fmdata[startdate]",0,0,0,0,'date')));
		trbasic('结束日期',"fmdata[enddate]",'','calendar',array('guide'=>'不限日期请留空','validate'=>makesubmitstr("fmdata[enddate]",0,0,0,0,'date')));
		trbasic('允许传入的变量名','fmdata[params]','','text',array('guide' => '逗号分隔多个变量名，指定编码请加上charset变量，取值gbk/big5/utf-8。'));
		trbasic('缓存周期',"fmdata[period]",'','text',array('guide'=>'单位：分钟，留空为不缓存。','validate'=>makesubmitstr("fmdata[period]",0,'int',0,4)));
		tabfooter('bsubmit');
		a_guide('fragmentadd');
	}else{
		$fmdata['title'] = trim(strip_tags($fmdata['title']));
		if(!$fmdata['title'] || !$fmdata['ename']) cls_message::show('资料不完全',M_REFERER);
		if(preg_match("/[^a-zA-Z_0-9]+/",$fmdata['ename'])) cls_message::show('标识不合规范',M_REFERER);
		$fmdata['ename'] = strtolower($fmdata['ename']);
		if(in_array($fmdata['ename'],ename_arr())) cls_message::show('标识重复',M_REFERER);
		foreach(array('startdate','enddate') as $var){
			if(isset($fmdata[$var])){
				$fmdata[$var] = trim($fmdata[$var]);
				$fmdata[$var] = !cls_string::isDate($fmdata[$var]) ? 0 : strtotime($fmdata[$var]);
			}
		}
		$fmdata['params'] = trim(strip_tags($fmdata['params']));
		$params = explode(',',$fmdata['params']);
		foreach($params as $k => $v){
			if(preg_match("/[^a-zA-Z_0-9]+/",$v)) unset($params[$k]);
		}
		$fmdata['params'] = implode(',',$params);
		$fmdata['period'] = max(0,intval($fmdata['period']));
		$db->query("INSERT INTO {$tblprefix}fragments SET
		ename ='$fmdata[ename]',
		title='$fmdata[title]',
		frcaid='$fmdata[frcaid]',
		tclass='$fmdata[tclass]',
		startdate='$fmdata[startdate]',
		enddate='$fmdata[enddate]',
		params='$fmdata[params]',
		period='$fmdata[period]'
		");
		adminlog('添加碎片');
		cls_CacheFile::Update('fragments');
		cls_message::show('碎片添加完成，请设置内容模板。',"?entry=$entry&action=tpl&ename=$fmdata[ename]&tclass={$fmdata['tclass']}");
	}

}elseif($action == 'detail' && $ename){
	if(!($fragment = fetch_one($ename))) cls_message::show('请指定正确的碎片。');
	if(!submitcheck('bsubmit')){
		tabheader('碎片设置','fragmentdetail',"?entry=$entry&action=$action&ename=$ename",2,1,1);
		trbasic('碎片名称','fmdata[title]',$fragment['title'],'text',array('validate' => ' onfocus="initPinyin(\'fmdata[ename]\')"' . makesubmitstr('fmdata[title]',1,0,3,30)));
		trbasic('碎片英文标识','',$fragment['ename'],'');
		trbasic('碎片分类','fmdata[frcaid]',makeoption(array(0 => '不分类') + $frcatalogs,$fragment['frcaid']),'select');
		trbasic('模板类型','',@$tclassarr[$fragment['tclass']],'');
		trbasic('开始日期',"fmdata[startdate]",$fragment['startdate'] ? date('Y-m-d',$fragment['startdate']) : '','calendar',array('guide'=>'不限日期请留空','validate'=>makesubmitstr("fmdata[startdate]",0,0,0,0,'date')));
		trbasic('结束日期',"fmdata[enddate]",$fragment['enddate'] ? date('Y-m-d',$fragment['enddate']) : '','calendar',array('guide'=>'不限日期请留空','validate'=>makesubmitstr("fmdata[enddate]",0,0,0,0,'date')));
		trbasic('允许传入的变量名','fmdata[params]',$fragment['params'],'text',array('guide' => '逗号分隔多个变量名，指定编码请加上charset变量，取值gbk/big5/utf-8。'));
		trbasic('缓存周期',"fmdata[period]",$fragment['period'],'text',array('guide'=>'单位：分钟，留空为不缓存。','validate'=>makesubmitstr("fmdata[period]",0,'int',0,4)));
		tabfooter('bsubmit');
		a_guide('fragmentdetail');
	}else{
		$fmdata['title'] = trim(strip_tags($fmdata['title']));
		if(!$fmdata['title']) cls_message::show('资料不完全',M_REFERER);
		foreach(array('startdate','enddate') as $var){
			if(isset($fmdata[$var])){
				$fmdata[$var] = trim($fmdata[$var]);
				$fmdata[$var] = !cls_string::isDate($fmdata[$var]) ? 0 : strtotime($fmdata[$var]);
			}
		}
		$fmdata['params'] = trim(strip_tags($fmdata['params']));
		$params = explode(',',$fmdata['params']);
		foreach($params as $k => $v){
			if(preg_match("/[^a-zA-Z_0-9]+/",$v)) unset($params[$k]);
		}
		$fmdata['params'] = implode(',',$params);
		$fmdata['period'] = max(0,intval($fmdata['period']));

		$db->query("UPDATE {$tblprefix}fragments SET
		title='$fmdata[title]',
		frcaid='$fmdata[frcaid]',
		startdate='$fmdata[startdate]',
		enddate='$fmdata[enddate]',
		params='$fmdata[params]',
		period='$fmdata[period]'
		WHERE ename ='$ename'
		");
		adminlog('设置碎片');
		cls_CacheFile::Update('fragments');
		cls_message::show('碎片设置完成', axaction(6,"?entry=$entry&action=fragmentsedit"));
	}

}elseif($action == 'view' && $ename){
	if(!($fragment = fetch_one($ename))) cls_message::show('请指定正确的碎片。');
	$na = array_filter(explode(',',$fragment['params']));
	if($na){
		tabheader('设置碎片的预览变量','fragmentdetail',"{$cms_abs}api/pick.php",2,0,1,0,'get');
		trhidden('frname',$ename);
		trhidden('frview',1);
		foreach($na as $k) trbasic("$k 变量的值",$k,'','text',$k == 'charset' ? array('guide' => '指定内容的编码，取值gbk/big5/utf-8') : array());
		tabfooter('bsubmit','预览');
	}else mheader("location:{$cms_abs}api/pick.php?frname=$ename&frview=1");
}elseif($action == 'pick' && $ename){
	if(!($fragment = fetch_one($ename))) cls_message::show('请指定正确的碎片。');
	$pstr = '';
	if($na = array_filter(explode(',',$fragment['params']))){
		foreach($na as $k) $pstr .= "&$k=变量值";
	}
	tabheader('站内调用碎片方法 - '.$fragment['title']);
	trbasic("碎片js调用代码",'js',"<script language=\"javascript\" src=\"{\$cms_abs}api/pick.php?frname=$ename$pstr\"></script>",'textarea',array('w' => 560,'h' => 30,'guide' => '放置于页面模板中需要调用该碎片内容的位置，如有变量，请自行设置变量值。'));
	trbasic("模板标识调用代码",'tag',"{c\$pre_$ename [tclass=fragment/] [url={\$ cms_abs}api/pick.php?frname=$ename&frdata=1$pstr/] [ttl=1800/] [timeout=2/]}{/c\$pre_$ename}",'textarea',array('w' => 560,'h' => 50,'guide' => '放置于页面模板中需要调用该碎片内容的位置，如有变量，请自行设置变量值。<br>pre_'.$ename.'(标识名:注意首尾同步),ttl(缓存秒数),timeout(超时秒数)，可自行修改。'));
	trbasic("碎片内容提取url",'xml',"{\$ cms_abs}api/pick.php?frname=$ename&frdata=1$pstr",'textarea',array('w' => 560,'h' => 30,'guide' => '用于自定义 碎片调用 模板标识时设置url值，如有变量，请自行设置变量值。'));
	tabfooter();
	tabheader('跨站调用碎片方法 - '.$fragment['title']);
	trbasic("碎片js调用代码",'js',"<script language=\"javascript\" src=\"{$cms_abs}api/pick.php?frname=$ename$pstr\"></script>",'textarea',array('w' => 560,'h' => 30,'guide' => '适用于任何核心的系统进行跨站数据的js调用。'));
	trbasic("模板标识调用代码",'tag',"{c\$pre_$ename [tclass=fragment/] [url={$cms_abs}api/pick.php?frname=$ename&frdata=1$pstr/] [ttl=1800/] [timeout=2/]}{/c\$pre_$ename}",'textarea',array('w' => 560,'h' => 50,'guide' => '只适用于08cms核心的系统进行跨站数据调用,置于调用方系统的相关页面模板内。'));
	trbasic("碎片内容提取url",'xml',"{$cms_abs}api/pick.php?frname=$ename&frdata=1$pstr",'textarea',array('w' => 560,'h' => 30,'guide' => '适用于08cms核心的系统自定义 碎片调用 模板标识时设置url值，也可用于有类似接口的其它系统。'));
	tabfooter();
}elseif($action == 'tpl' && $ename){
	if(!($fragment = fetch_one($ename))) cls_message::show('请指定正确的碎片。');
	$ttype = $fragment['tclass'] ? 'ctag' : 'rtag';
	include_once dirname(__FILE__) . '/mtags/_taginit.php';
	$mtags = load_mtags($ttype);
	$isadd = 1;$_infragment = 1;
	if($mtag = cls_cache::Read($ttype,'fr_'.$ename,'')) $isadd = 0;
    $mtag = array_merge((array) $mtag, (array) @$mtagnew);
    empty($_POST) || cls_Array::array_stripslashes($mtag);
	$tclass = $mtagnew['tclass'] = empty($fragment['tclass']) ? '' : $fragment['tclass'];
    $sclass = @$mtag['setting']['chids']; // = '4'; //默认没有修改过的模型：include里面还要用它；
    _08_FilesystemFile::filterFileParam($tclass);
	$mtagnew['ename'] = 'fr_'.$ename;
	$mtagnew['cname'] = '碎片_'.$fragment['title'];
	if(!submitcheck($isadd ? 'bmtagadd' : 'bmtagsdetail')){
		$upform = in_array($tclass,array('image','images',)) ? 1 : 0;
		$helpstr = !$tclass ? '' : "&nbsp; &nbsp;>><a href=\"tools/taghelp.html#".(str_replace('tag','',$ttype).'_'.$tclass)."\" target=\"08cmstaghelp\">帮助</a>";
		tabheader('碎片内容调用模板'.$helpstr,'mtagsadd',"?entry=$entry&action=$action&ename=$ename",2,$upform);
        $mtagses = _08_factory::getMtagsInstance($tclass);
        if ( is_object($mtagses) )
        {
            $mtagses->showCotypesSelect($mtag);
            # 如果是编辑选中时让定义sclass
            if( empty($_POST) )
            {
                trhidden('_sclass', $mtagses->getSclass(@(array)$mtag['setting']));
            }
        }
		trbasic('标识名称','',$mtagnew['cname'], '');
		trbasic('标识英文名称','',$mtagnew['ename'], '');
		
		list($modeAdd,$modeSave) = array($isadd,0);
		include(dirname(__FILE__) . "/mtags/".($tclass ? $tclass : 'rtag').".php");
		/*$b_flag = submitcheck('re_preid') || ($modeAdd && !submitcheck('set_preid'));
		if(!$b_flag || empty($tclass)) */
		tabfooter($isadd ? 'bmtagadd' : 'bmtagsdetail','提交');
		
		a_guide($ttype.(empty($mtagnew['tclass']) ? 'edit' : $mtagnew['tclass']));
	}else{
		list($modeAdd,$modeSave) = array($isadd,1);
		include_once dirname(__FILE__) . "/mtags/".($tclass ? $tclass : 'rtag').".php";
		$mtagnew['setting'] = empty($mtagnew['setting']) ? array() : $mtagnew['setting'];
		if(!empty($mtagnew['setting'])){
			foreach($mtagnew['setting'] as $key => $val){
				if(in_array($key,$unsetvars) && empty($val)) unset($mtagnew['setting'][$key]);
				if(!empty($unsetvars1[$key]) && in_array($val,$unsetvars1[$key])) unset($mtagnew['setting'][$key]);
			}
		}
		$mtagnew['template'] = empty($mtagnew['template']) ? '' : stripslashes($mtagnew['template']);
		$mtagnew['disabled'] = @$iscopy || empty($mtag['disabled']) ? 0 : 1;
		$mtag = array(
		'cname' => stripslashes($mtagnew['cname']),
		'ename' => $mtagnew['ename'],
		'tclass' => $tclass,
		'template' => $mtagnew['template'],
		'setting' => $mtagnew['setting'],
		);
        $mtag['setting']['chids'] = empty($sclass) ? @$_sclass : $sclass; //修正文档模型ID的保存
		cls_CacheFile::Save($mtag,cls_cache::CacheKey($ttype,$mtagnew['ename']),$ttype);
		adminlog('设置碎片内容调用模板');
		cls_message::show('碎片内容调用模板设置完成',axaction(6,"?entry=$entry&action=fragmentsedit"));
	}
}

function ename_arr(){
	global $db,$tblprefix;
	$re = array();
	$query = $db->query("SELECT ename FROM {$tblprefix}fragments");
	while($r = $db->fetch_array($query)) $re[] = $r['ename'];
	return $re;
}
function fetch_one($ename){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}fragments WHERE ename='$ename'");
	return $r;
}


?>
