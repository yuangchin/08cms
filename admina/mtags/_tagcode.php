<?PHP
/**
* 添加标识：$TagCodeIsAdd = 1
*/
$TagCodeIsAdd = empty($TagCodeIsAdd) ? 0 : 1;
$errormsg = '';
if(empty($mtagnew['ename'])){
	$errormsg = '请输入标识英文名称';
}elseif(!preg_match("/[a-zA-Z][a-z_A-Z0-9]{2,31}/",$mtagnew['ename'])){
	$errormsg = '标识英文名称不合规范(3~32个字母数字和下划线组成,字母开头)';
}else{
	$mtagnew['ename'] = trim(strtolower($mtagnew['ename']));
	$usedename = array_keys($mtags);
	if(!$TagCodeIsAdd && !$iscopy) $usedename = array_diff($usedename,array($tname));//仅是修改时，要将标识本身的名称排除
	if(in_array($mtagnew['ename'], $usedename)) $errormsg = '标识英文名称重复';
}
if(!$errormsg){
	if($TagCodeIsAdd){
		$tclass = empty($mtagnew['tclass']) ? '' : $mtagnew['tclass'];
	}else{
		$tclass = $mtagnew['tclass'] = $mtag['tclass'];
	}
	list($modeAdd,$modeSave) = array($TagCodeIsAdd,1);
    isset($tclass) && _08_FilesystemFile::filterFileParam($tclass);
	
	
	include(dirname(__FILE__) . DS . ($tclass ? $tclass : 'rtag').".php");
    $_orders = array();
	if(!$errormsg){
		if(!$TagCodeIsAdd) $mtagnew['disabled'] = @$mtag['disabled'];
		$mtagnew['setting'] = empty($mtagnew['setting']) ? array() : $mtagnew['setting'];
        
		if(!empty($mtagnew['setting'])){
			foreach($mtagnew['setting'] as $key => &$val){
                # 重新排序这两个字段
                if(($key == 'chids' || $key == 'chsource') && $val)
                {
                    $_orders[$key] = $val;
                }
                if ($key === 'dealhtml_tags')
                {
                    $keys = array_keys($val);
                    $val = implode('|', $keys);
                }
				if(in_array($key,$unsetvars) && empty($val)) unset($mtagnew['setting'][$key]);
				if(!empty($unsetvars1[$key]) && in_array($val,$unsetvars1[$key])) unset($mtagnew['setting'][$key]);
			}
		}
		$mtagnew['template'] = empty($mtagnew['template']) ? '' : $mtagnew['template'];
        $mtagnew['setting'] = array_merge($_orders, $mtagnew['setting']);
		
		$mtagcode = mtag_code($ttype,$mtagnew);
		_view_tagcode($mtagcode);
	}
}
echo "<script language=\"javascript\" reload=\"1\">".($errormsg ? "alert('$errormsg');" : '')."parent.\$id('".($TagCodeIsAdd ? 'mtagsadd' : 'mtagsdetail')."').target='_self';</script>";
mexit();