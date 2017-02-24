<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('affix')) cls_message::show($re);
$types = array('email' => 'Email','pm' => '站内短信',);
if($action == 'splangsedit'){
	backnav('otherset','email');
	$ftype = empty($ftype) ? '' : $ftype;
	$splangs = fetch_arr($ftype);
	if(!submitcheck('bsplangsedit')) {
		tabheader('功能语言模板管理','','','7');
		trcategory(array('序号',array('功能语言名称','txtL'),'类型','详情'));
		$sn = 0;
		foreach($splangs as $slid => $splang){
			if(empty($ftype) || $ftype == $splang['type']){
			$sn ++;
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$sn</td>\n".
				"<td class=\"txtL\">".$splang['cname']."</td>\n".
				"<td class=\"txtC w120\">".$types[$splang['type']]."</td>\n".
				"<td class=\"txtC w40\"><a href=\"?entry=splangs&action=splangdetail&slid=$slid\" onclick=\"return floatwin('open_splang',this)\">编辑</a></td></tr>\n";
			}
		}
		tabfooter();
		a_guide('splangsedit');
	}
}elseif($action == 'splangdetail' && $slid){
	$splang = fetch_one($slid);
	if(!submitcheck('bsplangdetail')){
		tabheader('功能语言设置','splangsdetail',"?entry=splangs&action=splangdetail&slid=$slid");
		trbasic('功能语言名称','',$splang['cname'],'');
		trbasic('功能语言类型','',$types[$splang['type']],'');
		trbasic('功能语言内容','splangnew[content]',$splang['content'],'textarea',array('w' => 500,'h' => 300,));
		tabfooter('bsplangdetail');
		a_guide('splangdetail');
	}
	else{
		if(empty($splangnew['content'])) cls_message::show('资料不完全',M_REFERER);
		$db->query("UPDATE {$tblprefix}splangs SET content='$splangnew[content]' WHERE slid='$slid'");
		cls_CacheFile::Update('splangs');
		adminlog('详细修改功能语言');
		cls_message::show('功能语言修改完成',axaction(6,"entry=splangs&action=splangsedit"));
	}
}
function fetch_arr($type){
	global $db,$tblprefix;
	$items = array();
	$query = $db->query("SELECT * FROM {$tblprefix}splangs ".($type ? "WHERE type='$type'" : '')." ORDER BY vieworder,slid");
	while($item = $db->fetch_array($query)){
		$items[$item['slid']] = $item;
	}
	return $items;
}
function fetch_one($slid){
	global $db,$tblprefix;
	$item = $db->fetch_one("SELECT * FROM {$tblprefix}splangs WHERE slid='$slid'");
	return $item;
}

?>
