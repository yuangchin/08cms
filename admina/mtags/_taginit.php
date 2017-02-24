<?php
foreach(array('grouptypes','currencys','channels','fchannels','fcatalogs','cotypes','votes','commus','mcommus','vcatalogs','mchannels','dbsources','catalogs','mcatalogs','matypes','abrels','usualtags','tagclasses',) as $k) $$k = cls_cache::Read($k);
$lang = array('ctag' => '复合标识','rtag' => '区块标识',);

switch(@$ttype){
	case 'rtag':
		$tclassarr = array('' => '',);
		$tclass = '';
	break;
    default : $tclassarr = cls_Tag::TagClass();
    break;
}
$unsetvars = cls_Tag::UnsetVars();
$unsetvars1 = cls_Tag::UnsetVars1();

function load_mtags($ttype,$force = 0){
	if(!($arr = cls_cache::Read($ttype.'s','','')) || $force){
		$dir = cls_tpl::TemplateTypeDir('tag');
		$arr = array();
		if(is_dir($dir)){
			if($d = opendir($dir)){
				while(($f = readdir($d)) !== false){
					if(filetype($dir.$f) == 'file' && preg_match("/^$ttype(.+?)\.cac\.php$/is",$f,$matches) && $matches[1] != 's'){
						if(!($key = $matches[1]) || ($key == 's') || substr($key,0,3) == 'fr_') continue;
						$r = cls_cache::Read($ttype,$key,'',1);
						foreach($r as $x => $y) if(!in_array($x,array('ename','cname','tclass','vieworder','disabled',))) unset($r[$x]);
						$arr[$key] = $r;
					}
				}
				closedir($d);
			}
		}
		unset($matches,$r);
		mtags_cache($arr,$ttype);
	}
	return $arr;
}
function mtags_cache($mtags,$ttype){
	cls_Array::_array_multisort($mtags);
	cls_CacheFile::Save($mtags,$ttype.'s',$ttype.'s');
}
function tag_style($ename){
	global $ttype;
	return "{".($ttype == 'rtag' ? 'tpl' : str_replace('tag','',$ttype))."\$<b>$ename</b>}";
}
function mtags_update(&$mtags,$mtag){
	foreach($mtag as $k => $v){
		if(!in_array($k,array('ename','cname','tclass','vieworder','disabled',))) unset($mtag[$k]);
	}
	$mtag['tclass'] = empty($mtag['tclass']) ? 0 : $mtag['tclass'];
	$mtags[$mtag['ename']] = $mtag;
}
function mtag_code($ttype,$mtag){
	$mode = str_replace('tag','',$ttype);
	$str = '{'.$mode.'$'.$mtag['ename'];//起始符
	!empty($mtag['cname']) && $str .= ' [cname='.$mtag['cname'].'/]';
	!empty($mtag['tclass']) && $str .= ' [tclass='.$mtag['tclass'].'/]';
	!empty($mtag['disabled']) && $str .= ' [disabled=1/]';
	if(!empty($mtag['setting'])){
		foreach($mtag['setting'] as $key => $val){
			$str .= ' ['.$key.'='.$val.'/]';
		}
	}
	$str .= "}";//参数中止
	!empty($mtag['template']) && $str .= $mtag['template'];//加入模板
	$str .= '{/'.$mode.'$'.$mtag['ename'].'}';//加入结束符
	return $str;

}
function mtag_error($str = '模板标识错误'){
	global $errormsg;
	if(!submitcheck('bmtagcode')){
		cls_message::show($str,M_REFERER);
	}else $errormsg = $str;
}

function _tag2code($tname, $del_setting = false){
	if(is_array($tname)) {
       $mtag = array_merge($tname, $tname['setting']);
       if($del_setting) unset($mtag['setting']);
       $tname = $mtag['ename'];
	} else {
	   $mtag = cls_cache::ReadTag('ctag',$tname);
	}

	if(empty($mtag) || empty($mtag['tclass'])) return '{c $'.$tname.'}';
	$template = empty($mtag['template']) ? '' : $mtag['template'];
	foreach(array('vieworder','template',) as $k) unset($mtag[$k]);
	$str = '{c$'.$tname;//起始符
	foreach($mtag as $k => $v) {
	   /**
        * 插入新标识时不想让生成一个[ename=...]的标识参数
        * 2013-04-24
        * 
        * @author amw895
        */
       if(!empty($_GET['textid']))
       {
            if($k == 'ename' || empty($mtag[$k])) 
            {
                unset($mtag[$k]);
                continue;
            }
       }
	   $str .= ' ['.$k.'='.$v.'/]';
	}
	$str .= "}";//参数中止
	empty($template) || $str .= $template;//加入模板
	$str .= '{/c$'.$tname.'}';//加入结束符
	unset($mtag,$template,$k,$v);
	return $str;
}
function _tag_merge($oarr = array(),$narr = array()){
	if(empty($oarr) || !is_array($oarr)) return $narr;
	if(empty($narr) || !is_array($narr)) return $oarr;
	$osetting = empty($oarr['setting']) ? array() : $oarr['setting'];
	$nsetting = empty($narr['setting']) ? array() : $narr['setting'];
	unset($oarr['setting'],$narr['setting']);
	$narr = array_merge($oarr,$narr);
	$narr['setting'] = array_merge($osetting,$nsetting);
	return $narr;
}

function _view_tagcode($mtagcode,$helpstr = '',$iframe = 1){
	global $cms_abs;
	if($iframe){
	    empty($mtagcode) && $mtagcode = '';
	    $copyString = _08_HTML::createCopyCode('call_function', $mtagcode);
		echo "<script language=\"javascript\" reload=\"1\">parent.\$id('mtagcodeiframe').style.display='';</script>".
		"<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#D3ECFC\" style=\"padding:0px;margin:0px;\">".
		"<tr class=\"txt\"><td class=\"txtL w25B\"><textarea rows=\"8\" name=\"mtagcode\" id=\"mtagcode\" cols=\"90\">".(empty($mtagcode) ? '' : htmlspecialchars(str_replace("\t","    ",$mtagcode)))."</textarea></td><td class=\"item2\">".
		"<b><< 当前标识模板代码</b><p style='margin:10px 0 0 -60px;'>{$copyString}<span style='position: absolute; margin-top:4px'>&nbsp; &nbsp; (<a href='#' onclick=\"parent.\$id('mtagcodeiframe').style.display='none';\">收起代码</a>)</span></p>";
		"</td></tr></table>";
	}else{
		echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#D3ECFC\" style=\"padding:0px;margin:0px;\">".
		"<tr class=\"txt\"><td class=\"txtL w25B\"><textarea rows=\"15\" name=\"mtagcode\" id=\"mtagcode\" cols=\"110\">".(empty($mtagcode) ? '' : htmlspecialchars(str_replace("\t","    ",$mtagcode)))."</textarea></td><td class=\"item2\">".
		"<b><< 当前标识模板代码</b><br><br>[<a href='#' onclick=\"mtagcodecopy(\$id('mtagcode'));\">复制代码</a>]&nbsp; &nbsp; [$helpstr]";
		"</td></tr></table>";
	}
}
function _tag_helpstr($ttype,$tclass = '',$title = '帮助'){
	$addstr = $tclass ? ("#".str_replace('tag','',$ttype)."_$tclass") : '';
	return "<a href=\"tools/taghelp.html$addstr\" target=\"08cmstaghelp\">$title</a>";
}
