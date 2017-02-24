<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
#set_time_limit(0);
@include_once _08_INCLUDE_PATH.'http.cls.php';
@include_once _08_INCLUDE_PATH.'linkparse.cls.php';
@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'custom.fun.php';
class cls_gather{
	var $gsid = 0;
	var $gmission = array();
	var $fields = array();
	var $oconfigs = array();
	var $urlarr = array();
	var $mpcontent = '';
	var $mplinks = array();
	function __construct(){
		$this->cls_gather();
	}
	function cls_gather(){
	}
	function init(){
		$this->gsid = 0;
		$this->gmission = array();
		$this->fields = array();
		$this->oconfigs = array();
		$this->urlarr = array();
		$this->mpcontent = '';
		$this->mplinks = array();
	}
	function set_mission($gsid){
		if(!($this->gmission = cls_cache::Read('gmission',$gsid,''))) return false;
		$this->gsid = $gsid;
		unset($this->gmission['fsettings'],$this->gmission['dvalues']);//无字段信息及输出设置信息的简化信息
		return true;
	}
	function gather_fields(){
		$gmid = $this->gmission['gmid'];
		$gmodel = cls_cache::Read('gmodel',$gmid,'');
		$gfields = $gmodel['gfields'];
		$chid = $gmodel['chid'];
		$fields = cls_cache::Read('fields',$chid);
        $cotypes = cls_cache::Read('cotypes');
        $cfields = array('caid'=>array('datatype'=>'select','cname'=>'栏目'));
        foreach($cotypes as $k=>$v){
            $cfields['ccid'.$k]['datatype'] = $v['asmode'] ? 'mselect' : 'select';
            $cfields['ccid'.$k]['cname'] = $v['cname'];
        }
        $fields = $cfields + $fields + array('jumpurl'=>array('datatype'=>'text','cname'=>'跳转URL'),'createdate'=>array('datatype'=>'text','cname'=>'添加时间'),'mname'=>array('datatype'=>'text','cname'=>'会员名称'));;
		$gmission = cls_cache::Read('gmission',$this->gsid,'');
		$fsettings = $gmission['fsettings'];
		foreach($fields as $k => $v){
			if(isset($gfields[$k]) && isset($fsettings[$k])){
				$this->fields[$k] = $v + $fsettings[$k];
				$this->fields[$k]['islink'] = $gfields[$k];
				//$this->fields[$k]['rpid'] = empty($this->fields[$k]['rpid']) ? 0 : $this->fields[$k]['rpid'];
                $this->fields[$k]['rpid'] = empty($fsettings[$k]['rpid'])?0:$fsettings[$k]['rpid'];
				$this->fields[$k]['jumpfile'] = empty($this->fields[$k]['jumpfile']) ? '' : $this->fields[$k]['jumpfile'];
			}
		}
		unset($fields,$gmodel,$gfields,$gmission,$fsettings);
	}
	function output_configs(){
		$gmission = cls_cache::Read('gmission',$this->gsid,'');
		$this->oconfigs = $gmission['dvalues'];
		unset($gmission);
	}
	function fetch_surls(){
		$surls = array();
		$this->gmission['uurls'] && $surls = array_filter(explode("\n",$this->gmission['uurls']));
		if($this->gmission['uregular'] && strpos($this->gmission['uregular'],'(*)')>1){
			for($i = $this->gmission['ufromnum'];$i <= $this->gmission['utonum'];$i++){
				$surls[] = str_replace("(*)",$i,$this->gmission['uregular']);
			}
		}
		$this->gmission['udesc'] && krsort($surls);
		return $surls;
	}
	/**  $type 追溯网址类型  1=》追溯网址1  2=》 追溯网址2  默认1
	**/
	function fetch_addurl($surl,$pattern,$reflink,$type=1){
		if(empty($surl) || empty($pattern)) return '';
		$html = $this->onepage($surl);
		if($type=1){
			$addurl = $this->fetch_detail($pattern,$html,$this->gmission['umode1']);
		}else if($type=2){
			$addurl = $this->fetch_detail($pattern,$html,$this->gmission['umode2']);
		}else $addurl = $this->fetch_detail($pattern,$html);
		$addurl = fillurl($addurl,$reflink);
		if($type == 1){    //追溯页1
			if($this->gmission['uinclude1'] && !preg_match('#'.$this->gmission['uinclude1'].'#i',$addurl)) $addurl=''; //需要包含的字符
			if($this->gmission['uforbid1'] && preg_match('#'.$this->gmission['uforbid1'].'#i',$addurl)) $addurl=''; //禁止包含的字符
		}
		else{              //追溯页2
			if($this->gmission['uinclude2'] && !preg_match('#'.$this->gmission['uinclude2'].'#i',$addurl)) $addurl=''; //需要包含的字符
			if($this->gmission['uforbid2'] && preg_match('#'.$this->gmission['uforbid2'].'#i',$addurl)) $addurl='';//禁止包含的字符
		}
		unset($html);
		return $addurl;
	}

	function fetch_gurls($surl,$istest=0){//
		global $db,$tblprefix,$timestamp,$progress;
		$c_upload = cls_upload::OneInstance();
		if(empty($surl) || !($html = $this->onepage($surl)))return false;//源网址不存在或无法读取该页面
		$this->gmission['uregion'] && $html = $this->fetch_detail($this->gmission['uregion'],$html);//取出初始有效范围
		if(!($urlregions = @explode($this->gmission['uspilit'],$html))) return false;//划出url区域
		if($this->gmission['udesc']) krsort($urlregions);//采集顺序
		unset($html);
		if(!$istest){//内容采集时需要从网址列表页中采集的内容预选采集
			$ufields = array();
			empty($this->fields) && $this->gather_fields();//加载任务中的采集字段
			foreach($this->fields as $k => $v){
				if($v['frompage'] == 1) $ufields[] = $k;
			}
		}
		$linkcount = 0;
		$rets = array();//测试的返回数组
		foreach($urlregions as $urlregion){//遍历每个url内容区块
			if($istest && count($rets) >= 10) break;//只测试10个网址
			$c_upload->init();
			$this->clean_blank($urlregion);
			if(!$gurl = $this->fetch_detail($this->gmission['uurltag'],$urlregion)) continue;//无法获取内容页的url
			$gurl = fillurl($gurl,!empty($this->gmission['ubase']) ? $this->gmission['ubase'] : $surl);
			if($this->gmission['uinclude'] && !preg_match('#'.$this->gmission['uinclude'].'#i',$gurl)) continue;//需要包含的字符
			if($this->gmission['uforbid'] && preg_match('#'.$this->gmission['uforbid'].'#i',$gurl)) continue;//禁止包含的字符

			$refresh = false;
			if(!$istest && $row = $db->fetch_one("SELECT guid,abover FROM {$tblprefix}gurls WHERE gurl='".addslashes($gurl)."'")){//如果是已存在的网址
				if(!$this->gmission['sonid'] || $row['abover']) continue;//无子任务或合辑已完结，则略过此网址
				$refresh = true;
				$guid = $row['guid'];
			}
			$utitle = $this->fetch_detail($this->gmission['utitletag'],$urlregion);
			$utitle = !$utitle ? '标题不详': addslashes(strip_tags($utitle));
			$gurl1 = $this->fetch_addurl($gurl,$this->gmission['uurltag1'],!empty($this->gmission['ubase0']) ? $this->gmission['ubase0'] : $gurl,1);
			$gurl2 = $this->fetch_addurl($gurl1,$this->gmission['uurltag2'],!empty($this->gmission['ubase1']) ? $this->gmission['ubase1'] : $gurl1,2);
			$linkcount++;
			if(!$istest){//非测试状态，需要采集列表中的内容
				if(!$refresh){
					$contents = array();
					foreach($ufields as $v) $contents[$v] = $this->common_field($v,$urlregion,$gurl);
					$db->query("INSERT INTO {$tblprefix}gurls SET
					gurl='$gurl',
					gurl1='$gurl1',
					gurl2='$gurl2',
					utitle='$utitle',
					contents='".addslashes(serialize($contents))."',
					ufids='".implode(',',$c_upload->ufids)."',
					adddate='$timestamp',
					gsid='".$this->gsid."'");
					$guid = $db->insert_id();
					$progress && $progress->linkcount($linkcount);
				}
				if($this->gmission['sonid'] && $guid) $this->fetch_son_gurls($this->gmission['sonid'],$guid,$gurl,$gurl1,$gurl2,0);//采集合辑中的网址列表
			}else{//测试状态
				$rets[$gurl] = array(
					'utitle' => $utitle,
					'gurl'	 => $gurl,
					'gurl1'	 => $gurl1,
					'gurl2'	 => $gurl2
				);
#				$this->gmission['sonid'] && $rets = $rets + $this->fetch_son_gurls($this->gmission['sonid'],0,$gurl,$gurl1,$gurl2,1);
			}
		}
		unset($ufields,$urlregions,$urlregion,$contents);
		if($istest) return $rets;
	}
	function fetch_son_gurls($gsid=0,$guid=0,$url0='',$url1='',$url2='',$istest=0){//采集或测试合辑内的网址列表
		global $db,$tblprefix,$timestamp,$progress;
		$c_upload = cls_upload::OneInstance();
		$rets = array();
		if(!$gsid) return $rets;
		$ng = new cls_gather;
		$ng->set_mission($gsid);
		$gmission = &$ng->gmission;
		$surl = ${'url'.$gmission['ufrompage']};//采集网址列表的源url
		if(!$gmission['pid'] || !$surl || !($html = $ng->onepage($surl))) return $rets;//如果不是子任务或网址源url不存在或源url页面采不到内容
		$html = $ng->fetch_detail($ng->gmission['uregion'],$html);//初始值范围
		$urlregions = explode($ng->gmission['uspilit'],$html);//分隔标记拆分
		if($ng->gmission['udesc']) krsort($urlregions);//采集顺序
		unset($html);
		$ufields = array();
		empty($ng->fields) && $ng->gather_fields();
		foreach($ng->fields as $k => $v) $v['frompage'] == 1 && $ufields[] = $k;
		$linkcount = 0;
		$ubase = $this->gmission['ubase' . $gmission['ufrompage']];
		foreach($urlregions as $urlregion){//每个url区块
			$c_upload->init();
			$ng->clean_blank($urlregion);
			if(!$gurl = $ng->fetch_detail($ng->gmission['uurltag'],$urlregion)) continue;//url模印
			$gurl = fillurl($gurl,$ubase ? $ubase : $surl);//补全url
			if($ng->gmission['uinclude'] && !preg_match('#'.$ng->gmission['uinclude'].'#i',$gurl)) continue;
			if($ng->gmission['uforbid'] && preg_match('#'.$ng->gmission['uforbid'].'#i',$gurl)) continue;

			if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}gurls WHERE gurl='".addslashes($gurl)."'")) continue;//如果是已存在的网址
			$utitle = $ng->fetch_detail($ng->gmission['utitletag'],$urlregion);//标题
			$utitle = !$utitle ? '标题不详': strip_tags($utitle);
			$gurl1 = $ng->fetch_addurl($gurl,$ng->gmission['uurltag1'],$gmission['ubase0'] ? $gmission['ubase0'] : $gurl,1);//追溯页1
			$gurl2 = $ng->fetch_addurl($gurl1,$ng->gmission['uurltag2'],$gmission['ubase1'] ? $gmission['ubase1'] : $gurl1,2);//追溯页2
			$linkcount++;
			$contents = array();
			if(!$istest){
				foreach($ufields as $v) $contents[$v] = $ng->common_field($v,$urlregion,$gurl);//需要在列表页中采集的内容，在采集网址的同时采集内容
			}
			if($istest){//合辑需要将其子任务的网址列出来，
				$rets[$gurl]['utitle'] = $utitle;
				$rets[$gurl]['gurl'] = $gurl;
				$rets[$gurl]['gurl1'] = $gurl1;
				$rets[$gurl]['gurl2'] = $gurl2;
				$rets[$gurl]['son'] = 1;
			}else{//将网址及内容存入数据库中
				$db->query("INSERT INTO {$tblprefix}gurls SET
				pid='$guid',
				gurl='$gurl',
				gurl1='$gurl1',
				gurl2='$gurl2',
				utitle='$utitle',
				contents='".addslashes(serialize($contents))."',
				ufids='".implode(',',$c_upload->ufids)."',
				adddate='$timestamp',
				gsid='".$ng->gsid."'");
			}
		}
		$progress && $progress->linkcount($linkcount);
		unset($ng,$urlregions,$urlregion,$ufields,$contents);
		return $rets;
	}
	function gather_sonid($pid=0,$gsid=0){//采集合辑中的未采集项目
		global $db,$tblprefix,$timestamp;
		if(!$pid || !$gsid) return;
		$ng = new cls_gather;
		$ng->set_mission($gsid);
		$ng->gather_fields();//先行分析采集规则
		if(empty($ng->fields)) return;
		$query = $db->query("SELECT guid FROM {$tblprefix}gurls WHERE gsid='$gsid' AND gatherdate='0' AND pid='$pid' ORDER BY guid ASC");
		while($row = $db->fetch_array($query)){
			$ng->gather_guid($row['guid'],0);
		}
		unset($ng);
	}
	function gather_guid($guid=0,$istest=0,$item=0){//只采集未采内容
		global $db,$tblprefix,$timestamp,$progress;
		$c_upload = cls_upload::OneInstance();
		if((!$guid || !($item = $db->fetch_one("SELECT * FROM {$tblprefix}gurls WHERE guid='$guid'"))) && !$item) return false;
		if(empty($item['gatherdate'])){//未采内容
			$contents = empty($item['contents']) ? array() : unserialize($item['contents']);
			unset($item['contents']);
			if(empty($this->fields)) $this->gather_fields();
			if(empty($this->fields)) return false;
			$fields0 = $fields2 = $fields3 = array();
			foreach($this->fields as $k => $v){
				if($v['frompage'] == '0'){
					$fields0[] = $k;
				}elseif($v['frompage'] == '2' && $item['gurl1']){
					$fields2[] = $k;
				}elseif($v['frompage'] == '3' && $item['gurl2']){
					$fields3[] = $k;
				}
			}
			$c_upload->init();
			if(!empty($fields0)){
				$html = $this->onepage($item['gurl']);
				foreach($fields0 as $k) $contents[$k] = $istest && !$html ? false : $this->one_content($k,$html,$item['gurl'],0);
			}
			if(!empty($fields2)){
				$html = $this->onepage($item['gurl1']);
				foreach($fields2 as $k) $contents[$k] = $istest && !$html ? false : $this->one_content($k,$html,$item['gurl1'],1);
			}
			if(!empty($fields3)){
				$html = $this->onepage($item['gurl2']);
				foreach($fields3 as $k) $contents[$k] = $istest && !$html ? false : $this->one_content($k,$html,$item['gurl2'],2);
			}
			if(!$istest){
				$item['ufids'] .= ($item['ufids'] && $c_upload->ufids ? ',' : '').implode(',',$c_upload->ufids);
				$db->query("UPDATE {$tblprefix}gurls SET
							contents = '".addslashes(serialize($contents))."',
							ufids = '$item[ufids]',
							gatherdate = '$timestamp'
							WHERE guid='$guid'");
			}
			$progress && $progress->content(1);
		}
		if(!$istest && $this->gmission['sonid'] && !$item['abover']) $this->gather_sonid($guid,$this->gmission['sonid']);//非测试时,采集合辑中的网址内容
		return $istest ? $contents : true;
	}
	function output_sonid($pid=0,$gsid=0){//将合辑中的未采集项目入库
		global $db,$tblprefix,$timestamp;
		if(!$pid || !$gsid) return;
		$ng = new cls_gather;
		$ng->set_mission($gsid);
		$ng->output_configs();//先分析是否进行入库规则设置
		if(empty($ng->oconfigs)) return;
		$query = $db->query("SELECT guid FROM {$tblprefix}gurls WHERE gsid='$gsid' AND outputdate='0' AND gatherdate<>'0' AND pid='$pid' ORDER BY guid ASC");
		while($row = $db->fetch_array($query)) $ng->output_guid($row['guid']);
		unset($ng);
	}
	function output_guid($guid=0){//禁止重复输出,未完结合辑需要输出辑内的内容
		global $db,$tblprefix,$timestamp,$progress;
		if(!$guid || !($item = $db->fetch_one("SELECT * FROM {$tblprefix}gurls WHERE guid='$guid' AND gatherdate<>'0'"))) return false;
		$c_upload = cls_upload::OneInstance();
		$curuser = cls_UserMain::CurUser();
		if(!$item['outputdate']){
			$archivenew = empty($item['contents']) ? array() : unserialize($item['contents']);
			unset($item['contents']);
			empty($this->fields) && $this->gather_fields();
			empty($this->oconfigs) && $this->output_configs();
			if(empty($this->fields) || empty($this->oconfigs)) return false;
			if(!empty($this->oconfigs['musts'])){
				$mustsarr = explode(',',$this->oconfigs['musts']);
				foreach($mustsarr as $k){
					if(empty($archivenew[$k])) return false;//缺少必有字段内容，输出中止
				}
			}
			$gmodels = cls_cache::Read('gmodels');
			$gmid = $this->gmission['gmid'];
			$chid = $gmodels[$gmid]['chid'];
			$fields = cls_cache::Read('fields',$chid);

			$c_upload->init();
			$arc = new cls_arcedit;
			if($aid = $item['aid']){
				if(!$arc->set_aid($aid,array('chid'=>$chid,'ch'=>1)) && !$arc->arcadd($chid,@$this->oconfigs['caid'],$aid))return false;
			}else{
				$catalogs = cls_cache::Read('catalogs');
				if(empty($catalogs[@$archivenew['caid']])) $archivenew['caid'] = '';
				if(!($aid = $arc->arcadd($chid,empty($archivenew['caid']) ? @$this->oconfigs['caid'] : $archivenew['caid']))) return false;
			}
			$cotypes = cls_cache::Read('cotypes');
			foreach($cotypes as $k => $v){
				if(!empty($archivenew["ccid$k"])){
					$newccid = array_filter(explode(',',$archivenew["ccid$k"]));
					foreach($newccid as $c) if(!$coclass = cls_cache::Read('coclasses',$k,$c)) unset($newccid[$c]);
					$archivenew["ccid$k"] = implode(',',$newccid);
				}
				isset($this->oconfigs["ccid$k"]) && $arc->arc_ccid(empty($archivenew["ccid$k"]) ? $this->oconfigs["ccid$k"] : $archivenew["ccid$k"],$k);
			}
			foreach($fields as $k => $v){
				if(empty($archivenew[$k]) && isset($this->oconfigs[$k])) $archivenew[$k] = $this->oconfigs[$k];
				if(isset($archivenew[$k])){
					$archivenew[$k] = addslashes($archivenew[$k]);
					$arc->updatefield($k,$archivenew[$k],$v['tbl']);
					if($arr = multi_val_arr($archivenew[$k],$v)) foreach($arr as $x => $y) $arc->updatefield($k.'_'.$x,$y,$v['tbl']);
				}
			}
			//处理会员
			$u = new cls_userbase;
			$mnamearr = empty($this->oconfigs['mname']) ? array() : explode(',',$this->oconfigs['mname']);
			if(!empty($archivenew['mname'])) $u->activeuserbyname($archivenew['mname']);
			if(!empty($mnamearr) && empty($u->info['mid']))	$u->activeuserbyname($mnamearr[array_rand($mnamearr)]);
			if(!empty($u->info['mid'])){
				$arc->updatefield('mid',$u->info['mid']);
				$arc->updatefield('mname',$u->info['mname']);
			}
			//处理时间
			$archivenew['createdate'] = str_replace(array('年','月','日'),array('-','-',''),@$archivenew['createdate']);
			$archivenew['createdate'] = strtotime($archivenew['createdate']) ? strtotime($archivenew['createdate']) : $timestamp;
			$arc->updatefield('createdate',$archivenew['createdate']);
			$arc->updatefield('initdate',$archivenew['createdate']);
			//处理跳转URL
			$arc->updatefield('jumpurl',empty($archivenew['jumpurl']) ? '' : $archivenew['jumpurl']);
			$arc->auto();
			$arc->autocheck();
			$arc->updatedb();
			
			$abrels = cls_cache::Read('abrels');
			if(!empty($item['pid']) && !empty($this->oconfigs['arid']) && isset($abrels[$this->oconfigs['arid']])){
				if($pid = $db->result_one("SELECT aid FROM {$tblprefix}gurls WHERE guid='$item[pid]'")) $arc->set_album($pid,$this->oconfigs['arid']);
			}

			$ufids = $c_upload->ufids + explode(',',$item['ufids']);
			empty($ufids) || $db->query("UPDATE {$tblprefix}userfiles SET aid=$aid WHERE ufid ".multi_str($ufids));

			$db->query("UPDATE {$tblprefix}gurls SET aid='$aid',outputdate='$timestamp',contents='',ufids='' WHERE guid='$guid'");
			$progress && $progress->output(1);
		}
		if($this->gmission['sonid'] && !$item['abover']) $this->output_sonid($guid,$this->gmission['sonid']);//将合辑中的内容入库
		unset($arc,$fields,$field,$item,$archivenew);
		return true;
	}
	function one_content($fname,&$html,$reflink,$reindex){
		$content = '';
		if($fname != $this->gmission['mpfield']){
			$url = empty($this->gmission['ubase' . $reindex]) ? '' : $this->gmission['ubase' . $reindex];
			$content = $this->common_field($fname,$html,$url ? $url : $reflink);
		}else{
			$this->mpfield($fname,$html,$reflink,$reindex);
			$content = $this->mpcontent;
		}
		$this->redeal_content($fname,$content);
		return $content;
	}
	function redeal_content($fname,&$content){//对不同类型的字段作一个再处理及限制
		if($content == '') return;
		empty($this->fields) && $this->gather_fields();
		if(!$field = $this->fields[$fname]) return;
		if(in_array($field['datatype'],array('htmltext','text','select','mselect'))){
			$content = trim($content);
		}elseif(in_array($field['datatype'],array('int','date'))){
			$content = intval($content);
		}elseif($field['datatype'] == 'float'){
			$content = floatval($content);
		}elseif($field['datatype'] == 'multitext'){
			$content = mnl2br(trim($content));
		}
	}
	function mpfield($fname,&$html,$reflink,$reindex,$step=0){
		if(!$html) return '';
		empty($this->fields) && $this->gather_fields();
		if(!$field = $this->fields[$fname]) return;
		if(!$step){
			$this->mpcontent = '';
			$this->mplinks = array();
		}
		$baseurl = $this->gmission['ubase' . $reindex];
		$baseurl || $baseurl = $reflink;
		if($mparea = $this->fetch_detail($this->gmission['mptag'],$html)){
			$mplinks = array_unique(array_merge(array($reflink),$this->searchlinks($mparea,$baseurl)));
		}else $mplinks = array($reflink);
		if(!$this->gmission['mpmode']){//完整分页导航//同样需要处理$step
			foreach($mplinks as $mplink){
				$step ++;
				if($this->gmission['mpinclude'] && !preg_match('#'.$this->gmission['mpinclude'].'#i',$mplink)) continue;
				if($this->gmission['mpforbid'] && preg_match('#'.$this->gmission['mpforbid'].'#i',$mplink)) continue;
				if(in_array($mplink,$this->mplinks)) continue;//重复的页码
				if(!$mphtml = $this->onepage($mplink)) continue;
				if(!in_array($field['datatype'],array('images','files','flashs','medias'))){
					$this->mpcontent .= ($this->mpcontent ? '[##]' : '').$this->common_field($fname,$mphtml,$baseurl);
				}else{
					$contentarr = ($this->mpcontent && is_array(unserialize($this->mpcontent))) ? unserialize($this->mpcontent) : array();
					$contentarr = array_merge($contentarr,unserialize($this->common_field($fname,$mphtml,$baseurl)));
					$this->mpcontent = serialize($contentarr);
					unset($contentarr);
				}
				$this->mplinks[] = $mplink;
			}
		}else{
			if($step > 20) return;
			$continue = 0;
			foreach($mplinks as $mplink){
				if($this->gmission['mpinclude'] && !preg_match('#'.$this->gmission['mpinclude'].'#i',$mplink)) continue;
				if($this->gmission['mpforbid'] && preg_match('#'.$this->gmission['mpforbid'].'#i',$mplink)) continue;
				if(in_array($mplink,$this->mplinks)) continue;
				if(!$mphtml = $this->onepage($mplink)) continue;
				if(!in_array($field['datatype'],array('images','files','flashs','medias'))){
					$this->mpcontent .= ($this->mpcontent ? '[##]' : '').$this->common_field($fname,$mphtml,$baseurl);
				}else{
					$contentarr = ($this->mpcontent && is_array(unserialize($this->mpcontent))) ? unserialize($this->mpcontent) : array();
					$contentarr = array_merge($contentarr,unserialize($this->common_field($fname,$mphtml,$baseurl)));
					$this->mpcontent = serialize($contentarr);
					unset($contentarr);
				}
				$continue = 1;
				$this->mplinks[] = $mplink;
				$nexturl = $mplink;
				$step ++;
			}
			if($continue){
				if(!$mphtml = $this->onepage($nexturl)) return;
				$this->mpfield($fname,$mphtml,$nexturl,$reindex,$step);
			}
		}
		unset($mphtml,$mplinks);
	}
	function common_field($fname,&$html,$reflink){//当前任务，当前url情况下
		if($html == '') return '';
		empty($this->fields) && $this->gather_fields();
		if((!$field = $this->fields[$fname]) || empty($field['ftag'])) return '';
		$linkparse = new linkparse;
		if(!in_array($field['datatype'],array('images','files','flashs','medias'))){
			$content = $this->fetch_detail($field['ftag'],$html);
			$this->c_replace($field['fromreplace'],$field['toreplace'],$content);
			$this->clearhtml($field['clearhtml'],$content);
			$linkparse->setsource($content,$reflink,$field['rpid'],@$field['wmid'],$field['jumpfile']);
			if(!$field['islink']){
				$linkparse->handlelinks();
				$content = $linkparse->html;
			}else{
				$content = $linkparse->handlelink($content);
				if(in_array(mextension($content),array('jpg','gif','png','jpeg','bmp'))){
					$imageinfo = @getimagesize(cls_url::view_url($content));
					!empty($imageinfo) && ($content .= '#'.$imageinfo[0].'#'.$imageinfo[1]);
				}
			}
		}else{
			$content = $this->fetch_detail($field['ftag'],$html);
			$fregions = explode($field['splittag'],$content);
			$furls = array();
			$linkparse->setsource('',$reflink,$field['rpid'],$field['wmid'],$field['jumpfile']);
			foreach($fregions as $fregion){
				$urlarr = array();
				$this->clean_blank($fregion);
				if(!$furl = $this->fetch_detail($field['remotetag'],$fregion)) continue;
				$furl = $linkparse->handlelink($furl);
				$urlarr['remote'] = $furl;
				if($field['datatype'] == 'images'){
					$imageinfo = @getimagesize(cls_url::view_url($furl));
					!empty($imageinfo[0]) && ($urlarr['width'] = $imageinfo[0]);
					!empty($imageinfo[1]) && ($urlarr['height'] = $imageinfo[1]);
				}
				$urlarr['title'] = $this->fetch_detail($field['titletag'],$fregion);
				$furls[] = $urlarr;
			}
			$content = serialize($furls);
		}
		!empty($field['func']) && $this->func_deal($field['func'],$content);
		unset($linkparse,$urlarr,$furls);
		return $content;
	}
	function func_deal($funcstr,&$content){
		if(empty($funcstr) || empty($content) || !in_str('(*)',$funcstr)) return;
		$funcname = substr($funcstr,0,strpos($funcstr,'('));
		if(empty($funcname) || !function_exists($funcname)) return;
		$content = str_replace( "'","\'", $content); //处理匹配字符串有单引号的情况进行转义
		$funcstr = str_replace('(*)',"'".$content."'",$funcstr);//将匹配的字符串当作php字符串（加单引号）
		@eval("\$result = $funcstr;");
		$content = $result;
		unset($result);
	}
	function onepage($url){
		global $mcharset,$progress;
		$timeout = $this->gmission['timeout'] ? $this->gmission['timeout'] : 0xffff;
        // 把域名里的&amp转回&字符
        $url = htmlspecialchars_decode($url);
		if($this->gmission['mcookies']){
			$m_http = new http;
			$m_http->timeout = $timeout;
			$m_http->setCookies($this->gmission['mcookies']);
			$html = $m_http->fetchtext($url);
			unset($m_http);
		}else $html = html_get_contents("compress.zlib://".$url,$timeout);//url前面添加前缀：compress.zlib:// 是为了防止文件经过gzip压缩变过之后，导致获取到的页面内容为乱码。该前缀不管文件是否经过gzip压缩，都可以正常运行。
		$html = cls_string::iconv($this->gmission['mcharset'],$mcharset,$html);
		$this->clean_blank($html);
		$progress && $progress->pagecount(1);
		return $html;
	}
	function c_replace(&$fromreplace,&$toreplace,&$content){
		if(!$fromreplace || !$content) return;
		$fromarr = explode("(|)",$fromreplace);
		$toarr = explode("(|)",$toreplace);
		foreach($fromarr as $k => $fromtag){
			$totag = isset($toarr[$k]) ? $toarr[$k] : '';
			$tags = explode('(*)',$fromtag);
			if(count($tags) > 1 && ($tags[0] || $tags[1])){
				$stag = $this->regencode($tags[0]);
				$etag = $this->regencode($tags[1]);
				$content=preg_replace("/".$stag."(.*?)".$etag."/is",$totag,$content);
			}else $content = str_replace($fromtag,$totag,$content);
		}
	}
	   /** @param $umode 模印匹配模式 
					  1 完全匹配
					  0 非完全匹配 
	**/
	function fetch_detail($tagstr,&$html,$umode=0){
		if(!$tagstr) return '';#static $debug = 0;$debug++;if($debug > 1)exit;
		$this->clean_blank($tagstr);
		$pos = strpos($tagstr, '(*)');
		if(!$pos || $pos + 3 == strlen($pos)) return '';//echo "\n/" . $this->regencode($tagstr) . "/is\n";exit();
		if(!preg_match('/' . $this->regencode($tagstr) . '/is', $html, $matches)) return '';
		#var_dump($matches);
		if($umode == 1){
			$fetchstr = &$matches[0];
		}else{
			$fetchstr = &$matches[1];
		}
		$this->clean_blank($fetchstr);
		unset($html,$tagstr,$matches);
		return $fetchstr;
	}
	function searchlinks($html,$reflink){
		$links = array();
		$aregions = array();

		$regex = "/<a(.+?)href[ ]*=[ |'|\"]*(.+?)[ |'|\"]+/is";
		if(preg_match_all($regex,$html,$matches)){
			$aregions = array_unique($matches[2]);
			foreach($aregions as $aregion){
				$aregion = fillurl($aregion,$reflink);
				$links[] = $aregion;
			}
		}
		return $links;
	}
	function clean_blank(&$str){
		$str=preg_replace("/([\r\n|\r|\n]*)/is","",$str);
		$str=preg_replace("/>([\s]*)</is","><",$str);
		$str=preg_replace("/^([ ]*)/is","",$str);
		$str=preg_replace("/([ ]*)$/is","",$str);
	}
	function regencode($str){
		$search  = array("\\",'"',".","[", "]","(", ")","?","+","*","^","{","}","$","|","/","\(\?\)","\(\*\)");
		$replace = array("\\\\",'\"',"\.","\[","\]","\(","\)","\?","\+","\*","\^","\{","\}","\$","\|","\/",".*?","(.*?)");
		return str_replace($search,$replace,$str);
	}
	function clearhtml(&$serial,&$str){
		if(!$serial || !$str) return;
		$ids = array_filter(explode(',',$serial));
		$search = array(
					  "/<a[^>]*?>(.*?)<\/a>/is",
					  "/<br[^>]*?>/i",
					  "/<table[^>]*?>([\s\S]*?)<\/table>/i",
					  "/<tr[^>]*?>([\s\S]*?)<\/tr>/i",
					  "/<td[^>]*?>([\s\S]*?)<\/td>/i",
					  "/<p[^>]*?>([\s\S]*?)<\/p>/i",
					  "/<font[^>]*?>([\s\S]*?)<\/font>/i",
					  "/<div[^>]*?>([\s\S]*?)<\/div>/i",
					  "/<span[^>]*?>([\s\S]*?)<\/span>/i",
					  "/<tbody[^>]*?>([\s\S]*?)<\/tbody>/i",
					  "/<([\/]?)b>/i",
					  "/<img[^>]*?>/i",
					  "/&nbsp;/i",
					  "/<script[^>]*?>([\w\W]*?)<\/script>/i",
					  );
		$replace = array(
					   "\\1",
					   "",
					   "\\1",
					   "\\1",
					   "\\1",
					   "\\1",
					   "\\1",
					   "\\1",
					   "\\1",
					   "\\1",
					   "",
					   "",
					   "",
					   "\\1",
					   );
		foreach($ids as $id) $str = preg_replace($search[$id-1],$replace[$id-1],$str);
	}
}
function fillurl($surl,$refhref,$basehref=''){//$refhref用以参照的完全网址
	$surl = trim($surl);
	$refhref = trim($refhref);
	$basehref = trim($basehref);
	if($surl == '') return '';

	if($basehref){
		$preurl = strtolower(substr($surl,0,6));
		if(in_array($preurl,array('http:/','ftp://','mms://','rtsp:/','thunde','emule:','ed2k:/'))){
			return  $surl;
		}else{
			return $basehref.'/'.$surl;
		}
	}

	$urlparses = @parse_url($refhref);
	$homeurl = $urlparses['host'];
	$baseurlpath = $homeurl.$urlparses['path'];
	$baseurlpath = preg_replace("/\/([^\/]*)\.(.*)$/","/",$baseurlpath);
	$baseurlpath = preg_replace("/\/$/","",$baseurlpath);

	$i = $pathstep = 0;
	$dstr = $pstr = $okurl = '';
	$surl = (strpos($surl,"#") > 0) ? substr($surl,0,strpos($surl,"#")) : $surl;
	if($surl[0]=="/"){//不含http的绝对网址
		$okurl = "http://".$homeurl.$surl;
	}elseif($surl[0] == "."){//相对网址
		if(strlen($surl) <= 1){
			return "";
		}elseif($surl[1] == "/"){
			$okurl = "http://".$baseurlpath."/".substr($surl,2,strlen($surl)-2);
		}else{
			$urls = explode("/",$surl);
			foreach($urls as $u){
				if($u == ".."){
					$pathstep++;
				}elseif($i < count($urls) - 1){
					$dstr .= $urls[$i]."/";
				}else{
					$dstr .= $urls[$i];
				}
				$i++;
			}
			$urls = explode("/",$baseurlpath);
			if(count($urls) <= $pathstep){
				return "http://".$baseurlpath.'/'.$dstr;
			}else{
				$pstr = "http://";
				for($i = 0;$i < count($urls)-$pathstep;$i++){
					$pstr .= $urls[$i]."/";
				}
				$okurl = $pstr.$dstr;
			}
		}
	}else{
		$preurl = strtolower(substr($surl,0,6));
		if(strlen($surl)<7){
			$okurl = "http://".$baseurlpath."/".$surl;
		}elseif(in_array($preurl,array('http:/','ftp://','mms://','rtsp:/','thunde','emule:','ed2k:/'))){
			$okurl = $surl;
		}else $okurl = "http://".$baseurlpath."/".$surl;
	}

	$preurl = strtolower(substr($okurl,0,6));
	if(in_array($preurl,array('ftp://','mms://','rtsp:/','thunde','emule:','ed2k:/'))){
		return $okurl;
	}else{
		$okurl = preg_replace("/^(http:\/\/)/","",$okurl);
		$okurl = preg_replace("/\/{1,}/","/",$okurl);
		return "http://".$okurl;
	}
}

?>
