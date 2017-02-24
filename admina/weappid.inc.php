<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();

$action = empty($action) ? 'config' : $action;
$page = !empty($page) ? max(1, intval($page)) : 1; 

$aid = empty($aid) ? 0 : intval($aid);
$mid = empty($mid) ? 0 : intval($mid);
if($action=='message'){
	$tab = empty($tab) ? 'msgget' : $tab;
}elseif($action=='wpappid'){
	$tab = isset($tab) ? $tab : '_all_';
}else{
	$tab = isset($tab) ? $tab : '0';
}
if(!empty($mid)){
	$key = $mid;
	$type = 'mid';
	$user = new cls_userinfo;
	$user->activeuser($mid,1);
	@$purl = cls_Mspace::IndexUrl($user->info);
	$plink = "微信:公众号 : <a href='$purl' target='_blank'>mid=$mid</a>";
	if($re = $curuser->NoBackFunc('member')) cls_message::show($re);
}elseif(!empty($aid)){
	$key = $aid;
	$type = 'aid';
	$arc = new cls_arcedit;
	$arc->set_aid($aid,array('au'=>0,'ch'=>0));
	@cls_ArcMain::Url($arc->archive); 
	$purl = $arc->archive['arcurl'];
	$plink = "微信:公众号 : <a href='$purl' target='_blank'>aid=$aid</a>";
	if($re = $curuser->NoBackFunc('normal')) cls_message::show($re);
}else{
	$key = 0;
	$type = 'sid';
	$purl = $cms_abs;  
	$plink = "微信:公众号(总站) ";
	if($re = $curuser->NoBackFunc('weixin')) cls_message::show($re);
} //公众号配置

$wecfg = cls_w08Basic::getConfig($key, $type); //print_r($wecfg);
if(empty($wecfg)){
	cls_message::show('请指定正确的公众号配置！');
}

$wmDefCfg = cls_cache::exRead('wxconfgs'); 
$extabs = $wmDefCfg['sys_confgs']['tabs'];
//print_r($wmDefCfg);

$submb = array('config'=>'公众号配置', 'menu'=>'菜单配置', 'follow'=>'关注者管理', 'message'=>'消息管理', 'keyword'=>'关键词管理',); // 'material'=>'素材管理',
if(empty($infloat)){ wxs_nav($plink,$submb,$action,12); } //if(!empty($infloat)){

echo "\n<script>\n"; include(_08_INCLUDE_DIR.'/js/weixin.js'); echo "\n</script>\n";

if($action=='message'){ 
	$extabs = array('msgxxx'=>'','msgget'=>'接收记录','msgsend'=>'发送记录','sendform'=>'推送/群发');
	foreach($extabs as $k => $v){
		$act = $k=='sendform' ? " onclick=\"return floatwin('open_reply',this)\" " : '';
		$tpclassarr[] = "$tab" === "$k" ? "<b>-$v-</b>" : "<a href=\"?entry=$entry&action=$action&tab=$k&aid=$aid&mid=$mid&infloat=$infloat\"$act>$v</a>";
	}
	$msgtabstr = tab_list($tpclassarr,5,0);		
}

if($action=='testdebug'){
	
	echo $action;

}elseif($action=='config'){

	if(!submitcheck('wxconfig')){ 
		
		tabheader("公众号配置$plink",'cfweixin',"?entry=$entry&action=$action&aid=$aid&mid=$mid&tab=$tab&infloat=$infloat",2,0,1);
        trbasic('网址URL','mconfigsnew[weixin_url]', cls_w08Basic::getWeixinURL($type,$key) . '/', 'text', array('guide' => '该URL由系统自动生成，暂时不开放编辑，直接把它复制到微信公众平台即可。','w' => 80,'validate' =>'maxlength="250" readonly'));
        trbasic('Token *','mconfigsnew[weixin_token]', @$wecfg['token'], 'text', array('guide' => 'Token可以任意填写，但必须与公众平台一致，用作生成签名，没有公众账号？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a>','w' => 80, 'validate' => 'maxlength="250"'));
        trbasic('AppId *','mconfigsnew[weixin_appid]', @$wecfg['appid'], 'text', array('guide' => '没有AppId？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a> （注：只有服务号或订阅号认证过后才能申请。）','w' => 80, 'validate' => 'maxlength="250"'));
        trbasic('AppSecret *','mconfigsnew[weixin_appsecret]', @$wecfg['appsecret'], 'text', array('guide' => '没有AppSecret？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a> （注：只有有服务号或订阅号认证过后才能申请。）','w' => 80, 'validate' => 'maxlength="250"'));
        trbasic('微信原始ID *','mconfigsnew[weixin_orgid]', @$wecfg['orgid'], 'text', array('guide' => '在公众平台:设置-账户信息-原始ID（一般以gh_开头），用于管理回复关注者消息','w' => 80, 'validate' => 'maxlength="250"'));
		$config = array('witdh' => 80, 'type' => 'image','varname' => 'mconfigsnew[weixin_qrcode]','value' => $wecfg['qrcode']);
		$config['guide'] = ($type=='sid' ? '前台调用样式：{$weixin_qrcode}' : '');
        trspecial('微信二维码',specialarr($config)); //qrcode用原始的值,specialarr里面处理路径
		tabfooter('');

		tabheader('微信功能开关','','',2,0,1);
		trbasic('启用微信接口','mconfigsnew[weixin_enable]',$wecfg['enable'],'radio', array('guide' => '请与公众平台的【开发者中心】【服务器配置】同时保持(启用)状态，本系统的相关功能（如：菜单配置,关注者管理,消息管理,素材管理,菜单配置,公众号管理等）才可使用；
		<br>如果不启用，直接上传您的微信二维码即可，但本系统上述功能不能使用。', 'validate' =>''));
		trhidden('weixin_id',empty($wecfg['id']) ? 0 : $wecfg['id']);
		trhidden('cache_id',@$cache_id);
		tabfooter('wxconfig');
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
		);
		$exrow = $db->select('COUNT(*)')->from('#__weixin_config')->where(array('weixin_appid'=>$mconfigsnew['weixin_appid']))->limit(1)->exec()->fetch();
		$exrow = empty($exrow['COUNT(*)']) ? 0 : $exrow['COUNT(*)'];

		if($exrow>1){
			cls_message::show('AppId='.$mconfigsnew['weixin_appid'].' 已经使用,请更换！',axaction(6,M_REFERER));
		}
		$db->update('#__weixin_config', $data)->where("weixin_id=$weixin_id")->exec();
		
		if($type=='sid' && empty($mconfigsnew['weixin_enable'])){
			$db->query("UPDATE {$tblprefix}mconfigs SET `value`='0' WHERE varname IN('weixin_enable','weixin_scansend','weixin_scanupload','weixin_getpw')"); 
			$m_excache->clear(); 
			cls_CacheFile::ReBuild(); 
			
		}
		if($type=='sid'){ //更新mconfig配置,兼容之前调用
			saveconfig('weixin', $mconfigsnew);
		}
		cls_message::show('公众号配置完成',axaction(1,M_REFERER));
	}

}elseif($action=='menu'){ 
	
	$tab = $wecfg['cache_id']; 
	$wmDbCfg = cls_w08Menu::getMenuData($wecfg, $tab); 
	$wmDefList = cls_w08Menu::getMenuDef($wmDbCfg, $wmDefCfg, $tab);
	$wmPickData = cls_w08Menu::getMenuPick($wmDefCfg, $tab);
	
	if(!submitcheck('menusave') && !submitcheck('menucreate') && !submitcheck('menudelete')){
		
		tabheader("菜单配置",'wxmenu',"?entry=$entry&action=$action&aid=$aid&mid=$mid",2,0,1);
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
						 &nbsp; <a id='cupick_$imuid' href='javascript:;' onClick=\"wxMenuPickWin($imuid)\">&lt;&lt; 选取菜单</a>
						 &nbsp; <a id='cupick_$imuid' href='javascript:;' onClick=\"wxMenuClearWin($imuid)\">&lt;&lt;清空</a></td>\n";
			$itemstr .= "</tr>";
			echo $itemstr;
		} }
		//tabfooter('wxmenu');
		$btntitle = $type=='sid' ? '创建微信菜单' : "创建[$type=$key]的微信菜单";
		$btncreate = "<input class='btn use_menu' type='submit' name='menucreate' value='$btntitle' onclick=\"_08cms_layer({type: 2, url:'?entry=$entry&action=$action&menucreate=1&tab=$tab&aid=$aid&mid=$mid&infloat=1',title:'$btntitle',height:240}); return false;\">";
		$btndelete = "<input class='btn use_menu' type='submit' name='menudelete' value='删除微信菜单' onclick=\"_08cms_layer({type: 2, url:'?entry=$entry&action=$action&menudelete=1&tab=$tab&aid=$aid&mid=$mid&infloat=1',title:'删除微信菜单',height:240}); return false;\">";
		$btnextra = "$btncreate &nbsp; $btndelete";
		tabfooter('menusave', '保存微信菜单', " &nbsp; $btnextra");
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
		$whr = $wecfg['fromid_type']=='sid' ? array('mcid'=>0) : array('appid'=>$wecfg['appid']);
		//$whr = $whr + array('key'=>$k); echo "$tab"; print_r($whr);
		foreach($catalogsnew as $k=>$v){
			if(empty($v['name'])){
				$db->delete('#__weixin_menu')->where($whr+array('key'=>$k))->exec();
			}if(isset($wmDbCfg[$k])){ 
				$db->update('#__weixin_menu', $v)->where($whr+array('key'=>$k))->exec();
			}elseif(!empty($v['name'])){
				$v['appid'] = $wecfg['appid'];
				$v['key'] = $k;
				if($wecfg['fromid_type']=='sid'){
					$v['mcid'] = 0;	
				}
				$db->insert('#__weixin_menu', $v)->exec();
			}
		}
		cls_message::show('菜单配置保存完成',axaction(1,M_REFERER));
	}elseif(submitcheck('menucreate')){
		$weixin = new cls_w08Menu($wecfg); 
		$data = $weixin->create($wmDbCfg); 
		die('<br>创建完成，点击关闭！'); 
	}elseif(submitcheck('menudelete')){
		$weixin = new cls_w08Menu($wecfg); 
		$data = $weixin->del(); 
		die('删除完成，点击关闭！'); 
	}

}elseif($action=='follow'){

	echo "<div class='conlist1'>关注者管理</div><div id='wx_utable'><br>&nbsp;<br>加载中……<br>如果出现错误，请检查公众号配置或刷新页面；<br>如果翻页中出现错误，请重新点翻页。</div>
	      <div id='p_bar' class='p_bar'></div>"; 
	$weixin = new cls_wmpUser($wecfg); 
	$data = $weixin->getUserInfoList();
	$ustr = implode(',',$data['data']['openid']);
	$ustr = str_replace(array('-'),array('~'),$ustr);
	?>
	<script>
	<?php
	echo "var wu_total=$data[total], wu_count=$data[count], wu_appid='{$wecfg['appid']}', wu_next='$data[next_openid]', wu_page=1, 
	wu_msgurl='?entry=weappid&aid=$aid&mid=$mid&action=message&tab=sendform&openid=', wu_ismc=0, wu_list='$ustr';";
	?>
	wxGetUserPage(); wxGetPageBar(wu_page);
	</script>
    <?php
	
}elseif($action=='message' && $tab=='msgget'){ 

	echo $msgtabstr;

  $keyword = empty($keyword) ? '' : $keyword; 
  $keytype = empty($keytype) ? 'detail' : $keytype;
  $filterstr = ''; //$checked?"&checked=$checked":
  foreach(array('aid','mid','infloat','action','tab') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE appid='{$wecfg['appid']}' "; //cu.mid='$memberid' commu_offer. archives1.
  $fromsql = "FROM {$tblprefix}weixin_msgget ";
    
  if($keyword){
	  if($keytype){
	  	$wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }else{
	  	$wheresql .= " AND (detail ".sqlkw($keyword)." ) ";
	  }
  } 
  //
  if(!submitcheck('bsubmit')){
	  
	  echo form_str('sendlogs',"?entry=$entry$filterstr&page=$page");
	  tabheader_e();
	  echo "<tr><td class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键词\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('0'=>'--筛选范围--','detail'=>'内容','openid'=>'openID'),$keytype)."</select>&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader("接收消息记录$plink",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  $cy_arr[] = '类型';
	  $cy_arr[] = '信息内容|L';
	  $cy_arr[] = '发送用户';
	  $cy_arr[] = '时间';
	  $cy_arr[] = '回复状态';
	  $cy_arr[] = '现在回复';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY id DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  
	  $itemstr = ''; 
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[id]]\" value=\"$r[id]\">";
		  $time = date('Y-m-d H:i',$r['ctime']);
		  if($r['type']=='text'){ $type = '文本'; }
		  elseif($r['type']=='image'){ $type = '图片'; }
		  elseif($r['type']=='news'){ $type = '图文'; }
		  else{ $res = '---'; }
		  if($r['restate']=='Auto'){ $res = '自动'; }
		  elseif($r['restate']=='Kefu'){ $res = '客服'; }
		  else{ $res = '---'; }
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtC\">$type</td>\n";
		  $itemstr .= "<td class=\"txtL\">".mhtmlspecialchars(cls_string::CutStr($r['detail'],40))."</td>\n";
		  $itemstr .= "<td class=\"txtC\">$r[openid]</td>\n";
		  $itemstr .= "<td class=\"txtC w110\">$time</td>\n";
		  $itemstr .= "<td class=\"txtC w60\">$res</td>\n";
		  $itemstr .= "<td class=\"txtC w60\"><a href=\"?entry=$entry&aid=$aid&mid=$mid&action=message&tab=sendform&id=$r[id]\" onclick=\"return floatwin('open_reply',this)\">回复</a></td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry&page=$page$filterstr");
  
	  tabheader('批量操作');
	  $str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"now\">删除记录 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m3\">删除3月前 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m1\">删除1月前 &nbsp;";
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除记录",'',$str,'');
	  tabfooter('bsubmit');
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($arcdeal_del)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	//echo "$arcdeal[delete],$arcdeal_del";
	if($arcdeal_del=='now'){
		if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&page=$page$filterstr");
		foreach($selectid as $k){
			$db->query("DELETE FROM {$tblprefix}weixin_msgget WHERE id='$k'",'UNBUFFERED');
			continue;
		}
	}elseif($arcdeal_del=='m3'){
		$sql = "DELETE FROM {$tblprefix}weixin_msgget WHERE ctime<='".($timestamp-90*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}elseif($arcdeal_del='m1'){
		$sql = "DELETE FROM {$tblprefix}weixin_msgget WHERE ctime<='".($timestamp-30*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}
	cls_message::show('记录批量操作成功',axaction(1,"?entry=$entry&page=$page$filterstr"));
 }
 
}elseif($action=='message' && $tab=='msgsend'){ 
	
	if(!empty($infloat)){
		echo $subnav;	
	}
	echo $msgtabstr;

  $keyword = empty($keyword) ? '' : $keyword; 
  $keytype = empty($keytype) ? 'detail' : $keytype;
  $filterstr = ''; //$checked?"&checked=$checked":
  foreach(array('aid','mid','infloat','action','tab') as $k) $$k && $filterstr .= "&$k=".rawurlencode(stripslashes($$k));

  $selectsql = "SELECT * ";
  $wheresql = " WHERE appid='{$wecfg['appid']}' "; //cu.mid='$memberid' commu_offer. archives1.
  $fromsql = "FROM {$tblprefix}weixin_msgsend ";
    
  if($keyword){
	  if($keytype){
	  	$wheresql .= " AND ($keytype ".sqlkw($keyword).") ";
	  }else{
	  	$wheresql .= " AND (detail ".sqlkw($keyword)." ) ";
	  }
  } 
  
  if(!submitcheck('bsubmit')){
	  
	  echo form_str('sendlogs',"?entry=$entry$filterstr&page=$page");
	  tabheader_e();
	  echo "<tr><td class=\"txt txtleft\">";
	  echo "<input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" size=\"8\" style=\"vertical-align: middle;\" title=\"关键词\">&nbsp; ";
	  echo "<select style=\"vertical-align: middle;\" name=\"keytype\">".makeoption(array('0'=>'--筛选范围--','media_id'=>'信息ID','subject'=>'标题','detail'=>'内容','openid'=>'openID'),$keytype)."</select>&nbsp; ";
	  echo strbutton('bfilter','筛选');
	  tabfooter();
	  tabheader("发送消息记录$plink",'','',10);
	  $cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">");
	  $cy_arr[] = '类型';
	  $cy_arr[] = '信息内容|L';
	  $cy_arr[] = '发送用户';
	  $cy_arr[] = '时间';
	  //$cy_arr[] = '现在回复';
	  
	  trcategory($cy_arr);
  
	  $pagetmp = $page; //echo "$selectsql $fromsql $wheresql";
	  do{
		  $query = $db->query("$selectsql $fromsql $wheresql ORDER BY id DESC LIMIT ".(($pagetmp - 1) * $mrowpp).",$mrowpp");
		  $pagetmp--;
	  } while(!$db->num_rows($query) && $pagetmp);
  	
	  $itemstr = '';
	  while($r = $db->fetch_array($query)){
		  $selectstr = "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$r[id]]\" value=\"$r[id]\">";
		  $time = date('Y-m-d H:i',$r['ctime']);
		  if($r['type']=='text'){ $type = '文本'; }
		  elseif($r['type']=='news'){ $type = '图文'; }
		  else{ $type = '---'; }
		  if(!empty($r['media_id'])){
			  $detail = '媒体ID: '.$r['media_id'];
		  }elseif(!empty($r['subject'])){ 
			  $detail = '标题: '.$r['detail'];
		  }else{
		  	  $detail = $r['detail'];
		  }
		  if($r['openid']=='-1'){
			  $openid = "(群发:所有)";
		  }elseif(is_numeric($r['openid'])){
			  $openid = "(群发:分组ID={$r['openid']})";
		  }else{
			  $openid = $r['openid']; 
		  }
		  $detail = mhtmlspecialchars(cls_string::CutStr($detail,40));
		  $itemstr .= "<tr class=\"txt\"><td class=\"txtC w40\">$selectstr</td>";
		  $itemstr .= "<td class=\"txtC w60\">$type</td>\n";
		  $itemstr .= "<td class=\"txtL\">$detail</td>\n";
		  $itemstr .= "<td class=\"txtC\">$openid</td>\n";
		  $itemstr .= "<td class=\"txtC w110\">$time</td>\n";
		  //$itemstr .= "<td class=\"txtC w60\">回复</td>\n";
		  $itemstr .= "</tr>\n"; 
		  
	  }
	  echo $itemstr;
	  tabfooter();
	  echo multi($db->result_one("SELECT count(*) $fromsql $wheresql"),$mrowpp,$page, "?entry=$entry&page=$page$filterstr");
  
	  tabheader('批量操作');
	  $str = "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"now\">删除记录 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m3\">删除3月前 &nbsp;";
	  $str .= "<input class=\"radio\" type=\"radio\" name=\"arcdeal_del\" value=\"m1\">删除1月前 &nbsp;";
	  trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[delete]\" value=\"1\" onclick='deltip()'> 删除记录",'',$str,'');
	  tabfooter('bsubmit');
  }else{
	if(empty($arcdeal)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	if(empty($arcdeal_del)) cls_message::show('请选择操作项目。',"?entry=$entry&page=$page$filterstr");
	//echo "$arcdeal[delete],$arcdeal_del";
	if($arcdeal_del=='now'){
		if(empty($selectid)) cls_message::show('请选择记录。',"?entry=$entry&page=$page$filterstr");
		foreach($selectid as $k){
			$db->query("DELETE FROM {$tblprefix}weixin_msgsend WHERE id='$k'",'UNBUFFERED');
			continue;
		}
	}elseif($arcdeal_del=='m3'){
		$sql = "DELETE FROM {$tblprefix}weixin_msgsend WHERE ctime<='".($timestamp-90*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}elseif($arcdeal_del='m1'){
		$sql = "DELETE FROM {$tblprefix}weixin_msgsend WHERE ctime<='".($timestamp-30*24*3600)."'";
		//echo "$sql";
		$db->query($sql,'UNBUFFERED');
	}
	cls_message::show('记录批量操作成功',axaction(1,"?entry=$entry&page=$page$filterstr"));
 }

}elseif($action=='message' && $tab=='sendform'){ 

	$id = empty($id) ? 0 : $id;
	$openid = empty($openid) ? 0 : $openid;
	$pushdata = cls_w08push::getPushArea(); //print_r($pushdata);
	if(!submitcheck('dosendform')){
		
		tabheader("回复/群发消息",'sendform',"?entry=$entry&aid=$aid&mid=$mid&action=message&tab=sendform");
		if(!empty($id) || !empty($openid)){
			if($id){
				$row = $db->select()->from('#__weixin_msgget')->where(array('id'=>$id))->exec()->fetch();
				$openid = $row['openid'];	
			}
			define('WX_ERR_RETURN',1);
			$weixin = new cls_wmpUser($wecfg);
			$udata = $weixin->getUserInfo($openid); //print_r($udata);
			if(!empty($udata['errcode'])){
				$utitle = "获取用户信息失败：".$udata['errcode']." <br>(".$udata['message'];
			}else{
				$utitle = "[".@$udata['city']."]".@$udata['nickname']."";
			}
			trbasic('客服回复','',"$utitle ($openid)",'',array('w'=>60,'validate'=>"maxlength='60'",'guide'=>'当用户和公众号产生特定动作的交互时方可调用本接口，具体规定请看微信公众平台文档。'));
		}else{
			$weixin = new cls_wmpUser($wecfg); //print_r($wecfg);
			$data = $weixin->groupList(); $garr = array();
			foreach($data['groups'] as $k=>$v){
				$k2 = $v['id'] ? $v['id'] : '-1';
				$gname = $k ? "群发分组:$k2-{$v['name']}[{$v['count']}人]" : "群发所有用户:(含未分组:[{$v['count']}]人)";
				$garr[$k2] = $gname;
			} // [id] => 0 [name] => 未分组[count] => 1
			trbasic('* 群发对象','nmsg[groupid]',makeoption($garr,0),'select',array('guide'=>'群发数量非常有限，具体限制请看微信公众平台文档。'));
		}
		//*
		echo "<tr class=\"txt\"><td class=\"txt txtright fB\">消息格式</td>\n".
			"<td class=\"txtL\">\n";
			echo "<label><input type=\"radio\" class=\"radio\" name='nmsg[type]' value='text' onclick=\"wxSendType(this)\" checked>文本消息</label> ";
			foreach($pushdata as $paid=>$v){
				echo "&nbsp; &nbsp; \n<label><input type=\"radio\" class=\"radio\" name='nmsg[type]' value='$paid' onclick=\"wxSendType(this)\">$v[cname]</label> \n";
			}
		echo "</td></tr>\n";
		//*/
		#trhidden('nmsg[type]','text');
		trbasic('* 信息内容','nmsg[detail]','','textarea',array('w'=>360,'validate'=>'240','guide'=>'最多240个字.','rowid'=>"rowd_text"));
		foreach($pushdata as $paid=>$v){
			$str = "";
			foreach($v['data'] as $val){
				$str .= "<br>\n<input class='checkbox' type='checkbox' name='nmsg[ids][]' value='$val[pushid]'> <a href='$val[url]' target='_blank'>$val[title]</a>";
			}
			trbasic("* {$v['cname']}",'',"您可选取如下：{$v['cname']}信息推送：$str",'',array('rowid'=>"rowd_$paid",'rshow'=>0));
		}
		if(!empty($id)){
			trbasic(' 原消息','',"<textarea class='js-resize' style='width:360px;height:100px;border:1px solid #DDD;' disabled>{$row['detail']}</textarea>",'');
		}
		trhidden('id',@$id);
		trhidden('openid',@$openid);
		tabfooter('dosendform','发送');
		echo "\r\n<script type='text/javascript'></script>"; //wxKwdsetSence(1,1);
		#a_guide('wxkwdsadd');
	}else{
		$detail = @$nmsg['detail']; //print_r($nmsg);
		if(!empty($nmsg['ids'])){
			$pdata = $pushdata[$nmsg['type']];
			$deids = ""; $cnt = 0;
			foreach($pdata['data'] as $k=>$v){
				if(in_array($v['pushid'],$nmsg['ids'])){
					$cnt++; if($cnt>8) continue;
					$deids .= "\n● <a href='{$v['url']}'>{$v['title']}</a>";	
				}
			}
			$deids && $detail = "为您推送如下".$pdata['cname']."信息：$deids";
		}
		$groupid = @$nmsg['groupid'];
		//die("$detail.flag");
		$detail || cls_message::show('缺少回复内容',M_REFERER);
		if($openid && $detail){
			$weixin = new cls_wmpMsgsend($wecfg);
			$data = $weixin->sendText($openid,stripslashes($detail));
			//记录... 
			$db->query("UPDATE {$tblprefix}weixin_msgget SET restate='Kefu' WHERE id='$id' "); 
			$sqlex = ",ctime='".TIMESTAMP."',appid='{$wecfg['appid']}',openid='$openid'";
			$db->query("INSERT INTO {$tblprefix}weixin_msgsend SET type='text',detail='".maddslashes($detail,1)."'$sqlex ");
		}elseif($groupid && $detail){
			$weixin = new cls_wmpMsgmass($wecfg);
			$g2id = $groupid==-1 ? 0 : $groupid; //echo "$g2id";
			$data = $weixin->sendText(stripslashes($detail),$g2id); 
			//记录... 
			$sqlex = ",ctime='".TIMESTAMP."',appid='{$wecfg['appid']}',openid='$groupid'";
			$db->query("INSERT INTO {$tblprefix}weixin_msgsend SET type='text',detail='".maddslashes($detail,1)."'$sqlex ");
		}else{
			//$db->query("INSERT INTO {$tblprefix}weixin_keywords SET $basesql $extrasql ");
		}
		cls_message::show("回复/群发完成",axaction(6,"?entry=$entry&aid=$aid&mid=$mid&action=keyword"));
	}

}elseif($action=='material'){
	
	echo 'material-完善中…';
	
}elseif($action=='keyword'){

	$kwds = fetch_arr($wecfg['appid']);
	if(!submitcheck('bwxkwdsedit')){
		$addlink = "&nbsp; >><a href=\"?entry=$entry&aid=$aid&mid=$mid&action=keywdadd\" onclick=\"return floatwin('open_keywdadd',this)\">添加</a>";
		tabheader("关键词管理 $plink $addlink",'wxkwdsedit',"?entry=$entry&aid=$aid&mid=$mid&action=$action",'8');
		trcategory(array(array('关键词','txtL'),array('内容','txtL'),'排序','删?','详细')); //'启用',
		foreach($kwds as $kid => $kwd){
			$ikwd = $kwd['keyword']=='add_friend_autoreply_info' ? '(关注时自动回复)' : $kwd['keyword'];
			echo "<tr class=\"txt\">".
				"<td class=\"txtL\">".mhtmlspecialchars($ikwd)."</td>\n".
				"<td class=\"txtL\">".mhtmlspecialchars(cls_string::CutStr($kwd['detail'],20))."</td>\n".
				"<td class=\"txtC w40\"><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"wxkwdsnew[$kid][vieworder]\" value=\"$kwd[vieworder]\"></td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"  name=\"delete[$kid]\" value=\"1\" onclick=\"deltip(this)\" >\n".
				"<td class=\"txtC w30\"><a href=\"?entry=$entry&aid=$aid&mid=$mid&action=keywdadd&id=$kid\" onclick=\"return floatwin('open_wxkwddetail',this)\">设置</a></td>\n".
				"</tr>\n";
		}

		tabfooter('bwxkwdsedit');
		//a_guide('wxkwdsedit');
	}else{
		
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k=>$v){
				$db->query("DELETE FROM {$tblprefix}weixin_keywords WHERE id='$k'");
			}
		}
		foreach($wxkwdsnew as $id => $v){
			$v['available'] = empty($v['available']) ? 0 : $v['available'];
			$db->query("UPDATE {$tblprefix}weixin_keywords SET vieworder='$v[vieworder]' WHERE id='$id'"); //available='$v[available]',
		}
		cls_message::show('关键词修改完成',axaction(1,M_REFERER));
		//cls_message::show('关键词修改完成');

	}

}elseif($action=='keywdadd'){
	
	$id = empty($id) ? 0 : $id;
	$lable = $id ? '修改' : '添加';
	if($id){
		$row = $db->select()->from('#__weixin_keywords')->where(array('id'=>$id))->exec()->fetch();
	}else{
		if(count(fetch_arr($wecfg['appid']))>=50) cls_message::show('最多只能加50条关键词记录',axaction(1,M_REFERER));;	
	}
	
	if(!submitcheck('bgwxkwdsadd')){
		
		tabheader("关键词{$lable}",'wxkwdsadd',"?entry=$entry&aid=$aid&mid=$mid&action=keywdadd");

		echo "<tr class=\"txt\"><td class=\"txt txtright fB\">回复场景</td>\n".
		"<td class=\"txtL\">\n";
		#foreach($sms_cfg_aset as $k=>$v){
			echo "<label><input type=\"radio\" class=\"radio\" id=\"sence_1\" name='sence_k' onclick='wxKwdsetSence(1)' checked>按信息关键词回复</label> &nbsp; &nbsp; \n"
				."<label><input type=\"radio\" class=\"radio\" id=\"sence_0\" name='sence_k' onclick='wxKwdsetSence(0)'>关注时回复</label> \n";
		#}
		echo "</td></tr>\n";
		
		trbasic('* 关键词','wxkwdsadd[keyword]',@$row['keyword'],'text',array('w'=>60,'validate'=>"maxlength='60'",'guide'=>'最多60个字,可用半角逗号[,]分开; 关键词不要重复,不要一个关键词包含另一关键词.'));
		trbasic('* 回复内容','wxkwdsadd[detail]',@$row['detail'],'textarea',array('w'=>360,'validate'=>'240','guide'=>'最多240个字.'));
		
		trbasic('排序','wxkwdsadd[vieworder]',@$row['vieworder'],'text',array('guide' => '列表排序'));
		trhidden('id',$id);
		tabfooter('bgwxkwdsadd','添加');
		if($id && @$row['keyword']==='add_friend_autoreply_info'){
			echo "\r\n<script type='text/javascript'>wxKwdsetSence(0);\$id('sence_0').checked=true;</script>";
		}
		#a_guide('wxkwdsadd');
	}else{
		$wxkwdsadd['keyword'] || cls_message::show('缺少关键词',M_REFERER);
		$wxkwdsadd['detail'] || cls_message::show('缺少回复内容',M_REFERER);
		//$wxkwdsadd['tpl'] || cls_message::show('缺少模板文件',M_REFERER);
		$wxkwdsadd['vieworder'] = empty($wxkwdsadd['vieworder']) ? 0 : max(0,intval($wxkwdsadd['vieworder']));
		$wxkwdsadd['ttl'] = empty($wxkwdsadd['ttl']) ? 0 : max(0,intval($wxkwdsadd['ttl']));
		$basesql = "keyword='$wxkwdsadd[keyword]',detail='$wxkwdsadd[detail]',vieworder='$wxkwdsadd[vieworder]'";
		$extrasql = ",appid='{$wecfg['appid']}',type='text'"; //,available='1'
		if($id){
			$db->query("UPDATE {$tblprefix}weixin_keywords SET $basesql WHERE id='$id' ");
		}else{
			$db->query("INSERT INTO {$tblprefix}weixin_keywords SET $basesql $extrasql ");
		}
		cls_message::show("{$lable}关键词完成",axaction(6,"?entry=$entry&aid=$aid&mid=$mid&action=keyword"));
	}

} //echo "$action=='message' && $tab=='msgget'";

function fetch_arr($appid,$tab='weixin_keywords'){
	global $db,$tblprefix;
	$keywords = array();
	$query = $db->query("SELECT * FROM {$tblprefix}$tab WHERE appid='$appid' ORDER BY vieworder");
	while($row = $db->fetch_array($query)){
		$keywords[$row['id']] = $row;
	}
	return $keywords;
}
function wxs_nav($title='',$arr = array(),$current='',$numpl=8){//针对所选择的链接，高亮当前页
	global $aid,$mid;
	$multi = count($arr) < $numpl ? 0 : 1;
	echo "<div class=\"itemtitle\"><h3".(!$multi ? '' : ' class=h3other').">$title</h3><ul class=\"tab1".(!$multi ? '' : '  tab0 bdtop')."\">\n";
	foreach($arr as $k => $v){
		$nclassstr = (!$multi ? '' : 'td24').($k == $current ? ' current' : '');
		echo "<li".($nclassstr ? " class=\"$nclassstr\"" : '')."><a href=\"?entry=weappid&action=$k&aid=$aid&mid=$mid\"><span>$v</span></a></li>\n";
	}
	echo "</ul></div><div class=\"blank15h\"></div>";
}

?>
