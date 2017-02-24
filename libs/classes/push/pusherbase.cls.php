<?php
/*
** 推送执行类：根据推送规则，从来源内容表推送(更新)信息到推送内容表
** 基于单个字段的操作都未执行updatedb操作，updatedb需要另行执行
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）。
** 
*/ 

class cls_pusherbase{
	protected static $area = array();//推送位配置
	protected static $fields = array();//推送字段配置
	protected static $updates = array();//数据表更新的暂存值，针对具体的某次推送

	# 需要展示一条推送位时，对资料数组中的数据展示需求处理(如url及附件url处理)
	public static function ViewOneInfo($info = array()){
		if(empty($info)) return $info;
		if(!empty($info['url'])){
			$info['url'] = cls_url::view_url($info['url'],false); # 补全url
		} 
		cls_url::arr_tag2atm($info,'pa');
		return $info;
	}

	# 初始化推送位配置
	public static function SetArea($paid){
		if(!self::$area || self::$area['paid'] != $paid){
			if(!($pusharea = cls_PushArea::Config($paid))) return false;
			self::$area = $pusharea;
		}
		return true;
	}
	
	//将指定来源信息推送到指定推送位
	//loadtype : 0.手动推送, 11.手动添加, 21.自动推送
	public static function push($info,$paid,$loadtype=0){
		if(!cls_pusher::SetArea($paid)) return false;
		if(!cls_pusher::_PushCheck($info)) return false;
		if(!cls_pusher::_SetFields() || !self::$fields[$paid]) return false;
		$info = cls_pusher::_DealSourceInfo($info);
		foreach(self::$fields[$paid] as $k => $v){//刷新与推送对字段配置的要求会不一样，是否需要处理?????
			if(cls_pusher::_push_field($v,$info)){//捕捉到错误信息
				cls_pusher::rollback();
				return false;
			}
		}
		cls_pusher::setEnddate($info);
		if($loadtype) cls_pusher::onedbfield('loadtype',$loadtype);
		cls_pusher::updatedb(0,cls_pusher::_GetFromid($info));
		return true;
	}
	
	//将某条推荐信息从来源更新
	public static function Refresh($pushid,$paid){
		if(!cls_pusher::SetArea($paid)) return false;
		if(!($push = cls_pusher::oneinfo($pushid,$paid))) return false;
		if($push['norefresh']) return false;
		if(!cls_pusher::_SetFields() || !self::$fields[$paid]) return false;
		if(!($fromid = (int)$push['fromid']) || !($info = cls_pusher::_OneFromInfo($fromid,$paid))) return false;
		$info = cls_pusher::_DealSourceInfo($info);
		foreach(self::$fields[$paid] as $k => $v){
			if(cls_pusher::_push_field($v,$info,1)) continue;//捕捉到错误信息
		}
		cls_pusher::setEnddate($info);
		cls_pusher::updatedb($pushid);
		return true;
	}
	
	//指定某个推荐位一键更新来源
	//返回更新条数
	public static function RefreshPaid($paid){
		global $db,$tblprefix,$timestamp;
		if(!cls_pusher::SetArea($paid)) return 0;
		$query = $db->query("SELECT pushid FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE fromid<>0 AND checked=1 AND startdate<'$timestamp' AND (enddate='0' OR enddate>'$timestamp') AND norefresh=0");
		$i = 0;
		while($r = $db->fetch_array($query)){
			if(cls_pusher::Refresh($r['pushid'],$paid)) $i++;
		}
		return $i;
	}
	
	//设置到期日期:
	public static function setEnddate($info){
		$from = empty(self::$area['enddate_from']) ? 0 : self::$area['enddate_from'];
		if(!empty($info[$from]) && is_numeric($info[$from])){ //&& $info[$from]>TIMESTAMP
			cls_pusher::onedbfield('enddate',$info[$from]); 
		}
	}
	
	public static function HaveNewToday($paid){
		global $db,$tblprefix;
		if(!cls_pusher::SetArea($paid)) return false;
		$num = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE createdate>'".(mktime(0,0,0))."'",0,'SILENT');
		return $num ? true : false;
	}
	
	public static function AddCopy($pushid,$toclassid,$paid){//将推送信息在指定的分类中增加共享副本
		global $db,$tblprefix;
		if(!cls_pusher::SetArea($paid)) return '未指定推送位';
		if(!($toclassid = empty($toclassid) ? 0 : max(0,intval($toclassid)))) return '请指定需要共享到的分类';//注意：此处未再检证分类id
		if(!($copyspace = self::$area['copyspace'])) return '未指定共享分类';
		if(!($push = cls_pusher::oneinfo($pushid,$paid))) return '请指定正确的推送信息';
		$copyid = $push['copyid'];
		$classid = 'classid'.self::$area['copyspace'];
		if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE copyid='$copyid' AND $classid='$toclassid'")) return '分类中已有当前推送的共享';
		$sqlstr = "copyid='$copyid',$classid='$toclassid'";
		foreach($push as $k => $v){
			if(!in_array($k,array('pushid',$classid,'copyid'))) $sqlstr .= ",$k='".addslashes($v)."'";
		}
		$db->query("INSERT INTO {$tblprefix}".cls_pusher::tbl($paid)." SET $sqlstr");
	}
	
	public static function DelCopy($pushid,$toclassid,$paid){//将某个副本删除
		global $db,$tblprefix;
		if(!cls_pusher::SetArea($paid)) return '未指定推送位';
		if(!($toclassid = empty($toclassid) ? 0 : max(0,intval($toclassid)))) return '请指定需要共享到的分类';//注意：此处未再检证分类id
		if(!($copyspace = self::$area['copyspace'])) return '未指定共享分类';
		if(!($push = cls_pusher::oneinfo($pushid,$paid))) return '请指定正确的推送信息';
		$copyid = $push['copyid'];
		$classid = 'classid'.self::$area['copyspace'];
		$db->query("DELETE FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE copyid='$copyid' AND $classid='$toclassid' AND pushid<>'$pushid'");
	}
	
	public static function paidsarr($type,$typeid = 0,$smallid = 0){//取得推荐位列表数组
		$pushareas = cls_PushArea::Config();
		$re = array();
		foreach($pushareas as $k =>$v){
			if(($type == $v['sourcetype']) && ($typeid == $v['sourceid'])){
				if($smallid && ($type == 'archives') && $ids = cls_pusher::_AllSmallIds($k)){
					if(!in_array($smallid,$ids)) continue;
				}
				$re[$k] = $v['cname'];
			}
		}
		return $re;
	}
	
	//来源是否需要模型字段
	public static function SourceNeedAdv($paid){
		if(!cls_pusher::SetArea($paid)) return false;
		return @self::$area['sourceadv'] ? true : false;
	}
	
	//编辑单个字段数据，返回意外错误信息
	//可以传入修正后的数段配置$field
	public static function onefield($field = array(),$nvalue = '',$ovalue = ''){
		$c_upload = cls_upload::OneInstance();
		if(!cls_pusher::_FieldCfgOk($field)) return '错误字段'.@$field['cname'];
		$a_field = new cls_field;
		$a_field->init($field,$ovalue);
		$nvalue = $a_field->DealByValue($nvalue,'');//不输出错误信息
		if($a_field->error) return $a_field->error;//捕捉出错信息
		unset($a_field);
		if($field['ename'] == 'url'){ # 将url做相对url处理
			$nvalue = cls_url::save_url($nvalue);
		}
		cls_pusher::onedbfield($field['ename'],$nvalue);
		if($arr = multi_val_arr($nvalue,$field)) foreach($arr as $x => $y) cls_pusher::onedbfield($field['ename'].'_'.$x,$y);
		if($field['ename'] == 'subject'){
			cls_pusher::_SetColor();
		}
		return;
	}
	
	//更新一个数据表字段
	public static function onedbfield($ename,$nvalue,$ovalue = '__new'){//ovalue是数据库中直接读出的未addslash的值
		if($ovalue == '__new' || $ovalue != stripslashes($nvalue)){//过滤掉无效的字段
			self::$updates[$ename] = $nvalue;
			return true;
		}else return false;
	}
	
	//格式化排序值
	public static function orderformat($value,$paid,$ename = 'vieworder'){
		if(!cls_pusher::SetArea($paid)) return $value;
		if(!$value) $value = 500;
		$value = min(500,max(1,intval($value)));
		if($value <500){
			$value = min(self::$area['maxorderno'],$value);
		}
		return $value;
	}
	
	public static function updatedb($pushid = 0,$fromid = 0){//针对单条信息的操作
		//pushid：当前推送信息id，0为手动添加或新增推送
		//fromid：内容来源信息的id，新增推送时需要传入
		global $db,$tblprefix,$timestamp;
		if(!self::$area) return false;//未初始化推送位
		$curuser = cls_UserMain::CurUser();
		$sqlstr = '';foreach(self::$updates as $k => $v) $sqlstr .= ($sqlstr ? "," : "").$k."='".$v."'";
		if($sqlstr && $paid = @self::$area['paid']){
			if($pushid){//更新现有推送记录
				$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET $sqlstr WHERE pushid='$pushid'");
				cls_pusher::_updatecopy($pushid,self::$updates);
			}else{
				$sqlstr .= ",paid='$paid',fromid='$fromid',mid='{$curuser->info['mid']}',mname='{$curuser->info['mname']}',createdate='$timestamp',checked=1";
				$db->query("INSERT INTO {$tblprefix}".cls_pusher::tbl($paid)." SET $sqlstr");
				if($pushid = $db->insert_id()){
					$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET copyid='$pushid' WHERE pushid='$pushid'");
					if($curuser->pmautocheck(self::$area['autocheck'])) cls_pusher::_OrderFirst($pushid,$paid); # 新推送信息自动置顶
				}
			}
		}
		self::$updates = array();
		return $pushid;
	}
	public static function rollback(){
		self::$updates = array();
	}
	
	/*更新推送位栏目或地区
	*@param $classid  栏目或地区的ID值
	*       $key    栏目或类系字段名
 	*       $paid   推送位id
	*/
	public static function setclassid($pushid,$classid,$key,$paid){
		if(!cls_pusher::SetArea($paid)) return false;
		if(!cls_pusher::_SetFields() || !self::$fields[$paid]) return false;
		if(isset(self::$fields[$paid][$key])){
			if($re = cls_pusher::onefield(self::$fields[$paid][$key],isset($classid) ? $classid : '')){//捕捉出错信息
				cls_pusher::rollback();
				return $re;
			}
		}
	}
	
	//指定某个推荐位进行排序
	public static function ORefreshPaid($paid){
		if(!cls_pusher::SetArea($paid)) return false;
		$orderspace = self::$area['orderspace'];
		switch($orderspace){
			case 0:
				cls_pusher::ORefresh($paid);
			break;
			case 1:
				if($arr = cls_pusher::_fetch_classids(1,$paid)){
					foreach($arr as $k) cls_pusher::ORefresh($paid,$k,0);
				}
			break;
			case 2:
				if($arr = cls_pusher::_fetch_classids(2,$paid)){
					foreach($arr as $k) cls_pusher::ORefresh($paid,0,$k);
				}
			break;
			case 3:
				$arr1 = cls_pusher::_fetch_classids(1,$paid);
				$arr2 = cls_pusher::_fetch_classids(2,$paid);
				foreach($arr1 as $k1){
					foreach($arr2 as $k2){
						cls_pusher::ORefresh($paid,$k1,$k2);
					}
				}
			break;
		}
	}
	
	# 将某条信息排到当前排序空间的首位(同时需要将其它非固位信息后移一位)
	protected static function _OrderFirst($pushid = 0,$paid = 0){
		global $db,$tblprefix,$timestamp;
		if(!cls_pusher::SetArea($paid)) return false;
		$orderspace = self::$area['orderspace'];
		$maxorderno = (int)self::$area['maxorderno'] ? (int)self::$area['maxorderno'] : 10;
		$maxorderno = min(50,$maxorderno);
		
		if(!($push = cls_pusher::oneinfo($pushid,$paid))) return false;
		if(!$push['checked']) return false;
		if($push['startdate'] > $timestamp) return false;
		if($push['enddate'] && $push['enddate'] < $timestamp) return false;
		# 处理排序空间
		$spacestr = '';
		switch($orderspace){
			case 1:
				$spacestr .= " AND classid1='".(int)$push['classid1']."'";
			break;
			case 2:
				$spacestr .= " AND classid2='".(int)$push['classid2']."'";
			break;
			case 3:
				$spacestr .= " AND classid1='".(int)$push['classid1']."'";
				$spacestr .= " AND classid2='".(int)$push['classid2']."'";
			break;
		}
		
		# 将当前信息排到非固位的第一位
		$_wherestr = "WHERE fixedorder=500 AND vieworder<500 $spacestr 
		AND pushid<>'$pushid' 
		AND checked=1 
		AND startdate<'$timestamp' 
		AND (enddate='0' OR enddate>'$timestamp') 
		ORDER BY trueorder,pushid DESC";
		$NowFirstNo = (int)$db->result_one("SELECT trueorder FROM {$tblprefix}".cls_pusher::tbl($paid)." $_wherestr LIMIT 0,1");
		if(empty($NowFirstNo)) $NowFirstNo = 1;
		$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET trueorder=$NowFirstNo,vieworder=$NowFirstNo WHERE pushid='$pushid'");
		# 将当前排序空间内的有效排序信息住后挪一位
		$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET trueorder=trueorder+1,vieworder=vieworder+1 $_wherestr LIMIT $maxorderno");
		return true;
	}
	
	
	//针对特定的排序空间的信息进行排序
	public static function ORefresh($paid,$classid1 = 0,$classid2 = 0){
		global $db,$tblprefix,$timestamp;
		if(!cls_pusher::SetArea($paid)) return false;
		$orderspace = self::$area['orderspace'];
		$maxorderno = (int)self::$area['maxorderno'] ? (int)self::$area['maxorderno'] : 10;
		$maxorderno = min(50,$maxorderno);
		switch($orderspace){
			case 0:
				if($classid1 || $classid2) return false;
			break;
			case 1:
				if(!$classid1 || $classid2) return false;
			break;
			case 2:
				if($classid1 || !$classid2) return false;
			break;
			case 3:
				if(!$classid1 || !$classid2) return false;
			break;
		}
		$spacestr = '';
		$classid1 && $spacestr .= " AND classid1='$classid1'";
		$classid2 && $spacestr .= " AND classid2='$classid2'";
		
		$va = $fa = array();$i = 0;
		$sqlstr = "SELECT pushid,vieworder,fixedorder FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE checked=1 AND startdate<'$timestamp' AND (enddate='0' OR enddate>'$timestamp')";
		$spacestr && $sqlstr .= $spacestr;
		$sqlstr .= " ORDER BY fixedorder,vieworder,pushid DESC";
		$sqlstr .= " LIMIT 0,$maxorderno";
		$query = $db->query($sqlstr);
		while($r = $db->fetch_array($query)){
			if($r['fixedorder'] <> 500){
				$fa[$r['pushid']] = (int)$r['fixedorder'];
			}else{
				$va[$r['pushid']] = ++$i;
			}
		}
		$va = CombineOrderArray($va,$fa);
		$str = '';
		foreach($va as $k => $v){
			$str .= ",($k,$v)";
		}
		if($str = substr($str,1)){
			$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET trueorder=500".($spacestr ? ' WHERE '.substr($spacestr,5) : ''));
			$db->query("INSERT INTO {$tblprefix}".cls_pusher::tbl($paid)." (pushid,trueorder) VALUES $str ON DUPLICATE KEY UPDATE trueorder = VALUES(trueorder)");
			$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET vieworder=trueorder WHERE pushid IN (".implode(',',array_keys($va)).")");
		}
		return true;
	}
	
	public static function delete($pushid,$paid){//删除某个推荐信息
		global $db,$tblprefix;
		if(!cls_pusher::SetArea($paid)) return false;
		if(!($push = cls_pusher::oneinfo($pushid,self::$area['paid']))) return false;
		$ids = array($pushid);
		if($copyinfos = cls_pusher::copyinfos($push,self::$area['paid'])){//同时删除副本，副本不包含本身
			foreach($copyinfos as $k => $v) $ids[] = $k;
		}
		$db->query("DELETE FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE pushid ".multi_str($ids));
		return true;
	}
	
	//审核某个推荐信息
	public static function check($info){
		return cls_pusher::onedbfield('checked',1,@$info['checked']);
	}
	
	//解审某个推荐信息
	public static function uncheck($info){
		return cls_pusher::onedbfield('checked',0,@$info['checked']);
	}
	
	public static function oneinfo($pushid,$paid,$isView = false){//读取某条推荐信息
		global $db,$tblprefix;
		$pushid = empty($pushid) ? 0 : max(0,intval($pushid));
		if(!cls_pusher::SetArea($paid) || !$pushid) return false;
		$re = $db->fetch_one("SELECT * FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE pushid='$pushid'");
		if($isView) $re = cls_pusher::ViewOneInfo($re);
		return $re;
	}
	public static function copyinfos($info = array(),$paid = 0){//读取某条推荐信息的所有副本
		global $db,$tblprefix;
		if(!$paid || !cls_pusher::SetArea($paid) || !$info) return false;
		if(!($copyid = $info['copyid']) || !($pushid = $info['pushid'])) return false;
		$re = array();
		$query = $db->query("SELECT * FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE copyid='$copyid' AND pushid<>'$pushid' ORDER BY pushid");
		while($r = $db->fetch_array($query)) $re[$r['pushid']] = $r;
		return $re;
	}
	public static function copynum($info = array(),$paid = 0){//读取某条推荐信息的副本数量
		global $db,$tblprefix;
		if(!$paid || !cls_pusher::SetArea($paid) || !$info) return false;
		if(!($copyid = $info['copyid']) || !($pushid = $info['pushid'])) return false;
		$re = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE copyid='$copyid' AND pushid<>'$pushid'");
		return $re ? $re : 0;
	}
	
	public static function tbl($paid){//读取某条推荐位的完整标题
		return cls_PushArea::ContentTable($paid);
	}
	
	public static function AllTitle($paid,$noid = 0,$admin_url = 0){//读取某条推荐位的完整标题
		if(!cls_pusher::SetArea($paid)) return false;
		$pushtypes = cls_cache::Read('pushtypes');
		$re = $pushtypes[self::$area['ptid']]['title'].'>';
		$re .= $admin_url ? "<a href='?entry=extend&extend=pushs&paid=$paid' onclick=\"return floatwin('open_arcdetail',this)\" title='点击管理推送位'>".self::$area['cname']."</a>" : self::$area['cname'];
		if(!$noid) $re .= "($paid)";
		return $re;
	}
	
	//加载信息的初始化条件
	//在这里不排除已加载的id
	public static function InitWhere($paid,$pre = ''){
		if(!cls_pusher::SetArea($paid)) return false;
		$re = '';
		switch(self::$area['sourcetype']){
			case 'archives':
				$re .= "{$pre}checked=1";//已审
				if(!empty(self::$area['smallids'])){  
					if($ids = cls_pusher::_AllSmallIds(self::$area['paid'])){
						$re .= " AND {$pre}caid IN (".implode(',',$ids).")";//已审
					}
				}
			break;
			case 'members':
				$re .= "{$pre}checked=1";//已审
			break;
			case 'catalogs':
				$re .= "{$pre}closed=0";//关闭
			break;
			case 'commus':
				$re .= "{$pre}checked=1";//已审
			break;
			default:return false;
		}
		if($Sql = cls_pusher::AddSourceSql($paid,$pre)){
			$re .= ' AND '.$Sql;
		}
		return $re;
	}
	
	# 附加的自定义Sql
	public static function AddSourceSql($paid,$pre = ''){
		$re = '';
		if(cls_pusher::SetArea($paid)){
			if($sql = @self::$area['sourcesql']){//处理附加sql
				if(preg_match("/^return\b/i",$sql)) $sql = @eval($sql);//通过函数引用
				if($sql = key_replace($sql,array('pre' => $pre,'timestamp' => cls_env::GetG('timestamp')))){
					$re = $sql;
				}
			}
		}
		return $re;
	}
	
	
	public static function InitFrom($paid,$pre = ''){
		global $tblprefix;
		if(!cls_pusher::SetArea($paid)) return false;
		$re = '';
		switch(self::$area['sourcetype']){
			case 'archives':
				if(!($tbl = atbl(self::$area['sourceid']))) return false;
				$re .= cls_env::GetG('tblprefix').$tbl;
			break;
			case 'members':
				$re .= cls_env::GetG('tblprefix')."members";
			break;
			case 'catalogs':
				$re .= cls_env::GetG('tblprefix').cls_catalog::Table(self::$area['sourceid'],true);
			break;
			case 'commus':
				$re .= cls_env::GetG('tblprefix').cls_commu::ContentTable(self::$area['sourceid']);
			break;
			default:return false;
		}
		if($re && $pre){
			$re .= ' '.substr($pre,0,-1);
		}
		return $re;
	}
	protected static function _InSourceSql($info){//推送来源是否存在
		global $db,$tblprefix;
		if(!($paid = self::$area['paid'])) return false;
		if(!($SourceSql = cls_pusher::AddSourceSql($paid))) return true;
		if(!($FromTable = cls_pusher::InitFrom($paid))) return false;
		if(!($IDKey = cls_pusher::_IDKey($paid))) return false;
		if(!($Fromid = cls_pusher::_GetFromid($info))) return false;
		$re = $db->result_one("SELECT COUNT(*) FROM $FromTable WHERE $IDKey='$Fromid' AND $SourceSql");
		return $re ? true : false;
	}
	
	//删除来源id后，同时删除相关的推送信息
	//$sourcetype : archives 或 members 或 commus 或 catalogs 或 
	//$sourceid : 分别对应文档模型id, 会员模型id, 交互模型id, 类系项目id或栏目(0); 交互与类目一定要带此参数
	public static function DelelteByFromid($fromid=0, $sourcetype='archives', $sourceid=-1){
		global $db,$tblprefix;
		if(!($fromid = max(0,intval($fromid)))) return;
		$sourceid = is_numeric($sourceid) ? $sourceid : -1;
		$sourceid = max(-1,intval($sourceid));
		$pushareas = cls_PushArea::Config();
		foreach($pushareas as $k => $v){
			if($sourcetype == $v['sourcetype']){ 
				if(in_array($v['sourcetype'],array('commus','catalogs')) && intval($v['sourceid'])!=$sourceid ) continue; //交互与类目一定要带与sourceid相符
				if(in_array($v['sourcetype'],array('archives','members')) && $sourceid>0 && intval($v['sourceid'])!=$sourceid ) continue; //可以不要这行,但带了sourceid可提高效率
				$db->query("DELETE FROM {$tblprefix}".cls_pusher::tbl($k)." WHERE fromid='$fromid'", 'SILENT');
			}	
		}
	}
	
	# 只用于新推送的检查，更新内容不需要此检查
	protected static function _PushCheck($info){//推送检查
		global $timestamp;
		if(!self::$area) return false;//未初始化推送位
		if(!cls_pusher::_GetFromid($info)) return false;//来源不存在，或类型不对应
		switch(self::$area['sourcetype']){
			case 'archives':
				if(self::$area['sourceid'] != $info['chid']) return false;//类型id需要对应
				if(!$info['checked']) return false;//未审的
				$ids = cls_pusher::_AllSmallIds(self::$area['paid']);
				if($ids && !in_array($info['caid'],$ids)) return false;
				#if($info['enddate'] && $info['enddate'] < $timestamp) return false;//过期的
			break;
			case 'members':
				if(self::$area['sourceid'] != $info['mchid']) return false;//类型id需要对应
				if(!$info['checked']) return false;//未审的
			break;
			case 'catalogs':
				if(!empty($info['closed'])) return false;//关闭的
			break;
			case 'commus':
			break;
			default:return false;
		}
		if(!cls_pusher::_InSourceSql($info)) return false;//不属于附加SQL范围内的
		if(cls_pusher::_SourceExist($info)) return false;//已经推送过的，因为涉及查询，处理尽快排后
		//被附加sql限制的处理
		return true;
	}
	
	
	
	//计算出所有的指定栏目的子栏目
	protected static function _AllSmallIds($paid){//推送检查
		if(!cls_pusher::SetArea($paid) || !self::$area['smallids']) return array();
		$smallids = array_filter(explode(',',self::$area['smallids']));
		if(!self::$area['smallson'] || self::$area['sourcetype'] != 'archives'){
			return $smallids;
		}else{
			$re = array();
			foreach($smallids as $id) $re = array_merge($re,sonbycoid($id,0,1));
			return $re;
		}
	}
	
	//通过fromid得到一条来源内容的信息
	protected static function _OneFromInfo($fromid,$paid){//推送检查
		global $db,$tblprefix;
		if(!$paid || !cls_pusher::SetArea($paid) || !$fromid) return false;
		if(!($IDKey = cls_pusher::_IDKey($paid))) return false;
		switch(self::$area['sourcetype']){
			case 'archives':
				if(!($ntbl = atbl(self::$area['sourceid']))) return false;
				$sqlstr = "SELECT * FROM {$tblprefix}$ntbl a";
				if(cls_pusher::SourceNeedAdv($paid)) $sqlstr .= " INNER JOIN {$tblprefix}archives_".self::$area['sourceid']." c ON a.$IDKey=c.$IDKey";
				$sqlstr .= " WHERE a.$IDKey='$fromid'";
			break;
			case 'members':
				$sqlstr = "SELECT * FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.$IDKey=m.$IDKey";
				if(cls_pusher::SourceNeedAdv($paid)) $sqlstr .= " INNER JOIN {$tblprefix}members_".self::$area['sourceid']." c ON c.$IDKey=m.$IDKey";
				$sqlstr .= " WHERE m.$IDKey='$fromid'";
			break;
			case 'catalogs':
				if(!($ntbl = cls_catalog::Table(self::$area['sourceid'],true))) return false;
				$sqlstr = "SELECT * FROM {$tblprefix}$ntbl WHERE $IDKey='$fromid'";
			break;
			case 'commus':
				if(!($ntbl = cls_commu::ContentTable(self::$area['sourceid']))) return false;
				$sqlstr = "SELECT * FROM {$tblprefix}$ntbl WHERE $IDKey='$fromid'";
			break;
			default:return false;
		}
		$re = $db->fetch_one($sqlstr);
		return $re ? $re : false;
	}
	
	# 来源信息中的id的key
	protected static function _IDKey($paid){
		if(!$paid || !cls_pusher::SetArea($paid)) return '';
		$KeyArray = array(
			'archives' => 'aid',
			'members' => 'mid',
			'catalogs' => cls_catalog::Key(self::$area['sourceid']),
			'commus' => 'cid',
		);
		return isset($KeyArray[self::$area['sourcetype']]) ? $KeyArray[self::$area['sourcetype']] : '';
	}
	
	//加工来源内容
	protected static function _DealSourceInfo($info){
		if(!self::$area) return $info;//未初始化推送位
		switch(self::$area['sourcetype']){
			case 'archives':
				if(!isset($info['arcurl'])){
					if(!empty(self::$area['sourcefields']['url']['nodemode'])){
						$info['nodemode'] = 1;//设置手机版标记
					}
					cls_ArcMain::Url($info,-1);
				}
			
			break;
			case 'members':
				if(!isset($info['mspacehome'])) $info['mspacehome'] = cls_Mspace::IndexUrl($info);
			break;
			case 'catalogs':
			break;
			case 'commus':
			break;
			default:return false;
		}
		return maddslashes($info,1);
	}
	protected static function _SetFields(){//设置当前操作的字段配置
		if(self::$area){
			isset(self::$fields[self::$area['paid']]) or self::$fields[self::$area['paid']]=array();
			if(!self::$fields[self::$area['paid']]){
				if(!self::$fields[self::$area['paid']] = cls_PushArea::Field(self::$area['paid'])) return false;
			}
		}else return false;
		return true;
	}
	
	protected static function _OneSourceValue($rule,$info = array()){//取得一个字段的来源值
		if(!$rule || !$info) return false;
		$re = key_replace($rule,$info); # 将当前资料代入{xxxx}占位符
		if(preg_match("/^return\b/i",$re)){//通过函数引用
			$re = @eval($re);
		}elseif(preg_match("/\[cnode::(.+?)\]/i",$re)){//类目节点
			$re = preg_replace("/\[cnode::(.+?)::(\d+)::(\d)\]/ies","cls_cnode::url('\\1','\\2','\\3')",$re);
		}elseif(preg_match("/\[mcnode::(.+?)\]/i",$re)){//会员频道节点
			$re = preg_replace("/\[mcnode::(.+?)::(\d+)\]/ies","cls_mcnode::url('\\1','\\2')",$re);
		}
		return $re;
	}
	
	//处理一个字段的推送，返回出错信息
	//如果是刷新，需要排除共享类系的更新
	protected static function _push_field($field = array(),$info = array(),$isrefresh = 0){
		if(!cls_pusher::_FieldCfgOk($field)) return '错误字段：'.@$field['cname'];
		if($isrefresh){
			if(empty(self::$area['sourcefields'][$field['ename']]['refresh'])) return @$field['cname'].'：不需要从来源更新';
			if(!empty(self::$area['copyspace']) && $field['ename'] == 'classid'.self::$area['copyspace']){//更新时不要更新共享类系
				return @$field['cname'].'：共享分类不从来源更新';
			}
		}
		if(($from = @self::$area['sourcefields'][$field['ename']]['from']) && ($value = cls_pusher::_OneSourceValue($from,$info))){
			return cls_pusher::onefield($field,$value);
		}elseif($field['notnull']) return $field['cname'].'：不能为空';//必填字段未设置字段来源规则，或来源规则设置错误，返回false;
		return;
	}
	
	
	//校验一个字段配置是否合法
	protected static function _FieldCfgOk($field = array()){
		if(!($paid = self::$area['paid'])) return false;
		if(!$field || @$field['type'] != 'pa' || @$field['tpid'] != $paid) return false;
		return true;
	}
	
	//设置标题颜色//方法封装有问题，后续改进?????????
	protected static function _SetColor(){
		global $color;
		if($color){
			cls_pusher::onedbfield('color',$color == '#' ? '' : $color);
		}
	}
	
	protected static function _SourceExist($info){//推送来源是否存在
		global $db,$tblprefix;
		if(!($paid = self::$area['paid'])) return true;//???当作已存在
		$fromid = cls_pusher::_GetFromid($info);
		$re = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE fromid='$fromid'");
		return $re ? true : false;
	}
	
	//从一条来源中得到fromid
	protected static function _GetFromid($info){
		if(!empty(self::$area)){
			if(!($IDKey = cls_pusher::_IDKey(self::$area['paid']))) return 0;
			$fromid = @$info[$IDKey];
		}
		return empty($fromid) ? 0 : $fromid;
	}
	
	//将针对pushid的更新同时更新到副本
	protected static function _updatecopy($pushid = 0,$updates = array()){
		global $db,$tblprefix;
		if(!self::$area) return false;//未初始化推送位
		if(empty(self::$area['copyspace'])) return false;
		if(empty($updates)) return false;
		if(!($push = cls_pusher::oneinfo($pushid,self::$area['paid']))) return false;
		if(!($copyinfos = cls_pusher::copyinfos($push,self::$area['paid']))) return false;
		$classid = 'classid'.self::$area['copyspace'];
		$paid = self::$area['paid'];
		
		$sqlstr = '';
		foreach($updates as $k => $v){
			if(!in_array($k,array($classid))){
				 $sqlstr .= ",$k='$v'";
			}
		}
		if($sqlstr = substr($sqlstr,1)){
			if($ids = array_keys($copyinfos)){
				$db->query("UPDATE {$tblprefix}".cls_pusher::tbl($paid)." SET $sqlstr WHERE pushid ".multi_str($ids));
			}
		}
		return true;
	}
	
	//获取推荐信息中某个类系中被使用过的分类id
	protected static function _fetch_classids($coid = 1,$paid = 0){
		global $db,$tblprefix,$timestamp;
		if(!in_array($coid,array(1,2))) return false;
		if(!cls_pusher::SetArea($paid)) return false;
		$re = array();
		$query = $db->query("SELECT DISTINCT(classid$coid) FROM {$tblprefix}".cls_pusher::tbl($paid)." WHERE checked=1 AND startdate<'$timestamp' AND (enddate='0' OR enddate>'$timestamp')");
		while($r = $db->fetch_array($query)){
			$re[] = $r["classid$coid"];
		}
		return $re;
	}
	
	
}
