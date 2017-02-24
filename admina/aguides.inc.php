<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
$page = isset($page) ? $page : 1;
$oflag = empty($oflag) ? 'cname' : $oflag;
$page = !empty($page) ? max(1, intval($page)) : 1;
submitcheck('bfilter') && $page = 1;
empty($keyword) && $keyword = '';
$action = empty($action) ? 'aguidesedit' : $action;

$wheresql = $keyword ? " WHERE ename ".sqlkw($keyword) : '';

if($action == 'aguidesedit'){
	if(!submitcheck('baguideadd') && !submitcheck('baguidesedit')){
		tabheader("添加管理注释&nbsp; &nbsp; &nbsp; &nbsp; >><a href=\"?entry=aguides&action=convert\">生成其它编码</a>",'aguideadd',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
		trbasic('标识名称','aguideadd[ename]');
		trbasic('注释名称','aguideadd[cname]');
		echo "<tr><td class=\"item1\">注释内容</td><td class=\"item2\"><textarea rows=\"8\" name=\"aguideadd[content]\" cols=\"110\"></textarea></td></tr>";
		tabfooter('baguideadd','添加');
		
		echo form_str($actionid.'aguidesedit',"?entry=$entry&action=$action");
		tabheader_e();
		echo "<tr><td class=\"txt txtleft\">";
		echo '搜索关键词'."&nbsp; <input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"10\">&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();

		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}aguides$wheresql ORDER BY $oflag LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		}while(!$db->num_rows($query) && $pagetmp);
		$itemaguide = '';
		while($aguide = $db->fetch_array($query)){
			$itemaguide .= "<tr class=\"txt\"><td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$aguide[agid]]\" value=\"$aguide[agid]\">\n".
				"<td class=\"txtC\"><input type=\"text\" size=\"30\" name=\"aguidesnew[$aguide[agid]][cname]\" value=\"$aguide[cname]\"></td>\n".
				"<td class=\"txtL\">$aguide[ename]</td>\n".
				"<td class=\"txtC\">".($aguide['createdate'] ? date($dateformat,$aguide['createdate']) : '-')."</td>\n".
				"<td class=\"txtC\">".($aguide['updatedate'] ? date($dateformat,$aguide['updatedate']) : '-')."</td>\n".
				"<td class=\"txtC w80\">$aguide[mname]</td>\n".
				"<td class=\"txtC w60\"><a href=\"?entry=aguides&action=aguidedetail&agid=$aguide[agid]&page=$page&oflag=$oflag\">".'修改'."</a></td></tr>\n";
		}
		$aguidecount = $db->result_one("SELECT count(*) FROM {$tblprefix}aguides$wheresql");
		$multi = multi($aguidecount,$atpp,$page,"?entry=aguides&action=aguidesedit&oflag=$oflag");
	
		tabheader("注释管理&nbsp;&nbsp;&nbsp;<a href=\"?entry=aguides&action=aguidesedit&page=$page&oflag=cname\">名称排序</a>&nbsp;&nbsp;&nbsp;<a href=\"?entry=aguides&action=aguidesedit&page=$page&oflag=ename\">ID排序</a>",
					'aguidesedit',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag",7);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'delete','chkall')\">",'名称','注释id','添加日期','更新日期','经手人','内容'));
		echo $itemaguide;
		tabfooter();
		echo $multi;
		echo "<input class=\"button\" type=\"submit\" name=\"baguidesedit\" value=\"提交\"></form>\n";
	
	}
	elseif(submitcheck('baguideadd')){
		if(!$aguideadd['cname'] || !$aguideadd['ename']) {
			cls_message::show('数据丢失', "?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
		}
		if(preg_match("/[^a-z_A-Z0-9]+/",$aguideadd['ename'])){
			cls_message::show('请输入合法的英文标识!', "?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
		}
		if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}aguides WHERE ename='$aguideadd[ename]'")){
			cls_message::show('输入的英文标识已存在!', "?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
		}
		$db->query("INSERT INTO {$tblprefix}aguides SET 
					cname='$aguideadd[cname]',
					ename='$aguideadd[ename]',
					content='$aguideadd[content]',
					createdate='$timestamp',
					mid='$memberid',
					mname='".$curuser->info['mname']."'
					");
		updatethiscache();
		cls_message::show('注释添加成功!',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
	}
	elseif(submitcheck('baguidesedit')){
		if(isset($delete)){
			foreach($delete as $agid){
				$db->query("DELETE FROM {$tblprefix}aguides WHERE agid=$agid");
				unset($aguidesnew[$agid]);
			}
		}
		foreach($aguidesnew as $agid => $aguidenew){
			$db->query("UPDATE {$tblprefix}aguides SET 
						cname='$aguidenew[cname]',
						updatedate='$timestamp' 
						WHERE agid='$agid'");
		}
		updatethiscache();
		cls_message::show('注释修改成功!',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
	}
}elseif($action == 'aguidedetail' && $agid){
	if(!($aguide = $db->fetch_one("SELECT * FROM {$tblprefix}aguides WHERE agid=".$agid))){
		cls_message::show('请指定正确的注释!',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
	}
	if(!submitcheck('baguidedetail')){
		tabheader('编辑注释','aguidedetail',"?entry=aguides&action=aguidedetail&agid=$agid&page=$page&oflag=$oflag");
		trbasic('标识名称','',$aguide['ename'],'');
		trbasic('注释名称','aguidenew[cname]',$aguide['cname']);
		echo "<tr><td class=\"item1\">注释内容</td><td class=\"item2\"><textarea rows=\"25\" name=\"aguidenew[content]\" style='width:100%;'>".(empty($aguide['content']) ? '' : mhtmlspecialchars($aguide['content']))."</textarea></td></tr>";
		tabfooter('baguidedetail');
	}else{
		$aguidenew['cname']	= trim($aguidenew['cname']) ? trim($aguidenew['cname']) : $aguide['cname'];
		$aguidenew['content']	= trim($aguidenew['content']) ? trim($aguidenew['content']) : $aguide['content'];
		$db->query("UPDATE {$tblprefix}aguides SET 
					cname='$aguidenew[cname]',
					content='$aguidenew[content]',
					updatedate='$timestamp' 
					WHERE agid='$agid'");
		updatethiscache();
		cls_message::show('注释编辑成功!',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
	}
}elseif($action == 'convert'){
	$droot = 'P:/apache/www/mpack/aguides/';
	foreach(array('scgbk','scutf8','tcbig5','tcutf8') as $lan){
		dir_copy(M_ROOT.'dynamic/aguides',$droot.$lan,1,0);
		$icharset = $lan == 'scgbk' ? 'gbk' : ($lan == 'tcbig5' ? 'big5' : 'utf-8');
		//$agarr = findfiles($droot.$lan.'/','php');
		$agarr = glob($droot.$lan.'/*.php');
		foreach($agarr as $v){
			if($lan == 'tcutf8'){
				convert_file('gbk','big5',$v);
				convert_file('big5','utf-8',$v);
			}else convert_file('gbk',$icharset,$v);
		}
	}
	cls_message::show('编码转换成功!',"?entry=aguides&action=aguidesedit&page=$page&oflag=$oflag");
}
function updatethiscache(){
	global $db,$tblprefix;
	clear_files(M_ROOT.'./dynamic/aguides/');
	$query = $db->query("SELECT * FROM {$tblprefix}aguides ORDER BY agid");
	while($aguide = $db->fetch_array($query)){
		if(@$fp = fopen(M_ROOT.'./dynamic/aguides/'.$aguide['ename'].'.php','wb')){
			fwrite($fp,"<?php\n\$aguide = '".addcslashes($aguide['content'],'\'\\')."';\n?>");
			fclose($fp);
		}
	}
}
function clear_files($dir,$keeps = array()){
	if(!is_dir($dir)) return;
	$handle = dir($dir);
	while($entry = $handle->read()){
		if($entry != '.' && $entry != '..' && is_file($dir.'/'.$entry)){
			if(!$keeps || !in_array($entry,$keeps)) @unlink($dir.'/'.$entry);
		}
	}
	$handle->close();
}
function convert_file($scode,$tcode,$sfile=''){//gbk,big5,utf-8
	if(!$sfile || !is_file($sfile)) return;
	if(empty($scode) || empty($tcode) || $scode == $tcode) return;
	$str = file2str($sfile);
	$str = cls_string::iconv($scode,$tcode,$str);
	str2file($str,$sfile);
}
function dir_copy($source,$destination,$f = 0,$d = 0){//$f-是否复制文件夹下文件，$d是否复制搜索下级文件夹
	if(!is_dir($source)) return false;
	mmkdir($destination,0);
	if($f || $d){
		$handle = dir($source);
		while($entry = $handle->read()){
			if(($entry != ".") && ($entry != "..")){
				if(is_dir($source."/".$entry)){
					$d && dir_copy($source."/".$entry,$destination."/".$entry,$f,$d);
				}else{
					$f && copy($source."/".$entry,$destination."/".$entry);
				}
			}
		}
		$handle->close();
	}
	return true;
}

?>
