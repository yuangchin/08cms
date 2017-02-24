<?php
$aguide = '<ul>
	<li>请注意这里是MySQL的正则语法，非Perl兼容正则表达式。 </li>
	<li>注：此操作极为危险，请小心使用，使用前请先备份好数据！ </li>
	<li>正则替换示例：</li>
	<ul>
		<li>修正附件地址替换：<font color="red">(src\\s*=\\s*[\\\'|&quot;]?)/08cms/</font>。<br />
			/08cms/为网站配置里的CMS系统URL注：这里是用在archives_xx系列表中的content字段<br />
			下面的替换文本框相应地用：<font color="red">\\1&lt;!cmsurl /&gt;</font>。<br />
			\\1为对刚才的src\\s*=\\s*[\\\'|&quot;]?引用，&lt;!cmsurl /&gt;为系统内定的cmsurl修正参数，相应的ftp为&lt;!ftpurl /&gt; </li>
		<li>反向引用使用\\0-\\9的语法而不是通常的$0-$9。\\0为找到的字串引用，\\1-\\9圆括号匹配引用</li>
	</ul>
</ul>';
?>