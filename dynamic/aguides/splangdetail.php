<?php
$aguide = '邮件标题不支持html，内容可支持html语法<br />
邮件功能说明：<br />
　　邮件激活地址请用：{$cms_abs}adminm.php?action=emailactivate&mid={$mid}&id={$confirmid}
形式，需要链接可以这样：<br />
　　&lt;a href=&quot;{$cms_abs}adminm.php?action=emailactivate&amp;mid={$mid}&amp;id={$confirmid}&quot;
target=&quot;_blank&quot;&gt;{$cms_abs}adminm.php?action=emailactivate&amp;mid={$mid}&amp;id={$confirmid}&lt;/a&gt;<br />
　　邮件密码找回地址：{$cms_abs}adminm.php?action=getpwd&mid={$mid}&id={$confirmid} 形式，需要链接可以这样：<br />
　　&lt;a href=&quot;{$cms_abs}adminm.php?action=getpwd&mid={$mid}&id={$confirmid}&quot;
target=&quot;_blank&quot;&gt;{$cms_abs}adminm.php?action=getpwd&mid={$mid}&id={$confirmid}&lt;/a&gt;';

$aguide = '';
?>