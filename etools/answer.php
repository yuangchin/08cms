<?
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."./include/adminm.fun.php";

$forward = empty($forward) ? M_REFERER : $forward;
$forwardstr = '&forward='.urlencode($forward);
$cuid = 37; $chid = 106;
$aid = empty($aid) ? 0 : max(0,intval($aid));
$cid = empty($cid) ? 0 : max(0,intval($cid));
$toaid = empty($toaid) ? 0 : max(0,intval($toaid));
$tocid = empty($tocid) ? 0 : max(0,intval($tocid));
if(empty($action)){	
	$mid = empty($mid)?$memberid:$mid;
	if(!$mid) cls_message::show('请先登陆会员。',$forward); 
	if(!$aid) cls_message::show('请指定需要回答的对象。',$forward);
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('问答功能已关闭。',$forward);
	$arc = new cls_arcedit;
	$arc->set_aid($aid,array('chid'=>$chid));
	$commu['chids'] = empty($commu['chids'])?array():$commu['chids'];
	if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])) cls_message::show('请指定需要回答的对象',$forward);
	if($arc->archive['mid']==$mid) cls_message::show('不能对自己的问题进行回答。');
	if($arc->archive['answercid']) cls_message::show('问题已经解决！');	
	$arc->archive['close'] && cls_message::show('问题已经关闭。');
	if(empty($commu['repeatanswer']) && $db->result_one("select cid from {$tblprefix}$commu[tbl] where aid='$aid' and mid='$mid'")) cls_message::show('不允许重复回答问题。',$forward);	
	
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		_header('我来回答问题');
		tabheader('我来回答','commuadd',"?aid=$aid&mid=$mid$forwardstr",2,1,1);
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			$a_field->init($v);
			$a_field->isadd = 1;
			$a_field->trfield('fmdata');
		}
		unset($a_field);
		tr_regcode("commu_$cuid");
		tabfooter('bsubmit');
		_footer();
	}else{//数据处理
		_header();
		if(!regcode_pass("commu_$cuid",empty($regcode) ? '' : trim($regcode))) cls_message::show('验证码错误',axaction(2,M_REFERER));
		if(!$curuser->pmbypmid($commu['pmid'])) cls_message::show('您没有回答权限。',axaction(2,M_REFERER));
		//处理托管情况下，回答问题的回答者并不是被托管的会员问题，mid通过链接在会员中心传递过来
		$mid = empty($mid)?$memberid:$mid;
		$sqlstr = "aid='$aid',ip='$onlineip',mid='$mid',mname='{$curuser->info['mname']}',createdate='$timestamp'";
		if($curuser->pmautocheck($commu['autocheck'],'cuadd')) $sqlstr .= ",checked=1";
		$c_upload = new cls_upload;	
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				$a_field->init($v);
				$fmdata[$k] = $a_field->deal('fmdata','mcmessage',axaction(2,M_REFERER));
				$sqlstr .= ",$k='$fmdata[$k]'";
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $sqlstr .= ",{$k}_x='$y'";
			}
		}
		unset($a_field);
		$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET $sqlstr");
		if($cid = $db->insert_id()){
			//记录回答数量。
			$db->query("update {$tblprefix }".atbl($chid)." set stat0=stat0+1 where aid='$aid'");
			$c_upload->closure(1,$cid,"commu_$cuid");
			$c_upload->saveuptotal(1);
			cls_cubasic::setCridsOuter($cuid);
			cls_message::show('回答成功。',axaction(6,$forward));
		}else{
			$c_upload->closure(1);
			cls_message::show('回答不成功。',axaction(10,$forward));
		}
	}
		
}elseif($action == 'vote'){
	$inajax = empty($inajax) ? 0 : 1;
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('问答功能已关闭。',$forward);
	if(!$cid || !$db->result_one("SELECT cid FROM {$tblprefix}$commu[tbl] WHERE cid='$cid'")) cls_message::show('请选择正确的操作对象',$forward);
	if(empty($m_cookie["08cms_cuid_{$cuid}_vote_$cid"])){
		msetcookie("08cms_cuid_{$cuid}_vote_$cid",1,365 * 86400);
	}else cls_message::show('您已经投过票了。',$forward);
	$opt = empty($opt) ? 1 : min(2,max(1,intval($opt)));
	$db->query("UPDATE {$tblprefix}$commu[tbl] SET opt$opt = opt$opt + 1 WHERE cid='$cid'");
	cls_message::show($inajax ? 'succeed' : '投票成功。',$forward);
}elseif($action == 'ok'){
	$inajax = empty($inajax) ? 0 : 1;
	$chid = 106;
	if(!$aid) cls_message::show('请指定需要操作的问题。',$forward);
	$arc = new cls_arcedit;
	$arc->set_aid($aid,array('chid'=>$chid,'ch'=>1)); //print_r($arc); die();
	if(!$memberid) cls_message::show('请先登陆会员。',$forward); 
	if($arc->archive['mid'] != $memberid) cls_message::show('您没有操作权限！',$forward);
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('问答功能已关闭。',$forward);
	if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])) cls_message::show('请指定需要操作的问题。',$forward);
	($arc->archive['close']) && cls_message::show('悬赏操作失败，问题已经关闭。',$forward);
	if(!$cid || !$tomid = $db->result_one("select mid from {$tblprefix}$commu[tbl] where cid='$cid'")) cls_message::show('请指定悬赏的对象。',$forward);
	if($arc->archive['answercid'] || $arc->archive['ccid35'] == 3036) cls_message::show('悬赏操作失败，已经悬赏过。',$forward);#问题有最佳答案或是已经解决的问题时。
	//更新交互记录
	$db->query("UPDATE {$tblprefix}$commu[tbl] SET isanswer = '1' WHERE cid ='$cid'");
	//给指定会员加分
	if($tomid && $arc->archive['currency']){
		$crids = array(1=>$arc->archive['currency']);
		$tocuruser = new cls_userinfo;
		$tocuruser->activeuser($tomid);
		$tocuruser->updatecrids($crids,1,'被选为最佳答案');
		unset($tocuruser);
	}
	$arc->updatefield('answercid',$cid);
	//更改文档状态
	$arc->arc_ccid(3036,35);//设置已经解决
    $arc->updatefield('finishdate',$timestamp);
	$arc->updatedb();
	cls_message::show($inajax ? 'succeed' : '设定最佳答案成功。',$forward);
	unset($arc);
}elseif($action == 'supplementary'){
	$inajax = empty($inajax) ? 0 : 1;
	if(!$memberid) cls_message::show('请先登陆会员。',$forward);
	if(!$aid) cls_message::show('请指定需要操作的问题。',$forward);
	$arc = new cls_arcedit;
	$arc->set_aid($aid,array('chid'=>$chid,'ch'=>1));
	if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('问答功能已关闭。',$forward);
	if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])) cls_message::show('请指定需要操作的问题。',$forward);
	($arc->archive['close']) && cls_message::show('操作失败，问题已经关闭。',$forward);
	if($arc->archive['answercid'] || $arc->archive['ccid35'] == 3036) cls_message::show('操作失败，问题已经解决！',$forward);
	//问答文章的判断
	if($tocid){	//注意判断当前补充的人是否正确！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！	
		if(!$tomid = $db->result_one("select mid from {$tblprefix}$commu[tbl] where cid='$tocid'")) cls_message::show('请指定回复或补充的对象。',$forward);
		if(!($memberid == $tomid || $memberid == $arc->archive['mid'])) cls_message::show('你没有补充或追问的操作权限！',$forward);
		if(!empty($selzw)){
			if(empty($fmdata['content'])) cls_message::show('内容不能为空。',$forward);
			$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET aid='$aid',tocid='$tocid',content='$fmdata[content]',ip='$onlineip',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp'".($curuser->pmautocheck($commu['autocheck'],'cuadd')?',checked=1':'')."");				
		}
		cls_message::show($inajax ? 'succeed' : '问题操作成功。',$forward);
	}elseif($toaid){
		$memberid != $arc->archive['mid'] && cls_message::show('操作失败，你没有该问题的操作权限！',$forward);
		if(!empty($addreward)){
			$rewardpoints < 0 && cls_message::show('追加积分失败，追加不能为负数！',$forward);
			$curuser->detail_data();
			$crids = empty($rewardpoints) ? array():array(1=>-$rewardpoints);
			if($curuser->info['currency1'] - $rewardpoints < 0) cls_message::show('你没有足够的积分！',$forward);
			$curuser->updatecrids($crids,1,'追加悬赏分');
			$arc->updatefield('currency',$rewardpoints+$arc->archive['currency'],"archives_$chid");
			$arc->updatedb();
		}
		if(!empty($added)){
			if(empty($fmdata['content'])) cls_message::show('补充问题不能为空。',$forward);
			$db->query("INSERT INTO {$tblprefix}$commu[tbl] SET aid='$aid',toaid='$aid',content='$fmdata[content]',ip='$onlineip',mid='$memberid',mname='{$curuser->info['mname']}',createdate='$timestamp'".($curuser->pmautocheck($commu['autocheck'],'cuadd')?',checked=1':'')."");
		}
		cls_message::show($inajax ? 'succeed' : '操作成功。',$forward);
		unset($arc);
	}
}

?>

