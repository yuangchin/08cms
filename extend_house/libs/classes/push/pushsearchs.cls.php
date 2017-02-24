<?php
class cls_pushsearchs extends cls_pushsearchsbase{
	

	// 文档模型下aids
	static function get_subAids($chids,$whr,$pfield){
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$cha = explode(',',$chids); $aids = '';
		if(!$whr) return '';
		foreach($cha as $chid){ 
			$tbl = atbl($chid);
			if(!$tbl) continue;
			$sql = "SELECT aid FROM {$tblprefix}$tbl a WHERE $whr"; 
			$query = $db->query($sql);
			while($row = $db->fetch_array($query)){    
				$aids .= ','.$row['aid'];
			}
		}
		$aids = empty($aids) ? "-1" : substr($aids,1); 
		return "$pfield IN($aids)";
	}
	
}
