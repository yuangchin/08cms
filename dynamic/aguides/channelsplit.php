<?php
$aguide = '
<li>分表将把文档主记录从默认表archives移到其它数据表archivesx(x为分表id)保存，文档合理分表可以提高数据负载能力及查询效率。
<li>在分表之前，所有模型的文档主记录存在archives表中，允许将一个或多个模型的文档记录拆分到指定分表。
<li>分表操作有一定的风险，在操作之前请备份archives,channels,splitbls及相关分表archivesx(x为分表id)。
<li>数据分表后会影响前台模板的文档调用相关标识的使用，及部分管理后台的管理脚本，在分表后需要作相应的调整，请谨慎操作。
<li>根据需要移动的数据量大小，可能需要等待较长时间才能执行完成，在执行完成之前，请不要关闭当前操作。
';
?>