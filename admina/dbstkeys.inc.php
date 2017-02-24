<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('database')) cls_message::show($re);
/*
提示:访问: ?entry=dbstkeys&action=output&aext=1，根据提示，更新对比文件
各系统，出测试包时（打新包前），更新索引对比文件供打包用；
家装/汽车:因两个版本共用数据,需要在完全分离了数据库情况下进行。
*/
backnav('data','dbstkeys');
$cacpath = _08_EXTEND_DIR.DS._08_CACHE_DIR.DS._08_SYSCACHE_DIR.DS.'dbst_keys.cac.php';
if(empty($action)) $action = 'compare';
if(empty($aext)) $aext = '';
$acts = array('compare'=>'标准索引对比','output'=>'导出索引数组','noprikey'=>'无主索引表列表');
//'getsql'=>'生成补全主索引sql',
$amsg = '标准索引对比'; $alnk = '';
if(!empty($aext)){
	foreach($acts as $k=>$v){
		if($k==$action){
			$alnk .= "<span style='color:#f00'>$v</span>";
			$amsg = $v;
		}else{
			$alnk .= "<a href='?entry=dbstkeys&action=$k&aext=1'>$v</a>";
		}
		$alnk = "<span style='float:right'>$alnk</span>";
	}
}else{
	$alnk = "";
}

$db2 = clone $db;
tabheader(" 索引对比管理 --- &nbsp; $alnk &nbsp; $amsg &nbsp; "); //,'cfdebug',"?entry=$entry&action=$action"
if($action == 'compare'){
	
	if(!(is_file(M_ROOT.$cacpath))){
		echo "\n<tr><td class=\"txtC\"><span style='color:red;'>(标准索引文件不存在)</span>\n</td></tr>";	
	}else{

		$a1 = cls_cache::exRead('dbst_keys'); 
		$a2 = dbIndexs($dbname); //
		
		echo "\n<tr><td width='480'>";
		echo arrShow($a1,$a2,'[标准索引]中有,但[当前数据库] 无以下索引','[标准索引]与[当前数据库]相同','当前数据库');
		echo arrShow($a2,$a1,'[当前数据库]中有,但[标准索引] 无以下索引','[当前数据库]与[标准索引]相同','标准索引');
		//echo arrShow($pub0,'[标准索引/当前数据库] 公共索引','[标准索引]与[当前数据库] 完全相同');
		
		$str = ''; //$i=0;
		$str .= "\n<tr><td colspan=3><div class='conlist1'>标准索引 ---- (".count($a1).")个 \n</div></td></tr>";
		$str .= "<th class=\"txtC\" width='28%'>数据表</th>
				 <th class=\"txtC\" width='28%'>索引名称</th>
				 <th class=\"txtC\" >索引字段</th>";
		foreach($a1 as $k1=>$v1){
			$item = explode('~', $k1);
			$str .= "\n<tr><td>$item[0]</td><td>$item[1]</td><td class=\"txtL\">$v1</td></tr>";	
		}
		echo "<table class=' tb tb2 bdbot'>$str</table>";
		
		echo "\n</td></tr>";

	}

}elseif($action == 'output'){
		
	echo "\n<tr><td class=\"txtC w200\" valign='top'><b>当前索引数组</b></td>\n";
	echo "<td class=\"txtL\" style='line-height:120%;'><pre>(可复制以下内容到{$cacpath}文件,手动更新[标准索引]对比缓存)\n"; 

	$a2 = dbIndexs($dbname);
	foreach($a2 as $k=>$v){
		echo "\n'$k'=>'$v',"; 
	}

	echo "\n</pre><br>\n(可复制到{$cacpath}文件,手动更新[标准索引]对比缓存)</td></tr>";
	
}elseif($action == 'noprikey'){ //
	
	echo "\n<tr><td class=\"txtC w200\" valign='top'><b>无主索引的表</b></td>\n";
	echo "<td class=\"txtL\">"; 
	
	$query = $db2->query("SHOW TABLES FROM $dbname", 'SILENT');
	$index = array(); $sql = '';
	while($v = $db2->fetch_row($query)){ 
	  $ind = $db2->query("SHOW index FROM $v[0]", 'SILENT');
	  $flag = 0; if(!$ind) continue;
	  while($t = @$db2->fetch_array($ind)){
		 	 $flag++;
	  }
	  if($flag<1){ 
	  	echo "\n$v[0]<br>"; 
		$fields = dbFields($v[0]); //print_r($fields);
		$sql .= "\n ALTER TABLE `$v[0]` ADD PRIMARY KEY ( `$fields[0]` ) <br>";
	  }
	}
	
	echo "\n</td></tr>";
	
	echo "\n<tr><td class=\"txtC w200\" valign='top'><b>补全主索引sql</b></td>\n";
	echo "<td class=\"txtL\">\n$sql\n
	<br><span class='tips1'>根据需要, 可复制sql手动执行</span>
	</td></tr>";
		
} //echo "\n</pre><br>\n(复制到{$cacpath}文件,手动更新[标准索引]对比缓存)</td></tr>";

tabfooter(''); 
a_guide('dbstkeys');


// arr输出
function arrShow($a1,$a2,$title,$tnull,$cobj){
	
	$str = ''; $i=0;
	$str .= "\n<tr><td colspan=3><div class='conlist1'>$title\n</div></td></tr>";
	$str .= "<th class=\"txtC\" width='28%'>数据表</th>
			 <th class=\"txtC\" width='28%'>索引名称</th>
			 <th class=\"txtC\" >索引字段</th>";
	foreach($a1 as $k1=>$v1){
		$istr = '';
		$item = explode('~', $k1);
		if(!isset($a2[$k1])){
			$i++;
			$istr .= "<td>$item[0]</td><td>$item[1]</td><td class=\"txtL\"><span style='color:#f00;'>[{$cobj}无此项]</span> $v1</td>";	
		}elseif($a2[$k1]!=$v1){
			$i++;
			$istr .= "<td>$item[0]</td><td>$item[1]</td><td class=\"txtL\">$v1 <br> <span style='color:#f00;'>[{$cobj}值为]</span> ".$a2[$k1]."</td>";	
		}
		$istr && $str .= "\n<tr>$istr</tr>";
	}
	if($i==0) $str .= "\n<tr><td colspan=3><span style='color:#f00;'>$tnull</span>\n</td></tr>";
	return "<table class=' tb tb2 bdbot'>$str</table>\n";
	
}

// db索引
function dbIndexs($db){
	global $db2;
	$query = $db2->query("SHOW TABLES FROM $db", 'SILENT');
	$index = array(); //$indstr = "";
	$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
	while($v = $db2->fetch_row($query)){ 
	  $ind = $db2->query("SHOW index FROM $v[0]", 'SILENT');
	  while($t = @$db2->fetch_array($ind)){
		  $tab = substr($t['Table'],strlen($tblprefix));
		  $key = "{fix}_$tab~$t[Key_name]";
		  if(isset($index[$key])){
			  $index[$key] .= ",$t[Column_name]";
		  }else{
			  $index[$key] = "$t[Column_name]";
		  }
	} }
	return $index;
}
// db字段
function dbFields($tab){
	global $db2;
	$cols = array();
	$fields = $db2->query("show full fields from $tab", 'SILENT');
	if($fields){
	while($row = @$db2->fetch_array($fields)){
		  $cols[] = "$row[Field]"; // $v[0] : $row[Field] : $row[Type]
	} }
	return $cols;
}


?>