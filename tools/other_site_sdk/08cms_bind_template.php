<?php
/**
 * 08CMS绑定模板
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('OTHER_SITE_BIND_PATH') || die('Access forbidden!');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$mcharset?>" />
<title>帐号绑定</title>
<script type="text/javascript" src="<?=$cms_abs?>include/js/validator.js"></script>
<link type="text/css" rel="stylesheet" href="<?=$cms_abs?>images/common/validator.css" />
<style type="text/css">
<!--
body,html{color:#444;margin:0;padding:0;font:13px/24px SimSun,san-serif;}
div,h3,form,input,ul{margin:0;padding:0;}
ul,li{list-style-type:none}
.bodys { width: 500px; margin: 0 auto; }
.denglu{font-size:14px; font-weight:bold;}
.usr{ margin-bottom:5px;}
.p10{padding:10px;}
.blank0{ height:0;clear:both;display:block; font-size:1px; overflow:hidden}
.tab{position:absolute; z-index:1;}
.tab span{ padding:0 10px; height:30px; line-height:30px; color:#888; float:left; margin-left:10px; border:1px solid #134D9D; border-width:1px 1px 0 1px; display:block; cursor:pointer;}
.tab span strong{ color:#000; font-size:14px; margin-right:10px;}
.tab span.act{ background:#FFF;}
.tabct{border:1px solid #134D9D;width:458px; position:absolute;top:30px;; z-index:0; padding:0 10px; height:295px}
.dengluframe li{ height:30px; line-height:30px; margin-bottom:10px; padding-left:100px;}
.dengluframe li input{border:1px solid #134D9D; height:25px; line-height:25px;}
.dengluframe li input.regcode{ width:50px;}
.dengluframe li input.btn,.dengluframe li input.btn2{ width:80px; background:#134D9D; color:#FFF; margin-left:40px; cursor:pointer;}
.dengluframe li input.btn2{ margin-left:46px;}
 -->
</style>

<script type="text/javascript">
    window.resizeTo(600, 500); 
	var table = function(){
	 var data = {};
	 return function(item){
	  var x = item.id.match(/^(\w+\_(?:\d+\_)?)(\d+)$/);
	  if(typeof data[x[1]] == "undefined") data[x[1]] = 1;
	  document.getElementById("r_"+ x[1] + data[x[1]]).style.display = "none";
	  document.getElementById("r_"+ x[1] + x[2]).style.display = "";
	  document.getElementById(x[1] + data[x[1]]).className = "hidden";
	  document.getElementById( x[1] + x[2]).className = "act";
	  data[x[1]] = x[2];
	 };
	}();
</script>

</head>

<body>
    <div class="p10 bodys">
        <h3 class="denglu">帐号绑定</h3>
        <div class="usr">你好，欢迎来到<?=$hostname?>！</div>
        <div style="position:relative;">
            <div id="tab" class="tab">
				<span class="act" onclick="table(this)" id="a_1"><strong>非会员</strong>选择新用户名绑定</span>
                <span onclick="table(this)" id="a_2"><strong>会员</strong>使用已有用户名绑定</span>
            </div>
            <div class="blank0"></div>
            <div id="tabct">
            	<div class="tabct" id="r_a_1" >
                	<script type="text/javascript">var register_validator = _08cms.validator('bindregister');</script>
                    <form action="<?=$post_url?>&act=bindregister&infloat=<?=$infloat?>&handlekey=<?=$handlekey?>" method="post" name="bindregister" id="bindregister">
                        <ul class="dengluframe" style="margin-top:50px; margin-left:-20px;">
                            <li><span style="margin-left:12px">用户名:</span><input type="text" name="mname" value="<?=$username?>" rule="text" init="请确认用户名" warn="用户名3-15个字符" must="1" min="3" max="15" ></li>
                            <li><span style="margin-left:12px">密&nbsp;&nbsp;码:</span><input type="password" name="password" value="" autocomplete="off" init="请输入密码" rule="text" must="1" ></li>
                            <li>确认密码:<input type="password" pass="OK" init="" max="18" min="6" must="1" vid="password" rule="comp" name="password2" ></li>
                            <li><span style="margin-left:12px">邮&nbsp;&nbsp;箱:</span><input type="text" name="email" value="" autocomplete="off" init="请输入邮箱" warn="邮箱地址无效" rule="email" must="1"  ></li>
                            <li><span style="margin-left:12px">验证码:</span><?php echo _08_HTML::getCode();?><input type="hidden" value="register_regcode" name="verify" /></li>
                            <li><input type="hidden" value="<?=$useravatar?>" name="thumb" /><input class="btn2" type="submit" name="bsubmit" value="注册绑定" title="注册绑定"></li>
                        </ul>
                    </form>
                </div>
                <div class="tabct" id="r_a_2" style="display:none">
                	<script type="text/javascript">var login_validator = _08cms.validator('bindlogin');</script>
                    <form action="<?=$post_url?>&act=bindlogin&infloat=<?=$infloat?>&handlekey=<?=$handlekey?>" method="post" name="bindlogin" id="bindlogin">
                        <ul class="dengluframe" style="margin-top:70px; margin-left:-20px;">
                            <li>用 户 名:<input type="text"  name="mname" value="<?=$username?>" autocomplete="off" rule="text" init="请输入用户名"  warn="用户名3-15个字符" must="1" min="3" max="15" ></li>
                            <li>登录密码:<input type="password"  name="password" value="" autocomplete="off" init="请输入密码" rule="text" must="1" ></li>
                            <li>验 证 码:<input type="text" name="regcode" id="regcode" size="4" maxlength="4" rule="number" must="1" min="4" max="4" init="请输入图片框中的字符" rev="验证码" offset="1" value="" class="regcode"> <?php echo _08_HTML::getCode();?><input type="hidden" value="login_regcode" name="verify" /></li>
                            <li><input class="btn" type="submit" name="bsubmit" value="登录绑定" title="登录绑定"></li>
                        </ul>
                    </form>
                </div>
            </div>
         </div>
    </div>
</body>
</html>