<?php
defined('M_COM') || exit('No Permission');
$rprojects = array (
  1 => 
  array (
    'rpid' => '1',
    'cname' => '远程图片下载(系统)',
    'rmfiles' => 
    array (
      'jpg' => 
      array (
        'maxsize' => 300,
        'minisize' => 1,
        'mime' => 'image/jpg',
        'ftype' => 'image',
        'extname' => 'jpg',
      ),
      'gif' => 
      array (
        'maxsize' => 300,
        'minisize' => 1,
        'mime' => 'image/gif',
        'ftype' => 'image',
        'extname' => 'gif',
      ),
      'jpeg' => 
      array (
        'maxsize' => 300,
        'minisize' => 1,
        'mime' => 'image/jpeg',
        'ftype' => 'image',
        'extname' => 'jpeg',
      ),
      'png' => 
      array (
        'maxsize' => 300,
        'minisize' => 1,
        'mime' => 'image/png',
        'ftype' => 'image',
        'extname' => 'png',
      ),
    ),
    'timeout' => '10',
    'excludes' => 
    array (
    ),
    'issystem' => '1',
  ),
) ;