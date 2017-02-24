<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
include_once M_ROOT."include/database.fun.php";
aheader();
if($re = $curuser->NoBackFunc('database')) cls_message::show($re);
$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';
if(!($backupdir = $db->result_one("SELECT value FROM {$tblprefix}mconfigs WHERE varname='backupdir'"))) {
	$backupdir = cls_string::Random(6);
	$db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value) values ('backupdir','$backupdir')");
}
$backupdir = 'backup_'.$backupdir;
mmkdir(M_ROOT.'dynamic/'.$backupdir);

if($action == 'dbexport'){
	if(!submitcheck('bdbexport')){
		backnav('data','dbbackup');

		$dbtables = array();
		$query = $db->query("SHOW TABLES FROM `$dbname`");
		while($dbtable = $db->fetch_row($query)){//如果有外来表，会出现什么情况?
			$dbtable[0] = preg_replace("/^".$tblprefix."(.*?)/s","\\1",$dbtable[0]);
			$dbtables[] = $dbtable[0];
		}
		$num = 3;
		tabheader('选择数据表'.'<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)">全选','dbexport','?entry=database&action=dbexport',2 * $num);
		$i = 0;
		foreach($dbtables as $dbtable){
			if(!($i % $num)) echo "<tr class=\"txt\">";
			echo "<td class=\"txtC w5B\"><input class=\"checkbox\" type=\"checkbox\" name=\"tables[]\" value=\"$dbtable\"></td>\n".
			"<td class=\"txtL w30B\">$dbtable</td>\n";
			$i ++;
			if(!($i % $num)) echo "</tr>\n";
		}
		if($i % $num){
			while($i % $num){
				echo "<td class=\"txtL w5B\"></td>\n".
					"<td class=\"txtL w30B\"></td>\n";
				$i ++;
			}
			echo "</tr>\n";
		}
		tabfooter();
		
		$sqlcompatarr = array('0' => '默认','MYSQL40' => 'MySQL 3.23/4.0.x','MYSQL41' => 'MySQL 4.1.x/5.x');
		$sqlcharsetarr = array('0' => '默认','gbk' => 'GBK','utf8' => 'UTF-8');
		tabheader('备份参数设置');
		trbasic('备份分卷大小(KB)','sizelimit','2048');
		trbasic('备份文件名(不需扩展名)','filename',date('ymd').'_'.cls_string::Random(6));
		trbasic('建表语句格式','sqlcompat',makeoption($sqlcompatarr),'select');
		trbasic('强制字符集','sqlcharset',makeoption($sqlcharsetarr),'select');
		trbasic('十六进制方式','usehex','0','radio');
		tabfooter('bdbexport','备份');
		a_guide('dbexport');
	}else{
		(!$filename || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $filename)) && cls_message::show('文件名称不合法','?entry=database&action=dbexport');
		(empty($tables) && empty($tablestr)) && cls_message::show('请选择要备份的数据表','?entry=database&action=dbexport');
		
		if(empty($tables)){
			if(strpos($tablestr,'_tablestr.bktxt')>0){ 
				// 选的表太多，GET参数太长，用.bktxt文件保存；注意为安全需要不要直接用.txt后缀；
				$tmpdir = M_ROOT.'./dynamic/'.$backupdir.'/';
				$tmpfile = "{$filename}_tablestr.bktxt";
				$tablestr = file_get_contents($tmpdir.$tmpfile);
			}
			$tables = array_filter(explode(',',$tablestr));
		}else{
			$tablestr = implode(',',$tables);
		}

		$db->query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

		$volume = empty($volume) ? 1 : (intval($volume) + 1);
		$idstring = '# DatafileID: '.base64_encode("$timestamp,08CMS,$cms_version,$volume")."\n";

		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '',$mcharset);
		$setnames = ($sqlcharset && $db->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if($db->version() > '4.1') {
			if($sqlcharset) {
				$db->query("SET NAMES '".$sqlcharset."';\n\n");
			}
			if($sqlcompat == 'MYSQL40') {
				$db->query("SET SQL_MODE='MYSQL40'");
			} elseif($sqlcompat == 'MYSQL41') {
				$db->query("SET SQL_MODE=''");
			}
		}

		$backupfilename = './dynamic/'.$backupdir.'/'.str_replace(array('/', '\\', '.'), '', $filename);
		$sqldump = '';
		$tableid = empty($tableid) ? 0 : intval($tableid);
		$startfrom = empty($startfrom) ? 0 : intval($startfrom);
		$complete = TRUE;
		for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $sizelimit * 1000; $tableid++){
			$sqldump .= sqldumptable($tblprefix.$tables[$tableid], $startfrom, strlen($sqldump));
			if($complete) {//单个数据表的完成标记
				$startfrom = 0;
			}
		}
		$dumpfile = $backupfilename."-%s".'.sql';
		!$complete && $tableid --;//数据表分割在两个卷的情况
		if(trim($sqldump)){
			$sqldump = "$idstring".
				"# <?exit();?>\n".
				"# 08cms Multi-Volume Data Dump Vol.$volume\n".
				"# Version: 08cms $cms_version\n".
				"# Date: ".date("Y-m-d",$timestamp)."\n".
				"# Made By: ".$curuser->info['mname']."\n".
				"# ----------------------------------------------\n".
				"# 08cms Home: \n".
				"# ----------------------------------------------\n\n\n".
				"$setnames".
				$sqldump;
			$dumpfilename = sprintf($dumpfile, $volume);
			@$fp = fopen($dumpfilename, 'wb');
			@flock($fp, 2);
			if(@!fwrite($fp, $sqldump)) {
				@fclose($fp);
				cls_message::show('数据表导出失败','?entry=database&action=dbexport');
			} else {
				fclose($fp);
				unset($sqldump);
				$parastr = "&bdbexport=1";
				$parastr .= "&startfrom=".$startrow;
				foreach(array('filename','sizelimit','volume','tableid','sqlcompat','sqlcharset','usehex','tablestr') as $k){
					if($k=='tablestr' && strlen($tablestr)>1500 && strpos($tablestr,'_tablestr.bktxt')<=0){
						// 选的表太多，GET参数太长，用.bktxt文件保存；注意为安全需要不要直接用.txt后缀；
						$tmpdir = M_ROOT.'./dynamic/'.$backupdir.'/';
						$tmpfile = "{$filename}_tablestr.bktxt";	
						file_put_contents($tmpdir.$tmpfile,$tablestr);
						$tablestr = $tmpfile;
					}
					$parastr .= "&$k=".$$k;
				}
				cls_message::show('数据库备份中...',"?entry=database&action=dbexport$parastr",count($tables),$tableid,$volume);
			}
		}
		adminlog('数据库备份');
		cls_message::show('数据库备份完成','?entry=database&action=dbexport');
	}
}
elseif($action == 'dbimport'){
	if(!submitcheck('bdbimport') && !submitcheck('bbddelete')){
		backnav('data','dbimport');
		
		$expfiles = array();
		if(is_dir(M_ROOT.'dynamic/'.$backupdir)){
			$expfiles = glob(M_ROOT.'dynamic/'.$backupdir.'/*.sql');
		}
		$itemstr = '';
		foreach($expfiles as $k => $expfile){
			$infos = array();
			$fp = fopen($expfile,'rb');
			$identify = explode(',', base64_decode(preg_replace("/^# DatafileID:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
			fclose ($fp);
			$infos['filename'] = basename($expfile);
			$infos['createdate'] = date("$dateformat $timeformat",@filemtime($expfile));
			$infos['filesize'] = ceil(@filesize($expfile) / 1024);
			$infos['cmsname'] = empty($identify[1]) ? '' : $identify[1];
			$infos['version'] = empty($identify[2]) ? '' : $identify[2];
			$infos['volume'] = empty($identify[3]) ? '' : $identify[3];
			$infos['download'] = "<a href=\"?entry=database&action=download&filename=$infos[filename]\">下载</a>";
			$infos['import'] = ($infos['volume'] == '1' && $infos['cmsname'] == '08CMS') ? "<a href=\"?entry=database&action=dbimport&bdbimport=1&filename=$infos[filename]\">恢复</a>" : "-";
			$itemstr .= "<tr class=\"txt\"><td class=\"txtC\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$infos[filename]]\" value=\"$infos[filename]\" onclick=\"deltip()\">\n".
				"<td class=\"txtL\"><a href=\"".$cms_abs."dynamic/$backupdir/$infos[filename]\">$infos[filename]</a></td>\n".
				"<td class=\"txtC\">$infos[version]</td>\n".
				"<td class=\"txtC\">$infos[volume]</td>\n".
				"<td class=\"txtC\">$infos[filesize]</td>\n".
				"<td class=\"txtC\">$infos[createdate]</td>\n".
				"<td class=\"txtC\">$infos[download]</td>\n".
				"<td class=\"txtC\">$infos[import]</td></tr>\n";
		}
		tabheader('备份文件列表','dbimport','?entry=database&action=dbimport',8);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" class=\"category\" onclick=\"deltip(this,0,checkall,this.form)\">",'备份文件名','版本','分卷','大小(K)','备份时间','下载','恢复'));
		echo $itemstr;
		tabfooter('bbddelete','删除');
		a_guide('dbimport');
		
	}elseif(submitcheck('bbddelete')){
		empty($selectid) && cls_message::show('请选择备份文件','?entry=database&action=dbimport');        
        $file = _08_FilesystemFile::getInstance();
		foreach($selectid as $filename){
			$file->delFile(M_ROOT.'dynamic/'.$backupdir.'/'.$filename);
		}
		adminlog('删除数据库备份文件');
		cls_message::show('备份文件删除成功','?entry=database&action=dbimport');
	}elseif(submitcheck('bdbimport')){
		empty($filename) && cls_message::show('请选择备份文件','?entry=database&action=dbimport');
		$volume = empty($volume) ? 1 : intval($volume);
		$datafile = M_ROOT.'dynamic/'.$backupdir.'/'.$filename;
		$sqldump = '';
		if(@$fp = fopen($datafile, 'rb')){
			$dumpinfo = fgets($fp, 256);
			$dumpinfo = explode(',', base64_decode(preg_replace("/^# DatafileID:\s*(\w+).*/s", "\\1", $dumpinfo)));
			if(($dumpinfo[1] == '08CMS') && ($dumpinfo[3] == $volume)){
				$sqldump = fread($fp, filesize($datafile));
			}
			fclose($fp);
		}
		if(!empty($sqldump)){
			$sqlquery = splitsql($sqldump);
			unset($sqldump);
			
			foreach($sqlquery as $sql) {
				$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);
				if($sql != '') {
					$db->query($sql, 'SILENT' , true);
					if(($sqlerror = $db->error()) && $db->errno() != 1062) {
						$db->halt('MySQL Query Error', $sql);
					}
				}
			}
		}
				
		$filename_next = preg_replace("/-($volume)(\..+)$/","-".($volume + 1)."\\2",$filename);
		if(is_file(M_ROOT.'dynamic/'.$backupdir.'/'.$filename_next)){
			$volume ++;
#			<a href=\"?entry=database&action=dbimport\">中上操作</a>
			cls_message::show('正在恢复第 ' . ($volume - 1) . " 分卷数据...","?entry=database&action=dbimport&bdbimport=1&volume=$volume&filename=$filename_next");
		}else{
			adminlog('恢复数据库备份');
			cls_CacheFile::ReBuild();
			cls_message::show('数据库恢复数据完成。');
		}
	}
}elseif($action == 'dboptimize'){
	if(!submitcheck('bdboptimize') && !submitcheck('bdbrepair')){
		backnav('data','dboptimize');
		
		$dbtables = array();
		$query = $db->query("SHOW TABLES FROM `$dbname`");
		while($dbtable = $db->fetch_row($query)){
			$dbtable[0] = preg_replace("/^".$tblprefix."(.*?)/s","\\1",$dbtable[0]);
			$dbtables[] = $dbtable[0];
		}

		$num = 3;
		tabheader('选择数据表'.'<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)">全选','dbexport','?entry=database&action=dboptimize',2 * $num);
		$i = 0;
		foreach($dbtables as $dbtable){
			if(!($i % $num)){
				echo "<tr class=\"txt\">";
			}
			echo "<td class=\"txtC w5B\"><input class=\"checkbox\" type=\"checkbox\" name=\"tables[]\" value=\"$dbtable\"></td>\n".
			"<td class=\"txtL w30B\">$dbtable</td>\n";
			$i ++;
			if(!($i % $num)){
				echo "</tr>\n";
			}
		}
		if($i % $num){
			while($i % $num){
				echo "<td class=\"txtL w5B\"></td>\n".
					"<td class=\"txtL w30B\"></td>\n";
				$i ++;
			}
			echo "</tr>\n";
		}
		tabfooter();
		echo "<input class=\"button\" type=\"submit\" name=\"bdboptimize\" value=\"优化\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<input class=\"button\" type=\"submit\" name=\"bdbrepair\" value=\"修复\">";
		a_guide('dboptimize');
	}else{
		empty($tables) && cls_message::show('请选择数据表','?entry=database&action=dboptimize');
		$dealstr = submitcheck('bdboptimize') ? 'OPTIMIZE' : 'REPAIR';
		$tablestr = '';
		foreach($tables as $table){
			$tablestr .= ($tablestr ? ',' : '').$tblprefix.$table;
		}
		$tablestr && $db->query("$dealstr TABLE $tablestr");
		adminlog(submitcheck('bdboptimize') ? '数据表优化' : '数据表修复');
		cls_message::show('数据表操作成功','?entry=database&action=dboptimize');
	}
}elseif($action == 'dbsql'){
	if(!submitcheck('bdbsql')){
		backnav('data','dbsql');
		
		tabheader('运行SQL代码','dbsql','?entry=database&action=dbsql');
		echo "<tr class=\"txt\"><td class=\"txtL w25B\">输入SQL代码内容</td><td class=\"txtL\"><textarea rows=\"15\" name=\"sqlcode\" cols=\"100\"></textarea></td></tr>";
		tabfooter('bdbsql');
		a_guide('dbsql');
	}else{
		empty($sqlcode) && cls_message::show('请输入SQL语句','?entry=database&action=dbsql');
		$sqlquery = splitsql(str_replace(array(' cms_', ' {tblprefix}', ' `cms_'), array(' '.$tblprefix, ' '.$tblprefix, ' `'.$tblprefix), $sqlcode));
		$affected_rows = 0;
		foreach($sqlquery as $sql){
			if(trim($sql) != '') {
				$db->query(stripslashes($sql),'SILENT');
				if($sqlerror = $db->error()){
					break;
				}else{
					$affected_rows += intval($db->affected_rows());
				}
			}
		}
		adminlog('运行SQL代码');
		cls_message::show("代码运行完成，涉及{$affected_rows}条记录。",'?entry=database&action=dbsql');
	}
}
elseif($action == 'download' && $filename){
	adminlog('下载数据库备份文件。');
	_08_FilesystemFile::filterFileParam($filename);
	cls_atm::Down(M_ROOT.'dynamic/'.$backupdir.'/'.$filename);
}
?>