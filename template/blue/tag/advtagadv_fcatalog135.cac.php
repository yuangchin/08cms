<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog135 = array (
  'ename' => 'adv_fcatalog135',
  'tclass' => 'advertising',
  'template' => '<div class="ad mb5">
{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" >{c$ad_image1200_70 [cname=ad_image1200_70/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=70/]}<img src="{url}" width="1200" height="70" />{/c$ad_image1200_70}</a>{elseif $v[\'flash\']}{c$flash1200_70 [cname=flash1200_70/] [tclass=flash/] [tname=flash/] [val=u/] [width=1200/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash1200_70}{/if}</div>',
  'setting' => 
  array (
    'casource' => 'fcatalog135',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;