<?PHP
/**
 * 系统升级所用的通用方法，取代之前update.fun.php的作用
 *
 */
class _08_Update{
	public static function Cache($CacheName = ''){
		return cls_cache::cacRead($CacheName,_08_CACHE_PATH.'updatedata',1);
	}
	
	public static function SqlFile($Filename = ''){
		if(!$Filename) return '';
		$file = _08_CACHE_PATH.'updatedata'.DS.$Filename;
		if(is_file($file)){
			$Re = file2str($file);
		}else $Re = '';
		return $Re;
	}
	
	public static function Header(){
		aheader();
	}
	
	public static function Title($title = ''){
		echo "<title>$title</title>";
	}
	
	public static function Url($args = array()){
		$re = '';
		foreach($args as $k => $v) $v && $re .= "&$k=$v";
		return $re ? '?'.substr($re,1) : '';
	}
	
	public static function SetBaseParam($RepalceArray = array()){
		if(!$RepalceArray) return;
		$content = file2str(M_ROOT.'base.inc.php');
		foreach($RepalceArray as $k => $v){
			$content = preg_replace("/$k/is", $v, $content);
		}
		str2file($content,M_ROOT.'base.inc.php');
	}
	
	public static function SetMconfig($key = '',$val = '',$cftype = ''){
		global $db,$tblprefix;
		$db->query("REPLACE INTO {$tblprefix}mconfigs (varname,value,cftype) VALUES ('$key','$val','$cftype')",'SILENT');
	}
	
	public static function runquery($stxt){
		global $tblprefix,$db;
		$stxt = str_replace("\r", "\n", str_replace(' {$tblprefix}', ' '.$tblprefix, $stxt));
		$ta = explode(";\n", trim($stxt));
		foreach($ta as $a){
			$sql = '';
			$s = explode("\n", trim($a));
			foreach($s as $k){
				if(!$k || $k[0] == '#' || $k[0].$k[1] == '--') continue;
				$sql .= $k;
			}
			$sql = trim($sql);
			if($sql) {
				if(substr($sql, 0, 12) == 'CREATE TABLE') {
					$db->query(self::CreateTableString($sql,cls_env::GetG('dbcharset')),'',true);
				} else {
					$db->query($sql,'SILENT',true);
				}
			}
		}
	}
	
	private static function CreateTableString($sql, $dbcharset){
		if(mysql_get_server_info() > '4.1'){
			if(preg_match("/\s*TYPE\s*=\s*([a-z]+?)/isU",$sql,$matches)){
				$Typestr = trim($matches[0]);
				$Type = $matches[1];
				if(strcmp($Type,'MYISAM') && strcmp($Type,'HEAP')) $Type = 'MYISAM';
				$sql = str_replace($Typestr,"ENGINE=$Type DEFAULT CHARSET=$dbcharset", $sql);
			}
		}
		return $sql;
	}
	
	
	
	
}