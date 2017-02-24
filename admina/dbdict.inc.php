<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(@$action !== 'dictExpert') aheader();
if($re = $curuser->NoBackFunc('database')) cls_message::show($re);
$dbfields = cls_cache::Read('dbfields');
$db2 = clone $db;

if(empty($action)){
	$dbtable = empty($dbtable) ? 'catalogs' : $dbtable;
	if(!submitcheck('bdbdict')){
		backnav('data','dbdict');

		$dbtables = array('' => '请选择数据表');
		$tablists = cls_DbOther::tabLists();
		$dbtable = isset($tablists[$dbtable]) ? $dbtable : 'catalogs';
		$filterbox = '选择数据表'.'&nbsp; &nbsp;';
		$filterbox .= "<select style=\"vertical-align: middle;\" name=\"dbtable\" onchange=\"redirect('?entry=dbdict&dbtable=' + this.options[this.selectedIndex].value);\">";
		foreach($tablists as $tab=>$r){
			$filterbox .= "<option value='$tab'".($dbtable == $tab ? ' selected' : '').">$tblprefix$tab --- $r[Comment] ($r[Rows])</option>";	
		}
		$filterbox .= "</select>";
		$lnk1 = "<a href='?entry=dbdict&action=dictCheck' onclick=\"return floatwin('open_dicCheck2',this,480,560)\">检测&gt;&gt;</a>";
		$lnk2 = "<a href='?entry=dbdict&action=dictExpert' target='_blank'>导出&gt;&gt;</a>";
		$exp = "<span style='float:right'> $lnk1 &nbsp; $lnk2 &nbsp; </span>";
		tabheader($exp.$filterbox);
		tabfooter();
		
		$tblfields = cls_DbOther::dictComment($dbtable);
		
		tabheader('数据库字段列表'.'&nbsp; -&nbsp; '.$dbtable,'dbdict',"?entry=dbdict&dbtable=$dbtable",5);
		trcategory(array('序号','字段名称','字段类型','内容替换','字段备注'));

		$i = 1;
		foreach($tblfields as $k => $v){ 

			echo "<tr>".
				"<td class=\"txtC w30\">$i</td>\n".
				"<td class=\"txtL\"><b>$k</b></td>\n".
				"<td class=\"txtL\">$v->Type</td>\n".
				"<td class=\"txtC\">".($v->Key=='PRI'?'':"<a href=\"?entry=dbdict&action=dbreplace&dbtable=$dbtable&dbfield=$k\">&gt;&gt;".'替换'."</a>")."</td>\n".
				"<td class=\"txtC\"><input type=\"text\" size=\"30\" name=\"dbfieldsnew[$dbtable][$k] xname=\"$k\" value=\"$v->Comment\" ></td>\n".
				"</tr>";
			$i ++;
		}
		tabfooter('bdbdict','修改');
		a_guide('dbfieldsremark');
	}else{
		if(!empty($dbfieldsnew)){
			foreach($dbfieldsnew as $k => $v){
				if(!empty($v)){
					foreach($v as $k1 => $v1){
						if(empty($v1)){
							$db2->query("DELETE FROM {$tblprefix}dbfields WHERE ddtable='$k' AND ddfield='$k1'");
						}else{
							if(!isset($dbfields[$k.'_'.$k1])){
								$db2->query("INSERT INTO {$tblprefix}dbfields SET ddtable='$k',ddfield='$k1',ddcomment='$v1'");
							}else $db2->query("UPDATE {$tblprefix}dbfields SET ddcomment='$v1' WHERE ddtable='$k' AND ddfield='$k1'");
						}
					}
				}
				
			}
		}
		cls_CacheFile::Update('dbfields');
		cls_message::show('数据库字段备注修改完成',"?entry=dbdict&dbtable=$dbtable");

	}

}elseif($action == 'dbreplace'){
	if(empty($dbtable)) cls_message::show('请指定正确的数据表');
	if(empty($dbfield)) cls_message::show('请指定正确的字段。');
	if(!submitcheck('bdbreplace')){
		$mode0arr = array(0 => '常规',1 => '正则');
		tabheader('字段内容替换操作','dbreplace',"?entry=dbdict&action=$action&dbtable=$dbtable&dbfield=$dbfield",2);
		trbasic('当前数据表','',$dbtable,'');
		trbasic('当前字段','',$dbfield,'');
		trbasic('搜索模式'.'&nbsp; [<a href="http://dev.mysql.com/doc/refman/5.1/zh/regexp.html" target="_blank">'.'正则帮助'.'</a>]','mode',makeradio('mode',$mode0arr,0),'');
		trbasic('搜索文本','rpstring','','textarea');
		trbasic('替换文本','tostring','','textarea');
		trbasic('WHERE附加条件字串','where','','text',array('guide'=>'不要含WHERE','w'=>50));
		tabfooter('bdbreplace','开始替换');
		a_guide('dbreplace');
	}else{
		if(!isset($mode)||!$rpstring||!$tostring)cls_message::show('搜索模式,搜索文本或替换文本不能为空',M_REFERER);
		$rs=$db2->query("SHOW COLUMNS FROM $dbtable",'SILENT');
		unset($key);
		while($row=$db2->fetch_array($rs))
			if('PRI'==$row['Key']){
				$key=$row['Field'];
				break;
			}
		if(1==$mode){
			if(!isset($key))cls_message::show('数据表没有主键',M_REFERER);
			if($dbfield == $key)cls_message::show('请不要批量处理主键字段。',M_REFERER);
			$rpstring=stripslashes($rpstring);
			$tostring=stripslashes($tostring);
			$where=$where?" and $where":'';
			$rs=$db2->query("select `$key`,`$dbfield` from `$dbtable` where `{$tblprefix}$dbfield` REGEXP '".str_replace(array("\\","'"),array("\\\\","\\'"),$rpstring)."'$where");
			$count=$db2->num_rows($rs);
			if(0==$count)cls_message::show('没有找到符合条件的记录。',M_REFERER);
			$replace=0;
			while($row=$db2->fetch_array($rs))
				if($db2->query("update `$dbtable` set `$dbfield` = '".addslashes(preg_replace($rpstring,$tostring,$row[$dbfield]))."' where `$key` = '".addslashes($row[$key])."'")) $replace++;
			cls_message::show('查找'.$count.'条记录。成功替换'.$replace.'条记录。',M_REFERER);
		}else{
			if(isset($key)&&$dbfield == $key)cls_message::show('请不要批量处理主键字段。',M_REFERER);
			$where = $where ? " where $where" : '';
			$db2->query("update `$dbtable` set `$dbfield`=replace(`$dbfield`,'$rpstring','$tostring')$where");
			cls_message::show('成功替换'.$db2->affected_rows().'条记录。',M_REFERER);
		}
	}
	
}elseif($action=='dictExpert' || $action=='dictCheck'){ // 检测,导出
	$tdoc = ftplDoc();
	$ttab = ftplTab();
	$tablists = cls_DbOther::tabLists();
	$slist = $tlist = ''; 
	$clist = '<tr><td>数据表</td><td>字段</td></tr>'; $n=0;
	foreach($tablists as $tab=>$r){ //echo "\n<br>$tab,";
		$t1 = $ttab; $ra='';
		$tblfields = cls_DbOther::dictComment($tab);
		if($action=='dictCheck' && empty($r['Comment'])){
			$clist .= "<tr><td>$tab</td><td>------</td></tr>\n";
		}
		foreach($tblfields as $fk=>$fv){ //Field Type Collation Null Key Default Extra Privileges Comment
			if($action=='dictCheck'){
				$t3 = substr($fk,0,3); //pid
				$t4 = substr($fk,0,4); //stat,ccid
				$t7 = substr($fk,0,7); //inorder,incheck
				if($t3=='pid' || in_array($t4,array('stat','ccid')) || in_array($t7,array('inorder','incheck'))) continue;
				empty($fv->Comment) && $clist .= "<tr><td>$tab</td><td>$fk</td></tr>\n";
			}else{
				$ra .= "<tr><td>$fv->Field</td><td>$fv->Comment</td><td>$fv->Type</td><td>$fv->Null</td><td>$fv->Key</td><td>$fv->Default</td></tr>\n";
			}
		} //if($n==1) print_r($fv);
		if($action!='dictCheck'){
			$slist .= str_replace(array('{fields}','{tabid}','{tabname}'),array($ra,$tab,$r['Comment']),$t1);
			$tlist .= "<a href='#$tab'>".($r['Comment'] ? $r['Comment'] : $tab)."</a>\n"; //$n++; if($n>20) break;
		}
	}
	if($action=='dictCheck'){
		$clist || $clist = '<tr><td>(无记录)</td></tr>\n';
		echo "<div class='itemtitle'><h3>检测如下 [数据表 : 字段] 没有注释</h3></div><table class='tb tb2 bdbot'>$clist</table>";
	}else{
		$str = str_replace(array('{tablists}','{tabmap}','{tabcnt}','{sysname}'),array($slist,$tlist,count($tablists),cls_env::mconfig('hostname')),$tdoc);
		header("Content-Type:text/html;CharSet=$mcharset");
		header("Content-Disposition:attachment;Filename=dict-".date('Y-md-Hi',$timestamp).".html");
		die($str);
	}
}

function ftplTab(){
	return "<a name='{tabid}'></a>
<table border='0' align='center' cellpadding='5' cellspacing='1' class='tab'>
<tr class='title'><td colspan='6'><a href='#' class='r'>[Top]</a>{tabid}[{tabname}]</td></tr>
<tr class='head'><td width='20%'>Field</td><td width='25%'>Memo</td><td>Type</td><td width='10%'>Null</td><td width='10%'>Key</td><td width='10%'>Default</td></tr>
{fields}</table>";
}

function ftplDoc(){
	return '
<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset={mcharset}" />
<title>({sysname})数据库词典</title>
<style type="text/css">
body, td, th { font-size: 12px; }
a:link, a:visited { text-decoration: none; }
td.itm { padding-left:8px; }
td.itm a { width: 125px; height: 15; font-style: normal; float: left; overflow: hidden; border: 1px solid #CCC; white-space: nowrap; word-break: keep-all; padding: 3px; margin: 3px; }
a.r { float: right; }
table.tab { width: 720px; background: #69C; border: 1px solid #069; margin: 12px auto 1px auto; }
table tr td { background: #FFF; }
table tr.title td { font-weight: bold; background: #FFF; }
table tr.head td { font-weight: bold; background: #669; color: #FFF; }
table tr.bgFFF td { background-color: #FFF; }
table tr.bgCCC td { background-color: #F0F0F0; }
</style></head><body>
<table border="0" align="center" cellpadding="5" cellspacing="1" class="tab">
<tr class="title">
  <td colspan="6"><a href="?" class="r">共个{tabcnt}表[刷新]</a>({sysname})数据库词典</td>
</tr>
<tr bgcolor="#FFFFFF">
  <td colspan="6" class="itm">
{tabmap}<a href="#~remark~">[备注]</a>
  </td>
</tr>
</table>
{tablists}
<a name="~remark~"></a>
<table border="0" align="center" cellpadding="5" cellspacing="1" class="tab">
  <tr class="title"><td colspan="3"><a href="#" style="float:right">[Top]</a>[数据库词典备注]</td></tr>
  <tr class="head"><td width="20%">项目</td><td width="25%">字段:意义</td><td>备注</td></tr>
  <tr><td>类系相关</td><td>ccid*,<br>ccid*date期限</td><td>*为数字，参考后台：<br>网站架构 &gt;&gt; 类目管理</td></tr>
  <tr><td>统计相关</td><td>stat1,stat2,stat3... 或<br>stat_1,stat_2,stat_3...         </td><td>另见文档&lt计划任务-统计字段.txt&gt，用于统计交互,合辑,文档数量等；<br>根据各系统定义，相关的表有：archives*,coclass*,members_sub,</td></tr>
  <tr><td>地图相关</td><td>map,map_0,map_1或<br>ditu,ditu_0,dutu_1或<br>dt,dt_0,dt_1 </td><td>对应地图坐标及其经度,纬度</td></tr>
  <tr><td>合辑相关</td><td>pid*:合辑项目<br>inorder*:辑内顺序<br>incheck*:辑内审核</td><td>*为数字，参考后台：<br>网站架构 &gt;&gt; 扩展架构 &gt;&gt; 合辑项目管理</td></tr>
</table>
</body></html>';	
}

?>
