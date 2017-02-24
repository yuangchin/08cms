<?php
defined('M_COM') || exit('No Permission');
$fields5 = array (
  'subject' => 
  array (
    'ename' => 'subject',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '标题',
    'issystem' => '1',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives23',
    'length' => '100',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '1',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '1',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'thumb' => 
  array (
    'ename' => 'thumb',
    'datatype' => 'image',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '活动图片',
    'issystem' => '0',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives23',
    'length' => '0',
    'vieworder' => '0',
    'rpid' => '1',
    'wmid' => '0',
    'guide' => '活动的宣传图片',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'content' => 
  array (
    'ename' => 'content',
    'datatype' => 'htmltext',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '活动说明',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '0',
    'vieworder' => '0',
    'rpid' => '1',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'hdtel' => 
  array (
    'ename' => 'hdtel',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '咨询电话',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '50',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '/^\\s*\\d{3,4}[-]?\\d{7,8}\\s*$/',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'ksnum' => 
  array (
    'ename' => 'ksnum',
    'datatype' => 'int',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '可售套数',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '0',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'scj' => 
  array (
    'ename' => 'scj',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '市场价',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '100',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '例：5000元/平米；一次性付清9.5折',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'tgj' => 
  array (
    'ename' => 'tgj',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '团购价',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '100',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '例：5000元/平米；一次性付清9.5折',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'hdadr' => 
  array (
    'ename' => 'hdadr',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '活动地址',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '255',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '1',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'hdnum' => 
  array (
    'ename' => 'hdnum',
    'datatype' => 'int',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '团购人数',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_5',
    'length' => '0',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'yhsm' => 
  array (
    'ename' => 'yhsm',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '优惠说明',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives23',
    'length' => '100',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'keywords' => 
  array (
    'ename' => 'keywords',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '关键词',
    'issystem' => '0',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives23',
    'length' => '100',
    'vieworder' => '99',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'abstract' => 
  array (
    'ename' => 'abstract',
    'datatype' => 'multitext',
    'type' => 'a',
    'tpid' => '5',
    'cname' => '摘要',
    'issystem' => '0',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives23',
    'length' => '0',
    'vieworder' => '99',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
) ;