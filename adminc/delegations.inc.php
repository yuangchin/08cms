<?php
 
$cuid = 36; //接受外部传chid，但要做好限制
$caid = empty($caid) ? 0 : max(1,intval($caid));
$chid = empty($chid) ? 3 : max(2,intval($chid)); 
$cid = empty($cid) ? 0 : max(0,intval($cid));
$mid = $curuser->info['mid'];
$class = empty($cid) ? 'cls_culist' : 'cls_cuedit';
$_init = array(
	'cuid' => $cuid,//交互模型id
	'ptype' => 'u',
	'pchid' => $chid,
	'caid' => $caid,
	'url' => "&chid=$chid", //表单url，必填，不需要加入mchid
	'select'=>"SELECT w.owerstatus,w.jjrstatus,w.weituodate,cu.cid,cu.pid,cu.louhao,cu.loushi,cu.mj,cu.shi,cu.ting,cu.wei,cu.zj,cu.lxr,cu.lpmc,cu.chid as cchid,cu.createdate as cucreate ",
	'from'=>" FROM {$tblprefix}weituos w INNER JOIN {$tblprefix}commu_weituo cu ON w.cid=cu.cid ",
	'where' => " AND w.tmid='$mid' AND cu.chid = '$chid'", //附加条件,前面需要[ AND ]
);


if($cid){
    if(!($commu = cls_cache::Read('commu',$cuid)) || !$commu['available']) cls_message::show('委托功能已关闭。');
    if(empty($ysubmit) && empty($nsubmit)){
    $result = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] c INNER JOIN {$tblprefix}weituos w ON w.cid=c.cid WHERE c.cid='$cid' AND w.tmid='$memberid'");
    empty($result) && cls_message::show('操作对象不存在',axaction(6,M_REFERER));		
    echo form_str('viewweituo',"?action=$action&cid=$cid");
    tabheader('联系方式');
    trbasic('手机','',$result['tel'],'');
    trbasic('联系人','',$result['lxr'],'');
    tabfooter();
    tabheader('基本信息');
    trbasic('小区名称','',$result['lpmc'],'');
    trbasic('楼栋号','',$result['louhao'].'号/幢'.$result['loushi'].'室','');
    trbasic($result['chid']==3 ? '产证面积' : '出租面积','',$result['mj'].'平方米','');
    trbasic('房型','',$result['shi'].'室'.$result['ting'].'厅'.$result['wei'].'卫','');
    trbasic($result['chid'] == 3 ? '售价' : '租金','',$result['zj'].($result['chid'] == 3 ? '万元' : '元/月'),'');
	$farr = cls_field::options(cls_cache::Read('field', 2, 'zlfs')); 
    $result['chid'] == 2 && trbasic('租赁方式','',$result['zlfs'] ? $farr[$result['zlfs']] : '',''); 
    tabfooter();
    
    if($result['jjrstatus'] != 2)echo '<div align="center"><input class="button" type="submit" name="ysubmit" value="保存，接受委托" onclick="return confirm(\'在接受委托之前，请确认你们已经线下达成协议。\')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="button" type="submit" name="nsubmit" value="拒绝委托"></div></form>';
    }elseif(!empty($ysubmit)){
    $fmdata = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] c INNER JOIN {$tblprefix}weituos w ON w.cid=c.cid WHERE c.cid='$cid' AND w.tmid='$memberid'");
    empty($fmdata) && cls_message::show('操作对象不存在',axaction(6,M_REFERER));
    $fmdata['jjrstatus'] == 2 && cls_message::show('请勿重复操作。',axaction(6,M_REFERER));
    $fmdata['caid'] = $fmdata['chid'] == 3 ? '3' : '4';
    $fmdata['subject'] = $fmdata['address'];
    $fields = cls_cache::Read('fields',$fmdata['chid']);
    $cotypes = cls_cache::Read('cotypes');	
    $a_field = new cls_field;
    $arc = new cls_arcedit;
    if($aid = $arc->arcadd($fmdata['chid'],$fmdata['caid'])){
    
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
    	//插入标题的首写字母以及全拼
    	$arc->updatefield('subjectstr',cls_string::Pinyin(str_replace('\\','',$fmdata['subject']),1));
    	
    	//新增字段mchid，存放会员的模型ID，区分是个人发布还是经纪人发布
    	$arc->updatefield('mchid',$curuser->info['mchid']);
    			
    	$arc->updatefield('fdname',$fmdata['lxr'],"archives_$fmdata[chid]");
    	$arc->updatefield('fdtel',$fmdata['tel'],"archives_$fmdata[chid]");
    	$curuser->detail_data();
    	$arc->updatefield('lxdh',$curuser->info['lxdh'],"archives_$fmdata[chid]");
    	$arc->updatefield('xingming',$curuser->info['xingming'],"archives_$fmdata[chid]");
    	$validday = empty($validday) ? 30 : $validday;
    	$membervalidday = ($curuser->info['grouptype14date'] - $timestamp)/86400;
    	$arc->setend($curuser->info['grouptype14'] == 8 && $membervalidday > $validday ? $membervalidday : $validday);
    	$arc->auto();
    	$arc->autocheck();
    	$arc->updatedb();
    	$db->query("UPDATE {$tblprefix}weituos set jjrstatus='2' WHERE tmid='$memberid' AND cid='$cid'");
    	cls_message::show(($fmdata['chid'] == 3 ? '【委托出售】' : '【委托出租】')."添加成功<br/>马上去完善房源资料 <a href=\"?action=".($fmdata['chid'] == 2 ? 'chuzu' : 'chushou')."add&aid=$aid\" onclick=\"return floatwin('open_inarchive',this)\">{$fmdata['subject']}</a>");
    }else{
    	cls_message::show('添加失败',axaction(6,M_REFERER));	
    }
    cls_message::show('操作成功',axaction(6,M_REFERER));
    }elseif(!empty($nsubmit)){
    $db->query("UPDATE {$tblprefix}weituos set jjrstatus='1' WHERE tmid='$memberid' AND cid='$cid'");
    cls_message::show('操作成功',axaction(6,M_REFERER));
    }

}else{
	$oL = new $class($_init); 
	$oL->top_head();
    
	//搜索项目 ****************************
	$oL->s_additem('keyword',array('fields' => array('cu.lpmc'=>'小区名称'),'custom'=>1));
    $oL->s_additem('jjrstatus',array('pre'=>'w.'));
	$oL->s_additem('indays');
	$oL->s_additem('outdays');   
    
	//搜索sql及filter字串处理
	$oL->s_deal_str();
	
    if(empty($tmp)){        
    	$_menu = $chid == 2 ? 'chuzu' : 'chushou';
    	backnav('weituo',$_menu);
    }
	//搜索区域 ******************
	$oL->s_header();
	$oL->s_view_array();        
    $oL->s_footer();
    
	
	//显示列表区头部 ***************
	$oL->m_header();
    $oL->m_additem('xqimg',array('title'=>'小区图片','side'=>'L'));
	$oL->m_additem('lpmc',array('title'=>'小区名称','mtitle'=>"小区名称:<font color='#36F'>{lpmc}</font>",'side'=>'L')); 
    $oL->m_additem('louhao',array('title'=>'楼号','mtitle'=>'{louhao}号/幢')); 
    $oL->m_additem('loushi',array('title'=>'楼室','mtitle'=>'{loushi}室')); 
    $oL->m_additem('mj',array('title'=>'面积','mtitle'=>'{mj}平方米')); 
    
    $oL->m_additem('shi',array('title'=>'室','mtitle'=>'{shi}室')); 
    $oL->m_additem('ting',array('title'=>'厅','mtitle'=>'{ting}厅')); 
    $oL->m_additem('wei',array('title'=>'卫','mtitle'=>'{wei}卫')); 
    
    if($chid==2){//出租
	   $oL->m_additem('zj',array('title'=>'租金','mtitle'=>"{zj}元/月"));
	}elseif($chid==3){//出售
	   $oL->m_additem('zj',array('title'=>'总价','mtitle'=>"{zj}万元"));
	}
    	
    $oL->m_addgroup('{lpmc}<br/>地址：{louhao}&nbsp;{loushi}<br/>{mj}&nbsp;{shi}{ting}{wei}&nbsp;{zj}','描述信息');//请注意分组不能嵌套，每项只能参与一次分组  
    
    
    $oL->m_additem('lxr',array('title'=>'联系人','mtitle'=>'联系人：{lxr}','side'=>'L'));		       
	$oL->m_additem('connectinfo',array('type'=>'url','title'=>'联系','mtitle'=>'查看联系','url'=>"?action=$action&cuid=$cuid&cid={cid}&chid=$chid",'width'=>40,));
    $oL->m_additem('cucreate',array('type'=>'date','title'=>'委托日期','mtitle'=>'{cucreate}'));
    $oL->m_addgroup('{lxr}<br/>委托日期：{cucreate}<br/>{connectinfo}','申请信息'); 
     
    $oL->m_additem('entrusted_state',array('type'=>'date','title'=>'委托状态'));
    
	$oL->m_view_top(); //显示索引行，多行多列展示的话不需要
	$oL->m_view_main(); 
	$oL->m_footer(); //显示列表区尾部
	

	$oL->o_view_bools(); //显示单选项
	
	$oL->o_footer('');
	$oL->guide_bm('','0');
		

			
}

?>