<?php
if(!empty($extend)){
    _08_FilesystemFile::filterFileParam($extend);
	$extend_str = "&extend=$extend";
	if(@is_file($ex = dirname(__FILE__)."/extends/$extend.php")){
		include($ex);
	}else mexit('指定的文件不存在。');
}else mexit('指定的操作未定义。');
