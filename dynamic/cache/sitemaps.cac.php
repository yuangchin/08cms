<?php
defined('M_COM') || exit('No Permission');
$sitemaps = array (
  'google' => 
  array (
    'ename' => 'google',
    'cname' => 'Google Sitemap',
    'xml_url' => 'google.xml',
    'available' => '1',
    'vieworder' => '1',
    'issystem' => '1',
    'tpl' => 'google.htm',
    'ttl' => '1',
  ),
  'baidu' => 
  array (
    'ename' => 'baidu',
    'cname' => 'Baidu新闻协议',
    'xml_url' => 'baidu.xml',
    'available' => '1',
    'vieworder' => '2',
    'issystem' => '1',
    'tpl' => 'baidu.htm',
    'ttl' => '1',
  ),
  'google_col' => 
  array (
    'ename' => 'google_col',
    'cname' => 'Goolge_栏目',
    'xml_url' => 'google_col.xml',
    'available' => '1',
    'vieworder' => '3',
    'issystem' => '0',
    'tpl' => 'google_col.htm',
    'ttl' => '1',
  ),
  'google_arc' => 
  array (
    'ename' => 'google_arc',
    'cname' => 'Goolge_文档',
    'xml_url' => 'google_arc.xml',
    'available' => '1',
    'vieworder' => '4',
    'issystem' => '0',
    'tpl' => 'google_arc.htm',
    'ttl' => '0',
  ),
  'baidu_mobile' => 
  array (
    'ename' => 'baidu_mobile',
    'cname' => '百度移动Sitemap',
    'xml_url' => 'baidu_mobile.xml',
    'available' => '1',
    'vieworder' => '5',
    'issystem' => '0',
    'tpl' => 'baidu_mobile.htm',
    'ttl' => '12',
  ),
  'baidu_mob_push' => 
  array (
    'ename' => 'baidu_mob_push',
    'cname' => '百度主动推送',
    'xml_url' => 'baidu_mob_push.xml',
    'available' => '1',
    'vieworder' => '6',
    'issystem' => '0',
    'tpl' => 'baidu_mob_push.htm',
    'ttl' => '0',
  ),
) ;