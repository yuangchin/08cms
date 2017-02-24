<?php
/**
 * 广告标签
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */

(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!empty($mtagnew)) cls_Array::array_stripslashes($mtagnew);//不存数据库，将转义取消
if(!submitcheck('bsubmit')){
    $mtag = cls_cache::Read($ttype,$mtagnew['ename'],'');
    tabheader("广告模板：>> <a href=\"?entry=$entry&extend=$extend&action=recache&src_type=other&fcaid=$fcaid\">更新缓存</a>",'adv_tpl',"?entry=$entry&extend=$extend&action=$action&src_type=other&fcaid=$fcaid",2,1,1);
	trbasic('广告ID','', $fcaid, '');    
    trbasic('启用状态','adv[checked]', $advertising['checked'],'radio',array('guide' => '如果不启用则不会在前台页面显示。'));
	templatebox('广告内模板','mtagnew[template]',empty($mtag['template']) ? '' : $mtag['template'],10,110);
    echo '<script type="text/javascript">
              if(parent.document.getElementById("operateitem").innerHTML != "") {
                  document.getElementById(\'mtagnew[template]\').parentNode.parentNode.align="center";
              }
          </script>';
	trbasic('广告返回的数组名称','mtagnew[setting][val]',empty($mtag['setting']['val']) ? 'v' : $mtag['setting']['val'],'text',array('guide' => '系统默认为v，当广告存在嵌套时，该参数需设为不同于上下级广告。<br> 在当前广告内可用{aaa}或{$v[aaa]}调用信息，跨广告调用信息只能使用{$v[aaa]}。'));
	trbasic('列表中显示多少条内容','mtagnew[setting][limits]',empty($mtag['setting']['limits']) ? 10 : $mtag['setting']['limits']);
	trbasic('从第几条记录开始显示','mtagnew[setting][startno]',empty($mtag['setting']['startno']) ? '' : $mtag['setting']['startno'],'text',array('guide'=>'设置按当前设置的第几条记录开始，默认为0。'));
#	echo "<script>function setdisabled(showid,hideid){var showobj=\$id(showid),hideobj=\$id(hideid),sinput=showobj.getElementsByTagName('input');hinput=hideobj.getElementsByTagName('input');showobj.style.display='';hideobj.style.display='none';for(var i=0;i<sinput.length;i++){sinput[i].disabled=false}for(var i=0;i<hinput.length;i++){hinput[i].disabled=true}}</script>";
#	echo "<script>window.onload = function(){setdisabled(".(empty($mtag['setting']['ids'])?"'ids_mod1','ids_mod2'":"'ids_mod2','ids_mod1'").");}</script>";
	$addstr = "&nbsp; >><a href=\"?entry=liststr&action=adv_farchives&typeid=$fcaid\" target=\"_blank\">生成</a>";
	echo '</div>';

	echo "<div id=\"ids_mod1\" style=\"display:".(empty($mtag['setting']['ids']) ? '' : 'none')."\">";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isfunc]\" name=\"mtagnew[setting][isfunc]\"".(empty($mtag['setting']['isfunc']) ? '' : ' checked').">字串来自函数";
	$addstr .= "<br><input class=\"checkbox\" type=\"checkbox\" id=\"mtagnew[setting][isall]\" name=\"mtagnew[setting][isall]\"".(empty($mtag['setting']['isall']) ? '' : ' checked').">完整查询字串";
	tabfooter();

    // 高级配置
    echo '<div class="conlist1"><span style="float:left;">高级配置：</span><div style="float:left; width:18px; height:18px;overflow: hidden;margin-top: 6px;" onclick="toggers(this);" class="add2" title="点击我展开！"></div></div>';
    echo '<div id="adv_config" style="display:none;"><table width="100%" border="0" cellpadding="0" cellspacing="0" class=" tb tb2 bdbot">';
    trbasic('允许传入广告位变量','adv[params]',$advertising['params'],'text',array('w'=>'50','guide' => '1. 单个参数格式： "参数名":"参数值"，多个参数用英文逗号隔开<br />2.	参数值必须是数值型，可以是系统原始标识，也可以是固定数值'));

    $addstr = "&nbsp; >><a href=\"?entry=liststr&action=adv_farchives&typeid=$fcaid\" target=\"_blank\">生成</a>";
    trbasic('筛选查询字串'.$addstr,'mtagnew[setting][wherestr]',empty($mtag['setting']['wherestr']) ? '' : $mtag['setting']['wherestr'],'textarea',array('guide' => '函数格式：函数名(\'参数1\',\'参数2\')。完整查询字串包含select、from、where,不要含order及limit。'));
    tabfooter();
    echo <<<EOT
        </div>
        <style type="text/css">
            .add2 { background:url(images/admina/add2.gif) no-repeat -5px -5px; }
            .sub2 { background:url(images/admina/sub2.gif) no-repeat -5px -5px; }
        </style>
        <script type="text/javascript">
            function toggers(obj)
            {
                if(obj.className == 'add2') {
                    obj.className = 'sub2';
                    document.getElementById('adv_config').style.display = '';
                } else {
                    obj.className = 'add2';
                    document.getElementById('adv_config').style.display = 'none';
                }
            }
        </script>
EOT;
    tabfooter('bsubmit','提交');
}else{
	if(empty($mtagnew['template'])) mtag_error('请输入广告模板');    
    $mtagnew['setting']['casource'] = $fcaid;
	$mtagnew['setting']['startno'] = trim($mtagnew['setting']['startno']);
    $mtagnew['setting']['validperiod'] = '1';
	$mtagnew['setting']['orderstr'] = 'a.vieworder DESC ';
#    $mtagnew['setting']['js'] = 1;
	$mtagnew['setting']['limits'] = empty($mtagnew['setting']['limits']) ? 10 : max(0,intval($mtagnew['setting']['limits']));
	$mtagnew['setting']['alimits'] = (isset($mtagnew['setting']['alimits']) ? intval($mtagnew['setting']['alimits']) : 0);
	$mtagnew['setting']['length'] = (isset($mtagnew['setting']['length']) ? max(0,intval($mtagnew['setting']['length'])) : '');
	$mtagnew['setting']['wherestr'] = empty($mtagnew['setting']['wherestr']) ? '' : trim($mtagnew['setting']['wherestr']);
	$mtagnew['setting']['isfunc'] = empty($mtagnew['setting']['isfunc']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['isall'] = empty($mtagnew['setting']['isall']) || empty($mtagnew['setting']['wherestr']) ? 0 : 1;
	$mtagnew['setting']['ttl'] = (isset($mtagnew['setting']['ttl']) ? intval($mtagnew['setting']['ttl']) : 0);
	$mtagnew['setting']['forceindex'] = (isset($mtagnew['setting']['forceindex']) ? trim($mtagnew['setting']['forceindex']) : '');
	if(empty($mtagnew['setting']['forceindex'])) unset($mtagnew['setting']['forceindex']);
	$idvars = array('isfunc','isall',);
	foreach($idvars as $k) unset($mtagnew['setting'][$k]);
}