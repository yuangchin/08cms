<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('database')) cls_message::show($re);
if(empty($action)) $action = 'ddrecords';
if($action == 'ddconfig'){
	if(!submitcheck('bmconfigs')){
		tabheader('SQL诊断设置','cfdebug',"?entry=$entry&action=$action");
		trbasic('是否收集页面SQL记录','mconfigsnew[debugenabled]',empty($mconfigs['debugenabled']) ? 0 : $mconfigs['debugenabled'],'radio',array('guide' => '仅在分析系统效率时使用，收集记录本身会增加服务器负担，平时请保持关闭'));
		trbasic('包含管理后台及会员中心','mconfigsnew[debugadmin]',empty($mconfigs['debugadmin']) ? 0 : $mconfigs['debugadmin'],'radio',array('guide' => '默认不收集管理后台及会员中心的查询记录'));
		trbasic('收集超过多少耗时的查询','mconfigsnew[debuglow]',empty($mconfigs['debuglow']) ? 0 : $mconfigs['debuglow'],'text',array('guide' => '单位:ms，留空为收集所有查询'));
		trbasic('清空旧查询记录','debugclear',0,'radio',array('guide' => '系统默认最多收集5万条SQL记录，在开始分析前请尽量清空之前的旧记录'));
		tabfooter('bmconfigs');
	}else{
		$mconfigsnew['debuglow'] = max(0,intval($mconfigsnew['debuglow']));
		saveconfig('debug');
		if(!empty($debugclear)) $db->query("TRUNCATE TABLE {$tblprefix}dbdebugs");
		cls_message::show('SQL诊断设置完成',axaction(6,"?entry=$entry"));
	}
}elseif($action == 'ddrecords'){
	backnav('data','dbdebug');
	$page = empty($page) ? 1 : max(1, intval($page));
	submitcheck('bfilter') && $page = 1;
	$ddsql = empty($ddsql) ? '' : trim($ddsql);
	$ddurl = empty($ddurl) ? '' : trim($ddurl);
	$ddtpl = empty($ddtpl) ? '' : trim($ddtpl);
	$ddtag = empty($ddtag) ? '' : trim($ddtag);
	$inddused = empty($inddused) ? 0 : max(0,intval($inddused));
	$outddused = empty($outddused) ? 0 : max(0,intval($outddused));

	$fromsql = "FROM {$tblprefix}dbdebugs";
	$wheresql = "";
	$ddsql && $wheresql .= " AND (ddsql ".sqlkw($ddsql)." OR ddtbl ".sqlkw($ddsql).")";
	$ddurl && $wheresql .= " AND ddurl ".sqlkw($ddurl);
	$ddtpl && $wheresql .= " AND ddtpl ".sqlkw($ddtpl);
	$ddtag && $wheresql .= " AND ddtag ".sqlkw($ddtag);
	$inddused && $wheresql .= " AND ddused<'$inddused'";
	$outddused && $wheresql .= " AND ddused>'$outddused'";

	$filterstr = '';
	foreach(array('ddsql','ddurl','ddtpl','ddtag','inddused','outddused',) as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));
	$wheresql = $wheresql ? 'WHERE '.substr($wheresql,5) : '';
	
	if(!submitcheck('bsubmit')){
		echo form_str('ddrecords',"?entry=$entry&action=$action&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "查询:<input class=\"text\" name=\"ddsql\" type=\"text\" value=\"$ddsql\" size=\"20\" style=\"vertical-align: middle;\" title=\"搜索查询或数据库\">&nbsp; ";
		echo "URL:<input class=\"text\" name=\"ddurl\" type=\"text\" value=\"$ddurl\" size=\"20\" style=\"vertical-align: middle;\">&nbsp; ";
		echo "模板:<input class=\"text\" name=\"ddtpl\" type=\"text\" value=\"$ddtpl\" size=\"15\" style=\"vertical-align: middle;\">&nbsp; ";
		echo "标识:<input class=\"text\" name=\"ddtag\" type=\"text\" value=\"$ddtag\" size=\"15\" style=\"vertical-align: middle;\">&nbsp; ";
		echo "查询用时:<input class=\"text\" name=\"outddused\" type=\"text\" value=\"".($outddused ? $outddused : '')."\" size=\"4\" style=\"vertical-align: middle;\">-";
		echo "<input class=\"text\" name=\"inddused\" type=\"text\" value=\"".($inddused ? $inddused : '')."\" size=\"4\" style=\"vertical-align: middle;\">ms&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();
	
		tabheader("SQL诊断分析 [".(empty($debugenabled) ? '统计关闭中' : '统计开启中')."]&nbsp; &nbsp; >><a href=\"?entry=$entry&action=ddconfig\" onclick=\"return floatwin('open_ddrecords',this)\">设置</a>",'','',12);
		$cy_arr = array('序号','SQL语句|L','所属数据库|L|H','用时(ms)','模板/标识|L','页面URL|L','受访页面');
        /**
         * include/debug.cls.php里按照53行注释处理，可用以下提示显示相关信息
         * 则打印信息类似于：/include/userbase.cls.php : 272 能精确到哪个文件哪一行
         */ 
		#$cy_arr = array('序号','SQL语句|L','所属数据库|L|H','用时(ms)','页面/行数|C','页面URL|L','受访页面');
		trcategory($cy_arr);
	
		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * $fromsql $wheresql ORDER BY ddid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$count = $db->result_one("SELECT count(*) $fromsql $wheresql");
		$ii = $count - $pagetmp * $atpp + 1;
		// 格式化后,便于复制分析,其他无意义
		$arr = array("INNER JOIN","WHERE","ORDER BY","GROUP BY","LIMIT","FORCE INDEX",);
		while($r = $db->fetch_array($query)){
			$sql = preg_replace("/\s+/", " ", $r['ddsql']); //过滤多余回车 
			foreach($arr as $v) $sql = str_replace($v,"\n$v",$sql);	
			//$sql = str_replace(array("    ","   ","  ")," ",$sql);
			$sql = str_replace(") AND",") \n AND",$sql); 
			if("$r[ddtag]"){
				$ddtag = "$r[ddtag]";
				if(is_file(cls_tpl::TemplateTypeDir('tag')."ctag$ddtag.cac.php")){
					$ddtag = "<a href=\"?entry=mtags&action=mtagsdetail&ttype=ctag&tname=$ddtag\" onclick=\"return floatwin('open_mtagsedit',this)\">$ddtag</a>";
				}
			}else $ddtag = '-';
			$ii --;
			echo "<tr class=\"txt\"><td class=\"txtC\">$ii</td>\n";
			echo "<td class=\"txtL\"><textarea class=\"js-resize\" style=\"width:360px;height:68px\" >$sql</textarea></td>\n";
			echo "<td class=\"txtL\">$r[ddtbl]</td>\n";
			echo "<td class=\"txtC\">$r[ddused]</td>\n";
			echo "<td class=\"txtL\">".($r['ddtpl'] ? "<a href=\"?entry=mtpls&action=mtpldetail&tplname=$r[ddtpl]\" onclick=\"return floatwin('open_mtplsedit',this)\">$r[ddtpl]</a>" : '-')."<br>$ddtag</td>\n"; 
            # 当/include/debug.cls.php里按照53行注释处理，可用以下提示显示相关信息
			#echo "<td class=\"txtC\">".$r['ddtpl']."</td>\n"; 
			echo "<td class=\"txtL\"><textarea class=\"js-resize\" style=\"width:180px;height:60px\">$r[ddurl]</textarea></td>\n";
			echo "<td class=\"txtC\"><a href=\"$r[ddurl]\" target=\"_blank\">>>查看</a><br>".($r['ddate'] ? date('Y-m-d',$r['ddate']) : '-')."<br>".($r['ddate'] ? date('H:i:s',$r['ddate']) : '-')."</td>\n";
			echo "</tr>\n";
		}
		tabfooter();
		echo multi($count, $atpp, $page, "?entry=$entry&action=$action$filterstr");
		echo '<br><br>'.strbutton('bsubmit','清除记录');
	}else{
		$db->query("DELETE $fromsql $wheresql");
		cls_message::show('记录清除成功',"?entry=$entry&action=$action$filterstr");
	}
}

?>