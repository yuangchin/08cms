<?PHP
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('farchive')) cls_message::show($re);

//只有指定分类才能添加
$fcaid = cls_fcatalog::InitID(@$fcaid);
if(!($fcatalog = cls_fcatalog::Config($fcaid))) cls_message::show('请指定副件分类');
if($re = $curuser->NoBackPmByTypeid($fcaid,'fcaid')) cls_message::show($re);# 当前副件分类的后台管理权限
if(!$curuser->pmbypmid($fcatalog['apmid'])) cls_message::show('您没有此分类的添加权限');
$fields = cls_fcatalog::Field($fcaid);

if(!submitcheck('bsubmit')){
	$a_field = new cls_field;
	$subject_table = 'farchives';
	tabheader($fcatalog['title'].'-信息发布','farchiveadd',"?entry=$entry$extend_str&fcaid=$fcaid",2,1,1);
	foreach($fields as $k => $v){
		$a_field->init($v);
		$a_field->isadd = 1;
		$a_field->trfield('fmdata');
	}
	unset($a_field);
	cls_fcatalog::areaShow($fcaid,'','Checkbox'); //选择地区
	if(empty($fcatalog['nodurat'])){
		trbasic('开始日期',"fmdata[startdate]",'','calendar',array('guide'=>'不限日期请留空','validate'=>makesubmitstr("fmdata[startdate]",0,0,0,0,'date')));
		trbasic('结束日期',"fmdata[enddate]",'','calendar',array('guide'=>'不限日期请留空','validate'=>makesubmitstr("fmdata[enddate]",0,0,0,0,'date')));
	}
    if(!in_array((int)$fcatalog['ftype'], array(1))) {
        trbasic('静态保存格式','fmdata[customurl]','','text',array('guide'=>'留空按所在分类设置。例:{$infodir}/{$aid}-{$page}.html，{$infodir}副件总目录，{$y}年 {$m}月 {$d}日 {$aid}副件id {$page}分页页码。','w'=>50));
    }

	tabfooter('bsubmit');
	a_guide('farchiveadd');
}else{
	$c_upload = cls_upload::OneInstance();
	$a_field = new cls_field;
	foreach($fields as $k => $v){
		if(isset($fmdata[$k])){
			$a_field->init($v);
			$fmdata[$k] = $a_field->deal('fmdata','cls_message::show',axaction(2,M_REFERER));
		}
	}
	unset($a_field);
	$arc = new cls_farcedit;
	if($aid = $arc->arcadd($fcatalog['chid'],$fcaid)){
		if(empty($fcatalog['nodurat'])){
			foreach(array('startdate','enddate') as $var){
				if(isset($fmdata[$var])){
					$fmdata[$var] = trim($fmdata[$var]);
					$fmdata[$var] = !cls_string::isDate($fmdata[$var]) ? 0 : strtotime($fmdata[$var]);
					$arc->updatefield($var,$fmdata[$var]);
				}
			}
		}
		if(isset($fmdata['customurl'])){
			$fmdata['customurl'] = preg_replace("/^\/+/",'',trim($fmdata['customurl']));
			$arc->updatefield('customurl',$fmdata['customurl']);
		}
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				$arc->updatefield($k,$fmdata[$k],$v['tbl']);
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $arc->updatefield($k.'_'.$x,$y,$v['tbl']);
			}
		}
		$ordernum  = 0;
		//(vieworder)最大排序
		$ordernum = $db->result_one("SELECT MAX(vieworder) AS ordernum FROM {$tblprefix}farchives");
		$arc->updatefield('vieworder',++$ordernum,'farchives');
		
		$areas = @$fmdata['farea']; //保存地区 
		$areas = @implode(',',$areas);
		$arc->updatefield('farea', $areas ,'farchives');
		
		$arc->arccolor();
		$arc->autocheck();
		$arc->updatedb();
		$c_upload->closure(1,$aid,'farchives');
		$c_upload->saveuptotal(1);
        _08_Advertising::cleanTag($fcaid);
		adminlog('添加副件信息');
		cls_message::show('副件信息添加完成',axaction(6,"?entry=$entry$extend_str&fcaid=$fcaid"));
	}else{
		$c_upload->closure(1);
		cls_message::show('信息添加失败',axaction(6,"?entry=$entry$extend_str&fcaid=$fcaid"));
	}
}


?>
