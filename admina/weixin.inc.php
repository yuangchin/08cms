<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();

$action = empty($action) ? 'appids' : $action;
$page = !empty($page) ? max(1, intval($page)) : 1; 
$tab = isset($tab) ? $tab : '0';

$wmDefCfg = cls_cache::exRead('wxconfgs'); 
$extabs = $wmDefCfg['sys_confgs']['tabs'];
if(empty($infloat)){ backnav('weixin',$action); }

///*
$aid = empty($aid) ? 0 : intval($aid);
$mid = empty($mid) ? 0 : intval($mid);
if(!empty($mid)){
	$key = $mid;
	$type = 'mid';
	if($re = $curuser->NoBackFunc('member')) cls_message::show($re);
}elseif(!empty($aid)){
	$key = $aid;
	$type = 'aid';
	if($re = $curuser->NoBackFunc('normal')) cls_message::show($re);
}else{
	$key = 0;
	$type = 'sid';
	if($re = $curuser->NoBackFunc('weixin')) cls_message::show($re);
} //公众号配置
//*/

echo "\n<script>\n"; include(_08_INCLUDE_DIR.'/js/weixin.js'); echo "\n</script>\n";

if($action=='testdebug'){
	
	echo $action;

}elseif($action=='appids'){
	
	$extabs = array_merge(array('_all_'=>'[所有]'), $extabs); //print_r($extabs);
	$mtypetitle = '';
	foreach($extabs as $k => $v){
		$tpclassarr[] = "$tab" === "$k" ? "<b>-$v-</b>" : "<a href=\"?entry=$entry&action=$action&tab=$k\">$v</a>";
		if("$tab"==="$k") $mtypetitle = $v;
	}
	if(@$bsubmit!='batch'){
		echo tab_list($tpclassarr,10,0);
	}
	
  $keyword = empty($keyword) ? '' : $keyword; 
  $keytype = empty($keytype) ? 'weixin_fromid' : $keytype;
  $filterstr = '';//$checked?"&checked=$checked":'';
  foreach(array('keyword','keytype','action','tab') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE 1=1 "; //cu.mid='$memberid' commu_offer. archives1.
  $fromsql = "FROM {$tblprefix}weixin_config ";
    
  if($keyword){
	  if($keytype){
	  	$wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }
  }
  if(empty($tab)){
	  $wheresql .= " AND (weixin_fromid_type='sid') "; 
  }elseif($tab=='_all_'){
	  $wheresql .= " AND (1=1) "; 
  }else{ 
	  $wheresql .= " AND (weixin_cache_id='$tab') "; 
  } 
  
  if(!submitcheck('bsubmit')){
	  
	  echo form_str('sendlogs',"?entry=$entry&action=$action&tab=$tab&page=$page");
	  tabheader_e();
	  echo "<tr><td class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键词\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('0'=>'--筛选范围--','weixin_fromid'=>'来源ID','weixin_appid'=>'AppID','weixin_orgid'=>'原始ID',),$keytype)."</select>&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  if($tab=='_all_'){
		$link = ' (所有) ';  
	  }elseif(empty($tab)){
		$wecfg = cls_w08Basic::getConfig(0, 'sid'); 
		if(!empty($wecfg["appid"])){
			$link = " <a style='float:right' title='只能有一个总站配置'>(不能添加多个)</a>"; //die('ddd');
		}elseif(empty($wecfg['appid']) && !empty($mconfigs["weixin_appid"])){
			$link = " <a href='?entry=$entry&action=import' onclick=\"return floatwin('open_crmenu',this,640,480)\" style='float:right'>导入旧版[总站]微信配置</a>";
		}else{
			$link = " <a href='?entry=$entry&action=appadd&tab=0&return=main' onclick=\"return floatwin('open_crmenu',this,640,480)\" style='float:right'>添加微信配置</a>";
		}
		$link .= "<a href='?entry=weappid&action=menu&menucreate=1' onclick=\"return floatwin('open_crmenu',this,640,480)\" style='float:right'>生成[总站]微信菜单</a>";
	  }else{
		$link = "<a href='?entry=$entry&action=appadd&tab=$tab&return=main' onclick=\"return floatwin('open_crmenu',this,640,480)\" style='float:right'>添加微信配置</a>";
		$link .= "<a href='?entry=$entry&action=$action&tab=$tab&bsubmit=batch' onclick=\"return floatwin('open_crmenu',this,640,480)\" style='float:right'>生成所有[$mtypetitle]微信菜单</a>";
	  }
	  tabheader("{$link}公众号管理",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  //$cy_arr[] = '发送时间';
	  $cy_arr[] = '类型';
	  $cy_arr[] = '来源ID';
	  $cy_arr[] = 'AppID';
	  $cy_arr[] = '状态';
	  //$cy_arr[] = '通信密匙|L';
	  $cy_arr[] = '原始ID';
	  $cy_arr[] = '微信二维码';
	  $cy_arr[] = '菜单缓存'; 
	  $cy_arr[] = '配置';
	  $cy_arr[] = '菜单';
	  $cy_arr[] = '关注者';
	  $cy_arr[] = '消息';
	  //$cy_arr[] = '素材';
	  //$cy_arr[] = '关键词';
	  $cy_arr[] = '调试'; 
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY weixin_fromid_type DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; 
	  $baseurl = "?entry=weappid&isframe=1&action";
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[weixin_id]]\" value=\"$r[weixin_id]\">";
		  $sidtype = $r['weixin_fromid_type']=='sid' ? '(总站)' : $r['weixin_fromid_type'];
		  $enable = $r['weixin_enable'] ? '启用' : '---';
		  if($r['weixin_fromid_type']=='aid'){
			$arc = new cls_arcedit;
			$arc->set_aid($r['weixin_fromid'],array('au'=>0,'ch'=>0));
			$info = $arc->archive;
			//@cls_ArcMain::Url($info); 
			//$url = $info['arcurl'];
			$upara = "aid={$r['weixin_fromid']}";
		  }elseif($r['weixin_fromid_type']=='mid'){
			//$user = new cls_userinfo;
			//$user->activeuser($r['weixin_fromid'],1);
			//$url = cls_Mspace::IndexUrl($user->info);
			$upara = "mid={$r['weixin_fromid']}";
		  }else{
			$url = $cms_abs;  
			$upara = "";
		  }
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtC\">$sidtype</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[weixin_fromid]</td>\n";
		  $itemstr .= "<td class=\"txtL\">$r[weixin_appid]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$enable</td>\n";
		  //$itemstr .= "<td class=\"txtL w240\">r[weixin_appid]</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[weixin_orgid]</td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href='$r[weixin_qrcode]' target='_blank'>二维码</a></td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[weixin_cache_id]</td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href='$baseurl=config&$upara' target='_blank'>配置</a></td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href='$baseurl=menu&$upara' target='_blank'>菜单</a></td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href='$baseurl=follow&$upara' target='_blank'>关注者</a></td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href='$baseurl=message&$upara' target='_blank'>消息</a></td>\n";
		  //$itemstr .= "<td class=\"txtC\"><a href='$baseurl=keyword&$upara' target='_blank'>关键词</a></td>\n";
		  $itemstr .= "<td class=\"txtC\"><a href='?entry=wetest&$upara' target='_blank'>调试</a></td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry&page=$page$filterstr");
  
	  tabheader('批量操作');
	  $str = "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delcfg]\" value=\"now\" onclick='deltip()'>删除公众号配置 &nbsp; "; 
	  $str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delact]\" value=\"now\">清除公众号access_token缓存(更换公众号后执行) &nbsp; "; 
	  if(empty($tab)){
		$str .= "<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[dqrcac]\" value=\"now\" onclick='deltip()'>清除二维码缓存(更换总站公众号后执行) &nbsp; ";   
	  }

	  trbasic("选择操作项目",'',$str,''); 
	  tabfooter('bsubmit');
  }elseif($bsubmit=='batch'){
	  $nextid = empty($nextid) ? 0 : intval($nextid);
	  $remu = cls_w08Menu::batch($tab,$nextid); 
	  if(empty($remu['wecfg'])) cls_message::show("没有要创建的菜单！<br>点击关闭","");
	  $msg = "创建[".$remu['wecfg']['fromid_type']."=".$remu['wecfg']['fromid']."]微信菜单";
	  if(!empty($remu['res']['errcode'])){
		$ermsg = "失败！<br>".$remu['res']['errcode']."<br>(".$remu['res']['message'].")";
	  }else{
		$ermsg = "成功！<br>";
	  }
	  $msg .= "$ermsg<hr>"; //die($msg);
	  if(empty($remu['nextid'])){
		  cls_message::show("{$msg}生成本类所有微信菜单完成。<br>点击关闭",""); //?entry=$entry&action=$action&tab=$tab
	  }else{
		  cls_message::show("{$msg}将自动跳转到下一个配置".'。',"?entry=$entry&action=$action&tab=$tab&bsubmit=batch&nextid={$remu['nextid']}"); 
	  }
  }else{ 
	if(empty($arcdeal)) cls_message::show('请选择操作项目',axaction(1,M_REFERER));
	if(!empty($arcdeal['dqrcac'])){
		$db->query("DELETE FROM {$tblprefix}weixin_qrcode");
		$db->query("DELETE FROM {$tblprefix}weixin_qrlimit");
		cls_message::show('批量操作成功'.'。',"?entry=$entry&page=$page$filterstr");
	}
	if(empty($selectid)) cls_message::show('请选择项目',axaction(1,M_REFERER));
	//define('DEBUG_RETURN','1'); //错误信息直接返回
	foreach($selectid as $k){
		if(!empty($arcdeal['delcfg'])){
			$db->query("DELETE FROM {$tblprefix}weixin_config WHERE weixin_id='$k'");
			continue;
		}
		if(!empty($arcdeal['delact'])){
			$db->query("UPDATE {$tblprefix}weixin_config SET weixin_actoken='',weixin_acexp='0' WHERE weixin_id='$k'");
			continue;
		}
	}
	cls_message::show('批量操作成功'.'。',"?entry=$entry&page=$page$filterstr");
 }

}elseif($action=='appadd'){
	
	if(!submitcheck('wxconfig')){ 
		
		/*$wecfg = cls_w08Basic::getConfig($key, $type);
		if(!empty($wecfg)){
			cls_message::show('公众号管理',axaction(32,"?entry=weappid&action=config&$type=$key"));
		}*/
		
		$url = cls_w08Basic::getWeixinURL($type,$key);
		tabheader("公众号配置",'cfweixin',"?entry=$entry&action=$action&aid=$aid&mid=$mid&tab=$tab",2,0,1);
		
		$typemid = "<label><input type=\"radio\" class=\"radio\" id=\"ftype_1\" name='mconfigsnew[weixin_fromid_type]' value='mid' onclick=\"wxCfgsetType('mid')\">会员（mid）</label>";
		$typeaid = "<label><input type=\"radio\" class=\"radio\" id=\"ftype_0\" name='mconfigsnew[weixin_fromid_type]' value='aid' onclick=\"wxCfgsetType('aid')\">文档（aid）</label>";
		
		echo "<tr class=\"txt\"><td class=\"txt txtright fB\">类型</td>\n".
		"<td class=\"txtL\">\n";
		if(empty($tab)){
			echo "<input type='hidden' name='mconfigsnew[weixin_fromid_type]' value='sid' />";
			echo "<input type='hidden' name='mconfigsnew[weixin_fromid]' value='0' />";
			echo "<input type='hidden' name='mconfigsnew[weixin_cache_id]' value='0' />";
		}elseif($aid){
			echo "<input type='hidden' name='mconfigsnew[weixin_fromid_type]' value='aid' />";
			$typeaid = str_replace('<input','<input readonly checked',$typeaid); 
			echo "$typeaid <input type='text' size='40' id='mconfigsnew[weixin_fromid]' name='mconfigsnew[weixin_fromid]' value='$aid' maxlength='250' readonly />";
		}elseif($mid){
			echo "<input type='hidden' name='mconfigsnew[weixin_fromid_type]' value='mid' />";
			$typemid = str_replace('<input','<input readonly checked',$typemid); 
			echo "$typemid <input type='text' size='40' id='mconfigsnew[weixin_fromid]' name='mconfigsnew[weixin_fromid]' value='$mid' maxlength='250' readonly />";	
		}else{
			$jscmd = 'wxCfgsetInit()';
			echo "$typemid &nbsp; $typeaid"; //".str_replace('<input','<input checked',$typemid)."
			echo " &nbsp; aid/mid:<input type='text' size='30' id='mconfigsnew[weixin_fromid]' name='mconfigsnew[weixin_fromid]' value='' maxlength='250' onBlur=\"wxCfgsetID(this)\" rule='text' must='1' />";
		}
		//echo "<label><input type=\"radio\" class=\"radio\" id=\"sence_1\" name='sence_k' onclick='wxKwdsetSence(1)' checked>会员（mid）</label> &nbsp; &nbsp; \n"
			//."<label><input type=\"radio\" class=\"radio\" id=\"sence_0\" name='sence_k' onclick='wxKwdsetSence(0)'>文档（aid）</label> \n";
		echo "</td></tr>\n";
		
        trbasic('网址URL','mconfigsnew[weixin_url]', $url . '/', 'text', array('guide' => '该URL由系统自动生成，暂时不开放编辑，直接把它复制到微信公众平台即可。','w' => 80,'validate' =>'maxlength="250" readonly'));
        trbasic('Token *','mconfigsnew[weixin_token]', @$wecfg['token'], 'text', array('guide' => 'Token可以任意填写，但必须与公众平台一致，用作生成签名，没有公众账号？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a>','w' => 80, 'validate' => 'maxlength="250"'));
        trbasic('AppId *','mconfigsnew[weixin_appid]', @$wecfg['appid'], 'text', array('guide' => '没有AppId？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a> （注：只有服务号或订阅号认证过后才能申请。）','w' => 80, 'validate' => 'maxlength="250"'));
        trbasic('AppSecret *','mconfigsnew[weixin_appsecret]', @$wecfg['appsecret'], 'text', array('guide' => '没有AppSecret？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a> （注：只有有服务号或订阅号认证过后才能申请。）','w' => 80, 'validate' => 'maxlength="250"'));
        trbasic('微信原始ID *','mconfigsnew[weixin_orgid]', @$wecfg['orgid'], 'text', array('guide' => '在公众平台:设置-账户信息-原始ID（一般以gh_开头），用于管理回复关注者消息','w' => 80, 'validate' => 'maxlength="250"'));
		$config = array('witdh' => 80, 'type' => 'image','varname' => 'mconfigsnew[weixin_qrcode]','value' => cls_url::tag2atm(@$wecfg['qrcode']));
		$config['guide'] = '前台调用样式：{$weixin_qrcode}';
        trspecial('微信二维码',specialarr($config));
		tabfooter('');

		tabheader('微信功能开关','','',2,0,1);
		trbasic('启用微信接口','mconfigsnew[weixin_enable]',@$wecfg['enable'],'radio', array('guide' => '请与公众平台的【开发者中心】【服务器配置】同时保持(启用)状态，本系统的相关功能（如：菜单配置,关注者管理,消息管理,素材管理,菜单配置,公众号管理等）才可使用；
		<br>如果不启用，直接上传您的微信二维码即可，但本系统上述功能不能使用。', 'validate' =>''));
		$muarr = $wmDefCfg['sys_confgs']['tabs']; unset($muarr[0]);
		trbasic('默认微信菜单','mconfigsnew[weixin_cache_id]',makeoption($muarr,@$tab),'select');	
		//trhidden('weixin_id',empty($wecfg['id']) ? 0 : $wecfg['id']);
		trhidden('return',@$return);
		tabfooter('wxconfig');
		if(!empty($jscmd)) echo "\r\n<script type='text/javascript'>$jscmd;</script>";
		a_guide('admin_weixin_config');
		
	}else{
		
		if(!empty($mconfigsnew['weixin_qrcode'])){
			$mconfigsnew['weixin_qrcode'] = cls_url::save_atmurl($mconfigsnew['weixin_qrcode']);
		}
		// 公共数据
		$data = array(
			'weixin_enable' => $mconfigsnew['weixin_enable'], 
			'weixin_token' => $mconfigsnew['weixin_token'], 
			'weixin_appid' => $mconfigsnew['weixin_appid'], 
			'weixin_appsecret' => $mconfigsnew['weixin_appsecret'], 
			'weixin_orgid' => $mconfigsnew['weixin_orgid'], 
			'weixin_qrcode' => $mconfigsnew['weixin_qrcode'],
			'weixin_fromid_type' => $mconfigsnew['weixin_fromid_type'],
			'weixin_fromid' => $mconfigsnew['weixin_fromid'],
			'weixin_cache_id' => $mconfigsnew['weixin_cache_id'],
		);
		$exrow = $db->select('COUNT(*)')->from('#__weixin_config')->where(array('weixin_appid'=>$mconfigsnew['weixin_appid']))->limit(1)->exec()->fetch();
		$exrow = empty($exrow['COUNT(*)']) ? 0 : $exrow['COUNT(*)']; //$db->setDebug();
		$eyrow = $db->select('COUNT(*)')->from('#__weixin_config')->where(array('weixin_fromid_type'=>$mconfigsnew['weixin_fromid_type'],'weixin_fromid'=>$mconfigsnew['weixin_fromid']))->limit(1)->exec()->fetch();
		$eyrow = empty($eyrow['COUNT(*)']) ? 0 : $eyrow['COUNT(*)'];
		if($exrow>0){
			cls_message::show('AppId='.$mconfigsnew['weixin_appid'].' 已经使用,请更换！',axaction(6,M_REFERER));
		}
		if($eyrow>0){
			cls_message::show($mconfigsnew['weixin_fromid_type'].'='.$mconfigsnew['weixin_fromid'].' 已经使用,请更换！',axaction(6,M_REFERER));
		}
		$db->insert('#__weixin_config', $data)->exec();
		//if(@$return=='dir'){
			//cls_message::show('公众号配置完成',axaction(64,"?entry=weappid&action=config&{$mconfigsnew['weixin_fromid_type']}={$mconfigsnew['weixin_fromid']}")); //isframe=1&
		//}else{ // $return=='main'
			cls_message::show('公众号配置完成',axaction(6,M_REFERER));
		//}
	}

}elseif($action=='import'){
	
	$wecfg = array(); //print_r($wecfg);
	if(empty($tab)){ //兼容之前的默认值...
		$dcfg = array('appid','token','appsecret','orgid','enable','qrcode');
		foreach($dcfg as $k){ //echo $mconfigs["weixin_$k"].'<br>';
			if(empty($wecfg[$k]) && !empty($mconfigs["weixin_$k"])){
				$wecfg["weixin_$k"] = $mconfigs["weixin_$k"];
			}
		}
	}
	$wecfg['weixin_fromid_type'] = 'sid';
	$wecfg['weixin_fromid'] = '0';
	$wecfg['weixin_cache_id'] = '0';
	$db->insert('#__weixin_config', $wecfg)->exec();
	cls_message::show('导入配置成功！',axaction(6,M_REFERER));
	
}elseif($action=='menu'){ 
	
	$wmDbCfg = cls_w08Menu::getMenuData(array(), $tab);
	$wmDefList = cls_w08Menu::getMenuDef($wmDbCfg, $wmDefCfg, $tab);
	$wmPickData = cls_w08Menu::getMenuPick($wmDefCfg, $tab);
	
	if(!submitcheck('menusave') && !submitcheck('menucreate') && !submitcheck('menudelete')){

		foreach($extabs as $k => $v){
			$tpclassarr[] = "$tab" === "$k" ? "<b>-$v-</b>" : "<a href=\"?entry=$entry&action=$action&tab=$k\">$v</a>";
		}
		echo tab_list($tpclassarr,10,0);
		
		tabheader("菜单配置",'wxmenu',"?entry=$entry&action=$action&tab=$tab",2,0,1);
		$cy_arr[] = 'ID|R';
		$cy_arr[] = '菜单名称|L';
		$cy_arr[] = '链接/KEY (支持变量:{aid},{mid},{cms_abs},{mobileurl})|L';
		trcategory($cy_arr);
		for($i=1;$i<=3;$i++){ for($j=0;$j<=5;$j++){
			$itemstr = ''; $mlen = 7; $imuid = "$i{$j}";
			if($j==0 && $i<=2){
				$icon = "<img src='images/admina/sub2.gif' width='32' height='32' class='md'>";
				$mlen = 4;
			}elseif($j==0 && $i==3){
				$icon = "<img src='images/admina/sub3.gif' width='32' height='32' class='md'>";
				$mlen = 4;
			}elseif($i<=2){
				$icon = "<img src='images/admina/line1.gif' width='32' height='32' class='md'>";
				$icon .= "<img src='images/admina/".($j==5 ? 'line3' : 'line2').".gif' width='32' height='32' class='md'>";
			}else{ //$i==3
				$icon = "<img src='images/admina/blank.gif' width='32' height='32' class='md'>";
				$icon .= "<img src='images/admina/".($j==5 ? 'line3' : 'line2').".gif' width='32' height='32' class='md'>";
			}
			$name = empty($wmDefList[$imuid]['name']) ? '' : $wmDefList[$imuid]['name'];
			$val = empty($wmDefList[$imuid]['val']) ? '' : $wmDefList[$imuid]['val'];
			$itemstr .= "<tr class=\"txt\">";
			$itemstr .= "<td class=\"txtR\">$imuid</td>\n";
			$itemstr .= "<td class=\"txtL w190\">$icon<input name='catalogsnew[$imuid][name]' value='$name' size='25' maxlength='$mlen' type='text'></td>\n";
			$itemstr .= "<td class=\"txtL w190\"><input name='catalogsnew[$imuid][val]' value='$val' id='catalogsnew[$imuid][val]' maxlength='180' type='text' style='width:400px; margin-top:4px;'>
						 &nbsp; <a id='cupick_$imuid' href='javascript:;' onClick=\"wxMenuPickWin($imuid)\">&lt;&lt;选取菜单 &nbsp;</a>
						 &nbsp; <a id='cupick_$imuid' href='javascript:;' onClick=\"wxMenuClearWin($imuid)\">&lt;&lt;清空</a></td>\n";
			$itemstr .= "</tr>";
			echo $itemstr;
		} }
		//tabfooter('wxmenu');
		//$btntitle = $type=='sid' ? '创建微信菜单' : "创建[$type=$key]的微信菜单";
		//$btncreate = "<input class='btn use_menu' type='submit' name='menucreate' value='$btntitle' onclick=\"_08cms_layer({type: 2, url:'?entry=weixin&action=$action&menucreate=1&tab=$tab&infloat=1',title:'$btntitle',height:240}); return false;\">";
		//$btndelete = "<input class='btn use_menu' type='submit' name='menudelete' value='删除微信菜单' onclick=\"_08cms_layer({type: 2, url:'?entry=weixin&action=$action&menudelete=1&tab=$tab&infloat=1',title:'删除微信菜单',height:240}); return false;\">";
		//$btnextra = $tab ? "" : "$btncreate &nbsp; $btndelete";
		tabfooter('menusave', '保存微信菜单', ""); // &nbsp; $btnextra
		a_guide('<li>1. 一级菜单最多4个汉字, 二级菜单最多7个汉字</li>
		<li>2. 如果一级菜单下有二级菜单, 则该一级菜单的[链接/KEY]必须为空, 支持变量:{aid},{mid},{cms_abs},{mobileurl}</li>
		<li>3. 因创建菜单等操作每天有次数限制，请先修改保存配置，确认后进行创建菜单等操作。</li>', 'fix');
		?>
        <style type="text/css"> 
                #catalogsnew_body a {display:block; height: 28px; line-height: 28px; overflow:hidden; text-align: left; padding-left: 15px; white-space:nowrap;; }
                #catalogsnew_body a:hover{background-color: #DEEFFB}
        </style>
		<script>
		<?php
		echo "var mu_pics=$wmPickData;\n";
		?>
		//wxMenuInit(); 
		</script>
		<?php
		//tabheader("短信发送记录",'','',10);
	}elseif(submitcheck('menusave')){
		$db = _08_factory::getDBO();
		$whr = array('mcid'=>$tab);
		//$whr = $whr + array('key'=>$k); echo "$tab"; print_r($whr);
		foreach($catalogsnew as $k=>$v){
			if(empty($v['name'])){
				$db->delete('#__weixin_menu')->where($whr+array('key'=>$k))->exec();
			}if(isset($wmDbCfg[$k])){ 
				$db->update('#__weixin_menu', $v)->where($whr+array('key'=>$k))->exec();
			}elseif(!empty($v['name'])){
				if($tab){
					$v['mcid'] = $tab;
				}else{
					$v['mcid'] = 0;
				}
				$v['key'] = $k;
				$db->insert('#__weixin_menu', $v)->exec();
			}
		}
		cls_message::show('菜单配置保存完成',axaction(1,M_REFERER));
	}

}elseif($action=='switch'){

	$wecfg = cls_w08Basic::getConfig(0, 'sid');
	if(empty($wecfg['enable'])){
		$weixin_enable = 0;
		foreach(array('weixin_login','weixin_scanupload','weixin_scanupload') as $key){
			$mconfigs['weixin_login'] = 0;	
		}
	}else{
		$weixin_enable = 1;
	}
	if(!submitcheck('wxconfig')){
		
		tabheader("总站微信功能开关",'cfweixin',"?entry=$entry&action=$action&tab=$tab",2,0,1);
		trbasic('启用扫码登录','mconfigsnew[weixin_login]', @$mconfigs['weixin_login'],'radio', array('guide' => '需要认证服务号，且启用微信接口，并把公众号配置完整。', 'validate' =>''));
		trbasic('启用扫码找回密码','mconfigsnew[weixin_getpw]', @$mconfigs['weixin_getpw'],'radio', array('guide' => '需要认证服务号，且启用微信接口，并把公众号配置完整。', 'validate' =>''));
		#trbasic('启用扫码发信息','mconfigsnew[weixin_scansend]', @$mconfigs['weixin_scansend'],'radio', array('guide' => '需要认证服务号，且启用微信接口，并把公众号配置完整。', 'validate' =>''));
		#trbasic('启用扫码发图片','mconfigsnew[weixin_scanupload]', @$mconfigs['weixin_scanupload'],'radio', array('guide' => '需要认证服务号，且启用微信接口，并把公众号配置完整。', 'validate' =>''));
		trbasic('启用微信调试','mconfigsnew[weixin_debug]', @$mconfigs['weixin_debug'],'radio', array('guide' => '正式使用请关闭，关闭可提高效率和安全性。', 'validate' =>''));
		trhidden('weixin_enable',@$weixin_enable);
		if(empty($wecfg['enable'])){
			echo "</table>\n<br /> (不能提交) 请先配置并开启总站微信公众号接口"; 
			echo "</form>\n<div class='blank9'></div>";
		}else{
			tabfooter('wxconfig','保存');
		}
		a_guide('admin_weixin_config');
		
	}else{
		
		saveconfig('weixin', $mconfigsnew);
		adminlog('微信设置','微信公众平台配置');
		cls_message::show('公众号配置完成',axaction(1,M_REFERER));
	}

} //echo "$action=='message' && $tab=='msgget'";

?>
