<?php
defined('M_COM') || exit('No Permission');
$abrels = array (
  1 => 
  array (
    'arid' => '1',
    'cname' => '楼盘内资讯,问答,特价,周边,沙盘',
    'remark' => '资讯,问答,特价,周边,沙盘->楼盘',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbums',
    'available' => '1',
    'schids' => 
    array (
      0 => 1,
      1 => 106,
      2 => 107,
      3 => 8,
      4 => 111,
    ),
    'tchids' => 
    array (
      0 => 4,
      1 => 115,
      2 => 116,
    ),
    'autocheck' => 1,
  ),
  2 => 
  array (
    'arid' => '2',
    'cname' => '新房团购内户型',
    'remark' => '户型->新房团购',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbums',
    'available' => '1',
    'schids' => 
    array (
      0 => 11,
    ),
    'tchids' => 
    array (
      0 => 5,
    ),
    'autocheck' => 1,
  ),
  3 => 
  array (
    'arid' => '3',
    'cname' => '楼盘内(租房,二手,团购,相册,户型)',
    'remark' => '单个文档(二手房,出租,团购,相册,户型)->单个楼盘',
    'source' => '0',
    'target' => '0',
    'tbl' => '',
    'available' => '1',
    'schids' => 
    array (
      0 => 2,
      1 => 3,
      2 => 5,
      3 => 7,
      4 => 11,
    ),
    'tchids' => 
    array (
      0 => 4,
    ),
    'autocheck' => 1,
  ),
  4 => 
  array (
    'arid' => '4',
    'cname' => '公司内经纪人',
    'remark' => '经纪人->经纪公司',
    'source' => '1',
    'target' => '1',
    'tbl' => '',
    'available' => '1',
  ),
  5 => 
  array (
    'arid' => '5',
    'cname' => '视频内楼盘',
    'remark' => '楼盘->视频',
    'source' => '0',
    'target' => '0',
    'tbl' => '',
    'available' => '1',
    'schids' => 
    array (
      0 => 4,
      1 => 115,
      2 => 116,
    ),
    'tchids' => 
    array (
      0 => 12,
    ),
    'autocheck' => 1,
  ),
  6 => 
  array (
    'arid' => '6',
    'cname' => '开发商内楼盘',
    'remark' => '楼盘->开发商',
    'source' => '0',
    'target' => '0',
    'tbl' => '',
    'available' => '1',
    'schids' => 
    array (
      0 => 4,
      1 => 115,
      2 => 116,
    ),
    'tchids' => 
    array (
      0 => 13,
    ),
    'autocheck' => 1,
  ),
  12 => 
  array (
    'arid' => '12',
    'cname' => '关联专题',
    'remark' => '资讯、楼盘、活动、视频、开发商->专题',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbum_zt',
    'available' => '1',
    'schids' => 
    array (
      0 => 1,
      1 => 4,
      2 => 5,
      3 => 12,
      4 => 13,
    ),
    'tchids' => 
    array (
      0 => 14,
    ),
    'autocheck' => 1,
  ),
  32 => 
  array (
    'arid' => '32',
    'cname' => '看房团内楼盘',
    'remark' => '看房活动下的楼盘',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbum_kfhdlp',
    'available' => '1',
    'schids' => 
    array (
      0 => 4,
    ),
    'tchids' => 
    array (
      0 => 110,
    ),
    'autocheck' => 1,
  ),
  33 => 
  array (
    'arid' => '33',
    'cname' => '楼盘内楼盘分销',
    'remark' => '楼盘分销->楼盘',
    'source' => '0',
    'target' => '0',
    'tbl' => '',
    'available' => '1',
    'schids' => 
    array (
      0 => 113,
    ),
    'tchids' => 
    array (
      0 => 4,
    ),
    'autocheck' => 1,
  ),
  34 => 
  array (
    'arid' => '34',
    'cname' => '直播内资讯,楼盘,视频',
    'remark' => '直播->资讯,楼盘,视频',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbums_live',
    'available' => '1',
    'schids' => 
    array (
      0 => 1,
      1 => 4,
      2 => 12,
    ),
    'tchids' => 
    array (
      0 => 114,
    ),
    'autocheck' => 1,
  ),
  35 => 
  array (
    'arid' => '35',
    'cname' => '楼盘内的资讯,周边,楼栋(商业地产)',
    'remark' => '资讯,周边,楼栋->楼盘(含写字楼,商铺,楼栋)',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbums_arcs',
    'available' => '1',
    'schids' => 
    array (
      0 => 1,
      1 => 8,
      2 => 111,
    ),
    'tchids' => 
    array (
      1 => 115,
      2 => 116,
    ),
    'autocheck' => 1,
  ),
  36 => 
  array (
    'arid' => '36',
    'cname' => '楼盘内(租房,二手,相册)(商业地产)',
    'remark' => '单个文档(二手房,出租,相册)->单个楼盘(含写字楼,商铺)',
    'source' => '0',
    'target' => '0',
    'tbl' => '',
    'available' => '1',
    'schids' => 
    array (
      0 => 7,
      1 => 117,
      2 => 118,
      3 => 119,
      4 => 120,
    ),
    'tchids' => 
    array (
      1 => 115,
      2 => 116,
    ),
    'autocheck' => 1,
  ),
  37 => 
  array (
    'arid' => '37',
    'cname' => '楼栋内户型',
    'remark' => '户型->楼栋',
    'source' => '0',
    'target' => '0',
    'tbl' => 'aalbums_loudong',
    'available' => '1',
    'schids' => 
    array (
      0 => 11,
    ),
    'tchids' => 
    array (
      0 => 111,
    ),
    'autocheck' => 1,
  ),
  38 => 
  array (
    'arid' => '38',
    'cname' => '房源内图片',
    'remark' => '房源->图片',
    'source' => '0',
    'target' => '0',
    'tbl' => '',
    'available' => '1',
    'schids' => 
    array (
      0 => 121,
    ),
    'tchids' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'autocheck' => 1,
  ),
  51 => 
  array (
    'arid' => '51',
    'cname' => '会员-测评师',
    'remark' => '测评师',
    'source' => '1',
    'target' => '1',
    'tbl' => 'cepingshi',
    'available' => '1',
  ),
) ;