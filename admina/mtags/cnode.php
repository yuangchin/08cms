<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	templatebox('标识内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
	trbasic('信息调用的特征标记','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当标识存在嵌套时，该标记要不同于上下级标识。[PHP术语]资料数据的数组名。<br> 在本标识模板内{xxx}或{$v[xxx]}都可调出xxx资料，标识外调用只能使用{$v[xxx]}。'));			
    if($sclass === 'ca')
    {
        $sourcearr = array(
			'active' => '激活类目',
		);
    }
    else
    {
        $sourcearr = array(
			'0' => '非关联项',
			'active' => '激活类目',
		);
    }
	$sourcearr = $sourcearr + cls_catalog::ccidsarr(0);
	trbasic('栏目' . ($sclass === 'ca' ? '<span style="color:red">(作为列表项)</span>' : ''),
	'',"<select onchange=\"setIdWithS(this)\" id=\"mselect_mtagnew[setting][casource]\" style=\"vertical-align: middle;\">" . makeoption($sourcearr,(empty($_POST) ? @$mtag['setting']['casource'] : 0)) . "</select><input type=\"text\" value=\"".(empty($_POST) ? @$mtag['setting']['casource'] : 0)."\" onfocus=\"setIdWithI(this)\" name=\"mtagnew[setting][casource]\" id=\"mtagnew[setting][casource]\"/>",'');

	foreach($cotypes as $k => $cotype) {
		if($cotype['sortable']){
            if($sclass === ('co'.$k))
            {
                $sourcearr = array(
    				'active' => '激活类目',
    			);
            }
            else
            {
                $sourcearr = array(
    				'0' => '非关联项',
    				'active' => '激活类目',
    			);
            }
			$sourcearr = $sourcearr + cls_catalog::ccidsarr($k);	
            isset($mtag['setting']['cosource'.$k]) || $mtag['setting']['cosource'.$k] = '0';
			trbasic($cotype['cname'] . ($sclass === ('co'.$k) ? '<span style="color:red">(节点展示项)</span>' : ''),
			'',"<select onchange=\"setIdWithS(this)\" id=\"mselect_mtagnew[setting][cosource$k]\" style=\"vertical-align: middle;\">" . makeoption($sourcearr,empty($_POST) ? @$mtag['setting']['cosource'.$k] : 0) . "</select><input type=\"text\" value=\"".(empty($_POST) ? @$mtag['setting']['cosource'.$k] : 0)."\" onfocus=\"setIdWithI(this)\" name=\"mtagnew[setting][cosource$k]\" id=\"mtagnew[setting][cosource$k]\"/>",'');
		}
	}
	$levelarr = array('0' => '不追溯','1' => '一级','2' => '二级','3' => '三级',);
	trbasic('追溯指定列表项目的上级类目','',makeradio('mtagnew[setting][level]',$levelarr,isset($mtag['setting']['level']) ? $mtag['setting']['level'] : '0'),'');
	$arr = array('js' => '使用JS动态调用当前标识解析出来的内容',);
	$str = '';foreach($arr as $k => $v) $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"mtagnew[setting][$k]\" value=\"1\" ".(empty($mtag['setting'][$k]) ? '' : 'checked')."> &nbsp;$v<br>";
	trbasic('当前标识的更多设置','',$str,'');
	tabfooter();
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入标识模板');
	if($mtagnew['setting']['listby'] == 'ca' && empty($mtagnew['setting']['casource'])){
		$mtagnew['setting']['casource'] = 'active';
	}elseif(preg_match("/^co(\d+)/is",$mtagnew['setting']['listby'],$matches)){
		if(empty($mtagnew['setting']['cosource'.$matches[1]])) $mtagnew['setting']['cosource'.$matches[1]] = 'active';
	}
}
?>
