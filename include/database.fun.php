<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query){
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach($queries as $query){
			$str1 = substr($query, 0, 1);
			if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
		}
		$num++;
	}
	return($ret);
}
function sqldumptable($table,$startfrom = 0,$currsize = 0){
	global $db, $sizelimit, $startrow, $sqlcompat, $sqlcharset, $dumpcharset, $usehex, $complete;
	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = $db->query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(in_str('asession',$table)){
		return ;
	}elseif(!$query && $db->errno() == 1146){
		return;
	}elseif(!$query) {
		$usehex = FALSE;
	}else {
		while($fieldrow = $db->fetch_array($query)){
			$tablefields[] = $fieldrow;
		}
	}
	if(!$startfrom){
		$createtable = $db->query("SHOW CREATE TABLE $table", 'SILENT');
		if(!$db->error()){
			$tabledump = "DROP TABLE IF EXISTS $table;\n";
		}else return '';

		$create = $db->fetch_row($createtable);
		$tabledump .= $create[1].";\n\n";

		if($sqlcompat == 'MYSQL41' && $db->version() < '4.1') $tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
		if($db->version() > '4.1' && $sqlcharset)  $tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=[a-zA-Z0-9_\.\-]{2,8}/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);

		$tablestatus = $db->fetch_one("SHOW TABLE STATUS LIKE '$table'");
		if($sqlcompat == 'MYSQL40' && $db->version() >= '4.1' && $db->version() < '5.1') {
			if($tablestatus['Auto_increment'] <> '') {
				$temppos = strpos($tabledump, ',');
				$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
			}
			if($tablestatus['Engine'] == 'MEMORY') $tabledump = str_replace('TYPE=MEMORY','TYPE=HEAP',$tabledump);
		}
	}
	// 之前发现的末尾无[;]上面已经修正；万一其它情况下,还出现这个问题,这里再检查一次。
	if(substr($tabledump,-3)!=";\n\n"){
		$tabledump = $tabledump.";\n\n"; //substr($tabledump,strlen($tabledump)-3).";\n\n";	
	}

	$tabledumped = 0;
	$numrows = $offset;
	$firstfield = $tablefields[0];

	while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {//批次处理
		if($firstfield['Extra'] == 'auto_increment') {
			$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
		} else {
			$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
		}
		$tabledumped = 1;
		$rows = $db->query($selectsql);
		$numfields = $db->num_fields($rows);

		$numrows = $db->num_rows($rows);
		while($row = $db->fetch_row($rows)){//记录处理
			$comma = $t = '';
			for($i = 0; $i < $numfields; $i++){
				$t .= $comma.($usehex && !empty($row[$i]) && (in_str('char',$tablefields[$i]['Type']) || in_str('text',$tablefields[$i]['Type'])) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
				$comma = ',';
			}
			if(strlen($t) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000){
				if($firstfield['Extra'] == 'auto_increment'){
					$startfrom = $row[0];
				}else{
					$startfrom++;
				}
				$tabledump .= "INSERT INTO $table VALUES ($t);\n";
			}else{
				$complete = FALSE;
				break 2;
			}
		}
	}
	$startrow = $startfrom;
	$tabledump .= "\n";
	return $tabledump;
}
function syntablestruct($sql, $version, $dbcharset) {
	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) return $sql;
	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;
	if($sqlversion === $version) return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);
	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}
?>