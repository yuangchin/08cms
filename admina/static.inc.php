<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('static')) cls_message::show($re);
foreach(array('cotypes','channels','currencys','permissions','cntpls','mcntpls','catalogs','mtpls','cnodes','splitbls','static_process','grouptypes') as $k) $$k = cls_cache::Read($k);
$transtr = empty($transtr) ? '' : $transtr;
if($action == 'index'){
	backnav('static','index');
	if(!submitcheck('bstatic') && !submitcheck('bclear')){
		tabheader('首页静态','staticindex',"?entry=$entry&action=$action");
		$ptypearr = array('i' => '系统首页','m' => '会员频道首页');
		trbasic('选择首页类型','',makecheckbox('ptypes[]',$ptypearr,array('i','m')),'');
		$indexfile = M_ROOT.cls_url::m_parseurl(idx_format(),array('page' => 1));
		$str = ($fm = @filemtime($indexfile)) ? '静态更新：'.date('Y-m-d H:i',$fm) : '尚未生成静态';
		$str .= " &nbsp;<a href=\"$cms_abs\" target=\"_blank\">>>浏览</a>";
		trbasic('系统首页状态','',$str,'');
		$indexfile = M_ROOT.cls_url::m_parseurl(cls_node::mcn_format(),array('page' => 1));
		$str = ($fm = @filemtime($indexfile)) ? '静态更新：'.date('Y-m-d H:i',$fm) : '尚未生成静态';
		$str .= " &nbsp;<a href=\"$memberurl\" target=\"_blank\">>>浏览</a> &nbsp;";
		trbasic('会员频道首页','',$str,'');
		tabfooter();
		echo "<input class=\"button\" type=\"submit\" name=\"bstatic\" value=\"生成静态\"> &nbsp; &nbsp;";
		echo "<input class=\"button\" type=\"submit\" name=\"bclear\" value=\"解除静态\"> &nbsp; &nbsp;";
	}elseif(submitcheck('bstatic')){
		if(empty($ptypes)) cls_message::show('请选择首页类型！',"?entry=$entry&action=$action");
		$msg = '';
		if(in_array('i',$ptypes)) $msg .= '<br>系统首页：'.cls_CnodePage::Create(array('inStatic' => true));
		if(in_array('m',$ptypes)) $msg .= '<br>会员频道首页：'.cls_McnodePage::Create(array('inStatic' => true));
		adminlog('生成首页静态');
		cls_message::show($msg ? $msg : '未执行任何操作。',"?entry=$entry&action=$action");
	}elseif(submitcheck('bclear')){
		if(empty($ptypes)) cls_message::show('请选择首页类型！',"?entry=$entry&action=$action");
		$msg = '';
		if(in_array('i',$ptypes)) $msg .= '<br>系统首页：'.cls_cnode::UnStaticIndex();
		if(in_array('m',$ptypes)) $msg .= '<br>会员频道首页：'.cls_mcnode::UnStaticIndex();
		adminlog('解除首页静态');
		cls_message::show($msg ? $msg : '未执行任何操作。',"?entry=$entry&action=$action");
	}
}elseif($action == 'archives') {
	backnav('static','archives');
	if(empty($enablestatic)) cls_message::show('静态模式未开启');
	$nsplitbls = array();
	foreach($splitbls as $k => $v) empty($v['nostatic']) && $nsplitbls[$k] = $v;
	if(!($stid = empty($stid) ? first_id($nsplitbls) : $stid) || empty($nsplitbls[$stid])) cls_message::show('请指定文档类型');
	$pagefrom = empty($pagefrom) ? 0 : max(0,intval($pagefrom));
	$pageto = empty($pageto) ? 0 : max(0,intval($pageto));
	$aidfrom = empty($aidfrom) ? 0 : max(0,intval($aidfrom));
	$aidto = empty($aidto) ? 0 : max(0,intval($aidto));
	$debugmode = empty($debugmode) ? 0 : 1;
	$numperpic = empty($numperpic) ? 20 : min(50,max(10,intval($numperpic)));
	$caid = empty($caid) ? '0' : $caid;
	$kpmode = empty($kpmode) ? '0' : $kpmode;
	$indays = empty($indays) ? 0 : max(0,intval($indays));
	$outdays = empty($outdays) ? 0 : max(0,intval($outdays));

	$ntbl = atbl($stid,1);
	$fromsql = "FROM {$tblprefix}$ntbl a";
	$wheresql = "WHERE a.checked='1'";
	if(!empty($caid)){
		if($cnsql = cnsql(0,sonbycoid($caid),'a.')) $wheresql .= " AND $cnsql";
	}
	$indays && $wheresql .= " AND a.createdate>'".($timestamp - 86400 * $indays)."'";
	$outdays && $wheresql .= " AND a.createdate<'".($timestamp - 86400 * $outdays)."'";
	$aidfrom && $wheresql .= " AND a.aid>='$aidfrom'";
	$aidto && $wheresql .= " AND a.aid<='$aidto'";
	$filterstr = '';
	foreach(array('kpmode','pagefrom','pageto','aidfrom','aidto','debugmode','numperpic','caid','stid','indays','outdays',) as $k){
		$filterstr .= "&$k=".rawurlencode($$k);
	}
	$_total = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$_pics = @ceil($_total / $numperpic);
	if(!submitcheck('bsubmit')){
		tabheader("筛选文档&nbsp; &nbsp; >><a href=\"?entry=$entry&action=archivesurl\" onclick=\"return floatwin('open_staticurl',this)\">快速修复链接</a>",'archives',"?entry=$entry&action=$action");
		$stidsarr = array();foreach($nsplitbls as $k => $v) $stidsarr[$k] = $v['cname'];
		trbasic('文档类型','',makeradio('stid',$stidsarr,$stid,8),'');
		trrange('文档id范围',array('aidfrom',$aidfrom,'',' - ',8),array('aidto',$aidto,'','',8),'text');
		trrange('添加日期',array('outdays',empty($outdays) ? '' : $outdays,'','&nbsp; 天前'.'&nbsp; -&nbsp; ',5),array('indays',empty($indays) ? '' : $indays,'','&nbsp; 天内',5));
		tr_cns('所属栏目','caid',array('value' => $caid,'framein' => 1,));
		trbasic('文档个数/批次','numperpic',$numperpic,'text',array('guide' => '可选范围10-50，建议设为20，多批次将自动连续执行。','w' => 5));
		tabfooter();
		echo "<input class=\"button\" type=\"submit\" name=\"bfilter\" value=\"筛选\"> &nbsp; &nbsp;";

		tabheader("内容页静态 (共{$_total}个 {$_pics}批)");
		$kpmodearr = array('1' => '保持原URL','0' => '按新规则更新URL');
		trbasic('URL处理方式','',makeradio('kpmode',$kpmodearr,$kpmode),'',array('guide' => '运营中系统建议保持原URL(仅之前未静态的文档才使用新规则)，新上线系统建议使用按新规则更新'));
		trrange('批次范围选择<br>共 '.$_pics.' 批',array('pagefrom',$pagefrom ? $pagefrom : '','',' - ',5),array('pageto',$pageto ? $pageto : '','','',5),'text','输入示例：从第2批到第5批，留空为不限。');
		trbasic('启用断点调试','debugmode',$debugmode,'radio',array('guide' => '方便查看每批次的静态生成状况，多批次不自动连续执行'));

		tabfooter('bsubmit','执行');
	#	a_guide('staticarchives');

	}else{
		$npage = empty($npage) ? 1 : $npage;
		if(empty($pages)) $pages = $_pics;
		if(empty($pages)) cls_message::show('请选择文档',"?entry=$entry&action=$action$filterstr");
		if($pagefrom && $pageto && $pageto < $pagefrom) cls_message::show('页码范围输入有误',"?entry=$entry&action=$action$filterstr");
		$pagefrom && $npage = max($npage,$pagefrom);
		$pageto && $pages = min($pages,$pageto);
		$npage = min($npage,$pages);

		$selectid = array();
		$fromstr = empty($fromid) ? "" : "a.aid<$fromid";
		$offsetfrom = empty($fromid) ? ($pagefrom ? ($pagefrom-1)*$numperpic : 0) : 0;
		$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
		$query = $db->query("SELECT aid $fromsql $nwheresql ORDER BY a.aid DESC LIMIT $offsetfrom,$numperpic");
		while($item = $db->fetch_array($query)) $selectid[] = $item['aid'];

		$initno = ($npage - 1) * $numperpic;
		$str = "<br><b>文档内容页静态:</b> $numperpic/批 第 $npage 批 共 $pages 批 &nbsp;>><a href=\"?entry=$entry&action=$action$filterstr\">返回</a>";

		$nextpage  = $npage + 1;
		$nexturl = "?entry=$entry&action=$action$filterstr$transtr&pages=$pages&npage=$nextpage&bsubmit=1&fromid=".min($selectid);
		$thisurl = "?entry=$entry&action=$action$filterstr$transtr&pages=$pages&npage=$npage&bsubmit=1&fromid=".(empty($fromid) ? 0 : $fromid);
		if($debugmode){
			$str .= " &nbsp;>><a href=\"$thisurl\">重来</a>";
			if($nextpage <= $pages) $str .= " &nbsp;>><a href=\"$nexturl\">下一批</a>";
		}
		static_process('body',$str);

		$arc = new cls_arcedit;
		foreach($selectid as $aid){
			$initno ++;
			$arc->set_aid($aid,array('au'=>0));
			for($k = 0;$k <= @$arc->arc_tpl['addnum'];$k++){
				$re = $arc->tostatic($k,$kpmode);
				static_process('msg',str_pad($k ? '' : $initno,$k ? 10+strlen($initno) : 10,'.').'aid:'.str_pad($aid,10,'.').'附'.$k.'.....'.$re);
			}
		}
		unset($arc);

		if($debugmode){
			exit();
		}else{
			if($nextpage <= $pages) static_process('jump',$nexturl);
			static_process('hide');
			adminlog('文档静态管理','文档列表管理操作');
			cls_message::show('静态操作完成',"?entry=$entry&action=$action$filterstr");
		}
	}
}elseif($action == 'archivesurl') {
	echo "<title>快速修复文档静态链接</title>";
	if(empty($enablestatic)) cls_message::show('静态模式未开启');
	$stidsarr = array();
	foreach($splitbls as $k => $v){
		if(empty($v['nostatic'])) $stidsarr[$k] = $v['cname'];
	}
	if(empty($stidsarr)) cls_message::show('所有的文档类型都不需要静态');	
	
	if(!submitcheck('bsubmit')){
		tabheader("快速修复文档静态链接",'archives',"?entry=$entry&action=$action");
		trbasic('选择文档类型<br><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,\'stids\',\'chkall\')">全选','',makecheckbox('stids[]',$stidsarr,array_keys($stidsarr),8),'');
		trbasic('URL修复方式','',makeradio('kpmode',array(0 => '保持原URL',1 => '按新规则更新URL')),'');
		tabfooter('bsubmit','执行');
		a_guide('<li>通常系统新开启静态模式时，在未主动静态的情况下，前台会出现找不到文件的情况(400错误)
		<li>可通过快速修复链接，使前台可按静态URL正常访问页面',true);
	}else{
		if(!empty($stids)){
			if(!is_array($stids)){
				$stids = explode(',',$stids);
			}
			$stids = array_filter($stids);
		}
		if(empty($stids)) cls_message::show('链接修复完毕。',"?entry=$entry&action=$action");
		$page = empty($page) ? 1 : max(1,intval($page));
		$kpmode = empty($kpmode) ? 0 : 1;
		$ostids = $stids;
		
		if($stid = array_shift($stids)){
			$_keepid = _archive_url($stid,$page,$kpmode);
		}
		if($_keepid){//继续相同类型文档($stid保持不变)
			$stids = $ostids;
			$page ++;
		}else{//切换成下一种类型文档($stid需要变化)
			$page = 1;
		}
		if(empty($stids)){
			cls_message::show('链接修复完毕。',"?entry=$entry&action=$action");
		}else{
			$num = count($stids);
			$stids = implode(',',$stids);
			cls_message::show("正在执行 {$splitbls[$stid]['cname']} 的第 <b>{$page}</b> 页。<br>还有 <b>{$num}</b> 种类型的文档需要修复，请耐心等待。","?entry=$entry&action=$action&stids=$stids&page=$page&kpmode=$kpmode&bsubmit=1");
		}
	}
}elseif($action == 'cnodes'){
	backnav('static','cnodes');
	if(empty($enablestatic)) cls_message::show('静态模式未开启');
	$pagefrom = empty($pagefrom) ? 0 : max(0,intval($pagefrom));
	$pageto = empty($pageto) ? 0 : max(0,intval($pageto));
	$debugmode = empty($debugmode) ? 0 : 1;
	$numperpic = empty($numperpic) ? 20 : min(50,max(10,intval($numperpic)));
	$caid = !isset($caid)? '0' : max(-1,intval($caid));
	$cnlevel = max(0,intval(@$cnlevel));
	$tid = !isset($tid)? 0 : max(0, intval($tid));
	$viewdetail = empty($viewdetail) ? '0' : $viewdetail;

	$fromsql = "FROM {$tblprefix}cnodes";
	$wheresql = " WHERE closed=0";
	$cnlevel && $wheresql .= " AND cnlevel='$cnlevel'";
	$tid && $wheresql .= " AND tid='$tid'";
	if(!empty($caid)){
		if($caid == -1){
			$wheresql .= " AND caid<>0";
		}else $wheresql .= " AND caid ".multi_str(sonbycoid($caid));
	}
	$filterstr = '';
	foreach(array('pagefrom','pageto','debugmode','numperpic','caid','cnlevel','tid','viewdetail',) as $k) $filterstr .= "&$k=".rawurlencode($$k);
	foreach($cotypes as $k => $v){
		if($v['sortable']){
			${"ccid$k"} = isset(${"ccid$k"}) ? max(-1,intval(${"ccid$k"})) : 0;
			if(!empty(${"ccid$k"})){
				if(${"ccid$k"} == -1){
					$wheresql .= " AND ccid$k<>0";
				}else{
					$wheresql .= " AND ccid$k ".multi_str(sonbycoid(${"ccid$k"},$k));
				}
				${"ccid$k"} && $filterstr .= "&ccid$k=".${"ccid$k"};
			}
		}
	}
	$_total = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$_pics = @ceil($_total / $numperpic);
	if(!submitcheck('bsubmit')){
		tabheader("筛选类目节点".viewcheck(array('name' => 'viewdetail','value' =>$viewdetail,'body' =>$actionid.'tbodyfilter',))."更多条件&nbsp; &nbsp; >><a href=\"?entry=$entry&action=cnodesurl\" onclick=\"return floatwin('open_staticurl',this)\">快速修复链接</a>",'archives',"?entry=$entry&action=$action");
		$arr = array('0' => '不限',);foreach($cntpls as $k => $v) $arr[$k] = $v['cname'];
		trbasic('节点配置','tid',makeoption($arr,$tid),'select');
		trbasic('栏目','caid',makeoption(array('0' => '不限','-1' => '全部') + cls_catalog::ccidsarr(0),$caid),'select');
		trbasic('节点交叉','cnlevel',makeoption(array('0'=>'不限','1'=>'单重节点','2'=>'双重交叉','3'=>'三重交叉','4'=>'四重交叉'),$cnlevel),'select');
		echo "<tbody id=\"{$actionid}tbodyfilter\" style=\"display:".($viewdetail ? '' : 'none')."\">";
		foreach($cotypes as $k => $v){
			if($v['sortable']) trbasic($v['cname'],"ccid$k",makeoption(array('0' => '不限','-1' => '全部') + cls_catalog::ccidsarr($k),${"ccid$k"}),'select');
		}
		echo "</tbody>";
		trbasic('节点数量/批次','numperpic',$numperpic,'text',array('guide' => '可选范围10-50，系统默认为20。','w' => 5));
		tabfooter();
		echo "<input class=\"button\" type=\"submit\" name=\"bfilter\" value=\"筛选\"> &nbsp; &nbsp;";

		tabheader("类目节点页静态  ({$_total}个 {$_pics}批)");
		trrange('批次范围选择<br>共 '.$_pics.' 批',array('pagefrom',$pagefrom ? $pagefrom : '','',' - ',5),array('pageto',$pageto ? $pageto : '','','',5),'text','输入示例：从第2批到第5批，留空为不限。');
		trbasic('启用断点调试','debugmode',$debugmode,'radio',array('guide' => '方便查看每批次的静态生成状况，多批次不自动连续执行'));
		tabfooter('bsubmit','执行');
#		a_guide('staticcnotes');
	}else{
		$npage = empty($npage) ? 1 : $npage;
		if(empty($pages)) $pages = @ceil($_total / $numperpic);
		if(empty($pages)) cls_message::show('请选择类目节点',"?entry=$entry&action=$action$filterstr");
		if($pagefrom && $pageto && $pageto < $pagefrom) cls_message::show('页码范围输入有误',"?entry=$entry&action=$action$filterstr");
		$pagefrom && $npage = max($npage,$pagefrom);
		$pageto && $pages = min($pages,$pageto);
		$npage = min($npage,$pages);

		$cnstrarr = $selectid = array();
		$fromstr = empty($fromid) ? "" : "cnid<$fromid";
		$offsetfrom = empty($fromid) ? ($pagefrom ? ($pagefrom-1)*$numperpic : 0) : 0;
		$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
		$query = $db->query("SELECT cnid,ename,tid $fromsql $nwheresql ORDER BY cnid DESC LIMIT $offsetfrom,$numperpic");
		while($item = $db->fetch_array($query)){
			$selectid[] = $item['cnid'];
			$cnstrarr[$item['ename']] = $item['tid'];
		}

		$initno = ($npage - 1) * $numperpic;
		$str = "<br><b>类目节点页静态:</b> $numperpic/批 第 $npage 批 共 $pages 批 &nbsp;>><a href=\"?entry=$entry&action=$action$filterstr\">返回</a>";
		$nextpage  = $npage + 1;
		$nexturl = "?entry=$entry&action=$action$filterstr$transtr&pages=$pages&npage=$nextpage&bsubmit=1&fromid=".min($selectid);
		$thisurl = "?entry=$entry&action=$action$filterstr$transtr&pages=$pages&npage=$npage&bsubmit=1&fromid=".(empty($fromid) ? 0 : $fromid);
		if($debugmode){
			$str .= " &nbsp;>><a href=\"$thisurl\">重来</a>";
			if($nextpage <= $pages) $str .= " &nbsp;>><a href=\"$nexturl\">下一批</a>";
		}
		static_process('body',$str);

		foreach($cnstrarr as $cnstr => $v){
			if(!empty($cntpls[$v])){
				$addnum = empty($cntpls[$v]['addnum']) ? 0 : $cntpls[$v]['addnum'];
				for($k = 0;$k <= $addnum;$k++){
					$re = cls_CnodePage::Create(array('cnstr' => $cnstr,'addno' => $k,'inStatic' => true));
					static_process('msg',str_pad($k ? '' : $initno,$k ? 10+strlen($initno) : 10,'.').str_pad($cnstr,40,'.').'附'.$k.'.....'.$re);
				}
				$initno ++;
			}
		}
		if($debugmode){
			exit();
		}else{
			if($nextpage <= $pages) static_process('jump',$nexturl);
			static_process('hide');
			adminlog('类目节点静态管理','节点列表管理操作');
			cls_message::show('类目节点操作完成',"?entry=$entry&action=$action$filterstr");
		}
	}
}elseif($action == 'cnodesurl') {
	echo "<title>快速修复节点静态链接</title>";
	if(empty($enablestatic)) cls_message::show('静态模式未开启');
	
	if(!submitcheck('bsubmit')){
		tabheader("快速修复节点静态链接",'archives',"?entry=$entry&action=$action");
		trbasic('URL修复方式','',makeradio('kpmode',array(0 => '补全缺少页面',1 => '全部页面重写')),'');
		tabfooter('bsubmit','执行');
		a_guide('<li>通常系统新开启静态模式时，在未主动静态的情况下，前台会出现找不到文件的情况(400错误)
		<li>可通过快速修复链接，使前台可按静态URL正常访问页面',true);
	}else{
		$page = empty($page) ? 1 : max(1,intval($page));
		$kpmode = empty($kpmode) ? 0 : 1;
		$_continue = _nodes_url('cnodes',$page,$kpmode);
		
		if($_continue){//继续下一页
			$page ++;
			cls_message::show("正在执行第 <b>$page</b> 页。请耐心等待。","?entry=$entry&action=$action&page=$page&kpmode=$kpmode&bsubmit=1");
		}else{//全部完成
			cls_message::show('链接修复完毕。',"?entry=$entry&action=$action");
		}
	}
}elseif($action == 'mcnodes'){
	backnav('static','mcnodes');
	if(empty($enablestatic)) cls_message::show('静态模式未开启');
	$pagefrom = empty($pagefrom) ? 0 : max(0,intval($pagefrom));
	$pageto = empty($pageto) ? 0 : max(0,intval($pageto));
	$debugmode = empty($debugmode) ? 0 : 1;
	$numperpic = min(500,max(20,intval(@$numperpic)));
	$mcnvar = trim(@$mcnvar);
	$tid = !isset($tid)? 0 : max(0, intval($tid));

	$fromsql = "FROM {$tblprefix}mcnodes";
	$wheresql = "WHERE closed=0";
	$mcnvar && $wheresql .= " AND mcnvar='$mcnvar'";
	$tid && $wheresql .= " AND tid='$tid'";

	$filterstr = '';
	foreach(array('pagefrom','pageto','debugmode','numperpic','mcnvar',) as $k) $filterstr .= "&$k=".rawurlencode($$k);
	$_total = $db->result_one("SELECT count(*) $fromsql $wheresql");
	$_pics = @ceil($_total / $numperpic);
	if(!submitcheck('bsubmit')){
		tabheader("筛选会员节点&nbsp; &nbsp; >><a href=\"?entry=$entry&action=mcnodesurl\" onclick=\"return floatwin('open_staticurl',this)\">快速修复链接</a>",'archives',"?entry=$entry&action=$action");
		$arr = array('0' => '不限',);foreach($mcntpls as $k => $v) $arr[$k] = $v['cname'];
		trbasic('节点配置','tid',makeoption($arr,$tid),'select');
		$mcnvars = array('' => '全部类型','caid' => '栏目');
		foreach($cotypes as $k => $v) !$v['self_reg'] && $mcnvars['ccid'.$k] = $v['cname'];
		foreach($grouptypes as $k => $v) !$v['issystem'] && $mcnvars['ugid'.$k] = $v['cname'];
		$mcnvars['mcnid'] = '自定义节点';
		trbasic('节点类型','mcnvar',makeoption($mcnvars,$mcnvar),'select');
		trbasic('节点数量/批次','numperpic',$numperpic,'text',array('guide' => '可选范围10-50，系统默认为20。','w' => 5));
		tabfooter();
		echo "<input class=\"button\" type=\"submit\" name=\"bfilter\" value=\"筛选\"><br />";

		tabheader("会员节点页静态  ({$_total}个 {$_pics}批)");
		trrange('批次范围选择<br>共 '.$_pics.' 批',array('pagefrom',$pagefrom ? $pagefrom : '','',' - ',5),array('pageto',$pageto ? $pageto : '','','',5),'text','输入示例：从第2批到第5批，留空为不限。');
		trbasic('启用断点调试','debugmode',$debugmode,'radio',array('guide' => '方便查看每批次的静态生成状况，多批次不自动连续执行'));
		tabfooter('bsubmit','现在执行');
	#	a_guide('staticmcnodes');
	}else{
		$npage = empty($npage) ? 1 : $npage;
		if(empty($pages)) $pages = @ceil($_total / $numperpic);
		if(empty($pages)) cls_message::show('请选择节点',"?entry=$entry&action=$action$filterstr");
		if($pagefrom && $pageto && $pageto < $pagefrom) cls_message::show('页码范围输入有误',"?entry=$entry&action=$action$filterstr");
		$pagefrom && $npage = max($npage,$pagefrom);
		$pageto && $pages = min($pages,$pageto);
		$npage = min($npage,$pages);

		$selectid = $cnstrarr = array();
		$fromstr = empty($fromid) ? "" : "cnid<$fromid";
		$offsetfrom = empty($fromid) ? ($pagefrom ? ($pagefrom-1)*$numperpic : 0) : 0;
		$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
		$query = $db->query("SELECT cnid,ename,tid $fromsql $nwheresql ORDER BY cnid DESC LIMIT $offsetfrom,$numperpic");
		while($item = $db->fetch_array($query)){
			$selectid[] = $item['cnid'];
			$cnstrarr[$item['ename']] = $item['tid'];
		}

		$initno = ($npage - 1) * $numperpic;
		$str = "<br><b>会员节点页静态:</b> $numperpic/批 第 $npage 批 共 $pages 批 &nbsp;>><a href=\"?entry=$entry&action=$action$filterstr\">返回</a>";

		$nextpage  = $npage + 1;
		$nexturl = "?entry=$entry&action=$action$filterstr$transtr&pages=$pages&npage=$nextpage&bsubmit=1&fromid=".min($selectid);
		$thisurl = "?entry=$entry&action=$action$filterstr$transtr&pages=$pages&npage=$npage&bsubmit=1&fromid=".(empty($fromid) ? 0 : $fromid);
		if($debugmode){
			$str .= " &nbsp;>><a href=\"$thisurl\">重来</a>";
			if($nextpage <= $pages) $str .= " &nbsp;>><a href=\"$nexturl\">继续</a>";
		}
		static_process('body',$str);
		foreach($cnstrarr as $cnstr => $v){
			if(!empty($mcntpls[$v])){
				$addnum = empty($mcntpls[$v]['addnum']) ? 0 : $mcntpls[$v]['addnum'];
				
				for($k = 0;$k <= $addnum;$k++){
					$re = cls_McnodePage::Create(array('cnstr' => $cnstr,'addno' => $k,'inStatic' => true));
					static_process('msg',str_pad($k ? '' : $initno,$k ? 10+strlen($initno) : 10,'.').str_pad($cnstr,40,'.').'附'.$k.'.....'.$re);
				}
				$initno ++;
			}
		}
		if($debugmode){
			exit();
		}else{
			if($nextpage <= $pages) static_process('jump',$nexturl);
			static_process('hide');
			adminlog('会员节点静态管理','节点列表管理操作');
			cls_message::show('会员节点操作完成',"?entry=$entry&action=$action$filterstr");
		}
	}
}elseif($action == 'mcnodesurl') {
	echo "<title>快速修复会员节点静态链接</title>";
	if(empty($enablestatic)) cls_message::show('静态模式未开启');
	
	if(!submitcheck('bsubmit')){
		tabheader("快速修复会员节点静态链接",'archives',"?entry=$entry&action=$action");
		trbasic('URL修复方式','',makeradio('kpmode',array(0 => '补全缺少页面',1 => '全部页面重写')),'');
		tabfooter('bsubmit','执行');
		a_guide('<li>通常系统新开启静态模式时，在未主动静态的情况下，前台会出现找不到文件的情况(400错误)
		<li>可通过快速修复链接，使前台可按静态URL正常访问页面',true);
	}else{
		
		$page = empty($page) ? 1 : max(1,intval($page));
		$kpmode = empty($kpmode) ? 0 : 1;
		$_continue = _nodes_url('mcnodes',$page,$kpmode);
		
		if($_continue){//继续下一页
			$page ++;
			cls_message::show("正在执行第 <b>$page</b> 页。请耐心等待。","?entry=$entry&action=$action&page=$page&kpmode=$kpmode&bsubmit=1");
		}else{//全部完成
			cls_message::show('链接修复完毕。',"?entry=$entry&action=$action");
		}
	}
}elseif($action == 'cfstatic'){
	backnav('static','cfstatic');
	$mconfigs = cls_cache::Read('mconfigs');
	if(!submitcheck('bmconfigs')){
		tabheader('静态综合设置','cfstatic',"?entry=$entry&action=$action");
		trbasic('是否启用静态','mconfigsnew[enablestatic]',$mconfigs['enablestatic'],'radio',array('guide' => '开启静态后，请将前台页面生成静态或快速修复链接，否则会出现链接打不开的情况<br>切换模式之后请先整站更新所有推送位,使推送信息URL跟切换后的静态模式同步'));
		$tnstr = "<input type=\"text\" size=\"25\" id=\"mconfigsnew[cnhtmldir]\" name=\"mconfigsnew[cnhtmldir]\" value=\"$mconfigs[cnhtmldir]\">&nbsp;
				<input class=\"checkbox\" type=\"checkbox\" name=\"mconfigsnew[disable_htmldir]\" id=\"mconfigsnew[disable_htmldir]\" value=\"1\"".(empty($mconfigs['disable_htmldir']) ? '' : ' checked').">不启用此路径";
		trbasic('类目节点及文档静态总路径','',$tnstr,$mconfigs['cnhtmldir']);
		trbasic('多页码静态时生成页数','mconfigsnew[liststaticnum]',$mconfigs['liststaticnum']);
		trbasic('以下文件名在url中隐藏','mconfigsnew[hiddensinurl]',empty($mconfigs['hiddensinurl']) ? '' : $mconfigs['hiddensinurl'],'text',array('guide'=>'<span style="color:#F00">文件名长的放在前面</span>，多个文件名之间用逗号分隔，除非全站只使用动态，否则请不要隐藏index.php','w'=>50));
		tabfooter();

		tabheader('系统首页设置');
		trbasic('站点首页静态文件名','mconfigsnew[homedefault]',$mconfigs['homedefault']);
		trbasic('站点首页静态更新周期','mconfigsnew[indexcircle]',$mconfigs['indexcircle'],'text',array('guide' => '单位:分钟'));
		trbasic('静态更新的暂停时段','mconfigsnew[indexnostatic]',empty($mconfigs['indexnostatic']) ? '' : $mconfigs['indexnostatic'],'text',array('guide'=>'访问高峰时段暂停被动更新，可以缓解服务器压力,格式如：8-12,13,18-22。在模板调试模式下无效','w'=>50));
		tabfooter();

		tabheader('类目节点设置');
		for($i = 0;$i <= $cn_max_addno;$i ++){
			$pvar = $i ? '附加页'.$i : '首页';
			$configstr = '静态保存格式'."<input type=\"text\" size=\"25\" id=\"mconfigsnew[cn_urls][$i]\" name=\"mconfigsnew[cn_urls][$i]\" value=\"".@$cn_urls[$i]."\">";
			$configstr .= " &nbsp;静态更新周期(分钟)<input type=\"text\" size=\"5\" id=\"mconfigsnew[cn_periods][$i]\" name=\"mconfigsnew[cn_periods][$i]\" value=\"".@$cn_periods[$i]."\">";
			trbasic('类目节点'.$pvar.'设置','',$configstr,'',array('guide'=>!$i ? '留空为默认格式，{$cndir}系统默认保存路径，{$page}分页页码，数字之间建议加上分隔符_或-连接。': ''));
		}
		trbasic('静态更新的暂停时段','mconfigsnew[cn_nostatic]',empty($mconfigs['cn_nostatic']) ? '' : $mconfigs['cn_nostatic'],'text',array('guide'=>'访问高峰时段暂停被动更新，可以缓解服务器压力,格式如：8-12,13,18-22。在模板调试模式下无效','w'=>50));
		tabfooter();

		tabheader('文档页面设置');
		trbasic('文档页静态保存格式','mconfigsnew[arccustomurl]',empty($mconfigs['arccustomurl']) ? '' : $mconfigs['arccustomurl'],'text',array('guide'=>'留空为默认格式，{$topdir}顶级栏目路径，{$cadir}所属栏目路径，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。','w'=>50));
		trbasic('文档页静态更新周期','mconfigsnew[archivecircle]',$mconfigs['archivecircle'],'text',array('guide' => '单位:分钟'));
		trbasic('静态更新的暂停时段','mconfigsnew[arc_nostatic]',empty($mconfigs['arc_nostatic']) ? '' : $mconfigs['arc_nostatic'],'text',array('guide'=>'访问高峰时段暂停被动更新，可以缓解服务器压力,格式如：8-12,13,18-22','w'=>50));
		tabfooter();

		tabheader('其它页面设置');
		trbasic('副件信息及独立页静态路径','mconfigsnew[infohtmldir]',$mconfigs['infohtmldir']);
		trbasic('会员频道节点静态更新周期','mconfigsnew[mcnindexcircle]',$mconfigs['mcnindexcircle'],'text',array('guide' => '单位:分钟'));
		trbasic('会员节点静态更新的暂停时段','mconfigsnew[mcn_nostatic]',empty($mconfigs['mcn_nostatic']) ? '' : $mconfigs['mcn_nostatic'],'text',array('guide'=>'访问高峰时段暂停被动更新，可以缓解服务器压力,格式如：8-12,13,18-22','w'=>50));
		tabfooter();

		tabheader('会员空间设置');
        setPermBar('以下会员允许生成静态空间','mconfigsnew[mspacepmid]',@$mconfigs['mspacepmid'], 'other', array(0=>'全部不允许'), '请有效控制静态空间数量(少于5000为宜)。');
        trbasic('空间类目页静态保存格式','mconfigsnew[ms_customurl]',empty($mconfigs['ms_customurl']) ? '' : $mconfigs['ms_customurl'],'text',array('guide'=>'留空为默认格式，静态固定生成在会员设置的静态目录。{$cadir}空间栏目目录，{$ucdir}个人分类目录，{$page}分页页码 {$addno}附加页id，数字之间建议用_或-分隔。','w'=>50));
		trbasic('静态空间被动更新周期','mconfigsnew[mspacecircle]',empty($mconfigs['mspacecircle']) ? '' : $mconfigs['mspacecircle'],'text',array('guide'=>'单位：分钟。留空为不自动更新，完全由会员手动更新。建议设为1440或留空。'));
		trbasic('静态更新的暂停时段','mconfigsnew[ms_nostatic]',empty($mconfigs['ms_nostatic']) ? '' : $mconfigs['ms_nostatic'],'text',array('guide'=>'访问高峰时段暂停被动更新，可以缓解服务器压力,格式如：8-12,13,18-22','w'=>50));
		tabfooter('bmconfigs');
		a_guide('cfstatic');

	}else{
		foreach(array('cnhtmldir','infohtmldir',) as $var){
			$mconfigsnew[$var] = strtolower($mconfigsnew[$var]);
			if($mconfigsnew[$var] == $mconfigs[$var]) continue;
			if(!$mconfigsnew[$var] || preg_match("/[^a-z_0-9]+/",$mconfigsnew[$var])){
				$mconfigsnew[$var] = $mconfigs[$var];
				continue;
			}
			if($mconfigs[$var] && is_dir(M_ROOT.$mconfigs[$var])){
				if(is_dir(M_ROOT.$mconfigsnew[$var])){ 
					$_msg = "修改未成功，目录[{$mconfigsnew[$var]}]已经存在！<br>请使用其它目录 或 手动先移动或删除此目录！";
					cls_message::show($_msg,"?entry=$entry&action=$action");
				}
				if(!rename(M_ROOT.$mconfigs[$var],M_ROOT.$mconfigsnew[$var])) $mconfigsnew[$var] = $mconfigs[$var];
			}else mmkdir(M_ROOT.$mconfigsnew[$var],0);
		}
		$mconfigsnew['homedefault'] = trim(strip_tags($mconfigsnew['homedefault']));
		$mconfigsnew['arccustomurl'] = preg_replace("/^\/+/",'',trim($mconfigsnew['arccustomurl']));
		$mconfigsnew['cn_urls'] = empty($mconfigsnew['cn_urls']) ? '' : implode(',',$mconfigsnew['cn_urls']);
		$mconfigsnew['cn_periods'] = empty($mconfigsnew['cn_periods']) ? '' : implode(',',$mconfigsnew['cn_periods']);
		$mconfigsnew['disable_htmldir'] = empty($mconfigsnew['disable_htmldir']) ? 0 : 1;
		$mconfigsnew['msgforwordtime'] = max(0,intval(@$mconfigsnew['msgforwordtime']));
		$mconfigsnew['indexcircle'] = max(0,intval($mconfigsnew['indexcircle']));
		$mconfigsnew['archivecircle'] = max(0,intval($mconfigsnew['archivecircle']));
		$mconfigsnew['liststaticnum'] = max(0,intval($mconfigsnew['liststaticnum']));
		$mconfigsnew['mspacecircle'] = max(0,intval($mconfigsnew['mspacecircle']));
		saveconfig('static');
		adminlog('网站设置','系统静态配置');
		cls_message::show('网站设置完成',"?entry=$entry&action=$action");
	}
}

function _archive_url($stid,$page = 1,$kpmode = 0){
	global $db,$tblprefix;
	$pics = 500;
	$query = $db->query("SELECT aid,chid FROM {$tblprefix}".atbl($stid,1)." WHERE checked='1' ORDER BY aid DESC LIMIT ".($pics * ($page - 1)).",$pics",'SILENT');
	$i = 0;
	$arc = new cls_arcedit;
	while($r = $db->fetch_array($query)){
		if($arc->set_aid($r['aid'],array('au'=>0,'chid'=>$r['chid'],))){
			$arc->set_arcurl($kpmode);
		}
		$i ++;
	}
	return $i < $pics ? false : true;
}

function _nodes_url($type,$page = 1,$kpmode = 0){
	global $db,$tblprefix;
	$pics = 500;
	$i = 0;
	if(!in_array($type,array('cnodes','mcnodes',))) return false;
	$query = $db->query("SELECT cnid,ename FROM {$tblprefix}$type WHERE closed=0 ORDER BY cnid LIMIT ".($pics * ($page - 1)).",$pics",'SILENT');
	$ClassName = $type == 'cnodes' ? 'cls_cnode' : 'cls_mcnode';
	while($r = $db->fetch_array($query)){
		if($type == 'cnodes'){
			cls_cnode::BlankStaticUrl($r['ename'],$kpmode);
		}else{
			cls_mcnode::BlankStaticUrl($r['ename'],$kpmode);
		}
		$i ++;
	}
	return $i < $pics ? false : true;
}

function static_process($op = 'msg', $param = ''){
	switch($op){
	case 'msg':
		echo '<script type="text/javascript">showmessage("', addslashes($param), '");</script>';
		break;
	case 'hide':
		echo '<script type="text/javascript">hide_progress("progressdiv");</script>';
		break;
	case 'jump':
		echo '<script type="text/javascript">redirect("', $param, '");</script>';
		break;
	case 'body':
		ob_implicit_flush();
?>
<div id="progressdiv" style="text-align:left">
	<div><?=$param?></div>
	<div id="progressbody" style="width:100%;height:500px;margin-top:20px;white-space:nowrap;overflow:auto;border:solid 1px #ddd"></div>
</div>
<script type="text/javascript">
var progressbody = document.getElementById('progressbody'), progressdiv = progressbody.parentNode, proressflag = progressbody.firstChild;
progressbody.style.height = document.body.clientHeight - progressbody.offsetTop - 10 + 'px';

function showmessage(message) {
	var div = document.createElement('DIV');
	div.appendChild(document.createTextNode(message));
	if(proressflag){
		progressbody.insertBefore(div, proressflag);
	}else{
		progressbody.appendChild(div);
	}
	proressflag = div;
}

function hide_progress(id) {
	document.getElementById(id).style.display = 'none';
}
</script>
<?php
		break;
	}
	ob_flush();
}
