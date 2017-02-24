<?php
defined('M_MCENTER') || exit('No Permission');

isset($action) || $action = '';
# 加载CK插件
if(in_array($action, array('chushouadd', 'chuzuadd')))
{
    if ( empty($ck_plugins_enable) )
    {
        $ck_ = new _08House_Archive();
        // 定义CK要开启的插件，注：该值与CK插件名称相同，多个用逗号分隔，如果升级该脚本时请继承下去
        $ck_plugins_enable = "{$ck_->__ck_plot_pigure},{$ck_->__ck_size_chart}";
        unset($ck_);
    }
}

u_memberstat($memberid,30);

if(!empty($infloat)){ # 在浮动窗口中，不要外框架效果。
	if(in_array(@$action, array('chushouadd', 'chuzuadd')))
	{
		cls_env::SetG('ck_plugins_enable',$ck_plugins_enable);
	}
	/*
	这个只在编辑时，且关联了小区 才可用，
	添加是，房源资料都不存在，也就不会关联小区，是不可用的，
	之前添加是，特别屏蔽的；
	后续看情况，再改善。
	*/
	_header();
}else{ # 完全展示，包含外框架
	$_Title_Adminm = "会员管理中心 - $cmsname";
	if(!empty($curuser->info['atrusteeship'])) $_Title_Adminm .= " [代管人：{$curuser->info['atrusteeship']['from_mname']}]";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$mcharset?>" />
<title><?=$_Title_Adminm?></title>
<meta content="IE=EmulateIE7" http-equiv="X-UA-Compatible"/>
<link type="text/css" rel="stylesheet" href="<?=$cms_abs?>images/common/validator.css" />
<link href="<?=MC_ROOTURL?>css/default.css" rel="stylesheet" type="text/css" />
<link href="<?=MC_ROOTURL?>css/pub_house.css" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" href="<?=$cmsurl?>images/common/window.css" />
</head>
<body>
<script language="javascript" type="text/javascript">
var CMS_ABS = "<?=$cms_abs?>" <?=empty($cmsurl) ? '' : ', CMS_URL = "'.$cmsurl.'"'?>, MC_ROOTURL = "<?=MC_ROOTURL?>"<?=empty($aallowfloatwin) ? ', eisable_floatwin = 1' : ''?><?=empty($aeisablepinyin) ? '' : ', eisable_pinyin = 1'?>, charset = '<?=$mcharset?>', tipm_ckkey = '<?=$ckpre?>mTips_List';
var originDomain = originDomain || document.domain; document.domain = '<?php echo $cms_top;?>' || document.domain;
</script>
<?php 
    cls_phpToJavascript::loadJQuery();
    if ( _08_Browser::getInstance()->isMobile() )
    {
?>
<link type="text/css" rel="stylesheet" href="<?=$cmsurl?>images/common/jqueryui/css/custom-theme/smoothness/jquery-ui-1.10.2.min.css" />
<script type="text/javascript" src="<?=$cmsurl?>images/common/jqueryui/js/jquery-ui-1.11.0.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>images/common/jqueryui/js/jquery.ui.touch-punch.min.js"></script>
<?php } ?>
<script type="text/javascript" src="<?=$cmsurl?>images/common/layer/layer.min.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/_08cms.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/common.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/adminm.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/floatwin.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/setlist.js"></script>
<!-- ueditor -->
<script type="text/javascript" src="<?=$cmsurl?>static/ueditor1_4_3/ueditor.config.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>static/ueditor1_4_3/ueditor.all.min.js"> </script>
<script type="text/javascript" src="<?=$cmsurl?>static/ueditor1_4_3/lang/zh-cn/zh-cn.js"></script>
<!-- ueditor end -->
<script type="text/javascript" src="<?=$cmsurl?>include/js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="<?=$cmsurl?>include/js/validator.js"></script>
<script type="text/javascript" src="<?=MC_ROOTURL?>js/Default.js"></script>
<?php
$usergroupstr = '';
$grouptypes = cls_cache::Read('grouptypes');
foreach($grouptypes as $k => $v){
	if($curuser->info['grouptype'.$k]){
		$usergroups = cls_cache::Read('usergroups',$k);
		$usergroupstr .=  '<span>(<em>'.$usergroups[$curuser->info['grouptype'.$k]]['cname'].'</em>)</span>';
	}
}
$mlogostyle = empty($mcenterlogo) ? '' : 'style="background:url('.view_checkurl($mcenterlogo).') no-repeat;"';
?>
    <div class="header">
        <div class="header_con">
            <div class="logo" <?=$mlogostyle?>>
                <div><?=$hostname.$usergroupstr?></div>
            </div>
            <ul class="links black_a">
                <li><em>欢迎您，<span id="spanAgentName"><?=$curuser->info['mname']?></span></em>| </li>
                <li><a href="<?=$cms_abs?>login.php?action=logout">退出</a>|</li>
                <?php if(!empty($curuser->info['atrusteeship'])) { ?>
                <li><a href="<?=$cms_abs?>login.php?action=logout&target=atrusteeship">退出代管</a>|</li>
                <?php } ?>                
                <? if(!in_array($curuser->info['mchid'],array(1,13))){?><li><a href="{c$diluurl [tclass=member/] [id=-1/]}{mspacehome}{/c$diluurl}" target="_blank">商铺空间</a>|</li>
				<? }?>
                <li><a href="<?=$cms_abs?>"><i class="ico_home">&nbsp;</i>返回首页</a>|</li>
                <li><a href="{c$help}" target="_blank"><i class="ico_help">&nbsp;</i>帮助</a></li>
                <!--li><a href="[c$fwcturl [tclass=cnode/] [listby=ca/] [casource=76/]}[indexurl}{/c$fwcturl]" target="_blank"><i class="ico_help">&nbsp;</i>帮助</a></li-->
            </ul>
			<ul class="usualurls">
<?php
$usualurls = cls_cache::Read('usualurls');
foreach($usualurls as $v){
	if($v['ismc'] && $v['available'] && $curuser->pmbypmid($v['pmid'])){
		if(($tmp=strpos($v['logo'],'#'))!==false) $v['logo']=substr($v['logo'],0,$tmp);
		echo "\n				<li><a href=\"$v[url]\"".($v['onclick'] ? " onclick=\"$v[onclick]\"" : '').($v['newwin'] ? ' target="_blank"' : '').'>'.($v['logo']?"<img src=\"$v[logo]\" width=\"28\" height=\"24\" align=\"absmiddle\" />":'')."<b>$v[title]</b></a></li>";
	}
}
?>
			</ul>

        </div>
    </div>
	<!--页面顶部 end-->
	<!--主体框架 begin-->
	<div class="main">
    	<div class="l_col black_a btn_a">
        	<ul class="cor_box" >
                <li class="cor tl"></li>
                <li class="cor tr"></li>
                <li class="con">
    				<ul class="cor_box close_box" id="menubox0">
                        <li class="box_head" onclick="javascript:redirect('<?=$cms_abs.'adminm.php'?>');"><i class="ico_home">&nbsp;</i><a href="<?=$cms_abs."adminm.php"?>"  onclick="SetClass();">我的首页</a></li>
                    </ul>
<?php
	$currarea = '';
	empty($m_cookie['ucmenu']) && $m_cookie['ucmenu'] = '';
	$i=$j=0;
	$mmnmenus = cls_cache::Read('mmnmenus');
	foreach($mmnmenus as $k => $v){
		$j++;
		$tmp=array();
		$ucmenu = 0;
		foreach($v['submenu'] as $key => $arr){
			if($curuser->pmbypmid(empty($arr['pmid']) ? 0 : $arr['pmid'])){
				$i++;
				$tmp[]="<li id=\"menu$i\" class=\"\" onclick=\"SetCookie($i)\"><a class=\"submenu".$key."\" href=\"$arr[url]\" target=\"".(empty($arr['newwin']) ? '_self' : '_blank') ."\">".$arr['title']."</a></li>";
			}
		}
		if(count($tmp)){?>
					<ul class="cor_box" id="menubox<?=$j?>">
                        <li class="box_t"></li>
                        <li class="box_head" onclick="changeBoxState(this)"><i class="ico_manages<?=$k?>"></i><a href="javascript:void(0)"
                            ><?=$v['title']?></a><b></b></li>
                        <li class="box_body">
                            <ul>
								<?=join($tmp,"\n				")?>
                            </ul>
                        </li>
                        <li class="box_b"></li>
                    </ul>

<?php		}
	}
?>

                </li>
                <li class="cor bl"></li>
                <li class="cor br"></li>
          </ul>
        </div>
        <!--左侧栏结束-->
        <div class="r_col<?=empty($action) ? '' : ' borGray'?>">
        <input id="hideMenuID" type="hidden" value="1" />
		<!--页面右侧 begin-->

			<!--当前位置 end-->
            

<?php
}

# 触发插件
_08_Plugins_Base::getInstance()->trigger('member.' . $action);
echo cls_AdminmPage::Create(array('DynamicReturn' => true));

?>
		<div class="clear"></div>
		</div>
	<!--主体框架 end-->
<?php
mcfooter();
?>