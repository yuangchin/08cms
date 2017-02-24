<?PHP

/**
* 目前只用于管理后台会员管理列表的静态功能
* 需要更合理布署，后续跟进
*/

function mstatic_doing($act,$mid){
	global $db,$tblprefix,$timestamp,$mspacedir;
	if($act=='static'){
		if($row = $db->fetch_one("SELECT mname,mspacepath as dir FROM {$tblprefix}members WHERE mid='$mid'")){
			$dir = $row['dir']; 
			$mname = $row['mname'];
			$dir = mstatic_fmtdir($dir);
			if(!$dir){ 
			   $dir = mstatic_automdir($mid,$mname); 
			   $db->query("UPDATE {$tblprefix}members SET mspacepath='$dir',msrefreshdate='$timestamp' WHERE mid='$mid'");
			}
			if(!mmkdir(M_ROOT.$mspacedir.'/'.$dir,0)) cls_message::show('静态空间目录无法生成。',M_REFERER);
			$ifile = M_ROOT.$mspacedir.'/'.$dir.'/index.php';
			if(!is_file($ifile)) str2file('<?php $mid = '.$mid.'; include dirname(dirname(__FILE__)).\'/index.php\'; ?>',$ifile);
			$message = cls_Mspace::ToStatic($mid);
			
			
			
		}
	}else{ // dynamic
		if($dir  = $db->result_one("SELECT mspacepath FROM {$tblprefix}members WHERE mid='$mid'")){
			if(!_08_FileSystemPath::CheckPathName($dir)) clear_dir(M_ROOT.$mspacedir.'/'.$dir,true);
			$db->query("UPDATE {$tblprefix}members SET mspacepath='',msrefreshdate=0 WHERE mid='$mid'");
		}
	}
}

function mstatic_fmtdir($dir){
   if($dir){ 
	   $sChr = "!\"#$%&'()*+,-./:;<=>?@[\]^`{|}~"; 
	   for($i=0;$i < strlen($sChr);$i++) { 
		 $dir = str_replace(substr($sChr,$i,1),'',$dir);
	   }
   }
   return $dir;
}

function mstatic_dodir($mid,$setdir=''){
	global $db,$tblprefix,$timestamp,$mspacedir;
	if(!$setdir) mstatic_doing('static',$mid);
	$row = $db->fetch_one("SELECT mname,mspacepath as dir FROM {$tblprefix}members WHERE mid='$mid'");
	$dir = $row['dir']; $mname = $row['mname'];
	$dir = mstatic_fmtdir($dir);
	$setdir = mstatic_fmtdir($setdir);
	if(!$dir){ 
		$dirc = $db->result_one("SELECT mid FROM {$tblprefix}members WHERE mspacepath='$setdir'");
		if($dirc){ 
			$dir = mstatic_automdir($mid,$mname);
		}else{
			$dir = $setdir;
		}
		$db->query("UPDATE {$tblprefix}members SET mspacepath='$dir',msrefreshdate='$timestamp' WHERE mid='$mid'");
		if(!mmkdir(M_ROOT.$mspacedir.'/'.$dir,0)) cls_message::show('静态空间目录无法生成。',M_REFERER);
		$ifile = M_ROOT.$mspacedir.'/'.$dir.'/index.php';
		if(!is_file($ifile)) str2file('<?php $mid = '.$mid.'; include dirname(dirname(__FILE__)).\'/index.php\'; ?>',$ifile); 
		$message = cls_Mspace::ToStatic($mid);
		echo "<br>$message";	
	}
}

function mstatic_automdir($mid,$mname,$dir=''){
	global $db,$tblprefix;
	if(!$dir){ 
	  $dir = mstatic_namedir($mname);
	  $dir = mstatic_fmtdir($dir);
	  //$pinyin = cls_string::iconv('gb2312','pinyin',$mname);
	  if(!$dir) $dir = "u$mid"; // 如果是中文,则用u+id
	}else{ // 目录已经使用,加随机字符
	  $T = strtolower('0123456789ABCDEFGHJKMNPQRSTUVWXY'); //32
	  $rnd = ''; for($i=0;$i<3;$i++) $rnd .= $T{mt_rand(0,31)};
	  $dir .= "_".$rnd; 
	}
	$dirc = $db->result_one("SELECT mid FROM {$tblprefix}members WHERE mspacepath='$dir'");
	if($dirc) return mstatic_automdir($mid,$mname,$dir);
	else return $dir;
}

// 使用mname作目录(中文除外)
function mstatic_namedir($str){
  global $mcharset;
  $step = ($mcharset=='utf-8')?3:2;
  $s = ""; $p = 0;
  $len = strlen($str);
  for($i=0;$i < $len;$i++){   
	  $ch = substr($str,$p,1);
	  $n = ord($ch);
	  if($n<128){ 
	  	$s .= $ch;
		$p++; 
	  }else{
	  	//$s .= ",$ch";  
	  	$p += $step; 
	  }
	  if($p>=$len) break;
  }   
  return $s; 
}
