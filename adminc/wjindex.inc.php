<?
!defined('M_COM') && exit('No Permission');

$grouptypes = cls_cache::Read('grouptypes');
$mctypes = cls_cache::Read('mctypes');

function d_time_format($time, $fix = '更新'){
	global $timestamp;
	$time = $timestamp - $time;
	if($time < 60){
		return '才刚刚'.$fix;
	}elseif($time < 1800){
		return floor($time / 60) . '分钟前'.$fix;
	}elseif($time < 3600){
		return '半小时前'.$fix;
	}elseif($time < 86400){
		return floor($time / 3660) . '小时前'.$fix;
	}elseif($time < 86400 * 30){
		return floor($time / 86400) . '天前'.$fix;
	}else{
		return floor($time / 86400 / 30) . '个月前'.$fix;
	}
}
$curuser->sub_data();
$usergroupstr = '';
foreach($grouptypes as $k => $v){
	if($curuser->info['grouptype'.$k]){
		$usergroups = cls_cache::Read('usergroups',$k);
		$usergroupstr .=  '<span>'.$usergroups[$curuser->info['grouptype'.$k]]['cname'].'</span>';
	}
}

$sendchuzunum = cls_DbOther::ArcLimitCount(2, '');
$sendchushounum = cls_DbOther::ArcLimitCount(3, ''); 
$chuzunum = cls_DbOther::ArcLimitCount(2, 'enddate', 'valid'); 
$chushounum = cls_DbOther::ArcLimitCount(3, 'enddate', 'valid'); 

$tuijiannums_cz = cls_DbOther::ArcLimitCount(2, 'ccid19', '>0');
$tuijiannums_cs = cls_DbOther::ArcLimitCount(3, 'ccid19', '>0');


$showhynews = '';
$query=$db->query("SELECT * From {$tblprefix}".atbl(1)." where chid=1 and checked=1 order by aid desc limit 0,5");
while($row=$db->fetch_array($query)){
	$row['arcurl'] = cls_ArcMain::Url($row);
	$subject=cls_string::CutStr($row['subject'],28);
	$showhynews.="<li><a href=\"{$row['arcurl']}\" title=\"{$row['subject']}\" target=\"_blank\">$subject</a></li>";
}

$showxzl = '';
$query=$db->query("SELECT * From {$tblprefix}".atbl(4)." a INNER JOIN {$tblprefix}archives_4 c ON c.aid=a.aid where a.chid=4 and a.checked=1 AND (c.leixing='0' OR c.leixing='1') order by a.updatedate desc limit 0,4");
while($row=$db->fetch_array($query)){
	$row['arcurl'] = cls_ArcMain::Url($row);
	$subjectdes=$row['createdate'] == $row['updatedate'] ? "发布了楼盘相关信息!(".d_time_format($row['createdate'],"发布").")" : "更新了楼盘相关信息!(".d_time_format($row['updatedate'],"更新").")" ;
	$showxzl.="<li><a href=\"{$row['arcurl']}\" title=\"{$row['subject']}\" target=\"_blank\">{$row['subject']}</a> $subjectdes</li>";
}


$cuid = 5;
$lycommu = cls_cache::Read('commu',$cuid);
$liuyancount = $db->result_one("SELECT count(*) FROM {$tblprefix}$lycommu[tbl] WHERE tomid='$memberid'");

$mcertimg = '';
$mcertname = '';
foreach($mctypes as $k => $v){
	if($v['available']){
		if($curuser->info["mctid$k"]){
			!empty($v['icon']) && $mcertimg .= "<img src=\"$v[icon]\" alt=\"$v[cname]\" title=\"$v[cname]\" />$v[cname]&nbsp;&nbsp;";
			$mcertname .= "$v[cname] ";
		}
	}
}

/*v2 系统参数*/
$valid_1str = "(enddate=0 OR enddate>'$timestamp')";

if(!empty($curuser->info['grouptype31']) || !empty($curuser->info['grouptype32']))
	$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);    
?>

    <div class="r_col_l blue_a">
        <!--信息-->
        <div class="welcome gray_t">
            <div class="img"><img src="<?=empty($curuser->info['image']) ? $cms_abs."images/common/mlogo.gif" : (empty($ftp_enabled)?$cms_abs:$ftp_url).$curuser->info['image']?>" /><br/><a href="adminm.php?action=memberinfo">修改资料</a><br/>(上传头像可获得积分)</div>
            <div class="hint">
                <strong>您好，<?=$curuser->info['mname']?></strong><br/><?=$mcertimg?>
                <p>
                您当前积分：<b><font color="#990000"><?=$curuser->info['currency1']?></font></b> 分 &nbsp;&nbsp;<? if(!empty($curuser->info['grouptype14'])) echo '信用值：<b><font color="#990000">'.$curuser->info['currency2'] .'</font></b> 分'?><br/>
                当前金额：<b><font color="#990000"><?=$curuser->info['currency0']?></font></b> 元 
                <?php if(in_array($curuser->info['mchid'],array(1,2))){ echo "&nbsp;&nbsp;置顶天数余额：<b><font color=\"#990000\">".$curuser->info['freezds'] ."</font></b> 天";}
                ?>
                </p>
        	</div>
        <div style=" width:420px; float:right;">
            <?
			if(!empty($curuser->info['grouptype14'])){
				#$memcert = $db->result_one("SELECT count(*) FROM {$tblprefix}mcerts WHERE mid='$memberid' AND checkdate<>0");
				$query=$db->query("SELECT * FROM {$tblprefix}mcerts WHERE mid='$memberid' AND checkdate=0");
				$sqstr = '';
				while($row = $db->fetch_array($query)){
					$sqstr .= $mctypes[$row['mctid']]['cname'].' ';
				}
				if($sqstr && $mcertname){
					echo "您当前的认证身份是：<strong> $mcertname</strong>,您现在申请的是：<strong> $sqstr</strong>正在审核中！";
				}elseif($sqstr){
					echo "您的<strong> $sqstr</strong>正在审核中！";
				}elseif($mcertname){
					echo "您当前的认证身份是： <strong> $mcertname</strong>会员。";
				}else{
					$mchid = $curuser->info['mchid'];
					$mfields = cls_cache::Read('mfields',$mchid);
					foreach($mctypes as $k => $v){
						//用来判断后台是否开启会员认证：
						if($v['available'] && in_array($mchid,explode(',',$v['mchids'])) && isset($mfields[$v['field']])){
							echo "您的信息<strong>尚未通过验证</strong>，不能发布房源信息！<a href=\"?action=mcerts\"><strong>马上完善资料、提交认证</strong></a>吧！<br/>";
							break;
						}
					}

				}
				if($curuser->info['grouptype14'] == 8 && !empty($curuser->info['grouptype14date'])){
					echo "<br/>您的<strong>高级经纪人</strong>权限剩余<font class=\"red\"><b>".ceil(($curuser->info['grouptype14date']-$timestamp)/86400)."</b></font>天，如要延长权限有效期，请进行>>><a style=\"color:red;font-weight:bold;\" href=\"?action=gaoji\">升级</a>";
				}
			}elseif(!empty($curuser->info['grouptype13'])){
				echo "您是<strong>个人会员</strong>，您所发布的信息须经过管理员的审核，注册经纪人无须管理员审核。";
			}elseif(!empty($curuser->info['grouptype15'])){
				echo "您是<strong>经纪公司会员</strong>，您可管理：<a href='?action=chushouarchives&ispid4=1'>经纪人二手房源</a> 和 <a href='?action=chuzuarchives&ispid4=1'>经纪人出租房源</a>。";
			}elseif(!empty($curuser->info['grouptype31'])){
				if($curuser->info['grouptype31'] == 102){
					echo "您是<strong>VIP公司</strong>会员享有优先排名，拥有更大的发布数量。";
					if(!empty($curuser->info['grouptype31date'])) 
						echo "<br/>您的<strong>VIP公司</strong>权限剩余<font class=\"red\"><b>".ceil(($curuser->info['grouptype31date']-$timestamp)/86400)."</b></font>天，如要延长权限有效期，请进行>>><a style=\"color:red;font-weight:bold;\" href=\"?action=vip&type=vipgs\">升级</a>";
				}else{
					echo "您是<strong>普通公司</strong>会员，发布数量与排名将有限制。<br/>升级<strong>VIP公司</strong>会员快速提高排名，增大发布数量。<a style=\"color:red;font-weight:bold;\" href=\"?action=vip&type=vipgs\">马上升级</a>";
				}
			}elseif(!empty($curuser->info['grouptype32'])){				
				if($curuser->info['grouptype32'] == 104){
					echo "您是<strong>VIP商家</strong>会员享有优先排名，拥有更大的发布数量。";
					if(!empty($curuser->info['grouptype32date']))
						echo "<br/>您的<strong>VIP商家</strong>权限剩余<font class=\"red\"><b>".ceil(($curuser->info['grouptype32date']-$timestamp)/86400)."</b></font>天，如要延长权限有效期，请进行>>><a style=\"color:red;font-weight:bold;\" href=\"?action=vip&type=vipsj\">升级</a>";
				}else{
					echo "您是<strong>普通商家</strong>会员，发布数量与排名将有限制。<br/>升级<strong>VIP商家</strong>会员快速提高排名，增大发布数量。<a style=\"color:red;font-weight:bold;\" href=\"?action=vip&type=vipsj\">马上升级</a>";
				}
			}
			if(!empty($curuser->info['grouptype34'])){	
				echo "<br/>您是<strong>问答专家</strong>，可点此 <a href='?action=zhuanjia_manage'>修改专家资料！</a>";
			}else{
				$memid = $curuser->info['mid'];
				$commu = cls_cache::Read('commu',42); 
				$fval = $db->result_one("SELECT mid FROM {$tblprefix}$commu[tbl] WHERE mid='$memid'"); 
				if($fval){
					echo "<br/>您已经<strong>申请了问答专家</strong>但还未审核，可点此 <a href='?action=zhuanjia_manage'>修改资料！</a>";
				}else{
					echo "<br/>您还不是<strong>问答专家</strong>，可点此 <a href='?action=zhuanjia_manage'>申请专家！</a>";
				}
			}
			?>

            </div>
            <? if(!empty($curuser->info['grouptype14'])){ ?>
            <div class="btn_a">
            	<? if($curuser->pmbypmid('16')){ ?>
                <a href="?action=chushouadd"><span>发布二手房</span></a>
                <a href="?action=chushouarchives"><span>管理二手房</span></a>
                <a href="?action=chuzuadd"><span>发布出租房</span></a>
                <a href="?action=chuzuarchives"><span>管理出租房</span></a>
                <? }if(empty($curuser->info['grouptype14'])){ ?><a href="?action=tuijianarchives"><span>设置店铺推荐位</span></a><? } ?>
            </div>
            <? } ?>
        </div>
        <!--日常管理-->		
        <ul class="cor_box">
            <li class="cor tl"></li>
            <li class="cor tr"></li>
            <li class="con">
                <ul>
                    <li class="box_head"><i class="ico_manage4">&nbsp;</i>日常管理</li>
                    <li class="box_body">
					<? if($curuser->info['grouptype14'] || $curuser->info['grouptype13']){ ?>
                    	<? if($curuser->pmbypmid('16')){ ?>
                            <ul>
                                <li class="cap"><strong>房源管理</strong></li>
                                <li class="infos"><span>出租房源：</span><span>已发布<em><?=$sendchuzunum?></em>套</span> <span>上架<em><?=$chuzunum?></em>套</span> <span>下架<em><?=$sendchuzunum-$chuzunum?></em>套</span> </li>
                                <li>
                                	<a href="?action=chuzuadd">发布出租房&gt;&gt;</a>
                                	<a href="?action=chuzuarchives">管理出租房&gt;&gt;</a>
                                </li>
                                <li class="infos"><span>二手房源：</span><span>已发布<em><?=$sendchushounum?></em>套</span> <span>上架<em><?=$chushounum?></em>套</span> <span>下架<em><?=$sendchushounum-$chushounum?></em>套</span> </li>
                                <li>
                                	<a href="?action=chushouadd">发布二手房&gt;&gt;</a>
                                	<a href="?action=chushouarchives">管理二手房&gt;&gt;</a> </li>
                            </ul>
                        <?
                        } 
						if($curuser->pmbypmid('14')){ 
                            $sendqiuzunum = cls_DbOther::ArcLimitCount(9, ''); 
                            $sendqiugounum = cls_DbOther::ArcLimitCount(10, ''); 
                            
                            $qiuzunum = cls_DbOther::ArcLimitCount(9, 'enddate', 'valid'); 
                            $qiugounum = cls_DbOther::ArcLimitCount(10, 'enddate', 'valid'); 

    						?>
                            <ul>
                                <li class="cap"><strong>需求管理</strong></li>
                                <li class="infos"><span>求租信息：</span><span>已发布<em><?=$sendqiuzunum?></em>套</span> <span>求租中<em><?=$qiuzunum?></em>套</span> <span>下架<em><?=$sendqiuzunum-$qiuzunum?></em>套</span> </li>
                                <li>
                                	<a href="?action=xuqiuarchive&chid=9">发布求租&gt;&gt;</a>
                                	<a href="?action=xuqiuarchives&chid=9">管理求租&gt;&gt;</a>
                                </li>
                                <li class="infos"><span>求购信息：</span><span>已发布<em><?=$sendqiugounum?></em>套</span> <span>求购中<em><?=$qiugounum?></em>套</span> <span>下架<em><?=$sendqiugounum-$qiugounum?></em>套</span> </li>
                                <li>
                                	<a href="?action=xuqiuarchive&chid=10">发布求购&gt;&gt;</a>
                                	<a href="?action=xuqiuarchives&chid=10">管理求购&gt;&gt;</a> </li>
                            </ul>
    						<?
						}
					}   
                    if($curuser->info['grouptype14']) { ?>
                		<? if($curuser->pmbypmid('17')){ ?>
    						<ul>
                                <li class="cap"><strong>店铺管理</strong></li>
                                <li class="infos"><span>二手房推荐：</span> 
                                    <span>已推荐<em><?=$tuijiannums_cs?></em>个</span> 
                                </li>
                                <li class="infos"><span>出租房推荐：</span> 
                                    <span>已推荐<em><?=$tuijiannums_cz?></em>个</span> 
                                </li>
                                <li><a href="?action=tuijianarchives">设置推荐信息&gt;&gt;</a>
                                </li>
                                <li class="infos"><span>店铺留言：</span> <span>您有<em><?=$liuyancount?></em>条新留言</span></li>
                                <li><a href="?action=liuyans">查看留言&gt;&gt;</a>
                                </li>
                            </ul>
					    <? 
						}
					}  
                    if(!empty($curuser->info['grouptype31'])) {
                        $newsChid = 104; $designChid = 101; $designCaseChid = 102;
                        $newsValidNum = cls_DbOther::ArcLimitCount($newsChid, 'enddate', 'valid'); 
                        $newsTotalNum = cls_DbOther::ArcLimitCount($newsChid, ''); 
                        $designValidNum = cls_DbOther::ArcLimitCount($designChid, 'enddate', 'valid'); 
                        $designTotalNum = cls_DbOther::ArcLimitCount($designChid, ''); 
                        $designCaseValidNum = cls_DbOther::ArcLimitCount($designCaseChid, 'enddate', 'valid'); 
                        $designCaseTotalNum = cls_DbOther::ArcLimitCount($designCaseChid, ''); 
                        
                        $cuid = 31;
                        $commu_yezhupl = cls_cache::Read('commu', $cuid);
                        $yezhuplNum = $db->result_one("SELECT count(*) FROM {$tblprefix}$commu_yezhupl[tbl] WHERE tomid='$memberid'");
                        
                        empty($yezhuplNum) && $yezhuplNum = 0;
						?>
                        <? if($curuser->pmbypmid('114')){?>
						<ul>
                            <li class="cap"><strong>动态管理</strong></li>
                            <li class="infos"><span>已生效 <em><?=$newsValidNum?></em>/<strong><?=$newsTotalNum?></strong></span></li>
                            <li>
                            	<a href="?action=designNews_a&chid=104&caid=512">发布动态&gt;&gt;</a>
                            	<a href="?action=designNews_s">管理动态&gt;&gt;</a>
                            </li>
                        </ul>
                        <? }
							if($curuser->pmbypmid('101')){?>
						<ul>
                            <li class="cap"><strong>设计师管理</strong></li>
                            <li class="infos"><span>已生效 <em><?=$designValidNum?></em>/<strong><?=$designTotalNum?></strong></span></li>
                            <li>
                            	<a href="?action=design_a">发布设计师&gt;&gt;</a>
                            	<a href="?action=design_s">管理设计师&gt;&gt;</a>
                            </li>
                        </ul>
						<ul>
                            <li class="cap"><strong>案例管理</strong></li>
                            <li class="infos"><span>已生效 <em><?=$designCaseValidNum?></em>/<strong><?=$designCaseTotalNum?></strong></span></li>
                            <li>
                            	<a href="?action=designCase_a">发布案例&gt;&gt;</a>
                            	<a href="?action=designCase_s">管理案例&gt;&gt;</a>
                            </li>
                        </ul>
						<ul>
                            <li class="cap"><strong>店铺管理</strong></li>
                            <li class="infos"><span>店铺点评：</span> <span>您有<em><?=$yezhuplNum?></em>条点评</span></li>
                            <li><a href="?action=commu_yezhupl">查看点评&gt;&gt;</a>
                            </li>
                        </ul>
						<? 
							}
					} 
                    if(!empty($curuser->info['grouptype32'])) {
                        $newsChid = 104; $goodsChid = 103;
                        $newsValidNum = cls_DbOther::ArcLimitCount($newsChid, 'enddate', 'valid'); 
                        $newsTotalNum = cls_DbOther::ArcLimitCount($newsChid, ''); 
                        $goodsValidNum = cls_DbOther::ArcLimitCount($goodsChid, 'enddate', 'valid'); 
                        $goodsTotalNum = cls_DbOther::ArcLimitCount($goodsChid, ''); 
                        
                        $cuid = 34;
                        $commu_brandsjly = cls_cache::Read('commu', $cuid);
                        $brandsjlyNum = $db->result_one("SELECT count(*) FROM {$tblprefix}$commu_brandsjly[tbl] WHERE tomid='$memberid'");
                        
                        empty($brandsjlyNum) && $brandsjlyNum = 0;
						?>
                        <? if($curuser->pmbypmid('114')){?>
						<ul>
                            <li class="cap"><strong>动态管理</strong></li>
                            <li class="infos"><span>已生效 <em><?=$newsValidNum?></em>/<strong><?=$newsTotalNum?></strong></span></li>
                            <li>
                            	<a href="?action=designNews_a">发布动态&gt;&gt;</a>
                            	<a href="?action=designNews_s">管理动态&gt;&gt;</a>
                            </li>
                        </ul>
                        <? }
							if($curuser->pmbypmid('103')){?>
						<ul>
                            <li class="cap"><strong>商品管理</strong></li>
                            <li class="infos"><span>已生效 <em><?=$goodsValidNum?></em>/<strong><?=$goodsTotalNum?></strong></span></li>
                            <li>
                            	<a href="?action=designGoods_a">发布商品&gt;&gt;</a>
                            	<a href="?action=designGoods_s">管理商品&gt;&gt;</a>
                            </li>
                        </ul>
						<ul>
                            <li class="cap"><strong>店铺管理</strong></li>
                            <li class="infos"><span>店铺留言：</span> <span>您有<em><?=$brandsjlyNum?></em>条留言</span></li>
                            <li><a href="?action=commu_brandsjly">查看留言&gt;&gt;</a>
                            </li>
                        </ul>
						<?
						}
					} 
					if($curuser->info['grouptype15']) { // 经纪公司
                        $idstr = '';
                        $namesql = "select m.mid,m.mname FROM {$tblprefix}members m WHERE m.mchid=2 AND pid4='$memberid' AND incheck4=1";
                        $query = $db->query($namesql);
                        while($row = $db->fetch_array($query)){
                        	$idstr .= ','.$row['mid'];
                        }
                        $idstr = empty($idstr) ? "0" : substr($idstr,1); 
                        $cnt_ch2 = 0;
                        $cnt_ch3 = 0;
                        if(!empty($idstr)){
                        	$cnt_ch2 = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(2)." WHERE mid IN($idstr) ");
                        	$cnt_ch3 = $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl(3)." WHERE mid IN($idstr) ");	
                        	empty($cnt_ch2) && $cnt_ch2 = 0;
                        	empty($cnt_ch3) && $cnt_ch3 = 0;
                        }
                        $cnt_ch104 = cls_DbOther::ArcLimitCount(104, ''); 
						
						?>
						<ul>
                            <li class="cap"><strong>房源管理</strong></li>
                            <li class="infos">
                            <span><a href='?action=chushouarchives&ispid4=1'>经纪人二手房源</a>(<em><?php echo $cnt_ch3; ?></em>)</span> 
                            <span><a href='?action=chuzuarchives&ispid4=1'>经纪人出租房源</a>(<em><?php echo $cnt_ch2; ?></em>)</span>
                            </li>
                            <li>
                            	<a href="?action=commu_yixiang">房源意向管理&gt;&gt;</a>
                            </li>
                        </ul>
						<ul>
                            <li class="cap"><strong>店铺管理</strong></li>
                            <li class="infos">
                            <span><a href="?action=designNews_s">动态管理</a>(<em><?php echo $cnt_ch104; ?></em>)</span> 
                            <span><a href="?action=designNews_a&chid=104&caid=554">发布&gt;&gt;</a></span>
                            </li>
                            <li>
                            	<a href='?action=agents&incheck=1'>经纪人管理</a>
                            </li>
                        </ul>
						<?
						  
					}
						
					if($curuser->info['grouptype33']) { // 售楼公司
                        $sql_ids = "SELECT loupan FROM {$tblprefix}members_13 WHERE mid='$memberid'"; 
                        $loupanids = $db->result_one($sql_ids); if($loupanids) $loupanids = substr($loupanids,1); 
                        if(empty($loupanids)) $loupanids = 0;
                        
                        $cu_yx = $db->result_one("SELECT count(*) FROM {$tblprefix}commu_yx WHERE aid IN(SELECT aid FROM {$tblprefix}".atbl(4)." WHERE aid IN($loupanids))");
                        $cu_dp = $db->result_one("SELECT count(*) FROM {$tblprefix}commu_zixun WHERE aid IN(SELECT aid FROM {$tblprefix}".atbl(4)." WHERE aid IN($loupanids))");
                        empty($cu_yx) && $cu_yx = 0;
                        empty($cu_dp) && $cu_dp = 0;
						
						?>
						<ul>
                            <li class="cap"><strong>楼盘管理</strong></li>
                            <li class="infos">
                            <span><a href='?action=louyx'>楼盘意向</a>(<em><?php echo $cu_yx; ?></em>)</span> 
                            <span><a href='?action=loupan_pinlun'>楼盘点评</a>(<em><?php echo $cu_dp; ?></em>)</span>
                            </li>
                            <li>
                            	<a href="?action=loupans">管理楼盘&gt;&gt;</a>
                            </li>
                        </ul>
						<?
						  
					}
						
                    $qa_ch = cls_DbOther::ArcLimitCount(106, ''); 
                    
                    $qa_get = $db->result_one("SELECT count(*) FROM {$tblprefix}".atbl(106)." WHERE tomid='$memberid'");
                    $qa_rep = $db->result_one("SELECT count(*) FROM {$tblprefix}commu_answers WHERE mid='$memberid'");
                    
                    empty($qa_get) && $qa_get = 0;
                    empty($qa_rep) && $qa_rep = 0;
						
						?>
                        
                        <ul>
                            <li class="cap"><strong>房产问答</strong></li>
                            <li class="infos">  
                            <span><a href="?action=wenda_manage&actext=qget">给我的问题</a>：<em><?php echo $qa_get; ?></em>个</span> 
                            <span><a href="?action=wenda_manage&actext=qout">我的提问</a>：<em><?php echo $qa_ch; ?></em>个</span> 
                            <span><a href="?action=wenda_manage&actext=answer">我的回答</a>：<em><?php echo $qa_rep; ?></em>个</span>
                            </li>
                            <li>
                            	<a href="?action=zhuanjia_manage">问答专家申请/修改资料&gt;&gt;</a>
                            </li>
                        </ul>
                        
                    </li>
                </ul>
            </li>
            <li class="cor bl"></li>
            <li class="cor br"></li>
        </ul>		
        <!--我的推广活动-->
    </div>
    <!--页面右侧-->
    <style type="text/css">.box{margin-bottom: 10px;}.box .box_head a{background: none;}</style>
    <div class="r_col_r">
        <div class="box">
            <div class="box_head">
                <a href="{c$cnode [tclass=cnode/] [listby=ca/] [casource=574/]}{indexurl}{/c$cnode}" target="_blank">更多&gt;&gt;</a><i class="ico_infos">&nbsp;</i>网站公告</div>
            <ul>
                {c$archives [tclass=archives/] [chids=109/] [chsource=2/] [casource=1/] [caids=574/]}
				<li><a href="{arcurl}" target="_blank">{c$text [tclass=text/] [tname=subject/] [trim=24/] [ellip=.../] [color=color/]}{/c$text}</a></li>
                {/c$archives}
            </ul>
        </div>
        <div class="box dongtai">
            <div class="box_head">
                <a href="{c$cnode [tclass=cnode/] [listby=ca/] [casource=575/]}{indexurl}{/c$cnode}" target="_blank">更多&gt;&gt;</a><i class="ico_share">&nbsp;</i>新手导航</div>
            <ul class="graywhite_t blue_a">
                {c$archives [tclass=archives/] [chids=109/] [chsource=2/] [casource=1/] [caids=575/]}
				<li><a href="{arcurl}" target="_blank">{c$text [tclass=text/] [tname=subject/] [trim=24/] [ellip=.../] [color=color/]}{/c$text}</a></li>
                {/c$archives}
            </ul>
            <div align="center">
                <span id="lblDisplayListMsg"></span></div>
        </div>
        <?php 
        if (!empty($curuser->info['grouptype33'])) {
         ?>
            <div class="box dongtai">
                <div class="box_head">
                    <a href="{c$cnode [tclass=cnode/] [listby=ca/] [casource=2/]}{indexurl}{/c$cnode}" target="_blank">更多&gt;&gt;</a><i class="ico_share">&nbsp;</i>楼盘动态</div>
                <ul class="graywhite_t blue_a">
                    <?=$showxzl?>
                </ul>
                <div align="center">
                    <span id="lblDisplayListMsg"></span></div>
            </div>
        <?php 
	    } 
		if (!empty($curuser->info['grouptype14'])||!empty($curuser->info['grouptype15'])) {
        ?>
            <div class="box dongtai">
                <div class="box_head">
                    <a href="{c$cnode [tclass=cnode/] [listby=ca/] [casource=565/]}{indexurl}{/c$cnode}" target="_blank">更多&gt;&gt;</a><i class="ico_share">&nbsp;</i>经纪人帮助</div>
                <ul class="graywhite_t blue_a">{c$archives [tclass=archives/] [chids=109/] [chsource=2/] [caidson=1/] [casource=1/] [caids=565/] [validperiod=1/]}                <li><a href="{arcurl}" target="_blank">{c$text [tclass=text/] [tname=subject/] [trim=24/] [ellip=.../] [color=color/]}{/c$text}</a></li>
                    {/c$archives}
                </ul>
                <div align="center">
                    <span id="lblDisplayListMsg"></span></div>
            </div>
        <?php 
    	} 
		if (!empty($curuser->info['grouptype31'])) {
        ?>
            <div class="box dongtai">
                <div class="box_head">
                    <a href="{c$cnode [tclass=cnode/] [listby=ca/] [casource=579/]}{indexurl}{/c$cnode}" target="_blank">更多&gt;&gt;</a><i class="ico_share">&nbsp;</i>装修公司帮助</div>
                <ul class="graywhite_t blue_a">
                    {c$archives [tclass=archives/] [chids=109/] [chsource=2/] [casource=1/] [caids=579/]}
    				<li><a href="{arcurl}" target="_blank">{c$text [tclass=text/] [tname=subject/] [trim=24/] [ellip=.../] [color=color/]}{/c$text}</a></li>
                    {/c$archives}
                </ul>
                <div align="center">
                    <span id="lblDisplayListMsg"></span></div>
            </div>
        <?php 
    	} 
		if (!empty($curuser->info['grouptype32'])) {
        ?>
            <div class="box dongtai">
                <div class="box_head">
                    <a href="{c$cnode [tclass=cnode/] [listby=ca/] [casource=580/]}{indexurl}{/c$cnode}" target="_blank">更多&gt;&gt;</a><i class="ico_share">&nbsp;</i>品牌商家帮助</div>
                <ul class="graywhite_t blue_a">
                    {c$archives [tclass=archives/] [chids=109/] [chsource=2/] [casource=1/] [caids=580/]}
    				<li><a href="{arcurl}" target="_blank">{c$text [tclass=text/] [tname=subject/] [trim=24/] [ellip=.../] [color=color/]}{/c$text}</a></li>
                    {/c$archives}
                </ul>
                <div align="center">
                    <span id="lblDisplayListMsg"></span></div>
            </div>
        <?php 
    	} 
        ?>
    </div>