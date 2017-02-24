<?php
$cotypes = cls_cache::Read('cotypes');
$vcps = cls_cache::Read('vcps');
$catalogs = cls_cache::Read('catalogs');

//分析模型定义及权限
$chid = empty($chid) ? 0 : max(0,intval($chid));
if(!($channel = cls_cache::Read('channel',$chid))) cls_message::show('请指定文档类型。');
$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.rawurlencode($forward);
$fields = cls_cache::Read('fields',$chid);

if(!submitcheck('bsubmit')){
	$pre_cns = array();
	$pre_cns['caid'] = empty($caid) ? 0 : max(0,intval($caid));
	foreach($cotypes as $k => $v) if(!$v['self_reg']) $pre_cns['ccid'.$k] = empty(${'ccid'.$k}) ? '' : trim(${'ccid'.$k});
	if(($pid = empty($pid) ? 0 : max(0,intval($pid))) && $p_album = $db->fetch_one("SELECT * FROM {$tblprefix}".atbl($pid,2)." WHERE aid='$pid'")){
		//是否继承合辑的栏目及分类//这个只是文档内的合辑处理，如果是会员合辑另行处理
		#$pre_cns['caid'] = $p_album['caid'];
		foreach($cotypes as $k => $v) if(!$v['self_reg'] && $p_album['ccid'.$k]){
			#$pre_cns['ccid'.$k] = $p_album['ccid'.$k];
		}
	}else $pid = 0;

	foreach($pre_cns as $k => $v) if(!$v) unset($pre_cns[$k]);
	if(!$curuser->allow_arcadd($chid,$pre_cns)) cls_message::show('您在所指定的栏目或分类中没有发表权限。');
	$catalogs = cls_cache::Read('catalogs');

	tabheader($channel['cname'].'&nbsp; -&nbsp; 添加文档','archiveadd',"?action=archiveadd&chid=$chid$forwardstr",2,1,1);

	if($pid){//指定合辑内添加文档的信息提示
		trhidden('fmdata[pid]',$pid);
		trbasic('所属合辑','',"<a href=\"".cls_ArcMain::Url($p_album)."\" target=\"_blank\">".mhtmlspecialchars($p_album['subject'])."</a>",'');
	}
	tr_cns('所属栏目','fmdata[caid]',array('value' => @$pre_cns['caid'],'chid' => $chid,'hidden' => empty($pre_cns['caid']) ? 0 : 1,'notblank' => 1,));
	foreach($cotypes as $k => $v){
		if(!$v['self_reg']){
			tr_cns($v['cname'],"fmdata[ccid$k]",array('value' => empty($pre_cns['ccid'.$k]) ? 0 : $pre_cns['ccid'.$k],'coid' => $k,'chid' => $chid,'max' => $v['asmode'],'hidden' => empty($pre_cns['ccid'.$k]) ? 0 : 1,'notblank' => $v['notblank'],'emode' => $v['emode'],'evarname' => "fmdata[ccid{$k}date]",));
		}
	}

	$a_field = new cls_field;
	$subject_table = atbl($chid);
	foreach($fields as $k => $field){
		if($field['available']){
			$a_field->init($field);
			$a_field->isadd = 1;
			$a_field->trfield('fmdata');
		}
	}
	unset($a_field);
	
	trbasic('浏览文档售价','fmdata[salecp]',makeoption(array('' => '免费') + $vcps['sale']),'select');
	trbasic('附件操作售价','fmdata[fsalecp]',makeoption(array('' => '免费') + $vcps['fsale']),'select');
	//关于文档的个人分类
	$uclasses = cls_Mspace::LoadUclasses($curuser->info['mid']);
	$ucidsarr = array(0 => '请选择');
	foreach($uclasses as $k => $v) if(!$v['cuid']) $ucidsarr[$k] = $v['title'];
	trbasic('我的分类','fmdata[ucid]',makeoption($ucidsarr),'select');
	
	tr_regcode('archive');
	tabfooter('bsubmit','添加');
}else{
	if(!regcode_pass('archive',empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误',axaction(2,M_REFERER));
	if(empty($fmdata['caid']) || !($catalog = @$catalogs[$fmdata['caid']])) cls_message::show('请指定正确的栏目',axaction(2,M_REFERER));
	
	$pre_cns = array();
	$pre_cns['caid'] = $fmdata['caid'];
	//分析分类的定义及权限
	foreach($cotypes as $k => $v){
		if(!$v['self_reg'] && isset($fmdata["ccid$k"])){
			$fmdata["ccid$k"] = empty($fmdata["ccid$k"]) ? '' : $fmdata["ccid$k"];
			if($v['notblank'] && !$fmdata["ccid$k"]) cls_message::show("请设置 $v[cname] 分类",axaction(2,M_REFERER));
			if($fmdata["ccid$k"]) $pre_cns['ccid'.$k] = $fmdata["ccid$k"];
			if($v['emode']){
				$fmdata["ccid{$k}date"] = !cls_string::isDate($fmdata["ccid{$k}date"]) ? 0 : trim($fmdata["ccid{$k}date"]);
				!$fmdata["ccid$k"] && $fmdata["ccid{$k}date"] = 0;
				if($fmdata["ccid$k"] && !$fmdata["ccid{$k}date"] && $v['emode'] == 2) cls_message::show("请设置 $v[cname] 分类期限",axaction(2,M_REFERER));
			}
		}
	}
	if(!$curuser->allow_arcadd($chid,$pre_cns)) cls_message::show('没有发表权限',axaction(2,M_REFERER),'已指定类目');//分析类目组合的发表权限
	
	//////////////字段的预处理，异常分析
	$c_upload = new cls_upload;	
	$a_field = new cls_field;
	foreach($fields as $k => $v){
		$a_field->init($v);
		$fmdata[$k] = $a_field->deal('fmdata','mcmessage',axaction(2,M_REFERER));
	}
	unset($a_field);
	if(isset($fmdata['keywords'])) $fmdata['keywords'] = cls_string::keywords($fmdata['keywords']);//关键词预处理
	$arc = new cls_arcedit;
	if($aid = $arc->arcadd($chid,$fmdata['caid'])){
		foreach($cotypes as $k => $v){
			if(!$v['self_reg'] && !empty($fmdata["ccid$k"])){
				$arc->arc_ccid($fmdata["ccid$k"],$k,$v['emode'] ? $fmdata["ccid{$k}date"] : 0);
			}
		}
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				$arc->updatefield($k,$fmdata[$k],$v['tbl']);
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $arc->updatefield($k.'_'.$x,$y,$v['tbl']);
			}
		}
		foreach(array('salecp','fsalecp','ucid',) as $k){
			isset($fmdata[$k]) && $arc->updatefield($k,trim($fmdata[$k]));
		}
		$arc->auto();
		$arc->autocheck();
		$arc->updatedb();
		
		$c_upload->closure(1,$aid);
		$c_upload->saveuptotal(1);
		
		if(isset($fmdata['pid']) && !empty($fmdata['pid'])){
			$db->query("INSERT INTO {$tblprefix}aalbums set arid='1',inid = '$aid',pid = '$fmdata[pid]',incheck='1',shoudong='1'");
		}
		
		//if(!empty($fmdata['pid'])) $arc->set_album($fmdata['pid'],$arid);//归辑设置,与文档数据库无关
		$arc->autostatic();//最后才执行自动静态
		
		cls_message::show('周边添加完成',axaction(6,M_REFERER));
	}else{
		$c_upload->closure(1);
		cls_message::show('添加周边失败',axaction(2,M_REFERER));
	}
}
?>

