<?php
/**
 * 信息提示模板
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

if ( defined('M_ADMIN') ) {  ########################################  后台
?>
<br /><br /><br /><br /><br /><br />
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="500" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF" class="<?php echo $class;?>">
                <tr><td><div class="conlist1 bdbot fB">提示信息</div></td></tr>
                <tr height="150"><td class="txtleft lineheight200" style="padding-left:20px;"><?php echo $str;?></td></tr>
            </table>
        </td>
    </tr>
</table><br /><br /><br /><br /><br /><br />
<?php
    afooter();
    mexit();
} else if ( defined('M_MCENTER') ) {   ########################################  会员中心
    $infloat && print('<div style="position:relative;">');
?>
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td align="center">
    <table border="0" cellpadding="0" cellspacing="1" align="center" class="<?php echo $class;?>">
    	<tr class="header"><td><b>提示信息</b></td></tr><tr><td height="120" align="center" valign="middle"><?php echo $str;?></td></tr></table>
        </td></tr></table>
<div class="blank9"></div></div>
<?php
    $infloat && print('</div>');
        
	if($no_mcfooter){
		mexit('</body></html>');
	}else mcfooter();
} else {   ########################################  其它页面
	$cms_top = cls_env::mconfig('cms_top');
    if(cls_tpl::SpecialTplname('message',defined('IN_MOBILE'))){
		$temparr = array('message' => $str); 
    	$msg = cls_tpl::SpecialHtml('message',$temparr,defined('IN_MOBILE'));
        $msg = $msg ? $msg : $str;
        $msg = str_ireplace('<head>', '<head><script>var originDomain = originDomain || document.domain; document.domain = "'.$cms_top.'" || document.domain;</script><meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>', $msg);
    	mexit($msg);
    }
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">
    <head><meta http-equiv="Content-Type" content="text/html; charset=<?php echo $mcharset;?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <script>var originDomain = originDomain || document.domain; document.domain = '<?php echo $cms_top;?>' || document.domain;</script>
	</head><body>
    <table width="98%" border="0" cellpadding="0" cellspacing="0"><tr><td align="center"><?php echo $str;?></td></tr></table>
    </body></html>
<?php
    mexit();
}