<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/adminm.fun.php";
$catalogs = cls_cache::Read('catalogs');
$chid = 106;
_header('在线提问');
?>
<style type="text/css">
	.msgbox a{display: none;}
	img#archive106{
		bottom:0;
	}
</style>
<?
if($ore = cls_Safefillter::refCheck('',0)){ // die("不是来自{$cms_abs}的请求！");
	cls_message::show('禁止外部网页提交');
}

$aid = empty($aid) ? 0 : max(0,intval($aid));
if(!($channel = cls_cache::Read('channel',$chid))) cls_message::show('请指定文档类型。');
if(!$memberid) cls_message::show('请先登陆会员。');
if($memberid==@$fmdata["tomid"]) cls_message::show('不能给自己提问啊！');
$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.rawurlencode($forward);
$fields = cls_cache::Read('fields',$chid);
$caid = empty($caid)?516:max(1,intval($caid));
$caid_arr = array($caid);
$cotypes = cls_cache::Read('cotypes');

if(!submitcheck('bsubmit')){
	$pre_cns = array();
	$pre_cns['caid'] = $caid;
	$tomid = empty($tomid)?'': max(0,intval($tomid));
	$pid = empty($pid)?'': max(0,intval($pid));
	foreach($cotypes as $k => $v) if(!$v['self_reg']) $pre_cns['ccid'.$k] = empty(${'ccid'.$k}) ? '' : trim(${'ccid'.$k});
	foreach($pre_cns as $k => $v) if(!$v) unset($pre_cns[$k]);
	if(!$curuser->allow_arcadd($chid,$pre_cns)) cls_message::show('您在所指定的栏目或分类中没有发表权限。');

	tabheader('','archiveadd',"?chid=$chid$forwardstr",2,1,1);
	tr_cns('所属栏目','fmdata[caid]',array('value' => $caid,'ids'=>$caid_arr,'chid' => $chid,'hidden' => !empty($pre_cns['caid']) ? 0 : 1,'notblank' => 1,));
	if($pid){
		trhidden('fmdata[pid]',$pid);
	}
	trhidden('fmdata[tomid]',$tomid);
	$a_field = new cls_field;
	$subject_table = atbl($chid);
	foreach($fields as $k => $field){
		if($field['available']){
			if($k!='currency'){
				$a_field->init($field);
				$a_field->isadd = 1;
				$a_field->trfield('fmdata');
			}else{
				$field['max']=$curuser->info['currency1'];
				$a_field->init($field);
				$curuserfield = $a_field->varr('fmdata');
				trbasic($curuserfield['trname'],'$curuserfield[varname]',$curuserfield['frmcell'],'',array('guide'=>'你当前可用的悬赏分为<font color="red">'.$curuser->info['currency1'].'分</font>'));
			}
		}
	}
	unset($a_field);
	tr_regcode("archive$chid");
	tabfooter('bsubmit','添加');
	_footer();
}else{
	if(!regcode_pass("archive$chid",empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误',axaction(2,M_REFERER));
	if(empty($fmdata['caid']) || !($catalog = @$catalogs[$fmdata['caid']])) cls_message::show('请指定正确的栏目',axaction(2,M_REFERER));
	$fmdata['currency'] > $curuser->info['currency1'] && cls_message::show('悬赏分不足。',axaction(2,M_REFERER));

	$pre_cns = array();
	$pre_cns['caid'] = $fmdata['caid'];
	//分析分类的定义及权限
	//内系自动设置
	$fmdata['ccid35'] = 3035;

	foreach($cotypes as $k => $v){
		if(!$v['self_reg'] && isset($fmdata["ccid$k"])){
			$fmdata["ccid$k"] = empty($fmdata["ccid$k"]) ? '' : $fmdata["ccid$k"];
			if($v['notblank'] && !$fmdata["ccid$k"]) cls_message::show("请设置 $v[cname] 分类",axaction(2,M_REFERER));
			if($fmdata["ccid$k"]) $pre_cns['ccid'.$k] = $fmdata["ccid$k"];
			if($v['emode']){
				$fmdata["ccid{$k}date"] = !cls_string::isDate($fmdata["ccid{$k}date"]) ? 0 : strtotime($fmdata["ccid{$k}date"]);
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
		foreach(array('salecp','fsalecp','ucid','tomid') as $k){
			isset($fmdata[$k]) && $arc->updatefield($k,trim($fmdata[$k]));
		}

		//设置提问有效时间
		$arc->auto();
		$arc->autocheck();
		$arc->updatedb();
		//这是楼盘内容页传过来的$fmdata['pid']，把问题合辑到楼盘
		if(!empty($fmdata['pid'])){
			$_pid = max(0,intval(@$fmdata['pid']));
			$db->query("INSERT INTO {$tblprefix}aalbums set arid='1',inid='$aid',pid='$_pid',incheck='1'");
		}

		$c_upload->closure(1,$aid);
		$c_upload->saveuptotal(1);

		$arc->autostatic();//最后才执行自动静态
		$curuser->updatecrids(array(1=>-$fmdata['currency']),$updatedb=1,$remark='提问悬赏分');
		?>
		<script type="text/javascript">
			if (window.parent.$('.jqiframe').length) {
				window.parent.$('.jqiframe').jqModal('hide');
				window.parent.$.jqModal.tip('文档添加完成', 'succeed');
			}
		</script>
		<?php
		cls_message::show('文档添加完成',axaction(1,M_REFERER));
	}else{
		$c_upload->closure(1);
		cls_message::show('添加文档失败',axaction(2,M_REFERER));
	}
}
?>
<script type="text/javascript">
	document.body.style.height = '420px';
	document.getElementById('_08_upload_inputIframe_fmdata[thumb]').parentNode.style.height = '144px';
</script>

