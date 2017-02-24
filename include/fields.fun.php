<?php
!defined('M_COM') && exit('No Permission');
empty($sysparams) && $sysparams = cls_cache::cacRead('sysparams');
$datatypearr = array(
	'text' => '单行文本',
	'multitext' => '多行文本',
	'htmltext' => 'Html文本',
	'image' => '单图',
	'images' => '图集',
	'flash' => 'Flash',
	'flashs' => 'Flash集',
	'media' => '视频',
	'medias' => '视频集',
	'file' => '单点下载',
	'files' => '多点下载',
	'select' => '单项选择',
	'mselect' => '多项选择',
	'cacc' => '类目选择',
	'date' => '日期(时间戳)',
	'int' => '整数',
	'float' => '小数',
	'map' => '地图',
	'vote' => '投票',
	'texts' => '文本集',
);
$limitarr = array(
	'' => '不限格式',
	'int' => '整数',
	'number' => '数字',
	'letter' => '字母',
	'numberletter' => '字母与数字',
	'tagtype' => '字母开始的字母数字下划线',
	'date' => '日期',
	'email' => 'E-mail',
);
$rpidsarr = array('0' => '不下载远程附件');
$rprojects = cls_cache::Read('rprojects');
foreach($rprojects as $k => $v) $rpidsarr[$k] = $v['cname'];
$wmidsarr = array('0' => '图片不加水印');
$watermarks = cls_cache::Read('watermarks');
foreach($watermarks as $k => $v) $v['Available'] && $wmidsarr[$k] = $v['cname'];