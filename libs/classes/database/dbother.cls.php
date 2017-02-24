<?php
/**
* 有关数据库及查询相关的一些方法汇集
* 
*/
class cls_DbOther{
	
	// 一个表的注释(数据库词典用) 优先:afields(架构) > dbfields缓存 > nowfields > init表资料 > $excom
	public static function dictComment($tab=''){
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$nowfields = $db->getTableColumns($tblprefix.$tab, 0); 
		static $dbfields,$dafields; // 多次使用提高效率? cls_cache::Read已经缓存?!
		if(empty($dbfields)) $dbfields = cls_cache::Read('dbfields');
		if(empty($dafields)){
			$query = $db->query("SELECT * FROM {$tblprefix}afields");
			while($r = $db->fetch_array($query)){
				$dafields[$r['tbl']][$r['ename']] = $r['cname'];
			}
		}
		$excom = array(
			'aid'=>'文档id','cid'=>'交互id','mid'=>'会员id','mname'=>'会员名','chid'=>'文档模型id','mchid'=>'会员模型id',
			'createdate'=>'添加时间','checked'=>'是否审核','ucid'=>'交互分类','cuid'=>'交互项目',
			'tmoid'=>'接收者会员id','tomname'=>'接收者会员名','ip'=>'IP地址',
			'tocid'=>'交互对象ID','reply'=>'回复','replydate'=>'回复时间',
			'pushid'=>'推送ID','paid'=>'推送位id','color'=>'颜色','copyid'=>'副本ID','frommid'=>'内容来源id',
			'abstract'=>'摘要','thumb'=>'缩略图','content'=>'内容','author'=>'作者',
			
		);
		if(substr($tab,0,5)=='push_' || substr($tab,0,10)=='farchives_'){
			if(substr($tab,0,5)=='push_'){
				$pfcfg = cls_cache::Read('pafields',$tab);
			}else{
				$pfcfg = cls_FieldConfig::InitialFieldArray('fchannel',str_replace('farchives_','',$tab));	
			}
			foreach($pfcfg as $k1=>$r){
				$k1 = $r['ename'];
				if(isset($nowfields[$k1])){
					$t = &$nowfields[$k1];
					$t->Comment = $r['cname'];
				}
			}
		}
		foreach($nowfields as $k1=>$v){ 
			$k2 = $tab.'_'.$k1;
			$tcom = isset($dafields[$tab][$k1]) ? $dafields[$tab][$k1] : (isset($dbfields[$k2]) ? $dbfields[$k2] : $v->Comment);
			if(empty($tcom)){ //从init取资料
				if(substr($tab,0,8)=='archives'&&isset($dbfields["init_archives_$k1"])){
					$tcom = $dbfields["init_archives_$k1"];
				}elseif(substr($tab,0,7)=='coclass'&&isset($dbfields["init_coclass_$k1"])){
					$tcom = $dbfields["init_coclass_$k1"];
				}elseif(substr($tab,0,5)=='push_'&&isset($dbfields["init_push_$k1"])){
					$tcom = $dbfields["init_push_$k1"];
				}
			}
			if(empty($tcom) && isset($excom[$k1])){
				$tcom = $excom[$k1];  //从excom取资料
			}
			if($tcom != $v->Comment){ //$nowfields->$k1['Comment'] = $tcom;
				$t = &$nowfields[$k1];
				$t->Comment = $tcom;
			}
		}
		return $nowfields;
	}
	
	// 获取数据表列表, 不用$db->getTableList(), 因得不到详情
	public static function tabLists(){
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$query = $db->query("SHOW TABLE STATUS"); //SHOW TABLES FROM $dbname
		$arr = array();
		while($r=$db->fetch_array($query)) { 
			$tab = $r['Name'];
			$len = strlen($tblprefix);
			if(substr($tab,0,$len)!==$tblprefix) continue; //不是我们系统的表,不要
			$tab = substr($tab,$len);
			$arr[$tab] = $r; // Name,Engine,Rows,Data_length,Collation,Comment,Auto_increment(Max+1) 
		}
		return $arr;
	}

    /**
     * 得到指定表的字段名数组
     * 
     * @param string $tbls		数据名名称，多个表以逗号分隔
     * @param int $Total		为true时返回完整字段配置数组,否则只返回名称数组
     * @
     * @return array			返回由字段名组成的数组
     * @static
     * @since 1.0
     */ 
	public static function ColumnNames($tbls = '',$Total = false){
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		$rets = array();
		if($tbls && is_array($x = explode(',',$tbls))){
			foreach($x as $v){
				$query = $db->query("SHOW COLUMNS FROM {$tblprefix}$v",'SILENT');
				while($r = $db->fetch_array($query)){
					if($Total){
						$rets[$r['Field']] = $r;
					}else{
						$rets[] = $r['Field'];
					}
				}
			}
			if(empty($Total)) $rets = array_unique($rets);
		}
		return $rets;
	}
	
    /**
     * 通过数据表得到生成缓存所需要的原始数据数组
     * 
     * @param array $cachecfg		生成缓存的相关配置，存放在(extend_sample)dynamic/syscache/cachedos.cac.php中
     * 
     * @return array				返回生成缓存所需要的原始数据数组
     * @static
     * @since 1.0
     */ 
	public static function CacheArray($cachecfg = array()){//$cachecfg = array(tbl,key,fieldstr,where,orderby,unserialize,explode,unset,varexport,merge,)
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		$rets = array();
		if(empty($cachecfg['tbl']) || empty($cachecfg['key'])) return $rets;
		empty($cachecfg['fieldstr']) && $cachecfg['fieldstr'] = '*';
		$sqlstr = "SELECT $cachecfg[fieldstr] FROM {$tblprefix}$cachecfg[tbl]".(empty($cachecfg['where']) ? '' : " WHERE $cachecfg[where]").(empty($cachecfg['orderby']) ? '' : " ORDER BY $cachecfg[orderby]");
		$query = $db->query($sqlstr);
		while($r = $db->fetch_array($query)){
			if(!empty($cachecfg['unserialize']) && is_array($x = array_filter(explode(',',$cachecfg['unserialize'])))){
				foreach($x as $v) cls_CacheFile::ArrayAction($r,$v,'unserialize');
			}
			if(!empty($cachecfg['explode']) && is_array($x = array_filter(explode(',',$cachecfg['explode'])))){
				foreach($x as $v) cls_CacheFile::ArrayAction($r,$v,'explode');
			}
			if(!empty($cachecfg['unset']) && is_array($x = array_filter(explode(',',$cachecfg['unset'])))){
				foreach($x as $v) unset($r[$v]);
			}
			if(!empty($cachecfg['varexport']) && is_array($x = array_filter(explode(',',$cachecfg['varexport'])))){
				foreach($x as $v) cls_CacheFile::ArrayAction($r,$v,'varexport');
			}
			if(!empty($cachecfg['merge']) && is_array($x = array_filter(explode(',',$cachecfg['merge'])))){
				foreach($x as $v) cls_CacheFile::ArrayAction($r,$v,'extract');
			}
			$rets[$r[$cachecfg['key']]] = $r;
		}
		return $rets;
	}
	
    /**
     * 得到多id字串的查询字串
     * 
     * @param string $ids		多个id以逗号分隔的字串
     * @param string $idvar		查询的字段名(含别名在内)
     * 
     * @return string			SQL字串
     * @static
     * @since 1.0
     */ 
	public static function str_fromids($ids,$idvar){
		if($ids && $idvar && ($ids = array_unique(array_filter(explode(',',$ids))))){
			return " AND $idvar ".multi_str($ids);
		}else return '';
	}

    /**
     * 统一修改文档、分类、推送的各分表(含init初始表)数据结构，如添加，修改，删除字段，批量操作数据等，通常用于开发或升级系统
     * 
     * @param string $sqlstr		SQL语句，使用{TABLE}作为表名通配符，如ALTER TABLE {TABLE} DROP xxx
     * @param string $type			分表类型：archive(文档),push(推送),coclass(分类)
     * 
     * @static
     */ 
	public static function BatchAlterTable($sqlstr,$type = 'archive'){
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		if(!$sqlstr || !in_str('{TABLE}',$sqlstr)) return;
		switch($type){
			case 'archive':
				$tblarr = array('init_archives');
				$query = $db->query("SELECT stid FROM {$tblprefix}splitbls");
				while($r = $db->fetch_array($query)) $tblarr[] = 'archives'.$r['stid'];
			break;
			case 'push':
				$tblarr = array('init_push');
				$pushareas = cls_PushArea::InitialInfoArray();
				foreach($pushareas as $k => $v){
					$tblarr[] = cls_PushArea::ContentTable($k);
				}
			break;
			case 'coclass':
				$tblarr = array('init_coclass');
				$query = $db->query("SELECT coid FROM {$tblprefix}cotypes");
				while($r = $db->fetch_array($query)) $tblarr[] = 'coclass'.$r['coid'];
			break;
		}
		if(!empty($tblarr)){
			foreach($tblarr as $tbl){
				$db->query(str_replace('{TABLE}',"{$tblprefix}$tbl",$sqlstr),'SILENT');
			} 
		}
	}
	
    /**
	 * 获取IN,NOT IN子句中的IDs, 辑内管理列表等使用
	 * (注：先查询出NOT IN()里IDs, 比直接用SELECT子句，平均要快上10倍左右	)
	 * 仅限数字的ID,如aid,cid等, 如果不是数字, 请自行处理结果
     * 
     * @param string $key      IDs的来源字段,如：inid
     * @param string $from     FROM子句, 不含FROM本身,如：{$tblprefix}{$abrel['tbl']}
     * @param string $where    WHERE条件, 如：pid='{$this->A['pid']}'
	 * @param string $re       返回标记: ids-返回字串, arr-返回数组
	 
     * @return int $ids    如：121,8,5,300，无资料返回0；或：array(121,8,5,300)，无资料返回array()；
     */
	public static function SubSql_InIds($key, $from, $where, $re='ids'){
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		$sql = "SELECT DISTINCT $key AS id FROM {$tblprefix}$from".(empty($where) ? "" : " WHERE $where");
		$query = $db->query($sql); $a = array();
		while($r = $db->fetch_array($query)){ 
			$a[] = $r['id'];
		}
		if($re=='ids'){
			return empty($a) ? '0' : implode(',',$a);
		}else{
			return $a;	
		}
	}
	
    /**
     * 文档限额统计，主要用于会员中心 限额统计，也可用于其它地方统计文档
     * 
     * @param int $chid     文档模型,如：2
     * @param int $field    字段,如：refreshdate, 为空表示无字段限制
     * @param int $days     时间间隔或条件,valid-有效的,exp-到期的,0-当天,数字n-(n+1)天内的,[=1]-直接加条件(无中括号),
	                        // !!! 如果$days里面含有url等传递的不确定变量，请自行预先处理。 !!! 
     * @param int $mid      会员ID,空表示当前会员,-1表示不区分会员(所有会员),
     * 
     * @static
     */
	public static function ArcLimitCount($chid, $field='refreshdate', $days='0', $mid=''){
		global $timestamp;
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		$curuser = cls_UserMain::CurUser();
		$mid = empty($mid) ? $curuser->info['mid'] : $mid;
		$chid = intval($chid);
		$field = cls_string::ParamFormat($field);
		$sql = ""; 
		$sql_ref = ($field=='refreshdate' ? "initdate<>refreshdate" : " "); //刷新额外条件
		if($mid==-1) $mid = ''; //-1表示不区分会员(所有)
		if($days==='valid'){ // 有效的
			$sqlt = " AND (enddate=0 OR enddate>'$timestamp') ";
		}elseif($days==='exp'){ // 到期的
			$sqlt = " AND (enddate>0 AND enddate<'$timestamp') ";
		}elseif(empty($days)){ // 表示当天, mktime(0,0,0)
			$sqlt = " AND $field>'".(mktime(0,0,0))."' $sql_ref";
		}elseif(intval($days)>0){ // ($days+1)天内的, mktime(0,0,0)
			$sqlt = " AND $field>'".(mktime(0,0,0)-$days*86400)."' $sql_ref";
		}elseif(strstr($days,'=') || strstr($days,'>') || strstr($days,'<')){ // 直接加条件,这里可自由组任何条件...
			$sqlt = " AND $field $days ";
		}else{ //
			$sqlt = " ";	
		}
		$sql .= " SELECT COUNT(*) FROM {$tblprefix}".atbl($chid)." ";
		$sql .= " WHERE ".(empty($mid) ? "1=1" : "mid='$mid'")." ";
		$field && $sql .= $sqlt;
		$re = $db->result_one($sql); //echo "$mid,$sql<br>";
		return empty($re) ? 0 : $re;
	}
	
    /**
     * 地图中参照物周边的查询子串
     * 
     * @param int $x,$y     	参照目标的坐标
     * @param int $diff    		指定范围，单位为km或度
     * @param int $mode     	计算模式，0按度数，1按实际距离//???
     * @param string $fname		查询的字段名（包含表别名前缀）
     * 
     * @return string			SQL字串
     * 
     * @static
     */
	public static function MapSql($x,$y,$diff,$mode,$fname){		
		if(!$diff) return '';
		$mode = empty($mode) ? 0 : 1;
		$x = floatval($x);
		$y = floatval($y);
		$dfx = $dfy = $diff = abs(floatval($diff));
		if($mode == 1){
			$radius = 6378.137;//km
			$dfx = $diff / (2 * $radius * M_PI) * 360;
			$dfy = $diff / (2 * $radius * M_PI * cos(deg2rad($x))) * 360;
		}
        // if($dfx>30 || $dfy>60) return ''; //如果跨度太大,超过最大值的1/3,则认为是整个地球,就清空这个条件 (???)
        // 纬度 <-90 或 >90 未考虑(在目前常规地图上,一般也不会定位到这个附近的点为中点)
		$re = $fname.'_0>='.($x - $dfx).' AND '.$fname.'_0<='.($x + $dfx);
        // 经度(及处理边界)
        $dmin = $y - $dfy; $dmax = $y + $dfy; //*
        if($dmin<-180){ 
            $re .= " AND ( ({$fname}_1>=".($y - $dfy + 360)." AND {$fname}_1<=180) OR ({$fname}_1>=-180 AND {$fname}_1<=".($y + $dfy).") )";
        }elseif($dmax>180){
            $re .= " AND ( ({$fname}_1>=".($y - $dfy)." AND {$fname}_1<=180) OR ({$fname}_1>=-180 AND {$fname}_1<=".($y + $dfy - 360).") )"; 
        }else{
            $re .= " AND {$fname}_1>=".($y - $dfy)." AND {$fname}_1<=".($y + $dfy)."";
        }//*/
		// 下面生成如：map_0>=-0.5 AND map_0<=1.5 AND map_1>=179.5 AND map_1<=-178.5 为错误！
        #$re .= ' AND '.$fname.'_1>='.($y - $dfy < -180 ? $y - $dfy + 360 : $y - $dfy).' AND '.$fname.'_1<='.($y + $dfy > 180 ? $y + $dfy - 360 : $y + $dfy);
		return $re;
	}
		
	public static function DropField($tbl,$ename,$datatype){
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		if(!$tbl || !$ename || !$datatype) return;
		$db->query("ALTER TABLE {$tblprefix}$tbl DROP $ename",'SILENT'); 
		if($datatype == 'map'){
			$db->query("ALTER TABLE {$tblprefix}$tbl DROP {$ename}_0",'SILENT'); 
			$db->query("ALTER TABLE {$tblprefix}$tbl DROP {$ename}_1",'SILENT'); 
		}
		return;
	}
	
	public static function AlterFieldSelectMode($nmode,$omode,$fname,$tbl){//需要用于类系，所以还是要考虑对所有文档主表进行操作
		$db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		if(!$fname || !$tbl || $nmode == $omode) return false;
		if($nmode xor $omode){
			$ntbls = m_tblarr($tbl);
			foreach($ntbls as $tbl){
				$omode && $db->query("UPDATE {$tblprefix}$tbl SET $fname= SUBSTRING_INDEX(TRIM(LEADING ',' FROM $fname),',',1) WHERE $fname<>''",'SILENT');
				$db->query("ALTER TABLE {$tblprefix}$tbl CHANGE $fname $fname ".($nmode ? "varchar(255) NOT NULL default ''" : "smallint(6) unsigned NOT NULL default 0"),'SILENT');
				if($nmode){
					$db->query("UPDATE {$tblprefix}$tbl SET $fname= '' WHERE $fname='0'",'SILENT');
					$db->query("UPDATE {$tblprefix}$tbl SET $fname= CONCAT(',',$fname,',') WHERE $fname<>''",'SILENT');
				}
			}
		}
		return true;
	}
	public static function AddField($tbl,$ename,$datatype,$str){
		global $db,$tblprefix;
		$db->query("ALTER TABLE {$tblprefix}$tbl ADD $ename $str",'SILENT');
		if($datatype == 'map'){
			$db->query("ALTER TABLE {$tblprefix}$tbl ADD {$ename}_0 double NOT NULL default '0'",'SILENT');
			$db->query("ALTER TABLE {$tblprefix}$tbl ADD {$ename}_1 double NOT NULL default '0'",'SILENT');
		}
		return;
	}
	
}
