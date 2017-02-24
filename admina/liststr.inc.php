<?PHP
/*
** 本脚本在以下几个地方调用
** 1、模板复合标识生成查询字串或排序字串，action=标识类型
** 2、自动条件类系用于生成条件字串，action:selfclass
** 3、推送位生成追加的SQL语句，pushmode=1
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
$dbfields = cls_cache::Read('dbfields');
empty($action) && $action = 'archives';
$typeid = __TypeInitID(@$typeid,$action);//指定类型id，如chid，mchid等
$actiontitle = '';//界面标题
$pushmode = empty($pushmode) ? 0 : 1;//是否推送筛选sql(适用于archives,members,catalogs,commus);
$tablearr = array();
switch($action){
	case 'archives'://文档列表
		$actiontitle = '文档列表SQL设置';//界面标题
		$NotypeidMsg = '请选择文档模型';
		$channels = cls_cache::Read('channels');

		if($typeid && !empty($channels[$typeid])){
			$stid = $channels[$typeid]['stid'];
			if($pushmode){
				$tablearr["archives$stid"] = array('{pre}','文档主表','archives');//表别名，表名称，注释表名(留空为本表)
			}else{
				$tablearr["archives$stid"] = array('a.','文档主表','archives');//表别名，表名称，注释表名(留空为本表)
				$tablearr["archives_$typeid"] = array('c.','模型专用表','');
			}
		}
		$filters = array(0 => '请指定文档模型') + cls_channel::chidsarr();
	break;
	case 'members'://会员列表
		$actiontitle = '会员列表SQL设置';//界面标题
		$mchannels = cls_cache::Read('mchannels');
		if($pushmode){
			$tablearr["members"] = array('{pre}','会员主表','');//表别名，表名称，注释表名(留空为本表)
		}else{
			$tablearr["members"] = array('m.','会员主表','');//表别名，表名称，注释表名(留空为本表)
			$tablearr["members_sub"] = array('s.','会员副表','');//表别名，表名称，注释表名(留空为本表)
			if($typeid && !empty($mchannels[$typeid])){
				$tablearr["members_$typeid"] = array('c.','模型专用表','');
			}
			$filters = array(0 => '请指定会员模型') + cls_mchannel::mchidsarr();
		}
	break;
	case 'catalogs'://类目列表
		$actiontitle = '类目列表SQL设置';//界面标题
		$pre = $pushmode ? '{pre}' : '';
		$cotypes = cls_cache::Read('cotypes');
		if($typeid && !empty($cotypes[$typeid])){
			$tablearr = array(
				"coclass$typeid" => array($pre,"[{$cotypes[$typeid]['cname']}]分类表",'coclass'),//表别名，表名称，注释表名(留空为本表)
			);
		}else{
			$tablearr = array(
				'catalogs' => array($pre,'栏目表',''),//表别名，表名称，注释表名(留空为本表)
			);
		}
		$filters = array(0 => '栏目列表(0)');
		foreach($cotypes as $k => $v){
			$filters[$k] = $v['cname']."($k)";
		}
	break;
	case 'farchives'://副件列表
	case 'adv_farchives'://广告列表
		$actiontitle = ($action == 'farchives' ? '副件' : '广告').'列表SQL设置';//界面标题
		$fcatalogs = cls_cache::Read('fcatalogs');
		$tablearr = array(
			'farchives' => array('a.','副件主表',''),//表别名，表名称，注释表名(留空为本表)
		);
		if($typeid && ($chid = @$fcatalogs[$typeid]['chid'])){
			$tablearr["farchives_$chid"] = array('c.','模型专用表','');//表别名，表名称，注释表名(留空为本表)
		}
		$filters = array(0 => '请指定副件分类') + cls_fcatalog::fcaidsarr();
	break;
	case 'commus'://交互列表
		$actiontitle = '交互列表SQL设置';//界面标题
		$NotypeidMsg = '请选择交互项目';
		$pre = $pushmode ? '{pre}' : '';
		$commus = cls_cache::Read('commus');
		$tablearr = array();
		if($typeid && !empty($commus[$typeid]) && ($tbl = @$commus[$typeid]['tbl'])){
			$tablearr[$tbl] = array($pre,"[{$commus[$typeid]['cname']}]交互表",'');//表别名，表名称，注释表名(留空为本表)
		}
		$filters = array(0 => '请指定交互项目');
		foreach($commus as $k => $v){
			if($v['tbl']) $filters[$k] = $v['cname']."($k)";
		}
	break;
	case 'pushs'://推送列表
		$actiontitle = '推送列表SQL设置';//界面标题
		$NotypeidMsg = '请选择推送位';
		$pre = '';
		$tablearr = array();
		if($typeid && $pusharea = cls_PushArea::Config($typeid)){
			$tablearr[cls_PushArea::ContentTable($typeid)] = array($pre,"[{$pusharea['cname']}]推送表",'');//表别名，表名称，注释表名(留空为本表)
		}
		$filters = array(0 => '请指定推送位');
		$pushareas = cls_PushArea::Config();
		foreach($pushareas as $k => $v){
			$filters[$k] = $v['cname']."($k)";
		}
	break;
	case 'selfclass'://分类条件设置
		$actiontitle = '类目自定义条件SQL设置';//界面标题
		$NotypeidMsg = '请指定一个文档模型<br>以明确使用哪个主表做为条件设置';
		$channels = cls_cache::Read('channels');
		if($typeid && !empty($channels[$typeid])){
			$stid = $channels[$typeid]['stid'];
			$tablearr = array(
				"archives$stid" => array('{$pre}','文档主表','archives'),//表别名，表名称，注释表名(留空为本表)
			);
		}
		$filters = array(0 => '请指定文档模型') + cls_channel::chidsarr();
	break;
}
$orderbyarr = array('' => '','ASC' => '升序','DESC' => '降序',);
$dbtypearr = array(1 => array('text','mediumtext','longtext','char','varchar','tinytext',), 2 => array('tinyint','smallint','int','mediumint','bigint','float','double','decimal','bit','bool','binary',));
$modearr = array(
	'=' => 0,
	'>' => 1,
	'>=' => 1,
	'<' => 1,
	'<=' => 1,
	'!=' => 0,
	'LIKE' => 0,
	'NOT LIKE' => 0,
	'LIKE %...%' => 2,
	'LIKE %...' => 2,
	'LIKE ...%' => 2,
	'REGEXP' => 2,
	'NOT REGEXP' => 2,
	'IS NULL' => 0,
	'IS NOT NULL' => 0,
);

echo "<title>$actiontitle</title>";
//同action下的typeid切换列表
$filterbox = $actiontitle;
if(!empty($filters)){
	$filterbox .= " &nbsp;<select style=\"vertical-align: middle;\" name=\"tclass\" onchange=\"redirect('?entry=$entry&action=$action&pushmode=$pushmode&typeid=' + this.options[this.selectedIndex].value);\">";
	foreach($filters as $k => $v) $filterbox .= "<option value=\"$k\"".($typeid == $k ? ' selected' : '').">$v</option>";
	$filterbox .= "</select>";
}
tabheader($filterbox);
tabfooter();

if(submitcheck('bliststr') && !empty($fmdata)){
	$wherestr = $orderstr = '';
	$orderarr = array();
	foreach($fmdata as $k => $v){
		if(!empty($v['mode'])){
			if(in_array($v['mode'],array('IS NULL','IS NOT NULL',))){
				$wherestr .= ($wherestr ? ' AND ' : '').$k.' '.$v['mode'];
			}elseif(in_array($v['mode'],array('LIKE','NOT LIKE','REGEXP','NOT REGEXP',)) && $v['value'] != ''){
				$wherestr .= ($wherestr ? ' AND ' : '').$k." ".$v['mode']." '".$v['value']."'";
			}elseif(in_array($v['mode'],array('LIKE %...%','LIKE ...%','LIKE %...',)) && $v['value'] != ''){
				$wherestr .= ($wherestr ? ' AND ' : '').$k." ".str_replace(array('%...%','...%','%...'),array("'%".$v['value']."%'","'".$v['value']."%'","'%".$v['value']."'"),$v['mode']);
			}else{
				$wherestr .= ($wherestr ? ' AND ' : '').$k.' '.$v['mode']." '".$v['value']."'";
			}
		}
		if(!empty($v['order'])){
			$orderarr[$k.' '.$v['order']] = intval($v['prior']);
		}
	}
	if(!empty($orderarr)){
		asort($orderarr);
		foreach($orderarr as $k => $v) $orderstr .= ($orderstr ? ',' : '').$k;
	}
	tabheader('查询字串生成结果');
	trbasic('筛选字串','view_wherestr',$wherestr,'textarea');
	if(!$pushmode && !in_array($action,array('selfclass',))){
		trbasic('排序字串','view_orderstr',$orderstr,'textarea');
	}
	tabfooter();
}
if(empty($tablearr)){
	cls_message::show(empty($NotypeidMsg) ? '请选择具体类型' : $NotypeidMsg);
}else{
	$TblNo = 0;
	foreach($tablearr as $dbtable => $cfg){
		$ititle = "$cfg[1] &nbsp;- &nbsp;$dbtable &nbsp;(别名 ".($cfg[0] ? $cfg[0] : '无').")";
		if(!$TblNo){
			tabheader($ititle,'liststr',"?entry=$entry&action=$action&pushmode=$pushmode&typeid=$typeid",7);
		}else tabheader($ititle);
		trcategory(array('序号',array('字段名称','txtL'),array('字段类型','txtL'),array('字段说明','txtL'),'筛选模式','筛选值','排序模式','排序优先'));
		$query = $db->query("SHOW FULL COLUMNS FROM {$tblprefix}$dbtable",'SILENT');
		$tblfields = array();
		while($row = $db->fetch_array($query)){
			$types = explode(' ',$row['Type']);
			$tblfields[$row['Field']] = strtolower($types[0]);
		}
		$i = 1;
		foreach($tblfields as $k => $v){
			$var = $cfg[0].$k;
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w30\">$i</td>\n".
				"<td class=\"txtL\"><b>$k</b></td>\n".
				"<td class=\"txtL\">$v</td>\n".
				"<td class=\"txtL\">".DbfieldComment(empty($cfg[2]) ? $dbtable : $cfg[2],$k)."</td>\n".
				"<td class=\"txtC\"><select style=\"vertical-align: middle;\" name=\"fmdata[$var][mode]\">".makeoption(thismodearr($v),empty($fmdata[$var]['mode']) ? '' : $fmdata[$var]['mode'])."</select></td>\n".
				"<td class=\"txtC\"><input type=\"text\" size=\"20\" name=\"fmdata[$var][value]\" value=\"".(empty($fmdata[$var]['value']) ? '' : mhtmlspecialchars(stripslashes($fmdata[$var]['value'])))."\"></td>\n".
				"<td class=\"txtC w50\"><select style=\"vertical-align: middle;\" name=\"fmdata[$var][order]\">".makeoption($orderbyarr,empty($fmdata[$var]['order']) ? '' : $fmdata[$var]['order'])."</select></td>\n".
				"<td class=\"txtC w80\"><input type=\"text\" size=\"4\" name=\"fmdata[$var][prior]\" value=\"".(empty($fmdata[$var]['prior']) ? 0 : mhtmlspecialchars(stripslashes($fmdata[$var]['prior'])))."\"></td>\n".
				"</tr>";
			$i ++;
		}
		$TblNo ++;
		if($TblNo < count($tablearr)){
			tabfooter();
		}else tabfooter('bliststr','生成');
	}

}
function DbfieldComment($tbl,$field){
	$dbfields = cls_cache::Read('dbfields');
	return empty($dbfields[$tbl.'_'.$field]) ? '-' : $dbfields[$tbl.'_'.$field];
}
function thismodearr($type){
	global $modearr,$dbtypearr;
	$type = str_replace(strstr($type,'('),'',$type);
	$retarr = array('' => '');
	foreach($modearr as $k => $v){
		if(!$v || !in_array($type,$dbtypearr[$v])) $retarr[$k] = $k;
	}
	return $retarr;
}
function __TypeInitID($typeid,$action = 'archives'){
	$typeid = empty($typeid) ? '' : trim($typeid);
	if(in_array($action,array('farchives','adv_farchives','pushs',))){
		$typeid = cls_string::ParamFormat($typeid);
	}else{
		$typeid = empty($typeid) ? 0 : max(0,intval($typeid));
	}
	return $typeid;
}


