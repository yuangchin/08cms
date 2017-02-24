<?php
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
if(!empty($run_inajax)){
    define('_08CMS_AJAX_EXEC', true);
}
$action = empty($action) ? 'vote' : $action;
if(empty($fname)){
	if(empty($vid) || !($vote = $db->fetch_one("SELECT * FROM {$tblprefix}votes WHERE vid='$vid' AND checked=1 AND (enddate=0 OR enddate>$timestamp)"))) cls_message::show('请指定正确的投票项目!');
	if($action == 'vote'){
		empty($vopids) && cls_message::show('请选择投票选项!');
		if($vote['enddate'] && $vote['enddate'] < $timestamp) cls_message::show('无效投票项目!',M_REFERER);
		if($vote['onlyuser'] && !$memberid) cls_message::show('游客无操作权限',M_REFERER);
		if($vote['norepeat'] || $vote['timelimit']){
			if(empty($m_cookie['voted_'.$vid.'_timelimit'])){
				msetcookie('voted_'.$vid.'_timelimit','1',$vote['norepeat'] ? 365 * 24 * 3600 : $vote['timelimit'] * 60);
			}else cls_message::show($vote['norepeat'] ? '请不要重复操作！' : '操作过于频繁！',M_REFERER);
		}
		foreach($vopids as $vopid) $db->query("UPDATE {$tblprefix}voptions SET votenum=votenum+1 WHERE vopid='$vopid'");
		//将总票数写入投票数据库
		$counts = $db->result_one("SELECT SUM(votenum) FROM {$tblprefix}voptions WHERE vid='$vid'");
		$db->query("UPDATE {$tblprefix}votes SET totalnum='$counts' WHERE vid='$vid'");
		cls_message::show('投票成功!',M_REFERER);
	}elseif($action == 'view'){
		$temparr = array('vid' => $vid);
		mexit(cls_tpl::SpecialHtml('vote',$temparr));
	}
}else{
	empty($vopids) && cls_message::show('请选择投票选项!',M_REFERER);
	$fname = empty($fname) ? '' : strip_tags(trim($fname));//字段名称
	$tbl = $type = empty($type) ? 'archives' : strip_tags(trim($type));
	$id = empty($id) ? 0 : max(0,intval($id));//记录id
	if(!($item = cls_field::field_votes($fname,$type,$id,0)) || !($votes = @unserialize($item[$fname]))) cls_message::show('请指定正确的投票项目!',M_REFERER);
	$arr = array(
		'archives' => array('fields','aid','chid'),
		'members' => array('mfields','mid','mchid'),
		'farchives' => array('ffields','aid','chid'),
		'catalogs' => array('cafields','caid',''),
		'coclass' => array('ccfields','ccid',''),
		'offers' => array('ofields','cid',''),
		'replys' => array('rfields','cid',''),
		'comments' => array('cfields','cid',''),
		'mcfields' => array('mcomments','cid',''),
		'mrfields' => array('mreplys','cid',''),
		);
	$typeid = $arr[$type][2] ? $item[$arr[$type][2]] : '';
	$fields = cls_cache::Read($arr[$type][0],$typeid);
	if(!($field = @$fields[$fname]) || $field['datatype'] != 'vote') cls_message::show('请指定正确的投票项目!');
	if($type == 'archives' && !$field['iscommon']){
		$tbl = $type."_$typeid";
	}elseif($type == 'members'){
		$tbl = $type.($field['iscommon'] ? '_sub' : "_$typeid");
	}elseif($type == 'farchives'){
		$tbl = $type."_$typeid";
	}
	if($field['nohtml'] && !$memberid) cls_message::show('游客无操作权限',M_REFERER);
	if($field['mode'] || $field['length']){
		if(empty($m_cookie['voted_'.$type.$id.'_'.$fname.'_timelimit'])){
			msetcookie('voted_'.$type.$id.'_'.$fname.'_timelimit','1',$field['mode'] ? 365 * 24 * 3600 : $field['length'] * 60);
		}else cls_message::show($field['mode'] ? '请不要重复操作！' : '操作过于频繁！',M_REFERER);
	}
	$valid0 = false;
	foreach($vopids as $vid => $opids){
		if(!($vote = @$votes[$vid]) || ($vote['enddate'] && $vote['enddate'] < $timestamp)) continue;
		$valid = false;
		foreach($opids as $opid){
			if(isset($vote['options'][$opid])){
				$vote['options'][$opid]['votenum'] = @$vote['options'][$opid]['votenum'] + 1;
				$valid = true;
			}
		}
		if(!empty($valid)){
			$vote['totalnum'] = 0;
			foreach($vote['options'] as $v) $vote['totalnum'] += @$v['votenum'];
			$votes[$vid] = $vote;
			$valid0 = true;

		}
	}
	$valid0 && $db->query("UPDATE {$tblprefix}$tbl SET $fname='".addslashes(serialize($votes))."' WHERE ".$arr[$type][1]."='$id'",'SILENT');
	cls_message::show('投票成功!',M_REFERER);
}
?>