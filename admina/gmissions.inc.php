<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('gather')) cls_message::show($re);
foreach(array('gmodels','gmissions','catalogs','rprojects','cotypes','channels','abrels','vcps','permissions','currencys',) as $k) $$k = cls_cache::Read($k);
include_once M_ROOT.'include/progress.cls.php';
$gmidsarr = array();foreach($gmodels as $k =>$v) $gmidsarr[$k] = $v['cname'];
if($action == 'gmissionsedit'){
	backnav('gmiss','admin');
	empty($gmidsarr) && cls_message::show('请添加采集模型!');
	if(!submitcheck('bsubmit')){
		tabheader("采集任务管理&nbsp; &nbsp; >><a href=\"?entry=gmissions&action=gmissionadd\" onclick=\"return floatwin('open_gmission',this)\">添加</a>",'gmissionsedit',"?entry=gmissions&action=gmissionsedit",'8');
		trcategory(array(array('任务名称','txtL'),'辑内任务','采集模型','规则','采集','<input class="checkbox" type="checkbox" name="chkall" onclick="deltip(this,0,checkall,this.form)">删?','管理','任务复制'));
		foreach($gmissions as $k => $v){
			if(empty($v['pid'])){
				gmission_list($k);
				if(!empty($v['sonid'])) gmission_list($v['sonid']);
			}
		}
		tabfooter('bsubmit','修改');
		a_guide('gmissionadd');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				$gmission = cls_cache::Read('gmission',$k,'');
				if($gmission['pid']) $db->query("UPDATE {$tblprefix}gmissions SET sonid='0' WHERE gsid='".$gmission['pid']."'");//如果有父任务，将关联关系清除
				if($gmission['sonid']){//如有辑内任务，将辑内任务一并删除
					$db->query("DELETE FROM {$tblprefix}gmissions WHERE gsid='".$gmission['sonid']."'");
					$db->query("DELETE FROM {$tblprefix}gurls WHERE gsid='".$gmission['sonid']."'");
					unset($gmissionsnew[$gmission['sonid']]);
				}
				$db->query("DELETE FROM {$tblprefix}gmissions WHERE gsid=$k");
				$db->query("DELETE FROM {$tblprefix}gurls WHERE gsid=$k");//将相关记录清除
				unset($gmissionsnew[$k]);
			}
		}
		if(!empty($gmissionsnew)){
			foreach($gmissionsnew as $k => $v){
				$v['cname'] = empty($v['cname']) ? addslashes($gmissions[$k]['cname']) : $v['cname'];
				$db->query("UPDATE {$tblprefix}gmissions SET cname='$v[cname]' WHERE gsid=$k");
			}
		}
		cls_CacheFile::Update('gmissions');
		adminlog('编辑采集任务管理列表');
		cls_message::show('采集任务修改完成',"?entry=gmissions&action=gmissionsedit");

	}
}elseif($action == 'gmissionadd'){
	$pid = empty($pid) ? 0 : max(0,intval($pid));
	if(empty($gmissions[$pid])) $pid = 0;
	if(!submitcheck('bgmissionadd')){
		tabheader('采集任务添加','gmissionadd',"?entry=gmissions&action=gmissionadd");
		trbasic('采集任务名称','gmissionadd[cname]');
		trbasic('采集模型','gmissionadd[gmid]',makeoption($gmidsarr),'select');
		if($pid){
			trbasic('所属采集任务','',$gmissions[$pid]['cname'],'');
			trhidden('pid',$pid);
		}
		tabfooter('bgmissionadd','添加');
		a_guide('gmissionadd');
	}else{
		$gmissionadd['cname'] = trim(strip_tags($gmissionadd['cname']));
		(!$gmissionadd['cname'] || !$gmissionadd['gmid']) && cls_message::show('采集任务资料不完全',M_REFERER);
		$db->query("INSERT INTO {$tblprefix}gmissions SET cname='$gmissionadd[cname]',gmid='$gmissionadd[gmid]',pid='$pid',timeout=5");
		if($pid && $sonid = $db->insert_id()){
			$db->query("UPDATE {$tblprefix}gmissions SET sonid='$sonid' WHERE gsid='$pid'");
		}
		cls_CacheFile::Update('gmissions');
		adminlog('添加采集任务');
		cls_message::show('采集任务添加完成',axaction(6,"?entry=gmissions&action=gmissionsedit"));
	}
}elseif($action == 'gmissioncopy'){
	$gsid = empty($gsid) ? 0 : max(0,intval($gsid));
	empty($gmissions[$gsid]) && cls_message::show('采集任务资料不完全');
	$gmissionss = array(cls_cache::Read('gmission', $gsid, ''));
	if(!submitcheck('bgmissioncopy')){
		tabheader('采集任务复制','gmissioncopy',"?entry=gmissions&action=gmissioncopy");
		trbasic('采集任务名称','gmissionnew[cname][]',$gmissions[$gsid]['cname'].'拷贝');
		trbasic('采集模型','',$gmidsarr[$gmissions[$gsid]['gmid']],'');
		if($gmissionss[0]['sonid']){
			trbasic('子任务名称','gmissionnew[cname][]',$gmissions[$gmissionss[0]['sonid']]['cname'].'拷贝');
			trbasic('子任务模型','',$gmidsarr[$gmissions[$gmissionss[0]['sonid']]['gmid']],'');
		}
		trhidden('gsid',$gsid);
		tabfooter('bgmissioncopy','复制');
		a_guide('gmissioncopy');
	}else{
		foreach($gmissionnew['cname'] as $k => $cname)
			$gmissionnew['cname'][$k] = trim(strip_tags($cname));
		$gmissionnew['cname'][0] || cls_message::show('采集任务资料不完全',M_REFERER);
		$gmissionss[0]['sonid'] && !empty($gmissionnew['cname'][1]) && $gmissionss[] = cls_cache::Read('gmission', $gmissionss[0]['sonid'], '');
		$gmissionss[0]['gsid'] = $pid = 0;
		cls_CacheFile::Update('gmissions');
		foreach($gmissionss as $k => $gmission){
			$cname = $gmissionnew['cname'][$k];
			$gmission['fsettings']	= serialize($gmission['fsettings']);
			$gmission['dvalues']	= serialize($gmission['dvalues']);
			while(list($key, $val) = each($gmission))$gmission[$key] = addslashes($val);
			$db->query("INSERT INTO {$tblprefix}gmissions SET
				cname='$cname',
				gmid='$gmission[gmid]',
				umode1='$gmission[umode1]',
				umode2='$gmission[umode2]',
				ubase='$gmission[ubase]',
				ubase0='$gmission[ubase0]',
				ubase1='$gmission[ubase1]',
				ubase2='$gmission[ubase2]',
				mcharset='$gmission[mcharset]',
				timeout='$gmission[timeout]',
				mcookies='$gmission[mcookies]',
				umode='$gmission[umode]',
				uurls='$gmission[uurls]',
				uregular='$gmission[uregular]',
				ufromnum='$gmission[ufromnum]',
				utonum='$gmission[utonum]',
				ufrompage='$gmission[ufrompage]',
				udesc='$gmission[udesc]',
				uinclude='$gmission[uinclude]',
				uforbid='$gmission[uforbid]',
				uregion='$gmission[uregion]',
				uspilit='$gmission[uspilit]',
				uurltag='$gmission[uurltag]',
				utitletag='$gmission[utitletag]',
				uurltag1='$gmission[uurltag1]',
				uinclude1='$gmission[uinclude1]',
				uforbid1='$gmission[uforbid1]',
				uurltag2='$gmission[uurltag2]',
				uinclude2='$gmission[uinclude2]',
				uforbid2='$gmission[uforbid2]',
				mpfield='$gmission[mpfield]',
				mpmode='$gmission[mpmode]',
				mptag='$gmission[mptag]',
				mpinclude='$gmission[mpinclude]',
				mpforbid='$gmission[mpforbid]',
				fsettings='$gmission[fsettings]',
				dvalues='$gmission[dvalues]',
				pid='$pid',sonid='0'"
			);
			$gmissionss[$k]['gsid'] = $pid = $db->insert_id();
		}
		if(count($gmissionss) > 1)
			$db->query("UPDATE {$tblprefix}gmissions SET sonid='$pid' WHERE gsid='{$gmissionss[0]['gsid']}'");
		cls_CacheFile::Update('gmissions');
		adminlog('复制采集任务');
		cls_message::show('采集任务添加完成',axaction(6,"?entry=gmissions&action=gmissionsedit"));
	}
}elseif($action == 'gmissionurls' && $gsid){
	backnav('grule','netsite');
	$gmission = cls_cache::Read('gmission',$gsid,''); //print_r($gmission);
	$gmodel = $gmodels[$gmission['gmid']];
	if(!submitcheck('bgmissionurls')){
		$mchararr = array('gbk' => 'GBK/GB2312','utf8' => 'UTF-8','big5' => 'BIG5',);
		tabheader('采集基本设置','gmissionurls',"?entry=gmissions&action=gmissionurls&gsid=$gsid");
		trbasic('采集任务名称','gmissionnew[cname]',$gmission['cname']);
		trbasic('页面编码','gmissionnew[mcharset]',makeoption($mchararr,$gmission['mcharset']),'select');
		trbasic('连接超时(秒)','gmissionnew[timeout]',empty($gmission['timeout']) ? 0 : $gmission['timeout'],'text',array('guide'=>'0或空表示不限制'));
		trbasic('登录网站'.'Cookies','gmissionnew[mcookies]',empty($gmission['mcookies']) ? '' : $gmission['mcookies'],'text',array('w'=>50));
		tabfooter();

		tabheader('网址来源规则');
		if(empty($gmission['pid'])){
			trbasic('手动来源网址<br> (每行一个网址，可输入多行)','gmissionnew[uurls]',$gmission['uurls'],'textarea');
			trbasic("序列来源网址<br><span onclick=\"replace_html(this,'gmissionnew[uregular]')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'gmissionnew[uregular]')\" style=\"color:#03F;cursor: pointer;\">(?)</span>",'gmissionnew[uregular]',empty($gmission['uregular']) ? '' : $gmission['uregular'],'text',array('w'=>50));
			trbasic('序列开始页码','gmissionnew[ufromnum]',$gmission['ufromnum']);
			trbasic('序列结束页码','gmissionnew[utonum]',$gmission['utonum']);
			trbasic('来源页BASE地址','gmissionnew[ubase]',empty($gmission['ubase']) ? '' : $gmission['ubase'],'text',array('w' => 70, 'guide'=>'非必填，但如果来源页内有类似 &lt;base href="http://xxx.xxx" /> 的标签，请在这里输入href里的内容'));
		}else{
			$frompagearr = array(0 => '基本内容页',1 => '内容追溯页1',2 => '内容追溯页2');
			trbasic('网址来自合辑的哪个页面','gmissionnew[ufrompage]',makeoption($frompagearr,$gmission['ufrompage']),'select');
		}
		trbasic('倒序采集','gmissionnew[udesc]',$gmission['udesc'],'radio');
		trbasic('内容页BASE地址','gmissionnew[ubase0]',empty($gmission['ubase0']) ? '' : $gmission['ubase0'],'text',array('w' => 70, 'guide'=>'非必填，但如果内容页内有类似 &lt;base href="http://xxx.xxx" /> 的标签，请在这里输入href里的内容'));
		tabfooter();

		tabheader('网址采集规则');
		trbasic("页面采集范围<br /> 采集模印<br><span onclick=\"replace_html(this,'gmissionnew[uregion]')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'gmissionnew[uregion]')\" style=\"color:#03F;cursor: pointer;\">(?)</span>",'gmissionnew[uregion]',$gmission['uregion'],'textarea');
		trbasic('网址列表分隔符','gmissionnew[uspilit]',$gmission['uspilit']);
		trbasic("网址采集模印<br><span onclick=\"replace_html(this,'gmissionnew[uurltag]')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'gmissionnew[uurltag]')\" style=\"color:#03F;cursor: pointer;\">(?)</span>",'gmissionnew[uurltag]',$gmission['uurltag'],'textarea');
		trbasic("标题采集模印<br><span onclick=\"replace_html(this,'gmissionnew[utitletag]')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'gmissionnew[utitletag]')\" style=\"color:#03F;cursor: pointer;\">(?)</span>",'gmissionnew[utitletag]',$gmission['utitletag'],'textarea');
		trbasic('结果网址必含','gmissionnew[uinclude]',$gmission['uinclude']);
		trbasic('结果网址禁含','gmissionnew[uforbid]',$gmission['uforbid']);
		tabfooter();

		tabheader('追溯网址规则');
		trbasic("追溯网址1采集模印<br><span onclick=\"replace_html(this,'gmissionnew[uurltag1]')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'gmissionnew[uurltag1]')\" style=\"color:#03F;cursor: pointer;\">(?)</span>",'gmissionnew[uurltag1]',$gmission['uurltag1'],'textarea');
		trbasic('完全匹配模印','gmissionnew[umode1]',$gmission['umode1'],'radio',array('guide'=>'选择完全匹配模印,则匹配返回的是上面完全字符串',));
		trbasic('追溯网址1必含','gmissionnew[uinclude1]',$gmission['uinclude1']);
		trbasic('追溯网址1禁含','gmissionnew[uforbid1]',$gmission['uforbid1']);
		trbasic('追溯页1BASE地址','gmissionnew[ubase1]',empty($gmission['ubase1']) ? '' : $gmission['ubase1'],'text',array('w' => 70, 'guide'=>'非必填，但如果追溯页1内有类似 &lt;base href="http://xxx.xxx" /> 的标签，请在这里输入href里的内容'));
		trbasic("追溯网址2采集模印<br><span onclick=\"replace_html(this,'gmissionnew[uurltag2]')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'gmissionnew[uurltag2]')\" style=\"color:#03F;cursor: pointer;\">(?)</span>",'gmissionnew[uurltag2]',$gmission['uurltag2'],'textarea');
		trbasic('完全匹配模印','gmissionnew[umode2]',$gmission['umode2'],'radio',array('guide'=>'选择完全匹配模印,则匹配返回的是上面完全字符串',));
		trbasic('追溯网址2必含','gmissionnew[uinclude2]',$gmission['uinclude2']);
		trbasic('追溯网址2禁含','gmissionnew[uforbid2]',$gmission['uforbid2']);
		trbasic('追溯页2BASE地址','gmissionnew[ubase2]',empty($gmission['ubase2']) ? '' : $gmission['ubase2'],'text',array('w' => 70, 'guide'=>'非必填，但如果追溯页2内有类似 &lt;base href="http://xxx.xxx" /> 的标签，请在这里输入href里的内容'));
		tabfooter('bgmissionurls');
		a_guide('gmissionurls');
	}else{
		$gmissionnew['cname'] = empty($gmissionnew['cname']) ? $gmission['cname'] : $gmissionnew['cname'];
		if(empty($gmission['pid'])){
			$gmissionnew['uurls'] = trim($gmissionnew['uurls']);
			$gmissionnew['uregular'] = trim($gmissionnew['uregular']);
			$gmissionnew['ufromnum'] = max(0,intval($gmissionnew['ufromnum']));
			$gmissionnew['utonum'] = max(0,intval($gmissionnew['utonum']));
			$gmissionnew['ufrompage'] = 0;
		}else{
			$gmissionnew['uurls'] = '';
			$gmissionnew['uregular'] = '';
			$gmissionnew['ufromnum'] = 0;
			$gmissionnew['utonum'] = 0;
			$gmissionnew['ufrompage'] = max(0,intval($gmissionnew['ufrompage']));
		}
		$db->query("UPDATE {$tblprefix}gmissions SET
					umode1='$gmissionnew[umode1]',
					umode2='$gmissionnew[umode2]',
					ubase='$gmissionnew[ubase]',
					ubase0='$gmissionnew[ubase0]',
					ubase1='$gmissionnew[ubase1]',
					ubase2='$gmissionnew[ubase2]',
					cname='$gmissionnew[cname]',
					timeout='$gmissionnew[timeout]',
					mcharset='$gmissionnew[mcharset]',
					mcookies='$gmissionnew[mcookies]',
					uurls='$gmissionnew[uurls]',
					uregular='$gmissionnew[uregular]',
					ufromnum='$gmissionnew[ufromnum]',
					utonum='$gmissionnew[utonum]',
					ufrompage='$gmissionnew[ufrompage]',
					udesc='$gmissionnew[udesc]',
					uregion='$gmissionnew[uregion]',
					uspilit='$gmissionnew[uspilit]',
					uurltag='$gmissionnew[uurltag]',
					utitletag='$gmissionnew[utitletag]',
					uinclude='$gmissionnew[uinclude]',
					uforbid='$gmissionnew[uforbid]',
					uurltag1='$gmissionnew[uurltag1]',
					uinclude1='$gmissionnew[uinclude1]',
					uforbid1='$gmissionnew[uforbid1]',
					uurltag2='$gmissionnew[uurltag2]',
					uinclude2='$gmissionnew[uinclude2]',
					uforbid2='$gmissionnew[uforbid2]'
					WHERE gsid=$gsid");
		cls_CacheFile::Update('gmissions');
		adminlog('详细修改采集任务');
		cls_message::show('采集任务修改完成',M_REFERER);

	}
}elseif($action == 'gmissionfields' && $gsid){
	backnav('grule','content');
	$gmission = cls_cache::Read('gmission',$gsid,'');
	$gmodel = cls_cache::Read('gmodel',$gmission['gmid'],'');
	$fields = cls_cache::Read('fields',$gmodel['chid']);
	$cotypes = cls_cache::Read('cotypes');
	$cfields = array('caid'=>array('datatype'=>'select','cname'=>'栏目'));
	foreach($cotypes as $k=>$v){
		$cfields['ccid'.$k]['datatype'] = $v['asmode'] ? 'mselect' : 'select';
		$cfields['ccid'.$k]['cname'] = $v['cname'];
	}
	$fields = $cfields + $fields + array('jumpurl'=>array('datatype'=>'text','cname'=>'跳转URL'),'createdate'=>array('datatype'=>'text','cname'=>'添加时间'),'enddate'=>array('datatype'=>'text','cname'=>'到期时间'),'mname'=>array('datatype'=>'text','cname'=>'会员名称'));
	if(!submitcheck('bgmissionfields')){
		$mpfieldarr = array('' => '无分页字段');
		foreach($fields as $k => $v){
			if(isset($gmodel['gfields'][$k])) $mpfieldarr[$k] = $v['cname'];
		}
		tabheader('分页采集规则','gmissionfields',"?entry=gmissions&action=gmissionfields&gsid=$gsid",4);
		trbasic('分页字段','gmissionnew[mpfield]',makeoption($mpfieldarr,isset($gmission['mpfield']) ? $gmission['mpfield'] : ''),'select');
		trbasic('分页导航是否完整','',makeradio('gmissionnew[mpmode]', array('0' => '是', '1' => '否'), $gmission['mpmode']),'');
		trbasic('分页导航区域采集模印','gmissionnew[mptag]',isset($gmission['mptag']) ? $gmission['mptag'] : '','textarea');
		trbasic('分页链接必含','gmissionnew[mpinclude]',isset($gmission['mpinclude']) ? $gmission['mpinclude'] : '');
		trbasic('分页链接禁含','gmissionnew[mpforbid]',isset($gmission['mpforbid']) ? $gmission['mpforbid'] : '');
		tabfooter();
		tabheader('采集字段规则','',"",4);
		foreach($fields as $k => $v){
			if(isset($gmodel['gfields'][$k])) missionfield($v['cname'],$k,empty($gmission['fsettings'][$k]) ? array() : $gmission['fsettings'][$k],$v['datatype']);
		}
		tabfooter('bgmissionfields');
		a_guide('gmissionfields');
	}else{
		if(!empty($fsettingsnew)){
			foreach($fsettingsnew as $k => $fsettingnew){
				if(!in_array($fields[$k]['datatype'],array('images','files','flashs','medias'))){
					$fsettingnew['clearhtml'] = isset(${'clearhtml'.$k}) ? implode(',',${'clearhtml'.$k}) : '';
				}
				foreach($fsettingnew as $t => $v){
					$fsettingnew[$t] = stripslashes($v);
				}
				$fsettingsnew[$k] = $fsettingnew;
			}
		}
		$fsettingsnew = empty($fsettingsnew) ? '' : addslashes(serialize($fsettingsnew));
		$db->query("UPDATE {$tblprefix}gmissions SET
					mpfield='$gmissionnew[mpfield]',
					mpmode='$gmissionnew[mpmode]',
					mptag='$gmissionnew[mptag]',
					mpinclude='$gmissionnew[mpinclude]',
					mpforbid='$gmissionnew[mpforbid]',
					fsettings='$fsettingsnew'
					WHERE gsid=$gsid");
		cls_CacheFile::Update('gmissions');
		adminlog('详细修改采集任务');
		cls_message::show('采集任务编辑完成',M_REFERER);

	}
}elseif($action == 'gmissionoutput' && $gsid){
	backnav('grule','output');
	$gmission = cls_cache::Read('gmission',$gsid,'');
	$gmodel = cls_cache::Read('gmodel',$gmission['gmid'],'');
	$dvalues = empty($gmission['dvalues']) ? array() : $gmission['dvalues'];
	$chid = $gmodel['chid'];
	$channel = cls_channel::Config($chid);
	$fields = cls_cache::Read('fields',$chid);
	if(!submitcheck('bgmissionoutput')){
		$a_field = new cls_field;
		$mustsarr = array();
		foreach($fields as $k => $v) isset($gmodel['gfields'][$k]) && $mustsarr[$k] = $v['cname'];
		tabheader("[$gmission[cname]]入库基本设置",'gmissionoutput',"?entry=gmissions&action=gmissionoutput&gsid=$gsid",2,1,1);
		trbasic('以下采集字段为空时不能入库','',multiselect('dvaluesnew[musts][]',$mustsarr,empty($dvalues['musts']) ? array() : explode(',',$dvalues['musts'])),'');
		if(isset($fields['abstract']) && !in_array('abstract',array_keys($gmodel['gfields']))){
			trbasic('自动摘要','dvaluesnew[autoabstract]',empty($dvalues['autoabstract']) ? 0 : $dvalues['autoabstract'],'radio');
		}
		if(isset($fields['thumb']) && !in_array('thumb',array_keys($gmodel['gfields']))){
			trbasic('自动缩略图','dvaluesnew[autothumb]',empty($dvalues['autothumb']) ? 0 : $dvalues['autothumb'],'radio');
		}
		if($gmission['pid']){
			$abidsarr = array();foreach($abrels as $k => $v) $abidsarr[$k] = $v['cname'];
			trbasic('入库归辑时遵循的合辑项目','dvaluesnew[arid]',makeoption($abidsarr,empty($dvalues['arid']) ? 0 : $dvalues['arid']),'select');
		}
		tabfooter();
		tabheader("[$gmission[cname]]入库默认值");
		tr_cns('所属栏目','dvaluesnew[caid]',array('value' => empty($dvalues['caid']) ? 0 : $dvalues['caid'],'chid' => $chid,'notblank' => 1,));
		foreach($cotypes as $k => $v){
			if(!$v['self_reg']){
				tr_cns($v['cname'],"dvaluesnew[ccid$k]",array('value' => empty($dvalues["ccid$k"]) ? '' : $dvalues["ccid$k"],'coid' => $k,'chid' => $chid,'max' => $v['asmode'],));
			}
		}
		foreach($fields as $k => $v){ 
			if(!in_array($k,array('abstract','thumb','content','subject'))){
				$a_field->init($v,!isset($dvalues[$k]) ? '' : $dvalues[$k]); 
				$a_field->trfield('dvaluesnew');
			}
		}
		trbasic('会员名称','dvaluesnew[mname]',empty($dvalues['mname']) ? '' : $dvalues['mname'],'text',array('guide'=>'指定多个会员名称时请以“,”分隔，注：填写多个会员时将随机抽取会员进行关联。格式：mmmm,bbbb,ccccc'));
		tabfooter('bgmissionoutput');
		a_guide('gmissionoutput');
	}else{//数组内的addsalshes
		if(empty($dvaluesnew['caid'])) cls_message::show('请指定正确的栏目',"?entry=gmissions&action=gmissionoutput&gsid=$gsid");
		if($gmission['pid'] && empty($dvaluesnew['arid'])) cls_message::show('请指定入库归辑时遵循的合辑项目',"?entry=gmissions&action=gmissionoutput&gsid=$gsid");
		$dvaluesnew['musts'] = empty($dvaluesnew['musts']) ? '' : implode(',',$dvaluesnew['musts']);
		foreach($cotypes as $k => $v){
			$dvaluesnew["ccid$k"] = empty($dvaluesnew["ccid$k"]) ? '' : $dvaluesnew["ccid$k"];
		}
		$dvaluesnew['autoabstract'] = empty($dvaluesnew['autoabstract']) ? 0 : $dvaluesnew['autoabstract'];
		$dvaluesnew['autothumb'] = empty($dvaluesnew['autothumb']) ? 0 : $dvaluesnew['autothumb'];
		$c_upload = cls_upload::OneInstance();
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(!isset($gmodel['gfields'][$k]) && !in_array($k,array('abstract','thumb'))){
				$a_field->init($v,!isset($dvalues[$k]) ? '' : $dvalues[$k]);
				$a_field->deal('dvaluesnew','cls_message::show',M_REFERER);
				$dvaluesnew[$k] = $a_field->newvalue; 
			}
			if($v['datatype']=='date'){
				$dvaluesnew[$k] = strtotime($dvaluesnew[$k]);
			}
		}
		//print_r($dvaluesnew); die();
		unset($a_field);
		//处理会员分隔防止出错。
		$dvaluesnew['mname'] = str_replace('，',',',$dvaluesnew['mname']);
		if(!empty($dvaluesnew)){
			foreach($dvaluesnew as $x => $y){
                if(is_array($y)){ 
                    $y = implode("\t",$y);
                } 
                $dvaluesnew[$x] = stripslashes($y);
                
			} 
            
		}
		$dvaluesnew = empty($dvaluesnew) ? '' : addslashes(serialize($dvaluesnew));
		$db->query("UPDATE {$tblprefix}gmissions SET
					dvalues='$dvaluesnew'
					WHERE gsid=$gsid");
		$c_upload->closure(1, $gsid, 'gmissions');
		$c_upload->saveuptotal(1);
		cls_CacheFile::Update('gmissions');
		adminlog('详细修改采集任务');
		cls_message::show('入库规则修改完成',M_REFERER);
	}
}elseif($action == 'urlstest' && $gsid){
	backnav('grule','test');
	if(!submitcheck('confirm') && empty($gather_test_url)){
		$message = "您选择了测试规则。<br>在测试之前请确认已经设置相关规则。<br>本测试从网址规则一步一步测试，请注意选择测试页里的相关链接进行下一步测试。<br><br>";
		$message .= "确认请点击>><a href=?entry=gmissions&action=urlstest&gsid=$gsid&confirm=ok>开始</a>";
		cls_message::show($message);
	}else{
		tabheader('采集网址规则测试', 'gather_testu', "?$_SERVER[QUERY_STRING]");
		$gather = new cls_gather;
		$gather->set_mission($gsid);
#		check_rule_urls($gather->gmission);
		if(empty($gather_test_url)){
			$message = '';
			if($surls = $gather->fetch_surls()){
				foreach($surls as $surl) $message .= $surl.'<br>';
			}else $message = '无来源网址'.'<br>';
				trbasic('全部来源网址','',$message,'');
			$surl = empty($surls) ? '' : $surls[array_rand($surls)];
		}else{
			$surl = $gather_test_url;
		}
		$sonid = $gather->gmission['sonid'];
		$lang_test = '测试';
		$lang_content = '内容';
		$lang_son = '子任务';
		trbasic('当前测试来源网址','',"<input name=\"gather_test_url\" style=\"width:98%\" value=\"$surl\" />",'');
		tabfooter('bsubmit');
		$tab_titles = array('序号',array('网址标题', 'txtL'),array('内容网址', 'txtL'),$lang_test,'追溯网址1','追溯网址2');
		if($sonid){
			array_splice($tab_titles, 3, 0, $lang_son);
			$ufrompage = cls_cache::Read('gmission',$sonid,'');
			$ufrompage = $ufrompage['ufrompage'];
			$ufrompage = 'gurl' . ($ufrompage ? $ufrompage : '');
		}
		if($rets = $gather->fetch_gurls($surl,1)){//得到测试网址列表
			tabheader('内容网址列表 (测试网址结果数量限制10)','','',$sonid ? 7 : 6);
			trcategory($tab_titles);
			$i = 0;
			foreach($rets as $k => $v){
				$i ++;
				$titlestr = empty($v['son']) ? "<b>$v[utitle]</b>" : "&nbsp; &nbsp; &nbsp; &nbsp; $v[utitle]";
				$gurlstr  = empty($k) ? '-' : "<a href=\"$k\" target=\"_blank\">".mhtmlspecialchars(strlen($k) > 25 ? '...' . substr($k, -25) : $k)."</a>";
				$gurl1str = empty($v['gurl1']) ? '-' : "<a href=\"$v[gurl1]\" target=\"_blank\">Y</a>";
				$gurl2str = empty($v['gurl2']) ? '-' : "<a href=\"$v[gurl2]\" target=\"_blank\">Y</a>";
				if(empty($k)){
					$teststr  = '&nbsp;';
				}else{
					$gurl	  = rawurlencode($k);
					$gurl1	  = empty($v['gurl1']) ? '' : rawurlencode($v['gurl1']);
					$gurl2	  = empty($v['gurl2']) ? '' : rawurlencode($v['gurl2']);
					$teststr  = "<a href=\"?entry=gmissions&action=contentstest&gsid=$gsid&confirm=ok&gather_test_url=$gurl&gather_test_url1=$gurl1&gather_test_url2=$gurl2\" onclick=\"return floatwin('open_newgmission_cnt',this)\" >$lang_content</a>";
					if($sonid){
	#					$sonurl   = $gurl2 ? $gurl2 : ($gurl1 ? $gurl1 : $gurl);
						$sonurl   = $$ufrompage;
						$teststr2 = "<a href=\"?entry=gmissions&action=urlstest&gsid=$sonid&confirm=ok&gather_test_url=$sonurl\" onclick=\"return floatwin('open_newgmission_son',this)\" >$lang_son</a>";
					}
				}
				echo "<tr class=\"txt\">".
					"<td class=\"txtC w40\">$i</td>\n".
					"<td class=\"txtL\">$titlestr</td>\n".
					"<td class=\"txtL\">$gurlstr</td>\n".
					"<td class=\"txtC\">$teststr</td>\n".
					($sonid ? "<td class=\"txtC\">$teststr2</td>\n" : '') .
					"<td class=\"txtC\">$gurl1str</td>\n".
					"<td class=\"txtC\">$gurl2str</td></tr>\n";
			}
			tabfooter();
		}else{
			$surl && cls_message::show(is_array($rets) ? '没有采集到内容' : '采集超时或出错');
		}
		a_guide('urlstest');
	}
}elseif($action == 'contentstest' && $gsid){//只从数据库中加入有效链接来测试
	backnav('grule','test');
	if(!submitcheck('confirm')){
		$message = "您选择了内容规则测试。<br>在执行这前请确认已设置内容规则，<br>以及库中需要有未采集内容的网址。<br><br>";
		$message .= "确认请点击>><a href=?entry=gmissions&action=contentstest&gsid=$gsid&confirm=ok>开始</a>";
		cls_message::show($message);
	}else{
		tabheader('采集内容规则测试', 'gather_testc', "?$_SERVER[QUERY_STRING]");
#		$counts = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}gurls WHERE gsid='$gsid' AND gatherdate=0");

		if(empty($gather_test_url)){
			$item = $db->fetch_one("SELECT guid,gurl,gurl1,gurl2,utitle FROM {$tblprefix}gurls WHERE gsid='$gsid' AND gatherdate=0 AND guid >= (SELECT floor(RAND() * (SELECT MAX(guid) FROM {$tblprefix}gurls))) ORDER BY guid LIMIT 1");
		}else{
			$item = array(
				'utitle' => '[测试采集]',
				'gurl' => $gather_test_url,
				'gurl1' => $gather_test_url1,
				'gurl2' => $gather_test_url2,
			);
		}
		if($item){
			trbasic('当前测试网址标题','',mhtmlspecialchars($item['utitle']),'');
#			trbasic('当前测试网址','',"<input name=\"gather_test_url\" style=\"width:98%\" value=\"$item[gurl]\" />",'');
#			trbasic('', '', '<input class="bigButton" type="submit" name="bsubmit" value="' . '提交'. '">','');
			trbasic('当前测试网址','',"<a href=\"$item[gurl]\" target=\"_blank\">$item[gurl]</a>",'');
			empty($item['gurl1']) || trbasic('追溯网址1','',"<a href=\"$item[gurl1]\" target=\"_blank\">$item[gurl1]</a>",'');
			empty($item['gurl2']) || trbasic('追溯网址2','',"<a href=\"$item[gurl2]\" target=\"_blank\">$item[gurl2]</a>",'');
			$gather = new cls_gather;
			$gather->set_mission($gsid);
			$contents = $gather->gather_guid(0,1, $item);
			if($contents){
				$timeout = '采集超时或出错';
				$chid = $gmodels[$gmissions[$gsid]['gmid']]['chid'];
				$fields = cls_cache::Read('fields',$chid);
				$cotypes = cls_cache::Read('cotypes');
				$cfields = array('caid'=>array('datatype'=>'select','cname'=>'栏目'));
				foreach($cotypes as $k=>$v){
					$cfields['ccid'.$k]['datatype'] = $v['asmode'] ? 'mselect' : 'select';
					$cfields['ccid'.$k]['cname'] = $v['cname'];
				}
				$fields = $cfields + $fields + array('jumpurl'=>array('datatype'=>'text','cname'=>'跳转URL'),'createdate'=>array('datatype'=>'text','cname'=>'添加时间'),'mname'=>array('datatype'=>'text','cname'=>'会员名称'));;
				foreach($contents as $k => $v){
					trbasic('['.$fields[$k]['cname'].']'.'采集结果', '', $v === false ? $timeout : mhtmlspecialchars($v),'');
				}
			}else{
				trbasic('采集结果','','','');
			}
		}else{
			trbasic('', '', '请先采集内容网址','');
		}
		tabfooter();
		a_guide('contentstest');
	}
}elseif($action == 'contentsoption' && $gsid){
	empty($gmissions[$gsid]) && cls_message::show('请指定正确的采集任务');
	$page = !empty($page) ? max(1, intval($page)) : 1;
	submitcheck('bfilter') && $page = 1;
	$viewdetail = empty($viewdetail) ? 0 : $viewdetail;
	$gathered = isset($gathered) ? $gathered : '-1';
	$outputed = isset($outputed) ? $outputed : '-1';
	$abover = isset($abover) ? $abover : '-1';
	$keyword = empty($keyword) ? '' : $keyword;

	$filterstr = '';
	foreach(array('viewdetail','gathered','outputed','abover','keyword') as $k) $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

	$wheresql = "WHERE gsid='$gsid'";
	$gathered != '-1' && $wheresql .= " AND gatherdate".($gathered ? '!=' : '=')."'0'";
	$outputed != '-1' && $wheresql .= " AND outputdate".($outputed ? '!=' : '=')."'0'";
	$abover != '-1' && $wheresql .= " AND abover='$abover'";
	$keyword && $wheresql .= " AND utitle ".sqlkw($keyword);
	if(!submitcheck('barcsedit')){
		echo form_str($actionid.'arcsedit',"?entry=gmissions&action=contentsoption&gsid=$gsid&page=$page");
		tabheader_e();
		echo "<tr><td colspan=\"2\" class=\"txt txtleft\">";
		echo "&nbsp; <input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\"  title=\"搜索采集标题\" style=\"vertical-align: middle;\">&nbsp; ";
		$gatheredarr = array('-1' => '采集状态','0' => '未采集','1' => '已采集');
		echo "<select style=\"vertical-align: middle;\" name=\"gathered\">".makeoption($gatheredarr,$gathered)."</select>&nbsp; ";
		$outputedarr = array('-1' => '入库状态','0' => '未入库','1' => '已入库');
		echo "<select style=\"vertical-align: middle;\" name=\"outputed\">".makeoption($outputedarr,$outputed)."</select>&nbsp; ";
		$aboverarr = array('-1' => '是否完结合辑','0' => '未完结','1' => '完结');
		echo "<select style=\"vertical-align: middle;\" name=\"abover\">".makeoption($aboverarr,$abover)."</select>&nbsp; ";
		echo strbutton('bfilter','筛选');
		echo "</td></tr>";
		tabfooter();

		$pagetmp = $page;
		do{
			$query = $db->query("SELECT * FROM {$tblprefix}gurls $wheresql ORDER BY guid DESC LIMIT ".(($pagetmp - 1) * $atpp).",$atpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);
		$itemstr = '';
		while($row = $db->fetch_array($query)){
			$gatherstr = $row['gatherdate'] ? date("Y-m-d",$row['gatherdate']) : '-';
			$outputstr = $row['outputdate'] ? date("Y-m-d",$row['outputdate']) : '-';
			$gurl1str = $row['gurl1'] ? "<a href=$row[gurl1] target=\"_blank\">查看</a>" : '-';
			$gurl2str = $row['gurl2'] ? "<a href=$row[gurl2] target=\"_blank\">查看</a>" : '-';
			$aboverstr = $row['abover'] ? 'Y' : '-';
			$itemstr .= "<tr class=\"txt\"><td class=\"txtC\"><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$row[guid]]\" value=\"$row[guid]\">\n".
				"<td class=\"txtL\"><a href=$row[gurl] target=\"_blank\">$row[utitle]</a></td>\n".
				"<td class=\"txtC\">$gurl1str</td>\n".
				"<td class=\"txtC\">$gurl2str</td>\n".
				"<td class=\"txtC\">$gatherstr</td>\n".
				"<td class=\"txtC\">$outputstr</td>\n".
				"<td class=\"txtC\">$aboverstr</td>\n".
				"<td class=\"txtC\"><a href=\"?entry=gmissions&action=contentdetail&guid=$row[guid]\" onclick=\"return floatwin('open_newgmission',this)\">查看</a></td></tr>\n";
		}
		$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}gurls $wheresql");
		$multi = multi($counts,$atpp,$page, "?entry=gmissions&action=contentsoption&gsid=$gsid$filterstr");

		tabheader('内容采集管理'.'-'.$gmissions[$gsid]['cname']."&nbsp; &nbsp; <input class=\"checkbox\" type=\"checkbox\" name=\"select_all\" value=\"1\">&nbsp;".'全选所有页内容','','',8);
		trcategory(array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">",'内容网址','追溯网址1','追溯网址2','采集','入库','完结','结果'));
		echo $itemstr;
		tabfooter();
		echo $multi;

		tabheader('操作项目');
		$soperatestr = '';
		$s_arr = array('delete' => '删除','gather' => '采集','output' => '入库','regather' => '重置状态');
		foreach($s_arr as $k => $v) $soperatestr .= "<input class=\"radio\" type=\"radio\" id=\"arcdeal_$k\" name=\"arcdeal\" value=\"$k\"" . ($k == 'delete' ? ' onclick="deltip()"' : ''). " /><label for=\"arcdeal_$k\">$v</label> &nbsp;";
		trbasic('选择操作项目','',$soperatestr,'');
		$aboverarr = array(0 => '未完结',1 => '已完结');
		trbasic("<input class=\"radio\" type=\"radio\" name=\"arcdeal\" value=\"abover\">&nbsp;".'合辑完结','',makeradio('arcabover',$aboverarr),'');
		tabfooter('barcsedit');
	}else{
		if(empty($selectid) && empty($select_all)) cls_message::show('请选择网址',"?entry=gmissions&action=contentsoption&gsid=$gsid$filterstr");
		if(!empty($select_all)){
			$parastr = "";
			foreach(array('arcabover') as $k) $parastr .= "&$k=".$$k;
			$selectid = array();
			$npage = empty($npage) ? 1 : $npage;
			if(empty($pages)){
				$counts = $db->result_one("SELECT count(*) FROM {$tblprefix}gurls $wheresql");
				$pages = @ceil($counts / $atpp);
			}
			if($npage <= $pages){
				$fromstr = empty($fromid) ? "" : "guid<$fromid";
				$nwheresql = !$wheresql ? ($fromstr ? "WHERE $fromstr" : "") : ($wheresql.($fromstr ? " AND " : "").$fromstr);
				$query = $db->query("SELECT guid FROM {$tblprefix}gurls $nwheresql ORDER BY guid DESC LIMIT 0,$atpp");
				while($item = $db->fetch_array($query)) $selectid[] = $item['guid'];
			}
		}
		if(!empty($arcdeal)){
			if($arcdeal == 'delete'){
				$idstr = multi_str($selectid);
				$db->query("DELETE FROM {$tblprefix}gurls WHERE guid $idstr OR pid $idstr", 'UNBUFFERED');
			}elseif($arcdeal == 'gather'){
				$progress = new Progress();
				$gather = new cls_gather;
				$gather->set_mission($gsid);
				foreach($selectid as $guid) $gather->gather_guid($guid,0);
				unset($gather);
			}elseif($arcdeal == 'output'){
				$progress = new Progress();
				$gather = new cls_gather;
				$gather->set_mission($gsid);
				foreach($selectid as $guid) $gather->output_guid($guid);
				unset($gather);
			}elseif($arcdeal == 'abover'){
				$gmissions[$gsid]['sonid'] && $db->query("UPDATE {$tblprefix}gurls SET abover='$arcabover' WHERE guid ".multi_str($selectid),'UNBUFFERED');
			}elseif($arcdeal == 'regather'){
				$db->query("UPDATE {$tblprefix}gurls SET gatherdate=0,outputdate=0 WHERE guid ".multi_str($selectid),'UNBUFFERED');
			}
		}
		empty($progress) || $progress->hide();
		if(!empty($select_all)){
			$npage ++;
			if($npage <= $pages){
				$fromid = min($selectid);
				$transtr = '';
				$transtr .= "&select_all=1";
				$transtr .= "&pages=$pages";
				$transtr .= "&npage=$npage";
				$transtr .= "&barcsedit=1";
				$transtr .= "&fromid=$fromid";
				cls_message::show("文件操作正在进行中...<br>全部 $pages 页,正在处理 $npage 页<br><br>
				<a href=\"?entry=gmissions&action=contentsoption&gsid=$gsid$filterstr\">>>中止当前操作</a>",
				"?entry=gmissions&action=contentsoption&gsid=$gsid&page=$page$filterstr$transtr$parastr&arcdeal=$arcdeal",200);
			}
		}
		adminlog('内容收集管理');
		cls_message::show('采集批量操作完成！',"?entry=gmissions&action=contentsoption&gsid=$gsid$filterstr");
	}
}elseif($action == 'contentdetail' && $guid){
	if(!$item = $db->fetch_one("SELECT * FROM {$tblprefix}gurls WHERE guid=".$guid)) cls_message::show('请指定正确的采集记录！');
	tabheader('采集结果');
	trbasic('网址标题','',mhtmlspecialchars($item['utitle']),'');
	trbasic('内容网址','',$item['gurl'] ? "<a href=\"$item[gurl]\" target=\"_blank\">$item[gurl]</a>" : '-','');
	trbasic('追溯网址'.'1','',$item['gurl1'] ? "<a href=\"$item[gurl1]\" target=\"_blank\">$item[gurl1]</a>" : '-','');
	trbasic('追溯网址'.'2','',$item['gurl2'] ? "<a href=\"$item[gurl2]\" target=\"_blank\">$item[gurl2]</a>" : '-','');
	if($item['contents']){
		$item['contents'] = unserialize($item['contents']);
		$chid = $gmodels[$gmissions[$item['gsid']]['gmid']]['chid'];
		$fields = cls_cache::Read('fields',$chid);
		$cotypes = cls_cache::Read('cotypes');
		$cfields = array('caid'=>array('datatype'=>'select','cname'=>'栏目'));
		foreach($cotypes as $k=>$v){
			$cfields['ccid'.$k]['datatype'] = $v['asmode'] ? 'mselect' : 'select';
			$cfields['ccid'.$k]['cname'] = $v['cname'];
		}
		$fields = $cfields + $fields + array('jumpurl'=>array('datatype'=>'text','cname'=>'跳转URL'),'createdate'=>array('datatype'=>'text','cname'=>'添加时间'),'mname'=>array('datatype'=>'text','cname'=>'会员名称'));;
		foreach($item['contents'] as $k => $v){
			trbasic('['.$fields[$k]['cname'].']'.'采集结果','',mhtmlspecialchars($v),'');
		}
	}elseif($item['outputdate']){
		trbasic('采集结果','','已入库','');
	}
	tabfooter();
}elseif($action == 'urlsauto' && $gsid){
	empty($gmissions[$gsid]) && cls_message::show('请指定正确的采集任务');
	if(!submitcheck('confirm')){
		$message = '您选择了批量采集当前任务(含辑内任务)的内容网址！<br>提示：一键全部完成包含这步操作'."<br><br>";
		$message .= "确认请点击>><a href=?entry=gmissions&action=urlsauto&gsid=$gsid&confirm=ok>开始</a>";
		cls_message::show($message);
	}
	$gather = new cls_gather;
	$gather->set_mission($gsid);
	$surls = $gather->fetch_surls();
	$progress = new Progress();
	foreach($surls as $surl) $gather->fetch_gurls($surl);
	unset($gather);
	$progress->hide();
	adminlog('链接自动采集');
	cls_message::show('内容网址采集完成');

}elseif($action == 'gatherauto' && $gsid){
	empty($gmissions[$gsid]) && cls_message::show('请指定正确的采集任务');
	if(!submitcheck('confirm')){
		$message = '您选择了采集当前任务(含辑内任务)的文档内容！<br>提示：一键全部完成包含了本步的操作。<br><br>';
		$message .= "确认请点击>><a href=?entry=gmissions&action=gatherauto&gsid=$gsid&confirm=ok>开始</a>";
		cls_message::show($message);
	}
	$gmission = cls_cache::Read('gmission',$gsid,'');
	//已采集但未完结的合辑中的子内容也需要采集
	$wheresql = "WHERE gsid='$gsid' AND ".($gmission['sonid'] ? 'abover=0' : 'gatherdate=0');
	if(empty($pages)){
		if(!$nums = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}gurls $wheresql")) cls_message::show('无采集项目');
		$pages = @ceil($nums / $atpp);
		$npage = $fromid = 0;
	}
	$npage = empty($npage) ? 0 : $npage;
	$gather = new cls_gather;
	$gather->set_mission($gsid);
	$gather->gather_fields();//先行分析采集规则
	empty($gather->fields) && cls_message::show('请设置采集规则!');
	$progress = new Progress();
	$query = $db->query("SELECT guid FROM {$tblprefix}gurls $wheresql AND guid>'$fromid' ORDER BY guid ASC LIMIT 0,$atpp");
	while($row = $db->fetch_array($query)){
		$gather->gather_guid($row['guid'],0);
		$fromid = $row['guid'];
	}
	unset($gather);
	$npage ++;
	if($npage <= $pages){
		cls_message::show("文件操作正在进行中...<br>共 $pages 页，正在处理第 $npage 页<br><br><a href=\"?entry=gmissions&action=gmissionsedit\">>>中止当前操作</a>","?entry=gmissions&action=gatherauto&gsid=$gsid&pages=$pages&npage=$npage&fromid=$fromid&confirm=ok");
	}
	$progress->hide();
	adminlog('内容自动采集');
	cls_message::show('内容自动采集完成');

}elseif($action == 'outputauto' && $gsid){
	empty($gmissions[$gsid]) && cls_message::show('请指定正确的采集任务');
	if(!submitcheck('confirm')){
		$message = "您选择了将当前任务(含辑内任务)中的内容批量入库！<br>提示：一键全部完成包含了本步的操作。<br><br>";
		$message .= "确认请点击>><a href=?entry=gmissions&action=outputauto&gsid=$gsid&confirm=ok>开始</a>";
		cls_message::show($message);
	}
	$gmission = cls_cache::Read('gmission',$gsid,'');
	//已入库但未完结的合辑中的子内容也需要入库
	$wheresql = "WHERE gsid='$gsid' AND gatherdate<>'0' AND ".($gmission['sonid'] ? 'abover=0' : 'outputdate=0');
	if(empty($pages)){
		if(!$nums = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}gurls $wheresql")) cls_message::show('无入库项目');
		$pages = @ceil($nums / $atpp);
		$npage = $fromid = 0;
	}
	$gather = new cls_gather;
	$gather->set_mission($gsid);
	$gather->output_configs();//先行分析入库规则
	empty($gather->oconfigs) && cls_message::show('请设置入库规则!');
	$progress = new Progress();
	$query = $db->query("SELECT guid FROM {$tblprefix}gurls $wheresql AND guid>'$fromid' ORDER BY guid ASC LIMIT 0,$atpp");
	while($row = $db->fetch_array($query)){
		$gather->output_guid($row['guid']);
		$fromid = $row['guid'];
	}
	$progress->hide();
	unset($gather);
	$npage ++;
	if($npage <= $pages){
		cls_message::show("文件操作正在进行中...<br>共 $pages 页，正在处理第 $npage 页<br><br><a href=\"?entry=gmissions&action=gmissionsedit\">>>中止当前操作</a>","?entry=gmissions&action=outputauto&gsid=$gsid&pages=$pages&npage=$npage&fromid=$fromid&confirm=ok");
	}
	adminlog('内容自动入库');
	cls_message::show('内容自动入库完成');
}elseif($action == 'allauto' && $gsid){
	empty($gmissions[$gsid]) && cls_message::show('请指定正确的采集任务');
	if(!submitcheck('confirm')){
		$message = '您选择了一键完成以下操作：<br>网址采集、内容采集、内容入库！<br>执行之前确保所有规则已经设置完成。<br><br>';
		$message .= "确认请点击>><a href=?entry=gmissions&action=allauto&gsid=$gsid&confirm=ok>开始</a>";
		cls_message::show($message);
	}
	$gmission = cls_cache::Read('gmission',$gsid,'');
	$gather = new cls_gather;
	$gather->set_mission($gsid);
	$progress = new Progress();
	if(empty($deal)){
		$surls = $gather->fetch_surls();
		foreach($surls as $surl){
			$gather->fetch_gurls($surl);
		}
		$progress->hide();
		cls_message::show('内容网址采集完毕！<br> 系统将自动转入内容采集！',"?entry=gmissions&action=allauto&gsid=$gsid&deal=gather&confirm=ok");
	}elseif($deal == 'gather'){
		//已采集但未完结的合辑中的子内容也需要采集
		$wheresql = "WHERE gsid='$gsid' AND ".($gmission['sonid'] ? 'abover=0' : 'gatherdate=0');
		if(empty($pages)){
			if(!$nums = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}gurls $wheresql")) cls_message::show('没有需要采集的内容网址！');
			$pages = @ceil($nums / $atpp);
			$npage = $fromid = 0;
		}
		$npage = empty($npage) ? 0 : $npage;
		$gather = new cls_gather;
		$gather->set_mission($gsid);
		$gather->gather_fields();//先行分析采集规则
		empty($gather->fields) && cls_message::show('请检查采集规格的完整性！');
		$query = $db->query("SELECT guid FROM {$tblprefix}gurls $wheresql AND guid>'$fromid' ORDER BY guid ASC LIMIT 0,$atpp");
		while($row = $db->fetch_array($query)){
			$gather->gather_guid($row['guid'],0);
			$fromid = $row['guid'];
		}
		unset($gather);
		$npage ++;
		if($npage <= $pages){
			cls_message::show("文件操作正在进行中...<br>共 $pages 页，正在处理第 ".($npage+1)." 页<br><br><a href=\"?entry=gmissions&action=gmissionsedit\">>>中止当前操作</a>","?entry=gmissions&action=allauto&gsid=$gsid&deal=gather&pages=$pages&npage=$npage&fromid=$fromid&confirm=ok");
		}
		$progress->hide();
		cls_message::show('内容采集完成！<br> 系统即将自动将内容入库！',"?entry=gmissions&action=allauto&gsid=$gsid&deal=output&confirm=ok");
	}elseif($deal == 'output'){
		$progress->hide();
		//已入库但未完结的合辑中的子内容也需要入库
		$wheresql = "WHERE gsid='$gsid' AND gatherdate<>'0' AND ".($gmission['sonid'] ? 'abover=0' : 'outputdate=0');
		if(empty($pages)){
			if(!$nums = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}gurls $wheresql")) cls_message::show('无输出项目',"?entry=gmissions&action=gmissionsedit");
			$pages = @ceil($nums / $atpp);
			$npage = $fromid = 0;
		}
		$gather = new cls_gather;
		$gather->set_mission($gsid);
		$gather->output_configs();//先行分析入库规则
		empty($gather->oconfigs) && cls_message::show('请检查采集规格的完整性！');
		$query = $db->query("SELECT guid FROM {$tblprefix}gurls $wheresql AND guid>'$fromid' ORDER BY guid ASC LIMIT 0,$atpp");
		while($row = $db->fetch_array($query)){
			$gather->output_guid($row['guid']);
			$fromid = $row['guid'];
		}
		unset($gather);
		$npage ++;
		if($npage <= $pages){
			cls_message::show("文件操作正在进行中...<br>共 $pages 页，正在处理第 ".($npage+1)." 页<br><br><a href=\"?entry=gmissions&action=gmissionsedit\">>>中止当前操作</a>","?entry=gmissions&action=allauto&gsid=$gsid&deal=output&pages=$pages&npage=$npage&fromid=$fromid&confirm=ok");
		}
		cls_message::show('一键采集全部过程完成！');
	}
}elseif($action == 'break'){
	cls_message::show('中止操作完成', axaction(2, "?entry=gmissions&action=gmissionsedit"));
}
function gmission_list($gsid = 0){
	global $gmission;
	$gmission = cls_cache::Read('gmission',$gsid,'');
	$gmodel = cls_cache::Read('gmodel',$gmission['gmid'],'');
	$levelstr = !empty($gmission['pid']) ? '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ' : '';
	$addstr = !empty($gmission['pid']) ? 'Y' : (!empty($gmission['sonid']) ? '-' : "<a href=\"?entry=gmissions&action=gmissionadd&pid=$gsid\" onclick=\"return floatwin('open_gmission',this)\">添加</a>");
	$regularstr = "<a href=\"?entry=gmissions&action=gmissionurls&gsid=$gsid\" onclick=\"return floatwin('open_gmission',this)\">规则</a>";
	$gatherstr = !empty($gmission['pid']) ? '&nbsp;' : "<a href=\"?entry=gmissions&action=allauto&gsid=$gsid\" onclick=\"return floatwin('open_gmission_gather',this)\"><b>一键</b></a>&nbsp;" .
				 "<a href=\"?entry=gmissions&action=urlsauto&gsid=$gsid\" onclick=\"return floatwin('open_gmission_gather',this)\">网址</a>&nbsp;" .
				 "<a href=\"?entry=gmissions&action=gatherauto&gsid=$gsid\" onclick=\"return floatwin('open_gmission_gather',this)\">内容</a>&nbsp;" .
				 "<a href=\"?entry=gmissions&action=outputauto&gsid=$gsid\" onclick=\"return floatwin('open_gmission_gather',this)\">入库</a>";
	echo "<tr class=\"txt\">".
		"<td class=\"txtL\">$levelstr<input type=\"text\" size=\"20\" name=\"gmissionsnew[$gsid][cname]\" value=\"$gmission[cname]\"></td>\n".
		"<td class=\"txtC\">$addstr</td>\n".
		"<td class=\"txtC\">$gmodel[cname]</td>\n".
		"<td class=\"txtC w70\">$regularstr</td>\n".
		"<td class=\"txtC w120\">$gatherstr</td>\n".
		"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$gsid]\" value=\"$gsid\" onclick=\"deltip()\">\n".
		"<td class=\"txtC w40\"><a href=\"?entry=gmissions&action=contentsoption&gsid=$gsid\" onclick=\"return floatwin('open_gmission',this)\">管理</a></td>".
		"<td class=\"txtC w60\"><a href=\"?entry=gmissions&action=gmissioncopy&gsid=$gsid\" onclick=\"return floatwin('open_gmission',this)\">复制</a></td>".
		"</tr>\n";
}
function missionfield($cname,$ename,$setting=array(),$datatype='text'){
	global $rprojects,$gmodel;
	$mcell = in_array($datatype,array('images','files','flashs','medias')) ? 1 : 0;//是否是多集模式字段
	$noremote = in_array($datatype,array('int','float','select','mselect','text')) ? 1 : 0;//是否不存在附件下载因素的字段
	${'clearhtml'.$ename} = (isset($setting['clearhtml']) && !$mcell) ? explode(',',$setting['clearhtml']) : array();
	$rpidsarr = array('0' => '不下载远程文件');foreach($rprojects as $k => $v) $rpidsarr[$k] = $v['cname'];
	$frompagearr = array('0' => '基本内容页','1' => '网址列表页','2' => '内容追溯页1','3' => '内容追溯页2');
	//区域id以及区域title的字符串
	$title_str = '';
	$num_str = '';
	if(strstr($ename,'ccid') && ($ccid_arr = cls_cache::Read('coclasses',str_replace('ccid','',$ename)))){
		foreach($ccid_arr as $k => $v){
			$title_str .= "(|)$v[title]";
			$num_str .= "(|)$k";
		}
	}else if(in_array($datatype,array('select','mselect')) && $ename != 'caid'){
		$_field_arr = cls_cache::Read('fields',$gmodel['chid']);
		$_field_innertext = explode("\n",$_field_arr[$ename]['innertext']);	
		foreach($_field_innertext as $v){
			$_temparr = explode('=',str_replace(array("\r","\n"),'',$v));
			$_temparr[1] = isset($_temparr[1]) ? $_temparr[1] : $_temparr[0];		
			$title_str .= "(|)$_temparr[1]";
			$num_str .= "(|)$_temparr[0]";
		}
		unset($_field_arr,$_field_innertext,$_temparr);	
	}
	$title_str = empty($title_str)?'':substr($title_str,3);
	$num_str = empty($num_str)?'':substr($num_str,3);
	echo "<tr class=\"category\"><td class=\"txtL\"><b>[".mhtmlspecialchars($cname)."]</b></td><td colspan=\"3\"></td></tr>";
	echo "<tr>\n".
		"<td width=\"15%\" class=\"txtR\">内容来源页面</td>\n".
		"<td width=\"35%\" class=\"txtL\"><select style=\"vertical-align: middle;\" name=\"fsettingsnew[$ename][frompage]\">".makeoption($frompagearr,empty($setting['frompage']) ? 0 : $setting['frompage'])."</select></td>\n".
		"<td width=\"15%\" class=\"txtR\">结果处理函数</td>\n".
		"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fsettingsnew[$ename][func]\" value=\"".(empty($setting['func']) ? '' : mhtmlspecialchars($setting['func']))."\"></td>\n".
		"</tr>\n";
	if(!$mcell){
		echo "<tr>\n".
			"<td width=\"15%\" class=\"txtR\">字段内容<br>采集模印<br><span onclick=\"replace_html(this,'".$ename."ftag')\" style=\"color:#03F;cursor: pointer;\">(*)</span>&nbsp;<span onclick=\"replace_html(this,'".$ename."ftag')\" style=\"color:#03F;cursor: pointer;\">(?)</span></td>\n".
			"<td class=\"txtL\"><textarea rows=\"5\" id=\"".$ename."ftag\" name=\"fsettingsnew[$ename][ftag]\" cols=\"30\">".(isset($setting['ftag']) ? mhtmlspecialchars($setting['ftag']) : '')."</textarea></td>\n".
			"<td width=\"15%\" class=\"txtR\">清除Html<br><input class=\"checkbox\" type=\"checkbox\" name=\"chk$ename\" onclick=\"checkall(this.form,'clearhtml$ename','chk$ename')\">全选</td>\n".
			"<td class=\"txtL\">";
			$html_arr = array('1'=>'a','2'=>'br','3'=>'table','4'=>'tr','5'=>'td','6'=>'p','7'=>'font','8'=>'div','9'=>'tbody','10'=>'tbody','11'=>'b','12'=>'&amp;nbsp;','13'=>'script');
		foreach($html_arr as $k => $v){
			echo "<input type=\"checkbox\" class=\"checkbox\" name=\"clearhtml{$ename}[]\" value=\"$k\"".(in_array($k,${'clearhtml'.$ename}) ? " checked" : "").">".(in_array($k,array('12'))?$v:"&lt;".($v)."&gt;").($k%4==0?"<br>":'')."\n";
		}
		echo "</td>\n</tr>\n";
		echo "<tr>\n".
			"<td width=\"15%\" class=\"txtR\">替换信息<br> 来源内容<br>".(!empty($title_str) && !empty($num_str)?"<span style=\"color:#03F;cursor: pointer;\" onclick=\"export_ccid('".$ename."','".$title_str."','".$num_str."')\">(导入'".$cname."')</span><span onclick=\"add_html('".$ename."')\" style=\"color:#03F;cursor: pointer;\">(|)</span><span style=\"color:#F03;cursor: pointer;\" onclick=\"clear_ccid('".$ename."')\"><br>(清空'".$cname."')</span>":'')."</td>\n".
			"<td class=\"txtL\"><textarea rows=\"5\" ".(!empty($title_str) && !empty($num_str)?"id=\"".$ename."_from\"":'')." name=\"fsettingsnew[$ename][fromreplace]\" cols=\"30\">".(isset($setting['fromreplace']) ? mhtmlspecialchars($setting['fromreplace']) : '')."</textarea></td>\n".
			"<td width=\"15%\" class=\"txtR\">替换信息<br>=>结果内容</td>\n".
			"<td class=\"txtL\"><textarea rows=\"5\" ".(!empty($title_str) && !empty($num_str)?"id=\"".$ename."_to\"":'')." name=\"fsettingsnew[$ename][toreplace]\" cols=\"30\">".(isset($setting['toreplace']) ? mhtmlspecialchars($setting['toreplace']) : '')."</textarea></td>\n".
			"</tr>\n";
	}else{
		echo "<tr>\n".
			"<td width=\"15%\" class=\"txtR\">列表区域<br>采集模印</td>\n".
			"<td class=\"txtL\"><textarea rows=\"4\" name=\"fsettingsnew[$ename][ftag]\" cols=\"30\">".(isset($setting['ftag']) ? mhtmlspecialchars($setting['ftag']) : '')."</textarea></td>\n".
			"<td width=\"15%\" class=\"txtR\">列表单元分隔标识</td>\n".
			"<td class=\"txtL\"><textarea rows=\"4\" name=\"fsettingsnew[$ename][splittag]\" cols=\"30\">".(isset($setting['splittag']) ? mhtmlspecialchars($setting['splittag']) : '')."</textarea></td>\n".
			"</tr>\n";
		echo "<tr>\n".
			"<td width=\"15%\" class=\"txtR\">单元链接<br>采集模印</td>\n".
			"<td class=\"txtL\"><textarea rows=\"4\" name=\"fsettingsnew[$ename][remotetag]\" cols=\"30\">".(isset($setting['remotetag']) ? mhtmlspecialchars($setting['remotetag']) : '')."</textarea></td>\n".
			"<td width=\"15%\" class=\"txtR\">单元标题<br>采集模印</td>\n".
			"<td class=\"txtL\"><textarea rows=\"4\" name=\"fsettingsnew[$ename][titletag]\" cols=\"30\">".(isset($setting['titletag']) ? mhtmlspecialchars($setting['titletag']) : '')."</textarea></td>\n".
			"</tr>\n";

	}
	if(!$noremote){
		echo "<tr>\n".
			"<td width=\"15%\" class=\"txtR\">远程下载方案</td>\n".
			"<td width=\"35%\" class=\"txtL\"><select style=\"vertical-align: middle;\" name=\"fsettingsnew[$ename][rpid]\">".makeoption($rpidsarr,empty($setting['rpid']) ? 0 : $setting['rpid'])."</select></td>\n".
			"<td width=\"15%\" class=\"txtR\">下载跳转文件样式</td>\n".
			"<td class=\"txtL\"><input type=\"text\" size=\"25\" name=\"fsettingsnew[$ename][jumpfile]\" value=\"".(empty($setting['jumpfile']) ? '' : mhtmlspecialchars($setting['jumpfile']))."\"></td>\n".
			"</tr>\n";
	}
}

function check_rule_urls(&$g){
	!$g['uurls'] && (!$g['uregular'] || !$g['ufromnum'] || !$g['utonum']) && cls_message::show('手动来源网址与序列来源网址至少需要填写一个');
	$g['uspilit'] && $g['uurltag'] || cls_message::show('网址列表分隔符和网址采集模印不能为空');
}

function check_rule_cnts(&$g){
}
?>

