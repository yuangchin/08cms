<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('mchannel')) cls_message::show($re);
foreach(array('grouptypes','currencys','mchannels',) as $k) $$k = cls_cache::Read($k);
if(empty($gtid) || empty($grouptypes[$gtid])) cls_message::show('请指定正确的会员组体系');

$grouptype = $grouptypes[$gtid];
$usergroups = fetch_arr();
$gtcname = $grouptypes[$gtid]['cname'];
$no_deepmode = in_array($gtid,@explode(',',$deep_gtids)) ? $no_deepmode : 0;
if($action == 'usergroupsedit'){
	if(!submitcheck('busergroupsadd') && !submitcheck('busergroupsedit')){
		$items = '';
		foreach($usergroups as $k => $usergroup){
			$items .= "<tr  class=\"txtcenter txt\">".
					"<td class=\"txtC\">$k</td>\n".
					"<td class=\"txtL\"><input type=\"text\" size=\"12\" name=\"usergroupsnew[$k][cname]\" value=\"$usergroup[cname]\"></td>\n".
					"<td class=\"txtC\">" . (empty($usergroup['ico']) ? '-' : "<img src=\"$usergroup[ico]\" border=\"0\" onload=\"if(this.height>20) {this.resized=true; this.height=20;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\">") . "</td>\n".
					"<td class=\"txtC\"><input type=\"text\" size=\"3\" maxlength=\"3\" name=\"usergroupsnew[$k][prior]\" value=\"$usergroup[prior]\"></td>\n".
					"<td class=\"txtC\">".($grouptype['mode'] < 2 ? '-' : "<input type=\"text\" size=\"12\" name=\"usergroupsnew[$k][currency]\" value=\"$usergroup[currency]\">")."</td>\n".
					"<td class=\"txtC\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"></td>\n".
					"<td class=\"txtC\"><a href=\"?entry=$entry&action=usergroupcopy&gtid=$gtid&ugid=$k\" onclick=\"return floatwin('open_usergroupsedit',this)\">复制</a></td>\n".
					"<td class=\"txtC\"><a href=\"?entry=$entry&action=usergroupdetail&gtid=$gtid&ugid=$k\" onclick=\"return floatwin('open_usergroupsedit',this)\">设置</a></td></tr>\n";
		}
		tabheader('编辑会员组-'.$grouptype['cname']."&nbsp;&nbsp;&nbsp;&nbsp;".($grouptype['mode'] == 2?"<a href=\"?entry=$entry&action=update_grouptype&gtid=$gtid\" onclick=\"return floatwin('open_inarchive',this)\"><font color=\"#FF0000\">修改全部会员积分等级</font></a>":''),'usergroupsedit',"?entry=$entry&action=usergroupsedit&gtid=$gtid",'7');
		$cr_title = '相关积分';
		if($grouptype['mode'] == 2){
			$cr_title = '基数积分'.'('.$currencys[$grouptype['crid']]['cname'].')';
		}elseif($grouptype['mode'] == 3){
			$cr_title = '兑换积分'.'('.(empty($grouptype['crid']) ? '现金': $currencys[$grouptype['crid']]['cname']).')';
		}
		trcategory(array('ID','会员组|L','图标','排序',$cr_title,"<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"deltip(this,$no_deepmode,checkall,this.form, 'delete', 'chkall')\">删?",'复制','编辑'));
		echo $items;
		tabfooter('busergroupsedit','修改');

		tabheader('添加会员组-'.$grouptype['cname'],'usergroupsadd',"?entry=$entry&action=usergroupsedit&gtid=$gtid");
		trbasic('会员组名称','usergroupadd[cname]');
		($grouptype['mode'] > 1) && trbasic($cr_title,'usergroupadd[currency]');
		tabfooter('busergroupsadd','添加');
		a_guide('usergroupsedit');
	}elseif(submitcheck('busergroupsadd')){
		if(!$usergroupadd['cname']) cls_message::show('会员组资料不完全',"?entry=$entry&action=usergroupsedit&gtid=$gtid");
		$usergroupadd['currency'] = $grouptype['mode'] < 2 ? 0 : max(0,intval($usergroupadd['currency']));
		$db->query("INSERT INTO {$tblprefix}usergroups SET
					ugid=".auto_insert_id('usergroups').",
					cname='$usergroupadd[cname]',
					currency='$usergroupadd[currency]',
					gtid='$gtid'");

		adminlog('添加会员组');
		cls_CacheFile::Update('usergroups',$gtid);
		cls_message::show('会员组添加完成', "?entry=$entry&action=usergroupsedit&gtid=$gtid");
	}elseif(submitcheck('busergroupsedit')){
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $ugid) {
				$db->query("DELETE FROM {$tblprefix}mcnodes WHERE mcnvar='ugid$gtid' AND mcnid='$ugid'");
				$db->query("DELETE FROM {$tblprefix}usergroups WHERE ugid='$ugid'");
				$db->query("UPDATE {$tblprefix}members SET grouptype$gtid=0 WHERE grouptype$gtid='$ugid'",'SILENT');
				unset($usergroupsnew[$ugid]);
			}
			cls_CacheFile::Update('mcnodes');
		}

		if(!empty($usergroupsnew)){
		
			$_update_cu = array();//存放改变了积分的等级的数据
			$_is_chushihua = 0;//是否积分初始化
			$_usergroup_key = array_keys($usergroupsnew);
			$_count_num = count($usergroupsnew);
			$_last_ugid = $_usergroup_key[$_count_num-1];
			$_second_last = $_count_num == 1 ?$_last_ugid:$_usergroup_key[$_count_num-2];
			$_first_ugid = $_usergroup_key[0];			
			
			foreach($usergroupsnew as $ugid => $usergroup){
				$usergroup['currency'] = $grouptype['mode'] < 2 ? 0 : max(0,intval($usergroup['currency']));
				$usergroup['prior'] = max(0,intval($usergroup['prior']));
				$usergroup['cname'] = empty($usergroup['cname']) ? $usergroups[$ugid]['cname'] : $usergroup['cname'];
				if(($usergroup['cname'] != $usergroups[$ugid]['cname']) || ($usergroup['prior'] != $usergroups[$ugid]['prior'] || ($usergroup['currency'] != $usergroups[$ugid]['currency']))){
					if($usergroup['currency'] != $usergroups[$ugid]['currency']){					
						$_new_a1 = $_new_a2 = array();
						foreach($usergroupsnew as $k => $v)	$_new_a1[] = $v['currency'];
						$_new_a2 = $_new_a1;arsort($_new_a2);
						//检测全部的积分是否从大到小排列，为了防止修改时低级的积分比高级的要高					
						if($_new_a1 !== $_new_a2) cls_message::show('积分修改错误，低等级的积分不能高于高等级的积分',M_REFERER);	
						unset($_new_a1,$_new_a2);
						//排除最低级的其他等级，如果积分为0，则$_is_chushihua++，用来判断是否是第一次设置该积分等级操作
						$ugid != $_last_ugid && empty($usergroups[$ugid]['currency']) && $_is_chushihua++;
						$_f_ugid = $ugid == $_first_ugid ?$ugid: $_usergroup_key[array_search($ugid,$_usergroup_key)-1];
						if(empty($_is_chushihua)){
							$_n_ugid = $ugid == $_last_ugid ? $ugid:$_usergroup_key[array_search($ugid,$_usergroup_key)+1];
							$_front_c = $usergroups[$_f_ugid]['currency'];
							$_next_c  = $usergroups[$_n_ugid]['currency'];
						
							if($ugid != $_first_ugid  && $usergroup['currency'] >= $_front_c){
								cls_message::show('积分修改错误，低等级的积分不能高于或者等于修改前的高等级的积分',M_REFERER);
							}
							if($ugid != $_last_ugid && $usergroup['currency'] <= $_next_c){
								cls_message::show('积分修改错误，高等级的积分不能低于或者等于高等级的积分',M_REFERER);
							}
							$_update_cu[$ugid]['oldcurrency']=$usergroups[$ugid]['currency'];
							if($usergroup['currency'] > $usergroups[$ugid]['currency'])	$_update_cu[$ugid]['up'] = 1;							
						}else{//考虑到是否是第一次设置的时候，等级的积分全为0
							$_update_cu[$ugid]['oldcurrency'] = $ugid== $_first_ugid ? 'the_first' : $usergroupsnew[$_f_ugid]['currency'];							
							if($usergroupsnew[$_last_ugid]['currency'] <= 0 && !empty($usergroupsnew[$_second_last]['currency'])){							
								$_update_cu[$_last_ugid]['oldcurrency'] = $usergroupsnew[$_second_last]['currency'];
								$_update_cu[$_last_ugid]['newcurrency'] = 0;
							}
						}
						$_update_cu[$ugid]['newcurrency'] = $usergroup['currency'];
					}
					$db->query("UPDATE {$tblprefix}usergroups SET
								cname='$usergroup[cname]',
								currency='$usergroup[currency]',
								prior='$usergroup[prior]'
								WHERE ugid='$ugid'");
				}
			}
		}
		adminlog('编辑会员组');
		cls_CacheFile::Update('usergroups',$gtid);
		update_grouptype();		
		cls_message::show('会员组修改完成', "?entry=$entry&action=usergroupsedit&gtid=$gtid");
	}
}elseif($action == 'usergroupcopy' && $gtid && $ugid){
	if(!($usergroup = $usergroups[$ugid])) cls_message::show('请指定正确的会员组');
	if(!submitcheck('busergroupcopy')){
		tabheader('会员组复制'.'-'.$grouptype['cname'],'usergroupcopy',"?entry=$entry&action=usergroupcopy&gtid=$gtid&ugid=$ugid",2,0,1);
		trbasic('源会员组名称','',$usergroup['cname'],'');
		trbasic('新会员组名称','usergroupnew[cname]','','text',array('validate'=>makesubmitstr('usergroupnew[cname]',1,0,0,30)));
		tabfooter('busergroupcopy');
		a_guide('usergroupcopy');
	}else{
		$usergroupnew['cname'] = trim(strip_tags($usergroupnew['cname']));
		if(empty($usergroupnew['cname'])) cls_message::show('资料不完全',M_REFERER);
		$sqlstr = "cname='$usergroupnew[cname]'";
		foreach($usergroup as $k => $v) if(!in_array($k,array('ugid','cname'))) $sqlstr .= ",$k='".addslashes($v)."'";
		$db->query("INSERT INTO {$tblprefix}usergroups SET ugid=".auto_insert_id('usergroups').",$sqlstr");
		$ugid = $db->insert_id();
		adminlog('复制会员组');
		cls_CacheFile::Update('usergroups',$gtid);
		cls_message::show('会员组复制完成',"?entry=$entry&action=usergroupdetail&gtid=$gtid&ugid=$ugid");
	}
}elseif(($action == 'usergroupdetail') && $gtid && $ugid){
	if(!($usergroup = $usergroups[$ugid])) cls_message::show('请指定正确的会员组');
	if(!submitcheck('busergroupdetail')){
		tabheader('编辑会员组'.'-'.$grouptype['cname'],'usergroupdetail',"?entry=$entry&action=$action&gtid=$gtid&ugid=$ugid",2,0,0);
		trbasic('会员组名称','usergroupnew[cname]',$usergroup['cname']);
		trbasic('勾选启用会员模型','',makecheckbox('usergroupnew[mchids][]',cls_mchannel::mchidsarr(),!empty($usergroup['mchids']) ? explode(',',$usergroup['mchids']) : array(),5),'');
		trbasic('会员组有效期','usergroupnew[limitday]',empty($usergroup['limitday']) ? '' : $usergroup['limitday'],'text',array('w' => 6,'guide' => '单位：天，留空为不过期'));
		$na = array(0 => '清除过期组');
		foreach($usergroups as $k => $v) $k == $ugid || $na[$k] = $v['cname'];
		trbasic('过期后转到其它组','usergroupnew[overugid]',makeoption($na,$usergroup['overugid']),'select');
		trspecial('会员组图标',specialarr(array('type' => 'image','varname' => 'usergroupnew[ico]','value' => $usergroup['ico'],)));
		if(!$grouptype['issystem'] && $grouptype['mode'] != 2) trbasic('注册自动成为本组会员','usergroupnew[autoinit]',$usergroup['autoinit'],'radio',array('guide'=>'当会员可以自动进入多个组中，选择优先级高的一个会员组。'));
		if($grouptype['forbidden']){
			$ugforbids = cls_cache::exRead('ugforbids');
			trbasic('禁止以下操作','',makecheckbox('usergroupnew[forbids][]',$ugforbids,empty($usergroup['forbids']) ? array() : explode(',',$usergroup['forbids']),5),'');
		}else{
			if($grouptype['afunction']){
				$amconfigs = cls_cache::Read('amconfigs');
				$arr = array();foreach($amconfigs as $k => $v) $arr[$k] = $v['cname'];
				trbasic('后台管理角色','',makecheckbox("usergroupnew[amcids][]",$arr,empty($usergroup['amcids']) ? array() :explode(',',$usergroup['amcids']),5),'',array('guide' => '-后面的数字为子站id，0为主站。'));
			}elseif($grouptype['mode'] > 1){
				trbasic('相关积分数量','usergroupnew[currency]',$usergroup['currency']);
			}
			$ugallows = cls_cache::exRead('ugallows');
			trbasic('本组会员有以下权限','',makecheckbox('usergroupnew[allows][]',$ugallows,empty($usergroup['allows']) ? array() : explode(',',$usergroup['allows']),5),'');
			trbasic('短信数量限制','usergroupnew[maxpms]',$usergroup['maxpms']);
			trbasic('上传限制'.'(M)','usergroupnew[maxuptotal]',$usergroup['maxuptotal']);
			trbasic('下载限制'.'(M)','usergroupnew[maxdowntotal]',$usergroup['maxdowntotal']);

		}
		tabfooter('busergroupdetail','修改');
		a_guide('usergroupdetail');
	}else{
		$sqlstr = '';
		if($grouptype['forbidden']){
			$usergroupnew['forbids'] = empty($usergroupnew['forbids']) ? '' : implode(',',$usergroupnew['forbids']);
			$sqlstr .= "forbids='$usergroupnew[forbids]',";
		}else{
			if($grouptype['afunction']){
				$usergroupnew['amcids'] = empty($usergroupnew['amcids']) ? '' : implode(',',$usergroupnew['amcids']);
				$sqlstr .= "amcids='$usergroupnew[amcids]',";
			}
			$usergroupnew['currency'] = ($grouptype['mode'] < 1) || empty($usergroupnew['currency']) ? 0 : max(0,intval($usergroupnew['currency']));
			//倘若积分改变
			$_is_one = 0;$_isadd = 0;    
			$_issame = array_values($usergroupnew['mchids'])==array_values(explode(',',$usergroups[$ugid]['mchids']))?'1':'0';    
			$_isadd = array_diff($usergroupnew['mchids'],explode(',',$usergroups[$ugid]['mchids']));
  
			if($usergroupnew['currency'] != $usergroups[$ugid]['currency'] || !$_issame){
				$_is_one = '1';
				update_grouptype();	
			}
			$usergroupnew['allows'] = empty($usergroupnew['allows']) ? '' : implode(',',$usergroupnew['allows']);
			$usergroupnew['maxuptotal'] = empty($usergroupnew['maxuptotal']) ? 0 : max(0,intval($usergroupnew['maxuptotal']));
			$usergroupnew['maxdowntotal'] = empty($usergroupnew['maxdowntotal']) ? 0 : max(0,intval($usergroupnew['maxdowntotal']));
			$usergroupnew['maxpms'] = empty($usergroupnew['maxpms']) ? 0 : max(0,intval($usergroupnew['maxpms']));
			$sqlstr .=  "maxpms='$usergroupnew[maxpms]',
			currency='$usergroupnew[currency]',
			allows='$usergroupnew[allows]',
			maxuptotal='$usergroupnew[maxuptotal]',
			maxdowntotal='$usergroupnew[maxdowntotal]',";
		}
		$usergroupnew['cname'] = empty($usergroupnew['cname']) ? $usergroup['cname'] : $usergroupnew['cname'];
		$usergroupnew['mchids'] = !empty($usergroupnew['mchids']) ? implode(',',$usergroupnew['mchids']) : '';
		$usergroupnew['limitday'] = empty($usergroupnew['limitday']) ? 0 : max(0,intval($usergroupnew['limitday']));
		$usergroupnew['autoinit'] = $grouptype['issystem'] || $grouptype['mode'] == 2 || empty($usergroupnew['autoinit']) ? 0 : 1;

		$c_upload = cls_upload::OneInstance();
		$usergroupnew['ico'] = upload_s($usergroupnew['ico'],$usergroup['ico'],'image');
		if($k = strpos($usergroupnew['ico'],'#')) $usergroupnew['ico'] = substr($usergroupnew['ico'],0,$k);
		$c_upload->closure(2,$ugid,'usergroup');
		$c_upload->saveuptotal(1);

		$sqlstr .= "cname='$usergroupnew[cname]',
				mchids='$usergroupnew[mchids]',
				autoinit='$usergroupnew[autoinit]',
				ico='$usergroupnew[ico]',
				limitday='$usergroupnew[limitday]',
				overugid='$usergroupnew[overugid]'
				";
		$db->query("UPDATE {$tblprefix}usergroups SET $sqlstr WHERE ugid='$ugid'");
		adminlog('详情修改会员组');
		cls_CacheFile::Update('usergroups',$gtid);
		cls_message::show('会员组编辑完成',axaction(6,"?entry=$entry&action=usergroupsedit&gtid=$gtid"));
	}
}elseif($action == 'update_grouptype'){
	$_currency_id = $db->result_one("SELECT crid FROM {$tblprefix}grouptypes where gtid = '$gtid'");
	$_usergroups_key = array_keys($usergroups);
	$_first_key = $_usergroups_key[0];
	foreach($usergroups as $k => $v){
		if(!empty($v['mchids'])){
			$_mchid_arr = mimplode($v['mchids']);
			$k != $_first_key && $_front_key = $_usergroups_key[array_search($k,$_usergroups_key)-1];			
			$_where_str = " WHERE mchid in (".$_mchid_arr.") AND currency".$_currency_id." >= '$v[currency]' ".($k == $_first_key ? '' : " AND currency".$_currency_id." < ".$usergroups[$_front_key]['currency']);
			$db->query("UPDATE {$tblprefix}members set grouptype".$gtid." = '$k' ".$_where_str);
		}	
	}
	cls_message::show('会员等级修改完成',axaction(6,"?entry=$entry&action=usergroupsedit&gtid=$gtid"));	
}
function fetch_arr(){
	global $db,$tblprefix,$gtid;
	$rets = array();
	$query = $db->query("SELECT * FROM {$tblprefix}usergroups WHERE gtid='$gtid' ORDER BY currency DESC,prior desc,ugid desc");
	while($r = $db->fetch_array($query)){
		$rets[$r['ugid']] = $r;
	}
	return $rets;
}

function fetch_one($ugid){
	global $db,$tblprefix;
	$r = $db->fetch_one("SELECT * FROM {$tblprefix}usergroups WHERE ugid='$ugid'");
	return $r;
}


//服务等级经纪信用积分修改时，修改会员信息字段grouptypes17
//$_update_cu:		存放批量修改时，积分改变的ugid以及积分
//$usergroups:		获取旧的积分等级数据
//$usergroupnew:	获取修改之后提交的积分等级数据
//$_is_one:			判断是否是针对单一的等级点击编辑进行修改
//$_issame:			判断用户模型改变
//$_isadd:			用于判断增加了哪些模型

function update_grouptype(){
	global $db,$tblprefix,$grouptype,$_update_cu,$usergroups,$ugid,$usergroupnew,$gtid,$_is_one,$_issame,$_isadd;
	
	if($grouptype['mode'] == 2){
		$_currency_id = $db->result_one("SELECT crid FROM {$tblprefix}grouptypes where gtid = '$gtid'");
 
    
		if(!empty($_update_cu)){
			$_arr_keys = array_keys($usergroups);
			foreach($_update_cu as $k=>$v){
				$_next_key = array_search($k,$_arr_keys)+1;
				//$v['up']标识着修改后的积分比原来的高
				$_ugid = !empty($v['up']) ? $_arr_keys[$_next_key] : $k;
				$_mchid_str = " AND mchid IN (".mimplode($usergroups[$_ugid]['mchids']).") ";
				$_where_str = !empty($v['up']) ? " WHERE currency".$_currency_id." >= '$v[oldcurrency]' AND currency".$_currency_id." < '$v[newcurrency]' ".$_mchid_str : " WHERE currency".$_currency_id." >= '$v[newcurrency]' ".($v['oldcurrency'] == 'the_first'? '':" AND currency".$_currency_id." < '$v[oldcurrency]' ").$_mchid_str;
				!empty($usergroups[$_ugid]['mchids']) && $db->query("UPDATE {$tblprefix}members SET grouptype".$gtid." = '$_ugid' ".$_where_str);
			}
		}else{
			if(empty($_is_one)) return;
			$_arr_keys  = array_keys($usergroups);
			$_now_key   = array_search($ugid,$_arr_keys);
            //注意区分是否是最低级，返回结果
			$_next_ugid = array_search($ugid,$_arr_keys)+1 >(count($_arr_keys)-1)?$_arr_keys[array_search($ugid,$_arr_keys)]:$_arr_keys[array_search($ugid,$_arr_keys)+1];
            //注意区分是否是最高级，返回结果
			$_front_ugid = array_search($ugid,$_arr_keys)-1 < 0 ? $_arr_keys[array_search($ugid,$_arr_keys)] : $_arr_keys[array_search($ugid,$_arr_keys)-1];           
			if($_now_key > 0 && $_now_key < (count($_arr_keys)-1)){
				if($usergroupnew['currency'] >= $usergroups[$_front_ugid]['currency'] || $usergroupnew['currency'] <= $usergroups[$_next_ugid]['currency']){
					cls_message::show('积分不能大于等于上一级积分或者小于等于下一级积分',M_REFERER);
				}
			}
			$_ugid  = $usergroupnew['currency'] > $usergroups[$ugid]['currency'] ? $_next_ugid : $ugid;
			if(!$_issame && $_isadd){
				$_isadd = mimplode($_isadd);
                //如果是修改最高等级的
				if(array_search($ugid,$_arr_keys)-1 < 0){
                    $_where_str = " WHERE currency".$_currency_id." >= '".$usergroups[$ugid]['currency']."'";  
				}else if(array_search($ugid,$_arr_keys)+ 1 > count($_arr_keys)-1){//如是修改最低级
				    $_where_str = " WHERE currency".$_currency_id." < '".$usergroups[$_front_ugid]['currency']."'";
				}else{
                    $_where_str = " WHERE currency".$_currency_id." >= '".$usergroups[$ugid]['currency']."' AND currency".$_currency_id." < '".$usergroups[$_front_ugid]['currency']."'";
                }
                #echo "UPDATE {$tblprefix}members SET grouptype".$gtid." = '$_ugid' ".$_where_str." AND mchid IN  (".$_isadd.")";
				!empty($_isadd) && $db->query("UPDATE {$tblprefix}members SET grouptype".$gtid." = '$_ugid' ".$_where_str." AND mchid IN  (".$_isadd.")");		
			}
			if($usergroupnew['currency'] != $usergroups[$ugid]['currency']){
				$_where_str = $usergroupnew['currency'] > $usergroups[$ugid]['currency'] ? " WHERE currency".$_currency_id." >= '".$usergroups[$ugid]['currency']."' AND currency".$_currency_id." < '$usergroupnew[currency]' " : " WHERE currency".$_currency_id." >= '$usergroupnew[currency]' AND currency".$_currency_id." < '".$usergroups[$ugid]['currency']."' ";
				$_mchid_arr = mimplode($usergroupnew['mchids']);
				!empty($usergroupnew['mchids']) && $db->query("UPDATE {$tblprefix}members SET grouptype".$gtid." = '$_ugid' ".$_where_str." AND mchid IN  (".$_mchid_arr.")");
			}
		}
	}
}

?>
