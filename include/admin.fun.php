<?php
/*
* 本脚本放置管理后台及会员中心会共用，但前台页面不需要用的函数
* 分别只使用在会员中心或管理后台的函数另外存放在admina.fun.php或adminm.fun.php中
*/
!defined('M_COM') && exit('No Permission');
function time_diff($t,$needsuffix = 0,$level= 2,$line = 0){
	global $timestamp;
	$line || $line = $timestamp;
	$diff = $t - $line;
	$suffix = $diff > 0 ? '后' : '前';
	$diff = abs($diff);
	$na = array(31536000 => '年',2592000 => 'M',86400 => '天',3600 => '时',60 => '分',);
	$str = '';$lv = 0;
	foreach($na as $k => $v){
		if($x = floor($diff / $k)){
			$str .= $x.$v;
			$diff = $diff % $k;
			$lv ++;
		}
		if($level && $lv >= $level) break;
	}
	$str || $str = '几秒';
	return $str.($needsuffix ? $suffix : '');
}
function cnodesfromcnc(&$cnconfig,$oldupdate = 0,$NodeMode = 0){
//$NodeMode：是否手机节点
//oldupdate:补全节点的同时更新节点配置，但不更新定制节点
	if($cnconfig['closed']) return false;
	$tid = $cnconfig['tid'];
	if(empty($cnconfig['isfunc'])){
		if(!($idsarr = cfgs2ids($cnconfig['configs']))) return false;
		$narr = array();$i = 0;$j = count($idsarr) - 1;
		foreach($idsarr as $k =>$ids){
			$kv = !$k ? 'caid' : 'ccid'.$k;
			if(!$i){
				foreach($ids as $id){
					if($i == $j) cls_node::AddOneCnode("$kv=$id",$tid,$oldupdate,$NodeMode);
					else $narr[] = "$kv=$id";
				}
			}else{
				$arr = array();
				foreach($narr as $v){
					foreach($ids as $id){
						if($i == $j) cls_node::AddOneCnode($v."&$kv=$id",$tid,$oldupdate,$NodeMode);
						else $arr[] = $v."&$kv=$id";
					}
				}
				$narr = $arr;
			}
			$i ++;
		}
		unset($narr,$arr,$idsarr,$ids);
		return true;
	}else{
		@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'custom.fun.php';
		if(empty($cnconfig['funcode'])) return false;
		return ($re = @eval($cnconfig['funcode'])) ? true : false;
	}
}
function mcnodesfromcnc($idcfg,$tid = 0){//手动配置节点
	if($idcfg['mcnvar'] == 'mcnid'){
		maddonecnode(array('mcnvar' => 'mcnid','alias' => $idcfg['alias'],),$tid);
	}else{
		foreach($idcfg['ids'] as $k) maddonecnode(array('mcnvar' => $idcfg['mcnvar'],'mcnid' => $k,),$tid);
	}
	return;
}
function maddonecnode($arr = array(),$tid = 0){
	global $db,$tblprefix;
	if(!$arr || empty($arr['mcnvar'])) return false;
	$sqlstr = "mcnvar='".$arr['mcnvar']."',tid='$tid'";
	if($arr['mcnvar'] == 'mcnid'){
		$arr['alias'] = empty($arr['alias']) ? '自定义节点' : trim(strip_tags($arr['alias']));
		$db->query("INSERT INTO {$tblprefix}mcnodes SET alias='$arr[alias]',$sqlstr");
		if($cnid = $db->insert_id()) $db->query("UPDATE {$tblprefix}mcnodes SET mcnid='$cnid',ename='".$arr['mcnvar']."=$cnid' WHERE cnid=$cnid");
	}elseif(!$db->result_one("SELECT 1 FROM {$tblprefix}mcnodes WHERE ename='".$arr['mcnvar']."=$arr[mcnid]'")){
		$db->query("INSERT INTO {$tblprefix}mcnodes SET alias='".cls_node::mcnode_cname($arr['mcnvar'].'='.$arr['mcnid'])."',mcnid='$arr[mcnid]',ename='".$arr['mcnvar']."=$arr[mcnid]',$sqlstr");
	}
	return true;
}

//此函数在后续升级中将被 NoBackFunc 取代，继续保留以兼容旧版。
function backallow($name){
	$curuser = cls_UserMain::CurUser();
	return $curuser->NoBackFunc($name) ? false : true;
}
function backnav($type,$menu,$cfg = array()){//可能有变量，不能使用扩展缓存
	if(defined('M_MCENTER')){
		$cfg || $cfg = cls_cache::cacRead('mcnavurls',MC_ROOTDIR.'./func/',1);
		if(@count($cfg[$type]) > 1) url_nav($cfg[$type],$menu);
	}else{
		$cfg || $cfg = cls_cache::exRead('bknavurls',1);
		$cfg &&	url_nav($cfg[$type]['title'],$cfg[$type]['menus'],$menu,12);
	}
}
function saveconfig($cftype, $mconfigsnew2 = array()){
	global $mconfigsnew,$db,$tblprefix;
    if ($mconfigsnew2)
    {
        $mconfigsnew = $mconfigsnew2;
    }
	$tpl_mconfigs = cls_cache::Read('tpl_mconfigs');
	$tpl_fields = cls_cache::Read('tpl_fields');
	$tplvars = array('cmslogo','cmstitle','cmskeyword','cmsdescription','cms_icpno','bazscert','copyright','cms_statcode',);
	foreach($tpl_fields as $k => $v) $tplvars[] = "user_$k";
	foreach($mconfigsnew as $k => $v){
		if(in_array($k,$tplvars)){
			$tpl_mconfigs[$k] = stripslashes($v);
			$cachetpl = TRUE;
		}else{
			$db->query("REPLACE INTO {$tblprefix}mconfigs (varname,value,cftype) VALUES ('$k','$v','$cftype')");
			if(in_array($k,array('hosturl','cmsurl','enablestatic','virtualurl',))){//针对合作开发的特殊处理
				global $$k;
				$$k = $v;
			}
		}
	}
	empty($cachetpl) || cls_CacheFile::Save($tpl_mconfigs,'tpl_mconfigs','tpl_mconfigs');
	cls_CacheFile::Update('mconfigs');//需要在此过程中更新btags
}
function atm_delete($dbstr,$type = 'image'){
	cls_atm::atm_delete($dbstr, $type);
}
function view_checkurl($dbstr){
	// $dbstr为数据库储存值，非完整url,用于后台显示图片列表,本地文件不存在时显示nopic.gif
	global $ftp_url;
	$dbstr = str_replace(array('<!ftpurl />','<!cmsurl />'),'',$dbstr);//兼容之前的ftp格式的存储
	if(strstr($dbstr,":/")) return $dbstr;
	return cls_url::is_remote_atm($dbstr) ? ($ftp_url.$dbstr) : (is_file(M_ROOT.preg_replace('/(#\d*)*/','',$dbstr)) ? cls_url::view_url($dbstr) : 'images/common/nopic.gif');
}
function auto_insert_id($tbl = 'coclass'){//0返回错误状态
	global $cms_idkeep,$db,$tblprefix,$coid;
	$cms_idkeep = empty($cms_idkeep) ? 0 : intval($cms_idkeep);
	$idvars = array(
		'channels' => 'chid','splitbls' => 'stid',
		'cotypes' => 'coid','catalogs' => 'caid','coclass' => 'ccid','abrels' => 'arid','acommus' => 'cuid','cnrels' => 'rid',
		'mchannels' => 'mchid','grouptypes' => 'gtid','usergroups' => 'ugid','currencys' => 'crid','permissions' => 'pmid',
		'frcatalogs' => 'frcaid',
		'amconfigs' => 'amcid','localfiles' => 'lfid','players' => 'plid','rprojects' => 'rpid','watermarks' => 'wmid','pagecaches' => 'pcid',
		'aurls' => 'auid','mctypes' => 'mctid','mtypes' => 'mtid','menus' => 'mnid','mmtypes' => 'mtid','mmenus' => 'mnid','usualurls' => 'uid',
		'cntpls' => '','mcntpls' => '','cnconfigs' => '','arc_tpls' => '','o_cntpls' => '','o_cnconfigs' => '','o_arc_tpls' => '',//模板config中的数组
		'fcatalogs' => '','fchannels' => '','pushareas' => '','pushtypes' => '',//模板config中的数组
		'freeinfos' => '','mtconfigs' => '','mcatalogs' => '',//模板mconfig中的数组
	);
	if(!isset($idvars[$tbl])) exit('insert_id_error');
	$idvar = $idvars[$tbl];
	$cfg = cls_cache::cacRead('idkeeps',_08_EXTEND_SYSCACHE_PATH,1);
	if($idvar){//数据表的id
		$dbtbl = ($tbl=='coclass') ? "coclass$coid" : $tbl; 
		if(!($min = empty($cfg[$tbl][0]) ? 0 : $cfg[$tbl][0]) || !($max = empty($cfg[$tbl][1]) ? 0 : $cfg[$tbl][1])){
			$maxid = $db->result_one("SELECT MAX($idvar) FROM {$tblprefix}$dbtbl");
		}else{
			$maxid = $cms_idkeep == 1 ? max($min,$db->result_one("SELECT MAX($idvar) FROM {$tblprefix}$dbtbl WHERE $idvar>$min AND $idvar<$max")) : max($max,$db->result_one("SELECT MAX($idvar) FROM {$tblprefix}$dbtbl"));
		}
	}else{//模板缓存中的数组id
		$$tbl = cls_cache::Read($tbl);
		$maxid = ($keys = array_keys($$tbl)) ? max($keys) : 0;
		if(($min = empty($cfg[$tbl][0]) ? 0 : $cfg[$tbl][0]) && ($max = empty($cfg[$tbl][1]) ? 0 : $cfg[$tbl][1])){
			if($cms_idkeep == 1){
				$maxid = 0;
				foreach($$tbl as $k => $v){
					if($k >= $min && $k <= $max && $k >= $maxid){
						$maxid = $k;
					}
				}
				$maxid || $maxid = $min - 1;
			}else $maxid = max($max,$maxid);
		}
	}
	return 1 + $maxid;
}
function autokeyword($str){
	global $a_split;
	if(empty($a_aplit)){
		include_once M_ROOT."include/splitword.cls.php";
		$a_split = new SplitWord();
	}
	if(!$str) return '';
	$str = preg_replace("/&#?\\w+;/", '', strip_tags($str));
	return str_replace(' ',',',$a_split->GetIndexText($a_split->SplitRMM($str),100));
}
function sizecount($filesize){
	if($filesize >= 1073741824){
		$filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
	}elseif($filesize >= 1048576){
		$filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
	}elseif($filesize >= 1024){
		$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
	}elseif($filesize) $filesize = $filesize . ' Bytes';
	return $filesize;
}
function umakeoption($sarr=array(),$selected=''){
	$optionstr = '';
	foreach($sarr as $k => $v) $optionstr .= isset($v['unsel']) ? "<optgroup label=\"$v[title]\" style=\"background-color :#E0ECF2;\"></optgroup>\n" : "<option value=\"$k\"".($k == $selected ? ' selected' : '').">$v[title]</option>\n";
	return $optionstr;
}
function umakeradio($varname,$arr=array(),$selectid='',$ppr=0){
	$str = '';
	$i = 0;
	foreach($arr as $k => $v){
		if(empty($v['unsel'])){
			$checked = $selectid == $k || (!$i && $selectid == '') ? 'checked' : '';
			$str .= "<input class=\"radio\" type=\"radio\" name=\"$varname\" id=\"_$varname$k\" value=\"$k\" $checked><label for=\"_$varname$k\">$v[title]</label>";
			$i ++;
			$str .= !$ppr || ($i % $ppr) ? '&nbsp;  &nbsp;' : '<br />';
		}
	}
	return $str;
}
function makeselect($varname,$options,$addstr = ''){
	//html标记中两个style,标准解析,后一个不起作用。
	if(strpos($addstr,'vertical-align')<=0) $addstr .= ' style="vertical-align: middle;"';
	return "<select id=\"$varname\" name=\"$varname\" $addstr>$options</select>";
}

function makeoption($arr, $key='', $default='') {
	$str = $default ? "<option value=\"\">$default</option>\n" : '';
	if(is_array($arr))
    {
        foreach($arr as $k => $v)
        {
            $str .= "<option value=\"$k\"";
            if( (is_array($key) && in_array($k, $key)) || ($k == $key && empty($k) == empty($key)) )
            {
                $str .= ' selected="selected"';
            }
            $str .= ">$v</option>\n";
        }
    }
	return $str;
}
function makeradio($varname,$arr=array(),$selectid='',$ppr=0,$onclick='',$cls = ''){
	$str = '';
	$i = 0;
	foreach($arr as $k => $v){
		$checked = $selectid == $k && empty($k) == empty($selectid) || (!$i && $selectid == '') ? ' checked' : '';
		$checked .= $onclick ? " onclick=\"$onclick\"" : '';
		$str .= "<label for=\"_$varname$k\"".($cls ? " class=\"$cls\"" : '')."><input class=\"radio\" type=\"radio\" name=\"$varname\" id=\"_$varname$k\" value=\"$k\"$checked>$v</label>";
		$i ++;
		$str .= !$ppr || ($i % $ppr) ? '&nbsp;  &nbsp;' : '<br />';
	}
	return $str;
}

function OneCheckBox($varname,$title = '',$value = 0,$chkedvalue = 1){
	$re = "<input type=\"hidden\" name=\"$varname\" value=\"\">\n"; //不选情况下为空,而不是!inset()
	$re .= "<input type=\"checkbox\" class=\"checkbox\" name=\"$varname\" value=\"$chkedvalue\"".($value == $chkedvalue ? ' checked' : '').">\n";
	if($title) $re .= "<label for=\"$varname\">$title</label>\n";
	return $re;
}

function OneInputText($varname,$value = '',$width = 20,$addstr = ''){
	if(!$varname) return $value;
	return "<input type=\"text\" size=\"$width\" id=\"$varname\" name=\"$varname\" value=\"".mhtmlspecialchars($value)."\"  $addstr/>\n";
}
function OneCalendar($varname,$value = '',$addstr = ''){
	return OneInputText($varname,$value,20,"class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\"  $addstr");
}
function makecheckbox($varname,$sarr,$value=array(),$ppr=0,$pad=0,$cls = ''){//$ppr每行单元数
	$str = "<input type=\"hidden\" name=\"$varname\" value=\"\">\n"; //不选情况下为空,而不是!inset()
	$i = 0;
	foreach($sarr as $k => $v){
		$checked = in_array($k,$value) ? 'checked' : '';
		$str .= "<label for=\"_$varname$k\"".($cls ? " class=\"$cls\"" : '')."><input class=\"checkbox\" type=\"checkbox\" name=\"$varname\" id=\"_$varname$k\" value=\"$k\" $checked>$v</label>";
		$i++;
		$str .= ($ppr && !($i % $ppr)) || ($pad && $i == $pad) ?  '<br />' : '&nbsp;  &nbsp;';
	}
	return $str;
}
function multiselect($varname,$sarray,$value=array(),$width='50%'){
	$value = is_array($value)?$value:array();
	$selectstr = "<select name=\"$varname\" id=\"$varname\" size=\"5\" multiple=\"multiple\" style=\"width:".$width."\">\n";
	foreach($sarray as $k => $v) $selectstr .= "<option value=\"$k\"".(in_array($k,$value) ? ' selected' : '').">$v";
	$selectstr .= "</select>";
	return $selectstr;
}
function autoabstract($str){
	global $autoabstractlength;
	empty($autoabstractlength) && $autoabstractlength = 100;
	if(!$str) return '';
	$str = str_replace(chr(0xa1).chr(0xa1),' ',cls_string::HtmlClear($str));
	$str = preg_replace("/&([^;&]*)(;|&)/s",' ',$str);
	$str = preg_replace("/\s+/s",' ',$str);
	$str = preg_replace('/\[#.*?#\]/','',$str); //去掉分页标记[#标题1#]
	return cls_string::CutStr(trim($str),$autoabstractlength);
}
function cridsarr($cash=0){
	$currencys = cls_cache::Read('currencys');
	$narr = $cash ? array(0 => '现金') : array();
	foreach($currencys as $k => $v) $narr[$k] = $v['cname'];
	return $narr;
}
function pmidsarr($mode = 'aread',$addstr=''){
	$permissions = cls_cache::Read('permissions');
	$narr = array('0' => !$addstr ? '完全开放': $addstr);
	foreach($permissions as $k => $v) if(!empty($v[$mode]))  $narr[$k] = $v['cname'];
	return $narr;
}
function vcaidsarr(){
	$vcatalogs = cls_cache::Read('vcatalogs');
	$narr = array();
	foreach($vcatalogs as $k => $v) $narr[$v['caid']] = $v['title'];
	return $narr;
}
function stidsarr($noid=0){//$noid是否附带id
	$splitbls = cls_cache::Read('splitbls');
	$narr = array();
	foreach($splitbls as $k => $v) $narr[$k] = $v['cname'].($noid ? '' : "($k)");
	return $narr;
}
function first_id($arr){//取得缓存数组的第一个单元id
	foreach($arr as $k => $v) return $k;
	return 0;
}
function ugidsarr($gtid,$mchid=0,$noid = 0){
	$grouptypes = cls_cache::Read('grouptypes');
	$mchannels = cls_cache::Read('mchannels');
	$narr = array();
	if(empty($grouptypes[$gtid])) return $narr;
	$usergroups = cls_cache::Read('usergroups',$gtid);
	foreach($usergroups as $k => $v) if(!$mchid || in_array($mchid,explode(',',$v['mchids']))) $narr[$k] = $v['cname'].($noid ? '' : "($k)");
	return $narr;
}
function allow_op($mode = 'acheck'){//管理后台的批量操作选项，结合的角色的相关操作权限
	//a-常规内容(文档与交互) f-副件 m-会员
	//check-审核与解审 del-删除
	global $a_checks;
	if(!$a_checks) return false;
	return array_intersect($a_checks,array(-1,$mode)) ? true : false;
}

function form_str($fname='',$furl='',$fupload=0,$checksubmit=1,$newwin=0,$method='post'){
	global $infloat,$ajaxtarget,$handlekey,$_vFormInit;
	$ques = strpos($furl, '?') === false ? '?' : '&';
    
    # CSRF HASH
    $hash = cls_env::getHashValue();
    $hash_name = cls_env::_08_HASH;
	$_vFormInit = 1; //表单初始化标记(认证码处判断)
	return ($checksubmit ? "<script type=\"text/javascript\">var _08cms_validator = _08cms.validator('$fname');</script>" : '')
	."<form name=\"$fname\" id=\"$fname\" method=\"$method\"".(!$fupload ? "" : " enctype=\"multipart/form-data\"")." action=\"$furl".($infloat?"{$ques}infloat=$infloat&handlekey=$handlekey":'')."\"".($newwin ? " onsubmit=\"return ajaxform(this)\"" : '').">\n<input type=\"hidden\" name=\"$hash_name\" value=\"$hash\" />\n";
        
}
function trhidden($varname,$value){
	echo "<input type=\"hidden\" id=\"$varname\" name=\"$varname\" value=\"$value\">\n";
}
function makesubmitstr($varname,$notnull = 0,$mlimit = 0,$min = 0,$max = 0,$type = 'text',$regular = ''){
	if(!$notnull && !$mlimit && !$regular && !$min && !$max && $type != 'date') return '';
	$regular = str_replace('"', '&quot;', $regular);
	if(in_array($type,array('image','flash','media','file'))){
		$submitstr = " rule=\"adj\" must=\"$notnull\" exts=\"$exts\"";
	}elseif(in_array($type,array('images','flashs','medias','files'))){
		$submitstr = " rule=\"adjs\" must=\"$notnull\" exts=\"$exts\" min=\"$min\" max=\"$max\"";
	}elseif($type == 'htmltext'){
		$submitstr = " rule=\"html\" must=\"$notnull\" vid=\"$varname\" min=\"$min\" max=\"$max\"";
	}elseif($type == 'multitext'){
		$submitstr = " rule=\"text\" must=\"$notnull\" min=\"$min\" max=\"$max\"";
	}elseif($type == 'text'){
		$submitstr = " rule=\"text\" must=\"$notnull\" mode=\"$mlimit\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
	}elseif($type == 'date'){
		$submitstr = " rule=\"date\" must=\"$notnull\" min=\"$min\" max=\"$max\"";
	}elseif($type == 'int'){
		$submitstr = " rule=\"int\" must=\"$notnull\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
	}elseif($type == 'float'){
		$submitstr = " rule=\"float\" must=\"$notnull\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
	}else{
		if(!$notnull)return '';
		$submitstr = " rule=\"must\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
	}
	return $submitstr;
}
function tr_cns($trname, $varname,$arr = array(),$multiset = 0){
	if(!empty($arr['coid']) && !empty($arr['chid']) && !coid_in_chid($arr['coid'],$arr['chid'])) return;
	empty($arr['notblank']) || $trname = '<font color="red"> * </font>'.$trname;
	$str = !$multiset || empty($arr['max']) || empty($arr['coid']) ? '' : "<select id=\"mode_$varname\" name=\"mode_$varname\" style=\"vertical-align: middle;\">".makeoption(array(0 => '重设',1 => '追加',2 => '移除',),1)."</select> &nbsp;";
	trbasic($trname,$varname,$str.cn_select($varname,$arr),'');
}
function cn_select($varname,$arr = array()){
	//array('value'=>0,'coid'=>0,'chid'=>0,'notblank'=>0,'addstr'=>'','framein'=>0,'ids'=>array(),'max'=>0,'notip'=>0,'hidden'=>0,'emode'=>0,'evarname'=>'','evalue'=>0,'guide'=>0)
	//addstr：为空时的字符，也是提示性字符
	//framein：将结构性栏目也作为有效栏目
	//hidden：使用隐藏域
	//max：多选时最多选几个
	//ids：指定允许列出的指定id分类和子分类或（栏目和子栏目）
	//emode：是否需要列出期限设置
	//notip：不需要操作警告及限制
	//guide：提示向导信息
	//viewp：0-根据catahidden清掉无效类目，1-需要pid资料，不清除无效类目但设为unsel,-1完全清除无效类目
	global $ca_vmode;
	$cotypes = cls_cache::Read('cotypes');
	$value = 0;$coid = 0;$chid = 0;$addstr='请选择';$hidden = 0;$notblank = 0;$framein=0;$max=0;$notip=0;$emode=0;$evalue='';$evarname='';$ids=array();$guide='';$viewp=0;
	$vmode = empty($arr['coid']) ? $ca_vmode : @$cotypes[$arr['coid']]['vmode'];
	extract($arr, EXTR_OVERWRITE);
	if(!empty($ids)) $ids = is_numeric($ids) ? array($ids) : $ids; //指定的栏目或类系ID
	if($max && !$vmode) $vmode = 2;
	if($hidden){
		$str = cls_catalog::cnstitle($value,$max,$coid ? cls_cache::Read('coclasses',$coid) : cls_cache::Read('catalogs'))."<input type=\"hidden\" name=\"$varname\" value=\"$value\">\n";
	}elseif(!$vmode){

		$dt_arr = array();
		$arr_mode0 = array();
		if(!empty($ids)){
			foreach($ids as $k) $arr_mode0[] = cls_catalog::uccidsarr($coid,$chid,$framein,0,$viewp,$k);
			foreach($arr_mode0 as $p){
				if($p && is_array($p))
		 			$dt_arr += $p;
			}
		}else{
			$dt_arr = cls_catalog::uccidsarr($coid,$chid,$framein,0,$viewp);
		}

		$str = "<select style=\"vertical-align: middle;\" name=\"$varname\"" . ($notblank ? ' rule="must"' : '') . ">".umakeoption(($addstr ? array('0' => array('title' => $addstr)) : array()) + $dt_arr,$value)."</select>";
		unset($dt_arr);
		unset($arr_mode0);
	}elseif($vmode == 1){
		$arr = cls_catalog::uccidsarr($coid,$chid,$framein,1,0);
		if(!empty($ids)) foreach($arr as $k=>$v) if(!in_array($k,$ids)) unset($arr[$k]);
		if(!$max){ //类系:单选按钮(radio):不要第一个[请选择]
			$str = umakeradio($varname,$arr,$value) . ($notblank ? "<input type=\"hidden\" rule=\"must\" vid=\"$varname\" />" : '');
			if(empty($value) && $notblank) $str = str_replace('checked>','>',$str); //必选项,第一个不要默认为选中状态
		}else{
			$str = "<span onclick=\"boxTo()\"><input type=\"hidden\" name=\"$varname\" value=\"$value\" max=\"$max\"/>";
			$val = explode(',', $value);
			foreach($arr as $k => $v) empty($v['unsel']) && $str .= "<input type=\"checkbox\" id=\"$varname$k\" value=\"$k\"" . (in_array($k, $val) ? ' checked' : ''). "><label for=\"$varname$k\">$v[title]</label> &nbsp;";
			$str .= ($notblank ? "<input type=\"hidden\" rule=\"must\" vid=\"$varname\" />" : '').'</span>';
		}
	}elseif($vmode == 2){
		$arr_mode2 = array();
		$str = "<script>var data = [";
		if(!empty($ids)){
			foreach($ids as $k) $arr_mode2[] = cls_catalog::uccidsarr($coid,$chid,$framein,1,1,$k);
			$_tmp = array();
			foreach($arr_mode2 as $p){
				foreach($p as $k2=>$p2){
					$_tmp[$k2] = $p2;
				}
			}
			cls_catalog::uccidstop($_tmp);
			$cnt = 0;
			foreach($_tmp as $k=>$v){
				$str .= ($cnt ? ',' : '' )."[$k,$v[pid],'".addslashes($v['title'])."',".(empty($v['unsel']) ? 0 : 1) . ']';
				$cnt++;
			}
		}else {
			$arr_mode2 =  cls_catalog::uccidsarr($coid,$chid,$framein,1,1);
			cls_catalog::uccidstop($arr_mode2);
			$cnt = 0;
			foreach($arr_mode2 as $k=>$v){ 
				$str .= ($cnt ? ',' : '' )."[$k,$v[pid],'".addslashes($v['title'])."',".(empty($v['unsel']) ? 0 : 1) . ']';
				$cnt++;
			}
		}
		$str .= "];\n_08cms.fields.linkage('$varname', data, '$value',$max,$notip,'','','$addstr');</script>" . ($notblank ? "<input type=\"hidden\" rule=\"must\" vid=\"$varname\" max=\"$max\" />" : '');
		unset($arr_mode2,$_tmp);
	}else{
		$data = $coid ? "coid&coid=$coid" : 'caid';
		if(!empty($ids)) $data .= "&ids=".implode(',',$ids);
		$data .= "&chid=$chid&framein=$framein&charset=" . cls_env::getBaseIncConfigs('mcharset');
		$data = _08_Http_Request::uri2MVC($data, false);
		$str = "<span><script>_08cms.fields.linkage('$varname', 'action/$data', '$value',$max,$notip,'','','$addstr');</script></span>" . ($notblank ? "<input type=\"hidden\" rule=\"must\" vid=\"$varname\" max=\"$max\" />" : '');
	}
	$emode && $str .= ' &nbsp;截止日期'.($emode > 1 ? '<font color="red"> * </font>' : '')."<input type=\"text\" size=\"10\" id=\"$evarname\" name=\"$evarname\" value=\"$evalue\" class=\"Wdate\" onfocus=\"WdatePicker({readOnly:true})\" rule=\"date\"" . ($emode > 1 ? ' must="1"' : '') . ">\n";
	if($guide) $str .= "<div class=\"tips1\">$guide</div>";
	return $str;
}
/**
 *  文档自动分页
 *
 * @access    public
 * @param     string  $mybody  内容
 * @param     string  $spsize  分页大小
 * @param     string  $sptag  分页标记
 * @return    string
 */
function SpBody($mybody, $spsize, $sptag){
	$mybody = preg_replace('/\[#.*?#\]/','',$mybody);//自动模式先去除已有的分页
    if(strlen($mybody) < $spsize) return $mybody;
    $mybody = stripslashes($mybody);
    $bds = explode('<', $mybody);
    $npageBody = '';
    $istable = 0;
    $mybody = '';
    foreach($bds as $i=>$k){
        if($i==0){
			$npageBody .= $bds[$i];
			continue;
		}
        $bds[$i] = "<".$bds[$i];
        if(strlen($bds[$i])>6){
            $tname = substr($bds[$i],1,5);
            if(strtolower($tname)=='table'){
                $istable++;
            }else if(strtolower($tname)=='/tabl'){
                $istable--;
            }
            if($istable>0){
                $npageBody .= $bds[$i]; continue;
            }else{
                $npageBody .= $bds[$i];
            }
        }else{
            $npageBody .= $bds[$i];
        }
        if(strlen($npageBody)>$spsize){
            $mybody .= $npageBody.$sptag;
            $npageBody = '';
        }
    }
    if($npageBody!='') $mybody .= $npageBody;
	$mybody = $sptag.$mybody;
    return addslashes($mybody);
}
function aboutarchive($aids='',$usemode='archives'){#empty($aids)表示添加模式，!empty($aids)表示编辑模式。$usemode=='archives'表示应用于文档添加，$usemode == 'tagarchives'表示应用标签文档添加，$usemode='tagfarchives'表示应用于副件标签添加。
	global $db,$tblprefix,$chid;
	$chidarr = array(0=>'选择相关模型') + ($usemode == 'tagfarchives' ? cls_fchannel::fchidsarr() : cls_channel::chidsarr());
	if($aids){
		$relatedarr = array();$relatedstr = '';
		if($usemode == 'tagfarchives'){
			$query = $db->query("SELECT a.aid,a.subject FROM {$tblprefix}farchives a WHERE aid in($aids)");
			while($r = $db->fetch_array($query)){$relatedarr[$r['aid']] = $r['subject'];$relatedstr .= ','.$r['aid'];}
		}else{
			$q = $db->query("SELECT distinct chid FROM {$tblprefix}archives_sub WHERE aid in ($aids)");
			while($row = $db->fetch_array($q)){
				if($ntbl = atbl($row['chid'])){
					$query = $db->query("SELECT aid,subject FROM {$tblprefix}$ntbl where aid in ($aids)");
					while($r = $db->fetch_array($query)){
						$relatedarr[$r['aid']] = $r['subject'];
						$relatedstr .= ','.$r['aid'];
					}
				}
			}
		}
		$relatedstr = substr($relatedstr,1);
	}
	$usemode == 'archives' && trbasic('相关信息','',makeradio('autorelated',array(1=>'自动',0=>'手动'),(!empty($relatedstr) ? 0 : 1 )),'');
	echo '<tr id="related" '.(!empty($relatedstr) || $usemode != 'archives' ? '' : 'style="display:none;"').'><td colspan="2">
	关键词：<input type="text" size="30" name="RelativeKey" id="RelativeKey">&nbsp;&nbsp;<label><input name="RelativeTypeSubject" id="RelativeTypeSubject" type="checkbox" value="1" checked="checked">标题</label>&nbsp;&nbsp;<label><input name="RelativeTypeKey" type="checkbox" value="2" id="RelativeTypeKey" checked="checked">关键词Tag</label>&nbsp;&nbsp;<select name="relatedchid" id="relatedchid">'.makeoption($chidarr,$chid).'</select>&nbsp;&nbsp;<input type="button" name="relativeButton" id="relativeButton" value="查找相关信息">
	<div class="blank12"></div>
	
	<table border="0" width="100%">
	<tbody><tr><td>待选信息<br><select style="width: 240px; height: 250px;" multiple="" name="TempInfoList" id="TempInfoList"></select></td>
	<td><input type="button" class="button" id="RAddButton" value=" 添加选中 &gt;  "><br><br><input type="button" class="button" id="RAddMoreButton" value=" 全部添加 &gt;&gt; "><br><br><input type="button" class="button" id="RDelButton" value=" &lt; 删除选中  "><br><br><input type="button" class="button" id="RDelMoreButton" value=" &lt;&lt; 全部删除 "></td>          <td>选中信息<br><select style="width: 240px; height: 250px;" multiple="" name="SelectInfoList" id="SelectInfoList">'.(!empty($relatedarr) ? makeoption($relatedarr) : '').'</select></td></tr></tbody></table>
	</td></tr>';
	echo '<script type="text/javascript">var relatedaid = document.getElementById("'.($usemode == 'archives' ? 'relatedaid' : 'mtagnew[setting][ids]').'"),aids = "'.$aids.'";</script>';
	echo '<script type="text/javascript" src="include/js/aboutarchive.js"></script>';
}
function editColor($color=''){
	if($color){
		echo "<script type=\"text/javascript\">try{ColorSel('".$color."');}catch(eColor){}</script>\n";
	}
}

