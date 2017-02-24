<?php
$aguide = <<<EOT
    <div style="margin:12px 0 0 3px;"><b>标识还原的作用</b><br />
	用于将模板中的标识代码(非封装标识)或封装标识还原为该标识的参数设置界面，方便修改模板中的标识参数。<br />
    <b>标识还原的用法</b><br />
    将非封装标识的整块代码复制到输入框，点击开始还原按钮即可。<br />
    <b>标识还原的示例</b><br />
    非封装标识：{c\$zturl [tclass=cnode/] [listby=ca/] [casource=107/]}{indexurl}{/c\$zturl}【注意：必须是完整标识，意思就是{/c\$zturl}闭合标识不能漏掉。】<br />
    封装标识：{c\$zturl}<br />
    </div>
EOT;
